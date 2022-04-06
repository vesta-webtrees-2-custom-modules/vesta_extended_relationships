<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Aura\Router\RouterContainer;
use Cissee\Webtrees\Module\ExtendedRelationships\AjaxRequests;
use Cissee\Webtrees\Module\ExtendedRelationships\ExtendedRelationshipController;
use Cissee\Webtrees\Module\ExtendedRelationships\ExtendedRelationshipModuleTrait;
use Cissee\Webtrees\Module\ExtendedRelationships\HelpTexts;
use Cissee\Webtrees\Module\ExtendedRelationships\Sync;
use Cissee\WebtreesExt\Module\ModuleMetaInterface;
use Cissee\WebtreesExt\Module\ModuleMetaTrait;
use Cissee\WebtreesExt\Modules\RelationshipPath;
use Cissee\WebtreesExt\Modules\RelationshipUtils;
use Cissee\WebtreesExt\MoreI18N;
use Cissee\WebtreesExt\Requests;
use Fig\Http\Message\RequestMethodInterface;
use Fisharebest\Localization\Translation;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Module\ModuleChartInterface;
use Fisharebest\Webtrees\Module\ModuleChartTrait;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleListInterface;
use Fisharebest\Webtrees\Module\ModuleListTrait;
use Fisharebest\Webtrees\Module\RelationshipsChartModule;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\LocalizationService;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\RelationshipService;
use Fisharebest\Webtrees\Services\TimeoutService;
use Fisharebest\Webtrees\Services\TreeService;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\User;
use Fisharebest\Webtrees\View;
use Fisharebest\Webtrees\Webtrees;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vesta\Hook\HookInterfaces\EmptyIndividualFactsTabExtender;
use Vesta\Hook\HookInterfaces\EmptyRelativesTabExtender;
use Vesta\Hook\HookInterfaces\IndividualFactsTabExtenderInterface;
use Vesta\Hook\HookInterfaces\RelativesTabExtenderInterface;
use Vesta\Model\GenericViewElement;
use Vesta\VestaModuleTrait;
use const CAL_GREGORIAN;
use function app;
use function cal_from_jd;
use function redirect;
use function response;
use function route;
use function view;

// we extend RelationshipsChartModule so that links to this chart are used even in non-extended tabs etc.
class ExtendedRelationshipModule extends RelationshipsChartModule implements
    ModuleCustomInterface, 
    ModuleMetaInterface, 
    ModuleConfigInterface, 
    ModuleChartInterface, 
    ModuleListInterface, 
    RequestHandlerInterface, 
    IndividualFactsTabExtenderInterface, 
    RelativesTabExtenderInterface {

    use ModuleCustomTrait,
        ModuleMetaTrait,
        ModuleConfigTrait,
        ModuleChartTrait,
        ModuleListTrait,
        VestaModuleTrait {
        VestaModuleTrait::customTranslations insteadof ModuleCustomTrait;
        VestaModuleTrait::getAssetAction insteadof ModuleCustomTrait;
        VestaModuleTrait::assetUrl insteadof ModuleCustomTrait;
        VestaModuleTrait::getConfigLink insteadof ModuleConfigTrait;
        ModuleMetaTrait::customModuleVersion insteadof ModuleCustomTrait;
        ModuleMetaTrait::customModuleLatestVersion insteadof ModuleCustomTrait;
    }

    use EmptyIndividualFactsTabExtender;
    use EmptyRelativesTabExtender;

    use ExtendedRelationshipModuleTrait {
        ExtendedRelationshipModuleTrait::editConfigAfterFaq insteadof VestaModuleTrait;
    }

    protected const ROUTE_URL = '/tree/{tree}/vesta-relationships-{ancestors}-{recursion}/{xref}{/xref2}';
    protected const ROUTE_URL_LIST = '/tree/{tree}/individual-patriarchs-list';

    /** It would be more correct to use PHP_INT_MAX, but this isn't friendly in URLs */
    public const UNLIMITED_RECURSION = 99;

    /** By default new trees allow unlimited recursion */
    public const DEFAULT_RECURSION = '99';
    public const DEFAULT_ANCESTORS = '1'; //should we use '1' here even if in case this option isn't configured?
    public const DEFAULT_PARAMETERS = [
        'ancestors' => self::DEFAULT_ANCESTORS,
        'recursion' => self::DEFAULT_RECURSION,
    ];

    /** @var ExtendedIndividualListController */
    protected $listController;

    public function __construct(
        ModuleService $module_service,
        RelationshipService $relationship_service,
        TreeService $tree_service) {

        parent::__construct($module_service, $relationship_service, $tree_service);
        $localization_service = app(LocalizationService::class);

        $this->listController = new ExtendedIndividualListController(
            $localization_service,
            $this);
    }

    public function customModuleAuthorName(): string {
        return 'Richard Cissée';
    }

    public function customModuleMetaDatasJson(): string {
        return file_get_contents(__DIR__ . '/metadata.json');
    }

    public function customModuleLatestMetaDatasJsonUrl(): string {
        return 'https://raw.githubusercontent.com/vesta-webtrees-2-custom-modules/vesta_extended_relationships/master/metadata.json';
    }

    public function customModuleSupportUrl(): string {
        return 'https://cissee.de';
    }

    public function resourcesFolder(): string {
        return __DIR__ . '/resources/';
    }

    public function customTranslations(string $language): array {
        $languageFile1 = $this->resourcesFolder() . 'lang/' . $language . '.mo';
        $languageFile2 = $this->resourcesFolder() . 'lang/' . $language . '.csv';
        $languageFile3 = $this->resourcesFolder() . 'lang/ext/' . $language . '.mo';
        $languageFile4 = $this->resourcesFolder() . 'lang/ext/' . $language . '.csv';
        $ret = [];
        if (file_exists($languageFile1)) {
            $ret = (new Translation($languageFile1))->asArray();
        }
        if (file_exists($languageFile2)) {
            $ret = array_merge($ret, (new Translation($languageFile2))->asArray());
        }
        if (file_exists($languageFile3)) {
            $ret = array_merge($ret, (new Translation($languageFile3))->asArray());
        }
        if (file_exists($languageFile4)) {
            $ret = array_merge($ret, (new Translation($languageFile4))->asArray());
        }
        return $ret;
    }

    public function onBoot(): void {
        //define our 'pretty' routes
        //note: potentially problematic in case of name clashes; 
        //webtrees isn't interested in solving this properly, see
        //https://www.webtrees.net/index.php/en/forum/2-open-discussion/33687-pretty-urls-in-2-x

        $router_container = app(RouterContainer::class);
        assert($router_container instanceof RouterContainer);

        $router_container->getMap()
            ->get(static::class, static::ROUTE_URL, $this)
            ->allows(RequestMethodInterface::METHOD_POST)
            ->tokens([
                'ancestors' => '\d+',
                'generations' => '\d+',
        ]);

        $router_container->getMap()
            ->get(ExtendedIndividualListController::class, static::ROUTE_URL_LIST, $this->listController);


        if (str_starts_with(Webtrees::VERSION, '2.1')) {
            // Replace an existing view with our own version.
            // this is hacky, but easier than patching IndividualListModule.
            // as usual, webtrees code isn't very extensible.
            View::registerCustomView('::lists/individuals-table', $this->name() . '::lists/individuals-table-switch');
            View::registerCustomView('::lists/individuals-table-with-patriarchs', $this->name() . '::lists/individuals-table-with-patriarchs');

            //same here
            View::registerCustomView('::lists/surnames-table', $this->name() . '::lists/surnames-table-switch');
            View::registerCustomView('::lists/surnames-table-with-patriarchs', $this->name() . '::lists/surnames-table-with-patriarchs');
        } else {
            View::registerCustomView('::lists/individuals-table', $this->name() . '::lists/individuals-table-switch_20');
            View::registerCustomView('::lists/individuals-table-with-patriarchs_20', $this->name() . '::lists/individuals-table-with-patriarchs_20');

            //same here
            View::registerCustomView('::lists/surnames-table', $this->name() . '::lists/surnames-table-switch');
            View::registerCustomView('::lists/surnames-table-with-patriarchs', $this->name() . '::lists/surnames-table-with-patriarchs');
        }


        $this->flashWhatsNew('\Cissee\Webtrees\Module\ExtendedRelationships\WhatsNew', 2);
    }

    protected function editConfigAfterFaq() {
        $url = route('module', [
            'module' => $this->name(),
            'action' => 'AdminSync'
        ]);
        ?>
        <h1><?php echo I18N::translate('Synchronization'); ?></h1>

        <ol class="breadcrumb small">
            <li>
                <a href="<?php echo $url; ?>">
                    <?php echo I18N::translate('Synchronize trees to obtain dated relationship links'); ?>					
                </a>
                <?php echo I18N::translate(' (see below for details).'); ?>
            </li>
        </ol>
        <?php
    }

    public static function getRelationshipLink(
        $moduleName,
        Tree $tree,
        $text,
        $xref1,
        $xref2,
        $mode,
        $beforeJD = null) {

        if ($text === null) {
            $slcaController = new ExtendedRelationshipController;

            $paths = $slcaController->x_calculateRelationships_123456($tree, $xref1, $xref2, $mode, 1, $beforeJD);

            foreach ($paths as $path) {

                $relationshipPath = RelationshipPath::create($tree, $path);
                if ($relationshipPath === null) {
                    // Cannot see one of the families/individuals, due to privacy;
                    continue;
                }
                $text = RelationshipUtils::getRelationshipName($relationshipPath);
                if ($text === '') {
                    $text = null;
                    continue;
                }

                /*
                  //TODO: 'getRelationshipName' requires a variant using $beforeJD,
                  //because 'ex-husband' etc. is not correct at all dates!
                  //also, 'husband' may not always be correct either, if the marriage e.g. occured after the birth of a child
                  //once we use additional events to establish family (such as ENGA), it gets more complicated
                  //should use 'fiancée' etc. at certain dates
                 */

                break;
            }
        }
        if ($text === null) {
            $text = MoreI18N::xlate('No relationship found');
        }

        $parameters = [
            'ancestors' => $mode
        ];

        if ($beforeJD !== null) {
            $parameters['beforeJD'] = $beforeJD;
        }

        $url = route(static::class, [
            'xref' => $xref1,
            'xref2' => $xref2,
            'tree' => $tree->name(),
            ] + $parameters + self::DEFAULT_PARAMETERS);

        return '<a href="' . $url . '" title="' . I18N::translate('Relationships') . '">' . $text . '</a>';
    }

    public function getRelationshipLinkForFactsTabFillViaAjax(
        $text,
        $xref1,
        $xref2,
        $tree,
        $mode,
        $beforeJD,
        $prefix,
        $suffix) {

        $toggleableRels = boolval($this->getPreference('FTAB_TOGGLEABLE_RELS', '1'));

        $parameters = [
            'module' => $this->name(),
            'action' => 'Rel',
            'xref1' => $xref1,
            'xref2' => $xref2,
            'mode' => $mode,
            'tree' => $tree->name()
        ];
        if ($beforeJD !== null) {
            $parameters['beforeJD'] = $beforeJD;
        }
        if ($text) {
            $parameters['text'] = $text;
        }

        $url = route('module', $parameters);

        //escape newlines (e.g. from Individual.getSexImage()), also
        //escape prefix/suffix for cases such as
        //$suffix = "<i class=\"icon-sex_m_9x9\"></i>";
        $prefix = str_replace(array("\n", "\r"), "", addslashes($prefix));
        $suffix = str_replace(array("\n", "\r"), "", addslashes($suffix));

        //must disambiguate with $beforeJD - may show up multiple times!
        //(and technically with everything else that goes into the url)
        //also with prefix/suffix, otherwise these get mixed up if same rel is used with different prefix/suffix!
        //hash alone would be sufficient, explicit xrefs here only for easier debugging!
        $rel = 'rel_' . $xref1 . '_' . $xref2 . '_' . md5($url . $prefix . $suffix);

        $main = '';
        if (!$toggleableRels) {
            $main = "<span class=\"" . $rel . "\"></span>";
        } else {
            //make toggleable, collapse initially
            $main = "<span class=\"toggleableRelsFactstab " . $rel . " collapse\"></span>";
        }


        ob_start();

        if (!$toggleableRels) {
            ?>
            <script>
                //load via ajax
                console.log("init via ajax <?php echo $rel ?>");
                var ajaxRequest = $.get("<?php echo $url ?>");
                ajaxRequest.done(function (content) {
                    $(".<?php echo $rel ?>").html("<?php echo $prefix ?>" + content + "<?php echo $suffix ?>");
                })
            </script>
            <?php
        } else {
            //print if checkbox is checked (change via persistent toggle)
            ?>
            <script>
                $('.<?php echo $rel ?>').on('shown.bs.collapse', function () {
                    console.log("on shown: check <?php echo $rel ?>");
                    if ("" === $(".<?php echo $rel ?>").text()) {
                        //load once via ajax
                        console.log("on shown: init via ajax <?php echo $rel ?>");
                        var ajaxRequest = $.get("<?php echo $url ?>");
                        ajaxRequest.done(function (content) {
                            $(".<?php echo $rel ?>").html("<?php echo $prefix ?>" + content + "<?php echo $suffix ?>");
                        });
                    }
                });
            </script>
            <?php
        }

        return new GenericViewElement($main, ob_get_clean());
    }

    //Families Tab

    protected function getOutputAfterTab($toggleableRels, $toggle) {
        $post = "";

        if ($toggleableRels) {
            $post = $this->getScript($toggle);
        }

        return new GenericViewElement('', $post);
    }

    protected function getScript($toggle) {
        ob_start();
        ?>
        <script>
            webtrees.persistentToggle(document.querySelector('#<?php echo $toggle; ?>'));
        </script>
        <?php
        return ob_get_clean();
    }

    protected function getOutputInDescriptionBox(
        bool $toggleableRels,
        string $id,
        string $targetClass,
        string $label) {

        ob_start();
        if ($toggleableRels) {
            ?>
            <label>
                <input id="<?php echo $id; ?>" type="checkbox" data-bs-toggle="collapse" data-bs-target=".<?php echo $targetClass; ?>" data-wt-persist="<?php echo $id; ?>">
                <?php echo I18N::translate($label); ?>
            </label>
            <?php
        }
        return new GenericViewElement(ob_get_clean(), '');
    }

    protected function getOutputAfterDescriptionBox(
        Individual $person,
        $settingsPrefix,
        $mainRels,
        $className) {

        $mode = intval($this->getPreference($settingsPrefix . 'TAB_REL_TO_DEFAULT_INDI', '1'));
        $recursion = intval($this->getPreference('RELATIONSHIP_RECURSION', self::DEFAULT_RECURSION));
        $showCa = boolval($this->getPreference($settingsPrefix . 'TAB_REL_TO_DEFAULT_INDI_SHOW_CA', '1'));

        if ($mode === 0) {
            return new GenericViewElement('', '');
        }

        $toggleableRels = boolval($this->getPreference($settingsPrefix . 'TAB_TOGGLEABLE_RELS', '1'));

        //expensive - load async (and only if visible) 
        //(we have to print via ajax call because we have to indirectly read local storage to determine visibility,
        //but it's preferable for faster tab display anyway)
        //FunctionsPrintRels::printSlcasWrtDefaultIndividual($controller->record, $mode, $recursion, $showCa);

        $xref = $person->xref();

        $url = route('module', [
            'module' => $this->name(),
            'action' => 'MainRels',
            'tree' => $person->tree()->name(), //always set the tree (2.x doesn't have default tree via Session class)!
            'pid' => $xref,
            'mode' => $mode,
            'recursion' => $recursion,
            'showCa' => $showCa
        ]);

        $main = '';

        if (!$toggleableRels) {
            $main = "<div class=\"" . $mainRels . "\"><span/></div>";
        } else {
            //make toggleable, collapse initially
            $main = "<div class=\"" . $className . " " . $mainRels . " collapse\"><span/></div>";
        }

        ob_start();
        if (!$toggleableRels) {
            ?>
            <script>
                //load via ajax
                console.log("init via ajax <?php echo $mainRels ?>");
                var ajaxRequest = $.get("<?php echo $url ?>");
                ajaxRequest.done(function (content) {
                    $(".<?php echo $mainRels ?> > span").html(content);
                })
            </script>
            <?php
        } else {
            //print if checkbox is checked (change via persistent toggle)
            ?>
            <script>
                $('.<?php echo $mainRels ?>').on('shown.bs.collapse', function () {
                    console.log("on shown: check <?php echo $mainRels ?>");
                    if ("" === $(".<?php echo $mainRels ?>").text()) {
                        //load once via ajax
                        console.log("on shown: init via ajax <?php echo $mainRels ?>");
                        var ajaxRequest = $.get("<?php echo $url ?>");
                        ajaxRequest.done(function (content) {
                            $(".<?php echo $mainRels ?> > span").html(content);
                        });
                    }
                });
            </script>
            <?php
        }
        return new GenericViewElement($main, ob_get_clean());
    }

    protected function getOutputFamilyAfterSubHeaders(Family $family, $type) {
        if ('FAMC' === $type) {
            $mode = intval($this->getPreference('TAB_REL_OF_PARENTS', '1'));
            $recursion = intval($this->getPreference('RELATIONSHIP_RECURSION', self::DEFAULT_RECURSION));
            $showCa = boolval($this->getPreference('TAB_REL_OF_PARENTS_SHOW_CA', '1'));
        } else {
            $mode = intval($this->getPreference('TAB_REL_TO_SPOUSE', '1'));
            $recursion = intval($this->getPreference('RELATIONSHIP_RECURSION', self::DEFAULT_RECURSION));
            $showCa = boolval($this->getPreference('TAB_REL_TO_SPOUSE_SHOW_CA', '1'));
        }

        $useBeforeJD = ($mode == 4) || ($mode == 5) || ($mode == 6) || ($mode == 7);

        $beforeJD = null;
        if ($useBeforeJD) {
            //same strategy as in Sync.php
            //'f_from' = 'family established no later than' (= minimum of date of marriage, first childbirth).

            $date = ExtendedRelationshipUtils::getFamilyEstablishedNoLaterThan($family);
            if ($date->isOK()) {
                $beforeJD = $date->minimumJulianDay();
            } else {
                //no need to load anything!
                return GenericViewElement::createEmpty();
            }

            /*
              $date = $family->getMarriageDate();
              if ($date->isOK()) {
              $beforeJD = $date->minimumJulianDay();
              }
             */
        }

        if ($mode === 0) {
            return;
        }

        $toggleableRels = boolval($this->getPreference('TAB_TOGGLEABLE_RELS', '1'));

        //expensive - load async (and only if visible) 
        //(we have to print via ajax call because we have to indirectly read local storage to determine visibility,
        //but it's preferable for faster tab display anyway)
        //FunctionsPrintRels::printSlcas($moduleName, $family, $access_level, $mode, $recursion, $showCa);
        //TODO: where is the $access_level checked now?

        $xref = $family->xref();

        $parameters = [
            'module' => $this->name(),
            'action' => 'FamRels',
            'tree' => $family->tree()->name(), //always set the tree (2.x doesn't have default tree via Session class)!
            'pid' => $xref,
            'mode' => $mode,
            'recursion' => $recursion,
            'showCa' => $showCa
        ];

        if ($beforeJD) {
            $parameters['beforeJD'] = $beforeJD;
        }

        $url = route('module', $parameters);

        //must disambiguate with $beforeJD - may show up multiple times!
        //(and technically with everything else that goes into the url)
        //hash alone would be sufficient, explicit xrefs here only for easier debugging!
        $rel = 'famRels_' . $xref . '_' . md5($url);

        $main = '';
        if (!$toggleableRels) {
            $main = "<span class=\"" . $rel . "\"></span>";
        } else {
            //make toggleable, collapse initially
            $main = "<span class=\"toggleableRels " . $rel . " collapse\"></span>";
        }

        ob_start();
        if (!$toggleableRels) {
            ?>
            <script>
                //load via ajax
                console.log("init via ajax <?php echo $rel ?>");
                var ajaxRequest = $.get("<?php echo $url ?>");
                ajaxRequest.done(function (content) {
                    $(".<?php echo $rel ?>").html(content);
                })
            </script>
            <?php
        } else {
            //print if checkbox is checked (change via persistent toggle)
            ?>
            <script>
                $('.<?php echo $rel ?>').on('shown.bs.collapse', function () {
                    console.log("on shown: check <?php echo $rel ?>");
                    if ("" === $(".<?php echo $rel ?>").text()) {
                        //load once via ajax
                        console.log("on shown: init via ajax <?php echo $rel ?>");
                        var ajaxRequest = $.get("<?php echo $url ?>");
                        ajaxRequest.done(function (content) {
                            $(".<?php echo $rel ?>").html(content);
                        });
                    }
                });
            </script>
            <?php
        }
        return new GenericViewElement($main, ob_get_clean());
    }

    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    //Chart

    public function chartUrl(Individual $individual, array $parameters = []): string {
        return route(static::class, [
            'xref' => $individual->xref(),
            'tree' => $individual->tree()->name(),
            ] + $parameters + self::DEFAULT_PARAMETERS);
    }

    public function chartBoxMenu(Individual $individual): ?Menu {
        return $this->chartMenu($individual);
    }

    public function chartMenuClass(): string {
        return 'menu-chart-relationship';
    }

    public function chartMenu(Individual $individual): Menu {

        $gedcomid = $individual->tree()->getUserPreference(Auth::user(), User::PREF_TREE_ACCOUNT_XREF);

        if ($gedcomid !== '' && $gedcomid !== $individual->xref()) {
            return new Menu(
                $this->getChartTitle(I18N::translate('Relationship to me')),
                $this->chartUrl($individual, ['xref2' => $gedcomid]),
                $this->chartMenuClass(),
                $this->chartUrlAttributes()
            );
        }

        return new Menu(
            $this->getChartTitle(I18N::translate('Relationships')),
            $this->chartUrl($individual),
            $this->chartMenuClass(),
            $this->chartUrlAttributes()
        );
    }

    //ok to use this class for ajax requests as long as we fully initialize (session.php) anyway!
    //(still ~100ms slower (local server) than using moduleAjax.php directly though, just for resolving via module.php grr)
    //otherwise debatable (initialization may be too expensive for larger number of ajax requests, cf gov4webtrees)
    //otoh, this approach is expected to be safer wrt rewrite rules etc
    public function getMainRelsAction(ServerRequestInterface $request): ResponseInterface {
        //'tree' is handled specifically in Router.php
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        ob_start();
        AjaxRequests::printMainSlcas($this->name(), $request, $tree);
        return response(ob_get_clean());
    }

    public function getFamRelsAction(ServerRequestInterface $request): ResponseInterface {
        //'tree' is handled specifically in Router.php
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        ob_start();
        AjaxRequests::printFamilySlcas($this->name(), $request, $tree);
        return response(ob_get_clean());
    }

    public function getRelAction(ServerRequestInterface $request): ResponseInterface {
        //'tree' is handled specifically in Router.php
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $link = AjaxRequests::getRelationshipLink($this->name(), $request, $tree);
        return response($link);
    }

    public function getAdminSyncAction(): ResponseInterface {
        return response($this->syncConfig());
    }

    public function postAdminSyncAction(ServerRequestInterface $request): ResponseInterface {
        $timeout_service = app(TimeoutService::class);
        $sync = new Sync($this->name());
        return $sync->sync($request, $timeout_service);
    }

    /*
      public function chartUrl(Individual $individual, array $parameters = []): string {
      }
     */

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $xref = $request->getAttribute('xref');
        assert(is_string($xref));

        $xref2 = $request->getAttribute('xref2') ?? '';

        $ajax = $request->getQueryParams()['ajax'] ?? '';
        $ancestors = (int) $request->getAttribute('ancestors');
        $recursion = (int) $request->getAttribute('recursion');
        $user = $request->getAttribute('user');

        Auth::checkComponentAccess($this, ModuleChartInterface::class, $tree, $user);

        //[RC] block added start
        $beforeJD = Requests::getIntOrNull($request, 'beforeJD');
        $dateDisplay = null;
        if ($beforeJD) {
            $ymd = cal_from_jd($beforeJD, CAL_GREGORIAN);
            $date = new Date($ymd["day"] . ' ' . strtoupper($ymd["abbrevmonth"]) . ' ' . $ymd["year"]);
            $dateDisplay = $date->display();
        }
        //[RC] block added end
        // Convert POST requests into GET requests for pretty URLs.
        if ($request->getMethod() === RequestMethodInterface::METHOD_POST) {
            $params = (array) $request->getParsedBody();

            $parameters = [
                'ancestors' => $params['ancestors'],
                'recursion' => $params['recursion'],
                'tree' => $tree->name(),
                'xref' => $params['xref'],
                'xref2' => $params['xref2'],
            ];

            if ($beforeJD !== null) {
                $parameters['beforeJD'] = $beforeJD;
            }

            return redirect(route(static::class, $parameters));
        }

        $individual1 = Registry::individualFactory()->make($xref, $tree);
        $individual2 = Registry::individualFactory()->make($xref2, $tree);

        //$ancestors_only = (int) $tree->getPreference('RELATIONSHIP_ANCESTORS', static::DEFAULT_ANCESTORS);
        //$max_recursion  = (int) $tree->getPreference('RELATIONSHIP_RECURSION', static::DEFAULT_RECURSION);
        $max_recursion = intval($this->getPreference('RELATIONSHIP_RECURSION', RelationshipsChartModule::DEFAULT_RECURSION));

        $recursion = min($recursion, $max_recursion);

        if ($individual1 instanceof Individual) {
            $individual1 = Auth::checkIndividualAccess($individual1, false, true);
        }

        if ($individual2 instanceof Individual) {
            $individual2 = Auth::checkIndividualAccess($individual2, false, true);
        }

        if ($individual1 instanceof Individual && $individual2 instanceof Individual) {
            if ($ajax === '1') {
                $controller = new ExtendedRelationshipsChartController($this);
                return $controller->chart($individual1, $individual2, $recursion, $ancestors, $beforeJD);
            }

            /* I18N: %s are individual’s names */
            $title = I18N::translate('Relationships between %1$s and %2$s', $individual1->fullName(), $individual2->fullName());

            $parameters = [
                'ajax' => true,
                'ancestors' => $ancestors,
                'recursion' => $recursion,
                'xref2' => $individual2->xref(),
            ];

            if ($beforeJD !== null) {
                $parameters['beforeJD'] = $beforeJD;
            }

            $ajax_url = $this->chartUrl($individual1, $parameters);
        } else {
            $title = I18N::translate('Relationships');
            $ajax_url = '';
        }

        //[RC] block added start
        $chart1 = ($ancestors == 1) || (boolval($this->getPreference('CHART_1', '1')));
        $chart2 = ($ancestors == 2) || (boolval($this->getPreference('CHART_2', '0')));
        $chart3 = ($ancestors == 3) || (boolval($this->getPreference('CHART_3', '1')));
        $chart4 = ($ancestors == 4) || (boolval($this->getPreference('CHART_4', '1')));
        $chart5 = ($ancestors == 5) || (boolval($this->getPreference('CHART_5', '1')));
        $chart6 = ($ancestors == 6) || (boolval($this->getPreference('CHART_6', '0')));
        $chart7 = ($ancestors == 7) || (boolval($this->getPreference('CHART_7', '0')));

        $options1 = [];
        $options2 = [];
        if ($beforeJD && ($chart4 || $chart5 || $chart6 || $chart7)) {
            //use separate options
            $this->addAncestorsOptions1($options1, $chart1, $chart2, $chart3);
            $this->addAncestorsOptions2($options2, $chart4, $chart5, $chart6, $chart7, $max_recursion);
        } else {
            //merge options
            $this->addAncestorsOptions1($options1, $chart1, $chart2, $chart3);
            $this->addAncestorsOptions2($options1, $chart4, $chart5, $chart6, $chart7, $max_recursion);
        }
        //[RC] block added end

        return $this->viewResponse($this->name() . '::page', [
                'ajax_url' => $ajax_url,
                'ancestors' => $ancestors,
                //'ancestors_only'     => $ancestors_only,
                //'ancestors_options'  => $this->ancestorsOptions(),
                'ancestors_options1' => $options1,
                'ancestors_options2' => $options2,
                'individual1' => $individual1,
                'individual2' => $individual2,
                'max_recursion' => $max_recursion,
                'module' => $this->name(),
                'recursion' => $recursion,
                //'recursion_options'  => $this->recursionOptions($max_recursion),
                'title' => $title,
                'tree' => $tree,
                'beforeJD' => $beforeJD,
                'dateDisplay' => $dateDisplay,
        ]);
    }

    /*
      public function getChartAction(ServerRequestInterface $request): ResponseInterface {
      //if null, initialized elsewhere if required
      $user = $request->getAttribute('user');

      //'tree' is handled specifically in Router.php
      $tree = $request->getAttribute('tree');
      assert($tree instanceof Tree);

      $controller = new ExtendedRelationshipsChartController($this);
      return $controller->page($request, $tree, $user);
      }

      //note that RelationshipsChartModule doesn't have separate actions any longer ...
      public function getChartActualAction(ServerRequestInterface $request): ResponseInterface {
      //'tree' is handled specifically in Router.php
      $tree = $request->getAttribute('tree');
      assert($tree instanceof Tree);

      $controller = new ExtendedRelationshipsChartController($this);
      return $controller->chart($request, $tree);
      }
     */

    public function getHelpAction(ServerRequestInterface $request): ResponseInterface {
        $topic = Requests::getString($request, 'topic');
        return response(HelpTexts::helpText($topic));
    }

    private function syncConfig() {
        $url = route('module', [
            'module' => $this->name(),
            'action' => 'AdminSync',
            'phase' => 1
        ]);

        // Render the view
        $innerHtml = view($this->name() . '::sync', [
            'title' => $this->title() . ' — ' . I18N::translate('Synchronization'),
            'post' => $url
        ]);

        // Insert the view into the (main) layout
        $html = view('layouts/administration', [
            'title' => $this->title() . ' — ' . I18N::translate('Synchronization'),
            'content' => $innerHtml
        ]);

        return $html;
    }

    //IndividualFactsTabExtenderInterface

    public function hFactsTabGetOutputForAssoRel(
        Fact $event,
        Individual $person,
        Individual $associate,
        $relationship_prefix,
        $relationship_name,
        $relationship_suffix,
        $inverse) {

        [, $tag] = explode(':', $event->tag());

        if ($inverse) {
            $restricted = (boolean) $this->getPreference('TAB_REL_TO_ASSO_RESTRICTED', '0');
            if ($restricted) {
                $parent = $event->record();
                if ($parent instanceof Family) {
                    $restrictedTo = preg_split("/[, ;:]+/", $this->getPreference('TAB_REL_TO_ASSO_RESTRICTED_FAM', 'MARR'), -1, PREG_SPLIT_NO_EMPTY);
                    if (!in_array($tag, $restrictedTo, true)) {
                        return null;
                    }
                } else {
                    $restrictedTo = preg_split("/[, ;:]+/", $this->getPreference('TAB_REL_TO_ASSO_RESTRICTED_INDI', 'CHR,BAPM'), -1, PREG_SPLIT_NO_EMPTY);
                    if (!in_array($tag, $restrictedTo, true)) {
                        return null;
                    }
                }
            }
        }

        //TODO: check if chart is available! otherwise, don't link to it.
        //(also check if respective option is available in chart?)
        //$relationship_name NOT used: we may use different reationship here!
        //for now, allow the day of the event as well "+1"
        //otherwise, we wouldn't obtain anything e.g. for baptisms on the date of birth
        //but only for certain events:
        //this is undesirable for events establishing additional relationships, i.e. MARR
        //e.g. we don't want trivial relation to best man of husband as brother-in-law
        $offset = 1;
        if ('MARR' === $tag) {
            $offset = 0;
        }

        $mode = (int) $this->getPreference('TAB_REL_TO_ASSO', '15');
        if ($mode == 0) {
            return null; //do not display, do not fallback
        }
        $beforeJD = null;
        if ($mode > 9) {
            if ($event->date()->minimumJulianDay() > 0) {
                $beforeJD = $event->date()->minimumJulianDay() + $offset;
            } //else event without (proper) date
            $mode = $mode - 10;
        }

        //direct
        //$link = $relationship_prefix . ExtendedRelationshipModule::getRelationshipLink($tree, null, $associate->xref(), $person->xref(), $mode, $beforeJD) . $relationship_suffix;
        //via ajax
        $link = $this->getRelationshipLinkForFactsTabFillViaAjax(
            null,
            $associate->xref(),
            $person->xref(),
            $person->tree(),
            $mode,
            $beforeJD,
            $relationship_prefix,
            $relationship_suffix);

        return $link;
    }

    //RelativesTabExtenderInterface

    public function hRelativesTabGetOutputFamAfterSH(Family $family, $type) {
        return $this->getOutputFamilyAfterSubHeaders($family, $type);
    }

    //IndividualFactsTabExtenderInterface
    //RelativesTabExtenderInterface

    public function hFactsTabGetOutputAfterTab(Individual $person) {
        $toggleableRels = boolval($this->getPreference('FTAB_TOGGLEABLE_RELS', '1'));
        return $this->getOutputAfterTab($toggleableRels, 'show-relationships-factstab');
    }

    public function hRelativesTabGetOutputAfterTab(Individual $person) {
        $toggleableRels = boolval($this->getPreference('TAB_TOGGLEABLE_RELS', '1'));
        return $this->getOutputAfterTab($toggleableRels, 'show-relationships');
    }

    public function hFactsTabGetOutputInDBox(Individual $person) {
        $toggleableRels = boolval($this->getPreference('FTAB_TOGGLEABLE_RELS', '1'));
        return $this->getOutputInDescriptionBox($toggleableRels, 'show-relationships-factstab', 'toggleableRelsFactstab', 'Relationships');
    }

    public function hRelativesTabGetOutputInDBox(Individual $person) {
        $toggleableRels = boolval($this->getPreference('TAB_TOGGLEABLE_RELS', '1'));
        return $this->getOutputInDescriptionBox($toggleableRels, 'show-relationships', 'toggleableRels', 'Relationships');
    }

    public function hFactsTabGetOutputAfterDBox(Individual $person) {
        return $this->getOutputAfterDescriptionBox($person, 'F', 'mainRelsFactstab', 'toggleableRelsFactstab');
    }

    public function hRelativesTabGetOutputAfterDBox(Individual $person) {
        return $this->getOutputAfterDescriptionBox($person, '', 'mainRels', 'toggleableRels');
    }

    /**
     * Possible options for the ancestors option
     *
     * @return string[]
     */
    private function addAncestorsOptions1(&$options, $chart1, $chart2, $chart3) {
        if ($chart1) {
            $options[1] = I18N::translate('Find a closest relationship via common ancestors');
        }

        if ($chart2) {
            $options[2] = I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each');
        }

        if ($chart3) {
            $options[3] = I18N::translate('Find all relationships via lowest common ancestors');
        }
    }

    private function addAncestorsOptions2(&$options, $chart4, $chart5, $chart6, $chart7, $max_recursion) {
        if ($chart4) {
            $options[4] = I18N::translate('Find the closest overall connections (preferably via common ancestors)');
        }

        if ($chart7) {
            $options[7] = I18N::translate('Find a closest relationship via common ancestors, or fallback to the closest overall connection');
        }

        if ($chart5) {
            $options[5] = I18N::translate('Find the closest overall connections');
        }

        if ($max_recursion != 0) {
            if ($chart6) {
                if ($max_recursion == RelationshipsChartModule::UNLIMITED_RECURSION) {
                    $options[6] = I18N::translate('Find all overall connections');
                } else {
                    $options[6] = I18N::translate('Find other overall connections');
                }
            }
        }
    }

    //////////////////////////////////////////////////////////////////////////////

    public function listTitle(): string {
        return $this->getListTitle(
                /* I18N: patriarchs are the male end-of-line ancestors ('Spitzenahnen') */I18N::translate('Individuals with Patriarchs'));
    }

    public function listMenuClass(): string {
        return 'menu-list-indi';
    }

    public function listUrl(Tree $tree, array $parameters = []): string {
        return $this->listController->listUrl($tree, $parameters);
    }

}

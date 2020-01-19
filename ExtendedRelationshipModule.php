<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Fisharebest\Localization\Translation;
use Cissee\Webtrees\Hook\HookInterfaces\EmptyIndividualFactsTabExtender;
use Cissee\Webtrees\Hook\HookInterfaces\EmptyRelativesTabExtender;
use Cissee\Webtrees\Hook\HookInterfaces\IndividualFactsTabExtenderInterface;
use Cissee\Webtrees\Hook\HookInterfaces\RelativesTabExtenderInterface;
use Cissee\Webtrees\Module\ExtendedRelationships\AjaxRequests;
use Cissee\Webtrees\Module\ExtendedRelationships\DirectFamily;
use Cissee\Webtrees\Module\ExtendedRelationships\ExtendedRelationshipController;
use Cissee\Webtrees\Module\ExtendedRelationships\ExtendedRelationshipModuleTrait;
use Cissee\Webtrees\Module\ExtendedRelationships\HelpTexts;
use Cissee\Webtrees\Module\ExtendedRelationships\Sync;
use Cissee\WebtreesExt\Functions\FunctionsExt;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Module\ModuleChartInterface;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\RelationshipsChartModule;
use Fisharebest\Webtrees\Services\TimeoutService;
use Fisharebest\Webtrees\Services\TreeService;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Webtrees;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Cissee\WebtreesExt\Requests;
use Vesta\Model\GenericViewElement;
use Vesta\VestaModuleTrait;
use function route;
use function view;

// we extend RelationshipsChartModule so that links to this chart are used even in non-extended tabs etc.
class ExtendedRelationshipModule extends RelationshipsChartModule implements ModuleCustomInterface, ModuleConfigInterface, ModuleChartInterface, IndividualFactsTabExtenderInterface, RelativesTabExtenderInterface {

  use VestaModuleTrait;
  use EmptyIndividualFactsTabExtender;
  use EmptyRelativesTabExtender;

//use ModuleChartTrait;

  use ExtendedRelationshipModuleTrait {
    ExtendedRelationshipModuleTrait::editConfigAfterFaq insteadof VestaModuleTrait;
  }

  /** It would be more correct to use PHP_INT_MAX, but this isn't friendly in URLs */
  const UNLIMITED_RECURSION = 99;

  /** By default new trees allow unlimited recursion */
  const DEFAULT_RECURSION = self::UNLIMITED_RECURSION;

  public function __construct(TreeService $tree_service) {
    parent::__construct($tree_service);
  }
    
    
  public function customModuleAuthorName(): string {
    return 'Richard Cissée';
  }

  public function customModuleVersion(): string {
    return '2.0.1.2';
  }

  public function customModuleLatestVersionUrl(): string {
    return 'https://cissee.de';
  }

  public function customModuleSupportUrl(): string {
    return 'https://cissee.de';
  }

  public function description(): string {
    return $this->getShortDescription();
  }

  /**
   * Where does this module store its resources
   *
   * @return string
   */
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

  public static function getRelationshipLink($moduleName, Tree $tree, $text, $xref1, $xref2, $mode, $beforeJD = null) {
    if ($text === null) {
      $slcaController = new ExtendedRelationshipController;

      $paths = $slcaController->x_calculateRelationships_123456($tree, $xref1, $xref2, $mode, 1, $beforeJD);

      foreach ($paths as $path) {
        // Extract the relationship names between pairs of individuals
        $relationships = $slcaController->oldStyleRelationshipPath($tree, $path);
        if (empty($relationships)) {
          // Cannot see one of the families/individuals, due to privacy;
          continue;
        }

        //use $person1/ $person2 here! (e.g. for gendered rels, and 'younger brother' etc.)
        $indi1 = Individual::getInstance($xref1, $tree);
        $indi2 = Individual::getInstance($xref2, $tree);

        //TODO: 'getRelationshipNameFromPath' requires a variant using $beforeJD,
        //because 'ex-husband' etc. is not correct at all dates!
        //also, 'husband' may not always be correct either, if the marriage e.g. occured after the birth of a child
        //once we use additional events to establish family (such as ENGA), it gets more complicated
        //should use 'fiancée' etc. at certain dates
        $text = FunctionsExt::getRelationshipNameFromPath(implode('', $relationships), $indi1, $indi2);
        break;
      }
    }
    if ($text === null) {
      $text = I18N::translate('No relationship found');
    }

    $parameters = [
        'module' => $moduleName,
        'action' => 'Chart',
        'xref' => $xref1, //do not use 'xref1' for 'Chart'
        'xref2' => $xref2,
        'find' => $mode,
        'tree' => $tree->name()
    ];

    if ($beforeJD !== null) {
      $parameters['beforeJD'] = $beforeJD;
    }

    return '<a href="' . route('module', $parameters) . '" title="' . I18N::translate('Relationships') . '">' . $text . '</a>';
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

    //disabled - buggy wrt I18N! not tested in 2.x. Won't be possible in future versions.
    $directAjax = false; //boolval($this->getPreference($settingsPrefix.'TAB_DIRECT_AJAX', '0'));
    $toggleableRels = boolval($this->getPreference('FTAB_TOGGLEABLE_RELS', '1'));

    if ($directAjax) {
      $url = Webtrees::MODULES_PATH . basename(__DIR__) . "/moduleAjax.php?request=rel&xref1=" . $xref1 . "&xref2=" . $xref2 . "&mode=" . $mode;
      if ($beforeJD) {
        $url .= "&beforeJD=" . $beforeJD;
      }
      if ($text) {
        $url .= "&text=" . $text;
      }
    } else {
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
    }

    //escape newlines (e.g. from Individual.getSexImage()), also
    //escape prefix/suffix for cases such as
    //$suffix = "<i class=\"icon-sex_m_9x9\"></i>";
    $prefix = str_replace(array("\n", "\r"), "", addslashes($prefix));
    $suffix = str_replace(array("\n", "\r"), "", addslashes($suffix));
    
    //must disambiguate with $beforeJD - may show up multiple times!
    //(and technically with everything else that goes into the url)
    //hash alone would be sufficient, explicit xrefs here only for easier debugging!
    $rel = 'rel_'.$xref1.'_'.$xref2.'_'.md5($url);

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
      persistent_toggle("<?php echo $toggle; ?>");
    </script>
    <?php
    return ob_get_clean();
  }

  protected function getOutputInDescriptionBox(bool $toggleableRels, string $id, string $targetClass, string $label) {
    ob_start();
    if ($toggleableRels) {
      ?>
      <label>
          <input id="<?php echo $id; ?>" type="checkbox" data-toggle="collapse" data-target=".<?php echo $targetClass; ?>">
          <?php echo I18N::translate($label); ?>
      </label>
      <?php
    }
    return new GenericViewElement(ob_get_clean(), '');
  }

  protected function getOutputAfterDescriptionBox(Individual $person, $settingsPrefix, $mainRels, $className) {
    $mode = intval($this->getPreference($settingsPrefix . 'TAB_REL_TO_DEFAULT_INDI', '1'));
    $recursion = intval($this->getPreference('RELATIONSHIP_RECURSION', self::DEFAULT_RECURSION));
    $showCa = boolval($this->getPreference($settingsPrefix . 'TAB_REL_TO_DEFAULT_INDI_SHOW_CA', '1'));

    if ($mode === 0) {
      return new GenericViewElement('', '');
    }

    //disabled - buggy wrt I18N!
    $directAjax = false; //boolval($this->getPreference($settingsPrefix.'TAB_DIRECT_AJAX', '0'));
    $toggleableRels = boolval($this->getPreference($settingsPrefix . 'TAB_TOGGLEABLE_RELS', '1'));

    //expensive - load async (and only if visible) 
    //(we have to print via ajax call because we have to indirectly read local storage to determine visibility,
    //but it's preferable for faster tab display anyway)
    //FunctionsPrintRels::printSlcasWrtDefaultIndividual($controller->record, $mode, $recursion, $showCa);

    $xref = $person->xref();

    if ($directAjax) {
      //untested, likely required further refactoring
      $url = Webtrees::MODULES_PATH . basename(__DIR__) . "/moduleAjax.php?request=mainRels&pid=" . $xref . "&mode=" . $mode . "&recursion=" . $recursion . "&showCa=" . $showCa;
    } else {
      $url = route('module', [
          'module' => $this->name(),
          'action' => 'MainRels',
          'tree' => $person->tree()->name(), //always set the tree (2.x doesn't have default tree via Session class)!
          'pid' => $xref,
          'mode' => $mode,
          'recursion' => $recursion,
          'showCa' => $showCa
      ]);
    }

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

      $date = DirectFamily::getFamilyEstablishedNoLaterThan2($family);
      if ($date->isOK()) {
        $beforeJD = $date->minimumJulianDay();
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

    //disabled - buggy wrt I18N!
    $directAjax = false; //boolval($this->getPreference($settingsPrefix.'TAB_DIRECT_AJAX', '0'));
    $toggleableRels = boolval($this->getPreference('TAB_TOGGLEABLE_RELS', '1'));

    //expensive - load async (and only if visible) 
    //(we have to print via ajax call because we have to indirectly read local storage to determine visibility,
    //but it's preferable for faster tab display anyway)
    //FunctionsPrintRels::printSlcas($moduleName, $family, $access_level, $mode, $recursion, $showCa);
    //TODO: where is the $access_level checked now?

    $xref = $family->xref();

    if ($directAjax) {
      //untested, likely required further refactoring
      $url = Webtrees::MODULES_PATH . basename(__DIR__) . "/moduleAjax.php?request=famRels&pid=" . $xref . "&mode=" . $mode . "&recursion=" . $recursion . "&showCa=" . $showCa;
      if ($beforeJD) {
        $url .= "&beforeJD=" . $beforeJD;
      }
    } else {
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
    }

    //must disambiguate with $beforeJD - may show up multiple times!
    //(and technically with everything else that goes into the url)
    //hash alone would be sufficient, explicit xrefs here only for easier debugging!
    $rel = 'famRels_'.$xref.'_'.md5($url);
    
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

  //Chart

  public function defaultAccessLevel(): int {
    return Auth::PRIV_PRIVATE;
  }

  public function chartMenu(Individual $individual): Menu {

    $tree = $individual->tree();
    $gedcomid = $tree->getUserPreference(Auth::user(), 'gedcomid');

    if ($gedcomid !== '') {
      return new Menu(
              $this->getChartTitle(I18N::translate('Relationship to me')),
              route('module', [
                  'module' => $this->name(),
                  'action' => 'Chart',
                  'xref' => $gedcomid, //do not use 'xref1' for 'Chart'
                  'xref2' => $individual->xref(),
                  'tree' => $tree->name(),
              ]),
              'menu-chart-relationship',
              ['rel' => 'nofollow']
      );
    }

    return new Menu(
            $this->getChartTitle(I18N::translate('Relationships')),
            route('module', [
                'module' => $this->name(),
                'action' => 'Chart',
                'xref' => $individual->xref(), //do not use 'xref1' for 'Chart'
                'tree' => $tree->name(),
            ]),
            'menu-chart-relationship',
            ['rel' => 'nofollow']
    );
  }

  public function chartBoxMenu(Individual $individual): ?Menu {
    return $this->chartMenu($individual);
  }

  /**
   * Return a link to this chart, if it is a relationship chart.
   *
   * @return string|null
   */
  public function getLinkForRelationship(Individual $individual1, Individual $individual2) {
    return route('module', [
        'module' => $this->name(),
        'action' => 'Chart',
        'xref' => $individual1->xref(), //do not use 'xref1' for 'Chart'
        'xref2' => $individual2->xref(),
        'tree' => $individual1->tree()->name(),
    ]);
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

  public function postChartAction(ServerRequestInterface $request): ResponseInterface {
    //TODO use helper for this!
    
    // Convert POST requests into GET requests for pretty URLs.
    $keys = array('tree','xref','xref2','recursion','find');
    $parameters = array_filter($request->getParsedBody(), static function (string $key) use ($keys): bool {
      return in_array($key, $keys);
    }, ARRAY_FILTER_USE_KEY);

    return redirect(route('module', [
        'module'      => $this->name(),
        'action'      => 'Chart']+$parameters));
  }
  
  //important to use 'Chart' here - we cannot adjust the link generated via parent.chartUrl
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

    if ($inverse) {
      $restricted = (boolean) $this->getPreference('TAB_REL_TO_ASSO_RESTRICTED', '0');
      if ($restricted) {
        $parent = $event->record();
        if ($parent instanceof Family) {
          $restrictedTo = preg_split("/[, ;:]+/", $this->getPreference('TAB_REL_TO_ASSO_RESTRICTED_FAM', 'MARR'), -1, PREG_SPLIT_NO_EMPTY);
          if (!in_array($event->getTag(), $restrictedTo, true)) {
            return null;
          }
        } else {
          $restrictedTo = preg_split("/[, ;:]+/", $this->getPreference('TAB_REL_TO_ASSO_RESTRICTED_INDI', 'CHR,BAPM'), -1, PREG_SPLIT_NO_EMPTY);
          if (!in_array($event->getTag(), $restrictedTo, true)) {
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
    if ('MARR' === $event->getTag()) {
      $offset = 0;
    }

    $mode = (int) $this->getPreference('TAB_REL_TO_ASSO', '15');
    if ($mode == 0) {
      return null; //do not display, do not fallback
    }
    $beforeJD = null;
    if ($mode > 9) {
      $beforeJD = $event->date()->minimumJulianDay() + $offset;
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
    //cannot use longer prefix, DB column is restricted to 32 chars wtf
    return $this->getOutputAfterDescriptionBox($person, 'F', 'mainRelsFactstab', 'toggleableRelsFactstab');
  }

  public function hRelativesTabGetOutputAfterDBox(Individual $person) {
    return $this->getOutputAfterDescriptionBox($person, '', 'mainRels', 'toggleableRels');
  }
}

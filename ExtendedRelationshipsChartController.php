<?php
declare(strict_types=1);

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\WebtreesExt\Functions\FunctionsPrintExtHelpLink;
use Cissee\WebtreesExt\Modules\RelationshipPath;
use Cissee\WebtreesExt\Modules\RelationshipUtils;
use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Functions\Functions;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Module\RelationshipsChartModule;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\RelationshipService;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Webtrees;
use Psr\Http\Message\ResponseInterface;
use function app;
use function asset;
use function response;
use function route;
use function view;

class ExtendedRelationshipsChartController {

    protected $module;

    public function __construct(ModuleInterface $module) {
        $this->module = $module;
    }

    public function chart(
        Individual $individual1,
        Individual $individual2,
        int $recursion,
        int $ancestors,
        $beforeJD): ResponseInterface {

        $tree = $individual1->tree();
        $find = $ancestors;
        $showCa = boolval($this->module->getPreference('CHART_SHOW_CAS', '1'));

        //$max_recursion  = (int) $tree->getPreference('RELATIONSHIP_RECURSION', RelationshipsChartModule::DEFAULT_RECURSION);
        $max_recursion = intval($this->module->getPreference('RELATIONSHIP_RECURSION', RelationshipsChartModule::DEFAULT_RECURSION));

        $recursion = min($recursion, $max_recursion);

        $controller = new ExtendedRelationshipController;
        $caAndPaths = $controller->calculateCaAndPaths_123456($individual1, $individual2, $find, $recursion, $beforeJD);

        ob_start();

        if ($find == 3) {
            //$cor = $controller->getCorFromPaths($paths);
            $corPlus = $controller->getCorFromCaAndPaths($individual1->tree(), $caAndPaths);
            $cor = $corPlus->getCor();
            echo '<h3>', I18N::translate('Uncorrected CoR (Coefficient of Relationship): %s', I18n::percentage($cor, 2));
            echo FunctionsPrintExtHelpLink::helpLink($this->module->name(), 'Uncorrected CoR');
            echo I18N::translate('(Number of relationships: %s)', count($caAndPaths)), '</h3>';
            if (count($caAndPaths) > 1) {
                $er = $corPlus->getEquivalentRelationships();
                if ($er === null) {
                    echo I18N::translate('(that\'s overall not significantly closer than the closest relationship via common ancestors)');
                } else {

                    //we could do this better - nevermind for now
                    $relationshipPath = RelationshipPath::createVirtual($individual1->sex(), $er);
                    $rel = RelationshipUtils::getRelationshipName($relationshipPath);

                    if ($corPlus->getActuallyBetterThan() === 0) {
                        echo I18N::translate('(that\'s overall as close as: %1$s)', $rel);
                    } else if ($corPlus->getActuallyBetterThan() < 0) {
                        echo I18N::translate('(that\'s overall almost as close as: %1$s)', $rel);
                    } else {
                        echo I18N::translate('(that\'s overall closer than: %1$s)', $rel);
                    }
                }
            }
        }

        if (I18N::direction() === 'ltr') {
            $diagonal1 = asset('css/images/dline.png');
            $diagonal2 = asset('css/images/dline2.png');
        } else {
            $diagonal1 = asset('css/images/dline2.png');
            $diagonal2 = asset('css/images/dline.png');
        }

        $num_paths = 0;
        //foreach ($paths as $path) {

        foreach ($caAndPaths as $caAndPath) {
            $path = $caAndPath->getPath();

            // Extract the relationship names between pairs of individuals
            $relationships = $this->oldStyleRelationshipPath($tree, $path);
            if (empty($relationships)) {
                // Cannot see one of the families/individuals, due to privacy;
                continue;
            }

            $relationshipPath = RelationshipPath::create($tree, $path);
            $rel = RelationshipUtils::getRelationshipName($relationshipPath);

            echo '<h3>', MoreI18N::xlate('Relationship: %s', $rel), '</h3>';

            $debugWebtreesRel = boolval($this->module->getPreference('CHART_SHOW_LEGACY', '1'));
            if ($debugWebtreesRel) {
                if (str_starts_with(Webtrees::VERSION, '2.1')) {
                    $webtreesRel = app(RelationshipService::class)->legacyNameAlgorithm(implode('', $relationships), $individual1, $individual2);
                } else {
                    $webtreesRel = Functions::getRelationshipNameFromPath(implode('', $relationships), $individual1, $individual2);
                }

                if ($rel !== $webtreesRel) {
                    echo '<h4>', '(', I18N::translate('via legacy algorithm: %s', $webtreesRel), ')';
                    echo FunctionsPrintExtHelpLink::helpLink($this->module->name(), 'Legacy Algorithm');
                    echo '</h4>';
                }
            }

            $num_paths++;

            //[RC] added
            //add common ancestors (if configured and not already included)
            $slcaKey = $caAndPath->getCommonAncestor();
            $fam = null;
            if (($slcaKey !== null) && ($showCa)) {
                $record = Registry::gedcomRecordFactory()->make($slcaKey, $tree);
                $caIsIndi = $record instanceof Individual;

                if ($caIsIndi) {
                    //skip - slca is already in the path!
                } else {
                    $fam = $record;
                }
            }

            // Use a table/grid for layout.
            $table = [];
            // Current position in the grid.
            $x = 0;
            $y = 0;
            // Extent of the grid.
            $min_y = 0;
            $max_y = 0;
            $max_x = 0;
            // For each node in the path.
            foreach ($path as $n => $xref) {
                if ($n % 2 === 1) {
                    $relName = RelationshipUtils::getRelationshipName(
                            $relationshipPath->sliceBefore(intdiv($n, 2), 1));

                    switch ($relationships[$n]) {
                        case 'hus':
                        case 'wif':
                        case 'spo':
                        case 'bro':
                        case 'sis':
                        case 'sib':
                            //[RC] adjusted
                            //only draw this in certain cases!
                            if ((!$fam) || (count($fam->spouses()) === 0)) {
                                $table[$x + 1][$y] = '<div style="background:url(' . e(asset('css/images/hline.png')) . ') repeat-x center;  width: 94px; text-align: center"><div class="hline-text" style="height: 32px;">' . $relName . '</div><div style="height: 32px;">' . view('icons/arrow-right') . '</div></div>';
                            } else {
                                //keep the relationship for later
                                $skippedRelationship = $relationships[$n];
                            }
                            $x += 2;
                            break;
                        case 'son':
                        case 'dau':
                        case 'chi':
                            if ($n > 2 && preg_match('/fat|mot|par/', $relationships[$n - 2])) {
                                $table[$x + 1][$y - 1] = '<div style="background:url(' . $diagonal2 . '); width: 64px; height: 64px; text-align: center;"><div style="height: 32px; text-align: end;">' . $relName . '</div><div style="height: 32px; text-align: start;">' . view('icons/arrow-down') . '</div></div>';
                                $x += 2;
                            } else {
                                $table[$x][$y - 1] = '<div style="background:url(' . e('"' . asset('css/images/vline.png') . '"') . ') repeat-y center; height: 64px; text-align: center;"><div class="vline-text" style="display: inline-block; width:50%; line-height: 64px;">' . $relName . '</div><div style="display: inline-block; width:50%; line-height: 64px;">' . view('icons/arrow-down') . '</div></div>';
                            }
                            $y -= 2;
                            break;
                        case 'fat':
                        case 'mot':
                        case 'par':
                            if ($n > 2 && preg_match('/son|dau|chi/', $relationships[$n - 2])) {
                                $table[$x + 1][$y + 1] = '<div style="background:url(' . $diagonal1 . '); background-position: top right; width: 64px; height: 64px; text-align: center;"><div style="height: 32px; text-align: start;">' . $relName . '</div><div style="height: 32px; text-align: end;">' . view('icons/arrow-down') . '</div></div>';
                                $x += 2;
                            } else {
                                $table[$x][$y + 1] = '<div style="background:url(' . e('"' . asset('css/images/vline.png') . '"') . ') repeat-y center; height: 64px; text-align:center; "><div class="vline-text" style="display: inline-block; width: 50%; line-height: 64px;">' . $relName . '</div><div style="display: inline-block; width: 50%; line-height: 32px">' . view('icons/arrow-up') . '</div></div>';
                            }
                            $y += 2;
                            break;
                    }
                    $max_x = max($max_x, $x);
                    $min_y = min($min_y, $y);
                    $max_y = max($max_y, $y);
                } else {
                    $individual = Registry::individualFactory()->make($xref, $tree);
                    $table[$x][$y] = view('chart-box', ['individual' => $individual]);
                }
            }

            //[RC] added TODO: layout properly!
            if ($fam) {
                $size = count($fam->spouses());

                if ($size > 0) { //there may be families with siblings only (we still have a ca in that case)
                    $x = 0;
                    $y = $max_y + count($fam->spouses()) + 1;
                    foreach ($fam->spouses() as $indi) {
                        $individual = Registry::individualFactory()->make($indi->xref(), $tree);
                        $table[$x][$y] = view('chart-box', ['individual' => $individual]);
                        //$x += 2;
                        $y -= 1;
                    }

                    //draw the extra lines
                    $relUp = I18N::translate('parents');
                    if ($size == 1) {
                        //single parent (spouse unknown)
                        switch ($individual->sex()) {
                            case 'M':
                                $relUp = MoreI18N::xlate('father');
                                break;
                            case 'F':
                                $relUp = MoreI18N::xlate('mother');
                                break;
                            default:
                                $relUp = MoreI18N::xlate('parent');
                        }
                    }

                    switch ($skippedRelationship) {
                        case 'bro':
                            $relDn = MoreI18N::xlate('son');
                            break;
                        case 'sis':
                            $relDn = MoreI18N::xlate('daughter');
                            break;
                        default:
                            $relDn = MoreI18N::xlate('child');
                    }

                    $table[0][$max_y + 1] = '<div style="background:url(' . asset('css/images/vline.png') . ') repeat-y center; height: 64px; text-align:center; "><div class="vline-text" style="display: inline-block; width:50%; line-height: 64px;">' . $relUp . '</div><div style="display: inline-block; width: 50%; line-height: 32px">' . view('icons/arrow-up') . '</div></div>';

                    $table[1][$max_y + 1] = '<div style="background:url(' . $diagonal2 . '); width: 64px; height: 64px; text-align: center;"><div class="vline-text" style="display: inline-block; width:50%; line-height: 64px;">' . $relDn . '</div><div style="height: 32px; text-align: end;">' . view('icons/arrow-down') . '</div></div>';

                    $max_x = max($max_x, $x); //shouldn't actually make any difference 
                    $max_y += count($fam->spouses()) + 1;
                }
            }
            //[RC] added end

            echo '<div class="wt-chart wt-relationship-chart">';
            echo '<table style="border-collapse: collapse; margin: 20px 50px;">';
            for ($y = $max_y; $y >= $min_y; --$y) {
                echo '<tr>';
                for ($x = 0; $x <= $max_x; ++$x) {
                    echo '<td style="padding: 0;">';
                    if (isset($table[$x][$y])) {
                        echo $table[$x][$y];
                    }
                    echo '</td>';
                }
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
        }

        if (!$num_paths) {
            echo '<p>', MoreI18N::xlate('No link between the two individuals could be found.');

            //[RC] extended
            if ($beforeJD !== null) {
                if (Auth::isManager($tree)) {
                    $url = route('module', [
                        'module' => $this->module->name(),
                        'action' => 'AdminSync'
                    ]);
                    echo ' ';
                    echo I18N::translate('If this is unexpected, and there are recent changes, you may have to follow this link: ');
                    ?>
                    <a href="<?php echo $url ?>">
                        <?php
                        echo I18N::translate('Synchronize trees to obtain dated relationship links');
                        echo '.';
                        ?>					
                    </a>
                    <?php
                }
            }
            echo '</p>';
        }

        $html = ob_get_clean();

        return response($html);
    }

    /**
     * Convert a path (list of XREFs) to an "old-style" string of relationships.
     *
     * Return an empty array, if privacy rules prevent us viewing any node.
     *
     * @param Tree     $tree
     * @param string[] $path Alternately Individual / Family
     *
     * @return string[]
     */
    //TODO refactor to align with ExtendedRelationshipController.oldStyleRelationshipPath
    private function oldStyleRelationshipPath(Tree $tree, array $path): array {
        $spouse_codes = [
            'M' => 'hus',
            'F' => 'wif',
            'U' => 'spo',
        ];
        $parent_codes = [
            'M' => 'fat',
            'F' => 'mot',
            'U' => 'par',
        ];
        $child_codes = [
            'M' => 'son',
            'F' => 'dau',
            'U' => 'chi',
        ];
        $sibling_codes = [
            'M' => 'bro',
            'F' => 'sis',
            'U' => 'sib',
        ];
        $relationships = [];

        for ($i = 1, $count = count($path); $i < $count; $i += 2) {
            $family = Registry::familyFactory()->make($path[$i], $tree);
            $prev = Registry::individualFactory()->make($path[$i - 1], $tree);
            $next = Registry::individualFactory()->make($path[$i + 1], $tree);
            if (preg_match('/\n\d (HUSB|WIFE|CHIL) @' . $prev->xref() . '@/', $family->gedcom(), $match)) {
                $rel1 = $match[1];
            } else {
                return [];
            }
            if (preg_match('/\n\d (HUSB|WIFE|CHIL) @' . $next->xref() . '@/', $family->gedcom(), $match)) {
                $rel2 = $match[1];
            } else {
                return [];
            }
            if (($rel1 === 'HUSB' || $rel1 === 'WIFE') && ($rel2 === 'HUSB' || $rel2 === 'WIFE')) {
                $relationships[$i] = $spouse_codes[$next->sex()];
            } elseif (($rel1 === 'HUSB' || $rel1 === 'WIFE') && $rel2 === 'CHIL') {
                $relationships[$i] = $child_codes[$next->sex()];
            } elseif ($rel1 === 'CHIL' && ($rel2 === 'HUSB' || $rel2 === 'WIFE')) {
                $relationships[$i] = $parent_codes[$next->sex()];
            } elseif ($rel1 === 'CHIL' && $rel2 === 'CHIL') {
                $relationships[$i] = $sibling_codes[$next->sex()];
            }
        }

        return $relationships;
    }

}

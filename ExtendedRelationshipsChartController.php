<?php
declare(strict_types=1);

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\WebtreesExt\Functions\FunctionsPrintExtHelpLink;
use Cissee\WebtreesExt\Modules\RelationshipPath;
use Cissee\WebtreesExt\Modules\RelationshipUtils;
use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Module\RelationshipsChartModule;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\RelationshipService;
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

            $relationshipPath = RelationshipPath::create($tree, $path);
            if ($relationshipPath === null) {
                // Cannot see one of the families/individuals, due to privacy;
                continue;
            }
            $rel = RelationshipUtils::getRelationshipName($relationshipPath);

            // Extract the relationship names between pairs of individuals
            $relationships = $relationshipPath->oldStylePath();

            echo '<h3>', MoreI18N::xlate('Relationship: %s', $rel), '</h3>';

            $debugWebtreesRel = boolval($this->module->getPreference('CHART_SHOW_LEGACY', '1'));
            if ($debugWebtreesRel) {
                $webtreesRel = app(RelationshipService::class)->legacyNameAlgorithm($relationships, $individual1, $individual2);

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
                    $relPos = intdiv($n, 2);
                    $relName = RelationshipUtils::getRelationshipName(
                            $relationshipPath->sliceBefore($relPos, 1));

                    switch ($relationshipPath->getRel($relPos)) {
                        case 'hus':
                        case 'wif':
                        case 'spo':
                        case 'bro':
                        case 'sis':
                        case 'sib':
                            //[RC] adjusted
                            //only draw this in certain cases!
                            if (($fam === null) || (count($fam->spouses()) === 0)) {
                                $table[$x + 1][$y] = '<div style="background:url(' . e(asset('css/images/hline.png')) . ') repeat-x center;  width: 94px; text-align: center"><div class="hline-text" style="height: 32px;">' . $relName . '</div><div style="height: 32px;">' . view('icons/arrow-right') . '</div></div>';
                            } else {
                                //keep the relationship for later
                                $skippedRelationship = $relationshipPath->getRel($relPos);
                            }
                            $x += 2;
                            break;
                        case 'son':
                        case 'dau':
                        case 'chi':
                            if ($n > 2 && preg_match('/fat|mot|par/', $relationshipPath->getRel($relPos-1))) {
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
                            if ($n > 2 && preg_match('/son|dau|chi/', $relationshipPath->getRel($relPos-1))) {
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
            if ($fam !== null) {
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
}

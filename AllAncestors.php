<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\Individual;

//code taken from BranchesListModule
class AllAncestors {
    
    /**
     * Find all ancestors of an individual, indexed by the Sosa-Stradonitz number.
     *
     * @param Individual $individual
     *
     * @return Individual[]
     */
    public static function allAncestors(Individual $individual): array
    {
        $ancestors = [
            1 => $individual,
        ];

        do {
            $sosa = key($ancestors);

            $family = $ancestors[$sosa]->childFamilies()->first();

            if ($family !== null) {
                if ($family->husband() !== null) {
                    $ancestors[$sosa * 2] = $family->husband();
                }
                if ($family->wife() !== null) {
                    $ancestors[$sosa * 2 + 1] = $family->wife();
                }
            }
        } while (next($ancestors));

        return $ancestors;
    }
    
    /**
     * Convert a SOSA number into a generation number. e.g. 8 = great-grandfather = 3 generations
     *
     * @param int $sosa
     *
     * @return string
     */
    public static function sosaGeneration(int $sosa): string
    {
        $generation = (int) log($sosa, 2) + 1;

        return '<sup title="' . MoreI18N::xlate('Generation') . '">' . $generation . '</sup>';
    }
}

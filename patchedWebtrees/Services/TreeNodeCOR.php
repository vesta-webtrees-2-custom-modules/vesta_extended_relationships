<?php

namespace Cissee\WebtreesExt\Services;

use Cissee\WebtreesExt\Modules\RelationshipPath;
use Cissee\WebtreesExt\Modules\RelationshipUtils;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;

class TreeNodeCOR {
    
    protected float $cor;
    protected int $numberOfRelationships;
    protected string $description;
    protected string $virtualDescription;
    
    public function cor(): float {
        return $this->cor;
    }
    
    public function numberOfRelationships(): int {
        return $this->numberOfRelationships;
    }
    
    public function description(): string {
        return $this->description;
    }
    
    public function virtualDescription(): string {
        return $this->virtualDescription;
    }
    
    public function __construct(
        float $cor,
        int $numberOfRelationships,
        string $description,
        string $virtualDescription) {
        
        $this->cor = $cor;
        $this->numberOfRelationships = $numberOfRelationships;
        $this->description = $description;
        $this->virtualDescription = $virtualDescription;        
    }
    
    public static function create(
        float $cor,
        int $numberOfRelationships,
        string $description,
        Tree $tree,
        string $sex,
        array $bestPath): TreeNodeCOR {
        
        //refactored fron CorPlus inputs (cf ExtendedRelationshipController)
        //and CorPlus evaluation (cf )
        $actuallyBetterThan = 1;
        $equivalentPathLength = -(log($cor, 2));
        $eplFraction = $equivalentPathLength - floor($equivalentPathLength);
        if ($eplFraction < 0.001) {
            $actuallyBetterThan = 0;
        } else if ($eplFraction < 0.5) {
            $actuallyBetterThan = -1;
        }
        
        $equivalentPathLength = (int) round($equivalentPathLength);
        $bestPathLength = (count($bestPath) - 1)  / 2;
        
        if ($equivalentPathLength == $bestPathLength) {
            $virtualDescription = I18N::translate('(that\'s overall not significantly closer than the closest relationship via common ancestors)');
        } else {
            //create an equivalent relationship that's similar 'in character'
            //to the relationship established via the closest ca

            $relationships = RelationshipPath::create($tree, $bestPath)->elements();
            $up = array();
            $right = array();
            $dn = array();
            foreach ($relationships as $relationship) {
                $rel = $relationship->rel();
                if (("mot" === $rel) || ("fat" === $rel)) {
                    $up[] = $rel;
                } else if (("bro" === $rel) || ("sis" === $rel)) {
                    $right[] = $rel;
                } else {
                    $dn[] = $rel;
                }
            }

            $toRemove = $bestPathLength - $equivalentPathLength;
            $upShortened = sizeof($up);
            $dnShortened = sizeof($dn);
            $diff = abs(sizeof($up) - sizeof($dn));
            if ($toRemove <= $diff) {
                //remove entirely from longer
                if (sizeof($up) > sizeof($dn)) {
                    $upShortened -= $toRemove;
                } else {
                    $dnShortened -= $toRemove;
                }
            } else {
                if ($diff === 0) {
                    $toRemoveFromBoth = floor($toRemove / 2);
                    $upShortened -= $toRemoveFromBoth;
                    $dnShortened -= $toRemoveFromBoth;
                    if ($toRemove % 2 !== 0) {
                        //we have to pick one
                        $upShortened -= 1;
                    }
                } else {
                    //remove as much as possible from longer
                    $toRemoveFromBoth = ceil(($toRemove - $diff) / 2);
                    $toRemoveFromLonger = $toRemove - $toRemoveFromBoth * 2;

                    $upShortened -= $toRemoveFromBoth;
                    $dnShortened -= $toRemoveFromBoth;
                    if (sizeof($up) > sizeof($dn)) {
                        $upShortened -= $toRemoveFromLonger;
                    } else {
                        $dnShortened -= $toRemoveFromLonger;
                    }
                }
            }

            //shorten 'least significant' parts of path
            $shortenedRelationships = array();
            for ($i = 0; $i < $upShortened; $i++) {
                $shortenedRelationships[] = $up[$i + sizeof($up) - $upShortened];
            }
            //0 or 1 times
            foreach ($right as $rel) {
                $shortenedRelationships[] = $rel;
            }
            for ($i = 0; $i < $dnShortened; $i++) {
                $shortenedRelationships[] = $dn[$i + sizeof($dn) - $dnShortened];
            }

            //return new CorPlus($cor, $actuallyBetterThan, $shortenedRelationships);
            //we could do this better - nevermind for now
            $relationshipPath = RelationshipPath::createVirtual($sex, $shortenedRelationships);
            $rel = RelationshipUtils::getRelationshipName($relationshipPath);

            if ($actuallyBetterThan === 0) {
                $virtualDescription = I18N::translate('(that\'s overall as close as: %1$s)', $rel);
            } else if ($actuallyBetterThan < 0) {
                $virtualDescription = I18N::translate('(that\'s overall almost as close as: %1$s)', $rel);
            } else {
                $virtualDescription = I18N::translate('(that\'s overall closer than: %1$s)', $rel);
            }
        }
        
        return new TreeNodeCOR(        
            $cor,
            $numberOfRelationships,
            $description,
            $virtualDescription);
    }
}

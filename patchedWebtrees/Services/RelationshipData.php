<?php

namespace Cissee\WebtreesExt\Services;

use Cissee\WebtreesExt\Modules\RelationshipPath;
use Cissee\WebtreesExt\Modules\RelationshipUtils;
use Exception;
use Fisharebest\Webtrees\Tree;

class RelationshipData {

    protected string $description;
    protected string $descriptionInverse;
    protected float $cor;
    protected array $path;

    public function description(): string {
        return $this->description;
    }

    public function descriptionInverse(): string {
        return $this->descriptionInverse;
    }

    public function cor(): float {
        return $this->cor;
    }

    public function path(): array {
        return $this->path;
    }

    public function __construct(
        string $description,
        string $descriptionInverse,
        float $cor,
        array $path) {

        $this->description = $description;
        $this->descriptionInverse = $descriptionInverse;
        $this->cor = $cor;
        $this->path = $path;
    }

    public static function create(
        Tree $tree,
        array $path,
        ?int $beforeJD = null): RelationshipData {

        //if ca is INDI, we have to add a single path
        //
        //if ca is FAM, we actually have to add two paths (one per family spouse - never mind whether each spouse is known or unknown) of length $pathSegments+2
        //i.e. the formula is
        //$cor = 2*pow(2,-($pathSegments+2)/2);
        //which is the same as
        //$cor = pow(2,-$pathSegments/2);
        //so we don't actually have to distinguish the two cases here!
        //
        //in each case,
        //divide by 2 to collapse all 'INDI - FAM - INDI' segments to 'INDI - INDI' segments

        $pathSegments = count($path) - 1;
        $cor = pow(2, -$pathSegments / 2);

        $relationshipPath = RelationshipPath::create($tree, $path, $beforeJD);
        if ($relationshipPath === null) {
            throw new Exception("unexpected null path");
        }
        $description = RelationshipUtils::getRelationshipName($relationshipPath);

        $relationshipPathInverse = RelationshipPath::create($tree, array_reverse($path), $beforeJD);
        if ($relationshipPathInverse === null) {
            throw new Exception("unexpected null path");
        }
        $descriptionInverse = RelationshipUtils::getRelationshipName($relationshipPathInverse);

        return new RelationshipData(
            $description,
            $descriptionInverse,
            $cor,
            $path);
    }
}

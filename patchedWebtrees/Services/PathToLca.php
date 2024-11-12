<?php

namespace Cissee\WebtreesExt\Services;

use Illuminate\Support\Collection;

class PathToLca {

    protected TreeNode $lcaNode;
    protected Collection $vias;

    public function lcaNode(): TreeNode {
        return $this->lcaNode;
    }
    /**
     *
     * @return string xref
     */
    public function lca(): string {
        return $this->lcaNode()->record()->xref();
    }

    /**
     *
     * @param Collection<TreeNode> $vias
     */
    public function __construct(
        Collection $vias) {

        $this->lcaNode = $vias->last();

        //ensure immutability
        $this->vias = clone $vias;
    }

    public function otherRefs(): Collection {
        $lca = $this->lca();
        return $this->vias->map(static function (TreeNode $node) use ($lca): string|null {
            $xref = $node->record()->xref();
            return ($xref === $lca)?null:$xref;
        })->filter();
    }

    public function getRelationshipDataIfValid(
        PathToLca $other): RelationshipData|null {

        if ($this->lca() !== $other->lca()) {
            return null;
        }

        if ($this->otherRefs()->intersect($other->otherRefs())->isNotEmpty()) {
            return null;
        }

        //build the relationship paths, alternating indi/fam
        $tree = $this->lcaNode()->record()->tree();
        $path = [];

        foreach ($this->vias as $node) {
            $path []= $node->record()->xref(); //INDI/FAM alternated
        }

        foreach ($other->vias->reverse() as $node) {

            if ($other->lcaNode() !== $node) {
                $path []= $node->record()->xref(); //INDI/FAM alternated
            }
        }

        return RelationshipData::create(
            $tree,
            $path);
    }
}

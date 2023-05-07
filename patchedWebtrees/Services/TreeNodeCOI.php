<?php

namespace Cissee\WebtreesExt\Services;

class TreeNodeCOI {
    
    protected float $coi;
    
    public function coi(): float {
        return $this->coi;
    }
    
    public function __construct(
        float $coi) {
        
        $this->coi = $coi;
    }
}

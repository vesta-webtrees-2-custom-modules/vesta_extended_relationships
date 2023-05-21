<?php

namespace Cissee\WebtreesExt\Services;

class RelationshipData {
    
    protected string $description;
    protected string $descriptionInverse;
    protected float $cor;
      
    public function description(): string {
        return $this->description;
    }
    
    public function descriptionInverse(): string {
        return $this->descriptionInverse;
    }
    
    public function cor(): float {
        return $this->cor;
    }

    public function __construct(
        string $description, 
        string $descriptionInverse, 
        float $cor) {
        
        $this->description = $description;
        $this->descriptionInverse = $descriptionInverse;
        $this->cor = $cor;
    }
}

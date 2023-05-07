<?php

namespace Cissee\WebtreesExt\Services;

class TreeNodeMarkup {
    
    protected TreeNodeMarkupType $type;
    protected string $label;
        
    public function type(): TreeNodeMarkupType {
        return $this->type;
    }
    
    public function label(): string {
        return $this->label;
    }
    
    public function __construct(
        TreeNodeMarkupType $type,
        string $label) {
        
        $this->type = $type;
        $this->label = $label;
    }
}

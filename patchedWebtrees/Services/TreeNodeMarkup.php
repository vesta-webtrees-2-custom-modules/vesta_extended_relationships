<?php

namespace Cissee\WebtreesExt\Services;

class TreeNodeMarkup {
    
    protected TreeNodeMarkupType $type;
    protected string $label;
    protected string|null $tooltip;
        
    public function type(): TreeNodeMarkupType {
        return $this->type;
    }
    
    public function label(): string {
        return $this->label;
    }
    
    public function tooltip(): string|null {
        return $this->tooltip;
    }
    
    public function __construct(
        TreeNodeMarkupType $type,
        string $label,
        string|null $tooltip = null) {
        
        $this->type = $type;
        $this->label = $label;
        $this->tooltip = $tooltip;
    }
}

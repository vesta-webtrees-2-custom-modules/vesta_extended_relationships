<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

class SurnamesWithPatriarchs {
  
    protected $array;
    protected $helpLink;
  
    public function getArray() {
        return $this->array;
    }
  
    public function getHelpLink() {
        return $this->helpLink;
    }
  
    public function __construct(
        $array,
        string $helpLink) {
    
        $this->array = $array;
        $this->helpLink = $helpLink;
    }
}

<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

class SurnamesWithPatriarchs {
  
  protected $array;
  
  public function getArray() {
    return $this->array;
  }
  
  public function __construct($array) {
    $this->array = $array;
  }
}

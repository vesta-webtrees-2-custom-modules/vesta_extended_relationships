<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

class IndividualsWithPatriarchs {
  
  protected $originalArray;
  protected $patriarchs;
  
  public function getOriginalArray() {
    return $this->originalArray;
  }

  public function getPatriarchs() {
    return $this->patriarchs;
  }
  
  public function __construct($originalArray, $patriarchs) {
    $this->originalArray = $originalArray;
    $this->patriarchs = $patriarchs;
  }
}

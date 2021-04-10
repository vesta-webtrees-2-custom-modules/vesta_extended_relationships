<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Fisharebest\Webtrees\Individual;

class SimpleRelPathFrom implements RelPathFrom {
    
  protected $sex;
  
  public function __construct(
          string $sex) {
    
    $this->sex = $sex;
  }
  
  public function matchFrom(
          string $sex,
          ?Individual $from): bool {
    
    //TODO expand this
    switch ($this->sex) {
      case "M":
      case "F":
      case "U":
        if ($sex !== $this->sex) {
          return false;
        }
      default:
        return true;
    }
  }
}

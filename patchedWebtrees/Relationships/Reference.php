<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

class Reference {
  
  protected $ref;
  protected $value;
  
  public function ref(): Times {
    return $this->ref;
  }
  
  public function value(): int {
    return $this->value;
  }
  
  public function __construct(
          Times $ref,
          int $value) {
    
    $this->ref = $ref;
    $this->value = $value;
  }
}

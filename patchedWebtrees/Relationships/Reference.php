<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

class Reference {
  
  protected $ref;
  protected $value;
  protected $resolver;
  
  public function ref(): Times {
    return $this->ref;
  }
  
  public function value(): int {
    return $this->value;
  }
  
  public function resolve(): string {
    $finalValue = ($this->value + $this->ref->offset());
    
    $resolver = $this->ref->resolver();
    if ($resolver === null) {
      return '' . $finalValue;
    }
    
    return $resolver->resolve($finalValue);
  }
  
  public function __construct(
          Times $ref,
          int $value) {
    
    $this->ref = $ref;
    $this->value = $value;
  }
}

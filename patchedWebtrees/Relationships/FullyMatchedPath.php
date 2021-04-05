<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

class FullyMatchedPath {
  
  protected $nominative;
  protected $genitive;
  
  public function nominative(): string {
    return $this->nominative;
  }
  
  public function genitive(): string {
    return $this->genitive;
  }
  public function __construct(
          string $nominative,
          ?string $genitive) {
   
    $this->nominative = $nominative;
    $this->genitive = $genitive;
  }
}

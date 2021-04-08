<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

class FullyMatchedPath {
  
  protected $nominative;
  protected $genitive;
  protected $numberOfSplits;
  
  public function nominative(): string {
    return $this->nominative;
  }
  
  public function genitive(): string {
    return $this->genitive;
  }
  
  public function numberOfSplits(): int {
    return $this->numberOfSplits;
  }
  
  public function __construct(
          string $nominative,
          ?string $genitive,
          int $numberOfSplits = 1) {
   
    $this->nominative = $nominative;
    $this->genitive = $genitive;
    $this->numberOfSplits = $numberOfSplits;
  }
}

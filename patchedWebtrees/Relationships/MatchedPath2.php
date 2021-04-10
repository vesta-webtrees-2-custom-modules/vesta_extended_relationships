<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Cissee\WebtreesExt\Modules\RelationshipPath;

class MatchedPath2 {
  
  protected $matchedPathElements; 
  protected $remainingPath;  
  protected $nominative;
  protected $genitive;
  
  public function matchedPathElements(): int {
    return $this->matchedPathElements;
  }
  
  public function remainingPath(): ?RelationshipPath {
    return $this->remainingPath;
  }
  
  public function nominative(): string {
    return $this->nominative;
  }
  
  public function genitive(): string {
    return $this->genitive;
  }
  public function __construct(
          int $matchedPathElements,
          ?RelationshipPath $remainingPath,
          string $nominative,
          ?string $genitive) {
   
    $this->matchedPathElements = $matchedPathElements;
    $this->remainingPath = $remainingPath;
    $this->nominative = $nominative;
    $this->genitive = $genitive;
  }
}

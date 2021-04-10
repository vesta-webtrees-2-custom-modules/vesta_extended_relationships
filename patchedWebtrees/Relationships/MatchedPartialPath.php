<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Cissee\WebtreesExt\Modules\RelationshipPath;

class MatchedPartialPath {
  
  protected $matchedPathElements; 
  protected $remainingPath;  
  protected $refs;
  
  public function matchedPathElements(): int {
    return $this->matchedPathElements;
  }
  
  public function remainingPath(): RelationshipPath {
    return $this->remainingPath;
  }
  
  public function refs(): array {
    return $this->refs;
  }
  
  public function __construct(
          int $matchedPathElements,
          RelationshipPath $remainingPath,
          array $refs) {
    
    $this->matchedPathElements = $matchedPathElements;
    $this->remainingPath = $remainingPath;
    $this->refs = $refs;
  }
}

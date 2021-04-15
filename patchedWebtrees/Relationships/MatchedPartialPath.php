<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Cissee\WebtreesExt\Modules\RelationshipPath;

class MatchedPartialPath {
  
  protected $matchedPathElements; 
  protected $dependsOnRemainingPath;
  protected $remainingPath;  
  protected $refs;
  
  public function matchedPathElements(): int {
    return $this->matchedPathElements;
  }
  
  /**
   * 
   * @return bool true e.g. if matcher has evaluated the total path length
   */
  public function dependsOnRemainingPath(): bool {
    return $this->dependsOnRemainingPath;
  }
  
  public function remainingPath(): RelationshipPath {
    return $this->remainingPath;
  }
  
  public function refs(): array {
    return $this->refs;
  }
  
  public function __construct(
          int $matchedPathElements,
          bool $dependsOnRemainingPath,
          RelationshipPath $remainingPath,
          array $refs) {
    
    $this->matchedPathElements = $matchedPathElements;
    $this->dependsOnRemainingPath = $dependsOnRemainingPath;
    $this->remainingPath = $remainingPath;
    $this->refs = $refs;
  }
}

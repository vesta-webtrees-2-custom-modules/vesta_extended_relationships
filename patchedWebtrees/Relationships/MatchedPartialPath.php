<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

class MatchedPartialPath {
  
  protected $matchedPath; 
  protected $remainingSex;
  protected $remainingPath;  
  protected $refs;
  
  public function matchedPath(): string {
    return $this->matchedPath;
  }
  
  public function remainingSex(): string {
    return $this->remainingSex;
  }
  
  public function remainingPath(): string {
    return $this->remainingPath;
  }
  
  public function refs(): array {
    return $this->refs;
  }
  
  public function __construct(
          string $matchedPath,
          string $remainingSex,
          string $remainingPath,
          array $refs) {
    
    $this->matchedPath = $matchedPath;
    $this->remainingSex = $remainingSex;
    $this->remainingPath = $remainingPath;
    $this->refs = $refs;
  }
}

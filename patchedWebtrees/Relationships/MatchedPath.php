<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

class MatchedPath {
  
  protected $matchedPath; 
  protected $remainingSex;
  protected $remainingPath;  
  protected $nominative;
  protected $genitive;
  
  public function matchedPath(): string {
    return $this->matchedPath;
  }
  
  public function remainingSex(): string {
    return $this->remainingSex;
  }
  
  public function remainingPath(): string {
    return $this->remainingPath;
  }
  
  public function nominative(): string {
    return $this->nominative;
  }
  
  public function genitive(): string {
    return $this->genitive;
  }
  public function __construct(
          string $matchedPath,
          string $remainingSex,
          string $remainingPath,
          string $nominative,
          ?string $genitive) {
   
    $this->matchedPath = $matchedPath;
    $this->remainingSex = $remainingSex;
    $this->remainingPath = $remainingPath;
    $this->nominative = $nominative;
    $this->genitive = $genitive;
  }
}

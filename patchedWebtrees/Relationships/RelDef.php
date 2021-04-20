<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Cissee\WebtreesExt\Modules\RelationshipPath;
use Illuminate\Support\Collection;

class RelDef {
    
  protected $elements;
  protected $nominative;
  protected $genitive;
  
  protected $minTimes;
  protected $maxTimes;

  public function minTimes(): int {
    return $this->minTimes;
  }  
   
  /**
   * 
   * @return int -1 indicates unlimited
   */
  public function maxTimes(): int {
    return $this->maxTimes;
  }
  
  /**
   * 
   * @param Collection<RelationshipPathMatcher> $elements
   * @param string $nominative
   */
  public function __construct(
          Collection $elements,
          string $nominative,
          ?string $genitive) {
    
    $this->elements = $elements;
    $this->nominative = $nominative;
    $this->genitive = $genitive;
    
    $this->minTimes = $this->elements
              ->map(static function (RelationshipPathMatcher $element): int {
                  return $element->minTimes();
              })
              ->reduce(static function (int $carry, int $item): int {
                  return $carry + $item;
              }, 0);
              
    $this->maxTimes = $this->elements
              ->map(static function (RelationshipPathMatcher $element): int {
                  return $element->maxTimes();
              })
              ->reduce(static function (int $carry, int $item): int {
                  return ($item === -1)?-1:($carry === -1)?-1:($carry + $item);
              }, 0);
  }
  
  /**
   * 
   * @param RelationshipPath $path
   * @return Collection<MatchedPath>
   */
  public function matchPath(
          RelationshipPath $path): ?Collection {
        
    //error_log("path: ".$path->size());
    //error_log("min: ".$this->minTimes);
    //error_log("max: ".$this->maxTimes);
    
    if ($this->minTimes > $path->size()) {      
      return null;
    }
    
    //no - we want to match partially!
    //otherwise (e.g. if this is included for performance reasons,
    //we must not cache partial paths obtained elsewhere,
    //because this might mess up the order of RelDefs:
    //fixed length-based is skipped (but expected to have precedence), dynamic-length based is kept)
    /*
    if (($this->maxTimes !== -1) && ($this->maxTimes < $path->size())) {
      return null;
    }
    */
    
    $currentMatchedPaths = [];
    $currentMatchedPaths []= new MatchedPartialPath(0, false, $path, []);
    
    foreach ($this->elements as $element) {
      /** @var RelationshipPathMatcher $element */
      
      $nextMatchedPaths = [];
      foreach ($currentMatchedPaths as $currentMatchedPath) {
        /** @var MatchedPartialPath $currentMatchedPath */
        
        $next = $element->matchPath(
                $currentMatchedPath->matchedPathElements(),
                $currentMatchedPath->dependsOnRemainingPath(),
                $currentMatchedPath->remainingPath(), 
                $currentMatchedPath->refs())->all();
        
        $nextMatchedPaths = array_merge(
                $nextMatchedPaths,
                $next);
      }
      $currentMatchedPaths = $nextMatchedPaths;
      
      if (empty($currentMatchedPaths)) {
        //functionally not required but pointless to continue
        return null;
      } 
    }

    $ret = [];
    foreach ($currentMatchedPaths as $currentMatchedPath) {
      if ($currentMatchedPath->remainingPath()->isEmpty() || !$currentMatchedPath->dependsOnRemainingPath()) {
        //error_log("RelDef matched" . $currentMatchedPath->matchedPathElements());
        
        $ret []= new MatchedPath(
              $currentMatchedPath->matchedPathElements(), 
              $currentMatchedPath->remainingPath(), 
              $this->nominative($currentMatchedPath->refs()), 
              $this->genitive($currentMatchedPath->refs()));
      }
    }
    
    return new Collection($ret);
  }
  
  protected function nominative(array $refs): string {
    $args = [];
    foreach ($refs as $ref) {      
      $args []= $ref->resolve();
    }
    return empty($args)?$this->nominative:sprintf($this->nominative, ...$args);
  }
  
  protected function genitive(array $refs): ?string {
    
    if ($this->genitive === null) {
      return null;
    }
    
    $args = [];
    foreach ($refs as $ref) {
      $args []= $ref->resolve();
    }
    return empty($args)?$this->genitive:sprintf($this->genitive, ...$args);
  }
}

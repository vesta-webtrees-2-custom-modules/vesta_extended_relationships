<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Cissee\WebtreesExt\Modules\RelationshipPath;
use Illuminate\Support\Collection;

class MatchedPathLengthRelationshipPathMatcher implements RelationshipPathMatcher {

  protected $times;
  protected $asFirstRef;

  public function minTimes(): int {
    return 0; //we're only peeking, so we don't contribute here!
  }
  
  public function maxTimes(): int {
    return 0; //we're only peeking, so we don't contribute here!
  }
  
  public function __construct(
          Times $times,
          bool $asFirstRef) {
    
    $this->times = $times;
    $this->asFirstRef = $asFirstRef;
  }
  
  public function matchPath(
          int $matchedPathElements,
          bool $matchedPathDependsOnRemainingPath,
          RelationshipPath $path, 
          array $refs): Collection {    
    
    $totalPathLength = $matchedPathElements;
    if ($this->times->minTimes() > $totalPathLength) {
      return new Collection();
    }
    
    $ret = [];    
    
    $nextRefs = [];
    if ($this->asFirstRef) {
      $nextRefs []= new Reference($this->times, $totalPathLength);
    }
    foreach ($refs as $ref) {
      $nextRefs []= $ref;
    }
    if (!$this->asFirstRef) {
      $nextRefs []= new Reference($this->times, $totalPathLength);
    }
        
    $ret []= new MatchedPartialPath(
            $matchedPathElements, 
            $matchedPathDependsOnRemainingPath, 
            $path, 
            $nextRefs);
      
    return new Collection($ret);
  }
}

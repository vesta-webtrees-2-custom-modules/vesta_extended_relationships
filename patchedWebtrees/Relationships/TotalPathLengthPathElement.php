<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Cissee\WebtreesExt\Modules\RelationshipPath;
use Illuminate\Support\Collection;

class TotalPathLengthPathElement implements RelPathElement {

  protected $times;

  public function minTimes(): int {
    return 0; //we're only peeking, so we don't contribute here!
  }
  
  public function maxTimes(): int {
    return 0; //we're only peeking, so we don't contribute here!
  }
  
  public function __construct(
          Times $times) {
    
    $this->times = $times;
  }
  
  public function matchPath(
          int $matchedPathElements,
          RelationshipPath $path, 
          array $refs): Collection {    
    
    if ($path->isEmpty()) {
      return new Collection();
    }
    
    $totalPathLength = $matchedPathElements + $path->size();
    if ($this->times->minTimes() > $totalPathLength) {
      return new Collection();
    }
    
    $ret = [];    
    
    $nextRefs = [];
    foreach ($refs as $ref) {
      $nextRefs []= $ref;
    }
    $nextRefs []= new Reference($this->times, $totalPathLength);

    $ret []= new MatchedPartialPath($matchedPathElements, $path, $nextRefs);
      
    return new Collection($ret);
  }
}

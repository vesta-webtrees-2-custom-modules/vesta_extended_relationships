<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Cissee\WebtreesExt\Modules\RelationshipPath;
use Illuminate\Support\Collection;

class PathSexRelationshipPathMatcher implements RelationshipPathMatcher {

  protected $sex;
  
  public function minTimes(): int {
    return 0; //we're only peeking, so we don't contribute here!
  }
  
  public function maxTimes(): int {
    return 0; //we're only peeking, so we don't contribute here!
  }
  
  public function __construct(
          string $sex) {
    
    if (!preg_match('/^[MFU]$/', $sex)) {
      throw new Exception();
    }
    
    $this->sex = $sex;
  }
  
  public function matchPath(
          int $matchedPathElements,
          bool $matchedPathDependsOnRemainingPath,
          RelationshipPath $path, 
          array $refs): Collection {    
    
    if ($path->sex() !== $this->sex) {
      return new Collection();
    }
    
    $ret = [];
    $ret []= new MatchedPartialPath($matchedPathElements, $matchedPathDependsOnRemainingPath, $path, $refs);      
    return new Collection($ret);
  }
}

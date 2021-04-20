<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Cissee\WebtreesExt\Modules\RelationshipPath;
use Cissee\WebtreesExt\Modules\RelationshipPathSplit;
use Illuminate\Support\Collection;

class SimpleRelationshipPathMatcher implements RelationshipPathMatcher {
  
  const CODES = array(
      'fat:fat' => 'M',
      'fat:par' => 'M',
      'mot:mot' => 'F',
      'mot:par' => 'F',
      'par:par' => 'U',
      
      'hus:hus' => 'M',
      'hus:spo' => 'M',
      'wif:wif' => 'F',
      'wif:spo' => 'F',
      'spo:spo' => 'U',
      
      'son:son' => 'M',
      'son:chi' => 'M',
      'dau:dau' => 'F',
      'dau:chi' => 'F',
      'chi:chi' => 'U',
      
      'bro:bro' => 'M',
      'bro:sib' => 'M',
      'sis:sis' => 'F',
      'sis:sib' => 'F',
      'sib:sib' => 'U');
    
  protected $code;
  protected $times;

  public function minTimes(): int {
    return $this->times->minTimes();
  }
  
  public function maxTimes(): int {
    return $this->times->maxTimes();
  }
  
  public function __construct(
          string $code,
          Times $times) {
    
    $this->code = $code;
    $this->times = $times;
  }
  
  public function matchPath(
          int $matchedPathElements,
          bool $matchedPathDependsOnRemainingPath,
          RelationshipPath $path, 
          array $refs): Collection {    
    
    if ($path->isEmpty()) {
      return new Collection();
    }
    
    $count = $this->times->fixedCount();
    
    //error_log("RelationshipPathMatcher matchPath: ". $path . " times " . $count);
    
    if ($count > 0) {
      return $this->matchFixedPath($matchedPathElements, $matchedPathDependsOnRemainingPath, $path, $refs, $count);
    }
    
    //is times a backreference?
    foreach ($refs as $ref) {
      //error_log("GOT BACK-REFERENCE? ");
      if ($ref->ref() === $this->times) {
        //use its value as fixed count (offset is only for term)
        //error_log("GOT REFERENCE count: ". ($ref->value() ."/" . $this->times->offset()));
        return $this->matchFixedPath($matchedPathElements, $matchedPathDependsOnRemainingPath, $path, $refs, $ref->value());
      }
    }
      
    $minCount = $this->times->minCount();      
    return $this->matchDynamicPath($matchedPathElements, $matchedPathDependsOnRemainingPath, $path, $refs, $minCount);
  }
  
  public function matchDynamicPath(
          int $matchedPathElements,
          bool $matchedPathDependsOnRemainingPath,
          RelationshipPath $path, 
          array $refs, 
          int $minCount): Collection {
    
    if ($path->size() < $minCount) {
      return new Collection();
    }
    
    //error_log("RelationshipPathMatcher matchDynamicPath: ". $path);

    for ($i=1; $i<=$minCount; $i++) {
      /** @var RelationshipPathSplit $split */
      $split = $path->splitBefore($i);      
      $head = $split->head();
      $tail = $split->tail();
      
      $sex = $this->match($head->last()->rel());
      if ($sex === null) {
        return new Collection();
      }
    }
    
    $ret = [];    
    while ($sex !== null) {
      //error_log("RelationshipPathMatcher matched dynamically! ". $path);
    
      $nextRefs = [];
      foreach ($refs as $ref) {
        $nextRefs []= $ref;
      }
      $nextRefs []= new Reference($this->times, $minCount);

      $ret []= new MatchedPartialPath($matchedPathElements + $minCount, $matchedPathDependsOnRemainingPath, $tail, $nextRefs);
      
      //can we match more?
      $minCount++;
      
      $sex = null;
      if ($minCount <= $path->size()) {
        /** @var RelationshipPathSplit $split */
        $split = $path->splitBefore($minCount);

        $head = $split->head();
        $tail = $split->tail();

        $sex = $this->match($head->last()->rel());
      }
    }
    
    return new Collection($ret);
  }
  
  public function matchFixedPath(
          int $matchedPathElements,
          bool $matchedPathDependsOnRemainingPath,
          RelationshipPath $path, 
          array $refs, 
          int $count): Collection {
    
    //error_log("RelationshipPathMatcher matchFixedPath: ". $path . "/" . $count);
    
    if ($path->size() < $count) {
      return new Collection();
    }
    
    $sex = null;
    for ($i=1; $i<=$count; $i++) {
      /** @var RelationshipPathSplit $split */
      $split = $path->splitBefore($i);
      $head = $split->head();
      $tail = $split->tail();
      
      $sex = $this->match($head->last()->rel());
      if ($sex === null) {
        return new Collection();
      }
    }
    
    //we have a match!
    //error_log("SimpleRelationshipPathMatcher matched fixed! ". $path . " as " . $sex . " code " . $this->code);
    
    $ret = [];
    $ret []= new MatchedPartialPath($matchedPathElements + $count, $matchedPathDependsOnRemainingPath, $tail, $refs);
    return new Collection($ret);
  }
  
  public function match(string $code): ?string {
    $key = $code . ":" . $this->code;
    
    if (array_key_exists($key, self::CODES)) {
      return self::CODES[$key];
    }
    return null;
  }
}

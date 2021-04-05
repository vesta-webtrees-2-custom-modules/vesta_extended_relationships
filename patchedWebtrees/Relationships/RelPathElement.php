<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Illuminate\Support\Collection;

class RelPathElement {
  
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

  public function __construct(
          string $code,
          Times $times) {
    
    $this->code = $code;
    $this->times = $times;
  }
  
  /**
   * 
   * @param string $path
   * @param array $refs
   * @return Collection<MatchedPartialPath> we may be able to match in different ways
   */
  public function matchPath(
          string $matchedPath,
          string $path, 
          array $refs): Collection {    
    
    $count = $this->times->fixedCount();
    
    //error_log("RelPathElement matchPath: ". $path . " times " . $count);
    
    if ($count > 0) {
      return $this->matchFixedPath($matchedPath, $path, $refs, $count);
    }
    
    //is times a backreference?
    foreach ($refs as $ref) {
      //error_log("GOT BACK-REFERENCE? ");
      if ($ref->ref() === $this->times) {
        //use its value as fixed count (offset is only for term)
        //error_log("GOT REFERENCE count: ". ($ref->value() ."/" . $this->times->offset()));
        return $this->matchFixedPath($matchedPath, $path, $refs, $ref->value());
      }
    }
      
    $minCount = $this->times->minCount();      
    return $this->matchDynamicPath($matchedPath, $path, $refs, $minCount);
  }
  
  public function matchDynamicPath(
          string $matchedPath,
          string $path, 
          array $refs, 
          int $minCount): Collection {
    
    if (strlen($path) < 3*$minCount) {
      return new Collection();
    }
    
    //error_log("RelPathElement matchDynamicPath: ". $path);
    
    for ($i=0; $i<$minCount; $i++) {
      $head = substr($path, 0, 3);
      $path = substr($path, 3);
      $matchedPath .= $head;
      
      $sex = $this->match($head);
      if ($sex === null) {
        return new Collection();
      }
    }
    
    $ret = [];    
    while ($sex !== null) {
      //error_log("RelPathElement matched dynamically! ". $path);
    
      $nextRefs = [];
      foreach ($refs as $ref) {
        $nextRefs []= $ref;
      }
      $nextRefs []= new Reference($this->times, $minCount);

      $ret []= new MatchedPartialPath($matchedPath, $sex, $path, $nextRefs);
      $minCount++;
      
      //can we match more?      
      $head = substr($path, 0, 3);
      $path = substr($path, 3);
      $matchedPath .= $head;
      
      $sex = $head?$this->match($head):null;
    }
    
    return new Collection($ret);
  }
  
  public function matchFixedPath(
          string $matchedPath,
          string $path, 
          array $refs, 
          int $count): Collection {
    
    for ($i=0; $i<$count; $i++) {
      $head = substr($path, 0, 3);
      $path = substr($path, 3);
      $matchedPath .= $head;
      
      $sex = $this->match($head);
      if ($sex === null) {
        return new Collection();
      }
    }
    
    //we have a match!
    //error_log("RelPathElement matched fixed! ". $path);
    
    $ret = [];
    $ret []= new MatchedPartialPath($matchedPath, $sex, $path, $refs);
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

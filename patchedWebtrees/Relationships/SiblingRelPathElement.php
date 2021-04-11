<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Cissee\WebtreesExt\Modules\RelationshipPath;
use Illuminate\Support\Collection;

class SiblingRelPathElement implements RelPathElement {
  
  const CODES = array(
      'bro:bro' => 'M',
      'bro:sib' => 'M',
      'sis:sis' => 'F',
      'sis:sib' => 'F',
      'sib:sib' => 'U');
    
  protected $code;
  protected $ageDiff;

  public function minTimes(): int {
    return 1;
  }
  
  public function maxTimes(): int {
    return 1;
  }
  
  /**
   * 
   * @param string $code
   * @param int $ageDiff -x for younger, 0 for twins, +x for elder
   */
  public function __construct(
          string $code,
          int $ageDiff) {
    
    $this->code = $code;
    $this->ageDiff = $ageDiff;
  }
  
  public function matchPath(
          int $matchedPathElements,
          RelationshipPath $path, 
          array $refs): Collection {    
    
    if ($path->isEmpty()) {
      return new Collection();
    }

    $split = $path->splitBefore(1);
    $head = $split->head();
    $tail = $split->tail();
      
    $sex = $this->match($head->last()->rel());
    if ($sex === null) {
      return new Collection();
    }
    
    $previous = $head->from();
    if ($previous === null) {
      return new Collection();
    }
    
    $next = $head->last()->to();
    if ($next === null) {
      return new Collection();
    }
    
    $dob1 = $previous->getBirthDate();
    $dob2 = $next->getBirthDate();
    if (!$dob1->isOK() || !$dob2->isOK()) {
      return new Collection();
    }
    
    if (abs($dob1->julianDay() - $dob2->julianDay()) < 2 && $dob1->minimumDate()->day > 0 && $dob2->minimumDate()->day > 0) {
      // Exclude BEF, AFT, etc.
      //twin, matches if ageDiff === 0
      if ($this->ageDiff !== 0) {
        return new Collection();
      }
    } else if ($dob1->maximumJulianDay() < $dob2->minimumJulianDay()) {
      //younger (because julianday is greater, i.e. later), matches if ageDiff < 0
      if ($this->ageDiff >= 0) {
        return new Collection();
      }
    } else if ($dob1->minimumJulianDay() > $dob2->maximumJulianDay()) {
      //elder, matches if ageDiff > 0
      if ($this->ageDiff <= 0) {
        return new Collection();
      }
    } else {
      return new Collection();
    }  
    
    //we have a match!
    //error_log("RelPathElement matched fixed! ". $path . " as " . $sex);
    
    $ret = [];
    $ret []= new MatchedPartialPath($matchedPathElements + 1, $tail, $refs);
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

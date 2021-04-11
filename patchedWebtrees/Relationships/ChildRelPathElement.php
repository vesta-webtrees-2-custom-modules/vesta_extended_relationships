<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Cissee\WebtreesExt\Modules\RelationshipPath;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Individual;
use Illuminate\Support\Collection;

class ChildRelPathElement implements RelPathElement {
  
  const CODES = array(
      'son:son' => 'M',
      'son:chi' => 'M',
      'dau:dau' => 'F',
      'dau:chi' => 'F',
      'chi:chi' => 'U');
    
  protected $code;
  protected $pedigree;

  public function minTimes(): int {
    return 1;
  }
  
  public function maxTimes(): int {
    return 1;
  }
  
  /**
   * 
   * @param string $code
   * @param string $pedigree
   */
  public function __construct(
          string $code,
          string $pedigree) {
    
    $this->code = $code;
    $this->pedigree = $pedigree;
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
    
    $family = $head->last()->family();
    if ($family === null) {
      return new Collection();
    }
    
    $next = $head->last()->to();
    if ($next === null) {
      return new Collection();
    }
    
    $pedigree = $this->getChildFamilyLabel($family, $next);
    
    if ($pedigree !== $this->pedigree) {
      return new Collection();
    }
    
    //TODO: should we additionally check ADOP event?
    
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
  
  //cf Individual.getChildFamilyPedigree
  //expected values:
  //'birth', 'adopted', 'foster', 'sealing', 'rada'
  protected function getChildFamilyLabel(Family $family, Individual $individual): string {
    preg_match('/\n1 FAMC @' . $family->xref() . '@(?:\n[2-9].*)*\n2 PEDI (.+)/', $individual->gedcom(), $match);
    return $match[1] ?? 'birth';
  }
}

<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Fisharebest\Webtrees\Individual;

//shortest length, as in original impl (Functions.php)
//(if $minimizeSplits is set, that is the first criteria)
class DefaultRelAlgorithm implements RelAlgorithm {
  
  protected $minimizeSplits;
  
  public function __construct(
          bool $minimizeSplits = false) {
    
    $this->minimizeSplits = $minimizeSplits;
  }
  
  public function getRelationshipNameFromPath(
          RelDefs $defs,
          RelPathJoiner $joiner,
          string $path, 
          Individual $person1 = null, 
          Individual $person2 = null): string {
    
    if (!preg_match('/^(mot|fat|par|hus|wif|spo|son|dau|chi|bro|sis|sib)*$/', $path)) {
      return '<span class="error">' . $path . '</span>';
    }
    
    // The path does not include the starting person. In some languages, the
    // translation for a man’s (relative) is different from a woman’s (relative),
    // due to inflection.
    $sex1 = $person1 ? $person1->sex() : 'U';
    
    $ret = $this->getFullyMatchedPath($defs, $joiner, $sex1, $path);
    return ($ret === null)?'':$ret->nominative();
  }
  
  const CODES = array(
      'fat' => 'M',
      'mot' => 'F',
      'par' => 'U',
      
      'hus' => 'M',
      'wif' => 'F',
      'spo' => 'U',
      
      'son' => 'M',
      'dau' => 'F',
      'chi' => 'U',
      
      'bro' => 'M',
      'sis' => 'F',
      'sib' => 'U');
  
  protected static $relationshipsCache = [];  
         
  public function getFullyMatchedPath(
          RelDefs $defs,
          RelPathJoiner $joiner,
          string $sex,
          string $path): ?FullyMatchedPath {
    
    $matchedPath = $defs->getMatchedPath($sex, $path);
    
    if ($matchedPath !== null) {
      return $matchedPath;
    }
    
    // Split the relationship into sub-relationships, e.g., third-cousin’s great-uncle.
    // Try splitting at every point, and choose the path with the shorted translated name.
    // But before starting to recursively go through all combinations, do a cache look-up
    if (array_key_exists($path, self::$relationshipsCache)) {
        return self::$relationshipsCache[$path];
    }

    $relationship = $this->getFullyMatchedPathViaSplit($defs, $joiner, $sex, $path);
        
    // and store the result in the cache
    self::$relationshipsCache[$path] = $relationship;
    
    /*
    if ($relationship === null) {
      error_log("[DefaultRelAlgorithm] nothing found for " . $path);
    } else {
      error_log("Best split on " . $path . " is " . $relationship->nominative());
    }
    */
    
    return $relationship;
  }
  
  protected function compare(FullyMatchedPath $a, FullyMatchedPath $b): int {
    
    $primary = 0;
    if ($this->minimizeSplits) {
      $primary = $a->numberOfSplits() <=> $b->numberOfSplits();
    }
    
    if ($primary !== 0) {
      return $primary;
    }
    
    //this assumes the length of nominative and genitive doesn't differ widely
    return strlen($a->nominative()) <=> strlen($b->nominative());
  }
          
          
  /**
   * 
   * @param RelDefs $defs
   * @param RelPathJoiner $joiner
   * @param string $sex
   * @param string $path
   * @param PathSplitPredicate|null $splitter override hook
   * @return FullyMatchedPath|null
   */
  protected function getFullyMatchedPathViaSplit(
          RelDefs $defs,
          RelPathJoiner $joiner,
          string $sex,
          string $path,
          ?PathSplitPredicate $splitter = null): ?FullyMatchedPath {
    
    $relationship = null;
    
    $sex1 = $sex;
    
    $path1 = '';
    $next = substr($path, 0, 3);
    $path2 = substr($path, 3);
    
    while ($path2) {
      $path1 .= $next;
      
      if (($splitter === null) || ($splitter->allowSplit($next, $path2))) {
        $sex2 = self::CODES[$next];
        
        $a = $this->getFullyMatchedPath($defs, $joiner, $sex1, $path1);
        $b = $this->getFullyMatchedPath($defs, $joiner, $sex2, $path2);

        if (($a !== null) && ($b !== null)) {
          $tmp = $joiner->join($a, $b);

          if (($relationship === null) || ($this->compare($tmp, $relationship) < 0)) {
            $relationship = $tmp;
          }        
        }        
      }
      
      $next = substr($path2, 0, 3);      
      $path2 = substr($path2, 3);
    }
    
    return $relationship;
  }
}

<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

//shortest length, as in original impl (Functions.php)
//(if $minimizeSplits is set, that is the first criteria)
//but
//only split within common-ancestor-based subpaths if there is no alternative at all
//(in other words, prefer "partner's cousin_even_if_this_is_a_long_term" over "father-in-law's niece")
class ModifiedRelAlgorithm extends DefaultRelAlgorithm implements RelAlgorithm {
  
  public function __construct(
          bool $minimizeSplits = false) {
    
    parent::__construct($minimizeSplits);
  }
  
  public function getFullyMatchedPath(
          RelDefs $defs,
          RelPathJoiner $joiner,
          string $sex,
          string $path): ?FullyMatchedPath {
    
    $matchedPath = $defs->getMatchedPath($sex, $path);
    
    if ($matchedPath !== null) {
      return $matchedPath;
    }

    //modified splitting!        

    // But before starting to recursively go through all combinations, do a cache look-up
    if (array_key_exists($path, self::$relationshipsCache)) {
        return self::$relationshipsCache[$path];
    }

    $splitter = new class implements PathSplitPredicate {
        public function allowSplit(string $endOfPath1, string $path2): bool {
          return 
            (preg_match('/^(hus|wif|spo)$/', $endOfPath1)) 
              || 
            (preg_match('/^(hus|wif|spo).*/', $path2));
        }
    };
    
    $relationship = $this->getFullyMatchedPathViaSplit($defs, $joiner, $sex, $path, $splitter);
    
    //fallback (we only have to check remaining splits)
    if ($relationship === null) {
      $splitter = new class implements PathSplitPredicate {
          public function allowSplit(string $endOfPath1, string $path2): bool {
            return 
              (!preg_match('/^(hus|wif|spo)$/', $endOfPath1)) 
                && 
              (!preg_match('/^(hus|wif|spo).*/', $path2));
          }
      };

      $relationship = $this->getFullyMatchedPathViaSplit($defs, $joiner, $sex, $path, $splitter);
    }
    
    // and store the result in the cache
    self::$relationshipsCache[$path] = $relationship;

    /*
    if ($relationship === null) {
      error_log("[ModifiedRelAlgorithm] nothing found for " . $path);
    } else {
      error_log("Best split on " . $path . " is " . $relationship->nominative());
    }
    */
    
    return $relationship;
  }
}

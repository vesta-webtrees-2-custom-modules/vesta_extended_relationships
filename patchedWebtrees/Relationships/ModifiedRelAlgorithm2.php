<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Cissee\WebtreesExt\Modules\RelationshipPath;
use Cissee\WebtreesExt\Modules\RelationshipPathSplit;
use Cissee\WebtreesExt\Modules\RelationshipPathSplitPredicate;

//shortest length, as in original impl (Functions.php)
//(if $minimizeSplits is set, that is the first criteria)
//but
//only split within common-ancestor-based subpaths if there is no alternative at all
//(in other words, prefer "partner's cousin_even_if_this_is_a_long_term" over "father-in-law's niece")
class ModifiedRelAlgorithm2 extends DefaultRelAlgorithm2 implements RelAlgorithm2 {
  
  public function __construct(
          bool $minimizeSplits = false) {
    
    parent::__construct($minimizeSplits);
  }
  
  public function getFullyMatchedPath(
          RelDefs $defs,
          RelPathJoiner $joiner,
          RelationshipPath $path): ?FullyMatchedPath {
    
    $matchedPath = $defs->getMatchedPath2($path);
    
    if ($matchedPath !== null) {
      return $matchedPath;
    }

    //modified splitting!        

    // But before starting to recursively go through all combinations, do a cache look-up
    if (array_key_exists($path->key(), self::$relationshipsCache)) {
        return self::$relationshipsCache[$path->key()];
    }

    $splitter = new class implements RelationshipPathSplitPredicate {
        public function allow(RelationshipPathSplit $split): bool {
          $last = $split->head()->last();
          if ($last === null) {
            return false;
          }
          $first = $split->tail()->first();
          if ($first === null) {
            return false;
          }
          return $last->relIsAnySpouse() || $first->relIsAnySpouse();
        }
    };
    
    $relationship = $this->getFullyMatchedPathViaSplit($defs, $joiner, $path, $splitter);
    
    //fallback (we only have to check remaining splits)
    if ($relationship === null) {
      $splitter = new class implements RelationshipPathSplitPredicate {
          public function allow(RelationshipPathSplit $split): bool {
            $last = $split->head()->last();
            if ($last === null) {
              return true;
            }
            $first = $split->tail()->first();
            if ($first === null) {
              return true;
            }
            return !$last->relIsAnySpouse() && !$first->relIsAnySpouse();
          }
      };

      $relationship = $this->getFullyMatchedPathViaSplit($defs, $joiner, $path, $splitter);
    }
    
    // and store the result in the cache
    self::$relationshipsCache[$path->key()] = $relationship;

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

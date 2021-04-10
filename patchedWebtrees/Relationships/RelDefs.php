<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Cissee\WebtreesExt\Modules\RelationshipPath;
use Illuminate\Support\Collection;

class RelDefs {
    
  /** @var Collection<RelDef> */
  protected $allDefs;
  
  /** @var Collection<RelDef> */
  protected $defsForPathSize1;
  
  /** @var Collection<RelDef> */
  protected $defsForPathSize2;
  
  /**
   * $defs are assumed to be ordered (most specific first, i.e. 'father' before 'parent')
   * 
   * @param Collection<RelDef> $defs
   */
  public function __construct(
          Collection $defs) {
    
    $this->allDefs = $defs;
    
    $this->defsForPathSize1 = $defs
              ->filter(static function (RelDef $def): bool {
                  return ($def->minTimes() <= 1);
              });
              
    $this->defsForPathSize2 = $defs
              ->filter(static function (RelDef $def): bool {
                  return (($def->minTimes() <= 2) && (($def->maxTimes() === -1) || ($def->maxTimes() >= 2)));
              });
  }
  
  public function getMatchedPath(
          RelationshipPath $path): ?FullyMatchedPath {
    
    //optimize, this doesn't seem to have much of an effect though
    if ($path->size() === 1) {
      return $this->doGetMatchedPath($this->defsForPathSize1, $path);
    }
    
    //optimize, this doesn't seem to have much of an effect though
    if ($path->size() === 2) {
      return $this->doGetMatchedPath($this->defsForPathSize2, $path);
    }
    
    return $this->doGetMatchedPath($this->allDefs, $path);
  }
  
  protected static $relationshipsCache = [];
  
  protected function doGetMatchedPath(
          Collection $defs,
          RelationshipPath $path): ?FullyMatchedPath {
    
    if (array_key_exists($path->key(), self::$relationshipsCache)) {
      return self::$relationshipsCache[$path->key()];
    }
    
    $ret = null;
    foreach ($defs as $def) {
          
      /** @var RelDef $def */
      $matched = $def->matchPath($path);
      if ($matched !== null) {
        foreach ($matched as $match) {
           /** @var MatchedPath $match */
           if ($match->remainingPath()->isEmpty()) {
            $ret = new FullyMatchedPath(
                    $match->nominative(),
                    $match->genitive());
            
            self::$relationshipsCache[$path->key()] = $ret;
          } else if ($match->matchedPathElements() > 0) {
            //we can at least cache the partial match
            $partial = $path->splitBefore($match->matchedPathElements())->head();
            if (!array_key_exists($partial->key(), self::$relationshipsCache)) {              
              self::$relationshipsCache[$partial->key()] = new FullyMatchedPath(
                    $match->nominative(),
                    $match->genitive());
            }
          }         
        }
      }
      
      if ($ret !== null) {
        //abort
        return $ret;
      }
    }
    
    return $ret;
  }
}

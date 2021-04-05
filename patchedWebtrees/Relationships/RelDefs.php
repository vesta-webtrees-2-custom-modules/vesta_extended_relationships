<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Illuminate\Support\Collection;

class RelDefs {
    
  /** @var Collection<RelDef> */
  protected $defs;
  
  /**
   * $defs are assumed to be ordered (most specific first, i.e. 'father' before 'parent')
   * 
   * @param Collection<RelDef> $defs
   */
  public function __construct(
          Collection $defs) {
    
    $this->defs = $defs;
  }
  
  protected static $relationshipsCache = [];
  
  /**
   * 
   * @param string $sex
   * @param string $path
   * @return ?FullyMatchedPath
   */
  public function getMatchedPath(
          string $sex,
          string $path): ?FullyMatchedPath {
    
    if (array_key_exists($sex.$path, self::$relationshipsCache)) {
      return self::$relationshipsCache[$sex.$path];
    }
    
    $ret = null;
    foreach ($this->defs as $def) {
      $matched = $def->matchPath($sex, $path);
      if ($matched !== null) {
        foreach ($matched as $match) {
          if (empty($match->remainingPath())) {
            $ret = new FullyMatchedPath(
                    $match->nominative(),
                    $match->genitive());
            
            self::$relationshipsCache[$sex.$path] = $ret;
          } else {
            //we can at least cache the non-full match
            $key = $sex.$match->matchedPath();
            if (!array_key_exists($key, self::$relationshipsCache)) {
              self::$relationshipsCache[$key] = new FullyMatchedPath(
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

<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

class DefaultRelPathJoiner implements RelPathJoiner {
  
  public function join(FullyMatchedPath $a, FullyMatchedPath $b): FullyMatchedPath {
    $ag = $a->genitive();
    
    if ($ag === null) {
      //legacy join      
      //(note that even if we have genitive for b, we cannot use it transitively)
      return new FullyMatchedPath(
            //"Vater's Ehefrau"
            MoreI18N::xlate('%1$s’s %2$s', $a->nominative(), $b->nominative()), 
            null);
    }
    
    $bg = $b->genitive();
    
    //$a "Vater", "des Vaters"
    //$b "Ehefrau", "der Ehefrau"
    return new FullyMatchedPath(
            $b->nominative() . ' ' . $ag, //"Ehefrau des Vaters"
            ($bg === null)?null:$bg . ' ' . $ag);  //"der Ehefrau des Vaters"
  }
}

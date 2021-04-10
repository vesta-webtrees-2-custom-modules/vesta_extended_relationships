<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Modules;

class RelationshipPathSplit {
    
  protected $head;
  protected $tail;
  
  public function head(): RelationshipPath {
    return $this->head;
  }
  
  public function tail(): RelationshipPath {
    return $this->tail;
  }
  
  /**
   * 
   * @param RelationshipPath $head
   * @param RelationshipPath $tail
   */
  public function __construct(
          RelationshipPath $head,
          RelationshipPath $tail) {
        
    $this->head = $head;
    $this->tail = $tail;
  }
}

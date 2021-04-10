<?php

namespace Cissee\WebtreesExt\Modules;

interface RelationshipPathSplitPredicate {
  
  /**
   * 
   * @param RelationshipPathSplit $split
   * @return int higher priority preferred, priority smaller than 1: never split here
   */
  public function prioritize(RelationshipPathSplit $split): int;
}

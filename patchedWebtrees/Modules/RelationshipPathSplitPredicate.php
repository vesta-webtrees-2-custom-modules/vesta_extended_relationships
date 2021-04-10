<?php

namespace Cissee\WebtreesExt\Modules;

interface RelationshipPathSplitPredicate {
  
  public function allow(RelationshipPathSplit $split): bool;
}

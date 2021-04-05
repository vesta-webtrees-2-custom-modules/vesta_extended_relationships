<?php

namespace Cissee\WebtreesExt\Relationships;

interface PathSplitPredicate {
  
  public function allowSplit(string $endOfPath1, string $path2): bool;
}

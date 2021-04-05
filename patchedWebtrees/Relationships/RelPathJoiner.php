<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

interface RelPathJoiner {
  
  public function join(FullyMatchedPath $a, FullyMatchedPath $b): FullyMatchedPath;
  
}

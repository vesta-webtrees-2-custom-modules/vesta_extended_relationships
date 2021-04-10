<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

interface RelDefBuildable {
  
  public function is(
          string $nominative, 
          ?string $genitive = null): RelDef;
  
}

<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Cissee\WebtreesExt\Modules\RelationshipPath;

interface RelAlgorithm {
  
  public function getRelationshipName(
          RelDefs $defs,
          FullyMatchedPathJoiner $joiner,
          RelationshipPath $path): string;
  
}

<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Cissee\WebtreesExt\Modules\RelationshipPath;

interface RelAlgorithm2 {
  
  public function getRelationshipName(
          RelDefs $defs,
          RelPathJoiner $joiner,
          RelationshipPath $path): string;
  
}

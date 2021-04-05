<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Fisharebest\Webtrees\Individual;

interface RelAlgorithm {
  
  public function getRelationshipNameFromPath(
          RelDefs $defs,
          RelPathJoiner $joiner,
          string $path, 
          Individual $person1 = null, 
          Individual $person2 = null): string;
  
}

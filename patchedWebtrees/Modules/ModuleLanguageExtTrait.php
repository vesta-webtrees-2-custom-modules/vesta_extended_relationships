<?php

declare(strict_types=1);

namespace Fisharebest\Webtrees\Module;

use Fisharebest\Webtrees\Functions\Functions;
use Fisharebest\Webtrees\Individual;

/**
 * Trait ModuleLanguageExtTrait - default implementation of ModuleLanguageExtInterface.
 */
trait ModuleLanguageExtTrait
{
  
  public function getRelationshipNameFromPath(
          string $path, 
          Individual $person1 = null, 
          Individual $person2 = null): string {
    
    return Functions::getRelationshipNameFromPath($path, $person1, $person2);
  }
}

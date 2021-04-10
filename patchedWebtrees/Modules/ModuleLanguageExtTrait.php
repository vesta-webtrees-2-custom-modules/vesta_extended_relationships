<?php

use Cissee\WebtreesExt\Modules\RelationshipPath;
use Fisharebest\Webtrees\Functions\Functions;
use Fisharebest\Webtrees\Individual;

declare(strict_types=1);

namespace Cissee\WebtreesExt\Modules;

/**
 * Trait ModuleLanguageExtTrait - default implementation of ModuleLanguageExtInterface.
 */
trait ModuleLanguageExtTrait
{
  
  public function getRelationshipName(
          RelationshipPath $path): string {
    
    return $path->getRelationshipNameLegacy();
  }
}

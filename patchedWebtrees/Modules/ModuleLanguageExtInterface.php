<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Modules;

use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\ModuleInterface;

/**
 * Interface ModuleLanguageExtInterface - provide relationship names logic.
 */
interface ModuleLanguageExtInterface extends ModuleInterface
{

    //replacement for Functions.getRelationshipNameFromPath
    public function getRelationshipNameFromPath(
          string $path, 
          Individual $person1 = null, 
          Individual $person2 = null): string;
}

<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Modules;

use Fisharebest\Webtrees\Module\ModuleInterface;

/**
 * Interface ModuleLanguageExtInterface - provide relationship names logic.
 */
interface ModuleLanguageExtInterface extends ModuleInterface
{
    public function getRelationshipName(
          RelationshipPath $path): string;
}

<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;

//test only!
class ExtendedRelationshipModuleSub1 extends AbstractModule
    implements ModuleCustomInterface {

    use ModuleCustomTrait;

    public function title(): string {
        return 'sub';
    }
}

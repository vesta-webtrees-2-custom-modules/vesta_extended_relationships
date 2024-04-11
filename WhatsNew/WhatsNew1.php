<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships\WhatsNew;

use Cissee\WebtreesExt\WhatsNew\WhatsNewInterface;

class WhatsNew1 implements WhatsNewInterface {

  public function getMessage(): string {
    return "Vesta Extended Relationships: Improved relationship names (better sub-relationship splitting, genitive constructions, additional relationships). Currently supported for English, German, and Slovak. Additional languages to follow.";
  }
}

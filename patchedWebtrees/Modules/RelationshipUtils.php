<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Modules;

use Fisharebest\Localization\Locale\LocaleDe;
use Fisharebest\Localization\Locale\LocaleSk;
use Fisharebest\Webtrees\I18N;
use function hrtime;

class RelationshipUtils {
   
  public static function getRelationshipName(
          RelationshipPath $path): string {

      if (I18N::locale() instanceof LocaleDe) {
        $ext = new LanguageGermanExt();
        
        $start = hrtime(true);
        $ret = $ext->getRelationshipName($path);
        $end = hrtime(true);
        $time1 = ($end - $start) / 1000000;
        
        error_log("--------");
      
        $start = hrtime(true);
        $path->getRelationshipNameLegacy();
        $end = hrtime(true);
        $time2 = ($end - $start) / 1000000;

        error_log("orig time: " . $time2);
        error_log("new time: " . $time1);
        
        return $ret;
      }
      
      if (I18N::locale() instanceof LocaleSk) {
        $ext = new LanguageSlovakExt();
        return $ext->getRelationshipName($path);
      }
      
      return $path->getRelationshipNameLegacy();
  }    
}

<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Fisharebest\Webtrees\Tree;
use ReflectionClass;

class DummyDirectTree extends Tree {

  //called via Individual::getEstimatedBirthDate
  public function getPreference(string $setting_name, string $default = ''): string {
    if ('MAX_ALIVE_AGE' === $setting_name) {
      return 120;
    }
    return $default;
  }

  public static function getInstance() {
    //cannot construct directly - superclass Tree has private constructor!
    $reflection = new ReflectionClass("Cissee\Webtrees\Module\ExtendedRelationships\DummyDirectTree");
    $instance = $reflection->newInstanceWithoutConstructor();
    return $instance;
  }

}

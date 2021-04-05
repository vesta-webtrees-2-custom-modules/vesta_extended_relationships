<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;


interface RelDefBuilderDescendantAxis {
  
  public function son(): RelDefBuilderDescendant;
  
  public function daughter(): RelDefBuilderDescendant;
  
  public function child(?Times $times = null): RelDefBuilderDescendant;
}

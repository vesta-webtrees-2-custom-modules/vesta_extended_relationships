<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;


interface RelDefBuilderSpouseAxis {
  
  public function husband(): RelDefBuilderSpouse;
  
  public function wife(): RelDefBuilderSpouse;
  
  public function spouse(): RelDefBuilderSpouse;
}

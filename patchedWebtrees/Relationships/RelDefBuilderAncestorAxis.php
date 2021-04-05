<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;


interface RelDefBuilderAncestorAxis {
  
  public function father(): RelDefBuilderAncestor;
  
  public function mother(): RelDefBuilderAncestor;
  
  public function parent(?Times $times = null): RelDefBuilderAncestor;
}

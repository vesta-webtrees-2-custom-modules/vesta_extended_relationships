<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

interface RelDefBuilderAncestor extends 
  RelDefBuildable,
  RelDefBuilderAncestorAxis,
  RelDefBuilderSiblingAxis,
  RelDefBuilderDescendantAxis,
  RelDefBuilderSpouseAxis {

  
}

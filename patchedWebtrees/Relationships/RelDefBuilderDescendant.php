<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

interface RelDefBuilderDescendant extends 
  RelDefBuildable,
  //RelDefBuilderAncestorAxis, //we don't need descendant -> ancestor
  //RelDefBuilderSiblingAxis, //we don't need descendant -> sibling
  RelDefBuilderDescendantAxis,
  RelDefBuilderSpouseAxis {

  
}

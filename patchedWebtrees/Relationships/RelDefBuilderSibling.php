<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

interface RelDefBuilderSibling extends 
  RelDefBuildable,
  //RelDefBuilderAncestorAxis, //we don't need sibling -> ancestor
  //RelDefBuilderSiblingAxis, //we don't need sibling -> sibling
  RelDefBuilderDescendantAxis,
  RelDefBuilderSpouseAxis {

  
}

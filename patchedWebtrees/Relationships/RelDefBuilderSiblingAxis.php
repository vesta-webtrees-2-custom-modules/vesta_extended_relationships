<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;


interface RelDefBuilderSiblingAxis {
  
  public function brother(): RelDefBuilderSibling;
  
  public function sister(): RelDefBuilderSibling;
  
  public function sibling(): RelDefBuilderSibling;
}

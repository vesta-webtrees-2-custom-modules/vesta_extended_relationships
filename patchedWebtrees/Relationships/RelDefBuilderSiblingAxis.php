<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;


interface RelDefBuilderSiblingAxis {
  
  public function brother(): RelDefBuilderSibling;
  
  public function sister(): RelDefBuilderSibling;
  
  public function sibling(): RelDefBuilderSibling;
  
  /**
   * 
   * @param RelPathElement $element match dynamically (you may evaluate INDI and FAM facts of the current as well as preceding path elements)
   * @return RelDefBuilderSpouse
   */
  public function siblingAxisVia(RelPathElement $element): RelDefBuilderSibling;
}

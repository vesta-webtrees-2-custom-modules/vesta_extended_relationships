<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;


interface RelDefBuilderDescendantAxis {
  
  public function son(): RelDefBuilderDescendant;
  
  public function daughter(): RelDefBuilderDescendant;
  
  public function child(?Times $times = null): RelDefBuilderDescendant;
  
  /**
   * 
   * @param RelPathElement $element match dynamically (you may evaluate INDI and FAM facts of the current as well as preceding path elements)
   * @return RelDefBuilderSpouse
   */
  public function descendantAxisVia(RelPathElement $element): RelDefBuilderDescendant;
}

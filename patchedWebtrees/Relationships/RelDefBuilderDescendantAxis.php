<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;


interface RelDefBuilderDescendantAxis {
  
  public function son(): RelDefBuilderDescendant;
  
  public function daughter(): RelDefBuilderDescendant;
  
  public function child(?Times $times = null): RelDefBuilderDescendant;
  
  public function adoptiveSon(): RelDefBuilderDescendant;
  
  public function adoptiveDaughter(): RelDefBuilderDescendant;
  
  public function adoptiveChild(): RelDefBuilderDescendant;
  
  public function fosterSon(): RelDefBuilderDescendant;
  
  public function fosterDaughter(): RelDefBuilderDescendant;
  
  public function fosterChild(): RelDefBuilderDescendant;
  
  /**
   * 
   * @param RelPathElement $element match dynamically (you may evaluate INDI and FAM facts of the current as well as preceding path elements)
   * @return RelDefBuilderSpouse
   */
  public function descendantAxisVia(RelPathElement $element): RelDefBuilderDescendant;
}

<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;


interface RelDefBuilderAncestorAxis {
  
  public function father(): RelDefBuilderAncestor;
  
  public function mother(): RelDefBuilderAncestor;
  
  public function parent(?Times $times = null): RelDefBuilderAncestor;
  
  public function adoptiveFather(): RelDefBuilderAncestor;
  
  public function adoptiveMother(): RelDefBuilderAncestor;
  
  public function adoptiveParent(): RelDefBuilderAncestor;

  public function fosterFather(): RelDefBuilderAncestor;
  
  public function fosterMother(): RelDefBuilderAncestor;
  
  public function fosterParent(): RelDefBuilderAncestor;
  
  /**
   * 
   * @param RelPathElement $element match dynamically (you may evaluate INDI and FAM facts of the current as well as preceding path elements)
   * @return RelDefBuilderSpouse
   */
  public function ancestorAxisVia(RelPathElement $element): RelDefBuilderAncestor;
}

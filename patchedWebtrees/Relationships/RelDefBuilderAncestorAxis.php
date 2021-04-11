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
   * restricted to marriages after birth of the child.
   * if this is unintended, just use a combination of '->parent()->spouse()' etc. instead
   * 
   * @return RelDefBuilderSpouse
   */
  public function stepFather(): RelDefBuilderSpouse;
  
  /**
   * restricted to marriages after birth of the child.
   * if this is unintended, just use a combination of '->parent()->spouse()' etc. instead
   * 
   * @return RelDefBuilderSpouse
   */
  public function stepMother(): RelDefBuilderSpouse;
  
  /**
   * restricted to marriages after birth of the child.
   * if this is unintended, just use a combination of '->parent()->spouse()' etc. instead
   * 
   * @return RelDefBuilderSpouse
   */
  public function stepParent(): RelDefBuilderSpouse;
  
  /**
   * restricted to marriages after birth of the children.
   * if this is unintended, just use a combination of '->parent()->spouse()->child()' etc. instead
   * 
   * @return RelDefBuilderDescendant
   */
  public function stepBrother(): RelDefBuilderDescendant;
  
  /**
   * restricted to marriages after birth of the children.
   * if this is unintended, just use a combination of '->parent()->spouse()->child()' etc. instead
   * 
   * @return RelDefBuilderDescendant
   */
  public function stepSister(): RelDefBuilderDescendant;
  
  /**
   * restricted to marriages after birth of the children.
   * if this is unintended, just use a combination of '->parent()->spouse()->child()' etc. instead
   * 
   * @return RelDefBuilderDescendant
   */
  public function stepSibling(): RelDefBuilderDescendant;
  
  /**
   * 
   * @param RelPathElement $element match dynamically (you may evaluate INDI and FAM facts of the current as well as preceding path elements)
   * @return RelDefBuilderSpouse
   */
  public function ancestorAxisVia(RelPathElement $element): RelDefBuilderAncestor;
}

<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;


interface RelDefBuilderSiblingAxis {
  
  public function brother(): RelDefBuilderSibling;
  
  public function sister(): RelDefBuilderSibling;
  
  public function sibling(): RelDefBuilderSibling;
  
  public function elderBrother(): RelDefBuilderSibling;
  
  public function elderSister(): RelDefBuilderSibling;
  
  public function elderSibling(): RelDefBuilderSibling;

  public function youngerBrother(): RelDefBuilderSibling;
  
  public function youngerSister(): RelDefBuilderSibling;
  
  public function youngerSibling(): RelDefBuilderSibling;

  public function twinBrother(): RelDefBuilderSibling;
  
  public function twinSister(): RelDefBuilderSibling;
  
  public function twinSibling(): RelDefBuilderSibling;
  
  /**
   * 
   * @param RelationshipPathMatcher $element match dynamically (you may evaluate INDI and FAM facts of the current as well as preceding path elements)
   * @return RelDefBuilderSpouse
   */
  public function siblingAxisVia(RelationshipPathMatcher $element): RelDefBuilderSibling;
}

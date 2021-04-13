<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;


interface RelDefBuilderSpouseAxis {
  
  /**
   * family has a marriage event, 
   * either as last event relevant in this context, or regardless of later events
   * (relevant events are: ['ANUL', 'DIV', 'ENGA', 'MARR', '_NMR'])
   * 
   * @return RelDefBuilderSpouse
   */
  public function husband(bool $ignoreLaterEvents = false): RelDefBuilderSpouse;
  
  /**
   * family has a marriage event,
   * either as last event relevant in this context, or regardless of later events
   * (relevant events are: ['ANUL', 'DIV', 'ENGA', 'MARR', '_NMR'])
   * 
   * @return RelDefBuilderSpouse
   */
  public function wife(bool $ignoreLaterEvents = false): RelDefBuilderSpouse;
  
  /**
   * family has a marriage event,
   * either as last event relevant in this context, or regardless of later events
   * (relevant events are: ['ANUL', 'DIV', 'ENGA', 'MARR', '_NMR'])
   * 
   * @return RelDefBuilderSpouse
   */
  public function spouse(bool $ignoreLaterEvents = false): RelDefBuilderSpouse;
  
  /**
   * family has an engagement event as last event relevant in this context
   * (relevant events are: ['ANUL', 'DIV', 'ENGA', 'MARR', '_NMR'])
   * 
   * @return RelDefBuilderSpouse
   */
  public function fiance(): RelDefBuilderSpouse;
  
  /**
   * family has an engagement event as last event relevant in this context
   * (relevant events are: ['ANUL', 'DIV', 'ENGA', 'MARR', '_NMR'])
   * 
   * @return RelDefBuilderSpouse
   */
  public function fiancee(): RelDefBuilderSpouse;
  
  /**
   * family has an engagement event as last event relevant in this context
   * (relevant events are: ['ANUL', 'DIV', 'ENGA', 'MARR', '_NMR'])
   * 
   * @return RelDefBuilderSpouse
   */
  public function betrothed(): RelDefBuilderSpouse;
  
  /**
   * family has a divorce event as last event relevant in this context
   * (relevant events are: ['ANUL', 'DIV', 'ENGA', 'MARR', '_NMR'])
   * 
   * @return RelDefBuilderSpouse
   */
  public function exHusband(): RelDefBuilderSpouse;
  
  /**
   * family has a divorce event as last event relevant in this context
   * (relevant events are: ['ANUL', 'DIV', 'ENGA', 'MARR', '_NMR'])
   * 
   * @return RelDefBuilderSpouse
   */
  public function exWife(): RelDefBuilderSpouse;
  
  /**
   * family has a divorce event as last event relevant in this context
   * (relevant events are: ['ANUL', 'DIV', 'ENGA', 'MARR', '_NMR'])
   * 
   * @return RelDefBuilderSpouse
   */
  public function exSpouse(): RelDefBuilderSpouse;
  
  /**
   * family has a 'not married' (_NMR) event as last event relevant in this context
   * (relevant events are: ['ANUL', 'DIV', 'ENGA', 'MARR', '_NMR'])
   * 
   * @return RelDefBuilderSpouse
   */
  public function nonMarriedMalePartner(): RelDefBuilderSpouse;
  
  /**
   * family has a 'not married' (_NMR) event as last event relevant in this context
   * (relevant events are: ['ANUL', 'DIV', 'ENGA', 'MARR', '_NMR'])
   * 
   * @return RelDefBuilderSpouse
   */
  public function nonMarriedFemalePartner(): RelDefBuilderSpouse;
  
  /**
   * family has a 'not married' (_NMR) event as last event relevant in this context
   * (relevant events are: ['ANUL', 'DIV', 'ENGA', 'MARR', '_NMR'])
   * 
   * @return RelDefBuilderSpouse
   */
  public function nonMarriedPartner(): RelDefBuilderSpouse;
  
  public function malePartner(): RelDefBuilderSpouse;
  
  public function femalePartner(): RelDefBuilderSpouse;
  
  public function partner(): RelDefBuilderSpouse;
  
  /**
   * restricted to marriages after birth of the child.
   * if this is unintended, just use a combination of '->spouse()->child()' etc. instead
   * 
   * @return RelDefBuilderDescendant
   */
  public function stepSon(): RelDefBuilderDescendant;
  
  /**
   * restricted to marriages after birth of the child.
   * if this is unintended, just use a combination of '->spouse()->child()' etc. instead
   * 
   * @return RelDefBuilderDescendant
   */
  public function stepDaughter(): RelDefBuilderDescendant;
  
  /**
   * restricted to marriages after birth of the child.
   * if this is unintended, just use a combination of '->spouse()->child()' etc. instead
   * 
   * @return RelDefBuilderDescendant
   */
  public function stepChild(): RelDefBuilderDescendant;
  
  /**
   * 
   * @param RelationshipPathMatcher $element match dynamically (you may evaluate INDI and FAM facts of the current as well as preceding path elements)
   * @return RelDefBuilderSpouse
   */
  public function spouseAxisVia(RelationshipPathMatcher $element): RelDefBuilderSpouse;
}

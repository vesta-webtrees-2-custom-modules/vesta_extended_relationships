<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;


interface RelDefBuilderSpouseAxis {
  
  /**
   * family has a marriage event as last event relevant in this context
   * 
   * @return RelDefBuilderSpouse
   */
  public function husband(): RelDefBuilderSpouse;
  
  /**
   * family has a marriage event as last event relevant in this context
   * (relevant events are: ['ANUL', 'DIV', 'ENGA', 'MARR', '_NMR'])
   * 
   * @return RelDefBuilderSpouse
   */
  public function wife(): RelDefBuilderSpouse;
  
  /**
   * family has a marriage event as last event relevant in this context
   * (relevant events are: ['ANUL', 'DIV', 'ENGA', 'MARR', '_NMR'])
   * 
   * @return RelDefBuilderSpouse
   */
  public function spouse(): RelDefBuilderSpouse;
  
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
   * 
   * @param RelPathElement $element match dynamically (you may evaluate INDI and FAM facts of the current as well as preceding path elements)
   * @return RelDefBuilderSpouse
   */
  public function spouseAxisVia(RelPathElement $element): RelDefBuilderSpouse;
}

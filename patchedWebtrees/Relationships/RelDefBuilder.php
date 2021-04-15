<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Illuminate\Support\Collection;

class RelDefBuilder implements 
  RelDefBuilderAncestor,
  RelDefBuilderSibling,
  RelDefBuilderDescendant,
  RelDefBuilderSpouse {
  
  /** @var Collection<RelationshipPathMatcher> */
  protected $elements;
  
  protected function __construct() {    
    $this->elements = new Collection();
  }
  
  public static function def(): RelDefBuilder {
    return new RelDefBuilder();
  }
  
  public static function male(): RelDefBuilder {
    $builder = new RelDefBuilder();
    return $builder->via(new PathSexRelationshipPathMatcher("M"));
  }
  
  public static function female(): RelDefBuilder {
    $builder = new RelDefBuilder();
    return $builder->via(new PathSexRelationshipPathMatcher("F"));
  }
  
  public static function unknown(): RelDefBuilder {
    $builder = new RelDefBuilder();
    return $builder->via(new PathSexRelationshipPathMatcher("U"));
  }
  
  public function father(): RelDefBuilderAncestor {
    return $this->via(new SimpleRelationshipPathMatcher('fat', Times::one()));
  }
  
  public function mother(): RelDefBuilderAncestor {
    return $this->via(new SimpleRelationshipPathMatcher('mot', Times::one()));
  }
  
  public function parent(?Times $times = null): RelDefBuilderAncestor {
    return $this->via(new SimpleRelationshipPathMatcher('par', ($times === null)?Times::one():$times));
  }
  
  public function adoptiveFather(): RelDefBuilderAncestor {
    return $this->via(new ParentRelationshipPathMatcher('fat', 'adopted'));
  }
  
  public function adoptiveMother(): RelDefBuilderAncestor {
    return $this->via(new ParentRelationshipPathMatcher('mot', 'adopted'));
  }
  
  public function adoptiveParent(): RelDefBuilderAncestor {
    return $this->via(new ParentRelationshipPathMatcher('par', 'adopted'));
  }
  
  public function fosterFather(): RelDefBuilderAncestor {
    return $this->via(new ParentRelationshipPathMatcher('fat', 'foster'));
  }
  
  public function fosterMother(): RelDefBuilderAncestor {
    return $this->via(new ParentRelationshipPathMatcher('mot', 'foster'));
  }
  
  public function fosterParent(): RelDefBuilderAncestor {
    $this->elements->add(new ParentRelationshipPathMatcher('par', 'foster'));
    return $this;
  }
  
  public function ancestorAxisVia(RelationshipPathMatcher $element): RelDefBuilderAncestor {
    return $this->via($element);
  }
  
  public function husband(bool $ignoreLaterEvents = false): RelDefBuilderSpouse {
    if ($ignoreLaterEvents) {
      return $this->via(new SpouseRelationshipPathMatcher('hus', ['FAM:MARR'], ['MARR']));
    }
    
    return $this->via(new SpouseRelationshipPathMatcher('hus', ['FAM:MARR'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
  }
  
  public function wife(bool $ignoreLaterEvents = false): RelDefBuilderSpouse {
    if ($ignoreLaterEvents) {
      return $this->via(new SpouseRelationshipPathMatcher('wif', ['FAM:MARR'], ['MARR']));
    }
    
    return $this->via(new SpouseRelationshipPathMatcher('wif', ['FAM:MARR'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
  }
  
  public function spouse(bool $ignoreLaterEvents = false): RelDefBuilderSpouse {
    if ($ignoreLaterEvents) {
      return $this->via(new SpouseRelationshipPathMatcher('spo', ['FAM:MARR'], ['MARR']));
    }
    
    return $this->via(new SpouseRelationshipPathMatcher('spo', ['FAM:MARR'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
  }
  
  public function fiance(): RelDefBuilderSpouse {
    return $this->via(new SpouseRelationshipPathMatcher('hus', ['FAM:ENGA'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
  }
  
  public function fiancee(): RelDefBuilderSpouse {
    return $this->via(new SpouseRelationshipPathMatcher('wif', ['FAM:ENGA'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
  }
  
  public function betrothed(): RelDefBuilderSpouse {
    return $this->via(new SpouseRelationshipPathMatcher('spo', ['FAM:ENGA'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
  }
  
  public function exHusband(): RelDefBuilderSpouse {
    return $this->via(new SpouseRelationshipPathMatcher('hus', ['FAM:ANUL', 'FAM:DIV'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
  }
  
  public function exWife(): RelDefBuilderSpouse {
    return $this->via(new SpouseRelationshipPathMatcher('wif', ['FAM:ANUL', 'FAM:DIV'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
  }
  
  public function exSpouse(): RelDefBuilderSpouse {
    return $this->via(new SpouseRelationshipPathMatcher('spo', ['FAM:ANUL', 'FAM:DIV'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
  }
  
  public function nonMarriedMalePartner(): RelDefBuilderSpouse {
    return $this->via(new SpouseRelationshipPathMatcher('hus', ['FAM:_NMR'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
  }
  
  public function nonMarriedFemalePartner(): RelDefBuilderSpouse {
    return $this->via(new SpouseRelationshipPathMatcher('wif', ['FAM:_NMR'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
  }
  
  public function nonMarriedPartner(): RelDefBuilderSpouse {
    return $this->via(new SpouseRelationshipPathMatcher('spo', ['FAM:_NMR'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
  }
  
  public function malePartner(): RelDefBuilderSpouse {
    return $this->via(new SimpleRelationshipPathMatcher('hus', Times::one()));
  }
  
  public function femalePartner(): RelDefBuilderSpouse {
    return $this->via(new SimpleRelationshipPathMatcher('wif', Times::one()));
  }
  
  public function partner(): RelDefBuilderSpouse {
    return $this->via(new SimpleRelationshipPathMatcher('spo', Times::one()));
  }
  
  public function spouseAxisVia(RelationshipPathMatcher $element): RelDefBuilderSpouse {
    return $this->via($element);
  }

  public function son(): RelDefBuilderDescendant {
    return $this->via(new SimpleRelationshipPathMatcher('son', Times::one()));
  }
  
  public function daughter(): RelDefBuilderDescendant {
    return $this->via(new SimpleRelationshipPathMatcher('dau', Times::one()));
  }
  
  public function child(?Times $times = null): RelDefBuilderDescendant {
    return $this->via(new SimpleRelationshipPathMatcher('chi', ($times === null)?Times::one():$times));
  }
 
  public function adoptiveSon(): RelDefBuilderDescendant {
    return $this->via(new ChildRelationshipPathMatcher('son', 'adopted'));
  }
  
  public function adoptiveDaughter(): RelDefBuilderDescendant {
    return $this->via(new ChildRelationshipPathMatcher('dau', 'adopted'));
  }
  
  public function adoptiveChild(): RelDefBuilderDescendant {
    return $this->via(new ChildRelationshipPathMatcher('chi', 'adopted'));
  }
  
  public function fosterSon(): RelDefBuilderDescendant {
    return $this->via(new ChildRelationshipPathMatcher('son', 'foster'));
  }
  
  public function fosterDaughter(): RelDefBuilderDescendant {
    return $this->via(new ChildRelationshipPathMatcher('dau', 'foster'));
  }
  
  public function fosterChild(): RelDefBuilderDescendant {
    return $this->via(new ChildRelationshipPathMatcher('chi', 'foster'));
  }
  
  public function descendantAxisVia(RelationshipPathMatcher $element): RelDefBuilderDescendant {
    return $this->via($element);
  }
  
  public function brother(): RelDefBuilderSibling {
    return $this->via(new SimpleRelationshipPathMatcher('bro', Times::one()));
  }
  
  public function sister(): RelDefBuilderSibling {
    return $this->via(new SimpleRelationshipPathMatcher('sis', Times::one()));
  }
  
  public function sibling(): RelDefBuilderSibling {
    return $this->via(new SimpleRelationshipPathMatcher('sib', Times::one()));
  }
  
  public function elderBrother(): RelDefBuilderSibling {
    return $this->via(new SiblingRelationshipPathMatcher('bro', 1));
  }
  
  public function elderSister(): RelDefBuilderSibling {
    return $this->via(new SiblingRelationshipPathMatcher('sis', 1));
  }
  
  public function elderSibling(): RelDefBuilderSibling {
    return $this->via(new SiblingRelationshipPathMatcher('sib', 1));
  }
  
  public function youngerBrother(): RelDefBuilderSibling {
    return $this->via(new SiblingRelationshipPathMatcher('bro', -1));
  }
  
  public function youngerSister(): RelDefBuilderSibling {
    return $this->via(new SiblingRelationshipPathMatcher('sis', -1));
  }
  
  public function youngerSibling(): RelDefBuilderSibling {
    return $this->via(new SiblingRelationshipPathMatcher('sib', -1));
  }
  
  public function twinBrother(): RelDefBuilderSibling {
    return $this->via(new SiblingRelationshipPathMatcher('bro', 0));
  }
  
  public function twinSister(): RelDefBuilderSibling {
    return $this->via(new SiblingRelationshipPathMatcher('sis', 0));
  }
  
  public function twinSibling(): RelDefBuilderSibling {
    return $this->via(new SiblingRelationshipPathMatcher('sib', 0));
  }
  
  public function siblingAxisVia(RelationshipPathMatcher $element): RelDefBuilderSibling {
    return $this->via($element);
  }
    
  public function stepFather(): RelDefBuilderSpouse {
    return $this->via(new StepParentRelationshipPathMatcher('par', 'hus'));
  }
  
  public function stepMother(): RelDefBuilderSpouse {
    return $this->via(new StepParentRelationshipPathMatcher('par', 'wif'));
  }
  
  public function stepParent(): RelDefBuilderSpouse {
    return $this->via(new StepParentRelationshipPathMatcher('par', 'spo'));
  }
     
  public function stepSon(): RelDefBuilderDescendant {
    return $this->via(new StepChildRelationshipPathMatcher('spo', 'son'));
  }
  
  public function stepDaughter(): RelDefBuilderDescendant {
    return $this->via(new StepChildRelationshipPathMatcher('spo', 'dau'));
  }
  
  public function stepChild(): RelDefBuilderDescendant {
    return $this->via(new StepChildRelationshipPathMatcher('spo', 'chi'));
  }
  
  public function stepBrother(): RelDefBuilderDescendant {
    return $this->via(new StepSiblingRelationshipPathMatcher('par', 'spo', 'son'));
  }
  
  public function stepSister(): RelDefBuilderDescendant {
    return $this->via(new StepSiblingRelationshipPathMatcher('par', 'spo', 'dau'));
  }
  
  public function stepSibling(): RelDefBuilderDescendant {
    return $this->via(new StepSiblingRelationshipPathMatcher('par', 'spo', 'chi'));
  }
  
  ////////
  
  public function matchedPathLengthAsFirstRef(Times $times): RelDefBuilder {
    return $this->via(new MatchedPathLengthRelationshipPathMatcher($times, true));
  }
  
  ////////
  
  public function via(RelationshipPathMatcher $element): RelDefBuilder {
    $this->elements->add($element);
    return $this;
  }
  
  public function is(string $nominative, ?string $genitive = null): RelDef {
    return new RelDef($this->elements, $nominative, $genitive);
  }
}

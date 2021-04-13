<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Illuminate\Support\Collection;

class RelDefBuilder implements 
  RelDefBuilderAncestor,
  RelDefBuilderSibling,
  RelDefBuilderDescendant,
  RelDefBuilderSpouse {
  
  /** @var Collection */
  protected $elements;
  
  /**
   * 
   * @param Collection<RelationshipPathMatcher> $elements
   */
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
    $this->elements->add(new SimpleRelationshipPathMatcher('fat', Times::one()));
    return $this;
  }
  
  public function mother(): RelDefBuilderAncestor {
    $this->elements->add(new SimpleRelationshipPathMatcher('mot', Times::one()));
    return $this;
  }
  
  public function parent(?Times $times = null): RelDefBuilderAncestor {
    $this->elements->add(new SimpleRelationshipPathMatcher('par', ($times === null)?Times::one():$times));
    return $this;
  }
  
  public function adoptiveFather(): RelDefBuilderAncestor {
    $this->elements->add(new ParentRelationshipPathMatcher('fat', 'adopted'));
    return $this;
  }
  
  public function adoptiveMother(): RelDefBuilderAncestor {
    $this->elements->add(new ParentRelationshipPathMatcher('mot', 'adopted'));
    return $this;
  }
  
  public function adoptiveParent(): RelDefBuilderAncestor {
    $this->elements->add(new ParentRelationshipPathMatcher('par', 'adopted'));
    return $this;
  }
  
  public function fosterFather(): RelDefBuilderAncestor {
    $this->elements->add(new ParentRelationshipPathMatcher('fat', 'foster'));
    return $this;
  }
  
  public function fosterMother(): RelDefBuilderAncestor {
    $this->elements->add(new ParentRelationshipPathMatcher('mot', 'foster'));
    return $this;
  }
  
  public function fosterParent(): RelDefBuilderAncestor {
    $this->elements->add(new ParentRelationshipPathMatcher('par', 'foster'));
    return $this;
  }
  
  public function ancestorAxisVia(RelationshipPathMatcher $element): RelDefBuilderAncestor {
    $this->elements->add($element);
    return $this;
  }
  
  public function husband(bool $ignoreLaterEvents = false): RelDefBuilderSpouse {
    if ($ignoreLaterEvents) {
      $this->elements->add(new SpouseRelationshipPathMatcher('hus', ['FAM:MARR'], ['MARR']));
    } else {
      $this->elements->add(new SpouseRelationshipPathMatcher('hus', ['FAM:MARR'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
    }
    return $this;
  }
  
  public function wife(bool $ignoreLaterEvents = false): RelDefBuilderSpouse {
    if ($ignoreLaterEvents) {
      $this->elements->add(new SpouseRelationshipPathMatcher('wif', ['FAM:MARR'], ['MARR']));
    } else {
      $this->elements->add(new SpouseRelationshipPathMatcher('wif', ['FAM:MARR'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
    }
    return $this;
  }
  
  public function spouse(bool $ignoreLaterEvents = false): RelDefBuilderSpouse {
    if ($ignoreLaterEvents) {
      $this->elements->add(new SpouseRelationshipPathMatcher('spo', ['FAM:MARR'], ['MARR']));
    } else {
      $this->elements->add(new SpouseRelationshipPathMatcher('spo', ['FAM:MARR'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
    }
    
    return $this;
  }
  
  public function fiance(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelationshipPathMatcher('hus', ['FAM:ENGA'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
    return $this;
  }
  
  public function fiancee(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelationshipPathMatcher('wif', ['FAM:ENGA'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
    return $this;
  }
  
  public function betrothed(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelationshipPathMatcher('spo', ['FAM:ENGA'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
    return $this;
  }
  
  public function exHusband(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelationshipPathMatcher('hus', ['FAM:ANUL', 'FAM:DIV'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
    return $this;
  }
  
  public function exWife(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelationshipPathMatcher('wif', ['FAM:ANUL', 'FAM:DIV'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
    return $this;
  }
  
  public function exSpouse(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelationshipPathMatcher('spo', ['FAM:ANUL', 'FAM:DIV'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
    return $this;
  }
  
  public function nonMarriedMalePartner(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelationshipPathMatcher('hus', ['FAM:_NMR'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
    return $this;
  }
  
  public function nonMarriedFemalePartner(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelationshipPathMatcher('wif', ['FAM:_NMR'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
    return $this;
  }
  
  public function nonMarriedPartner(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelationshipPathMatcher('spo', ['FAM:_NMR'], SpouseRelationshipPathMatcher::typicalRelevantFacts()));
    return $this;
  }
  
  public function malePartner(): RelDefBuilderSpouse {
    $this->elements->add(new SimpleRelationshipPathMatcher('hus', Times::one()));
    return $this;
  }
  
  public function femalePartner(): RelDefBuilderSpouse {
    $this->elements->add(new SimpleRelationshipPathMatcher('wif', Times::one()));
    return $this;
  }
  
  public function partner(): RelDefBuilderSpouse {
    $this->elements->add(new SimpleRelationshipPathMatcher('spo', Times::one()));
    return $this;
  }
  
  public function spouseAxisVia(RelationshipPathMatcher $element): RelDefBuilderSpouse {
    $this->elements->add($element);
    return $this;
  }

  public function son(): RelDefBuilderDescendant {
    $this->elements->add(new SimpleRelationshipPathMatcher('son', Times::one()));
    return $this;
  }
  
  public function daughter(): RelDefBuilderDescendant {
    $this->elements->add(new SimpleRelationshipPathMatcher('dau', Times::one()));
    return $this;
  }
  
  public function child(?Times $times = null): RelDefBuilderDescendant {
    $this->elements->add(new SimpleRelationshipPathMatcher('chi', ($times === null)?Times::one():$times));
    return $this;
  }
 
  public function adoptiveSon(): RelDefBuilderDescendant {
    $this->elements->add(new ChildRelationshipPathMatcher('son', 'adopted'));
    return $this;
  }
  
  public function adoptiveDaughter(): RelDefBuilderDescendant {
    $this->elements->add(new ChildRelationshipPathMatcher('dau', 'adopted'));
    return $this;
  }
  
  public function adoptiveChild(): RelDefBuilderDescendant {
    $this->elements->add(new ChildRelationshipPathMatcher('chi', 'adopted'));
    return $this;
  }
  
  public function fosterSon(): RelDefBuilderDescendant {
    $this->elements->add(new ChildRelationshipPathMatcher('son', 'foster'));
    return $this;
  }
  
  public function fosterDaughter(): RelDefBuilderDescendant {
    $this->elements->add(new ChildRelationshipPathMatcher('dau', 'foster'));
    return $this;
  }
  
  public function fosterChild(): RelDefBuilderDescendant {
    $this->elements->add(new ChildRelationshipPathMatcher('chi', 'foster'));
    return $this;
  }
  
  public function descendantAxisVia(RelationshipPathMatcher $element): RelDefBuilderDescendant {
    $this->elements->add($element);
    return $this;
  }
  
  public function brother(): RelDefBuilderSibling {
    $this->elements->add(new SimpleRelationshipPathMatcher('bro', Times::one()));
    return $this;
  }
  
  public function sister(): RelDefBuilderSibling {
    $this->elements->add(new SimpleRelationshipPathMatcher('sis', Times::one()));
    return $this;
  }
  
  public function sibling(): RelDefBuilderSibling {
    $this->elements->add(new SimpleRelationshipPathMatcher('sib', Times::one()));
    return $this;
  }
  
  public function elderBrother(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelationshipPathMatcher('bro', 1));
    return $this;
  }
  
  public function elderSister(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelationshipPathMatcher('sis', 1));
    return $this;
  }
  
  public function elderSibling(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelationshipPathMatcher('sib', 1));
    return $this;
  }
  
  public function youngerBrother(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelationshipPathMatcher('bro', -1));
    return $this;
  }
  
  public function youngerSister(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelationshipPathMatcher('sis', -1));
    return $this;
  }
  
  public function youngerSibling(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelationshipPathMatcher('sib', -1));
    return $this;
  }
  
  public function twinBrother(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelationshipPathMatcher('bro', 0));
    return $this;
  }
  
  public function twinSister(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelationshipPathMatcher('sis', 0));
    return $this;
  }
  
  public function twinSibling(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelationshipPathMatcher('sib', 0));
    return $this;
  }
  
  public function siblingAxisVia(RelationshipPathMatcher $element): RelDefBuilderSibling {
    $this->elements->add($element);
    return $this;
  }
    
  public function stepFather(): RelDefBuilderSpouse {
    $this->elements->add(new StepParentRelationshipPathMatcher('par', 'hus'));
    return $this;
  }
  
  public function stepMother(): RelDefBuilderSpouse {
    $this->elements->add(new StepParentRelationshipPathMatcher('par', 'wif'));
    return $this;
  }
  
  public function stepParent(): RelDefBuilderSpouse {
    $this->elements->add(new StepParentRelationshipPathMatcher('par', 'spo'));
    return $this;
  }
     
  public function stepSon(): RelDefBuilderDescendant {
    $this->elements->add(new StepChildRelationshipPathMatcher('spo', 'son'));
    return $this;
  }
  
  public function stepDaughter(): RelDefBuilderDescendant {
    $this->elements->add(new StepChildRelationshipPathMatcher('spo', 'dau'));
    return $this;
  }
  
  public function stepChild(): RelDefBuilderDescendant {
    $this->elements->add(new StepChildRelationshipPathMatcher('spo', 'chi'));
    return $this;
  }
  
  public function stepBrother(): RelDefBuilderDescendant {
    $this->elements->add(new StepSiblingRelationshipPathMatcher('par', 'spo', 'son'));
    return $this;
  }
  
  public function stepSister(): RelDefBuilderDescendant {
    $this->elements->add(new StepSiblingRelationshipPathMatcher('par', 'spo', 'dau'));
    return $this;
  }
  
  public function stepSibling(): RelDefBuilderDescendant {
    $this->elements->add(new StepSiblingRelationshipPathMatcher('par', 'spo', 'chi'));
    return $this;
  }
  
  ////////
  
  public function peekTotalPathLength(Times $times): RelDefBuilder {
    $this->elements->add(new TotalPathLengthRelationshipPathMatcher($times));
    return $this;
  }
  
  public function via(RelationshipPathMatcher $element): RelDefBuilder {
    $this->elements->add($element);
    return $this;
  }
  
  public function is(string $nominative, ?string $genitive = null): RelDef {
    return new RelDef($this->elements, $nominative, $genitive);
  }
}

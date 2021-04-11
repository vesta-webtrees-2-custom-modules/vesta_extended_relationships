<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Illuminate\Support\Collection;

class RelPath implements 
  RelDefBuilderAncestor,
  RelDefBuilderSibling,
  RelDefBuilderDescendant,
  RelDefBuilderSpouse {
  
  protected $from;
  
  /** @var Collection */
  protected $elements;
  
  /**
   * 
   * @param RelPathFrom $fro
   * @param Collection<RelPathElement> $elements
   */
  protected function __construct(
          RelPathFrom $from) {
    
    $this->from = $from;
    $this->elements = new Collection();
  }
  
  public static function any(): RelPath {
    return new RelPath(new SimpleRelPathFrom('*'));
  }
  
  public static function male(): RelPath {
    return new RelPath(new SimpleRelPathFrom('M'));
  }
  
  public static function female(): RelPath {
    return new RelPath(new SimpleRelPathFrom('F'));
  }
  
  public static function unknown(): RelPath {
    return new RelPath(new SimpleRelPathFrom('U'));
  }
  
  public static function from(RelPathFrom $relPathFrom): RelPath {
    return new RelPath($relPathFrom);
  }
  
  public function father(): RelDefBuilderAncestor {
    $this->elements->add(new SimpleRelPathElement('fat', Times::one()));
    return $this;
  }
  
  public function mother(): RelDefBuilderAncestor {
    $this->elements->add(new SimpleRelPathElement('mot', Times::one()));
    return $this;
  }
  
  public function parent(?Times $times = null): RelDefBuilderAncestor {
    $this->elements->add(new SimpleRelPathElement('par', ($times === null)?Times::one():$times));
    return $this;
  }
  
  public function adoptiveFather(): RelDefBuilderAncestor {
    $this->elements->add(new ParentRelPathElement('fat', 'adopted'));
    return $this;
  }
  
  public function adoptiveMother(): RelDefBuilderAncestor {
    $this->elements->add(new ParentRelPathElement('mot', 'adopted'));
    return $this;
  }
  
  public function adoptiveParent(): RelDefBuilderAncestor {
    $this->elements->add(new ParentRelPathElement('par', 'adopted'));
    return $this;
  }
  
  public function fosterFather(): RelDefBuilderAncestor {
    $this->elements->add(new ParentRelPathElement('fat', 'foster'));
    return $this;
  }
  
  public function fosterMother(): RelDefBuilderAncestor {
    $this->elements->add(new ParentRelPathElement('mot', 'foster'));
    return $this;
  }
  
  public function fosterParent(): RelDefBuilderAncestor {
    $this->elements->add(new ParentRelPathElement('par', 'foster'));
    return $this;
  }
  
  public function ancestorAxisVia(RelPathElement $element): RelDefBuilderAncestor {
    $this->elements->add($element);
    return $this;
  }
  
  public function husband(bool $ignoreLaterEvents = false): RelDefBuilderSpouse {
    if ($ignoreLaterEvents) {
      $this->elements->add(new SpouseRelPathElement('hus', ['FAM:MARR'], ['MARR']));
    } else {
      $this->elements->add(new SpouseRelPathElement('hus', ['FAM:MARR'], SpouseRelPathElement::typicalRelevantFacts()));
    }
    return $this;
  }
  
  public function wife(bool $ignoreLaterEvents = false): RelDefBuilderSpouse {
    if ($ignoreLaterEvents) {
      $this->elements->add(new SpouseRelPathElement('wif', ['FAM:MARR'], ['MARR']));
    } else {
      $this->elements->add(new SpouseRelPathElement('wif', ['FAM:MARR'], SpouseRelPathElement::typicalRelevantFacts()));
    }
    return $this;
  }
  
  public function spouse(bool $ignoreLaterEvents = false): RelDefBuilderSpouse {
    if ($ignoreLaterEvents) {
      $this->elements->add(new SpouseRelPathElement('spo', ['FAM:MARR'], ['MARR']));
    } else {
      $this->elements->add(new SpouseRelPathElement('spo', ['FAM:MARR'], SpouseRelPathElement::typicalRelevantFacts()));
    }
    
    return $this;
  }
  
  public function fiance(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement('hus', ['FAM:ENGA'], SpouseRelPathElement::typicalRelevantFacts()));
    return $this;
  }
  
  public function fiancee(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement('wif', ['FAM:ENGA'], SpouseRelPathElement::typicalRelevantFacts()));
    return $this;
  }
  
  public function betrothed(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement('spo', ['FAM:ENGA'], SpouseRelPathElement::typicalRelevantFacts()));
    return $this;
  }
  
  public function exHusband(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement('hus', ['FAM:ANUL', 'FAM:DIV'], SpouseRelPathElement::typicalRelevantFacts()));
    return $this;
  }
  
  public function exWife(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement('wif', ['FAM:ANUL', 'FAM:DIV'], SpouseRelPathElement::typicalRelevantFacts()));
    return $this;
  }
  
  public function exSpouse(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement('spo', ['FAM:ANUL', 'FAM:DIV'], SpouseRelPathElement::typicalRelevantFacts()));
    return $this;
  }
  
  public function nonMarriedMalePartner(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement('hus', ['FAM:_NMR'], SpouseRelPathElement::typicalRelevantFacts()));
    return $this;
  }
  
  public function nonMarriedFemalePartner(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement('wif', ['FAM:_NMR'], SpouseRelPathElement::typicalRelevantFacts()));
    return $this;
  }
  
  public function nonMarriedPartner(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement('spo', ['FAM:_NMR'], SpouseRelPathElement::typicalRelevantFacts()));
    return $this;
  }
  
  public function malePartner(): RelDefBuilderSpouse {
    $this->elements->add(new SimpleRelPathElement('hus', Times::one()));
    return $this;
  }
  
  public function femalePartner(): RelDefBuilderSpouse {
    $this->elements->add(new SimpleRelPathElement('wif', Times::one()));
    return $this;
  }
  
  public function partner(): RelDefBuilderSpouse {
    $this->elements->add(new SimpleRelPathElement('spo', Times::one()));
    return $this;
  }
  
  public function spouseAxisVia(RelPathElement $element): RelDefBuilderSpouse {
    $this->elements->add($element);
    return $this;
  }

  public function son(): RelDefBuilderDescendant {
    $this->elements->add(new SimpleRelPathElement('son', Times::one()));
    return $this;
  }
  
  public function daughter(): RelDefBuilderDescendant {
    $this->elements->add(new SimpleRelPathElement('dau', Times::one()));
    return $this;
  }
  
  public function child(?Times $times = null): RelDefBuilderDescendant {
    $this->elements->add(new SimpleRelPathElement('chi', ($times === null)?Times::one():$times));
    return $this;
  }
 
  public function adoptiveSon(): RelDefBuilderDescendant {
    $this->elements->add(new ChildRelPathElement('son', 'adopted'));
    return $this;
  }
  
  public function adoptiveDaughter(): RelDefBuilderDescendant {
    $this->elements->add(new ChildRelPathElement('dau', 'adopted'));
    return $this;
  }
  
  public function adoptiveChild(): RelDefBuilderDescendant {
    $this->elements->add(new ChildRelPathElement('chi', 'adopted'));
    return $this;
  }
  
  public function fosterSon(): RelDefBuilderDescendant {
    $this->elements->add(new ChildRelPathElement('son', 'foster'));
    return $this;
  }
  
  public function fosterDaughter(): RelDefBuilderDescendant {
    $this->elements->add(new ChildRelPathElement('dau', 'foster'));
    return $this;
  }
  
  public function fosterChild(): RelDefBuilderDescendant {
    $this->elements->add(new ChildRelPathElement('chi', 'foster'));
    return $this;
  }
  
  public function descendantAxisVia(RelPathElement $element): RelDefBuilderDescendant {
    $this->elements->add($element);
    return $this;
  }
  
  public function brother(): RelDefBuilderSibling {
    $this->elements->add(new SimpleRelPathElement('bro', Times::one()));
    return $this;
  }
  
  public function sister(): RelDefBuilderSibling {
    $this->elements->add(new SimpleRelPathElement('sis', Times::one()));
    return $this;
  }
  
  public function sibling(): RelDefBuilderSibling {
    $this->elements->add(new SimpleRelPathElement('sib', Times::one()));
    return $this;
  }
  
  public function elderBrother(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelPathElement('bro', 1));
    return $this;
  }
  
  public function elderSister(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelPathElement('sis', 1));
    return $this;
  }
  
  public function elderSibling(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelPathElement('sib', 1));
    return $this;
  }
  
  public function youngerBrother(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelPathElement('bro', -1));
    return $this;
  }
  
  public function youngerSister(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelPathElement('sis', -1));
    return $this;
  }
  
  public function youngerSibling(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelPathElement('sib', -1));
    return $this;
  }
  
  public function twinBrother(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelPathElement('bro', 0));
    return $this;
  }
  
  public function twinSister(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelPathElement('sis', 0));
    return $this;
  }
  
  public function twinSibling(): RelDefBuilderSibling {
    $this->elements->add(new SiblingRelPathElement('sib', 0));
    return $this;
  }
  
  public function siblingAxisVia(RelPathElement $element): RelDefBuilderSibling {
    $this->elements->add($element);
    return $this;
  }
    
  public function stepFather(): RelDefBuilderSpouse {
    $this->elements->add(new StepParentRelPathElement('par', 'hus'));
    return $this;
  }
  
  public function stepMother(): RelDefBuilderSpouse {
    $this->elements->add(new StepParentRelPathElement('par', 'wif'));
    return $this;
  }
  
  public function stepParent(): RelDefBuilderSpouse {
    $this->elements->add(new StepParentRelPathElement('par', 'spo'));
    return $this;
  }
     
  public function stepSon(): RelDefBuilderDescendant {
    $this->elements->add(new StepChildRelPathElement('spo', 'son'));
    return $this;
  }
  
  public function stepDaughter(): RelDefBuilderDescendant {
    $this->elements->add(new StepChildRelPathElement('spo', 'dau'));
    return $this;
  }
  
  public function stepChild(): RelDefBuilderDescendant {
    $this->elements->add(new StepChildRelPathElement('spo', 'chi'));
    return $this;
  }
  
  public function stepBrother(): RelDefBuilderDescendant {
    $this->elements->add(new StepSiblingRelPathElement('par', 'spo', 'son'));
    return $this;
  }
  
  public function stepSister(): RelDefBuilderDescendant {
    $this->elements->add(new StepSiblingRelPathElement('par', 'spo', 'dau'));
    return $this;
  }
  
  public function stepSibling(): RelDefBuilderDescendant {
    $this->elements->add(new StepSiblingRelPathElement('par', 'spo', 'chi'));
    return $this;
  }
  
  public function is(string $nominative, ?string $genitive = null): RelDef {
    return new RelDef($this->from, $this->elements, $nominative, $genitive);
  }
}

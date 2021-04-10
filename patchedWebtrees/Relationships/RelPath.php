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
    $this->elements->add(new SimpleRelPathElement("fat", Times::one()));
    return $this;
  }
  
  public function mother(): RelDefBuilderAncestor {
    $this->elements->add(new SimpleRelPathElement("mot", Times::one()));
    return $this;
  }
  
  public function parent(?Times $times = null): RelDefBuilderAncestor {
    $this->elements->add(new SimpleRelPathElement("par", ($times === null)?Times::one():$times));
    return $this;
  }
   
  public function ancestorAxisVia(RelPathElement $element): RelDefBuilderAncestor {
    $this->elements->add($element);
    return $this;
  }
  
  public function husband(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement("hus", ['FAM:MARR']));
    return $this;
  }
  
  public function wife(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement("wif", ['FAM:MARR']));
    return $this;
  }
  
  public function spouse(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement("spo", ['FAM:MARR']));
    return $this;
  }
  
  public function fiance(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement("hus", ['FAM:ENGA']));
    return $this;
  }
  
  public function fiancee(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement("wif", ['FAM:ENGA']));
    return $this;
  }
  
  public function betrothed(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement("spo", ['FAM:ENGA']));
    return $this;
  }
  
  public function exHusband(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement("hus", ['FAM:ANUL', 'FAM:DIV']));
    return $this;
  }
  
  public function exWife(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement("wif", ['FAM:ANUL', 'FAM:DIV']));
    return $this;
  }
  
  public function exSpouse(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement("spo", ['FAM:ANUL', 'FAM:DIV']));
    return $this;
  }
  
  public function nonMarriedMalePartner(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement("hus", ['FAM:_NMR']));
    return $this;
  }
  
  public function nonMarriedFemalePartner(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement("wif", ['FAM:_NMR']));
    return $this;
  }
  
  public function nonMarriedPartner(): RelDefBuilderSpouse {
    $this->elements->add(new SpouseRelPathElement("spo", ['FAM:_NMR']));
    return $this;
  }
  
  public function malePartner(): RelDefBuilderSpouse {
    $this->elements->add(new SimpleRelPathElement("hus", Times::one()));
    return $this;
  }
  
  public function femalePartner(): RelDefBuilderSpouse {
    $this->elements->add(new SimpleRelPathElement("wif", Times::one()));
    return $this;
  }
  
  public function partner(): RelDefBuilderSpouse {
    $this->elements->add(new SimpleRelPathElement("spo", Times::one()));
    return $this;
  }
  
  public function spouseAxisVia(RelPathElement $element): RelDefBuilderSpouse {
    $this->elements->add($element);
    return $this;
  }

  public function son(): RelDefBuilderDescendant {
    $this->elements->add(new SimpleRelPathElement("son", Times::one()));
    return $this;
  }
  
  public function daughter(): RelDefBuilderDescendant {
    $this->elements->add(new SimpleRelPathElement("dau", Times::one()));
    return $this;
  }
  
  public function child(?Times $times = null): RelDefBuilderDescendant {
    $this->elements->add(new SimpleRelPathElement("chi", ($times === null)?Times::one():$times));
    return $this;
  }
 
  public function descendantAxisVia(RelPathElement $element): RelDefBuilderDescendant {
    $this->elements->add($element);
    return $this;
  }
  
  public function brother(): RelDefBuilderSibling {
    $this->elements->add(new SimpleRelPathElement("bro", Times::one()));
    return $this;
  }
  
  public function sister(): RelDefBuilderSibling {
    $this->elements->add(new SimpleRelPathElement("sis", Times::one()));
    return $this;
  }
  
  public function sibling(): RelDefBuilderSibling {
    $this->elements->add(new SimpleRelPathElement("sib", Times::one()));
    return $this;
  }
    
  public function siblingAxisVia(RelPathElement $element): RelDefBuilderSibling {
    $this->elements->add($element);
    return $this;
  }
  
  public function is(string $nominative, ?string $genitive): RelDef {
    return new RelDef($this->from, $this->elements, $nominative, $genitive);
  }
}

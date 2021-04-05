<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Illuminate\Support\Collection;

class RelPath implements 
  RelDefBuilderAncestor,
  RelDefBuilderSibling,
  RelDefBuilderDescendant,
  RelDefBuilderSpouse {
  
  protected $sex;
  
  /** @var Collection */
  protected $elements;
  
  /**
   * 
   * @param string $sex
   * @param Collection<RelPathElement> $elements
   */
  protected function __construct(
          string $sex) {
    
    $this->sex = $sex;
    $this->elements = new Collection();
  }
  
  public static function any(): RelPath {
    return new RelPath("*");
  }
  
  public static function male(): RelPath {
    return new RelPath("M");
  }
  
  public static function female(): RelPath {
    return new RelPath("F");
  }
  
  public static function unknown(): RelPath {
    return new RelPath("U");
  }
  
  public function father(): RelDefBuilderAncestor {
    $this->elements->add(new RelPathElement("fat", Times::one()));
    return $this;
  }
  
  public function mother(): RelDefBuilderAncestor {
    $this->elements->add(new RelPathElement("mot", Times::one()));
    return $this;
  }
  
  public function parent(?Times $times = null): RelDefBuilderAncestor {
    $this->elements->add(new RelPathElement("par", ($times === null)?Times::one():$times));
    return $this;
  }
  
  public function husband(): RelDefBuilderSpouse {
    $this->elements->add(new RelPathElement("hus", Times::one()));
    return $this;
  }
  
  public function wife(): RelDefBuilderSpouse {
    $this->elements->add(new RelPathElement("wif", Times::one()));
    return $this;
  }
  
  public function spouse(): RelDefBuilderSpouse {
    $this->elements->add(new RelPathElement("spo", Times::one()));
    return $this;
  }

  public function son(): RelDefBuilderDescendant {
    $this->elements->add(new RelPathElement("son", Times::one()));
    return $this;
  }
  
  public function daughter(): RelDefBuilderDescendant {
    $this->elements->add(new RelPathElement("dau", Times::one()));
    return $this;
  }
  
  public function child(?Times $times = null): RelDefBuilderDescendant {
    $this->elements->add(new RelPathElement("chi", ($times === null)?Times::one():$times));
    return $this;
  }
  
  public function brother(): RelDefBuilderSibling {
    $this->elements->add(new RelPathElement("bro", Times::one()));
    return $this;
  }
  
  public function sister(): RelDefBuilderSibling {
    $this->elements->add(new RelPathElement("sis", Times::one()));
    return $this;
  }
  
  public function sibling(): RelDefBuilderSibling {
    $this->elements->add(new RelPathElement("sib", Times::one()));
    return $this;
  }
  
  public function is(string $nominative, ?string $genitive): RelDef {
    return new RelDef($this->sex, $this->elements, $nominative, $genitive);
  }
}

<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Modules;

use Exception;
use Fisharebest\Webtrees\Functions\Functions;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;

class RelationshipPath {
    
  protected $sex;
  protected $from;
  protected $elements;
  protected $oldStylePath = null;
  protected $key = null;
  
  public function sex(): string {
    return $this->sex;
  }
  
  public function from(): ?Individual {
    return $this->from;
  }
  
  public function isEmpty(): bool {
    return ($this->size() === 0);
  }
  
  public function size(): int {
    return $this->elements->count();
  }
  
  public function first(): ?RelationshipPathElement {    
    return $this->elements->first();
  }
  
  public function last(): ?RelationshipPathElement {    
    return $this->elements->last();
  }
  
  /**
   * 
   * @return string unique key suitable e.g. for caching
   */
  public function key(): string {
    if ($this->key === null) {
      $this->key = ($this->from === null)?$sex:$this->from->xref() . '|';
      $this->key .= implode('|', $this->elements
              ->map(static function (RelationshipPathElement $element): string {
                  return $element->key();
              })
              ->all());
    }    
            
    return $this->key;
  }
  
  /**
   * 
   * @param string $sex
   * @param Individual|null $from
   * @param Collection<RelationshipPathElement> $elements
   * @throws Exception
   */
  public function __construct(
          string $sex,
          ?Individual $from,
          Collection $elements) {
    
    if (!preg_match('/^[MFU]$/', $sex)) {
      throw new Exception();
    }
    
    $this->sex = $sex;
    $this->from = $from;
    $this->elements = $elements;
  }
    
  protected function oldStylePath() {
    if ($this->oldStylePath === null) {
      $this->oldStylePath = implode('', $this->elements
            ->map(static function (RelationshipPathElement $element): string {
                return $element->rel();
            })
            ->all());
    }
    return $this->oldStylePath;
  }
  
  public function __toString() {
    return $this->oldStylePath();
  }
  
  public function splitBefore(int $index): RelationshipPathSplit {    
    if (($index < 0) || ($this->size() < $index)) {
      throw new \Exception("index out of bounds: " . $index  . " vs " . $this->size());
    }
    
    //edge case
    if ($index === 0) {
      //empty head
      return new RelationshipPathSplit(
              new RelationshipPath($this->sex, $this->from, new Collection()),
              $this);
    }
    
    //edge case
    if ($index === $this->size()) {
      //empty tail
      $last = $this->elements->last();      
      assert($last !== null); //$this->size() === 0 already checked via preceding checks
      
      return new RelationshipPathSplit(
              $this,
              new RelationshipPath($last->toSex(), $last->to(), new Collection()));
    }    
    
    /** @var RelationshipPath $head */
    $head = $this->elements->slice(0,$index);
    
    /** @var RelationshipPath $tail */
    $tail = $this->elements->slice($index);
    
    return new RelationshipPathSplit(
            new RelationshipPath($this->sex, $this->from, $head),
            new RelationshipPath($head->last()->toSex(), $head->last()->to(), $tail));
  }
           
  /**
   * 
   * @param RelationshipPathSplitPredicate|null $splitter
   * @return Collection<RelationshipPathSplit>
   */
  public function split(?RelationshipPathSplitPredicate $splitter = null): Collection {
    $splits = [];
    for ($i=1; $i<$this->elements->count(); $i++) {
      /** @var RelationshipPathSplit $split */
      $split = $this->splitBefore($i);
      
      if (($splitter === null) || ($splitter->allow($split))) {
        $splits []= $split;
      }
    }
    
    return new Collection($splits);
  }
  
  public function getRelationshipNameLegacy(): string {
    $last = $this->last();
    if ($last === null) {
      return '';
    }
    
    return Functions::getRelationshipNameFromPath(
            $this->oldStylePath(), 
            $this->from(), 
            $last->to());
  }
  
  /**
   * Convert a path (list of XREFs) to a modernized RelationshipPath.
   *
   * Return an empty array, if privacy rules prevent us viewing any node.
   *
   * @param Tree     $tree
   * @param string[] $path Alternately Individual / Family
   *
   * @return RelationshipPath|null
   */
  public static function create(Tree $tree, array $path): ?RelationshipPath {
    
    //adapted from 'oldStyleRelationshipPath'
    
    $spouse_codes = [
        'M' => 'hus',
        'F' => 'wif',
        'U' => 'spo',
    ];
    $parent_codes = [
        'M' => 'fat',
        'F' => 'mot',
        'U' => 'par',
    ];
    $child_codes = [
        'M' => 'son',
        'F' => 'dau',
        'U' => 'chi',
    ];
    $sibling_codes = [
        'M' => 'bro',
        'F' => 'sis',
        'U' => 'sib',
    ];
    
    if (count($path) < 1) {
      return null;
    }
    
    $from = Registry::individualFactory()->make($path[0], $tree);
    $relationships = [];

    for ($i = 1, $count = count($path); $i < $count; $i += 2) {
      $family = Registry::familyFactory()->make($path[$i], $tree);
      $prev = Registry::individualFactory()->make($path[$i - 1], $tree);
      $next = Registry::individualFactory()->make($path[$i + 1], $tree);
      if (preg_match('/\n\d (HUSB|WIFE|CHIL) @' . $prev->xref() . '@/', $family->gedcom(), $match)) {
        $rel1 = $match[1];
      } else {
        return null;
      }
      if (preg_match('/\n\d (HUSB|WIFE|CHIL) @' . $next->xref() . '@/', $family->gedcom(), $match)) {
        $rel2 = $match[1];
      } else {
        return null;
      }
      
      $code = null;
      if (($rel1 === 'HUSB' || $rel1 === 'WIFE') && ($rel2 === 'HUSB' || $rel2 === 'WIFE')) {
        $code = $spouse_codes[$next->sex()];
      } elseif (($rel1 === 'HUSB' || $rel1 === 'WIFE') && $rel2 === 'CHIL') {
        $code = $child_codes[$next->sex()];
      } elseif ($rel1 === 'CHIL' && ($rel2 === 'HUSB' || $rel2 === 'WIFE')) {
        $code = $parent_codes[$next->sex()];
      } elseif ($rel1 === 'CHIL' && $rel2 === 'CHIL') {
        $code = $sibling_codes[$next->sex()];
      }
      
      $relationships []= new RelationshipPathElement($code, $family, $next);
    }
    return new RelationshipPath($from->sex(), $from, new Collection($relationships));
  }
}

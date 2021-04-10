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
  
  public function sliceBefore(int $index, int $length): RelationshipPath {
    if (($index < 0) || ($this->size() < $index)) {
      throw new \Exception("index out of bounds: " . $index  . " vs " . $this->size());
    }
    
    if (($length < 1) || ($this->size() < $index + $length)) {
      throw new \Exception("length out of bounds: " . $length  . " vs " . $this->size());
    }
    
    //edge case not allowed here!
    
    /** @var RelationshipPath $head */
    $head = $this->elements->slice(0,$index);
    
    /** @var RelationshipPath $tail */
    $tail = $this->elements->slice($index, $length);
    
    $last = $head->last();
    if ($last === null) {
      //empty head
      $last = $this->elements->last();
    }
    
    return new RelationshipPath($last->toSex(), $last->to(), $tail);
  }
  
  public function splitBefore(int $index): RelationshipPathSplit {
    if (($index < 0) || ($this->size() < $index)) {
      throw new \Exception("index out of bounds: " . $index  . " vs " . $this->size());
    }
    
    //edge case
    if ($this->size() === 0) {
      return new RelationshipPathSplit(
            new RelationshipPath($this->sex, $this->from, new Collection()),
            new RelationshipPath($this->sex, $this->from, new Collection()));
    }   
    
    /** @var RelationshipPath $head */
    $head = $this->elements->slice(0,$index);
    
    /** @var RelationshipPath $tail */
    $tail = $this->elements->slice($index);
    
    $last = $head->last();
    if ($last === null) {
      //empty head
      $last = $this->elements->last();
    }
    
    return new RelationshipPathSplit(
            new RelationshipPath($this->sex, $this->from, $head),
            new RelationshipPath($last->toSex(), $last->to(), $tail));
  }
           
  /**
   * 
   * @param RelationshipPathSplitPredicate|null $splitter
   * @return Collection<Collection<RelationshipPathSplit>> sorted by splitter priority
   */
  public function split(?RelationshipPathSplitPredicate $splitter = null): Collection {
    $splits = [];
    for ($i=1; $i<$this->elements->count(); $i++) {
      /** @var RelationshipPathSplit $split */
      $split = $this->splitBefore($i);
      
      $priority = 1;
      if ($splitter !== null) {
        $priority = $splitter->prioritize($split);
      }
      
      if ($priority > 0) {
        if (!array_key_exists($priority, $splits)) {
          $splits[$priority] = new Collection();
        }
        $splits[$priority]->add($split);
      }
    }
    
    //sort by key, reversed
    krsort($splits);
    
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
   * @param Tree     $tree
   * @param string[] $path Alternately Individual / Family
   *
   * @return RelationshipPath|null null if privacy rules prevent us viewing any node.
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

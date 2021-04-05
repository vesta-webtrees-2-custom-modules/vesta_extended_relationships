<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Illuminate\Support\Collection;

class RelDef {
    
  protected $sex;
  protected $elements;
  protected $nominative;
  protected $genitive;
  
  /**
   * 
   * @param string $sex
   * @param Collection<RelPathElement> $elements
   * @param string $nominative
   */
  public function __construct(
          string $sex,
          Collection $elements,
          string $nominative,
          ?string $genitive) {
    
    $this->sex = $sex;
    $this->elements = $elements;
    $this->nominative = $nominative;
    $this->genitive = $genitive;
  }
  
  /**
   * 
   * @param string $sex
   * @param string $path
   * @return Collection<MatchedPath>
   */
  public function matchPath(
          string $sex, 
          string $path): ?Collection {
    
    //error_log("RelDef: ". print_r($this->elements, true));
    //error_log("RelDef matchPath: ". $sex . '/' . $path);
    
    if (!preg_match('/^[MFU]$/', $sex)) {
      return null;
    }
    
    if (!preg_match('/^(mot|fat|par|hus|wif|spo|son|dau|chi|bro|sis|sib)+$/', $path)) {
      return null;
    }
    
    switch ($this->sex) {
      case "M":
      case "F":
      case "U":
        if ($sex !== $this->sex) {
          return null;
        }
      default:
        break;
    }
    
    $currentMatchedPaths = [];
    $currentMatchedPaths []= new MatchedPartialPath('', $sex, $path, []);
    
    foreach ($this->elements as $element) {
      $nextMatchedPaths = [];
      foreach ($currentMatchedPaths as $currentMatchedPath) {
        $next = $element->matchPath(
                $currentMatchedPath->matchedPath(),
                $currentMatchedPath->remainingPath(), 
                $currentMatchedPath->refs())->all();
        
        $nextMatchedPaths = array_merge(
                $nextMatchedPaths,
                $next);
      }
      $currentMatchedPaths = $nextMatchedPaths;
      
      if (empty($currentMatchedPaths)) {
        //functionally not required but pointless to continue
        return null;
      } 
    }

    $ret = [];
    foreach ($currentMatchedPaths as $currentMatchedPath) {
      $ret []= new MatchedPath(
              $currentMatchedPath->matchedPath(), 
              $currentMatchedPath->remainingSex(), 
              $currentMatchedPath->remainingPath(), 
              $this->nominative($currentMatchedPath->refs()), 
              $this->genitive($currentMatchedPath->refs()));
    }
    
    return new Collection($ret);
  }
  
  protected function nominative(array $refs): string {
    $args = [];
    foreach ($refs as $ref) {      
      $args []= "" . ($ref->value() + $ref->ref()->offset());
    }
    return empty($args)?$this->nominative:sprintf($this->nominative, ...$args);
  }
  
  protected function genitive(array $refs): ?string {
    
    $args = [];
    foreach ($refs as $ref) {
      $args []= "" . ($ref->value() + $ref->ref()->offset());
    }
    return ($this->genitive == null)?
      null:
      empty($args)?$this->genitive:sprintf($this->genitive, ...$args);
  }
}

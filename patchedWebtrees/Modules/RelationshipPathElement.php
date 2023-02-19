<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Modules;

use Exception;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Individual;

class RelationshipPathElement {
  
  const CODES = array(
      'fat' => 'M',
      'mot' => 'F',
      'par' => 'U',
      
      'hus' => 'M',
      'wif' => 'F',
      'spo' => 'U',
      
      'son' => 'M',
      'dau' => 'F',
      'chi' => 'U',
      
      'bro' => 'M',
      'sis' => 'F',
      'sib' => 'U');
   
  protected $rel;
  protected $family;
  protected $to;
  protected $key;
  
  public function rel(): string {
    return $this->rel;
  }
    
  public function relIsAnyParent(): bool {
    return (preg_match('/^(fat|mot|par)$/', $this->rel) === 1);
  }

  public function relIsAnySpouse(): bool {
    return (preg_match('/^(hus|wif|spo)$/', $this->rel) === 1);
  }
  
  public function relIsAnyChild(): bool {
    return (preg_match('/^(son|dau|chi)$/', $this->rel) === 1);
  }
  
  public function relIsAnySibling(): bool {
    return (preg_match('/^(bro|sis|sib)$/', $this->rel) === 1);
  }
  
  public function family(): ?Family {
    return $this->family;
  }
  
  public function toSex(): string {
    return $sex2 = self::CODES[$this->rel];
  }
  
  public function to(): ?Individual {
    return $this->to;
  }
  
  /**
   * 
   * @return string unique key suitable e.g. for caching
   */
  public function key(): string {
    return $this->key;
  }
  
  
  /**
   * 
   * @param string $rel
   * @param Family|null $family
   * @param Individual|null $to
   */
  public function __construct(
          string $rel,
          ?Family $family = null,
          ?Individual $to = null) {
    
    if (!preg_match('/^(mot|fat|par|hus|wif|spo|son|dau|chi|bro|sis|sib)$/', $rel)) {
      throw new Exception();
    }
    
    $this->rel = $rel;
    $this->family = $family;
    $this->to = $to;
    
    //issue #98: we have to use rel as part of key (always)
    //otherwise different paths to the same INDI get mixed up
    //while we're at it, semms safer to include FAM as well
    $this->key = $rel;
    if ($to !== null) {
        $this->key .= '>'.$to->xref();
    }
    if ($family !== null) {
        $this->key .= '>'.$family->xref();
    }
  }
}

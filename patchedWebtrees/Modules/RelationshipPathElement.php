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
  
  public function relIsAnySpouse(): bool {
    return (preg_match('/^(hus|wif|spo)$/', $this->rel) === 1);
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
          ?Family $family,
          ?Individual $to) {
    
    if (!preg_match('/^(mot|fat|par|hus|wif|spo|son|dau|chi|bro|sis|sib)$/', $rel)) {
      throw new Exception();
    }
    
    $this->rel = $rel;
    $this->family = $family;
    $this->to = $to;
    
    $this->key = ($to === null)?$rel:$to->xref();
  }
}

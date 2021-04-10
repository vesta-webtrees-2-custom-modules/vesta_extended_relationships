<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

class Times {
  
  protected $fixedCount;
  protected $minCount;
  protected $offset;
  protected $resolver;
  
  public function minTimes(): int {
    return ($this->fixedCount !== -1)?$this->fixedCount:$this->minCount;
  }
  
  /**
   * 
   * @return int -1 indicates unlimited
   */
  public function maxTimes(): int {
    return $this->fixedCount;
  }
  
  public function fixedCount(): int {
    return $this->fixedCount;
  }
  
  public function minCount(): int {
    if ($this->fixedCount !== -1) {
      throw new \Exception;
    }
    return $this->minCount;
  }
  
  public function offset(): int {
    return $this->offset;
  }
  
  public function resolver(): ?TimesResolver {
    return $this->resolver;
  }
  
  protected function __construct(
          int $fixedCount,
          int $minCount,
          int $offset = 0,
          ?TimesResolver $resolver = null) {
    
    $this->fixedCount = $fixedCount;
    $this->minCount = $minCount;
    $this->offset = $offset;
    $this->resolver = $resolver;
  }
  
  public static function one(): Times {
    return new Times(1, 1);
  }
  
  public static function fixed(int $count): Times {
    return new Times($count, $count);
  }
  
  /**
   * back-referenceable!
   * 
   * @param int $min
   * @param int $offset to be used for output only!
   * @return Times
   */
  public static function min(int $min, int $offset = 0): Times {
    return new Times(-1, $min, $offset);
  }
  
  /**
   * back-referenceable!
   * 
   * @param int $min
   * @param array[string] $resolver
   * @param string $resolverFallback
   * @return Times
   */
  public static function minWithResolver(int $min, array $resolver, string $resolverFallback): Times {
    return new Times(-1, $min, 0, new TimesResolver($resolver, $resolverFallback));
  }
}

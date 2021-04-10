<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

class TimesResolver {
  
  protected $resolver;
  protected $resolverFallback;
    
  public function resolve(int $input): string {
    if (array_key_exists($input, $this->resolver)) {
      return $this->resolver[$input];
    }
    return sprintf($this->resolverFallback, $input);
  }
  
  public function __construct(
          array $resolver = [], 
          string $resolverFallback = '%s') {
    
    $this->resolver = $resolver;
    $this->resolverFallback = $resolverFallback;
  }
}

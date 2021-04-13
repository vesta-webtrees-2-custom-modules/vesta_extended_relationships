<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Cissee\WebtreesExt\Modules\RelationshipPath;
use Illuminate\Support\Collection;

interface RelationshipPathMatcher {
  
  public function minTimes(): int;
  
  /**
   * 
   * @return int -1 indicates unlimited
   */
  public function maxTimes(): int;
  
  /**
   * 
   * @param int $matchedPathElements path already matched elsewhere
   * @param RelationshipPath $path remaining path
   * @param array $refs back-references
   * @return Collection<MatchedPartialPath> we may be able to match in different ways
   */
  public function matchPath(
          int $matchedPathElements,
          RelationshipPath $path, 
          array $refs): Collection;
}

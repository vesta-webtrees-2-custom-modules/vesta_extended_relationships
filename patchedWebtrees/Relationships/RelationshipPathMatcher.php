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
   * All params are to be transported, perhaps adjusted, to each returned MatchedPartialPath
   * 
   * @param int $matchedPathElements path already matched elsewhere
   * @param bool $matchedPathDependsOnRemainingPath
   * @param RelationshipPath $path remaining path
   * @param array $refs back-references
   * @return Collection<MatchedPartialPath> we may be able to match in different ways, or not at all
   */
  public function matchPath(
          int $matchedPathElements,
          bool $matchedPathDependsOnRemainingPath,
          RelationshipPath $path, 
          array $refs): Collection;
}

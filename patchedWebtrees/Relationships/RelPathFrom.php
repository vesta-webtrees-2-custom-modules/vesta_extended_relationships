<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Relationships;

use Fisharebest\Webtrees\Individual;

interface RelPathFrom {

  /**
   * 
   * @param string $sex
   * @param Individual|null $from
   * @return bool
   */
  public function matchFrom(
          string $sex,
          ?Individual $from): bool;
}

<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

interface DijkstraTiebreakerFunction {
    
    public function next(int $weight): void;
    
    public function conclude(): int;
}

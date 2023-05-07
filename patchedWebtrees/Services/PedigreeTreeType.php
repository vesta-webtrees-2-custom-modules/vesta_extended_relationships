<?php

namespace Cissee\WebtreesExt\Services;

//cannot use 'enum' in php 7, which is still supported by webtrees
class PedigreeTreeType {
    
    protected const FULL = 'FULL';
    protected const SKIP_REPEATED = 'SKIP_REPEATED';
    protected const SKIP_REPEATED_AND_NON_COLLAPSED = 'SKIP_REPEATED_AND_NON_COLLAPSED';
    protected const COMMON_ANCESTORS = 'COMMON_ANCESTORS';
    
    protected string $type;
    
    public function __construct(
        string $type) {
        
        $this->type = $type;
    }
    
    public static function full(): PedigreeTreeType {
        return new PedigreeTreeType(PedigreeTreeType::FULL);
    }
    
    public static function skipRepeated(): PedigreeTreeType {
        return new PedigreeTreeType(PedigreeTreeType::SKIP_REPEATED);
    }
    
    public static function skipRepeatedAndNonCollapsed(): PedigreeTreeType {
        return new PedigreeTreeType(PedigreeTreeType::SKIP_REPEATED_AND_NON_COLLAPSED);
    }
    
    public static function commonAncestors(): PedigreeTreeType {
        return new PedigreeTreeType(PedigreeTreeType::COMMON_ANCESTORS);
    }
}

<?php

namespace Cissee\WebtreesExt\Services;

//cannot use 'enum' in php 7, which is still supported by webtrees
class TreeNodeMarkupType {
    
    protected const CIRCULARITY = 'CIRCULARITY';
    protected const FIRST_REPEATED = 'FIRST_REPEATED';
    protected const OTHER_REPEATED = 'OTHER_REPEATED';
    protected const FIRST_LCA = 'FIRST_LCA';
    protected const OTHER_LCA = 'OTHER_LCA';
    
    protected string $type;
    
    public function typeClass(): string {
        return strtolower($this->type);
    }
    
    public function __construct(
        string $type) {
        
        $this->type = $type;
    }
    
    public static function circularity(): TreeNodeMarkupType {
        return new TreeNodeMarkupType(TreeNodeMarkupType::CIRCULARITY);
    }
    
    public static function firstRepeated(): TreeNodeMarkupType {
        return new TreeNodeMarkupType(TreeNodeMarkupType::FIRST_REPEATED);
    }
    
    public static function otherRepeated(): TreeNodeMarkupType {
        return new TreeNodeMarkupType(TreeNodeMarkupType::OTHER_REPEATED);
    }

    public static function firstLca(): TreeNodeMarkupType {
        return new TreeNodeMarkupType(TreeNodeMarkupType::FIRST_LCA);
    }
    
    public static function otherLca(): TreeNodeMarkupType {
        return new TreeNodeMarkupType(TreeNodeMarkupType::OTHER_LCA);
    }
}

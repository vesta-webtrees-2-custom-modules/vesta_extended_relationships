<?php

namespace Cissee\WebtreesExt\Services;

interface TreeNodeVisitor {
        
    //return true to abort processsing
    public function visitPreOrder(TreeNode $node): bool;
    
    public function visitPostOrder(TreeNode $node): void;
}

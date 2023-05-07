<?php

namespace Cissee\WebtreesExt\Services;

interface TreeNodeVisitor {
        
    //return whether to abort processing next
    public function visit(TreeNode $node): bool;
}

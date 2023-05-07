<?php

namespace Cissee\WebtreesExt\Services;

interface TreeNodeTransformer {
    
    public function transformPreOrder(
        TreeNode $node): TreeNode|null;
    
    public function transformPostOrder(
        TreeNode $node): TreeNode|null;
}

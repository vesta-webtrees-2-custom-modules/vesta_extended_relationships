<?php

namespace Cissee\WebtreesExt\Services;

use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Individual;
use Illuminate\Support\Collection;

class TreeNode {
    
    protected Individual|Family $record;
    protected int $generation;
    protected Collection $next;
    protected TreeNodeMarkup|null $markup;
    protected TreeNodeCOI|null $data;
      
    public function record(): Individual|Family {
        return $this->record;
    }
        
    public function generation(): int {
        return $this->generation;
    }
    
    public function next(): Collection {
        return $this->next;
    }
    
    public function markup(): TreeNodeMarkup|null {
        return $this->markup;
    }
    
    public function data(): TreeNodeCOI|null {
        return $this->data;
    }
    
    public function __construct(
        Individual|Family $record, 
        int $generation,
        Collection $next,
        TreeNodeMarkup|null $markup,
        TreeNodeCOI|null $data) {
        
        $this->record = $record;
        $this->generation = $generation;
        $this->next = $next;
        $this->markup = $markup;
        $this->data = $data;
    }
    
    public function withNext(
        Collection $next): TreeNode {
        
        return new TreeNode(
            $this->record(),
            $this->generation(),
            $next,
            $this->markup(),
            $this->data());
    }
    
    public function withMarkup(
        TreeNodeMarkup|null $markup): TreeNode {
        
        return new TreeNode(
            $this->record(),
            $this->generation(),
            $this->next(),
            $markup,
            $this->data());
    }
    
    public function withData(
        TreeNodeCOI|null $data): TreeNode {
        
        return new TreeNode(
            $this->record(),
            $this->generation(),
            $this->next(),
            $this->markup(),
            $data);
    }
    
    public function processPreOrder(
        TreeNodeVisitor $visitor): void {
        
        $abort = $visitor->visit($this);
        if ($abort) {
            return;
        }
            
        foreach ($this->next as $nextNode) {
            $nextNode->processPreOrder($visitor);
        }
    }
    
    public function transform(
        TreeNodeTransformer $transformer): TreeNode|null {
        
        $adjustedPre = 
            $transformer->transformPreOrder($this);
        
        if ($adjustedPre === null) {
            return null;
        }
        
        $next = [];
        foreach ($adjustedPre->next() as $nextNode) {
            $transformedNext = $nextNode->transform($transformer);
            if ($transformedNext !== null) {
                $next []= $transformedNext;
            }
        }
        
        $adjustedIn = 
            $adjustedPre->withNext(new Collection($next));
        
        $adjustedPost = 
            $transformer->transformPostOrder($adjustedIn);
        
        return $adjustedPost;
    }
}

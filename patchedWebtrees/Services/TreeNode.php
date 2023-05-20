<?php

namespace Cissee\WebtreesExt\Services;

use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Individual;
use Illuminate\Support\Collection;

class TreeNode {
    
    protected Individual|Family $record;
    protected int $generation;
    protected Collection $next;
    protected Collection $markups;
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
    
    public function markups(): Collection {
        return $this->markups;
    }
    
    public function data(): TreeNodeCOI|null {
        return $this->data;
    }
    
    public function __construct(
        Individual|Family $record, 
        int $generation,
        Collection $next,
        Collection $markups,
        TreeNodeCOI|null $data) {
        
        $this->record = $record;
        $this->generation = $generation;
        $this->next = $next;
        $this->markups = $markups;
        $this->data = $data;
    }
    
    public function withNext(
        Collection $next): TreeNode {
        
        return new TreeNode(
            $this->record(),
            $this->generation(),
            $next,
            $this->markups(),
            $this->data());
    }
    
    public function withMarkup(
        TreeNodeMarkup|null $markup): TreeNode {
        
        return new TreeNode(
            $this->record(),
            $this->generation(),
            $this->next(),
            $this->markups()->merge(new Collection([$markup])),
            $this->data());
    }
    
    public function replaceMarkups(
        Collection $markups): TreeNode {
        
        return new TreeNode(
            $this->record(),
            $this->generation(),
            $this->next(),
            $markups,
            $this->data());
    }
    
    public function withData(
        TreeNodeCOI|null $data): TreeNode {
        
        return new TreeNode(
            $this->record(),
            $this->generation(),
            $this->next(),
            $this->markups(),
            $data);
    }
    
    public function process(
        TreeNodeVisitor $visitor): void {
        
        $abort = $visitor->visitPreOrder($this);
        if ($abort) {
            return;
        }
            
        foreach ($this->next as $nextNode) {
            $nextNode->process($visitor);
        }
        
        $visitor->visitPostOrder($this);
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

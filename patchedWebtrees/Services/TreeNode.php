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
    protected TreeNodeCOI|null $coi;
    protected TreeNodeCOR|null $cor;
      
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
    
    public function coi(): TreeNodeCOI|null {
        return $this->coi;
    }
    
    public function cor(): TreeNodeCOR|null {
        return $this->cor;
    }
    
    public function __construct(
        Individual|Family $record, 
        int $generation,
        Collection $next,
        Collection $markups,
        TreeNodeCOI|null $coi,
        TreeNodeCOR|null $cor) {
        
        $this->record = $record;
        $this->generation = $generation;
        $this->next = $next;
        $this->markups = $markups;
        $this->coi = $coi;
        $this->cor = $cor;
    }
    
    public function withNext(
        Collection $next): TreeNode {
        
        return new TreeNode(
            $this->record(),
            $this->generation(),
            $next,
            $this->markups(),
            $this->coi(),
            $this->cor());
    }
    
    public function withMarkup(
        TreeNodeMarkup|null $markup): TreeNode {
        
        return new TreeNode(
            $this->record(),
            $this->generation(),
            $this->next(),
            $this->markups()->merge(new Collection([$markup])),
            $this->coi(),
            $this->cor());
    }
    
    public function replaceMarkups(
        Collection $markups): TreeNode {
        
        return new TreeNode(
            $this->record(),
            $this->generation(),
            $this->next(),
            $markups,
            $this->coi(),
            $this->cor());
    }
    
    public function withCoi(
        TreeNodeCOI|null $coi): TreeNode {
        
        return new TreeNode(
            $this->record(),
            $this->generation(),
            $this->next(),
            $this->markups(),
            $coi,
            $this->cor());
    }
    
    public function withCor(
        TreeNodeCOR|null $cor): TreeNode {
        
        return new TreeNode(
            $this->record(),
            $this->generation(),
            $this->next(),
            $this->markups(),
            $this->coi(),
            $cor);
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

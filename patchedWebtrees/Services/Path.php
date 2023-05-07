<?php

namespace Cissee\WebtreesExt\Services;

use Illuminate\Support\Collection;

class Path {
    
    protected string $ancestor;
    protected float $coiAncestor;
    protected int $length;
    protected Collection $vias;
      
    public function ancestor(): string {
        return $this->ancestor;
    }
    
    public function coiAncestor(): float {
        return $this->coiAncestor;
    }
    
    public function length(): int {
        return $this->length;
    }
    
    public function vias(): Collection {
        return $this->vias;
    }
    
    public function __construct(
        string $ancestor, 
        float $coiAncestor,
        int $length,
        Collection $vias) {
        
        $this->ancestor = $ancestor;
        $this->coiAncestor = $coiAncestor;
        $this->length = $length;
        $this->vias = $vias;
    }
    
    public static function init(
        string $ancestor,
        float $coiAncestor): Path {
        
        return new Path(
            $ancestor, 
            $coiAncestor,
            0, 
            new Collection());
    }
        
    public function expand(
        string|null $next): Path {
        
        $vias = $this->vias(); 
        if ($next !== null) {
            $vias = $vias->merge(new Collection([$next]));
        }
        
        return new Path(
            $this->ancestor(), 
            $this->coiAncestor(), 
            $this->length()+1, 
            $vias);
    }
    
    //coefficent of inbreeding
    //see e.g. https://genetic-genealogy.co.uk/Toc115570144.html
    public function coi(
        Path $other): float {
        
        if ($this->ancestor() !== $other->ancestor()) {
            //not a common ancestor
            return 0.0;
        }
        
        //sanity check
        if ($this->coiAncestor() !== $other->coiAncestor()) {
            throw new \Exception("inconsistent coiAncestor!");
        }
        
        $coiAncestor = $this->coiAncestor();
        
        //also skip if vias intersect
        //(because in that case there is a closer common ancestor)
        if ($this->vias()->intersect($other->vias())->isNotEmpty()) {
             return 0.0;
        }
        
        $exponent = ($this->length() + $other->length() + 1);
        
        /*
        error_log("this" . $this->length());
        error_log("other" . $other->length());
        error_log("via" . $this->ancestor());
        error_log("anc" . $coiAncestor);
        */
        
        return pow(0.5, $exponent) * (1 + $coiAncestor);
    }
}

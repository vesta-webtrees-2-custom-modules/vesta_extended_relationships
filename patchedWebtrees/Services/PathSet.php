<?php

namespace Cissee\WebtreesExt\Services;

use Illuminate\Support\Collection;

class PathSet {
    
    protected Collection $paths;
    protected float $coi;
      
    public function paths(): Collection {
        return $this->paths;
    }
    
    public function coi(): float {
        return $this->coi;
    }
    
    public function __construct(
        Collection $paths,
        float $coi) {
        
        $this->paths = $paths;
        $this->coi = $coi;
    }
    
    public static function leaf(
        string $ancestor): PathSet {
        
        return new PathSet(
            new Collection([Path::init($ancestor, 0.0)]), 
            0.0);
    }
    
    public function expand(
        PathSet|null $other,
        string|null $next): PathSet {

        ////////
        
        $nextCoi = 0.0;
        if ($other !== null) {
            foreach ($this->paths() as $path) {
                foreach ($other->paths() as $otherPath) {
                    $nextCoi += $path->coi($otherPath);
                }              
            }
        }
        
        ////////
        
        $nextPaths = $this->paths()->map(static function (Path $path) use ($next): Path {
                return $path->expand($next);
            });
            
        if ($other !== null) {
            $otherNextPaths = $other->paths()->map(static function (Path $path) use ($next): Path {
                return $path->expand($next);
            });
            
            $nextPaths = $nextPaths->merge($otherNextPaths);
        }
        
        if ($next !== null) {
            $nextPaths = $nextPaths->merge(new Collection([Path::init($next, $nextCoi)]));
        }
        
        return new PathSet($nextPaths, $nextCoi);
    }
}

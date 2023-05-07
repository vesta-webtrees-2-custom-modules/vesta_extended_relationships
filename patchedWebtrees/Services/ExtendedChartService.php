<?php

namespace Cissee\WebtreesExt\Services;

use Fisharebest\Webtrees\Elements\PedigreeLinkageType;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Individual;
use Illuminate\Support\Collection;

class ExtendedChartService {
    
    public function pedigreeTree(
        Individual $individual, 
        int|null $generations,
        PedigreeTreeType $type): TreeNode|null {
        
        return $this->pedigreeTrees(
            new Collection([$individual]),
            $generations,
            $type)->get($individual->xref());
    }
    
    /**
     *
     * @param Collection<Individual> $individuals
     * @param int|null $generations
     * @param  PedigreeTreeType $type
     *
     * @return Collection<string, TreeNode|null>
     */
    public function pedigreeTrees(
        Collection $individuals, 
        int|null $generations,
        PedigreeTreeType $type): Collection {
    
        if ($individuals->count() === 0) {
           return new Collection(); 
        }
        
        $fullTrees = new Collection();
        
        foreach ($individuals as $individual) {
            $path = new Collection();
            if ($generations === null) {
                $path->add($individual->xref());
            } //else no need to track path

            //in any case first build full tree
            //(we could skip repeated here already for the respective types,
            //but the respective data is anyway cached,
            //therefore performance wouldn't be significantly better)
            //(using DB::table('link') instead is problematic because it doesn't record PEDI) 
            $fullTree = $this->buildPedigreeTree(
                $individual, 
                1,
                $generations,
                $type,
                true,
                $path);
            
            $fullTrees->put($individual->xref(), $fullTree);
        }
        
        if (PedigreeTreeType::full() == $type) {
            return $fullTrees;
        }
        
   
        if (PedigreeTreeType::commonAncestors() == $type) {
            
            //cas are repeated in all trees
            //lcas are lowest per-path that are repeated in all other trees
            //we must check this from each tree to all others ('symmetrically'), or we'll miss paths
            //and add those up
            //(insufficient e.g. to only check first tree through all paths against second xrefs)
            $lcas = [];
            
            foreach ($fullTrees as $baseRef => $fullTree) {
                
                $collector = new class implements TreeNodeVisitor {

                    public static $xrefs;
                    public static $repeated;

                    public function visit(TreeNode $node): bool {
                        $xref = $node->record()->xref();

                        //#treeHasIndiOnly
                        if (array_key_exists($xref, self::$xrefs)) {
                            self::$repeated [$xref]= $xref;

                            return true;
                        }

                        self::$xrefs [$xref]= $xref;
                        return false;
                    }
                };

                $collector::$xrefs = [];
                $collector::$repeated = [];
                $fullTree->processPreOrder($collector);

                $xrefs = $collector::$xrefs;            

                //error_log("xrefs:" . print_r($xrefs, true));

                $partialLcas = null;
                
                foreach ($fullTrees as $offsetRef => $fullTree) {

                    if ($baseRef === $offsetRef) {
                        continue;
                    }
                    
                    $collector = new class implements TreeNodeVisitor {

                        public static $xrefs;
                        public static $repeated;

                        public function visit(TreeNode $node): bool {
                            $xref = $node->record()->xref();

                            //#treeHasIndiOnly
                            if (array_key_exists($xref, self::$xrefs)) {
                                self::$repeated [$xref]= $xref;

                                return true;
                            }

                            return false;
                        }
                    };

                    $collector::$xrefs = $xrefs;
                    $collector::$repeated = [];
                    $fullTree->processPreOrder($collector);

                    if ($partialLcas === null) {
                        $partialLcas = $collector::$repeated;
                    } else {
                        $partialLcas = array_intersect($partialLcas, $collector::$repeated);
                    }                
                }
                
                $lcas = array_merge($lcas, $partialLcas);
            }
                
            //error_log("lcas:" . print_r($lcas, true));
            
            if (sizeof($lcas) === 0) {
                return $fullTrees->map(static function (TreeNode $node): TreeNode|null {
                    return null;
                });
            }
            
            //we have lcas: prune all trees so that they only contain lca leaves, and markup those
            //prune: 
            //(P1) keep only paths leading to lcas
            //(P2) in general prune behind lcas (unless there are further lcas behind)
            
            //labels are global
            $lcasMarkups = [];
            
            $prunedTrees = new Collection();
            foreach ($fullTrees as $fullTree) {
                $transformer = new class implements TreeNodeTransformer {

                    public static $lcas;
                    public static $lcasMarkups;
                    public static $counter;

                    public function transformPreOrder(
                        TreeNode $node): TreeNode|null {

                        //markup lcas                    
                        $xref = $node->record()->xref();
                        if (array_key_exists($xref, self::$lcas)) {

                            if (array_key_exists($xref, self::$lcasMarkups)) {
                                $node = $node->withMarkup(new TreeNodeMarkup(
                                    TreeNodeMarkupType::otherLca(),
                                    self::$lcasMarkups[$xref]));
                            } else {
                                //labels are global
                                $label = "X".self::$counter++;
                                
                                self::$lcasMarkups[$xref] = $label;
                                $node = $node->withMarkup(new TreeNodeMarkup(
                                    TreeNodeMarkupType::firstLca(),
                                    self::$lcasMarkups[$xref]));
                            }
                        }

                        return $node;
                    }

                    public function transformPostOrder(
                        TreeNode $node): TreeNode|null {

                        //(P1)

                        $xref = $node->record()->xref();
                        if (array_key_exists($xref, self::$lcas)) {

                            //keep
                            return $node;
                        }
                        
                        //(P2)
                        if ($node->next()->isEmpty()) {
                            //prune leaf
                            return null;
                        }

                        //keep if on path to lca
                        return $node;
                    }
                };

                $transformer::$lcas = $lcas;
                //labels are global
                $transformer::$lcasMarkups = $lcasMarkups;
                $transformer::$counter = 1;

                $prunedTree = $fullTree->transform($transformer);
                
                //labels are global
                $lcasMarkups = $transformer::$lcasMarkups;
                
                $prunedTrees->add($prunedTree);
            }
            
            $fullTrees = $prunedTrees;
        }
        
        $finalTrees = new Collection();
        
        foreach ($fullTrees as $xref => $fullTree) {
            $finalTrees->put($xref, $this->processTree($fullTree, $type));
        }
        
        return $finalTrees;
    }
    
    public function processTree(
        TreeNode $fullTree, 
        PedigreeTreeType $type): TreeNode|null {

        //I. collect 'lowest' repeated

        //#treeHasIndiOnly
        //we could markup repeated INDI and FAM separately,
        //however, right now we only build and markup INDI (less confusing and easier to output in complex cases)

        $collector = new class implements TreeNodeVisitor {

            public static $xrefs;
            public static $repeated;

            public function visit(TreeNode $node): bool {
                $xref = $node->record()->xref();

                //#treeHasIndiOnly
                if (array_key_exists($xref, self::$xrefs)) {
                    self::$repeated [$xref]= $xref;

                    return true;
                }

                self::$xrefs [$xref]= $xref;
                return false;
            }
        };

        $collector::$xrefs = [];
        $collector::$repeated = [];
        $fullTree->processPreOrder($collector);

        //II. prune: 
        //(P1) if skipNonCollapsed, keep only paths leading to non-collapsed
        //(P2) in general prune behind repeateds (unless first occurrence)
                
        $skipNonCollapsed = false;
        if (PedigreeTreeType::skipRepeatedAndNonCollapsed() == $type) {
            $skipNonCollapsed = true;
        }
        
        //error_log("rep".print_r($collector::$repeated, true));

        $transformer = new class implements TreeNodeTransformer {

            public static $skipNonCollapsed;
            public static $repeated;
            public static $repeatedMarkups;
            public static $counter;

            public function transformPreOrder(
                TreeNode $node): TreeNode|null {

                //1. markup repeated (unless already marked up (i.e. as lca))
                if ($node->markup() === null) {                        
                    $xref = $node->record()->xref();
                    if (array_key_exists($xref, self::$repeated)) {

                        if (array_key_exists($xref, self::$repeatedMarkups)) {
                            $node = $node->withMarkup(new TreeNodeMarkup(
                                TreeNodeMarkupType::otherRepeated(),
                                self::$repeatedMarkups[$xref]));
                        } else {
                            $label = self::$counter++;
                            self::$repeatedMarkups[$xref] = $label;
                            $node = $node->withMarkup(new TreeNodeMarkup(
                                TreeNodeMarkupType::firstRepeated(),
                                self::$repeatedMarkups[$xref]));
                        }
                    }

                    //2. cutoff if otherRepeated for (P2)

                    if (($node->markup() !== null) && $node->markup()->type() == TreeNodeMarkupType::otherRepeated()) {                        
                        return $node->withNext(new Collection());
                    }
                }                

                return $node;
            }

            public function transformPostOrder(
                TreeNode $node): TreeNode|null {

                if (!self::$skipNonCollapsed) {
                    return $node;
                }
                
                //(P1)
                
                $xref = $node->record()->xref();
                if (array_key_exists($xref, self::$repeated)) {

                    //keep
                    return $node;
                }

                if ($node->next()->isEmpty()) {
                    //prune leaf
                    return null;
                }

                //keep if on path to firstRepeated
                return $node;
            }
        };

        $transformer::$skipNonCollapsed = $skipNonCollapsed;
        $transformer::$repeated = $collector::$repeated;
        $transformer::$repeatedMarkups = [];
        $transformer::$counter = 1;

        $prunedTree = $fullTree->transform($transformer);

        //error_log("rep2".print_r($transformer::$repeatedMarkups, true));

        //III. determine and markup coi
        
        $transformer2 = new class implements TreeNodeTransformer {

            public static $repeated;
            public static $pathSets;

            public function transformPreOrder(
                TreeNode $node): TreeNode|null {

                return $node;
            }

            public function transformPostOrder(
                TreeNode $node): TreeNode|null {

                $next = $node->next();

                $self = $node->record()->xref();

                //0, 1, or 2
                $left = null;
                $right = null;
                foreach ($next as $parent) {
                    if ($left === null) {
                        $left = $parent->record()->xref();
                    } else if ($right === null) {
                        $right = $parent->record()->xref();
                    } else {
                        throw new \Exception("more than 2 parents in family!");
                    }
                }

                //get stored PathSet for $self, or build
                $pathSetSelf = null;

                if (array_key_exists($self, self::$pathSets)) {    
                    $pathSetSelf = self::$pathSets[$self];
                } else {
                    $selfIsRepeated = array_key_exists($self, self::$repeated);                        

                    if ($left === null) {
                        //leaf: initialize
                        //(self must be a repeated node, we don't keep any other leaves)
                        if (!$selfIsRepeated) {
                            throw new \Exception();
                        }

                        $pathSetSelf = PathSet::leaf($self);

                    } else {                            
                        $pathSetLeft = self::$pathSets[$left];
                        $pathSetRight = null;

                        if ($right !== null) {
                            $pathSetRight = self::$pathSets[$right];
                        }

                        $pathSetSelf = $pathSetLeft->expand(
                                $pathSetRight, 
                                $selfIsRepeated?$self:null);
                    }

                    self::$pathSets[$self] = $pathSetSelf;
                }                    

                //set coi
                $coi = $pathSetSelf->coi();
                //error_log("coi for " . $self . " is " . $coi);

                if ($coi !== 0.0) {
                    $node = $node->withData(new TreeNodeCOI($coi));
                }

                return $node;
            }
        };

        $finalTree = $prunedTree;
        if ($skipNonCollapsed && ($prunedTree !== null)) {
            $transformer2::$repeated = $transformer::$repeated;
            $transformer2::$pathSets = [];
            $finalTree = $prunedTree->transform($transformer2);
        }

        return $finalTree;
    }
    
    public function buildPedigreeTree(
        Individual $individual, 
        int $generation,
        int|null $generations,
        PedigreeTreeType $type,
        bool $skipFamNodes,
        
        //depending on $generations, may have to track the path in order to prevent infinite loops
        Collection $path): TreeNode {

        if ($path->has($individual->xref())) {
            //circularity in GEDCOM!
            return new TreeNode(
                $individual,
                $generation,
                new Collection([]),
                new TreeNodeMarkup(TreeNodeMarkupType::circularity()),
                null);
        }
        
        $next = [];        

        if ($generation !== $generations) {
            //original method in ChartService picks first family regardless of 2 PEDI, 
            //we restrict to birth families (assuming birth if no PEDI is set)
            $family = $individual->childFamilies()
                ->filter(function (Family $x) use ($individual): bool {
                    $pedi = ExtendedChartService::getChildFamilyPedigreeLinkageType($individual, $x);
                    return PedigreeLinkageType::VALUE_BIRTH === $pedi;
                })
                /*
                ->sort(static function (Family $x, Family $y) use ($individual): int {
                    $sort_x = ExtendedChartService::getChildFamilyPedigreeLinkageTypeSortOrder($individual, $x);
                    $sort_y = ExtendedChartService::getChildFamilyPedigreeLinkageTypeSortOrder($individual, $y);
                    return $sort_x <=> $sort_y;
                })
                */
                ->first();
 
            if ($family instanceof Family) {
                
                if ($path->has($family->xref())) {
                    //circularity in GEDCOM!
                    $next []= new TreeNode(
                        $family,
                        $generation,
                        new Collection([]),
                        new TreeNodeMarkup(TreeNodeMarkupType::circularity()),
                        null);
                } else {
                    $parents = [];
                    
                    if ($family->husband() instanceof Individual) {
                        $parents []= $family->husband();
                    }

                    if ($family->wife() instanceof Individual) {
                        $parents []= $family->wife();
                    }

                    $nextInFamily = [];

                    foreach ($parents as $parent) {
                        $nextPath = $path;
                        if ($generations === null) {
                            $nextPath = $path->values();
                            $nextPath->add($family->xref());
                            $nextPath->add($parent->xref());
                        } //else no need to track path

                        $nextInFamily []= $this->buildPedigreeTree(
                            $parent, 
                            $generation + 1,
                            $generations,
                            $type,
                            $skipFamNodes,
                            $nextPath);
                    }

                    //if parents is empty: sibling-only family - weird case, we'll treat as leaf
                    //(would require special code to display properly)
                }
        
                if ($skipFamNodes) {
                    $next = $nextInFamily;
                } else {
                    $next []= new TreeNode(
                        $family,
                        $generation,
                        new Collection($nextInFamily),
                        null,
                        null); 
                }
            }
        } //else leaf via max generation cutoff
        
        return new TreeNode(
            $individual,
            $generation,
            new Collection($next),
            null,
            null);
    }
    
    public static function getChildFamilyPedigreeLinkageType(
        Individual $individual,
        Family $family): string {
        
        $fact = $individual->facts(['FAMC'])->first(static fn (Fact $fact): bool => $fact->target() === $family);

        if ($fact instanceof Fact) {
            $pedigree = $fact->attribute('PEDI');
        } else {
            $pedigree = '';
        }

        $values = [
            PedigreeLinkageType::VALUE_BIRTH   => PedigreeLinkageType::VALUE_BIRTH,
            PedigreeLinkageType::VALUE_ADOPTED => PedigreeLinkageType::VALUE_ADOPTED,
            PedigreeLinkageType::VALUE_FOSTER  => PedigreeLinkageType::VALUE_FOSTER,
            PedigreeLinkageType::VALUE_SEALING => PedigreeLinkageType::VALUE_SEALING,
            PedigreeLinkageType::VALUE_RADA    => PedigreeLinkageType::VALUE_RADA,
        ];

        return $values[$pedigree] ?? $values[PedigreeLinkageType::VALUE_BIRTH];
    }
    
    public static function getChildFamilyPedigreeLinkageTypeSortOrder(
        Individual $individual,
        Family $family): int {
        
        $fact = $individual->facts(['FAMC'])->first(static fn (Fact $fact): bool => $fact->target() === $family);

        if ($fact instanceof Fact) {
            $pedigree = $fact->attribute('PEDI');
        } else {
            $pedigree = '';
        }

        $values = [
            PedigreeLinkageType::VALUE_BIRTH   => 0,
            PedigreeLinkageType::VALUE_ADOPTED => 1,
            PedigreeLinkageType::VALUE_FOSTER  => 2,
            PedigreeLinkageType::VALUE_SEALING => 3,
            PedigreeLinkageType::VALUE_RADA    => 4,
        ];

        return $values[$pedigree] ?? $values[PedigreeLinkageType::VALUE_BIRTH];
    }
}

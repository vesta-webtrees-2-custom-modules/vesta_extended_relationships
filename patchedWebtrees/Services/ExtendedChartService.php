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
        
        $skipFamNodes = (PedigreeTreeType::commonAncestors() != $type);
        //also get rid of nextIndisVia in TreeNode
        
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
                $skipFamNodes,
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

                    public function visitPreOrder(TreeNode $node): bool {
                        $xref = $node->record()->xref();

                        //#treeHasIndiOnly
                        if (array_key_exists($xref, self::$xrefs)) {
                            self::$repeated [$xref]= $xref;

                            return true;
                        }

                        self::$xrefs [$xref]= $xref;
                        return false;
                    }
                    
                    public function visitPostOrder(TreeNode $node): void {}
                };

                $collector::$xrefs = [];
                $collector::$repeated = [];
                $fullTree->process($collector);

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

                        public function visitPreOrder(TreeNode $node): bool {
                            $xref = $node->record()->xref();

                            //#treeHasIndiOnly
                            if (array_key_exists($xref, self::$xrefs)) {
                                self::$repeated [$xref]= $xref;

                                return true;
                            }

                            return false;
                        }
                        
                        public function visitPostOrder(TreeNode $node): void {}
                    };

                    $collector::$xrefs = $xrefs;
                    $collector::$repeated = [];
                    $fullTree->process($collector);

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
            
            //we have lcas: preliminarily markup the respective nodes
            
            $prelimTrees = new Collection();
            foreach ($fullTrees as $xref => $fullTree) {
                $transformer = new class implements TreeNodeTransformer {

                    public static $lcas;
                    public static $counter;

                    public function transformPreOrder(
                        TreeNode $node): TreeNode|null {

                        //preliminarily markup lcas (this is only for later reference:
                        //it allows us to easily replace with final markup (or prune) without having to identify lca via full path)
                        $xref = $node->record()->xref();
                        if (array_key_exists($xref, self::$lcas)) {

                            $label = self::$counter++;
                                
                            $node = $node->withMarkup(new TreeNodeMarkup(
                                TreeNodeMarkupType::firstPathToLca(),
                                $label));
                        }

                        return $node;
                    }

                    public function transformPostOrder(
                        TreeNode $node): TreeNode|null {

                        return $node;
                    }
                };

                $transformer::$lcas = $lcas;
                $transformer::$counter = 1;

                $prelimTree = $fullTree->transform($transformer);
                
                $prelimTrees->put($xref, $prelimTree);
            }
            
            $fullTrees = $prelimTrees;
        }
        
        $finalTrees = new Collection();
        foreach ($fullTrees as $xref => $fullTree) {
            $finalTrees->put($xref, $this->processTree($fullTree, $type));
        }    
                
        //IIIb. determine paths to lcas, re-markup lcas (also 'INDI behind FAM lca'), also cor
        //prune trees behind 'lca paths' (except for those 'INDI behind FAM lca')
        //(algorithm is similar to III.)
        
        if (PedigreeTreeType::commonAncestors() == $type) {
            $finalTreesWithFinalMarkup = new Collection();
            
            //A. collect all paths (by xref and label)
            $pathsToLcasByXrefByTree = [];
            $pathsToLcasByLabelByTree = [];
            
            foreach ($finalTrees as $xref => $finalTree) {                
                
                $collector = new class implements TreeNodeVisitor {

                    public static $lcas;
                    public static $currentPath;
                    public static $pathsToLcasByXref;
                    public static $pathsToLcasByLabel;

                    public function visitPreOrder(TreeNode $node): bool {
                        $xref = $node->record()->xref();
                        self::$currentPath->push($node);
                        
                        if (in_array($xref, self::$lcas)) {
                            if (!array_key_exists($xref, self::$pathsToLcasByXref)) {
                                self::$pathsToLcasByXref[$xref] = new Collection();
                            }
                            
                            self::$pathsToLcasByXref[$xref]->push(
                                new PathToLca(self::$currentPath));
                            
                            $label = $node->markups()->first()->label();
                            
                            self::$pathsToLcasByLabel[$label] = 
                                new PathToLca(self::$currentPath);
                            
                        }

                        return false;
                    }

                    public function visitPostOrder(TreeNode $node): void {
                        $xref = $node->record()->xref();
                        self::$currentPath->pop();
                    }
                };

                $collector::$lcas = $lcas;
                $collector::$currentPath = new Collection();
                $collector::$pathsToLcasByXref = [];
                $collector::$pathsToLcasByLabel = [];
                $finalTree->process($collector);
                
                $pathsToLcasByXref = $collector::$pathsToLcasByXref;
                $pathsToLcasByXrefByTree[$xref] = $pathsToLcasByXref;
                
                $pathsToLcasByLabel = $collector::$pathsToLcasByLabel;
                $pathsToLcasByLabelByTree[$xref] = $pathsToLcasByLabel;
            }
            
            //B. compare paths between trees (skipping common lower lcas), re-enumerate globally
            
            //this doesn't make much sense otherwise:
            if (sizeof($individuals) !== 2) {
                throw new \Exception("commonAncestors only supported for exactly 2 individuals.");
            }
            
            $xrefFirst = null;
            $xrefSecond = null;
            foreach ($individuals as $individual) {
                if ($xrefFirst === null) {
                    $xrefFirst = $individual->xref();
                } else {
                    $xrefSecond = $individual->xref();
                }
            }
            
            //by label to keep 'running' i.e. ordered numbers
            $pathsToLcasByLabelFirst = $pathsToLcasByLabelByTree[$xrefFirst];
            $pathsToLcasByXrefSecond = $pathsToLcasByXrefByTree[$xrefSecond];
            
            $counter = 1;
            
            $finalMarkupFirst = [];
            $finalMarkupSecond = [];
            
            foreach ($pathsToLcasByLabelFirst as $label => $pathFirst) {
                $xref = $pathFirst->lca();
                $pathsSecond = $pathsToLcasByXrefSecond[$xref];
                
                foreach ($pathsSecond as $pathSecond) {
                    //keep unless they intersect on lower lca
                    $rd = $pathFirst->getRelationshipDataIfValid($pathSecond);
                    
                    /*
                    if ($rd !== null) {
                        error_log("keep!");    
                    } else {
                        error_log("drop---");    
                    }
                    */
                    
                    if ($rd !== null) {
                        $c = $counter++;

                        //use preliminary markup
                        $prelimFirst = $pathFirst->lcaNode()->markups()->first()->label();

                        if (!array_key_exists($prelimFirst, $finalMarkupFirst)) {
                            $finalMarkupFirst[$prelimFirst] = new Collection();
                        }

                        $finalMarkupFirst[$prelimFirst]->push(
                            new TreeNodeMarkup(
                                TreeNodeMarkupType::firstPathToLca(), 
                                "".$c,
                                $rd->description()));

                        $prelimSecond = $pathSecond->lcaNode()->markups()->first()->label();

                        if (!array_key_exists($prelimSecond, $finalMarkupSecond)) {
                            $finalMarkupSecond[$prelimSecond] = new Collection();
                        }

                        $finalMarkupSecond[$prelimSecond]->push(
                            new TreeNodeMarkup(
                                TreeNodeMarkupType::otherPathToLca(), 
                                "".$c,
                                $rd->descriptionInverse()));                            

                        /*
                        error_log("first: " . $prelimFirst . " rewrite " . $c);
                        error_log("second: " . $prelimSecond . " rewrite " . $c);
                        */
                    }
                }                                
            }
            
            //functionally ok but numbers are unordered
            /*
            $pathsToLcasByXrefFirst = $pathsToLcasByXrefByTree[$xrefFirst];
            $pathsToLcasByXrefSecond = $pathsToLcasByXrefByTree[$xrefSecond];
            
            $counter = 1;
            
            $finalMarkupFirst = [];
            $finalMarkupSecond = [];
            
            foreach ($pathsToLcasByXrefFirst as $xref => $pathsFirst) {
                $pathsSecond = $pathsToLcasByXrefSecond[$xref];
                
                foreach ($pathsFirst as $pathFirst) {
                    
                    foreach ($pathsSecond as $pathSecond) {
                        //keep unless they intersect on lower lca
                        $fp = $pathFirst->getFullPathIfValid($pathSecond);
                        if ($fp !== null) {
                            $c = $counter++;
                            
                            //use preliminary markup
                            $prelimFirst = $pathFirst->lcaNode()->markups()->first()->label();
                            
                            if (!array_key_exists($prelimFirst, $finalMarkupFirst)) {
                                $finalMarkupFirst[$prelimFirst] = new Collection();
                            }
                            
                            $finalMarkupFirst[$prelimFirst]->push(
                                new TreeNodeMarkup(TreeNodeMarkupType::firstPathToLca(), "".$c));
                            
                            $prelimSecond = $pathSecond->lcaNode()->markups()->first()->label();
                            
                            if (!array_key_exists($prelimSecond, $finalMarkupSecond)) {
                                $finalMarkupSecond[$prelimSecond] = new Collection();
                            }
                            
                            $finalMarkupSecond[$prelimSecond]->push(
                                new TreeNodeMarkup(TreeNodeMarkupType::otherPathToLca(), "".$c));                            
                            
                            //error_log("first: " . $prelimFirst . " rewrite " . $c);
                            //error_log("second: " . $prelimSecond . " rewrite " . $c);
                        }
                    }
                }                
            }
            */
                
            //C. replace preliminary markups with final markups (moving FAM markup to INDI behind)
            //and prune the trees
            foreach ($finalTrees as $xref => $finalTree) {
                
                $finalMarkup = ($xref === $xrefFirst)?
                    $finalMarkupFirst:
                    $finalMarkupSecond;
                
                $transformer = new class implements TreeNodeTransformer {

                    public static $finalMarkup;
                    public static $stackOfConferredMarkups;

                    protected static function getMarkups(
                        TreeNode $node): Collection {
                        
                        $markup = $node->markups()->first();
                        if ($markup !== null) {
                            if (array_key_exists($markup->label(), self::$finalMarkup)) {
                                $markups = self::$finalMarkup[$markup->label()];
                                return $markups;
                            } else {
                                //this is not a relevant lca (on this path)
                                //i.e. path is through lower relevant lca (on 'both sides')
                            }                            
                        }
                        
                        return new Collection();
                    }
                        
                    public function transformPreOrder(
                        TreeNode $node): TreeNode|null {
                        
                        if ($node->record() instanceof Family) {
                            throw new \Exception();
                        }
                        
                        $famMarkups = new Collection();
                        
                        //max one expected here
                        $nextFam = $node->next()->first();
                        
                        if ($nextFam !== null) {
                            //strip FAM node
                            $node = $node->withNext($nextFam->next());
                            
                            //but rescue its markups
                            $famMarkups = self::getMarkups($nextFam);
                        }
                        
                        //handle conferred FAM and own markups
                        $mergedMarkups = self::$stackOfConferredMarkups->last()
                            ->merge(self::getMarkups($node));
                            
                        //set final markups (if any), removing preliminary markup (if any)
                        $node = $node = $node->replaceMarkups($mergedMarkups);
                                                
                        //stash FAM markups (unconditionally for easier consistency)
                        self::$stackOfConferredMarkups->push($famMarkups);                            

                        return $node;
                    }
                    
                    public function transformPostOrder(
                        TreeNode $node): TreeNode|null {

                        //cleanup markups
                        self::$stackOfConferredMarkups->pop();
                        
                        if ($node->markups()->isNotEmpty()) {
                            //keep
                            return $node;
                        }
                        
                        if ($node->next()->isEmpty()) {
                            //prune leaf
                            return null;
                        }

                        //keep if on path to marked-up node
                        return $node;
                    }
                };

                $transformer::$finalMarkup = $finalMarkup;
                
                //init with empty markups for root node
                $transformer::$stackOfConferredMarkups = new Collection([new Collection()]);

                $finalTreeWithFinalMarkup = $finalTree->transform($transformer);
                
                $finalTreesWithFinalMarkup->put($xref, $finalTreeWithFinalMarkup);
            }
            
            $finalTrees = $finalTreesWithFinalMarkup;
        }
        
        return $finalTrees;
    }
    
    public function processTree(
        TreeNode $fullTree, 
        PedigreeTreeType $type): TreeNode|null {

        //don't do any of this for commonAncestors(): we want to enumerate the paths
        if (PedigreeTreeType::commonAncestors() == $type) {
            return $fullTree;
        }
        
        //I. collect 'lowest' repeated

        //#treeHasIndiOnly
        //we could markup repeated INDI and FAM separately,
        //however, right now we only build and markup INDI (less confusing and easier to output in complex cases)

        $collector = new class implements TreeNodeVisitor {

            public static $xrefs;
            public static $repeated;

            public function visitPreOrder(TreeNode $node): bool {
                $xref = $node->record()->xref();

                //#treeHasIndiOnly
                if (array_key_exists($xref, self::$xrefs)) {
                    self::$repeated [$xref]= $xref;

                    return true;
                }

                self::$xrefs [$xref]= $xref;
                return false;
            }
            
            public function visitPostOrder(TreeNode $node): void {}
        };

        $collector::$xrefs = [];
        $collector::$repeated = [];
        $fullTree->process($collector);
        
        $markupAndPruneBehindRepeateds = false;
        if (PedigreeTreeType::skipRepeated() == $type) {
            $markupAndPruneBehindRepeateds = true;
        }
        if (PedigreeTreeType::skipRepeatedAndNonCollapsed() == $type) {
            $markupAndPruneBehindRepeateds = true;
        }
        
        //II. prune: 
        //(P1) if skipNonCollapsed, keep only paths leading to non-collapsed
        //(P2) if pruneBehindRepeateds, do it (unless first occurrence)
                
        $skipNonCollapsed = false;
        if (PedigreeTreeType::skipRepeatedAndNonCollapsed() == $type) {
            $skipNonCollapsed = true;
        }

        $prunedTree = $fullTree;
        if ($markupAndPruneBehindRepeateds) {
            
            $transformer = new class implements TreeNodeTransformer {

                public static $skipNonCollapsed;
                public static $repeated;
                public static $repeatedMarkups;
                public static $counter;

                public function transformPreOrder(
                    TreeNode $node): TreeNode|null {

                    //1. markup repeated (unless already marked up (i.e. as lca))
                    if ($node->markups()->isEmpty()) {                        
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

                        if (($node->markups()->isNotEmpty()) && $node->markups()->first()->type() == TreeNodeMarkupType::otherRepeated()) {                        
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
        }

        //error_log("rep2".print_r($transformer::$repeatedMarkups, true));

        //III. determine and markup coi

        $finalTree = $prunedTree;
        if ($skipNonCollapsed && ($prunedTree !== null)) {
                    
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
                new Collection([new TreeNodeMarkup(TreeNodeMarkupType::circularity())]),
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
                        new Collection([new TreeNodeMarkup(TreeNodeMarkupType::circularity())]),
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
                        new Collection(),
                        null); 
                }
            }
        } //else leaf via max generation cutoff
        
        return new TreeNode(
            $individual,
            $generation,
            new Collection($next),
            new Collection(),
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

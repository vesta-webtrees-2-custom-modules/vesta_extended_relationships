<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Modules;

use Cissee\WebtreesExt\Relationships\DefaultRelAlgorithm;
use Cissee\WebtreesExt\Relationships\DefaultRelPathJoiner;
use Cissee\WebtreesExt\Relationships\RelDefs;
use Cissee\WebtreesExt\Relationships\RelPath;
use Cissee\WebtreesExt\Relationships\Times;
use Fisharebest\Webtrees\Module\AbstractModule;
use Illuminate\Support\Collection;

class LanguageEnglishExt extends AbstractModule implements ModuleLanguageExtInterface {
  
  public function getRelationshipName(
          RelationshipPath $path): string {
    
    //documentation:
    //priority of splits vs minimize number
    //
    //joiner
    
    //modified splitting!
    $splitter = new class implements RelationshipPathSplitPredicate {
        public function prioritize(RelationshipPathSplit $split): int {
          
          //prefer splits resulting in common ancestor-based subpaths
          if (RelationshipPathSplitUtils::isNextToSpouse($split)) {
            return 3;
          }

          //then, splits without a sibling rel
          //(mainly for performance reasons)
          if (RelationshipPathSplitUtils::isAscentToDescent($split)) {
            return 2;
          }
          
          return 1;
        }
    };
            
    $algorithm = new DefaultRelAlgorithm(
            $splitter, 
            true); //minimize number of splits
    
    $joiner = new DefaultRelPathJoiner();
    
    return $algorithm->getRelationshipName(
            self::defs(),
            $joiner,
            $path);
  }
  
  public static function defs(): RelDefs {
    
    //documentation:
    
    //only add customary terms here - do not use this to control how complex rels are generated.
    //use RelAlgorithm for that!
    
    //order is relevant
    
    //see RelDefBuilder*Axis for available methods
    //(split into 4 interfaces because not all combinations are required)
    
    $defs = [];
    
    $defs []= RelPath::any()->adoptiveFather()->is('adoptive father');
    $defs []= RelPath::any()->adoptiveMother()->is('adoptive mother');
    $defs []= RelPath::any()->adoptiveParent()->is('adoptive parent');
    
    $defs []= RelPath::any()->fosterFather()->is('foster father');
    $defs []= RelPath::any()->fosterMother()->is('foster mother');
    $defs []= RelPath::any()->fosterParent()->is('foster parent');
    
    $defs []= RelPath::any()->father()->is('father');
    $defs []= RelPath::any()->mother()->is('mother');
    $defs []= RelPath::any()->parent()->is('parent');

    ////////
    
    $defs []= RelPath::any()->husband()->is('husband');
    $defs []= RelPath::any()->wife()->is('wife');
    $defs []= RelPath::any()->spouse()->is('spouse');

    $defs []= RelPath::any()->fiance()->is('fiance');
    $defs []= RelPath::any()->fiancee()->is('fiancée');
    $defs []= RelPath::any()->betrothed()->is('fiancé(e)');

    $defs []= RelPath::any()->exHusband()->is('ex-husband');
    $defs []= RelPath::any()->exWife()->is('ex-wife');
    $defs []= RelPath::any()->exSpouse()->is('ex-spouse');

    $defs []= RelPath::any()->partner()->is('partner');
    
    ////////
    
    $defs []= RelPath::any()->adoptiveSon()->is('adoptive son');
    $defs []= RelPath::any()->adoptiveDaughter()->is('adoptive daughter');
    $defs []= RelPath::any()->adoptiveChild()->is('adoptive child');
    
    $defs []= RelPath::any()->fosterSon()->is('foster son');
    $defs []= RelPath::any()->fosterDaughter()->is('foster daughter');
    $defs []= RelPath::any()->fosterChild()->is('foster child');
    
    $defs []= RelPath::any()->son()->is('son');
    $defs []= RelPath::any()->daughter()->is('daughter');
    $defs []= RelPath::any()->child()->is('child');
    
    ////////
    
    $defs []= RelPath::any()->twinBrother()->is('twin brother');
    $defs []= RelPath::any()->twinSister()->is('twin sister');
    $defs []= RelPath::any()->twinSibling()->is('twin sibling');    
    
    $defs []= RelPath::any()->brother()->is('brother');
    $defs []= RelPath::any()->sister()->is('sister');
    $defs []= RelPath::any()->sibling()->is('sibling');
    
    ////////

    $defs []= RelPath::any()->spouse()->father()->is('father-in-law');
    $defs []= RelPath::any()->spouse()->mother()->is('mother-in-law');

    $defs []= RelPath::any()->child()->husband()->is('son-in-law');
    $defs []= RelPath::any()->child()->wife()->is('daughter-in-law');
    
    $defs []= RelPath::any()->spouse()->brother()->is('brother-in-law');
    $defs []= RelPath::any()->sibling()->husband()->is('brother-in-law');
    $defs []= RelPath::any()->spouse()->sister()->is('sister-in-law');
    $defs []= RelPath::any()->sibling()->wife()->is('sister-in-law');
        
    ////////

    $defs []= RelPath::any()->parent()->son()->is('half-brother');
    $defs []= RelPath::any()->parent()->daughter()->is('half-sister');
    $defs []= RelPath::any()->parent()->child()->is('half-sibling');
    
    //TODO: make step-x relationships available/configurable?
    
    ////////
    
    $defs []= RelPath::any()->father()->father()->is('paternal grandfather');
    $defs []= RelPath::any()->mother()->father()->is('maternal grandfather');
    $defs []= RelPath::any()->parent()->father()->is('grandfather');
    $defs []= RelPath::any()->parent(Times::fixed(2))->father()->is('great-grandfather');
    $defs []= RelPath::any()->parent(Times::min(2))->parent()->father()->is('great ×%s grandfather');
    
    $defs []= RelPath::any()->father()->mother()->is('paternal grandmother');
    $defs []= RelPath::any()->mother()->mother()->is('maternal grandmother');
    $defs []= RelPath::any()->parent()->mother()->is('grandmother');
    $defs []= RelPath::any()->parent(Times::fixed(2))->mother()->is('great-grandmother');
    $defs []= RelPath::any()->parent(Times::min(2))->parent()->mother()->is('great ×%s grandmother');

    $defs []= RelPath::any()->parent()->parent()->is('grandparent');
    $defs []= RelPath::any()->parent(Times::fixed(2))->parent()->is('great-grandparent');
    $defs []= RelPath::any()->parent(Times::min(2))->parent()->parent()->is('great ×%s grandparent');

    ////////

    $defs []= RelPath::any()->child()->son()->is('grandson');
    $defs []= RelPath::any()->child(Times::fixed(2))->son()->is('great-grandson');
    $defs []= RelPath::any()->child(Times::min(2))->child()->son()->is('great ×%s grandson');
    
    $defs []= RelPath::any()->child()->daughter()->is('granddaughter');
    $defs []= RelPath::any()->child(Times::fixed(2))->daughter()->is('great-granddaughter');
    $defs []= RelPath::any()->child(Times::min(2))->child()->daughter()->is('great ×%s granddaughter');
    
    $defs []= RelPath::any()->child()->child()->is('grandchild');
    $defs []= RelPath::any()->child(Times::fixed(2))->child()->is('great-grandchild');
    $defs []= RelPath::any()->child(Times::min(2))->child()->child()->is('great ×%s grandchild');

    ////////

    $defs []= RelPath::any()->parent()->brother()->is('uncle');
    $defs []= RelPath::any()->parent(Times::fixed(2))->brother()->is('great-uncle');
    $defs []= RelPath::any()->parent(Times::min(3, -1))->brother()->is('great ×%s uncle');
    
    $defs []= RelPath::any()->parent()->sister()->is('aunt');
    $defs []= RelPath::any()->parent(Times::fixed(2))->sister()->is('great-aunt');
    $defs []= RelPath::any()->parent(Times::min(3, -1))->sister()->is('great ×%s aunt');
        
    ////////

    $defs []= RelPath::any()->sibling()->son()->is('nephew');
    $defs []= RelPath::any()->sibling()->child()->son()->is('great-nephew');
    $defs []= RelPath::any()->sibling()->child(Times::min(2))->son()->is('great ×%s nephew');

    $defs []= RelPath::any()->sibling()->daughter()->is('niece');
    $defs []= RelPath::any()->sibling()->child()->daughter()->is('great-niece');
    $defs []= RelPath::any()->sibling()->child(Times::min(2))->daughter()->is('great ×%s niece');
    
    ////////

    $resolver = [
        1 => 'first',
        2 => 'second',
        3 => 'third',
        4 => 'fourth',
        5 => 'fifth',
        6 => 'sixth',
        7 => 'seventh',
        8 => 'eighth',
        9 => 'ninth',
        10 => 'tenth'];
    
    $defs []= RelPath::any()->parent()->sibling()->child()->is('cousin');
    //IMPL NOTE: used as back-reference (i.e. count must match in '->child($ref)')
    $ref = Times::minWithResolver(2, $resolver, '%s×'); 
    $defs []= RelPath::any()->parent($ref)->sibling()->child($ref)->is('%s cousin');
    
    ////////
    
    $ref = Times::minWithResolver(1, $resolver, '%s×'); 
    $defs []= RelPath::any()->parent()->parent($ref)->sibling()->child($ref)->is('%s cousin once removed ascending');
    $defs []= RelPath::any()->parent(Times::fixed(2))->parent($ref)->sibling()->child($ref)->is('%s cousin twice removed ascending');
    
    //IMPL NOTE: multiple 'times' instances are assigned to the term in the order they are first encountered
    $defs []= RelPath::any()->parent(Times::min(3))->parent($ref)->sibling()->child($ref)->is('%2$s cousin %1$s times removed ascending');
    
    ////////

    $defs []= RelPath::any()->parent($ref)->sibling()->child($ref)->child()->is('%s cousin once removed descending');
    $defs []= RelPath::any()->parent($ref)->sibling()->child($ref)->child(Times::fixed(2))->is('%s cousin twice removed ascending');
    $defs []= RelPath::any()->parent($ref)->sibling()->child($ref)->child(Times::min(3))->is('%1$s cousin %2$s times removed descending');
    
    ////////
    
    return new RelDefs(new Collection($defs));
  }
}

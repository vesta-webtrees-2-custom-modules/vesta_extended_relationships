<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Modules;

use Cissee\WebtreesExt\Relationships\DefaultFullyMatchedPathJoiner;
use Cissee\WebtreesExt\Relationships\DefaultRelAlgorithm;
use Cissee\WebtreesExt\Relationships\RelDefBuilder;
use Cissee\WebtreesExt\Relationships\RelDefs;
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
    
    $joiner = new DefaultFullyMatchedPathJoiner();
    
    return $algorithm->getRelationshipName(
            self::defs(),
            $joiner,
            $path);
  }
  
  public static function defs(): RelDefs {
    
    //documentation:
    
    //only add customary terms here - do not use this to control how complex rels are generated.
    //use RelAlgorithm for that!
    
    //also, if used as as basis for additional languages:
    //this is not a translation file where you have to translate each single row.
    //
    //rather, the definitions themselves should be adjusted if necessary.
    
    //order is relevant: most specific case should come first (husband before spouse)
    //(the defs are tested in the given order until a match is found)
    
    //see RelDefBuilder*Axis for available methods
    //(split into 4 interfaces because not all combinations are required)
    
    //see other languages for special constructions
    //(e.g. based on total path length)
    
    $defs = [];
    
    $defs []= RelDefBuilder::def()->adoptiveFather()->is('adoptive father');
    $defs []= RelDefBuilder::def()->adoptiveMother()->is('adoptive mother');
    $defs []= RelDefBuilder::def()->adoptiveParent()->is('adoptive parent');
    
    $defs []= RelDefBuilder::def()->fosterFather()->is('foster father');
    $defs []= RelDefBuilder::def()->fosterMother()->is('foster mother');
    $defs []= RelDefBuilder::def()->fosterParent()->is('foster parent');
    
    $defs []= RelDefBuilder::def()->father()->is('father');
    $defs []= RelDefBuilder::def()->mother()->is('mother');
    $defs []= RelDefBuilder::def()->parent()->is('parent');

    ////////
    
    $defs []= RelDefBuilder::def()->husband()->is('husband');
    $defs []= RelDefBuilder::def()->wife()->is('wife');
    $defs []= RelDefBuilder::def()->spouse()->is('spouse');

    $defs []= RelDefBuilder::def()->fiance()->is('fiance');
    $defs []= RelDefBuilder::def()->fiancee()->is('fiancée');
    $defs []= RelDefBuilder::def()->betrothed()->is('fiancé(e)');

    $defs []= RelDefBuilder::def()->exHusband()->is('ex-husband');
    $defs []= RelDefBuilder::def()->exWife()->is('ex-wife');
    $defs []= RelDefBuilder::def()->exSpouse()->is('ex-spouse');

    $defs []= RelDefBuilder::def()->partner()->is('partner');
    
    ////////
    
    $defs []= RelDefBuilder::def()->adoptiveSon()->is('adoptive son');
    $defs []= RelDefBuilder::def()->adoptiveDaughter()->is('adoptive daughter');
    $defs []= RelDefBuilder::def()->adoptiveChild()->is('adoptive child');
    
    $defs []= RelDefBuilder::def()->fosterSon()->is('foster son');
    $defs []= RelDefBuilder::def()->fosterDaughter()->is('foster daughter');
    $defs []= RelDefBuilder::def()->fosterChild()->is('foster child');
    
    $defs []= RelDefBuilder::def()->son()->is('son');
    $defs []= RelDefBuilder::def()->daughter()->is('daughter');
    $defs []= RelDefBuilder::def()->child()->is('child');
    
    ////////
    
    $defs []= RelDefBuilder::def()->twinBrother()->is('twin brother');
    $defs []= RelDefBuilder::def()->twinSister()->is('twin sister');
    $defs []= RelDefBuilder::def()->twinSibling()->is('twin sibling');    
    
    $defs []= RelDefBuilder::def()->brother()->is('brother');
    $defs []= RelDefBuilder::def()->sister()->is('sister');
    $defs []= RelDefBuilder::def()->sibling()->is('sibling');
    
    ////////

    $defs []= RelDefBuilder::def()->spouse()->father()->is('father-in-law');
    $defs []= RelDefBuilder::def()->spouse()->mother()->is('mother-in-law');
    $defs []= RelDefBuilder::def()->child()->husband()->is('son-in-law');
    $defs []= RelDefBuilder::def()->child()->wife()->is('daughter-in-law');
    
    $defs []= RelDefBuilder::def()->spouse()->brother()->is('brother-in-law');
    $defs []= RelDefBuilder::def()->sibling()->husband()->is('brother-in-law');
    $defs []= RelDefBuilder::def()->spouse()->sister()->is('sister-in-law');
    $defs []= RelDefBuilder::def()->sibling()->wife()->is('sister-in-law');
     
    $defs []= RelDefBuilder::def()->exSpouse()->father()->is('ex-father-in-law');
    $defs []= RelDefBuilder::def()->exSpouse()->mother()->is('ex-mother-in-law');
    $defs []= RelDefBuilder::def()->child()->exHusband()->is('ex-son-in-law');
    $defs []= RelDefBuilder::def()->child()->exWife()->is('ex-daughter-in-law');
    
    $defs []= RelDefBuilder::def()->exSpouse()->brother()->is('ex-brother-in-law');
    $defs []= RelDefBuilder::def()->sibling()->exHusband()->is('ex-brother-in-law');
    $defs []= RelDefBuilder::def()->exSpouse()->sister()->is('ex-sister-in-law');
    $defs []= RelDefBuilder::def()->sibling()->exWife()->is('ex-sister-in-law');
    
    ////////

    $defs []= RelDefBuilder::def()->parent()->son()->is('half-brother');
    $defs []= RelDefBuilder::def()->parent()->daughter()->is('half-sister');
    $defs []= RelDefBuilder::def()->parent()->child()->is('half-sibling');
    
    //TODO: make step-x relationships available/configurable?
    
    ////////
    
    $defs []= RelDefBuilder::def()->father()->father()->is('paternal grandfather');
    $defs []= RelDefBuilder::def()->mother()->father()->is('maternal grandfather');
    $defs []= RelDefBuilder::def()->parent()->father()->is('grandfather');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->father()->is('great-grandfather');
    $defs []= RelDefBuilder::def()->parent(Times::min(2))->parent()->father()->is('great ×%s grandfather');
    
    $defs []= RelDefBuilder::def()->father()->mother()->is('paternal grandmother');
    $defs []= RelDefBuilder::def()->mother()->mother()->is('maternal grandmother');
    $defs []= RelDefBuilder::def()->parent()->mother()->is('grandmother');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->mother()->is('great-grandmother');
    $defs []= RelDefBuilder::def()->parent(Times::min(2))->parent()->mother()->is('great ×%s grandmother');

    $defs []= RelDefBuilder::def()->father()->parent()->is('paternal grandparent');
    $defs []= RelDefBuilder::def()->mother()->parent()->is('maternal grandparent');
    $defs []= RelDefBuilder::def()->parent()->parent()->is('grandparent');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->parent()->is('great-grandparent');
    $defs []= RelDefBuilder::def()->parent(Times::min(2))->parent()->parent()->is('great ×%s grandparent');

    ////////

    $defs []= RelDefBuilder::def()->stepFather()->is('step-father');
    $defs []= RelDefBuilder::def()->stepMother()->is('step-mother');
    $defs []= RelDefBuilder::def()->stepParent()->is('step-parent');
    
    $defs []= RelDefBuilder::def()->stepSon()->is('step-son');
    $defs []= RelDefBuilder::def()->stepDaughter()->is('step-daughter');
    $defs []= RelDefBuilder::def()->stepChild()->is('step-child');
    
    $defs []= RelDefBuilder::def()->stepBrother()->is('step-brother');
    $defs []= RelDefBuilder::def()->stepSister()->is('step-sister');
    $defs []= RelDefBuilder::def()->stepSibling()->is('step-sibling');
    
    ////////

    $defs []= RelDefBuilder::def()->child()->son()->is('grandson');
    $defs []= RelDefBuilder::def()->child(Times::fixed(2))->son()->is('great-grandson');
    $defs []= RelDefBuilder::def()->child(Times::min(2))->child()->son()->is('great ×%s grandson');
    
    $defs []= RelDefBuilder::def()->child()->daughter()->is('granddaughter');
    $defs []= RelDefBuilder::def()->child(Times::fixed(2))->daughter()->is('great-granddaughter');
    $defs []= RelDefBuilder::def()->child(Times::min(2))->child()->daughter()->is('great ×%s granddaughter');
    
    $defs []= RelDefBuilder::def()->child()->child()->is('grandchild');
    $defs []= RelDefBuilder::def()->child(Times::fixed(2))->child()->is('great-grandchild');
    $defs []= RelDefBuilder::def()->child(Times::min(2))->child()->child()->is('great ×%s grandchild');

    ////////

    $defs []= RelDefBuilder::def()->parent()->brother()->is('uncle');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->brother()->is('great-uncle');
    $defs []= RelDefBuilder::def()->parent(Times::min(3, -1))->brother()->is('great ×%s uncle');
    
    $defs []= RelDefBuilder::def()->parent()->sister()->is('aunt');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->sister()->is('great-aunt');
    $defs []= RelDefBuilder::def()->parent(Times::min(3, -1))->sister()->is('great ×%s aunt');
        
    ////////

    $defs []= RelDefBuilder::def()->sibling()->son()->is('nephew');
    $defs []= RelDefBuilder::def()->sibling()->child()->son()->is('great-nephew');
    $defs []= RelDefBuilder::def()->sibling()->child(Times::min(2))->son()->is('great ×%s nephew');

    $defs []= RelDefBuilder::def()->sibling()->daughter()->is('niece');
    $defs []= RelDefBuilder::def()->sibling()->child()->daughter()->is('great-niece');
    $defs []= RelDefBuilder::def()->sibling()->child(Times::min(2))->daughter()->is('great ×%s niece');
    
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
    
    $defs []= RelDefBuilder::def()->parent()->sibling()->child()->is('cousin');
    //IMPL NOTE: used as back-reference (i.e. count must match in '->child($ref)')
    $ref = Times::minWithResolver(2, $resolver, '%s×'); 
    $defs []= RelDefBuilder::def()->parent($ref)->sibling()->child($ref)->is('%s cousin');
    
    ////////
    
    $ref = Times::minWithResolver(1, $resolver, '%s×'); 
    $defs []= RelDefBuilder::def()->parent()->parent($ref)->sibling()->child($ref)->is('%s cousin once removed ascending');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->parent($ref)->sibling()->child($ref)->is('%s cousin twice removed ascending');
    
    //IMPL NOTE: multiple 'times' instances are assigned to the term in the order they are first encountered
    $defs []= RelDefBuilder::def()->parent(Times::min(3))->parent($ref)->sibling()->child($ref)->is('%2$s cousin %1$s times removed ascending');
    
    ////////

    $defs []= RelDefBuilder::def()->parent($ref)->sibling()->child($ref)->child()->is('%s cousin once removed descending');
    $defs []= RelDefBuilder::def()->parent($ref)->sibling()->child($ref)->child(Times::fixed(2))->is('%s cousin twice removed descending');
    $defs []= RelDefBuilder::def()->parent($ref)->sibling()->child($ref)->child(Times::min(3))->is('%1$s cousin %2$s times removed descending');
    
    ////////
    
    return new RelDefs(new Collection($defs));
  }
}

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

class LanguageDutchExt extends AbstractModule implements ModuleLanguageExtInterface {
  
  public function getRelationshipName(
          RelationshipPath $path): string {
    
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
    
    //overview:
    //http://www.voorouders.net/help/verwantschappen/
    
    $defs = [];
    
    $defs []= RelDefBuilder::def()->adoptiveFather()->is('adoptievader', 'van de adoptievader');
    $defs []= RelDefBuilder::def()->adoptiveMother()->is('adoptiemoeder', 'van de adoptiemoeder');
    $defs []= RelDefBuilder::def()->adoptiveParent()->is('adoptieouder', 'van de adoptieouder');
    
    $defs []= RelDefBuilder::def()->fosterFather()->is('pleegvader', 'van de pleegvader');
    $defs []= RelDefBuilder::def()->fosterMother()->is('pleegmoeder', 'van de pleegmoeder');
    $defs []= RelDefBuilder::def()->fosterParent()->is('pleegouder', 'van de pleegouder');
    
    $defs []= RelDefBuilder::def()->father()->is('vader', 'van de vader');
    $defs []= RelDefBuilder::def()->mother()->is('moeder', 'van de moeder');
    $defs []= RelDefBuilder::def()->parent()->is('ouder', 'van de ouder');

    ////////
    
    $defs []= RelDefBuilder::def()->husband()->is('echtgenoot', 'van de echtgenoot');
    $defs []= RelDefBuilder::def()->wife()->is('echtgenote', 'van de echtgenote');
    $defs []= RelDefBuilder::def()->spouse()->is('huwelijkspartner', 'van de huwelijkspartner');

    $defs []= RelDefBuilder::def()->exHusband()->is('ex-echtgenoot', 'van de ex-echtgenoot');
    $defs []= RelDefBuilder::def()->exWife()->is('ex-echtgenote', 'van de ex-echtgenote');
    $defs []= RelDefBuilder::def()->exSpouse()->is('ex-huwelijkspartner', 'van de ex-huwelijkspartner');

    $defs []= RelDefBuilder::def()->malePartner()->is('partner', 'van de partner');
    $defs []= RelDefBuilder::def()->femalePartner()->is('partner', 'van de partner');
    $defs []= RelDefBuilder::def()->partner()->is('partner', 'van de partner');

    ////////

    $defs []= RelDefBuilder::def()->adoptiveSon()->is('adoptiezoon', 'van de adoptiezoon');
    $defs []= RelDefBuilder::def()->adoptiveDaughter()->is('adoptiedochter', 'van de adoptiedochter');
    $defs []= RelDefBuilder::def()->adoptiveChild()->is('adoptiekind', 'van het adoptiekind');
    
    $defs []= RelDefBuilder::def()->fosterSon()->is('pleegzoon', 'van de pleegzoon');
    $defs []= RelDefBuilder::def()->fosterDaughter()->is('pleegdochter', 'van de pleegdochter');
    $defs []= RelDefBuilder::def()->fosterChild()->is('pleegkind', 'van het pleegkind');
    
    $defs []= RelDefBuilder::def()->son()->is('zoon', 'van de zoon');
    $defs []= RelDefBuilder::def()->daughter()->is('dochter', 'van de dochter');
    $defs []= RelDefBuilder::def()->child()->is('kind', 'van het kind');
    
    ////////
    
    $defs []= RelDefBuilder::def()->twinBrother()->is('tweelingbroer', 'van de tweelingbroer');
    $defs []= RelDefBuilder::def()->twinSister()->is('tweelingzus', 'van de tweelingzus');
    $defs []= RelDefBuilder::def()->twinSibling()->is('tweeling', 'van de tweeling');    

    $defs []= RelDefBuilder::def()->brother()->is('broer', 'van de broer');
    $defs []= RelDefBuilder::def()->sister()->is('zus', 'van de zus');
    $defs []= RelDefBuilder::def()->sibling()->is('broer of zus', 'van de broer of zus');
    
    ////////
    
    $defs []= RelDefBuilder::def()->spouse()->father()->is('schoonvader', 'van de schoonvader');
    $defs []= RelDefBuilder::def()->spouse()->mother()->is('schoonmoeder', 'van de schoonmoeder');
    $defs []= RelDefBuilder::def()->child()->husband()->is('schoonzoon', 'van de schoonzoon');
    $defs []= RelDefBuilder::def()->child()->wife()->is('schoondochter', 'van de schoondochter');
    
    $defs []= RelDefBuilder::def()->spouse()->brother()->is('zwager', 'van de zwager');
    $defs []= RelDefBuilder::def()->sibling()->husband()->is('zwager', 'van de zwager');
    $defs []= RelDefBuilder::def()->spouse()->sister()->is('schoonzus', 'van de schoonzus');
    $defs []= RelDefBuilder::def()->sibling()->wife()->is('schoonzus', 'van de schoonzus');
    
    $defs []= RelDefBuilder::def()->exSpouse()->father()->is('ex-schoonvader', 'van de ex-schoonvader');
    $defs []= RelDefBuilder::def()->exSpouse()->mother()->is('ex-schoonmoeder', 'van de ex-schoonmoeder');
    $defs []= RelDefBuilder::def()->child()->exHusband()->is('ex-schoonzoon', 'van de ex-schoonzoon');
    $defs []= RelDefBuilder::def()->child()->exWife()->is('ex-schoondochter', 'van de ex-schoondochter');
    
    $defs []= RelDefBuilder::def()->exSpouse()->brother()->is('ex-zwager', 'van de ex-zwager');
    $defs []= RelDefBuilder::def()->sibling()->exHusband()->is('ex-zwager', 'van de ex-zwager');
    $defs []= RelDefBuilder::def()->exSpouse()->sister()->is('ex-schoonzus', 'van de ex-schoonzus');
    $defs []= RelDefBuilder::def()->sibling()->exWife()->is('ex-schoonzus', 'van de ex-schoonzus');
    
    ////////

    $defs []= RelDefBuilder::def()->parent()->son()->is('halfbroer', 'van de halfbroer');
    $defs []= RelDefBuilder::def()->parent()->daughter()->is('halfzus', 'van de halfzus');
    
    $defs []= RelDefBuilder::def()->stepFather()->is('stiefvader', 'van de stiefvader');
    $defs []= RelDefBuilder::def()->stepMother()->is('stiefmoeder', 'van de stiefmoeder');
    $defs []= RelDefBuilder::def()->stepParent()->is('stiefouder', 'van de stiefouder');
    
    $defs []= RelDefBuilder::def()->stepSon()->is('stiefzoon', 'van de stiefzoon');
    $defs []= RelDefBuilder::def()->stepDaughter()->is('stiefdochter', 'van de stiefdochter');
    $defs []= RelDefBuilder::def()->stepChild()->is('stiefkind', 'van de stiefkind');
    
    $defs []= RelDefBuilder::def()->stepBrother()->is('stiefbroer', 'van de stiefbroer');
    $defs []= RelDefBuilder::def()->stepSister()->is('stiefzus', 'van de stiefzus');
    $defs []= RelDefBuilder::def()->stepSibling()->is('stiefbroer of -zus', 'van de stiefbroer of -zus');
    
    ////////
    
    $defs []= RelDefBuilder::def()->parent()->father()->is('grootvader', 'van de grootvader');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->father()->is('overgrootvader', 'van de overgrootvader');
    $defs []= RelDefBuilder::def()->parent(Times::min(2))->parent()->father()->is('%s×overgrootvader', 'van de %s×overgrootvader');
    
    $defs []= RelDefBuilder::def()->parent()->mother()->is('grootmoeder', 'van de grootmoeder');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->mother()->is('overgrootmoeder', 'van de overgrootmoeder');
    $defs []= RelDefBuilder::def()->parent(Times::min(2))->parent()->mother()->is('%s×overgrootmoeder', 'van de %s×overgrootmoeder');

    $defs []= RelDefBuilder::def()->parent()->parent()->is('grootouder', 'van de grootouder');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->parent()->is('overgrootouder', 'van de overgrootouder');
    $defs []= RelDefBuilder::def()->parent(Times::min(2))->parent()->parent()->is('%s×overgrootouder', 'van de %s×overgrootouder');

    ////////

    $defs []= RelDefBuilder::def()->child()->son()->is('kleinzoon', 'van de kleinzoon');
    $defs []= RelDefBuilder::def()->child(Times::fixed(2))->son()->is('achterkleinzoon', 'van de achterkleinzoon');
    $defs []= RelDefBuilder::def()->child(Times::min(2))->child()->son()->is('%s×achterkleinzoon', 'van de %s×achterkleinzoon');
    
    $defs []= RelDefBuilder::def()->child()->daughter()->is('kleindochter', 'van de kleindochter');
    $defs []= RelDefBuilder::def()->child(Times::fixed(2))->daughter()->is('achterkleindochter', 'van de achterkleindochter');
    $defs []= RelDefBuilder::def()->child(Times::min(2))->child()->daughter()->is('%s×achterkleindochter', 'van de %s×achterkleindochter');
    
    $defs []= RelDefBuilder::def()->child()->child()->is('kleinkind', 'van het kleinkind');
    $defs []= RelDefBuilder::def()->child(Times::fixed(2))->child()->is('achterkleinkind', 'van het achterkleinkind');
    $defs []= RelDefBuilder::def()->child(Times::min(2))->child()->child()->is('%s×achterkleinkind', 'van het %s×achterkleinkind');

    ////////

    $defs []= RelDefBuilder::def()->parent()->brother()->is('oom', 'van de oom');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->brother()->is('oudoom', 'van de oudoom');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(3))->brother()->is('oud-oudoom', 'van de oud-oudoom');
    $defs []= RelDefBuilder::def()->parent(Times::min(4, -2))->brother()->is('%s×oud-oudoom', 'van de %s×oud-oudoom');
    
    $defs []= RelDefBuilder::def()->parent()->sister()->is('tante', 'van de tante');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->sister()->is('oudtante', 'van de oudtante');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(3))->sister()->is('oud-oudtante', 'van de oud-oudtante');
    $defs []= RelDefBuilder::def()->parent(Times::min(4, -2))->sister()->is('%s×oud-oudtante', 'van de %s×oud-oudtante');
        
    ////////

    $defs []= RelDefBuilder::def()->sibling()->son()->is('neef', 'van de neef');
    $defs []= RelDefBuilder::def()->sibling()->child(Times::min(1, 3))->son()->is('achterneef %se graad', 'van de achterneef %se graad');

    $defs []= RelDefBuilder::def()->sibling()->daughter()->is('nicht', 'van de nicht');
    $defs []= RelDefBuilder::def()->sibling()->child(Times::min(1, 3))->daughter()->is('achternicht %se graad', 'van de achternicht %se graad');
    
    ////////

    //cousins
    
    $defs []= RelDefBuilder::def()->parent()->sibling()->son()->is('neef', 'van de neef');
    $defs []= RelDefBuilder::def()->parent()->sibling()->daughter()->is('nicht', 'van de nicht');
    
    //reference for total path length (available via '%1$s'), we add 1 for sibling rel which is actually two steps ('genetically')
    $ref = Times::min(4, 1);
    
    $defs []= RelDefBuilder::def()->peekTotalPathLength($ref)->parent(Times::min(2))->sibling()->son()->is('achterneef %1$se graad', 'van de achterneef %1$se graad');
    $defs []= RelDefBuilder::def()->peekTotalPathLength($ref)->parent(Times::min(2))->sibling()->daughter()->is('achternicht %1$se graad', 'van de achternicht %1$se graad');
        
    $defs []= RelDefBuilder::def()->peekTotalPathLength($ref)->parent(Times::min(1))->sibling()->child(Times::min(1))->son()->is('achterneef %1$se graad', 'van de achterneef %1$se graad');
    $defs []= RelDefBuilder::def()->peekTotalPathLength($ref)->parent(Times::min(1))->sibling()->child(Times::min(1))->daughter()->is('achternicht %1$se graad', 'van de achternicht %1$se graad');
    
    ////////
    
    return new RelDefs(new Collection($defs));
  }
}

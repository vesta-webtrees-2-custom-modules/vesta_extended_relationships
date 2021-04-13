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
    
    $joiner = new DefaultRelPathJoiner();
    
    return $algorithm->getRelationshipName(
            self::defs(),
            $joiner,
            $path);
  }
  
  public static function defs(): RelDefs {
    
    //overview:
    //http://www.voorouders.net/help/verwantschappen/
    
    $defs = [];
    
    $defs []= RelPath::any()->adoptiveFather()->is('adoptievader', 'van de adoptievader');
    $defs []= RelPath::any()->adoptiveMother()->is('adoptiemoeder', 'van de adoptiemoeder');
    $defs []= RelPath::any()->adoptiveParent()->is('adoptieouder', 'van de adoptieouder');
    
    $defs []= RelPath::any()->fosterFather()->is('pleegvader', 'van de pleegvader');
    $defs []= RelPath::any()->fosterMother()->is('pleegmoeder', 'van de pleegmoeder');
    $defs []= RelPath::any()->fosterParent()->is('pleegouder', 'van de pleegouder');
    
    $defs []= RelPath::any()->father()->is('vader', 'van de vader');
    $defs []= RelPath::any()->mother()->is('moeder', 'van de moeder');
    $defs []= RelPath::any()->parent()->is('ouder', 'van de ouder');

    ////////
    
    $defs []= RelPath::any()->husband()->is('echtgenoot', 'van de echtgenoot');
    $defs []= RelPath::any()->wife()->is('echtgenote', 'van de echtgenote');
    $defs []= RelPath::any()->spouse()->is('huwelijkspartner', 'van de huwelijkspartner');

    $defs []= RelPath::any()->exHusband()->is('ex-echtgenoot', 'van de ex-echtgenoot');
    $defs []= RelPath::any()->exWife()->is('ex-echtgenote', 'van de ex-echtgenote');
    $defs []= RelPath::any()->exSpouse()->is('ex-huwelijkspartner', 'van de ex-huwelijkspartner');

    $defs []= RelPath::any()->malePartner()->is('partner', 'van de partner');
    $defs []= RelPath::any()->femalePartner()->is('partner', 'van de partner');
    $defs []= RelPath::any()->partner()->is('partner', 'van de partner');

    ////////

    $defs []= RelPath::any()->adoptiveSon()->is('Adoptiezoon', 'van de Adoptiezoon');
    $defs []= RelPath::any()->adoptiveDaughter()->is('adoptiedochter', 'van de adoptiedochter');
    $defs []= RelPath::any()->adoptiveChild()->is('adoptiekind', 'van het adoptiekind');
    
    $defs []= RelPath::any()->fosterSon()->is('pleegzoon', 'van de pleegzoon');
    $defs []= RelPath::any()->fosterDaughter()->is('pleegdochter', 'van de pleegdochter');
    $defs []= RelPath::any()->fosterChild()->is('pleegkind', 'van het pleegkind');
    
    $defs []= RelPath::any()->son()->is('zoon', 'van de zoon');
    $defs []= RelPath::any()->daughter()->is('dochter', 'van de dochter');
    $defs []= RelPath::any()->child()->is('kind', 'van het kind');
    
    ////////
    
    $defs []= RelPath::any()->twinBrother()->is('tweelingbroer', 'van de tweelingbroer');
    $defs []= RelPath::any()->twinSister()->is('tweelingzus', 'van de tweelingzus');
    $defs []= RelPath::any()->twinSibling()->is('tweeling', 'van de tweeling');    

    $defs []= RelPath::any()->brother()->is('broer', 'van de broer');
    $defs []= RelPath::any()->sister()->is('zus', 'van de zus');
    $defs []= RelPath::any()->sibling()->is('broer of zus', 'van de broer of zus');
    
    ////////
    
    $defs []= RelPath::any()->spouse()->father()->is('schoonvader', 'van de schoonvader');
    $defs []= RelPath::any()->spouse()->mother()->is('schoonmoeder', 'van de schoonmoeder');
    $defs []= RelPath::any()->child()->husband()->is('schoonzoon', 'van de schoonzoon');
    $defs []= RelPath::any()->child()->wife()->is('schoondochter', 'van de schoondochter');
    
    $defs []= RelPath::any()->spouse()->brother()->is('zwager', 'van de zwager');
    $defs []= RelPath::any()->sibling()->husband()->is('zwager', 'van de zwager');
    $defs []= RelPath::any()->spouse()->sister()->is('schoonzus', 'van de schoonzus');
    $defs []= RelPath::any()->sibling()->wife()->is('schoonzus', 'van de schoonzus');
    
    $defs []= RelPath::any()->exSpouse()->father()->is('ex-schoonvader', 'van de ex-schoonvader');
    $defs []= RelPath::any()->exSpouse()->mother()->is('ex-schoonmoeder', 'van de ex-schoonmoeder');
    $defs []= RelPath::any()->child()->exHusband()->is('ex-schoonzoon', 'van de ex-schoonzoon');
    $defs []= RelPath::any()->child()->exWife()->is('ex-schoondochter', 'van de ex-schoondochter');
    
    $defs []= RelPath::any()->exSpouse()->brother()->is('ex-zwager', 'van de ex-zwager');
    $defs []= RelPath::any()->sibling()->exHusband()->is('ex-zwager', 'van de ex-zwager');
    $defs []= RelPath::any()->exSpouse()->sister()->is('ex-schoonzus', 'van de ex-schoonzus');
    $defs []= RelPath::any()->sibling()->exWife()->is('ex-schoonzus', 'van de ex-schoonzus');
    
    ////////

    $defs []= RelPath::any()->parent()->son()->is('halfbroer', 'van de halfbroer');
    $defs []= RelPath::any()->parent()->daughter()->is('halfzus', 'van de halfzus');
    
    $defs []= RelPath::any()->stepFather()->is('stiefvader', 'van de stiefvader');
    $defs []= RelPath::any()->stepMother()->is('stiefmoeder', 'van de stiefmoeder');
    $defs []= RelPath::any()->stepParent()->is('stiefouder', 'van de stiefouder');
    
    $defs []= RelPath::any()->stepSon()->is('stiefzoon', 'van de stiefzoon');
    $defs []= RelPath::any()->stepDaughter()->is('stiefdochter', 'van de stiefdochter');
    $defs []= RelPath::any()->stepChild()->is('stiefkind', 'van de stiefkind');
    
    $defs []= RelPath::any()->stepBrother()->is('stiefbroer', 'van de stiefbroer');
    $defs []= RelPath::any()->stepSister()->is('stiefzus', 'van de stiefzus');
    $defs []= RelPath::any()->stepSibling()->is('stiefbroer of -zus', 'van de stiefbroer of -zus');
    
    ////////
    
    $defs []= RelPath::any()->parent()->father()->is('grootvader', 'van de grootvader');
    $defs []= RelPath::any()->parent(Times::fixed(2))->father()->is('overgrootvader', 'van de overgrootvader');
    $defs []= RelPath::any()->parent(Times::min(2))->parent()->father()->is('%s×overgrootvader', 'van de %s×overgrootvader');
    
    $defs []= RelPath::any()->parent()->mother()->is('grootmoeder', 'van de grootmoeder');
    $defs []= RelPath::any()->parent(Times::fixed(2))->mother()->is('overgrootmoeder', 'van de overgrootmoeder');
    $defs []= RelPath::any()->parent(Times::min(2))->parent()->mother()->is('%s×overgrootmoeder', 'van de %s×overgrootmoeder');

    $defs []= RelPath::any()->parent()->parent()->is('grootouder', 'van de grootouder');
    $defs []= RelPath::any()->parent(Times::fixed(2))->parent()->is('overgrootouder', 'van de overgrootouder');
    $defs []= RelPath::any()->parent(Times::min(2))->parent()->parent()->is('%s×overgrootouder', 'van de %s×overgrootouder');

    ////////

    $defs []= RelPath::any()->child()->son()->is('kleinzoon', 'van de kleinzoon');
    $defs []= RelPath::any()->child(Times::fixed(2))->son()->is('achterkleinzoon', 'van de achterkleinzoon');
    $defs []= RelPath::any()->child(Times::min(2))->child()->son()->is('%s×achterkleinzoon', 'van de %s×achterkleinzoon');
    
    $defs []= RelPath::any()->child()->daughter()->is('kleindochter', 'van de kleindochter');
    $defs []= RelPath::any()->child(Times::fixed(2))->daughter()->is('achterkleindochter', 'van de achterkleindochter');
    $defs []= RelPath::any()->child(Times::min(2))->child()->daughter()->is('%s×achterkleindochter', 'van de %s×achterkleindochter');
    
    $defs []= RelPath::any()->child()->child()->is('kleinkind', 'van het kleinkind');
    $defs []= RelPath::any()->child(Times::fixed(2))->child()->is('achterkleinkind', 'van het achterkleinkind');
    $defs []= RelPath::any()->child(Times::min(2))->child()->child()->is('%s×achterkleinkind', 'van het %s×achterkleinkind');

    ////////

    $defs []= RelPath::any()->parent()->brother()->is('oom', 'van de oom');
    $defs []= RelPath::any()->parent(Times::fixed(2))->brother()->is('oudoom', 'van de oudoom');
    $defs []= RelPath::any()->parent(Times::fixed(3))->brother()->is('oud-oudoom', 'van de oud-oudoom');
    $defs []= RelPath::any()->parent(Times::min(4, -2))->brother()->is('%s×oud-oudoom', 'van de %s×oud-oudoom');
    
    $defs []= RelPath::any()->parent()->sister()->is('tante', 'van de tante');
    $defs []= RelPath::any()->parent(Times::fixed(2))->sister()->is('oudtante', 'van de oudtante');
    $defs []= RelPath::any()->parent(Times::fixed(3))->sister()->is('oud-oudtante', 'van de oud-oudtante');
    $defs []= RelPath::any()->parent(Times::min(4, -2))->sister()->is('%s×oud-oudtante', 'van de %s×oud-oudtante');
        
    ////////

    $defs []= RelPath::any()->sibling()->son()->is('neef', 'van de neef');
    $defs []= RelPath::any()->sibling()->child(Times::min(1, 3))->son()->is('achterneef %se graad', 'van de achterneef %se graad');

    $defs []= RelPath::any()->sibling()->daughter()->is('nicht', 'van de nicht');
    $defs []= RelPath::any()->sibling()->child(Times::min(1, 3))->daughter()->is('achternicht %se graad', 'van de achternicht %se graad');
    
    ////////

    //cousins
    
    $defs []= RelPath::any()->parent()->sibling()->son()->is('neef', 'van de neef');
    $defs []= RelPath::any()->parent()->sibling()->daughter()->is('nicht', 'van de nicht');
    
    //total path length, add one for sibling rel which is actually two steps ('genetically')
    $ref = Times::min(4, 1);
    
    $defs []= RelPath::any()->peekTotalPathLength($ref)->parent(Times::min(2))->sibling()->son()->is('achterneef %1$se graad', 'van de achterneef %1$se graad');
    $defs []= RelPath::any()->peekTotalPathLength($ref)->parent(Times::min(2))->sibling()->daughter()->is('achternicht %1$se graad', 'van de achternicht %1$se graad');
        
    $defs []= RelPath::any()->peekTotalPathLength($ref)->parent(Times::min(1))->sibling()->child(Times::min(1))->son()->is('achterneef %1$se graad', 'van de achterneef %1$se graad');
    $defs []= RelPath::any()->peekTotalPathLength($ref)->parent(Times::min(1))->sibling()->child(Times::min(1))->daughter()->is('achternicht %1$se graad', 'van de achternicht %1$se graad');
    
    ////////
    
    return new RelDefs(new Collection($defs));
  }
}

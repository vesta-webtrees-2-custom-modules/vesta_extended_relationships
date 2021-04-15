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

class LanguageGermanExt extends AbstractModule implements ModuleLanguageExtInterface {
  
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
    
    $defs = [];
    
    $defs []= RelDefBuilder::def()->adoptiveFather()->is('Adoptivvater', 'des Adoptivvaters');
    $defs []= RelDefBuilder::def()->adoptiveMother()->is('Adoptivmutter', 'der Adoptivmutter');
    $defs []= RelDefBuilder::def()->adoptiveParent()->is('Adoptiv-Elternteil', 'des Adoptiv-Elternteils');
    
    $defs []= RelDefBuilder::def()->fosterFather()->is('Pflegevater', 'des Pflegevaters');
    $defs []= RelDefBuilder::def()->fosterMother()->is('Pflegemutter', 'der Pflegemutter');
    $defs []= RelDefBuilder::def()->fosterParent()->is('Pflege-Elternteil', 'des Pflege-Elternteils');
    
    $defs []= RelDefBuilder::def()->father()->is('Vater', 'des Vaters');
    $defs []= RelDefBuilder::def()->mother()->is('Mutter', 'der Mutter');
    $defs []= RelDefBuilder::def()->parent()->is('Elternteil', 'des Elternteils');

    ////////
    
    $defs []= RelDefBuilder::def()->husband()->is('Ehemann', 'des Ehemannes');
    $defs []= RelDefBuilder::def()->wife()->is('Ehefrau', 'der Ehefrau');
    $defs []= RelDefBuilder::def()->spouse()->is('Ehepartner', 'des Ehepartners');

    $defs []= RelDefBuilder::def()->exHusband()->is('Ex-Ehemann', 'des Ex-Ehemannes');
    $defs []= RelDefBuilder::def()->exWife()->is('Ex-Ehefrau', 'der Ex-Ehefrau');
    $defs []= RelDefBuilder::def()->exSpouse()->is('Ex-Ehepartner', 'des Ex-Ehepartners');

    $defs []= RelDefBuilder::def()->malePartner()->is('Partner', 'des Partners');
    $defs []= RelDefBuilder::def()->femalePartner()->is('Partnerin', 'der Partnerin');
    $defs []= RelDefBuilder::def()->partner()->is('Partner/Partnerin', 'des Partner/der Partnerin');

    ////////

    $defs []= RelDefBuilder::def()->adoptiveSon()->is('Adoptivsohn', 'des Adoptivsohnes');
    $defs []= RelDefBuilder::def()->adoptiveDaughter()->is('Adoptivtochter', 'der Adoptivtochter');
    $defs []= RelDefBuilder::def()->adoptiveChild()->is('Adoptivkind', 'des Adoptivkindes');
    
    $defs []= RelDefBuilder::def()->fosterSon()->is('Pflegesohn', 'des Pflegesohnes');
    $defs []= RelDefBuilder::def()->fosterDaughter()->is('Pflegetochter', 'der Pflegetochter');
    $defs []= RelDefBuilder::def()->fosterChild()->is('Pflegekind', 'des Pflegekindes');
    
    $defs []= RelDefBuilder::def()->son()->is('Sohn', 'des Sohnes');
    $defs []= RelDefBuilder::def()->daughter()->is('Tochter', 'der Tochter');
    $defs []= RelDefBuilder::def()->child()->is('Kind', 'des Kindes');
    
    ////////
    
    $defs []= RelDefBuilder::def()->twinBrother()->is('Zwillingsbruder', 'des Zwillingsbruders');
    $defs []= RelDefBuilder::def()->twinSister()->is('Zwillingsschwester', 'der Zwillingsschwester');
    $defs []= RelDefBuilder::def()->twinSibling()->is('Zwilling', 'des Zwillings');    

    $defs []= RelDefBuilder::def()->brother()->is('Bruder', 'des Bruders');
    $defs []= RelDefBuilder::def()->sister()->is('Schwester', 'der Schwester');
    $defs []= RelDefBuilder::def()->sibling()->is('Geschwisterteil', 'des Geschwisterteils');
    
    ////////

    //$ignoreLaterEvents: according to § 1590 BGB (https://www.gesetze-im-internet.de/bgb/__1590.html),
    //Schwägerschaft is forever
    
    $defs []= RelDefBuilder::def()->spouse(true)->father()->is('Schwiegervater', 'des Schwiegervaters');
    $defs []= RelDefBuilder::def()->spouse(true)->mother()->is('Schwiegermutter', 'der Schwiegermutter');

    $defs []= RelDefBuilder::def()->child()->husband(true)->is('Schwiegersohn', 'des Schwiegersohns');
    $defs []= RelDefBuilder::def()->child()->wife(true)->is('Schwiegertochter', 'der Schwiegertochter');
    
    $defs []= RelDefBuilder::def()->spouse(true)->brother()->is('Schwager', 'des Schwagers');
    $defs []= RelDefBuilder::def()->sibling()->husband(true)->is('Schwager', 'des Schwagers');
    $defs []= RelDefBuilder::def()->spouse(true)->sister()->is('Schwägerin', 'der Schwägerin');
    $defs []= RelDefBuilder::def()->sibling()->wife(true)->is('Schwägerin', 'der Schwägerin');
        
    ////////

    $defs []= RelDefBuilder::def()->parent()->son()->is('Halbbruder', 'des Halbbruders');
    $defs []= RelDefBuilder::def()->parent()->daughter()->is('Halbschwester', 'der Halbschwester');
    
    $defs []= RelDefBuilder::def()->stepFather()->is('Stiefvater');
    $defs []= RelDefBuilder::def()->stepMother()->is('Stiefmutter');
    $defs []= RelDefBuilder::def()->stepParent()->is('Stief-Elternteil');
    
    $defs []= RelDefBuilder::def()->stepSon()->is('Stiefsohn');
    $defs []= RelDefBuilder::def()->stepDaughter()->is('Stieftochter');
    $defs []= RelDefBuilder::def()->stepChild()->is('Stiefkind');
    
    $defs []= RelDefBuilder::def()->stepBrother()->is('Stiefbruder');
    $defs []= RelDefBuilder::def()->stepSister()->is('Stiefschwester');
    $defs []= RelDefBuilder::def()->stepSibling()->is('Stief-Geschwisterteil');
    
    ////////
    
    $defs []= RelDefBuilder::def()->parent()->father()->is('Großvater', 'des Großvaters');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->father()->is('Urgroßvater', 'des Urgroßvaters');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(3))->father()->is('Ururgroßvater', 'des Ururgroßvaters');
    $defs []= RelDefBuilder::def()->parent(Times::min(3))->parent()->father()->is('%s×Ur-Großvater', 'des %s×Ur-Großvaters');
    
    $defs []= RelDefBuilder::def()->parent()->mother()->is('Großmutter', 'der Großmutter');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->mother()->is('Urgroßmutter', 'der Urgroßmutter');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(3))->mother()->is('Ururgroßmutter', 'der Ururgroßmutter');
    $defs []= RelDefBuilder::def()->parent(Times::min(3))->parent()->mother()->is('%s×Ur-Großmutter', 'der %s×Ur-Großmutter');

    $defs []= RelDefBuilder::def()->parent()->parent()->is('Großelternteil', 'des Großelternteils');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->parent()->is('Urgroßelternteil', 'des Urgroßelternteils');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(3))->parent()->is('Ururgroßelternteil', 'des Ururgroßelternteils');
    $defs []= RelDefBuilder::def()->parent(Times::min(3))->parent()->parent()->is('%s×Ur-Großelternteil', 'des %s×Ur-Großelternteils');

    ////////

    $defs []= RelDefBuilder::def()->child()->son()->is('Enkelsohn', 'des Enkelsohns');
    $defs []= RelDefBuilder::def()->child(Times::fixed(2))->son()->is('Urenkelsohn', 'des Urenkelsohns');
    $defs []= RelDefBuilder::def()->child(Times::fixed(3))->son()->is('Ururenkelsohn', 'des Ururenkelsohns');
    $defs []= RelDefBuilder::def()->child(Times::min(3))->child()->son()->is('%s×Ur-Enkelsohn', 'des %s×Ur-Enkelsohns');
    
    $defs []= RelDefBuilder::def()->child()->daughter()->is('Enkeltochter', 'der Enkeltochter');
    $defs []= RelDefBuilder::def()->child(Times::fixed(2))->daughter()->is('Urenkeltochter', 'der Urenkeltochter');
    $defs []= RelDefBuilder::def()->child(Times::fixed(3))->daughter()->is('Ururenkeltochter', 'der Ururenkeltochter');
    $defs []= RelDefBuilder::def()->child(Times::min(3))->child()->daughter()->is('%s×Ur-Enkeltochter', 'der %s×Ur-Enkeltochter');
    
    $defs []= RelDefBuilder::def()->child()->child()->is('Enkelkind', 'der Enkelkindes');
    $defs []= RelDefBuilder::def()->child(Times::fixed(2))->child()->is('Urenkelkind', 'des Urenkelkindes');
    $defs []= RelDefBuilder::def()->child(Times::fixed(3))->child()->is('Ururenkelkind', 'des Ururenkelkindes');
    $defs []= RelDefBuilder::def()->child(Times::min(3))->child()->child()->is('%s×Ur-Enkelkind', 'des %s×Ur-Enkelkindes');

    ////////

    $defs []= RelDefBuilder::def()->parent()->brother()->is('Onkel', 'des Onkels');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->brother()->is('Großonkel', 'des Großonkels');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(3))->brother()->is('Urgroßonkel', 'des Urgroßonkels');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(4))->brother()->is('Ururgroßonkel', 'des Ururgroßonkels');
    $defs []= RelDefBuilder::def()->parent(Times::min(5, -2))->brother()->is('%s×Ur-Großonkel', 'des %s×Ur-Großonkels');
    
    $defs []= RelDefBuilder::def()->parent()->sister()->is('Tante', 'der Tante');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->sister()->is('Großtante', 'der Großtante');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(3))->sister()->is('Urgroßtante', 'der Urgroßtante');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(4))->sister()->is('Ururgroßtante', 'der Ururgroßtante');
    $defs []= RelDefBuilder::def()->parent(Times::min(5, -2))->sister()->is('%s×Ur-Großtante', 'der %s×Ur-Großtante');
        
    ////////

    $defs []= RelDefBuilder::def()->sibling()->son()->is('Neffe', 'des Neffen');
    $defs []= RelDefBuilder::def()->sibling()->child()->son()->is('Großneffe', 'des Großneffen');
    $defs []= RelDefBuilder::def()->sibling()->child(Times::fixed(2))->son()->is('Urgroßneffe', 'des Urgroßneffen');
    $defs []= RelDefBuilder::def()->sibling()->child(Times::fixed(3))->son()->is('Ururgroßneffe', 'des Ururgroßneffen');
    $defs []= RelDefBuilder::def()->sibling()->child(Times::min(4, -1))->son()->is('%s×Ur-Großneffe', 'des %s×Ur-Großneffen');

    $defs []= RelDefBuilder::def()->sibling()->daughter()->is('Nichte', 'der Nichte');
    $defs []= RelDefBuilder::def()->sibling()->child()->daughter()->is('Großnichte', 'der Großnichte');
    $defs []= RelDefBuilder::def()->sibling()->child(Times::fixed(2))->daughter()->is('Urgroßnichte', 'der Urgroßnichte');
    $defs []= RelDefBuilder::def()->sibling()->child(Times::fixed(3))->daughter()->is('Ururgroßnichte', 'der Ururgroßnichte');
    $defs []= RelDefBuilder::def()->sibling()->child(Times::min(4, -1))->daughter()->is('%s×Ur-Großnichte', 'der %s×Ur-Großnichte');
    
    ////////

    $defs []= RelDefBuilder::def()->parent()->sibling()->son()->is('Cousin', 'des Cousins');
    
    $ref = Times::min(1, 1); 
    $defs []= RelDefBuilder::def()->parent()->parent($ref)->sibling()->child($ref)->son()->is('Cousin %s. Grades', 'des Cousins %s. Grades');

    $defs []= RelDefBuilder::def()->parent()->sibling()->daughter()->is('Cousine', 'der Cousine');
    $defs []= RelDefBuilder::def()->parent()->parent($ref)->sibling()->child($ref)->daughter()->is('Cousine %s. Grades', 'der Cousine %s. Grades');
    
    ////////
    
    $ref = Times::min(1, 2);
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->sibling()->son()->is('Onkel 2. Grades', 'des Onkels 2. Grades');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->parent($ref)->sibling()->child($ref)->son()->is('Onkel %s. Grades', 'des Onkels %s. Grades');
    
    $defs []= RelDefBuilder::def()->parent(Times::fixed(3))->sibling()->son()->is('Großonkel 2. Grades', 'des Großonkels 2. Grades');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(3))->parent($ref)->sibling()->child($ref)->son()->is('Großonkel %s. Grades', 'des Großonkels %s. Grades');
    
    $defs []= RelDefBuilder::def()->parent(Times::fixed(4))->sibling()->son()->is('Urgroßonkel 2. Grades', 'des Urgroßonkels 2. Grades');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(4))->parent($ref)->sibling()->child($ref)->son()->is('Urgroßonkel %s. Grades', 'des Urgroßonkels %s. Grades');

    $defs []= RelDefBuilder::def()->parent(Times::fixed(5))->sibling()->son()->is('Ururgroßonkel 2. Grades', 'des Ururgroßonkels 2. Grades');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(5))->parent($ref)->sibling()->child($ref)->son()->is('Ururgroßonkel %s. Grades', 'des Ururgroßonkels %s. Grades');
    
    $defs []= RelDefBuilder::def()->parent(Times::min(6, -3))->sibling()->son()->is('%s×Ur-Großonkel 2. Grades', 'des %s×Ur-Großonkels 2. Grades');    
    $defs []= RelDefBuilder::def()->parent(Times::min(6, -3))->parent($ref)->sibling()->child($ref)->son()->is('%1$s×Ur-Großonkel %2$s. Grades', 'des %1$s×Ur-Großonkels %2$s. Grades');

    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->sibling()->daughter()->is('Tante 2. Grades', 'der Tante 2. Grades');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->parent($ref)->sibling()->child($ref)->daughter()->is('Tante %s. Grades', 'der Tante %s. Grades');
    
    $defs []= RelDefBuilder::def()->parent(Times::fixed(3))->sibling()->daughter()->is('Großtante 2. Grades', 'der Großtante 2. Grades');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(3))->parent($ref)->sibling()->child($ref)->daughter()->is('Großtante %s. Grades', 'der Großtante %s. Grades');
    
    $defs []= RelDefBuilder::def()->parent(Times::fixed(4))->sibling()->daughter()->is('Urgroßtante 2. Grades', 'der Urgroßtante 2. Grades');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(4))->parent($ref)->sibling()->child($ref)->daughter()->is('Urgroßtante %s. Grades', 'der Urgroßtante %s. Grades');

    $defs []= RelDefBuilder::def()->parent(Times::fixed(5))->sibling()->daughter()->is('Ururgroßtante 2. Grades', 'der Ururgroßtante 2. Grades');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(5))->parent($ref)->sibling()->child($ref)->daughter()->is('Ururgroßtante %s. Grades', 'der Ururgroßtante %s. Grades');
    
    $defs []= RelDefBuilder::def()->parent(Times::min(6, -3))->sibling()->daughter()->is('%s×Ur-Großtante 2. Grades', 'der %s×Ur-Großtante 2. Grades');    
    $defs []= RelDefBuilder::def()->parent(Times::min(6, -3))->parent($ref)->sibling()->child($ref)->daughter()->is('%1$s×Ur-Großtante %2$s. Grades', 'der %1$s×Ur-Großtante %2$s. Grades');
    
    ////////

    $ref = Times::min(1, 1);
    $defs []= RelDefBuilder::def()->parent($ref)->sibling()->child($ref)->son()->is('Neffe %s. Grades', 'des Neffen %s. Grades');
    $defs []= RelDefBuilder::def()->parent($ref)->sibling()->child($ref)->child()->son()->is('Großneffe %s. Grades', 'des Großneffen %s. Grades');
    $defs []= RelDefBuilder::def()->parent($ref)->sibling()->child($ref)->child(Times::fixed(2))->son()->is('Urgroßneffe %s. Grades', 'des Urgroßneffen %s. Grades');
    $defs []= RelDefBuilder::def()->parent($ref)->sibling()->child($ref)->child(Times::fixed(3))->son()->is('Ururgroßneffe %s. Grades', 'des Ururgroßneffen %s. Grades');
    $defs []= RelDefBuilder::def()->parent($ref)->sibling()->child($ref)->child(Times::min(4, -1))->son()->is('%2$s×Ur-Großneffe %1$s. Grades', 'des %2$s×Ur-Großneffen %1$s. Grades');
        
    $ref = Times::min(1, 1);
    $defs []= RelDefBuilder::def()->parent($ref)->sibling()->child($ref)->daughter()->is('Nichte %s. Grades', 'der Nichte %s. Grades');
    $defs []= RelDefBuilder::def()->parent($ref)->sibling()->child($ref)->child()->daughter()->is('Großnichte %s. Grades', 'der Großnichte %s. Grades');
    $defs []= RelDefBuilder::def()->parent($ref)->sibling()->child($ref)->child(Times::fixed(2))->daughter()->is('Urgroßnichte %s. Grades', 'der Urgroßnichte %s. Grades');
    $defs []= RelDefBuilder::def()->parent($ref)->sibling()->child($ref)->child(Times::fixed(3))->daughter()->is('Ururgroßnichte %s. Grades', 'der Ururgroßnichte %s. Grades');
    $defs []= RelDefBuilder::def()->parent($ref)->sibling()->child($ref)->child(Times::min(4, -1))->daughter()->is('%2$s×Ur-Großnichte %1$s. Grades', 'der %2$s×Ur-Großnichte %1$s. Grades');
    
    ////////
    
    return new RelDefs(new Collection($defs));
  }
}

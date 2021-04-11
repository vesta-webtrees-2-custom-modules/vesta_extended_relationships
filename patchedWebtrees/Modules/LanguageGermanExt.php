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
    
    $joiner = new DefaultRelPathJoiner();
    
    return $algorithm->getRelationshipName(
            self::defs(),
            $joiner,
            $path);
  }
  
  public static function defs(): RelDefs {
    
    $defs = [];
    
    $defs []= RelPath::any()->adoptiveFather()->is('Adoptivvater', 'des Adoptivvaters');
    $defs []= RelPath::any()->adoptiveMother()->is('Adoptivmutter', 'der Adoptivmutter');
    $defs []= RelPath::any()->adoptiveParent()->is('Adoptiv-Elternteil', 'des Adoptiv-Elternteils');
    
    $defs []= RelPath::any()->fosterFather()->is('Pflegevater', 'des Pflegevaters');
    $defs []= RelPath::any()->fosterMother()->is('Pflegemutter', 'der Pflegemutter');
    $defs []= RelPath::any()->fosterParent()->is('Pflege-Elternteil', 'des Pflege-Elternteils');
    
    $defs []= RelPath::any()->father()->is('Vater', 'des Vaters');
    $defs []= RelPath::any()->mother()->is('Mutter', 'der Mutter');
    $defs []= RelPath::any()->parent()->is('Elternteil', 'des Elternteils');

    ////////
    
    $defs []= RelPath::any()->husband()->is('Ehemann', 'des Ehemannes');
    $defs []= RelPath::any()->wife()->is('Ehefrau', 'der Ehefrau');
    $defs []= RelPath::any()->spouse()->is('Ehepartner', 'des Ehepartners');

    $defs []= RelPath::any()->exHusband()->is('Ex-Ehemann', 'des Ex-Ehemannes');
    $defs []= RelPath::any()->exWife()->is('Ex-Ehefrau', 'der Ex-Ehefrau');
    $defs []= RelPath::any()->exSpouse()->is('Ex-Ehepartner', 'des Ex-Ehepartners');

    $defs []= RelPath::any()->malePartner()->is('Partner', 'des Partners');
    $defs []= RelPath::any()->femalePartner()->is('Partnerin', 'der Partnerin');
    $defs []= RelPath::any()->partner()->is('Partner/Partnerin', 'des Partner/der Partnerin');

    ////////

    $defs []= RelPath::any()->adoptiveSon()->is('Adoptivsohn', 'des Adoptivsohnes');
    $defs []= RelPath::any()->adoptiveDaughter()->is('Adoptivtochter', 'der Adoptivtochter');
    $defs []= RelPath::any()->adoptiveChild()->is('Adoptivkind', 'des Adoptivkindes');
    
    $defs []= RelPath::any()->fosterSon()->is('Pflegesohn', 'des Pflegesohnes');
    $defs []= RelPath::any()->fosterDaughter()->is('Pflegetochter', 'der Pflegetochter');
    $defs []= RelPath::any()->fosterChild()->is('Pflegekind', 'des Pflegekindes');
    
    $defs []= RelPath::any()->son()->is('Sohn', 'des Sohnes');
    $defs []= RelPath::any()->daughter()->is('Tochter', 'der Tochter');
    $defs []= RelPath::any()->child()->is('Kind', 'des Kindes');
    
    ////////
    
    $defs []= RelPath::any()->twinBrother()->is('Zwillingsbruder', 'des Zwillingsbruders');
    $defs []= RelPath::any()->twinSister()->is('Zwillingsschwester', 'der Zwillingsschwester');
    $defs []= RelPath::any()->twinSibling()->is('Zwilling', 'des Zwillings');    

    $defs []= RelPath::any()->brother()->is('Bruder', 'des Bruders');
    $defs []= RelPath::any()->sister()->is('Schwester', 'der Schwester');
    $defs []= RelPath::any()->sibling()->is('Geschwisterteil', 'des Geschwisterteils');
    
    ////////

    //$ignoreLaterEvents: according to § 1590 BGB (https://www.gesetze-im-internet.de/bgb/__1590.html),
    //schwägerschaft is forever
    
    $defs []= RelPath::any()->spouse(true)->father()->is('Schwiegervater', 'des Schwiegervaters');
    $defs []= RelPath::any()->spouse(true)->mother()->is('Schwiegermutter', 'der Schwiegermutter');

    $defs []= RelPath::any()->child()->husband(true)->is('Schwiegersohn', 'des Schwiegersohns');
    $defs []= RelPath::any()->child()->wife(true)->is('Schwiegertochter', 'der Schwiegertochter');
    
    $defs []= RelPath::any()->spouse(true)->brother()->is('Schwager', 'des Schwagers');
    $defs []= RelPath::any()->sibling()->husband(true)->is('Schwager', 'des Schwagers');
    $defs []= RelPath::any()->spouse(true)->sister()->is('Schwägerin', 'der Schwägerin');
    $defs []= RelPath::any()->sibling()->wife(true)->is('Schwägerin', 'der Schwägerin');
        
    ////////

    $defs []= RelPath::any()->parent()->son()->is('Halbbruder', 'des Halbbruders');
    $defs []= RelPath::any()->parent()->daughter()->is('Halbschwester', 'der Halbschwester');
    
    $defs []= RelPath::any()->stepFather()->is('Stiefvater');
    $defs []= RelPath::any()->stepMother()->is('Stiefmutter');
    $defs []= RelPath::any()->stepParent()->is('Stief-Elternteil');
    
    $defs []= RelPath::any()->stepSon()->is('Stiefsohn');
    $defs []= RelPath::any()->stepDaughter()->is('Stieftochter');
    $defs []= RelPath::any()->stepChild()->is('Stiefkind');
    
    $defs []= RelPath::any()->stepBrother()->is('Stiefbruder');
    $defs []= RelPath::any()->stepSister()->is('Stiefschwester');
    $defs []= RelPath::any()->stepSibling()->is('Stief-Geschwisterteil');
    
    ////////

    
    ////////
    
    $defs []= RelPath::any()->parent()->father()->is('Großvater', 'des Großvaters');
    $defs []= RelPath::any()->parent(Times::fixed(2))->father()->is('Urgroßvater', 'des Urgroßvaters');
    $defs []= RelPath::any()->parent(Times::min(2))->parent()->father()->is('%s×Ur-Großvater', 'des %s×Ur-Großvaters');
    
    $defs []= RelPath::any()->parent()->mother()->is('Großmutter', 'der Großmutter');
    $defs []= RelPath::any()->parent(Times::fixed(2))->mother()->is('Urgroßmutter', 'der Urgroßmutter');
    $defs []= RelPath::any()->parent(Times::min(2))->parent()->mother()->is('%s×Ur-Großmutter', 'der %s×Ur-Großmutter');

    $defs []= RelPath::any()->parent()->parent()->is('Großelternteil', 'des Großelternteils');
    $defs []= RelPath::any()->parent(Times::fixed(2))->parent()->is('Urgroßelternteil', 'des Urgroßelternteils');
    $defs []= RelPath::any()->parent(Times::min(2))->parent()->parent()->is('%s×Ur-Großelternteil', 'des %s×Ur-Großelternteils');

    ////////

    $defs []= RelPath::any()->child()->son()->is('Enkelsohn', 'des Enkelsohns');
    $defs []= RelPath::any()->child(Times::fixed(2))->son()->is('Urenkelsohn', 'des Urenkelsohns');
    $defs []= RelPath::any()->child(Times::min(2))->child()->son()->is('%s×Ur-Enkelsohn', 'des %s×Ur-Enkelsohns');
    
    $defs []= RelPath::any()->child()->daughter()->is('Enkeltochter', 'der Enkeltochter');
    $defs []= RelPath::any()->child(Times::fixed(2))->daughter()->is('Urenkeltochter', 'der Urenkeltochter');
    $defs []= RelPath::any()->child(Times::min(2))->child()->daughter()->is('%s×Ur-Enkeltochter', 'der %s×Ur-Enkeltochter');
    
    $defs []= RelPath::any()->child()->child()->is('Enkelkind', 'der Enkelkindes');
    $defs []= RelPath::any()->child(Times::fixed(2))->child()->is('Urenkelkind', 'des Urenkelkindes');
    $defs []= RelPath::any()->child(Times::min(2))->child()->child()->is('%s×Ur-Enkelkind', 'des %s×Ur-Enkelkindes');

    ////////

    $defs []= RelPath::any()->parent()->brother()->is('Onkel', 'des Onkels');
    $defs []= RelPath::any()->parent(Times::fixed(2))->brother()->is('Großonkel', 'des Großonkels');
    $defs []= RelPath::any()->parent(Times::fixed(3))->brother()->is('Urgroßonkel', 'des Urgroßonkels');
    $defs []= RelPath::any()->parent(Times::min(4, -2))->brother()->is('%s×Ur-Großonkel', 'des %s×Ur-Großonkels');
    
    $defs []= RelPath::any()->parent()->sister()->is('Tante', 'der Tante');
    $defs []= RelPath::any()->parent(Times::fixed(2))->sister()->is('Großtante', 'der Großtante');
    $defs []= RelPath::any()->parent(Times::fixed(3))->sister()->is('Urgroßtante', 'der Urgroßtante');
    $defs []= RelPath::any()->parent(Times::min(4, -2))->sister()->is('%s×Ur-Großtante', 'der %s×Ur-Großtante');
        
    ////////

    $defs []= RelPath::any()->sibling()->son()->is('Neffe', 'des Neffen');
    $defs []= RelPath::any()->sibling()->child()->son()->is('Großneffe', 'des Großneffen');
    $defs []= RelPath::any()->sibling()->child(Times::fixed(2))->son()->is('Urgroßneffe', 'des Urgroßneffen');
    $defs []= RelPath::any()->sibling()->child(Times::min(3, -1))->son()->is('%s×Ur-Großneffe', 'des %s×Ur-Großneffen');

    $defs []= RelPath::any()->sibling()->daughter()->is('Nichte', 'der Nichte');
    $defs []= RelPath::any()->sibling()->child()->daughter()->is('Großnichte', 'der Großnichte');
    $defs []= RelPath::any()->sibling()->child(Times::fixed(2))->daughter()->is('Urgroßnichte', 'der Urgroßnichte');
    $defs []= RelPath::any()->sibling()->child(Times::min(3, -1))->daughter()->is('%s×Ur-Großnichte', 'der %s×Ur-Großnichte');
    
    ////////

    $defs []= RelPath::any()->parent()->sibling()->son()->is('Cousin', 'des Cousins');
    
    $ref = Times::min(1, 1); 
    $defs []= RelPath::any()->parent()->parent($ref)->sibling()->child($ref)->son()->is('Cousin %s. Grades', 'des Cousins %s. Grades');

    $defs []= RelPath::any()->parent()->sibling()->daughter()->is('Cousine', 'der Cousine');
    $defs []= RelPath::any()->parent()->parent($ref)->sibling()->child($ref)->daughter()->is('Cousine %s. Grades', 'der Cousine %s. Grades');
    
    ////////
    
    $ref = Times::min(1, 2);
    $defs []= RelPath::any()->parent(Times::fixed(2))->sibling()->son()->is('Onkel 2. Grades', 'des Onkels 2. Grades');
    $defs []= RelPath::any()->parent(Times::fixed(2))->parent($ref)->sibling()->child($ref)->son()->is('Onkel %s. Grades', 'des Onkels %s. Grades');
    
    $defs []= RelPath::any()->parent(Times::fixed(3))->sibling()->son()->is('Großonkel 2. Grades', 'des Großonkels 2. Grades');
    $defs []= RelPath::any()->parent(Times::fixed(3))->parent($ref)->sibling()->child($ref)->son()->is('Großonkel %s. Grades', 'des Großonkels %s. Grades');
    
    $defs []= RelPath::any()->parent(Times::fixed(4))->sibling()->son()->is('Urgroßonkel 2. Grades', 'des Urgroßonkels 2. Grades');
    $defs []= RelPath::any()->parent(Times::fixed(4))->parent($ref)->sibling()->child($ref)->son()->is('Urgroßonkel %s. Grades', 'des Urgroßonkels %s. Grades');
    
    $defs []= RelPath::any()->parent(Times::min(5, -3))->sibling()->son()->is('%s×Ur-Großonkel 2. Grades', 'des %s×Ur-Großonkels 2. Grades');    
    $defs []= RelPath::any()->parent(Times::min(5, -3))->parent($ref)->sibling()->child($ref)->son()->is('%1$s×Ur-Großonkel %2$s. Grades', 'des %1$s×Ur-Großonkels %2$s. Grades');

    $defs []= RelPath::any()->parent(Times::fixed(2))->sibling()->daughter()->is('Tante 2. Grades', 'der Tante 2. Grades');
    $defs []= RelPath::any()->parent(Times::fixed(2))->parent($ref)->sibling()->child($ref)->daughter()->is('Tante %s. Grades', 'der Tante %s. Grades');
    
    $defs []= RelPath::any()->parent(Times::fixed(3))->sibling()->daughter()->is('Großtante 2. Grades', 'der Großtante 2. Grades');
    $defs []= RelPath::any()->parent(Times::fixed(3))->parent($ref)->sibling()->child($ref)->daughter()->is('Großtante %s. Grades', 'der Großtante %s. Grades');
    
    $defs []= RelPath::any()->parent(Times::fixed(4))->sibling()->daughter()->is('Urgroßtante 2. Grades', 'der Urgroßtante 2. Grades');
    $defs []= RelPath::any()->parent(Times::fixed(4))->parent($ref)->sibling()->child($ref)->daughter()->is('Urgroßtante %s. Grades', 'der Urgroßtante %s. Grades');
    
    $defs []= RelPath::any()->parent(Times::min(5, -3))->sibling()->daughter()->is('%s×Ur-Großtante 2. Grades', 'der %s×Ur-Großtante 2. Grades');    
    $defs []= RelPath::any()->parent(Times::min(5, -3))->parent($ref)->sibling()->child($ref)->daughter()->is('%1$s×Ur-Großtante %2$s. Grades', 'der %1$s×Ur-Großtante %2$s. Grades');
    
    ////////

    $ref = Times::min(1, 1);
    $defs []= RelPath::any()->parent($ref)->sibling()->child($ref)->son()->is('Neffe %s. Grades', 'des Neffen %s. Grades');
    $defs []= RelPath::any()->parent($ref)->sibling()->child($ref)->child()->son()->is('Großneffe %s. Grades', 'des Großneffen %s. Grades');
    $defs []= RelPath::any()->parent($ref)->sibling()->child($ref)->child(Times::fixed(2))->son()->is('Urgroßneffe %s. Grades', 'des Urgroßneffen %s. Grades');
    $defs []= RelPath::any()->parent($ref)->sibling()->child($ref)->child(Times::min(3, -1))->son()->is('%2$s×Ur-Großneffe %1$s. Grades', 'des %2$s×Ur-Großneffen %1$s. Grades');
        
    $ref = Times::min(1, 1);
    $defs []= RelPath::any()->parent($ref)->sibling()->child($ref)->daughter()->is('Nichte %s. Grades', 'der Nichte %s. Grades');
    $defs []= RelPath::any()->parent($ref)->sibling()->child($ref)->child()->daughter()->is('Großnichte %s. Grades', 'der Großnichte %s. Grades');
    $defs []= RelPath::any()->parent($ref)->sibling()->child($ref)->child(Times::fixed(2))->daughter()->is('Urgroßnichte %s. Grades', 'der Urgroßnichte %s. Grades');
    $defs []= RelPath::any()->parent($ref)->sibling()->child($ref)->child(Times::min(3, -1))->daughter()->is('%2$s×Ur-Großnichte %1$s. Grades', 'der %2$s×Ur-Großnichte %1$s. Grades');
    
    ////////
    
    return new RelDefs(new Collection($defs));
  }
}

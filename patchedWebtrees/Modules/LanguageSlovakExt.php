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

class LanguageSlovakExt extends AbstractModule implements ModuleLanguageExtInterface {
  
  public function getRelationshipName(
          RelationshipPath $path): string {
    
    //modified splitting!
    $splitter = new class implements RelationshipPathSplitPredicate {
        public function prioritize(RelationshipPathSplit $split): int {
          
          //primarily prefer splits resulting in common ancestor-based subpaths
          if (RelationshipPathSplitUtils::isNextToSpouse($split)) {
            return 5;
          }
          
          //then, splits without a sibling rel
          //(mainly for performance reasons)
          if (RelationshipPathSplitUtils::isAscentToDescent($split)) {
            return 4;
          }
          
          //prefer 'sister' + 'great-grandson'
          //rather than 'niece' +'grandson'
          //(but use isNextToTerminalSibling instead of isNextToSibling in order to account for cases below)
          if (RelationshipPathSplitUtils::isNextToTerminalSibling($split)) {
            return 3;
          }
          
          //prefer 'grandfather' + 'second cousin'
          //rather than 'great-x3-grandfather' + 'great-x2-nephew'
          if (RelationshipPathSplitUtils::isWithinAscent($split)) {
            return 2;
          }
          
          //similar on the other side
          if (RelationshipPathSplitUtils::isWithinDescent($split)) {
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

    $defs []= RelDefBuilder::def()->father()->is('otec', 'otca');
    $defs []= RelDefBuilder::def()->mother()->is('matka', 'matky');
    $defs []= RelDefBuilder::def()->parent()->is('rodič', 'rodiča');

    $defs []= RelDefBuilder::def()->husband()->is('manžel', 'manžela');
    $defs []= RelDefBuilder::def()->wife()->is('manželka', 'manželky');
    $defs []= RelDefBuilder::def()->spouse()->is('manžel/manželka', 'manžela/manželky');

    $defs []= RelDefBuilder::def()->malePartner()->is('partner', 'partnera');
    $defs []= RelDefBuilder::def()->femalePartner()->is('partnerka', 'partnerky');

    $defs []= RelDefBuilder::def()->son()->is('syn', 'syna');
    $defs []= RelDefBuilder::def()->daughter()->is('dcéra', 'dcéry');
    $defs []= RelDefBuilder::def()->child()->is('dieťa', 'deťaťa');

    $defs []= RelDefBuilder::def()->brother()->is('brat', 'brata');
    $defs []= RelDefBuilder::def()->sister()->is('sestra', 'sestry');
    $defs []= RelDefBuilder::def()->sibling()->is('súrodenec', 'súrodenca');

    ////////

    $defs []= RelDefBuilder::def()->wife()->father()->is('tesť', 'tesťa');
    $defs []= RelDefBuilder::def()->wife()->mother()->is('testiná', 'testinej');
    $defs []= RelDefBuilder::def()->husband()->father()->is('svokor', 'svokra');
    $defs []= RelDefBuilder::def()->husband()->mother()->is('svokra', 'svokry');
    $defs []= RelDefBuilder::def()->spouse()->father()->is('svokor', 'svokra');
    $defs []= RelDefBuilder::def()->spouse()->mother()->is('svokra', 'svokry');

    $defs []= RelDefBuilder::def()->child()->husband()->is('zať', 'zaťa');
    $defs []= RelDefBuilder::def()->child()->wife()->is('nevesta', 'nevesty');

    $defs []= RelDefBuilder::def()->spouse()->brother()->is('švagor', 'švagra');
    $defs []= RelDefBuilder::def()->sibling()->husband()->is('švagor', 'švagra');
    $defs []= RelDefBuilder::def()->spouse()->sister()->is('švagriná', 'švagrinej');
    $defs []= RelDefBuilder::def()->sibling()->wife()->is('švagriná', 'švagrinej');

    ////////

    $defs []= RelDefBuilder::def()->parent()->son()->is('nevlastný brat', 'nevlastného brata');
    $defs []= RelDefBuilder::def()->parent()->daughter()->is('nevlastná sestra', 'nevlastnej sestry');

    //TODO: make step-x relationships available/configurable?

    ////////

    $defs []= RelDefBuilder::def()->parent()->father()->is('starý otec', 'starého otca');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->father()->is('prastarý otec', 'prastarého otca');
    $defs []= RelDefBuilder::def()->parent(Times::min(2))->parent()->father()->is('%s×prastarý otec', '%s×prastarého otca');

    $defs []= RelDefBuilder::def()->parent()->mother()->is('stará matka', 'starej matky');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->mother()->is('prastará matka', 'prastarej matky');
    $defs []= RelDefBuilder::def()->parent(Times::min(2))->parent()->mother()->is('%s×prastará matka', '%s×prastarej matky');

    $defs []= RelDefBuilder::def()->parent()->parent()->is('starý rodič', 'starého rodiča');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->parent()->is('prastarý rodič', 'prastarého rodiča');
    $defs []= RelDefBuilder::def()->parent(Times::min(2))->parent()->parent()->is('%s×prastarý rodič', '%s×prastarého rodiča');

    ////////

    $defs []= RelDefBuilder::def()->child()->son()->is('vnuk', 'vnuka');
    $defs []= RelDefBuilder::def()->child(Times::fixed(2))->son()->is('pravnuk', 'pravnuka');
    $defs []= RelDefBuilder::def()->child(Times::min(2))->child()->son()->is('%s×pravnuk', '%s×pravnuka');

    $defs []= RelDefBuilder::def()->child()->daughter()->is('vnučka', 'vnučky');
    $defs []= RelDefBuilder::def()->child(Times::fixed(2))->daughter()->is('pravnučka', 'pravnučky');
    $defs []= RelDefBuilder::def()->child(Times::min(2))->child()->daughter()->is('%s×pravnučka', '%s×pravnučky');

    $defs []= RelDefBuilder::def()->child()->child()->is('vnúča', 'vnúčaťa');
    $defs []= RelDefBuilder::def()->child(Times::fixed(2))->child()->is('pravnúča', 'pravnúčaťa');
    $defs []= RelDefBuilder::def()->child(Times::min(2))->child()->child()->is('%s×pravnúča', '%s×pravnúčaťa');

    ////////

    $defs []= RelDefBuilder::def()->father()->brother()->is('strýko', 'strýka');
    $defs []= RelDefBuilder::def()->father()->brother()->wife()->is('stryná', 'strynej');
    $defs []= RelDefBuilder::def()->mother()->brother()->is('ujo', 'uja');
    $defs []= RelDefBuilder::def()->mother()->brother()->wife()->is('ujčiná', 'ujčinej');
    $defs []= RelDefBuilder::def()->parent()->brother()->is('strýko', 'strýka');

    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->brother()->is('prastrýko', 'prastrýka');
    // $defs []= RelDefBuilder::def()->parent(Times::fixed(3))->brother()->is('pra-prastrýko', 'pra-prastrýka');
    // $defs []= RelDefBuilder::def()->parent(Times::min(4, -2))->brother()->is('%s× prastrýko', '%s× prastrýka');
    $defs []= RelDefBuilder::def()->parent()->sister()->is('teta', 'tety');
    $defs []= RelDefBuilder::def()->parent(Times::fixed(2))->sister()->is('prateta', 'pratety');
    // $defs []= RelDefBuilder::def()->parent(Times::fixed(3))->sister()->is('pra-prateta', 'pra-pratety');
    // $defs []= RelDefBuilder::def()->parent(Times::min(4, -2))->sister()->is('%s× prateta', '%s× pratety');

    ////////

    $defs []= RelDefBuilder::def()->sibling()->son()->is('synovec', 'synovca');
    $defs []= RelDefBuilder::def()->sibling()->child()->son()->is('prasynovec', 'prasynovca');
    // $defs []= RelDefBuilder::def()->sibling()->child(Times::min(2, -1))->son()->is('%s× prasynovec', '%s× prasynovca');
    $defs []= RelDefBuilder::def()->sibling()->daughter()->is('neter', 'netere');
    $defs []= RelDefBuilder::def()->sibling()->child()->daughter()->is('praneter', 'pranetere');
    // $defs []= RelDefBuilder::def()->sibling()->child(Times::min(2, -1))->daughter()->is('%s× praneter', '%s× pranetere');

    ////////

    $defs []= RelDefBuilder::def()->parent()->sibling()->son()->is('bratranec', 'bratranca');

    $defs []= RelDefBuilder::def()->parent()->parent(Times::fixed(3))->sibling()->child(Times::fixed(3))->son()->is('bratranec zo 4. kolena', 'bratranca zo 4. kolena');
    $defs []= RelDefBuilder::def()->parent()->parent(Times::fixed(5))->sibling()->child(Times::fixed(5))->son()->is('bratranec zo 6. kolena', 'bratranca zo 6. kolena');
    $defs []= RelDefBuilder::def()->parent()->parent(Times::fixed(6))->sibling()->child(Times::fixed(6))->son()->is('bratranec zo 7. kolena', 'bratranca zo 7. kolena');
    $defs []= RelDefBuilder::def()->parent()->parent(Times::fixed(13))->sibling()->child(Times::fixed(13))->son()->is('bratranec zo 14. kolena', 'bratranca zo 14. kolena');
    $defs []= RelDefBuilder::def()->parent()->parent(Times::fixed(15))->sibling()->child(Times::fixed(15))->son()->is('bratranec zo 16. kolena', 'bratranca zo 16. kolena');
    $defs []= RelDefBuilder::def()->parent()->parent(Times::fixed(16))->sibling()->child(Times::fixed(16))->son()->is('bratranec zo 17. kolena', 'bratranca zo 17. kolena');

    //IMPL NOTE: used as back-reference (i.e. count must match in '->child($ref)')
    $ref = Times::min(1, 1); 
    $defs []= RelDefBuilder::def()->parent()->parent($ref)->sibling()->child($ref)->son()->is('bratranec z %s. kolena', 'bratranca z %s. kolena');

    $defs []= RelDefBuilder::def()->parent()->sibling()->daughter()->is('sesternica', 'sesternice');

    //TODO WHY DONT THESE MATCH PARTIALLY????
    
    $defs []= RelDefBuilder::def()->parent()->parent(Times::fixed(3))->sibling()->child(Times::fixed(3))->daughter()->is('sesternica zo 4. kolena', 'sesternice zo 4. kolena');
    $defs []= RelDefBuilder::def()->parent()->parent(Times::fixed(5))->sibling()->child(Times::fixed(5))->daughter()->is('sesternica zo 6. kolena', 'sesternice zo 6. kolena');
    $defs []= RelDefBuilder::def()->parent()->parent(Times::fixed(6))->sibling()->child(Times::fixed(6))->daughter()->is('sesternica zo 7. kolena', 'sesternice zo 7. kolena');
    $defs []= RelDefBuilder::def()->parent()->parent(Times::fixed(13))->sibling()->child(Times::fixed(13))->daughter()->is('sesternica zo 14. kolena', 'sesternice zo 14. kolena');
    $defs []= RelDefBuilder::def()->parent()->parent(Times::fixed(15))->sibling()->child(Times::fixed(15))->daughter()->is('sesternica zo 16. kolena', 'sesternice zo 16. kolena');
    $defs []= RelDefBuilder::def()->parent()->parent(Times::fixed(16))->sibling()->child(Times::fixed(16))->daughter()->is('sesternica zo 17. kolena', 'sesternice zo 17. kolena');
 
    $ref = Times::min(1, 1);
    $defs []= RelDefBuilder::def()->parent()->parent($ref)->sibling()->child($ref)->daughter()->is('sesternica z %s. kolena', 'sesternice z %s. kolena');

    return new RelDefs(new Collection($defs));
  }
}
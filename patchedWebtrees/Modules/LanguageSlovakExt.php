<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Modules;

use Cissee\WebtreesExt\Relationships\DefaultRelPathJoiner;
use Cissee\WebtreesExt\Relationships\ModifiedRelAlgorithm;
use Cissee\WebtreesExt\Relationships\RelDefs;
use Cissee\WebtreesExt\Relationships\RelPath;
use Cissee\WebtreesExt\Relationships\Times;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\AbstractModule;
use Illuminate\Support\Collection;

class LanguageSlovakExt extends AbstractModule implements ModuleLanguageExtInterface {
  
  public function getRelationshipNameFromPath(
          string $path, 
          Individual $person1 = null, 
          Individual $person2 = null): string {
    
    $algorithm = new ModifiedRelAlgorithm(true); //modified splitting AND minimize number of splits
    $joiner = new DefaultRelPathJoiner();
    
    return $algorithm->getRelationshipNameFromPath(
            self::defs(),
            $joiner,
            $path,
            $person1,
            $person2);
  }
  
  public static function defs(): RelDefs {
    
    $defs = [];
    
    $defs []= RelPath::any()->father()->is('otec', 'otca');
    $defs []= RelPath::any()->mother()->is('matka', 'matky');
    $defs []= RelPath::any()->parent()->is('rodič', 'rodiča');

    $defs []= RelPath::any()->husband()->is('manžel', 'manžela');
    $defs []= RelPath::any()->wife()->is('manželka', 'manželky');
    $defs []= RelPath::any()->spouse()->is('manžel/manželka', 'manžela/manželky');
	
	  //$defs []= RelPath::any()->partner(sex::'M')->is('partner', 'partnera');
	  //$defs []= RelPath::any()->partner(sex::'F')->is('partnerka', 'partnerky');
    
    $defs []= RelPath::any()->son()->is('syn', 'syna');
    $defs []= RelPath::any()->daughter()->is('dcéra', 'dcéry');
    $defs []= RelPath::any()->child()->is('dieťa', 'deťaťa');
    
    $defs []= RelPath::any()->brother()->is('brat', 'brata');
    $defs []= RelPath::any()->sister()->is('sestra', 'sestry');
    $defs []= RelPath::any()->sibling()->is('súrodenec', 'súrodenca');
    
    ////////
    
	  $defs []= RelPath::any()->wife()->father()->is('tesť', 'tesťa');
	  $defs []= RelPath::any()->wife()->mother()->is('testiná', 'testinej');
	  $defs []= RelPath::any()->husband()->father()->is('svokor', 'svokra');
	  $defs []= RelPath::any()->husband()->mother()->is('svokora', 'svokry');
    $defs []= RelPath::any()->spouse()->father()->is('svokor', 'svokra');
    $defs []= RelPath::any()->spouse()->mother()->is('svokra', 'svokry');

    $defs []= RelPath::any()->child()->husband()->is('zať', 'zaťa');
    $defs []= RelPath::any()->child()->wife()->is('nevesta', 'nevesty');
    
    $defs []= RelPath::any()->spouse()->brother()->is('švagor', 'švagra');
    $defs []= RelPath::any()->sibling()->husband()->is('švagor', 'švagra');
    $defs []= RelPath::any()->spouse()->sister()->is('švagriná', 'švagrinej');
    $defs []= RelPath::any()->sibling()->wife()->is('švagriná', 'švagrinej');
        
    ////////

    $defs []= RelPath::any()->parent()->son()->is('nevlastný brat', 'nevlastného brata');
    $defs []= RelPath::any()->parent()->daughter()->is('nevlastná sestra', 'nevlastnej sestry');
    
    //TODO: make step-x relationships available/configurable?
    
    ////////
    
    $defs []= RelPath::any()->parent()->father()->is('starý otec', 'starého otca');
    $defs []= RelPath::any()->parent(Times::fixed(2))->father()->is('prastarý otec', 'prastarý otec');
    $defs []= RelPath::any()->parent(Times::min(2))->parent()->father()->is('%s×prastarý otec', '%s×prastarého otca');
    
    $defs []= RelPath::any()->parent()->mother()->is('stará matka', 'starej matky');
    $defs []= RelPath::any()->parent(Times::fixed(2))->mother()->is('prastará matka', 'prastarej matky');
    $defs []= RelPath::any()->parent(Times::min(2))->parent()->mother()->is('%s×prastará matka', '%s×prastarej matky');

    $defs []= RelPath::any()->parent()->parent()->is('starý rodič', 'starého rodiča');
    $defs []= RelPath::any()->parent(Times::fixed(2))->parent()->is('prastarý rodič', 'prastarého rodiča');
    $defs []= RelPath::any()->parent(Times::min(2))->parent()->parent()->is('%s×prastarý rodič', '%s×prastarého rodiča');

    ////////

    $defs []= RelPath::any()->child()->son()->is('vnuk', 'vnuka');
    $defs []= RelPath::any()->child(Times::fixed(2))->son()->is('pravnuk', 'pravnuka');
    $defs []= RelPath::any()->child(Times::min(2))->child()->son()->is('%s×pravnuk', '%s×pravnuka');
    
    $defs []= RelPath::any()->child()->daughter()->is('vnučka', 'vnučky');
    $defs []= RelPath::any()->child(Times::fixed(2))->daughter()->is('pravnučka', 'pravnučky');
    $defs []= RelPath::any()->child(Times::min(2))->child()->daughter()->is('%s×pravnučka', '%s×pravnučky');
    
    $defs []= RelPath::any()->child()->child()->is('vnúča', 'vnúčaťa');
    $defs []= RelPath::any()->child(Times::fixed(2))->child()->is('pravnúča', 'pravnúčaťa');
    $defs []= RelPath::any()->child(Times::min(2))->child()->child()->is('%s×pravnúča', '%s×pravnúčaťa');

    ////////
	
    $defs []= RelPath::any()->father()->brother()->is('strýko', 'strýka');
	  $defs []= RelPath::any()->father()->brother()->wife()->is('stryná', 'stranej');
	  $defs []= RelPath::any()->mother()->brother()->is('ujo', 'uja');
	  $defs []= RelPath::any()->mother()->brother()->wife()->is('ujčiná', 'ujčinej');
    $defs []= RelPath::any()->parent()->brother()->is('strýko', 'strýka');
    
    $defs []= RelPath::any()->parent(Times::fixed(2))->brother()->is('prastrýko', 'prastrýka');
    $defs []= RelPath::any()->parent(Times::fixed(3))->brother()->is('pra-prastrýko', 'pra-prastrýka');
    $defs []= RelPath::any()->parent(Times::min(4, -2))->brother()->is('%s×prastrýko', '%s×prastrýka');
    $defs []= RelPath::any()->parent()->sister()->is('teta', 'tety');
    $defs []= RelPath::any()->parent(Times::fixed(2))->sister()->is('prateta', 'pratety');
    $defs []= RelPath::any()->parent(Times::fixed(3))->sister()->is('pra-prateta', 'pra-pratety');
    $defs []= RelPath::any()->parent(Times::min(4, -2))->sister()->is('%s×prateta', '%s×pratety');
    
    ////////

    $defs []= RelPath::any()->sibling()->son()->is('synovec', 'synovca');
    $defs []= RelPath::any()->sibling()->child()->son()->is('prasynovec', 'prasynovca');
    $defs []= RelPath::any()->sibling()->child(Times::min(2, -1))->son()->is('%s×prasynovec', '%s×prasynovca');
    $defs []= RelPath::any()->sibling()->daughter()->is('neter', 'netere');
    $defs []= RelPath::any()->sibling()->child()->daughter()->is('praneter', 'pranetere');
    $defs []= RelPath::any()->sibling()->child(Times::min(2, -1))->daughter()->is('%s×praneter', '%s×pranetere');
    
    ////////

    $defs []= RelPath::any()->parent()->sibling()->son()->is('bratranec', 'bratranca');
    
    $defs []= RelPath::any()->parent()->parent(Times::fixed(3))->sibling()->child(Times::fixed(3))->son()->is('bratranec zo 4. kolena', 'bratranca zo 4. kolena');
    $defs []= RelPath::any()->parent()->parent(Times::fixed(5))->sibling()->child(Times::fixed(5))->son()->is('bratranec zo 6. kolena', 'bratranca zo 6. kolena');
    $defs []= RelPath::any()->parent()->parent(Times::fixed(6))->sibling()->child(Times::fixed(6))->son()->is('bratranec zo 7. kolena', 'bratranca zo 7. kolena');

    //IMPL NOTE: used as back-reference (i.e. count must match in '->child($ref)')
    $ref = Times::min(1, 1); 
    $defs []= RelPath::any()->parent()->parent($ref)->sibling()->child($ref)->son()->is('bratranec z %s. kolena', 'bratranca z %s. kolena');

    $defs []= RelPath::any()->parent()->sibling()->daughter()->is('sesternica', 'sesternice');

    $defs []= RelPath::any()->parent()->parent(Times::fixed(3))->sibling()->child(Times::fixed(3))->daughter()->is('sesternica zo 4. kolena', 'sesternice zo 4. kolena');
    $defs []= RelPath::any()->parent()->parent(Times::fixed(5))->sibling()->child(Times::fixed(5))->daughter()->is('sesternica zo 6. kolena', 'sesternice zo 6. kolena');
    $defs []= RelPath::any()->parent()->parent(Times::fixed(6))->sibling()->child(Times::fixed(6))->daughter()->is('sesternica zo 7. kolena', 'sesternice zo 7. kolena');

    $defs []= RelPath::any()->parent()->parent($ref)->sibling()->child($ref)->daughter()->is('sesternica z %s. kolena', 'sesternice z %s. kolena');
  
    return new RelDefs(new Collection($defs));
  }
}
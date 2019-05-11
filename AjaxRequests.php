<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\Webtrees\Module\ExtendedRelationships\FunctionsPrintRels;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Tree;
use Psr\Http\Message\ServerRequestInterface;
use Cissee\WebtreesExt\Requests;

class AjaxRequests {

  public static function printMainSlcas(ServerRequestInterface $request, Tree $tree) {

    $moduleName = Requests::getString($request, 'module');
    $mode = Requests::getInt($request, 'mode');
    $recursion = Requests::getInt($request, 'recursion');
    $showCa = Requests::getBool($request, 'showCa');

    $pid = Requests::getString($request, 'pid');
    $individual = Individual::getInstance($pid, $tree);

    FunctionsPrintRels::printSlcasWrtDefaultIndividual($moduleName, $individual, $mode, $recursion, $showCa);
  }

  public static function printFamilySlcas(ServerRequestInterface $request, Tree $tree) {

    $moduleName = Requests::getString($request, 'module');
    $mode = Requests::getInt($request, 'mode');
    $recursion = Requests::getInt($request, 'recursion');
    $showCa = Requests::getBool($request, 'showCa');    
    $beforeJD = Requests::getIntOrNull($request, 'beforeJD');

    $pid = Requests::getString($request, 'pid');
    $family = Family::getInstance($pid, $tree);

    if ($family->tree()->getPreference('SHOW_PRIVATE_RELATIONSHIPS')) {
      $access_level = Auth::PRIV_HIDE;
    } else {
      $access_level = Auth::accessLevel($family->tree());
    }

    FunctionsPrintRels::printSlcas($moduleName, $family, $access_level, $mode, $recursion, $showCa, $beforeJD);
  }

  public static function getRelationshipLink(ServerRequestInterface $request, Tree $tree) {

    $moduleName = Requests::getString($request, 'module');
    $text = Requests::getStringOrNull($request, 'text');
    $xref1 = Requests::getString($request, 'xref1');
    $xref2 = Requests::getString($request, 'xref2');
    $mode = Requests::getInt($request, 'mode');
    $beforeJD = Requests::getIntOrNull($request, 'beforeJD');

    return ExtendedRelationshipModule::getRelationshipLink($moduleName, $tree, $text, $xref1, $xref2, $mode, $beforeJD);
  }

}

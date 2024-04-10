<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\Webtrees\Module\ExtendedRelationships\FunctionsPrintRels;
use Cissee\WebtreesExt\Requests;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Psr\Http\Message\ServerRequestInterface;

class AjaxRequests {

  public static function printMainSlcas(string $moduleName, ServerRequestInterface $request, Tree $tree) {

    $mode = Requests::getInt($request, 'mode');
    $recursion = Requests::getInt($request, 'recursion');
    $showCa = Requests::getBool($request, 'showCa');

    $pid = Requests::getString($request, 'pid');
    $individual = Registry::individualFactory()->make($pid, $tree);

    FunctionsPrintRels::printSlcasWrtDefaultIndividual($moduleName, $individual, $mode, $recursion, $showCa);
  }

  public static function printFamilySlcas(string $moduleName, ServerRequestInterface $request, Tree $tree) {

    $mode = Requests::getInt($request, 'mode');
    $recursion = Requests::getInt($request, 'recursion');
    $showCa = Requests::getBool($request, 'showCa');
    $beforeJD = Requests::getIntOrNull($request, 'beforeJD');

    $pid = Requests::getString($request, 'pid');
    $family = Registry::familyFactory()->make($pid, $tree);

    if ($family->tree()->getPreference('SHOW_PRIVATE_RELATIONSHIPS')) {
      $access_level = Auth::PRIV_HIDE;
    } else {
      $access_level = Auth::accessLevel($family->tree());
    }

    FunctionsPrintRels::printSlcas($moduleName, $family, $access_level, $mode, $recursion, $showCa, $beforeJD);
  }

  public static function getRelationshipLink(string $moduleName, ServerRequestInterface $request, Tree $tree) {

    $text = Requests::getStringOrNull($request, 'text');
    $xref1 = Requests::getString($request, 'xref1');
    $xref2 = Requests::getString($request, 'xref2');
    $mode = Requests::getInt($request, 'mode');
    $beforeJD = Requests::getIntOrNull($request, 'beforeJD');

    return ExtendedRelationshipModule::getRelationshipLink(
        $moduleName,
        $tree,
        $text,
        $xref1,
        $xref2,
        $mode,
        $beforeJD);
  }

}

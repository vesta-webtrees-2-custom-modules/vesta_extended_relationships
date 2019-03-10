<?php
namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\Webtrees\Module\ExtendedRelationships\FunctionsPrintRels;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Tree;
use Symfony\Component\HttpFoundation\Request;

class AjaxRequests {
	
	public static function printMainSlcas(Request $request, Tree $tree) {

		$moduleName = $request->get('module');
		$mode = intval($request->get('mode'));
		$recursion = intval($request->get('recursion'));
		$showCa = boolval($request->get('showCa'));
		
		$pid = $request->get('pid', Gedcom::REGEX_XREF);
		$individual = Individual::getInstance($pid, $tree);

		FunctionsPrintRels::printSlcasWrtDefaultIndividual($moduleName, $individual, $mode, $recursion, $showCa);
	}
	
	public static function printFamilySlcas(Request $request, Tree $tree) {

		$moduleName = $request->get('module');
		$mode = intval($request->get('mode'));
		$recursion = intval($request->get('recursion'));
		$showCa = boolval($request->get('showCa'));
		$beforeJDraw = $request->get('beforeJD', null);
		$beforeJD = ($beforeJDraw === null)?null:intval($beforeJDraw);
		
		$pid    = $request->get('pid', Gedcom::REGEX_XREF);
		$family = Family::getInstance($pid, $tree);

		if ($family->tree()->getPreference('SHOW_PRIVATE_RELATIONSHIPS')) {
			$access_level = Auth::PRIV_HIDE;
		} else {
			$access_level = Auth::accessLevel($family->tree());
		}
		
		FunctionsPrintRels::printSlcas($moduleName, $family, $access_level, $mode, $recursion, $showCa, $beforeJD);
	}
	
	public static function getRelationshipLink(Request $request, Tree $tree) {

		$moduleName = $request->get('module');
		$text = $request->get('text', null);
		$xref1 = $request->get('xref1');
		$xref2 = $request->get('xref2');
		$mode = intval($request->get('mode'));
		
		$beforeJDraw = $request->get('beforeJD', null);
		$beforeJD = ($beforeJDraw === null)?null:intval($beforeJDraw);
		
		return ExtendedRelationshipModule::getRelationshipLink($moduleName, $tree, $text, $xref1, $xref2, $mode, $beforeJD);
	}
}
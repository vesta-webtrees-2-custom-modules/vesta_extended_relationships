<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\Webtrees\Module\ExtendedRelationships\ExtendedRelationshipController;
use Cissee\WebtreesExt\Functions\FunctionsExt;
use Exception;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Registry;

class FunctionsPrintRels {

  public static function printSlcasWrtDefaultIndividual($moduleName, $person, $mode, $recursion, $showCa) {
    if ($person === null) {
      return;
    }

    $person1 = $person->tree()->significantIndividual(Auth::user());

    if ($person1 === null) {
      return;
    }

    if (!$person1->canShow()) {
      return;
    }

    if ($person === null) {
      return;
    }

    if (!$person->canShow()) {
      return;
    }

    FunctionsPrintRels::printSlcasBetween($moduleName, $person1, $person, $mode, $recursion, $showCa);
  }

  public static function printSlcas($moduleName, Family $family, $access_level, $mode, $recursion, $showCa, $beforeJD = null) {
    $person1 = null;
    foreach ($family->facts(['HUSB'], false, $access_level) as $fact) {
      $person = $fact->target();
      if ($person instanceof Individual) {
        $person1 = $person;
      }
    }

    $person2 = null;
    foreach ($family->facts(['WIFE'], false, $access_level) as $fact) {
      $person = $fact->target();
      if ($person instanceof Individual) {
        $person2 = $person;
      }
    }

    if ($person1 === null) {
      return;
    }

    if (!$person1->canShow()) {
      return;
    }

    if ($person2 === null) {
      return;
    }

    if (!$person2->canShow()) {
      return;
    }

    FunctionsPrintRels::printSlcasBetween($moduleName, $person1, $person2, $mode, $recursion, $showCa, $beforeJD);
  }

  public static function printSlcasBetween($moduleName, $person1, $person2, $mode, $recursion, $showCa, $beforeJD = null) {
    $slcaController = new ExtendedRelationshipController;

    $caAndPaths = $slcaController->calculateCaAndPaths_123456($person1, $person2, $mode, $recursion, $beforeJD);

    $printed = array();

    foreach ($caAndPaths as $caAndPath) {
      $slcaKey = $caAndPath->getCommonAncestor();
      $path = $caAndPath->getPath();

      // Extract the relationship names between pairs of individuals
      $relationships = $slcaController->oldStyleRelationshipPath($person1->tree(), $path);
      if (empty($relationships)) {
        // Cannot see one of the families/individuals, due to privacy;
        continue;
      }

      $rel = FunctionsExt::getRelationshipNameFromPath(implode('', $relationships), $person1, $person2);

      $link = ExtendedRelationshipModule::getRelationshipLink($moduleName, $person1->tree(), $rel, $person1->xref(), $person2->xref(), $mode, $beforeJD);

      $print = /* I18N: (person 1) is (relative, e.g. father) of (person2)*/ I18N::translate('%1$s is %2$s of %3$s.',
                      $person2->fullName(),
                      $link,
                      $person1->fullName());
      $print .= "<br/>";

      if (($slcaKey !== null) && ($showCa)) {
        //add actual common ancestor(s), unless already mentioned)
        //not correct in all cases: INDIs may use different prefixes!
        //$caIsIndi = (substr($slcaKey, 0, 1) === "I");

        $record = Registry::gedcomRecordFactory()->make($slcaKey, $person1->tree());
        $caIsIndi = ($record instanceof Individual);

        if ($caIsIndi) {
          $indi = $record;

          if (($person1 !== $indi) && ($person2 !== $indi)) {
            $html = "";
            $html .= '<a href="' . $indi->url() . '" title="' . strip_tags($indi->fullName()) . '">';
            $html .= $indi->fullName() . '</a>';
            $print .= "(" . I18N::translate('Common ancestor: ') . $html . ")";
            $print .= "<br />";
          }
        } else {
          $caIsFam = ($record instanceof Family);
          if (!$caIsFam) {
            throw new Exception("unexpected class ". get_class($record));
          }
          $fam = $record;

          //huh - just use the standard family name!
          /*
          $names = array();
          foreach ($fam->spouses() as $indi) {
            $html = "";
            $html .= '<a href="' . $indi->url() . '" title="' . strip_tags($indi->fullName()) . '">';
            $html .= $indi->fullName() . '</a>';

            $names[] = $indi->fullName();
          }
          $famName = implode(' & ', $names);
          */
          $famName = $record->fullName();
          
          $html = "";
          $html .= '<a href="' . $fam->url() . '" title="' . strip_tags($famName) . '">';
          $html .= $famName . '</a>';
          $print .= "(" . I18N::translate('Common ancestors: ') . $html . ")";
          $print .= "<br />";
        }
      }

      //there may be different paths leading to the same text (same relationship, same common ancestor)
      //it seems pointless to list these texts here multiple times (in the chart it's ok because the actual - different - paths are shown)
      if (in_array($print, $printed)) {
        continue;
      }

      $printed[] = $print;
      echo $print;
    }
  }

}

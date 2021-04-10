<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Individual;

class ExtendedRelationshipUtils {

  public static function getBornNoLaterThan(Individual $individual): Date {
    $date = $individual->getBirthDate();
    if ($date->isOK()) {
      return $date;
    }

    $dates = array();
    foreach ($individual->facts() as $event) {
      $date = $event->date();
      if ($date->isOK()) {
        $dates[] = $date;
      }
    }

    $minDate = null;
    foreach ($dates as $date) {
      if ($minDate === null) {
        $minDate = $date;
      } else if (Date::compare($date, $minDate) < 0) {
        $minDate = $date;
      }
    }

    if ($minDate === null) {
      return new Date('');
    }
    return $minDate;
  }
  
  public static function getFamilyEstablishedNoLaterThan(Family $family): Date {
    $dates = array();

    $date = $family->getMarriageDate();
    if ($date->isOK()) {
      $dates[] = $date;
    }

    foreach ($family->children() as $child) {
      $date = ExtendedRelationshipUtils::getBornNoLaterThan($child);
      if ($date->isOK()) {
        $dates[] = $date;
      }
    }

    $minDate = null;
    foreach ($dates as $date) {
      if ($minDate === null) {
        $minDate = $date;
      } else if (Date::compare($date, $minDate) < 0) {
        $minDate = $date;
      }
    }

    if ($minDate === null) {
      return new Date('');
    }
    return $minDate;
  }
}

<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\Webtrees\Module\ExtendedRelationships\DirectIndividual;
use Cissee\Webtrees\Module\ExtendedRelationships\DummyDirectTree;
use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Functions\Functions;
use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Place;
use Illuminate\Support\Collection;

class DirectFamily extends GedcomRecord {

  const RECORD_TYPE = 'FAM';
  const ROUTE_NAME = 'family';

  /** @var DirectIndividual|null The husband (or first spouse for same-sex couples) */
  private $husb;

  /** @var DirectIndividual|null The wife (or second spouse for same-sex couples) */
  private $wife;
  private $tree_id;

  public function __construct(string $xref, string $gedcom, string $tree_id) {
    parent::__construct($xref, $gedcom, false, DummyDirectTree::getInstance());

    $this->tree_id = $tree_id;

    // Fetch family members
    if (preg_match_all('/^1 (?:HUSB|WIFE|CHIL) @(.+)@/m', $gedcom, $match)) {
      DirectIndividual::directLoad($tree_id, $match[1]);
    }

    if (preg_match('/^1 HUSB @(.+)@/m', $gedcom, $match)) {
      $this->husb = DirectIndividual::directGetInstance($match[1], $tree_id);
    }
    if (preg_match('/^1 WIFE @(.+)@/m', $gedcom, $match)) {
      $this->wife = DirectIndividual::directGetInstance($match[1], $tree_id);
    }

    // Make sure husb/wife are the right way round.
    if ($this->husb && $this->husb->sex() === 'F' || $this->wife && $this->wife->sex() === 'M') {
      list($this->husb, $this->wife) = array($this->wife, $this->husb);
    }
  }

  //[RC] adjusted
  //cf GedcomRecord::getInstance()
  public static function directGetInstance(string $xref, string $tree_id, string $gedcom = null) {
    // Is this record already in the cache?
    if (isset(self::$gedcom_record_cache[$xref][$tree_id])) {
      $record = self::$gedcom_record_cache[$xref][$tree_id];

      if ($record instanceof static) {
        return $record;
      }
    }

    // Do we need to fetch the record from the database?
    if ($gedcom === null) {
      $gedcom = Family::fetchGedcomRecord($xref, $tree_id);
    }

    $record = new DirectFamily($xref, $gedcom, $tree_id);

    // Store it in the cache
    self::$gedcom_record_cache[$xref][$tree_id] = $record;

    return $record;
  }

  //[RC] adjusted
  public function __toString() {
    return $this->xref . '@' . $this->tree_id;
  }

  //[RC] adjusted wrt Family class:
  //ignore the access_level
  public function facts(array $filter = [], bool $sort = false, int $access_level = null, bool $override = false): Collection {
    $facts = [];
    foreach ($this->facts as $fact) {
      if (($filter === [] || in_array($fact->getTag(), $filter))) {
        $facts[] = $fact;
      }
    }

    if ($sort) {
      Functions::sortFacts($facts);
    }

    return new Collection($facts);
  }

  //[RC] adjusted wrt Family class:
  //ignore the access_level
  public function getHusband($access_level = null) {
    if ($this->husb) {
      return $this->husb;
    }

    return null;
  }

  //[RC] adjusted wrt Family class:
  //ignore the access_level
  public function getWife($access_level = null) {
    if ($this->wife) {
      return $this->wife;
    }

    return null;
  }

  /**
   * Can the name of this record be shown?
   *
   * @param int|null $access_level
   *
   * @return bool
   */
  public function canShowName(int $access_level = null): bool {
    // We can always see the name (Husband-name + Wife-name), however,
    // the name will often be "private + private"
    return true;
  }

  //[RC] adjusted

  /**
   * Find the spouse of a person.
   *
   * @param Individual $person
   * @param int|null   $access_level
   *
   * @return Individual|null
   */
  public function spouse(DirectIndividual $person, $access_level = null) {
    if ($person === $this->wife) {
      return $this->getHusband($access_level);
    }

    return $this->getWife($access_level);
  }

  /**
   * Get the (zero, one or two) spouses from this family.
   *
   * @param int|null $access_level
   *
   * @return Individual[]
   */
  public function spouses($access_level = null): array {
    return array_filter([
        $this->getHusband($access_level),
        $this->getWife($access_level),
    ]);
  }

  //[RC] adjusted

  /**
   * Get a list of this family’s children.
   *
   * @param int|null $access_level
   *
   * @return Individual[]
   */
  public function children($access_level = null): array {
    $children = [];
    foreach ($this->facts(['CHIL'], false, false, false) as $fact) {
      $xref = trim($fact->value(), '@');
      $child = DirectIndividual::directGetInstance($xref, $this->tree_id);

      if ($child) {
        $children[] = $child;
      }
    }

    return $children;
  }

  /**
   * Static helper function to sort an array of families by marriage date
   *
   * @param Family $x
   * @param Family $y
   *
   * @return int
   */
  public static function compareMarrDate(Family $x, Family $y): int {
    return Date::compare($x->getMarriageDate(), $y->getMarriageDate());
  }

  /**
   * Number of children - for the individual list
   *
   * @return int
   */
  public function numberOfChildren(): int {
    $nchi = count($this->children());
    foreach ($this->facts(['NCHI']) as $fact) {
      $nchi = max($nchi, (int) $fact->value());
    }

    return $nchi;
  }

  /**
   * get the marriage event
   *
   * @return Fact|null
   */
  public function getMarriage() {
    return $this->facts(['MARR'])->first();
  }

  /**
   * Get marriage date
   *
   * @return Date
   */
  public function getMarriageDate() {
    $marriage = $this->getMarriage();
    if ($marriage) {
      return $marriage->date();
    }

    return new Date('');
  }

  /**
   * Get the marriage year - displayed on lists of families
   *
   * @return int
   */
  public function getMarriageYear(): int {
    return $this->getMarriageDate()->minimumDate()->year;
  }

  /**
   * Get the marriage place
   *
   * @return Place
   */
  public function getMarriagePlace(): Place {
    $marriage = $this->getMarriage();

    return $marriage->place();
  }

  /**
   * Get a list of all marriage dates - for the family lists.
   *
   * @return Date[]
   */
  public function getAllMarriageDates(): array {
    foreach (Gedcom::MARRIAGE_EVENTS as $event) {
      if ($array = $this->getAllEventDates([$event])) {
        return $array;
      }
    }

    return [];
  }

  /**
   * Get a list of all marriage places - for the family lists.
   *
   * @return Place[]
   */
  public function getAllMarriagePlaces(): array {
    foreach (Gedcom::MARRIAGE_EVENTS as $event) {
      $places = $this->getAllEventPlaces([$event]);
      if (!empty($places)) {
        return $places;
      }
    }

    return [];
  }

  /**
   * Derived classes should redefine this function, otherwise the object will have no name
   *
   * @return string[][]
   */
  public function getAllNames(): array {
    if ($this->getAllNames === null) {
      // Check the script used by each name, so we can match cyrillic with cyrillic, greek with greek, etc.
      $husb_names = [];
      if ($this->husb) {
        $husb_names = array_filter($this->husb->getAllNames(), function (array $x): bool {
          return $x['type'] !== '_MARNM';
        });
      }
      // If the individual only has married names, create a dummy birth name.
      if (empty($husb_names)) {
        $husb_names[] = [
            'type' => 'BIRT',
            'sort' => '@N.N.',
            'full' => I18N::translateContext('Unknown given name', '…') . ' ' . I18N::translateContext('Unknown surname', '…'),
        ];
      }
      foreach ($husb_names as $n => $husb_name) {
        $husb_names[$n]['script'] = I18N::textScript($husb_name['full']);
      }

      $wife_names = [];
      if ($this->wife) {
        $wife_names = array_filter($this->wife->getAllNames(), function (array $x): bool {
          return $x['type'] !== '_MARNM';
        });
      }
      // If the individual only has married names, create a dummy birth name.
      if (empty($wife_names)) {
        $wife_names[] = [
            'type' => 'BIRT',
            'sort' => '@N.N.',
            'full' => I18N::translateContext('Unknown given name', '…') . ' ' . I18N::translateContext('Unknown surname', '…'),
        ];
      }
      foreach ($wife_names as $n => $wife_name) {
        $wife_names[$n]['script'] = I18N::textScript($wife_name['full']);
      }

      // Add the matched names first
      foreach ($husb_names as $husb_name) {
        foreach ($wife_names as $wife_name) {
          if ($husb_name['script'] == $wife_name['script']) {
            $this->getAllNames[] = [
                'type' => $husb_name['type'],
                'sort' => $husb_name['sort'] . ' + ' . $wife_name['sort'],
                'full' => $husb_name['full'] . ' + ' . $wife_name['full'],
                    // No need for a fullNN entry - we do not currently store FAM names in the database
            ];
          }
        }
      }

      // Add the unmatched names second (there may be no matched names)
      foreach ($husb_names as $husb_name) {
        foreach ($wife_names as $wife_name) {
          if ($husb_name['script'] != $wife_name['script']) {
            $this->getAllNames[] = [
                'type' => $husb_name['type'],
                'sort' => $husb_name['sort'] . ' + ' . $wife_name['sort'],
                'full' => $husb_name['full'] . ' + ' . $wife_name['full'],
                    // No need for a fullNN entry - we do not currently store FAM names in the database
            ];
          }
        }
      }
    }

    return $this->getAllNames;
  }

  /**
   * This function should be redefined in derived classes to show any major
   * identifying characteristics of this record.
   *
   * @return string
   */
  public function formatListDetails(): string {
    return
            $this->formatFirstMajorFact(Gedcom::MARRIAGE_EVENTS, 1) .
            $this->formatFirstMajorFact(Gedcom::DIVORCE_EVENTS, 1);
  }

  //////////////////////////////////////////////////////////////////////////////
  //[RC] added functionality
  //TODO: other dates? such as ENGA
  public function getFamilyEstablishedNoLaterThan() {
    $dates = array();

    $date = $this->getMarriageDate();
    if ($date->isOK()) {
      $dates[] = $date;
    }

    foreach ($this->children() as $child) {
      $date = $child->getBornNoLaterThan();
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

  //same algorithm
  public static function getFamilyEstablishedNoLaterThan2(Family $family) {
    $dates = array();

    $date = $family->getMarriageDate();
    if ($date->isOK()) {
      $dates[] = $date;
    }

    foreach ($family->children() as $child) {
      $date = DirectIndividual::getBornNoLaterThan2($child);
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

  //////////////////////////////////////////////////////////////////////////////
}

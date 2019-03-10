<?php
namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\Webtrees\Module\ExtendedRelationships\DirectFamily;
use Cissee\Webtrees\Module\ExtendedRelationships\DummyDirectTree;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Collection;
use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\Functions\Functions;
use Fisharebest\Webtrees\Individual;

class DirectIndividual extends Individual {
	
	private $tree_id;
	
	public function __construct($xref, $gedcom, $tree_id) {
		parent::__construct($xref, $gedcom, false, DummyDirectTree::getInstance());
		
		$this->tree_id = $tree_id;
	}
	
	public static function directLoad($tree_id, array $xrefs) {
    $rows = DB::table('individuals')
        ->where('i_file', '=', $tree_id)
        ->whereIn('i_id', array_unique($xrefs))
        ->select(['i_id AS xref', 'i_gedcom AS gedcom'])
        ->get();

		foreach ($rows as $row) {				
			self::directGetInstance($row->xref, $tree_id, $row->gedcom);
		}
	}
	
	//cf GedcomRecord::getInstance()
	public static function directGetInstance($xref, $tree_id, $gedcom = null) {
		// Is this record already in the cache, and of the correct type?
		if (isset(self::$gedcom_record_cache[$xref][$tree_id])) {
			$record = self::$gedcom_record_cache[$xref][$tree_id];

			if ($record instanceof static) {
				return $record;
			}
		}
		
		// Do we need to fetch the record from the database?
		if ($gedcom === null) {
			$gedcom = Individual::fetchGedcomRecord($xref, $tree_id);
		}
		
		$record = new DirectIndividual($xref, $gedcom, $tree_id);
		
		// Store it in the cache
		self::$gedcom_record_cache[$xref][$tree_id] = $record;

		return $record;
	}
	
	public function __toString() {
		return $this->xref . '@' . $this->tree_id;
	}
		
	//override getFacts(), ignore the access_level
	//[RC] adjusted:
	//ignore the access_level
	public function facts(array $filter = [], bool $sort = false, int $access_level = null, bool $override = false): Collection {
		$facts = array();
		
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
	
	//override childFamilies(), ignore the access_level
	public function childFamilies($access_level = null): Collection {

		$families = array();
		foreach ($this->facts(['FAMC'], false, false, false) as $fact) {
			//$family = $fact->getTarget();
			$xref = trim($fact->getValue(), '@');
			$family = DirectFamily::directGetInstance($xref, $this->tree_id);
			
			if ($family) {
				$families[] = $family;
			}
		}

		return new Collection($families);
	}
	
	//override getSpouseFamilies(), ignore the access_level
	public function spouseFamilies($access_level = null): Collection {

		$families = array();
		foreach ($this->getFacts('FAMS', false, $access_level, true) as $fact) {
			//$family = $fact->getTarget();
			$xref = trim($fact->getValue(), '@');
			$family = DirectFamily::directGetInstance($xref, $this->tree_id);
			
			if ($family) {
				$families[] = $family;
			}
		}

		return new Collection($families);
	}
	
	public function getBornNoLaterThan() {
		$date = $this->getBirthDate();
		if ($date->isOK()) {
			return $date;
		}
		
		$dates = array();
		foreach ($this->facts() as $event) {
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
	
	//same algorithm
	public static function getBornNoLaterThan2(Individual $individual) {
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
}
<?php


namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\WebtreesExt\Functions\FunctionsPrintExtHelpLink;
use Exception;
use Fisharebest\Localization\Locale\LocaleInterface;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\IndividualListModule;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\LocalizationService;
use Fisharebest\Webtrees\Tree;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

class ExtendedIndividualListController extends IndividualListModule {
  
    /** @var LocalizationService */
    private $localization_service;
    private $module;
  
    public function __construct(
        LocalizationService $localization_service,
        ExtendedRelationshipModule $module) {
      
        parent::__construct($localization_service);
        $this->localization_service = $localization_service;
        $this->module = $module;
    }
  
    //originally returns array[][], we adjust this to be able to switch view in surnames-table-switch.phtml
    protected function surnames(
        Tree $tree,
        string $surn,
        string $salpha,
        bool $marnm,
        bool $fams,
        LocaleInterface $locale): array {
    
        if ($surn !== '') {
            //use original function
            return parent::surnames($tree, $surn, $salpha, $marnm, $fams, $locale);
        }
    
        switch ($tree->getPreference('SURNAME_LIST_STYLE')) {
            case 'style1':
            case 'style3':
                //use original function
                return parent::surnames($tree, $surn, $salpha, $marnm, $fams, $locale);
            case 'style2':
            default:
                break;
        }
                    
        $links = DB::table('link')
            ->where('l_file', '=', $tree->id())
            ->whereIn('l_type', ['FAMC', 'HUSB']) //patrilinear ascent only!
            ->select(['l_from', 'l_type', 'l_to'])
            ->get();
    
        $indi2fams = array();
        $fam2indi = array();
        foreach ($links as $link) {
            if ($link->l_type === 'FAMC') {
                //we have to handle potential multiple families here!
                if (!array_key_exists($link->l_from, $indi2fams)) {
                    $indi2fams[$link->l_from] = [];
                }
                $indi2fams[$link->l_from][] = $link->l_to;
            } else {
                $fam2indi[$link->l_from] = $link->l_to;
            }
        }

        $list = $this->surnamesWithPatriarchs(
            $tree, 
            $surn, 
            $salpha, 
            $marnm, 
            $fams, 
            $locale,
            $indi2fams, 
            $fam2indi);
    
        $wrapped = [];
            
        $helpLink = FunctionsPrintExtHelpLink::helpLink($this->module->name(), 'Patriarch');
        $wrapped[] = new SurnamesWithPatriarchs($list, $helpLink);
        return $wrapped;
    }
  
    protected function surnamesWithPatriarchs(
        Tree $tree,
        string $surn,
        string $salpha,
        bool $marnm,
        bool $fams,
        LocaleInterface $locale,
        array $indi2fams, 
        array $fam2indi): array {
    
        $collation = $this->localization_service->collation($locale);

        $query = DB::table('name')
            ->where('n_file', '=', $tree->id())
            ->select([
                new Expression('UPPER(n_surn /*! COLLATE ' . $collation . ' */) AS n_surn'),
                new Expression('n_surname /*! COLLATE utf8_bin */ AS n_surname'),
                //new Expression('COUNT(*) AS total'),

                //[RC] added
                new Expression('n_id'),
            ]);

        $this->whereFamily($fams, $query);
        $this->whereMarriedName($marnm, $query);

        if ($surn !== '') {
            $query->where('n_surn', '=', $surn);
        } elseif ($salpha === ',') {
            $query->where('n_surn', '=', '');
        } elseif ($salpha === '@') {
            $query->where('n_surn', '=', Individual::NOMEN_NESCIO);
        } elseif ($salpha !== '') {
            $this->whereInitial($query, 'n_surn', $salpha, $locale);
        } else {
            // All surnames
            $query->whereNotIn('n_surn', ['', Individual::NOMEN_NESCIO]);
        }
        $query
            //[RC] removed
            //->groupBy(['n_surn'])
            //[RC] removed
            //->groupBy(['n_surname'])
            ->orderBy('n_surname');

        $list = [];
        $patriarchsXrefs = [];

        foreach ($query->get() as $row) {
          $xref = $row->n_id;
          $patriarchs = $this->getPatriarchs($indi2fams, $fam2indi, $xref, []);

          //individuals are counted once per family in which they appear as a child
          //bit hacky but easiest solution for adoptions etc.
          foreach ($patriarchs as $patriarch) {
            $patriarchsXrefs[] = $patriarch;

            $surn = $row->n_surn;
            $surname = $row->n_surname . ' ' . $patriarch;

            if (!array_key_exists($surn, $list) || !array_key_exists($surname, $list[$surn])) {
              $list[$surn][$surname] = new SurnameWithPatriarch($row->n_surname, $patriarch, ($patriarch === $xref));
            } else {
              $list[$surn][$surname]->increment();
            }
          }      
        }

        //preload patriarchs
        self::load($tree, $patriarchsXrefs);
    
        return $list;
    }
  
    //function was in Individual, gone in webtrees 2.1
    //perhaps considered unnecessary optimization?
    /**
     * Sometimes, we'll know in advance that we need to load a set of records.
     * Typically when we load families and their members.
     *
     * @param Tree     $tree
     * @param string[] $xrefs
     *
     * @return void
     */
    public static function load(Tree $tree, array $xrefs): void {
        $rows = DB::table('individuals')
            ->where('i_file', '=', $tree->id())
            ->whereIn('i_id', array_unique($xrefs))
            ->select(['i_id AS xref', 'i_gedcom AS gedcom'])
            ->get();

        foreach ($rows as $row) {
            Registry::individualFactory()->make($row->xref, $tree, $row->gedcom);
        }
    }
  
    //originally returns Collection<Individual>, we adjust this to be able to switch view in individuals-table-switch.phtml
    public function individuals(
        Tree $tree,
        string $surn,
        string $salpha,
        string $galpha,
        bool $marnm,
        bool $fams,
        LocaleInterface $locale): Collection {
    
        $originalCollection = parent::individuals($tree, $surn, $salpha, $galpha, $marnm, $fams, $locale);

        $links = DB::table('link')
                ->where('l_file', '=', $tree->id())
                ->whereIn('l_type', ['FAMC', 'HUSB']) //patrilinear ascent only!
                ->select(['l_from', 'l_type', 'l_to'])
                ->get();

        $indi2fams = array();
        $fam2indi = array();
        foreach ($links as $link) {
            if ($link->l_type === 'FAMC') {
                //we have to handle potential multiple families here!
                if (!array_key_exists($link->l_from, $indi2fams)) {
                    $indi2fams[$link->l_from] = [];
                }
                $indi2fams[$link->l_from][] = $link->l_to;
            } else {
                $fam2indi[$link->l_from] = $link->l_to;
            }
        }

        $list = [];
        $patriarchsXrefs = [];

        foreach ($originalCollection as $individual) {
            $patriarchs = $this->getPatriarchs($indi2fams, $fam2indi, $individual->xref(), []);

            foreach ($patriarchs as $patriarch) {
                $patriarchsXrefs[] = $patriarch;
            }

            $list[$individual->xref()] = $patriarchs;            
        }

        $wrapped = [];
        $wrapped[] = new IndividualsWithPatriarchs($originalCollection, $list);
        return new Collection($wrapped);
    }
  
    protected function families(Tree $tree, $surn, $salpha, $galpha, $marnm, LocaleInterface $locale): Collection {
        $families = new Collection();
    
        foreach (parent::individuals($tree, $surn, $salpha, $galpha, $marnm, true, $locale) as $indi) {
            foreach ($indi->spouseFamilies() as $family) {
                $families->push($family);
            }
        }

        return $families->unique();
    }
    
    protected function getPatriarchs(
        array $indi2fams, 
        array $fam2indi, 
        string $xref,
        array $visited): array {
    
        if (!array_key_exists($xref, $indi2fams)) {
            return [$xref];
        }

        $fams = $indi2fams[$xref];

        if (sizeof($fams) === 0) {
            throw new Exception;
        }

        $ret = [];
        foreach ($fams as $fam) {
            if (!array_key_exists($fam, $fam2indi)) {
                $ret[] = $xref;
                continue;
            }

            $next = $fam2indi[$fam];

            if (in_array($next, $visited)) {
                //loop detected
                $ret[] = $xref;
                continue;
            }

            $visited[] = $next;

            $next = $this->getPatriarchs($indi2fams, $fam2indi, $next, $visited);

            foreach ($next as $n) {
                $ret[] = $n;
            }
        }

        return $ret;
    }
}

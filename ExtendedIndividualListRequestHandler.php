<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\WebtreesExt\Functions\FunctionsPrintExtHelpLink;
use Exception;
use Fisharebest\Localization\Locale\LocaleInterface;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\IndividualListModule;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

class ExtendedIndividualListRequestHandler extends IndividualListModule {

    private $module;

    public function __construct(
        ExtendedRelationshipModule $module) {

        $this->module = $module;
    }

    /**
     * //RC adjusted: return SurnameWithPatriarch rather than int
     * Get a count of all surnames and variants.
     *
     * @param Tree $tree
     * @param bool $marnm if set, include married names
     * @param bool $fams  if set, only consider individuals with FAMS records
     *
     * @return array<array<SurnameWithPatriarch>>
     */
    protected function allSurnames(
        Tree $tree,
        bool $marnm,
        bool $fams): array {

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
        
        $helpLink = FunctionsPrintExtHelpLink::helpLink($this->module->name(), 'Patriarch');

        $list = $this->allSurnamesWithPatriarchs(
            $tree,
            $marnm,
            $fams,
            $indi2fams,
            $fam2indi,
            $helpLink);

        return $list;
        
        /*
        $wrapped = [];

        
        $wrapped[] = new SurnamesWithPatriarchs($list, $helpLink);
        return $wrapped;
        */
    }

    protected function allSurnamesWithPatriarchs(
        Tree $tree,
        bool $marnm,
        bool $fams,
        array $indi2fams,
        array $fam2indi,
        string $helpLink): array {

        $query = DB::table('name')
            ->where('n_file', '=', $tree->id())
            ->select([
            new Expression('n_surn /*! COLLATE utf8_bin */ AS n_surn'),
            new Expression('n_surname /*! COLLATE utf8_bin */ AS n_surname'),
            //new Expression('COUNT(*) AS total'),
            //[RC] added
            new Expression('n_id'),
        ]);

        $this->whereFamily($fams, $query);
        $this->whereMarriedName($marnm, $query);

        // All surnames
        $query->whereNotIn('n_surn', ['', Individual::NOMEN_NESCIO]);

        //[RC] removed
        /*
          $query->groupBy([
          $this->binaryColumn('n_surn'),
          $this->binaryColumn('n_surname'),
          ]);
         */

        $query
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
                    $list[$surn][$surname] = new SurnameWithPatriarch(
                        $row->n_surname, 
                        $patriarch, 
                        ($patriarch === $xref),
                        $helpLink);
                    
                } else {
                    $list[$surn][$surname]->increment();
                }
            }
        }

        //preload patriarchs
        self::load($tree, $patriarchsXrefs);
        
        return $list;
    }

    /**
     * Extract initial letters and counts for all surnames.
     *
     * @param array<array<SurnameWithPatriarch>> $all_surnames
     *
     * @return array<int>
     */
    protected function surnameInitials(array $all_surnames): array
    {
        $initials    = [];

        // Ensure our own language comes before others.
        foreach (I18N::language()->alphabet() as $initial) {
            $initials[$initial]    = 0;
        }

        foreach ($all_surnames as $surn => $surnames) {
            $initial = I18N::language()->initialLetter($surn);

            $initials[$initial] ??= 0;
            
            $surnamesCollection = new Collection($surnames);
            
            $initials[$initial] += 
                //[RC] adjusted
                //array_sum($surnames);
                $surnamesCollection->sum(function (SurnameWithPatriarch $surnameWithPatriarch) {
                    return $surnameWithPatriarch->getCount();
                });
        }

        // Move specials to the end
        $count_none = $initials[''] ?? 0;

        if ($count_none > 0) {
            unset($initials['']);
            $initials[','] = $count_none;
        }

        $count_unknown = $initials['@'] ?? 0;

        if ($count_unknown > 0) {
            unset($initials['@']);
            $initials['@'] = $count_unknown;
        }

        return $initials;
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

    /**
     * Fetch a list of individuals with specified names
     * To search for unknown names, use $surn="@N.N.", $salpha="@" or $galpha="@"
     * To search for names with no surnames, use $salpha=","
     *
     * @param Tree            $tree
     * @param string          $surname  if set, only fetch people with this n_surn
     * @param array<string>   $surnames if set, only fetch people with this n_surname
     * @param string          $galpha   if set, only fetch given names starting with this letter
     * @param bool            $marnm    if set, include married names
     * @param bool            $fams     if set, only fetch individuals with FAMS records
     *
     * @return Collection<int,IndividualsWithPatriarchs>
     */
    protected function individuals(
        Tree $tree, 
        string $surname, 
        array $surnames, 
        string $galpha, 
        bool $marnm, 
        bool $fams): Collection {

        $originalCollection = parent::individuals($tree, $surname, $surnames, $galpha, $marnm, $fams);

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

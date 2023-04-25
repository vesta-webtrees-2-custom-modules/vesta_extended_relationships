<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\WebtreesExt\Functions\FunctionsPrintExtHelpLink;
use Exception;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\IndividualListModule;
use Fisharebest\Webtrees\Module\ModuleListInterface;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExtendedIndividualListRequestHandler extends IndividualListModule {

    private $module;

    public function __construct(
        ExtendedRelationshipModule $module) {

        $this->module = $module;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();
        $user = Validator::attributes($request)->user();

        Auth::checkComponentAccess($this, ModuleListInterface::class, $tree, $user);

        $surname_param = Validator::queryParams($request)->string('surname', '');
        
        //preserve original surname (nicer for display)
        //$surname = I18N::strtoupper(I18N::language()->normalize($surname_param));
        $surname = $surname_param;
        
        if ('' !== $surname) {
            //but add special character so that no 'variants' (which are actually used to transport patriarchs) match for header display/actual search
            $unicodeChar = "\u{FEFF}"; //ZERO WIDTH NO-BREAK SPACE
            $surname = $surname_param.$unicodeChar;
        }        

        $params = [
            'alpha'               => Validator::queryParams($request)->string('alpha', ''),
            'falpha'              => Validator::queryParams($request)->string('falpha', ''),
            'show'                => Validator::queryParams($request)->string('show', 'surn'),
            'show_all'            => Validator::queryParams($request)->string('show_all', 'no'),
            'show_all_firstnames' => Validator::queryParams($request)->string('show_all_firstnames', 'no'),
            'show_marnm'          => Validator::queryParams($request)->string('show_marnm', ''),
            'surname'             => $surname,
        ];

        //never true after our adjustment above
        /*
        if ($surname_param !== $surname) {
            return Registry::responseFactory()->redirectUrl($this->listUrl($tree, $params));
        }
        */

        //Issue #101
        //set dummy preferences at $tree
        //so that actual preferences aren't loaded
        //so that switch in 'switch ($tree->getPreference('SURNAME_LIST_STYLE'))' ends up in default case
        $reflection = new \ReflectionClass(get_class($tree));
        $preferences = $reflection->getProperty('preferences');
        $preferences->setAccessible(true);
        $preferences->setValue($tree, ['placeholder'=>'placeholder']);
      
        return $this->createResponse($tree, $user, $params, false);
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
            //[RC] note
            //webtrees capitalizes here 
            //to align with surname from request for variants
            //and/or to get proper results in queries?
            //(the request parameter is capitalized in IndividualListModule.handle())
            //we override handle() and use the original surname (mainly for display)
            //$row->n_surn = $row->n_surn === '' ? $row->n_surname : $row->n_surn;
            //$row->n_surn = I18N::strtoupper(I18N::language()->normalize($row->n_surn));
            
            $xref = $row->n_id;
            $patriarchs = $this->getPatriarchs($indi2fams, $fam2indi, $xref, []);

            //individuals are counted once per family in which they appear as a child
            //bit hacky but easiest solution for adoptions etc.
            foreach ($patriarchs as $patriarch) {
                $patriarchsXrefs[] = $patriarch;

                //in IndividualListModule.createResponse(), $surn from $list[$surn][$surname] is compared with the 'xxx'root/canonical' surname parameter 
                //"The surname parameter is a root/canonical form. Display the actual surnames found."
                //
                //if there is a match, all $surname from $list[$surn][$surname] are displayed as variants in the list header
                //(otherwise, header falls back to $surname)
                //we do not want to display these variants, which we actually use to transport the patriarchs(*), therefore:
                //we adjust the 'surname parameter' (in overridden handle())
                //to something that displays properly but doesn't match anything from the array.
                //
                //(*) obviously the original variants info gets lost this way. Would be better to have both!
                //but that would require an extended re-implementation of the original list code
                $surn = $row->n_surn;
                
                //originally the surname variant, re-purposed for patriarch
                $surname = $patriarch;
                
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

        //no longer do this now that all surnames are always loaded!
        //preload patriarchs
        //self::load($tree, $patriarchsXrefs);
        
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
        
        ///handle #91 'Prepared statement contains too many placeholders'
        $coll = new Collection($xrefs);
        
        foreach ($coll->chunk(256) as $chunk) {
            $rows = DB::table('individuals')
                ->where('i_file', '=', $tree->id())
                ->whereIn('i_id', array_unique($chunk->all()))
                ->select(['i_id AS xref', 'i_gedcom AS gedcom'])
                ->get();

            foreach ($rows as $row) {
                Registry::individualFactory()->make($row->xref, $tree, $row->gedcom);
            }
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

        //'repair' adjusted $surname (cf handle())
        $surname = mb_substr($surname, 0, -1);
        
        //NOW normalize, apparently this is (sometimes?) required for correct query in parent::individuals
        //Issue #103
        //no - don't do this: leads to missing results when used with name with umlaut and language German:
        //normalization shoud only be done for display, not for querying -
        //don't really understand how this works in webtrees when $siurnames is empty
        //$surname = I18N::strtoupper(I18N::language()->normalize($surname));
        $surname = I18N::strtoupper($surname);
        
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

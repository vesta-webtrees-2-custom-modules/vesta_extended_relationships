<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\Webtrees\Module\ExtendedRelationships\OptimizedDijkstra;
use Cissee\Webtrees\Module\ExtendedRelationships\Sync;
use Cissee\WebtreesExt\Modules\RelationshipPath;
use Closure;
use Exception;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Tree;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;

class SomeTiebreaker implements DijkstraTiebreakerFunction {

    private int $total;
    private int $ongoing;

    public function __construct(
        int $total,
        int $ongoing) {

        $this->total = $total;
        $this->ongoing = $ongoing;
    }

    public static function create(): SomeTiebreaker {
        return new SomeTiebreaker(0, 0);
    }

    public function next(int $weight): void {
        //error_log("next".$weight);
        if ($weight === 1000) {
            //larger runs via common ancestors are considered to be better
            $this->total += $this->ongoing * $this->ongoing;

            //start next block
            $this->ongoing = 0;
        } else {
            $this->ongoing += $weight;
        }
    }

    public function conclude(): int {
        $this->total += $this->ongoing * $this->ongoing;

        //smaller = better!
        $this->total *= -1;

        return $this->total;
    }
}

class Descendant {

    private $xref; //xref

    public function xref(): string {
        return $this->xref;
    }

    public function __construct($xref) {
        $this->xref = (string) $xref;
    }

}

class IdWithDescendant {

    private $id; //xref
    private $descendant; //Descendant

    public function getId(): string {
        return $this->id;
    }

    public function getDescendant(): Descendant {
        return $this->descendant;
    }

    public function xref() {
        return $this->getDescendant()->xref();
    }

    public function __construct($id, Descendant $descendant) {
        $this->id = (string) $id;
        $this->descendant = $descendant;
    }

    /**
     *
     * @return IdWithPathElement
     */
    public function next($id) {
        return new IdWithDescendant($id, new Descendant($this->getId()));
    }

}

class CommonAncestorAndPath {

    private $ca; //xref
    private $path; //string[]
    private int $size;
    private int $shortestLeg;

    public function getCommonAncestor(): ?string {
        return $this->ca;
    }

    public function getPath() {
        return $this->path;
    }

    public function getSize() {
        return $this->size;
    }

    public function getShortestLeg() {
        return $this->shortestLeg;
    }

    public function __construct($ca, $path) {
        $this->ca = ($ca === null) ? null : (string) $ca;
        $this->path = array_map(ExtendedRelationshipController::stringMapper(), $path);
        $this->size = count($this->path);

        if ($this->ca === null) {
            $this->shortestLeg = PHP_INT_MAX;
        } else {
            $key = array_search($this->ca, $this->path);
            if ($key === false) {
               $this->shortestLeg = PHP_INT_MAX;
            } else {
               $index = array_search($key, array_keys($this->path));
               $this->shortestLeg = min($index, $this->size - ($index + 1));
            }
        }

        //error_log(print_r($this, true));
    }

    /**
     *
     * @return IdWithPathElement
     */
    public function next($id) {
        return new IdWithDescendant($id, new Descendant($this->getId()));
    }

}

class CorPlus {

    private $cor;
    private $actuallyBetterThan;
    private $equivalentRelationships;

    /**
     *
     * @return double
     */
    public function getCor() {
        return $this->cor;
    }

    /**
     *
     * @return int only abs value is relevant
     */
    public function getActuallyBetterThan() {
        return $this->actuallyBetterThan;
    }

    /**
     *
     * @return string[]|null
     */
    public function getEquivalentRelationships() {
        return $this->equivalentRelationships;
    }

    public function __construct($cor, $actuallyBetterThan, $equivalentRelationships) {
        $this->cor = $cor;
        $this->actuallyBetterThan = $actuallyBetterThan;
        $this->equivalentRelationships = $equivalentRelationships;
    }

}

/**
 * Controller for the relationships calculations
 */
class ExtendedRelationshipController {

    public function getCorFromPaths($paths) {
        $cor = 0.0;
        foreach ($paths as $path) {
            $pathSegments = count($path) - 1;

            //if ca is INDI, we have to add a single path for this ca.
            //
            //if ca is FAM, we actually have to add two paths (one per family spouse) of length $pathSegments+2
            //i.e. the formula is
            //$cor += 2*pow(2,-($pathSegments+2)/2);
            //which is the same as
            //$cor += pow(2,-$pathSegments/2);
            //so we don't actually have to distinguish the two cases here!
            //in each case,
            //divide by 2 to collapse all 'INDI - FAM - INDI' segments to 'INDI - INDI' segments

            $cor += pow(2, -$pathSegments / 2);
        }
        return $cor;
    }

    public function getCorFromCaAndPaths($tree, $caAndPaths) {
        $bestPathLength = null;
        $bestPath = null;

        $cor = 0.0;
        foreach ($caAndPaths as $caAndPath) {
            $path = $caAndPath->getPath();
            $pathSegments = count($path) - 1;

            //if ca is INDI, we have to add a single path for this ca.
            //
            //if ca is FAM, we actually have to add two paths (one per family spouse) of length $pathSegments+2
            //i.e. the formula is
            //$cor += 2*pow(2,-($pathSegments+2)/2);
            //which is the same as
            //$cor += pow(2,-$pathSegments/2);
            //so we don't actually have to distinguish the two cases here!
            //in each case,
            //divide by 2 to collapse all 'INDI - FAM - INDI' segments to 'INDI - INDI' segments

            $singleCor = pow(2, -$pathSegments / 2);
            $cor += $singleCor;

            if (!$bestPathLength) {
                $bestPathLength = $pathSegments / 2;
                $bestPath = $path;
            }
        }

        $actuallyBetterThan = 1;
        $equivalentPathLength = -(log($cor, 2));
        $eplFraction = $equivalentPathLength - floor($equivalentPathLength);
        if ($eplFraction < 0.001) {
            $actuallyBetterThan = 0;
        } else if ($eplFraction < 0.5) {
            $actuallyBetterThan = -1;
        }
        $equivalentPathLength = (int) round($equivalentPathLength);
        if ($equivalentPathLength == $bestPathLength) {
            return new CorPlus($cor, $actuallyBetterThan, null);
        }

        //create an equivalent relationship that's similar 'in character'
        //to the relationship established via the closest ca

        $relationships = RelationshipPath::create($tree, $bestPath)->elements();
        $up = array();
        $right = array();
        $dn = array();
        foreach ($relationships as $relationship) {
            $rel = $relationship->rel();
            if (("mot" === $rel) || ("fat" === $rel)) {
                $up[] = $rel;
            } else if (("bro" === $rel) || ("sis" === $rel)) {
                $right[] = $rel;
            } else {
                $dn[] = $rel;
            }
        }

        $toRemove = $bestPathLength - $equivalentPathLength;
        $upShortened = sizeof($up);
        $dnShortened = sizeof($dn);
        $diff = abs(sizeof($up) - sizeof($dn));
        if ($toRemove <= $diff) {
            //remove entirely from longer
            if (sizeof($up) > sizeof($dn)) {
                $upShortened -= $toRemove;
            } else {
                $dnShortened -= $toRemove;
            }
        } else {
            if ($diff === 0) {
                $toRemoveFromBoth = floor($toRemove / 2);
                $upShortened -= $toRemoveFromBoth;
                $dnShortened -= $toRemoveFromBoth;
                if ($toRemove % 2 !== 0) {
                    //we have to pick one
                    $upShortened -= 1;
                }
            } else {
                //remove as much as possible from longer
                $toRemoveFromBoth = ceil(($toRemove - $diff) / 2);
                $toRemoveFromLonger = $toRemove - $toRemoveFromBoth * 2;

                $upShortened -= $toRemoveFromBoth;
                $dnShortened -= $toRemoveFromBoth;
                if (sizeof($up) > sizeof($dn)) {
                    $upShortened -= $toRemoveFromLonger;
                } else {
                    $dnShortened -= $toRemoveFromLonger;
                }
            }
        }

        //shorten 'least significant' parts of path
        $shortenedRelationships = array();
        for ($i = 0; $i < $upShortened; $i++) {
            $shortenedRelationships[] = $up[$i + sizeof($up) - $upShortened];
        }
        //0 or 1 times
        foreach ($right as $rel) {
            $shortenedRelationships[] = $rel;
        }
        for ($i = 0; $i < $dnShortened; $i++) {
            $shortenedRelationships[] = $dn[$i + sizeof($dn) - $dnShortened];
        }

        return new CorPlus($cor, $actuallyBetterThan, $shortenedRelationships);
    }

    public function calculateRelationships_123456(
        Individual $individual1,
        Individual $individual2,
        $mode,
        $recursion,
        $beforeJD = null) {

        return $this->x_calculateRelationships_123456(
            $individual1->tree(),
            $individual1->xref(),
            $individual2->xref(),
            $mode,
            $recursion,
            $beforeJD);
    }

    public function x_calculateRelationships_123456(
        $tree,
        $xref1,
        $xref2,
        $mode,
        $recursion,
        $beforeJD = null) {

        if ($mode === 1) {
            //single slca
            $caAndPaths = $this->x_calculateRelationships_slca($tree, $xref1, $xref2, $mode);
            $paths = array();
            foreach ($caAndPaths as $caAndPath) {
                $paths[] = $caAndPath->getPath();
            }
            return $paths;
        }

        if ($mode === 2) {
            //all slcas
            $caAndPaths = $this->x_calculateRelationships_slca($tree, $xref1, $xref2, $mode);
            $paths = array();
            foreach ($caAndPaths as $caAndPath) {
                $paths[] = $caAndPath->getPath();
            }
            return $paths;
        }

        if ($mode === 3) {
            //lcas: all paths for CoR (uncorrected coefficient of relationship)
            $caAndPaths = $this->x_calculateRelationships_slca($tree, $xref1, $xref2, $mode);
            $paths = array();
            foreach ($caAndPaths as $caAndPath) {
                $paths[] = $caAndPath->getPath();
            }
            return $paths;
        }

        if ($mode === 4) {
            //adjusted original algorithm + dated links
            $ret = $this->x_calculateRelationships_withWeights($tree, $xref1, $xref2, $beforeJD);
            if (empty($ret) && $beforeJD) {
                //beforeJD and nothing found - check if we can at least provide results via common ancestors
                //(dated links may not have found anything due to insufficient dates)
                return $this->x_calculateRelationships_123456($tree, $xref1, $xref2, 1, $recursion);
            }
            return $ret;
        }

        if ($mode === 5) {
            //original algorithm, optimized + dated links
            $ret = $this->x_calculateRelationships_optimized($tree, $xref1, $xref2, 0, $beforeJD);
            if (empty($ret) && $beforeJD) {
                //beforeJD and nothing found - check if we can at least provide results via common ancestors
                //(dated links may not have found anything due to insufficient dates)
                return $this->x_calculateRelationships_123456($tree, $xref1, $xref2, 1, $recursion);
            }
            return $ret;
        }

        if ($mode === 6) {
            //original algorithm, optimized + dated links
            $ret = $this->x_calculateRelationships_optimized($tree, $xref1, $xref2, $recursion, $beforeJD);
            if (empty($ret) && $beforeJD) {
                //beforeJD and nothing found - check if we can at least provide results via common ancestors
                //(dated links may not have found anything due to insufficient dates)
                return $this->x_calculateRelationships_123456($tree, $xref1, $xref2, 1, $recursion);
            }
            return $ret;
        }

        //1 with fallback to 5
        if ($mode === 7) {
            //1
            $ret = $this->x_calculateRelationships_123456($tree, $xref1, $xref2, 1, $recursion);
            if (!empty($ret)) {
                return $ret;
            }
            //5 (directly: if $beforeJD, we don't have to fallback again to 1)
            return $this->x_calculateRelationships_optimized($tree, $xref1, $xref2, 0, $beforeJD);
            //return $this->x_calculateRelationships_123456($tree, $xref1, $xref2, 5, $recursion, $beforeJD);
        }

        return "unexpected mode!";
    }

    public function calculateCaAndPaths_123456(Individual $individual1, Individual $individual2, $mode, $recursion, $beforeJD = null) {
        if ($individual1->xref() === $individual2->xref()) {
            return [];
        }

        return $this->x_calculateCaAndPaths_123456($individual1->tree(), $individual1->xref(), $individual2->xref(), $mode, $recursion, $beforeJD);
    }

    public function x_calculateCaAndPaths_123456($tree, $xref1, $xref2, $mode, $recursion, $beforeJD = null) {
        if ($mode === 1) {
            //single slca
            return $this->x_calculateRelationships_slca($tree, $xref1, $xref2, $mode);
        }

        if ($mode === 2) {
            //all slcas
            return $this->x_calculateRelationships_slca($tree, $xref1, $xref2, $mode);
        }

        if ($mode === 3) {
            //lcas: all paths for CoR (uncorrected coefficient of relationship)
            return $this->x_calculateRelationships_slca($tree, $xref1, $xref2, $mode);
        }

        if ($mode === 4) {
            //adjusted original algorithm + dated links
            $paths = $this->x_calculateRelationships_withWeights($tree, $xref1, $xref2, $beforeJD);
            if (empty($paths) && $beforeJD) {
                //beforeJD and nothing found - check if we can at least provide results via common ancestors
                //(dated links may not have found anything due to insufficient dates)
                return $this->x_calculateCaAndPaths_123456($tree, $xref1, $xref2, 1, $recursion);
            }
            $caAndPaths = array();
            foreach ($paths as $path) {
                $caAndPaths[] = new CommonAncestorAndPath(null, $path);
            }
            return $caAndPaths;
        }

        if ($mode === 5) {
            //original algorithm, optimized + dated links
            $paths = $this->x_calculateRelationships_optimized($tree, $xref1, $xref2, 0, $beforeJD);
            if (empty($paths) && $beforeJD) {
                //beforeJD and nothing found - check if we can at least provide results via common ancestors
                //(dated links may not have found anything due to insufficient dates)
                return $this->x_calculateCaAndPaths_123456($tree, $xref1, $xref2, 1, $recursion);
            }
            $caAndPaths = array();
            foreach ($paths as $path) {
                $caAndPaths[] = new CommonAncestorAndPath(null, $path);
            }
            return $caAndPaths;
        }

        if ($mode === 6) {
            //original algorithm, optimized + dated links
            $paths = $this->x_calculateRelationships_optimized($tree, $xref1, $xref2, $recursion, $beforeJD);
            if (empty($paths) && $beforeJD) {
                //beforeJD and nothing found - check if we can at least provide results via common ancestors
                //(dated links may not have found anything due to insufficient dates)
                return $this->x_calculateCaAndPaths_123456($tree, $xref1, $xref2, 1, $recursion);
            }
            $caAndPaths = array();
            foreach ($paths as $path) {
                $caAndPaths[] = new CommonAncestorAndPath(null, $path);
            }
            return $caAndPaths;
        }

        //mode 7: 1 with fallback to 5
        if ($mode === 7) {
            //1
            $ret = $this->x_calculateCaAndPaths_123456($tree, $xref1, $xref2, 1, $recursion);
            if (!empty($ret)) {
                return $ret;
            }
            //5 (directly: if $beforeJD, we don't have to fallback again to 1)
            $paths = $this->x_calculateRelationships_optimized($tree, $xref1, $xref2, 0, $beforeJD);
            $caAndPaths = array();
            foreach ($paths as $path) {
                $caAndPaths[] = new CommonAncestorAndPath(null, $path);
            }
            return $caAndPaths;
            //return $this->x_calculateCaAndPaths_123456($tree, $xref1, $xref2, 5, $recursion, $beforeJD);
        }

        //throw new Exception("unexpected mode!");

        //Issue #125
        //just return empty
        return [];
    }

    public static function compareCommonAncestorAndPath(
        CommonAncestorAndPath $a,
        CommonAncestorAndPath $b) {

        $cmp = $a->getSize() <=> $b->getSize();

        if ($cmp !== 0) {
            return $cmp;
        }

        //tiebreaker: prefer $ca closed to either end of path (grandmother before aunt etc)
        return $a->getShortestLeg() <=> $b->getShortestLeg();
    }

    public function calculateRelationships_slca(Individual $individual1, Individual $individual2, $mode) {
        return $this->x_calculateRelationships_slca($individual1->tree(), $individual1->xref(), $individual2->xref(), $mode);
    }

    /**
     * 'naive' algorithm (no precomputations), performance seems to be sufficient for ~max. 15 generations
     *
     * @param String  $xref1
     * @param String  $xref2
     * @param integer $mode
     *
     * @return CommonAncestorAndPath[]
     */
    public function x_calculateRelationships_slca(Tree $tree, $xref1, $xref2, $mode) {

        $rows = DB::table('link')
            ->where('l_file', '=', $tree->id())
            ->whereIn('l_type', ['FAMC', 'HUSB', 'WIFE'])
            ->select(['l_from', 'l_to'])
            ->get();

        $graph = array();
        foreach ($rows as $row) {
            if (!array_key_exists($row->l_from, $graph)) {
                $graph[$row->l_from] = array();
            }
            $graph[$row->l_from][] = $row->l_to;
        }

        $queue1 = array(); //key = (generated); value = IdWithDescendant;
        $queue1[] = new IdWithDescendant($xref1, new Descendant(null));
        $ancestors1 = array(); //key = id; value = array of (key = Descendant xref, value = Descendant);

        while (!is_null($current = array_shift($queue1))) {
            //echo "in queue:".$current;
            if (($mode != 3) && array_key_exists($current->getId(), $ancestors1)) {
                //implex
                //echo "already there!";
            } else {
                //add to ancestors
                if (!array_key_exists($current->getId(), $ancestors1)) {
                    $ancestors1[$current->getId()] = array();
                }
                //add (effectively no-op if combination already exists)
                $ancestors1[$current->getId()][$current->xref()] = $current->getDescendant();

                //add ancestors to queue
                if (array_key_exists($current->getId(), $graph)) {
                    foreach ($graph[$current->getId()] as $next) {
                        $queue1[] = $current->next($next);
                    }
                }
            }
        }

        $queue2 = array(); //key = (generated); value = Descendant;
        $queue2[] = new IdWithDescendant($xref2, new Descendant(null));
        $ancestors2 = array(); //key = id; value = array of (key = Descendant xref, value = Descendant);
        //cas = common ancestors
        //lcas = lowest common ancestors
        //slcas = smallest lowest common ancestors
        //if (mode == 3), later filtered to lcas
        //if (mode != 3), only collects some lcas, later filtered to slcas
        $cas = array(); //key = id; value = array of (key = Descendant xref, value = Descendant);

        while (!is_null($current = array_shift($queue2))) {
            //echo "in queue:".$current;
            if (($mode != 3) && array_key_exists($current->getId(), $ancestors2)) {
                //implex
                //echo "already there 2: "; self::debug_echo($current->getId());
            } else {
                //is it a common ancestor?
                if (array_key_exists($current->getId(), $ancestors1)) {
                    if (($mode != 3) && array_key_exists($current->getId(), $cas)) {
                        //implex
                        //echo "ca already there: "; self::debug_echo($current->getId());
                    } else {
                        //echo "ca: "; self::debug_echo($current->getId());
                        //add to cas
                        if (!array_key_exists($current->getId(), $cas)) {
                            $cas[$current->getId()] = array();
                        }
                        //add (effectively no-op if combination already exists)
                        $cas[$current->getId()][$current->xref()] = $current->getDescendant();
                    }

                    if ($mode != 3) {
                        //we can stop here, cas further up are no slcas
                    } else {

                        //add to ancestors
                        if (!array_key_exists($current->getId(), $ancestors2)) {
                            $ancestors2[$current->getId()] = array();
                        }
                        //add (effectively no-op if combination already exists)
                        $ancestors2[$current->getId()][$current->xref()] = $current->getDescendant();

                        //add ancestors to queue
                        if (array_key_exists($current->getId(), $graph)) {
                            foreach ($graph[$current->getId()] as $next) {
                                $queue2[] = $current->next($next);
                            }
                        }
                    }
                } else {
                    //echo "add: "; self::debug_echo($current->getId());
                    //add to ancestors
                    if (!array_key_exists($current->getId(), $ancestors2)) {
                        $ancestors2[$current->getId()] = array();
                    }
                    //add (effectively no-op if combination already exists)
                    $ancestors2[$current->getId()][$current->xref()] = $current->getDescendant();

                    //add ancestors to queue
                    if (array_key_exists($current->getId(), $graph)) {
                        foreach ($graph[$current->getId()] as $next) {
                            $queue2[] = $current->next($next);
                        }
                    }
                }
            }
        }

        //finished processing queues
        /*
          echo "finished <br/>";
          foreach ($cas as $caKey => $caValue) {
          echo "ca: "; self::debug_echo($caKey);
          foreach ($caValue as $caValue2) {
          echo "ca desc: "; echo $caValue2->xref();
          echo "<br/>";
          }
          }
         */

        //filter to lcas or slcas, and get the paths
        $paths = array();
        foreach ($cas as $caKey => $caValue) {
            foreach ($caValue as $caValue2) {
                $caPaths = self::getPaths($caKey, $caValue2->xref(), $mode, $cas, $ancestors1, $ancestors2);
                foreach ($caPaths as $caPath) {
                    $paths[] = new CommonAncestorAndPath($caKey, $caPath);
                }
            }
        }

        //sort by length, then by shortest leg
        usort($paths, array($this, 'compareCommonAncestorAndPath'));

        if ($mode != 1) {
            return $paths;
        }

        if (empty($paths)) {
            return $paths;
        }

        //return one of the shortest paths
        return array(array_shift($paths));
    }

    private static function getPaths($ca, $caDescendant, $mode, $cas, $ancestors1, $ancestors2) {
        $path = array();

        //initialize ascent
        $path[] = $ca;

        return self::getPathsAscending($path, $caDescendant, $mode, $cas, $ancestors1, $ancestors2);
    }

    private static function getPathsAscending($path, $caDescendant, $mode, $cas, $ancestors1, $ancestors2) {
        //ascend (moving backwards from ca)
        //get first element of current path
        $next = reset($path);
        //echo "asc: "; self::debug_echo($next);

        $paths = array();
        foreach ($ancestors1[$next] as $value) {
            $next = $value->xref();

            if ($next) {
                if (self::abortAscent($next, $mode, $cas)) {
                    return array();
                }

                //copy path
                $newPath = $path;

                //add next element (at the front)
                array_unshift($newPath, $next);

                $extPaths = self::getPathsAscending($newPath, $caDescendant, $mode, $cas, $ancestors1, $ancestors2);
                $paths = array_merge($paths, $extPaths);
            } else {
                //we're finished ascending here

                if (!$caDescendant) {
                    //ca is the target = we're done
                    $paths[] = $path;
                } else {
                    if (self::abortDescent($caDescendant, $path, $mode, $cas)) {
                        return array();
                    }

                    //initialize descent
                    $path[] = $caDescendant;

                    $extPaths = self::getPathsDescending($path, $mode, $cas, $ancestors2);
                    $paths = array_merge($paths, $extPaths);
                }
            }
        }

        return $paths;
    }

    private static function abortAscent($next, $mode, $cas) {
        if (($mode != 3) && array_key_exists($next, $cas)) {
            //this lca ascends through another lca = it's no slca!
            //echo "skip lca on ascent: "; self::debug_echo($next);
            //abort
            return true;
        }

        return false;
    }

    private static function abortDescent($next, $path, $mode, $cas) {
        if (($mode != 3) && array_key_exists($next, $cas)) {
            //this lca descends through another lca = it's no slca!
            //echo "skip lca on descent: "; self::debug_echo($next);
            //abort
            return true;
        }

        if (($mode == 3) && in_array($next, $path)) {
            //this ca ascends and descends through the same node = it's no lca!
            //abort
            return true;
        } //else no need to check - cannot occur

        return false;
    }

    private static function getPathsDescending($path, $mode, $cas, $ancestors2) {
        //descend (moving forwards from ca)
        //get last element of current path
        $next = end($path);
        //echo "desc: "; self::debug_echo($next);

        $paths = array();

        foreach ($ancestors2[$next] as $value) {
            $next = $value->xref();

            if ($next) {
                if (self::abortDescent($next, $path, $mode, $cas)) {
                    return array();
                }

                //copy path
                $newPath = $path;

                //add next element
                $newPath[] = $next;

                $extPaths = self::getPathsDescending($newPath, $mode, $cas, $ancestors2);
                $paths = array_merge($paths, $extPaths);
            } else {
                //we're finished
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * Calculate the shortest paths - or all paths - between two individuals.
     * blood relationships preferred!
     * extension: only links established before given julian day
     *
     * @param String $xref1
     * @param String $xref2
     *
     * @return string[][]
     */
    public function x_calculateRelationships_withWeights(
        Tree $tree,
        string $xref1,
        string $xref2,
        $beforeJD = null) {

        $graph = array();

        //make sure the tables exist.
        Sync::initializeSchema();

        if ($beforeJD === null) {
            $rows = DB::table('link')
                ->where('l_file', '=', $tree->id())
                ->whereIn('l_type', ['FAMS', 'FAMC'])
                ->select(['l_from', 'l_to', 'l_type'])
                ->get();

            foreach ($rows as $row) {
                if ($row->l_type === 'FAMS') {
                    //edge between 'descent' nodes
                    $graph["D_" . $row->l_from]["D_" . $row->l_to] = 1;

                    //revert as ascent node (HUSB/WIFE)
                    $graph["A_" . $row->l_to]["A_" . $row->l_from] = 1;
                } else {
                    //edge between 'ascent' nodes
                    $graph["A_" . $row->l_from]["A_" . $row->l_to] = 1;

                    //revert as descent node (CHIL)
                    $graph["D_" . $row->l_to]["D_" . $row->l_from] = 1;
                }

                //edges connecting 'ascent' and 'descent' nodes
                //(maybe added more than once per node)
                $graph["A_" . $row->l_from]["D_" . $row->l_from] = 0; //turn around (related)
                $graph["A_" . $row->l_to]["D_" . $row->l_to] = 0; //turn around (related)
                $graph["D_" . $row->l_from]["A_" . $row->l_from] = 1000; //turn around (non-related)
                $graph["D_" . $row->l_to]["A_" . $row->l_to] = 1000; //turn around (non-related)
            }
        } else {
            $rows = DB::table('link')
                ->join('rel_families', function (JoinClause $join) use ($beforeJD): void {
                    $join
                    ->on('link.l_to', '=', 'rel_families.f_id')
                    ->on('link.l_file', '=', 'rel_families.f_file')
                    ->where('rel_families.f_from', '<', $beforeJD);
                })
                ->where('l_file', '=', $tree->id())
                ->whereIn('l_type', ['FAMS'])
                ->select(['l_from', 'l_to'])
                ->get();

            foreach ($rows as $row) {
                //edge between 'descent' nodes
                $graph["D_" . $row->l_from]["D_" . $row->l_to] = 1;

                //revert as ascent node (HUSB/WIFE)
                $graph["A_" . $row->l_to]["A_" . $row->l_from] = 1;

                //edges connecting 'ascent' and 'descent' nodes
                //(maybe added more than once per node)
                $graph["A_" . $row->l_from]["D_" . $row->l_from] = 0; //turn around (related)
                $graph["A_" . $row->l_to]["D_" . $row->l_to] = 0; //turn around (related)
                $graph["D_" . $row->l_from]["A_" . $row->l_from] = 1000; //turn around (non-related)
                $graph["D_" . $row->l_to]["A_" . $row->l_to] = 1000; //turn around (non-related)
            }

            $rows = DB::table('link')
                ->join('rel_individuals', function (JoinClause $join) use ($beforeJD): void {
                    $join
                    ->on('link.l_from', '=', 'rel_individuals.i_id')
                    ->on('link.l_file', '=', 'rel_individuals.i_file')
                    ->where('rel_individuals.i_from', '<', $beforeJD);
                })
                ->where('l_file', '=', $tree->id())
                ->whereIn('l_type', ['FAMC'])
                ->select(['l_from', 'l_to'])
                ->get();

            foreach ($rows as $row) {
                //edge between 'ascent' nodes
                $graph["A_" . $row->l_from]["A_" . $row->l_to] = 1;

                //revert as descent node (CHIL)
                $graph["D_" . $row->l_to]["D_" . $row->l_from] = 1;

                //edges connecting 'ascent' and 'descent' nodes
                //(maybe added more than once per node)
                $graph["A_" . $row->l_from]["D_" . $row->l_from] = 0; //turn around (related)
                $graph["A_" . $row->l_to]["D_" . $row->l_to] = 0; //turn around (related)
                $graph["D_" . $row->l_from]["A_" . $row->l_from] = 1000; //turn around (non-related)
                $graph["D_" . $row->l_to]["A_" . $row->l_to] = 1000; //turn around (non-related)
            }
        }

        $dijkstra = new OptimizedDijkstra($graph);

        $paths = $dijkstra->shortestPaths("A_" . $xref1, "D_" . $xref2);

        //1. use additional tiebreakers for multiple paths with same length (and same weight),
        //
        //in order to obtain consistent ordering;
        //2. seems reasonable to use symmetric ordering (order should be the same when swapping source and target individual),
        //otherwise potentially confusing;
        //
        //due to 2., we cannot use "shortest name" reliably.
        //
        //instead, we prefer 'longer runs' via common ancestors
        //(i.e. second cousin's wife is better than first cousin's wife's uncle)
        if (count($paths) > 1) {
            $weightedPaths = [];
            foreach ($paths as $path) {
                $f = SomeTiebreaker::create();
                $tiebreakerWeight = $dijkstra->calculateTiebreaker($path, $f);
                //error_log(print_r($path, true));
                //error_log("weight:".$tiebreakerWeight);
                $weightedPaths []= array('path' => $path, 'weight' => $tiebreakerWeight);
            }

            usort($weightedPaths, static fn (array $x, array $y): int => ($x['weight'] <=> $y['weight']));

            $paths = array_map(static fn (array $x): array => $x['path'], $weightedPaths);
        }

        $paths = ExtendedRelationshipController::adjustPaths($paths);

        return $paths;
    }

    public function calculateRelationships_optimized(
        Individual $individual1,
        Individual $individual2,
        int $recursion,
        $beforeJD = null) {

        return $this->x_calculateRelationships_optimized($individual1->tree(), $individual1->xref(), $individual2->xref(), $recursion, $beforeJD);
    }

    //adjustment from original: OptimizedDijkstra
    //extension: only links established before given julian day
    public function x_calculateRelationships_optimized(
        $tree,
        $xref1,
        $xref2,
        int $recursion,
        $beforeJD = null) {

        $graph = array();

        if ($beforeJD === null) {
            $rows = DB::table('link')
                ->where('l_file', '=', $tree->id())
                ->whereIn('l_type', ['FAMS', 'FAMC'])
                ->select(['l_from', 'l_to'])
                ->get();

            foreach ($rows as $row) {
                $graph[$row->l_from][$row->l_to] = 1;
                $graph[$row->l_to][$row->l_from] = 1;
            }
        } else {
            //make sure the tables exist.
            Sync::initializeSchema();

            $rows = DB::table('link')
                ->join('rel_families', function (JoinClause $join) use ($beforeJD): void {
                    $join
                    ->on('link.l_to', '=', 'rel_families.f_id')
                    ->on('link.l_file', '=', 'rel_families.f_file')
                    ->where('rel_families.f_from', '<', $beforeJD);
                })
                ->where('l_file', '=', $tree->id())
                ->whereIn('l_type', ['FAMS'])
                ->select(['l_from', 'l_to'])
                ->get();

            foreach ($rows as $row) {
                $graph[$row->l_from][$row->l_to] = 1;
                $graph[$row->l_to][$row->l_from] = 1;
            }

            $rows = DB::table('link')
                ->join('rel_individuals', function (JoinClause $join) use ($beforeJD): void {
                    $join
                    ->on('link.l_from', '=', 'rel_individuals.i_id')
                    ->on('link.l_file', '=', 'rel_individuals.i_file')
                    ->where('rel_individuals.i_from', '<', $beforeJD);
                })
                ->where('l_file', '=', $tree->id())
                ->whereIn('l_type', ['FAMC'])
                ->select(['l_from', 'l_to'])
                ->get();

            foreach ($rows as $row) {
                $graph[$row->l_from][$row->l_to] = 1;
                $graph[$row->l_to][$row->l_from] = 1;
            }
        }

        $dijkstra = new OptimizedDijkstra($graph);
        $paths = $dijkstra->shortestPaths($xref1, $xref2);

        //[RC] logic is dubious: completely excluding a node means
        //we won't get any other paths through that node? a-b-c excludes a-x-b-c?

        // Only process each exclusion list once;
        $excluded = array();

        $queue = array();
        foreach ($paths as $path) {
            // Insert the paths into the queue, with an exclusion list.
            $queue[] = array('path' => $path, 'exclude' => array());
            // While there are un-extended paths
            //foreach ($queue as $next) { //really equivalent?
            for ($next = current($queue); $next !== false; $next = next($queue)) {
                // For each family on the path
                for ($n = count($next['path']) - 2; $n >= 1; $n -= 2) {
                    $exclude = $next['exclude'];
                    if (count($exclude) >= $recursion) {
                        continue;
                    }
                    $exclude[] = $next['path'][$n];
                    sort($exclude);
                    $tmp = implode('-', $exclude);
                    if (in_array($tmp, $excluded)) {
                        continue;
                    } else {
                        $excluded[] = $tmp;
                    }
                    // Add any new path to the queue
                    foreach ($dijkstra->shortestPaths($xref1, $xref2, $exclude) as $new_path) {
                        $queue[] = array('path' => $new_path, 'exclude' => $exclude);
                    }
                }
            }
        }
        // Extract the paths from the queue, removing duplicates.
        $paths = array();
        foreach ($queue as $next) {
            // The Dijkstra library does not use strict types, and converts
            // numeric array keys (XREFs) from strings to integers;
            $path = array_map(ExtendedRelationshipController::stringMapper(), $next['path']);

            $paths[implode('-', $next['path'])] = $path;
        }

        return $paths;
    }

    /**
     * Convert numeric values to strings
     *
     * @return Closure
     */
    public static function stringMapper(): Closure {
        return static function ($xref) {
            return (string) $xref;
        };
    }

    public static function adjustPaths($paths) {
        //clean up paths
        $finalPaths = array();
        foreach ($paths as $path) {
            $finalPath = array();
            $previous = null;
            foreach ($path as $pathElement) {
                $pathElement = substr($pathElement, 2);
                if ($previous !== $pathElement) {
                    $finalPath[] = $pathElement;
                    $previous = $pathElement;
                }
            }
            $finalPaths[] = $finalPath;
        }

        return $finalPaths;
    }

    //////////////////////////////////////////////////////////////////////////////
}

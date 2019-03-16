<?php

declare(strict_types=1);

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Exception;
use Fisharebest\Webtrees\Http\Controllers\AbstractBaseController;
use Fisharebest\Webtrees\Services\TimeoutService;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Schema\Blueprint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function route;

//adapted from GedcomFileController
class Sync extends AbstractBaseController {

  /** @var string */
  protected $layout = 'layouts/ajax';

  protected $moduleName;

  public function __construct(string $moduleName) {
    $this->moduleName = $moduleName;
  }


  public function sync(Request $request, TimeoutService $timeout_service): Response {

    $phase = intval($request->get('phase'));

    switch ($phase) {
      case 1:
        Sync::initializeSchema();
        Sync::cleanupIndividuals();
        Sync::cleanupFamilies();
        $size = Sync::countIndividualsToBeSynced() + Sync::countFamiliesToBeSynced();
        $position = 1;
        $phase = 2;
        break;
      case 2:
        $size = intval($request->get('size'));
        $position = intval($request->get('position'));

        // Run for a few seconds. This keeps the resource requirements low.
        do {
          if (!Sync::calculateNextIndividual()) {
            $phase = 3;
            break;
          }
          $position += 1;
        } while (!$timeout_service->isTimeLimitUp());
        break;
      case 3:
        $size = intval($request->get('size'));
        $position = intval($request->get('position'));

        // Run for a few seconds. This keeps the resource requirements low.
        do {
          if (!Sync::calculateNextFamily()) {
            return $this->viewResponse($this->moduleName . '::sync-complete', []);
          }
          $position += 1;
        } while (!$timeout_service->isTimeLimitUp());
        break;
      default:
        throw new Exception("unexpected phase.");
    }

    // Calculate progress so far
    $progress = $position / ($size + 1);

    $url = route('module', [
        'module' => $this->moduleName,
        'action' => 'AdminSync',
        'phase' => $phase,
        'size' => $size,
        'position' => $position
    ]);

    return $this->viewResponse($this->moduleName . '::sync-progress', [
                'progress' => $progress,
                'post' => $url
    ]);
  }

  //////////////////////////////////////////////////////////////////////////////

  public static function initializeSchema() {

    //individuals
    //i_gedcom is for change detection
    if (!DB::schema()->hasTable('rel_individuals')) {
      DB::schema()->create('rel_individuals', function (Blueprint $table): void {
        $table->string('i_id', 20);
        $table->integer('i_file');
        $table->longText('i_gedcom');
        $table->mediumInteger('i_from')->nullable();
        $table->primary(['i_id', 'i_file']);
        $table->unique(['i_file', 'i_id']);
      });
    }

    if (!DB::schema()->hasTable('rel_families')) {
      DB::schema()->create('rel_families', function (Blueprint $table): void {
        $table->string('f_id', 20);
        $table->integer('f_file');
        $table->longText('f_gedcom');
        $table->mediumInteger('f_from')->nullable();
        $table->primary(['f_id', 'f_file']);
        $table->unique(['f_file', 'f_id']);
      });
    }

    //families
    //f_gedcom is for change detection
  }

  public static function cleanupIndividuals() {
    //delete individuals that don't exist anymore		
    DB::table('rel_individuals')
            ->leftJoin('individuals', function (JoinClause $join): void {
              $join
              ->on('individuals.i_id', '=', 'rel_individuals.i_id')
              ->on('individuals.i_file', '=', 'rel_individuals.i_file');
            })
            ->whereNull('individuals.i_id')
            ->delete();

    //delete changed entries
    DB::table('rel_individuals')
            ->join('individuals', function (JoinClause $join): void {
              $join
              ->on('individuals.i_id', '=', 'rel_individuals.i_id')
              ->on('individuals.i_file', '=', 'rel_individuals.i_file');
            })
            ->whereColumn('individuals.i_gedcom', '!=', 'rel_individuals.i_gedcom')
            ->delete();
  }

  public static function cleanupFamilies() {
    //delete families that don't exist anymore
    DB::table('rel_families')
            ->leftJoin('families', function (JoinClause $join): void {
              $join
              ->on('families.f_id', '=', 'rel_families.f_id')
              ->on('families.f_file', '=', 'rel_families.f_file');
            })
            ->whereNull('families.f_id')
            ->delete();

    //delete changed entries
    DB::table('rel_families')
            ->join('families', function (JoinClause $join): void {
              $join
              ->on('families.f_id', '=', 'rel_families.f_id')
              ->on('families.f_file', '=', 'rel_families.f_file');
            })
            ->whereColumn('families.f_gedcom', '!=', 'rel_families.f_gedcom')
            ->delete();

    //what about indis with changed date: should delete families via links because date MAY change
  }

  public static function countIndividualsToBeSynced() {
    return DB::table('individuals')
                    ->leftJoin('rel_individuals', function (JoinClause $join): void {
                      $join
                      ->on('individuals.i_id', '=', 'rel_individuals.i_id')
                      ->on('individuals.i_file', '=', 'rel_individuals.i_file');
                    })
                    ->whereNull('rel_individuals.i_id')
                    ->count();
  }

  public static function countFamiliesToBeSynced() {
    return DB::table('families')
                    ->leftJoin('rel_families', function (JoinClause $join): void {
                      $join
                      ->on('families.f_id', '=', 'rel_families.f_id')
                      ->on('families.f_file', '=', 'rel_families.f_file');
                    })
                    ->whereNull('rel_families.f_id')
                    ->count();
  }

  public static function calculateNextIndividual() {
    $row = DB::table('individuals')
            ->leftJoin('rel_individuals', function (JoinClause $join): void {
              $join
              ->on('individuals.i_id', '=', 'rel_individuals.i_id')
              ->on('individuals.i_file', '=', 'rel_individuals.i_file');
            })
            ->whereNull('rel_individuals.i_id')
            ->select(['individuals.i_id', 'individuals.i_file', 'individuals.i_gedcom'])
            ->first();

    if ($row == null) {
      return null;
    }

    $id = $row->i_id;
    $file = $row->i_file;
    $gedcom = $row->i_gedcom;

    //'i_from' = 'born no later than' (= minimum of date of birth, any valid fact/event date).
    //note: we don't use family data to estimate this date, 
    //because that would complicate the decision when to recalculate 

    $indi = new DirectIndividual($id, $gedcom, "" . $file);
    $date = $indi->getBornNoLaterThan();
    $maxJD = null;
    if ($date->isOK()) {
      $maxJD = $date->minimumJulianDay();
    }

    DB::table('rel_individuals')
            ->insert([
                'i_id' => $id,
                'i_file' => $file,
                'i_gedcom' => $gedcom,
                'i_from' => $maxJD,
    ]);

    return 'ok';
  }

  public static function calculateNextFamily() {
    $row = DB::table('families')
            ->leftJoin('rel_families', function (JoinClause $join): void {
              $join
              ->on('families.f_id', '=', 'rel_families.f_id')
              ->on('families.f_file', '=', 'rel_families.f_file');
            })
            ->whereNull('rel_families.f_id')
            ->select(['families.f_id', 'families.f_file', 'families.f_gedcom'])
            ->first();

    if ($row == null) {
      return null;
    }

    $id = $row->f_id;
    $file = $row->f_file;
    $gedcom = $row->f_gedcom;

    //'f_from' = 'family established no later than' (= minimum of date of marriage, first childbirth).

    $fam = new DirectFamily($id, $gedcom, "" . $file);
    $date = $fam->getFamilyEstablishedNoLaterThan();
    $maxJD = null;
    if ($date->isOK()) {
      $maxJD = $date->minimumJulianDay();
    }

    DB::table('rel_families')
            ->insert([
                'f_id' => $id,
                'f_file' => $file,
                'f_gedcom' => $gedcom,
                'f_from' => $maxJD,
    ]);

    return 'ok';
  }

}

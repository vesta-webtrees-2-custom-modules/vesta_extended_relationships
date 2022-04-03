<?php

declare(strict_types=1);

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\WebtreesExt\Requests;
use Exception;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Services\TimeoutService;
use Fisharebest\Webtrees\Services\TreeService;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Schema\Blueprint;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function app;
use function route;

//adapted from GedcomFileController
class Sync {

  use ViewResponseTrait;

  protected $moduleName;

  public function __construct(string $moduleName) {
    $this->moduleName = $moduleName;
    $this->layout = 'layouts/ajax';
  }


  public function sync(ServerRequestInterface $request, TimeoutService $timeout_service): ResponseInterface {
    $phase = Requests::getInt($request, 'phase');

    switch ($phase) {
      case 1:
        Sync::initializeSchema();
        Sync::cleanupIndividuals();
        Sync::cleanupFamilies();
        $size = Sync::countIndividualsToBeSynced() + Sync::countFamiliesToBeSynced();
        //error_log("INDIs: " . Sync::countIndividualsToBeSynced());
        //error_log("FAMs: " . Sync::countFamiliesToBeSynced());
        $position = 1;
        $phase = 2;
        break;
      case 2:
        $size = Requests::getInt($request, 'size');
        $position = Requests::getInt($request, 'position');

        // Run for a few seconds. This keeps the resource requirements low.
        do {
          $batchSize = 50;
          if (!Sync::calculateNextIndividuals($batchSize)) {
            $phase = 3;
            break;
          }
          $position += $batchSize;
        } while (!$timeout_service->isTimeLimitUp());
        break;
      case 3:
        $size = Requests::getInt($request, 'size');
        $position = Requests::getInt($request, 'position');

        // Run for a few seconds. This keeps the resource requirements low.
        do {
          $batchSize = 50;
          if (!Sync::calculateNextFamilies($batchSize)) {
            return $this->viewResponse($this->moduleName . '::sync-complete', []);
          }
          $position += $batchSize;
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

  public static function calculateNextIndividuals(int $batchSize): bool {
    //this query is rather slow (0.3 seconds locally), compared to everything else in this method
    //therefore we batch here!
    $rows = DB::table('individuals')
            ->leftJoin('rel_individuals', function (JoinClause $join): void {
              $join
              ->on('individuals.i_id', '=', 'rel_individuals.i_id')
              ->on('individuals.i_file', '=', 'rel_individuals.i_file');
            })
            ->whereNull('rel_individuals.i_id')
            ->limit($batchSize)
            ->select(['individuals.i_id', 'individuals.i_file', 'individuals.i_gedcom'])
            ->get();

    if ($rows->isEmpty()) {
      return false;
    }

    foreach ($rows as $row) {
      $id = $row->i_id;
      $file = intval($row->i_file);
      $gedcom = $row->i_gedcom;

      //'i_from' = 'born no later than' (= minimum of date of birth, any valid fact/event date).
      //note: we don't use family data to estimate this date, 
      //because that would complicate the decision when to recalculate 

      //$indi = new DirectIndividual($id, $gedcom, "" . $file);
      $tree = app(TreeService::class)->find($file);
      $indi = new Individual($id, $gedcom, null, $tree);
      $date = ExtendedRelationshipUtils::getBornNoLaterThan($indi);

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
    }

    return true;
  }

  public static function calculateNextFamilies(int $batchSize): bool {
    $rows = DB::table('families')
            ->leftJoin('rel_families', function (JoinClause $join): void {
              $join
              ->on('families.f_id', '=', 'rel_families.f_id')
              ->on('families.f_file', '=', 'rel_families.f_file');
            })
            ->whereNull('rel_families.f_id')
            ->limit($batchSize)
            ->select(['families.f_id', 'families.f_file', 'families.f_gedcom'])
            ->get();

    if ($rows->isEmpty()) {
      return false;
    }

    foreach ($rows as $row) {
      
      $id = $row->f_id;
      $file = intval($row->f_file);
      $gedcom = $row->f_gedcom;

      //'f_from' = 'family established no later than' (= minimum of date of marriage, first childbirth).

      //$fam = new DirectFamily($id, $gedcom, "" . $file);
      $tree = app(TreeService::class)->find($file);
      $fam = new Family($id, $gedcom, null, $tree);
      $date = ExtendedRelationshipUtils::getFamilyEstablishedNoLaterThan($fam);
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
    }

    return true;
  }

}

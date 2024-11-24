<?php

declare(strict_types=1);

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\WebtreesExt\MoreI18N;
use Cissee\WebtreesExt\Services\ExtendedChartService;
use Cissee\WebtreesExt\Services\PedigreeTreeType;
use Fig\Http\Message\RequestMethodInterface;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleChartInterface;
use Fisharebest\Webtrees\Module\ModuleChartTrait;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function redirect;
use function route;
use function view;

class ExtendedPedigreeChartModule extends AbstractModule implements ModuleChartInterface, RequestHandlerInterface {
    use ModuleChartTrait;

    protected const ROUTE_URL = '/tree/{tree}/vesta-pedigree-{kind}-{style}-{generations}/{xref}';

    // Chart styles
    public const STYLE_LEFT  = 'left';
    public const STYLE_RIGHT = 'right';
    public const STYLE_UP    = 'up';
    public const STYLE_DOWN  = 'down';

    public const KIND_FULL      = 'full';
    public const KIND_COMPACT   = 'compact';
    public const KIND_COLLAPSE  = 'collapse';

    /** It would be more correct to use PHP_INT_MAX, but this isn't friendly in URLs */
    public const UNLIMITED_GENERATIONS = 99;

    // Defaults
    public const DEFAULT_GENERATIONS = 8/*self::UNLIMITED_GENERATIONS*/;
    public const DEFAULT_GENERATIONS_COLLAPSE = self::UNLIMITED_GENERATIONS;
    public const DEFAULT_KIND = self::KIND_COLLAPSE;
    public const DEFAULT_STYLE = self::STYLE_RIGHT;

    protected const DEFAULT_PARAMETERS = [
        'generations' => self::DEFAULT_GENERATIONS,
        'style'       => self::DEFAULT_STYLE,
    ];

    protected const DEFAULT_PARAMETERS_COLLAPSE = [
        'generations' => self::DEFAULT_GENERATIONS_COLLAPSE,
        'style'       => self::DEFAULT_STYLE,
    ];

    // Limits
    protected const MINIMUM_GENERATIONS = 5;
    protected const MAXIMUM_GENERATIONS = 15;

    // For RTL languages
    protected const MIRROR_STYLE = [
        self::STYLE_UP    => self::STYLE_DOWN,
        self::STYLE_DOWN  => self::STYLE_UP,
        self::STYLE_LEFT  => self::STYLE_RIGHT,
        self::STYLE_RIGHT => self::STYLE_LEFT,
    ];

    protected $main_module;
    protected $kind;

    public function __construct(
        ExtendedRelationshipModule $main_module,
        string $kind) {

        $this->main_module = $main_module;
        $this->kind = $kind;
    }

    public function boot(): void {
        Registry::routeFactory()->routeMap()
            ->get(static::class, static::ROUTE_URL, $this)
            ->allows(RequestMethodInterface::METHOD_POST);

        View::registerCustomView('::modules/vesta-pedigree-chart/page', $this->main_module->name() . '::modules/pedigree-chart/page');
        View::registerCustomView('::modules/vesta-pedigree-chart/chart', $this->main_module->name() . '::modules/pedigree-chart/chart');
        View::registerCustomView('::modules/vesta-pedigree-chart/chart-x', $this->main_module->name() . '::modules/pedigree-chart/chart-x');
    }

    //TODO streamline
    protected function getVestaSymbol() {
        return json_decode('"\u26B6"');
    }

    public function title(): string {
        /*
        if (self::KIND_COMPACT == $this->kind) {
            return $this->getVestaSymbol() . ' ' . I18N::translate('Compact pedigree');
        }
        */
        if (self::KIND_COLLAPSE == $this->kind) {
            return $this->getVestaSymbol() . ' ' . I18N::translate('Pedigree collapse');
        }
        return $this->getVestaSymbol() . ' ' . MoreI18N::xlate('Pedigree');
    }

    public function description(): string {
        /*
        if (self::KIND_COMPACT == $this->kind) {
            return I18N::translate('A compact chart of an individual’s ancestors, formatted as a tree.');
        }
        */
        if (self::KIND_COLLAPSE == $this->kind) {
            return I18N::translate('A chart of an individual’s repeated ancestors, formatted as a tree.');
        }
        return MoreI18N::xlate('A chart of an individual’s ancestors, formatted as a tree.');
    }

    public function chartMenuClass(): string {
        return 'menu-chart-pedigree';
    }

    public function chartBoxMenu(Individual $individual): ?Menu {
        return $this->chartMenu($individual);
    }

    public function chartTitle(Individual $individual): string {
        /*
        if (self::KIND_COMPACT == $this->kind) {
            return I18N::translate('Compact pedigree tree of %s', $individual->fullName());
        }
        */
        if (self::KIND_COLLAPSE == $this->kind) {
            return I18N::translate('Pedigree collapse tree of %s', $individual->fullName());
        }
        return MoreI18N::xlate('Pedigree tree of %s', $individual->fullName());
    }

    public function chartUrl(Individual $individual, array $parameters = []): string {
        $defaults = static::DEFAULT_PARAMETERS;
        if (self::KIND_COLLAPSE == $this->kind) {
            $defaults = static::DEFAULT_PARAMETERS_COLLAPSE;
        }

        return route(static::class, [
                'xref' => $individual->xref(),
                'tree' => $individual->tree()->name(),
                'kind' => $this->kind,
            ] + $parameters + $defaults);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $tree        = Validator::attributes($request)->tree();
        $user        = Validator::attributes($request)->user();
        $xref        = Validator::attributes($request)->isXref()->string('xref');
        $kind        = Validator::attributes($request)->isInArrayKeys($this->kinds())->string('kind');
        $style       = Validator::attributes($request)->isInArrayKeys($this->styles('ltr'))->string('style');
        $generations = Validator::attributes($request)->isBetween(self::MINIMUM_GENERATIONS, self::UNLIMITED_GENERATIONS)->integer('generations');
        $ajax        = Validator::queryParams($request)->boolean('ajax', false);

        // Convert POST requests into GET requests for pretty URLs.
        if ($request->getMethod() === RequestMethodInterface::METHOD_POST) {
            return redirect(route(self::class, [
                'tree'        => $tree->name(),
                'xref'        => Validator::parsedBody($request)->isXref()->string('xref'),
                'kind'        => Validator::parsedBody($request)->isInArrayKeys($this->kinds())->string('kind'),
                'style'       => Validator::parsedBody($request)->isInArrayKeys($this->styles('ltr'))->string('style'),
                'generations' => Validator::parsedBody($request)->isBetween(self::MINIMUM_GENERATIONS, self::UNLIMITED_GENERATIONS)->integer('generations'),
            ]));
        }

        Auth::checkComponentAccess($this, ModuleChartInterface::class, $tree, $user);

        $individual = Registry::individualFactory()->make($xref, $tree);
        $individual = Auth::checkIndividualAccess($individual, false, true);

        if ($ajax) {
            $this->layout = 'layouts/ajax';

            $type = PedigreeTreeType::full();

            if ($kind === 'compact') {
                $type = PedigreeTreeType::skipRepeated();
            } else if ($kind === 'collapse') {
                //only keep paths to implex ancestors
                $type = PedigreeTreeType::skipRepeatedAndNonCollapsed();
            }

            $self = \Vesta\VestaUtils::get(ExtendedChartService::class)->pedigreeTree(
                $individual,
                ($generations === self::UNLIMITED_GENERATIONS)?null:$generations,
                $type);

            $ret = $this->viewResponse('modules/vesta-pedigree-chart/chart', [
                'start'       => $self,
                'generations' => ($generations === self::UNLIMITED_GENERATIONS)?null:$generations,
                'kind'        => $kind,
                'style'       => $style,
            ]);

            return $ret;
        }

        $actual = new ExtendedPedigreeChartModule($this->main_module, $kind);

        $ajax_url = $actual->chartUrl($individual, [
            'ajax'        => true,
            'generations' => $generations,
            'style'       => $style,
            'xref'        => $xref,
        ]);

        return $this->viewResponse('modules/vesta-pedigree-chart/page', [
            'ajax_url'           => $ajax_url,
            'generations'        => $generations,
            'individual'         => $individual,
            'module'             => $this->name(),
            'options'            => $this->options(),
            'kind'               => $kind,
            'style'              => $style,
            'styles'             => $this->styles(I18N::direction()),
            'title'              => $actual->chartTitle($individual),
            'tree'               => $tree,
        ]);
    }

    protected function kinds(): array {
        return [
                self::KIND_FULL => self::KIND_FULL,
                self::KIND_COMPACT => self::KIND_COMPACT,
                self::KIND_COLLAPSE => self::KIND_COLLAPSE,
            ];
    }

    /**
     * This chart can display its output in a number of styles
     *
     * @param string $direction
     *
     * @return array<string>
     */
    protected function styles(string $direction): array {
        // On right-to-left pages, the CSS will mirror the chart, so we need to mirror the label.
        if ($direction === 'rtl') {
            return [
                self::STYLE_RIGHT => view('icons/pedigree-left') . MoreI18N::xlate('left'),
                self::STYLE_LEFT  => view('icons/pedigree-right') . MoreI18N::xlate('right'),
                self::STYLE_UP    => view('icons/pedigree-up') . MoreI18N::xlate('up'),
                self::STYLE_DOWN  => view('icons/pedigree-down') . MoreI18N::xlate('down'),
            ];
        }

        return [
            self::STYLE_LEFT  => view('icons/pedigree-left') . MoreI18N::xlate('left'),
            self::STYLE_RIGHT => view('icons/pedigree-right') . MoreI18N::xlate('right'),
            self::STYLE_UP    => view('icons/pedigree-up') . MoreI18N::xlate('up'),
            self::STYLE_DOWN  => view('icons/pedigree-down') . MoreI18N::xlate('down'),
        ];
    }

    protected function options(): array {
        $ret = [];
        foreach (range(self::MINIMUM_GENERATIONS, self::MAXIMUM_GENERATIONS) as $n) {
            $ret[$n] = I18N::number($n);
        }
        $ret[self::UNLIMITED_GENERATIONS] = MoreI18N::xlate('unlimited');

        return $ret;
    }
}

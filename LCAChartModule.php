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
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function app;
use function redirect;
use function route;
use function view;

class LCAChartModule extends AbstractModule implements ModuleChartInterface, RequestHandlerInterface {
    use ModuleChartTrait;

    protected const ROUTE_URL = '/tree/{tree}/vesta-lca-{style}/{xref}{/xref2}';

    // Chart styles
    public const STYLE_LEFT  = 'left';
    public const STYLE_RIGHT = 'right';
    public const STYLE_UP    = 'up';
    public const STYLE_DOWN  = 'down';

    // Defaults
    public const DEFAULT_STYLE = self::STYLE_RIGHT;
    
    protected const DEFAULT_PARAMETERS  = [
        'style' => self::DEFAULT_STYLE,
    ];
    
    // For RTL languages
    protected const MIRROR_STYLE = [
        self::STYLE_UP    => self::STYLE_DOWN,
        self::STYLE_DOWN  => self::STYLE_UP,
        self::STYLE_LEFT  => self::STYLE_RIGHT,
        self::STYLE_RIGHT => self::STYLE_LEFT,
    ];
    
    protected $main_module;
    
    public function __construct(
        ExtendedRelationshipModule $main_module) {
        
        $this->main_module = $main_module;
    }
    
    public function boot(): void {
        Registry::routeFactory()->routeMap()
            ->get(static::class, static::ROUTE_URL, $this)
            ->allows(RequestMethodInterface::METHOD_POST);
        
        View::registerCustomView('::modules/vesta-lca-chart/page', $this->main_module->name() . '::modules/lca-chart/page');
        View::registerCustomView('::modules/vesta-lca-chart/chart', $this->main_module->name() . '::modules/lca-chart/chart');
        View::registerCustomView('::modules/vesta-lca-chart/chart-x', $this->main_module->name() . '::modules/lca-chart/chart-x');
    }

    //TODO streamline
    protected function getVestaSymbol() {
        return json_decode('"\u26B6"');
    }
    
    public function title(): string {
        return $this->getVestaSymbol() . ' ' . I18N::translate('Common ancestors');
    }

    public function description(): string {
        return MoreI18N::xlate('A chart displaying common ancestors of two individuals.');
    }

    public function chartMenuClass(): string {
        return 'menu-chart-relationship';
    }
    
    public function chartBoxMenu(Individual $individual): ?Menu {
        return $this->chartMenu($individual);
    }

    public function chartUrl(Individual $individual, array $parameters = []): string {
        return route(static::class, [
                'xref' => $individual->xref(),
                'tree' => $individual->tree()->name(),
            ] + $parameters + static::DEFAULT_PARAMETERS);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $tree        = Validator::attributes($request)->tree();
        $user        = Validator::attributes($request)->user();
        $xref        = Validator::attributes($request)->isXref()->string('xref');
        $xref2       = Validator::attributes($request)->isXref()->string('xref2', '');
        $style       = Validator::attributes($request)->isInArrayKeys($this->styles('ltr'))->string('style');
        $ajax        = Validator::queryParams($request)->boolean('ajax', false);
        
        // Convert POST requests into GET requests for pretty URLs.
        if ($request->getMethod() === RequestMethodInterface::METHOD_POST) {
            return redirect(route(self::class, [
                'tree'        => $tree->name(),
                'xref'        => Validator::parsedBody($request)->isXref()->string('xref'),
                'xref2'       => Validator::parsedBody($request)->string('xref2', ''),
                'style'       => Validator::parsedBody($request)->isInArrayKeys($this->styles('ltr'))->string('style'),
            ]));
        }

        Auth::checkComponentAccess($this, ModuleChartInterface::class, $tree, $user);

        $individual1 = Registry::individualFactory()->make($xref, $tree);
        $individual2 = Registry::individualFactory()->make($xref2, $tree);
        
        if ($individual1 instanceof Individual) {
            $individual1 = Auth::checkIndividualAccess($individual1, false, true);
        }

        if ($individual2 instanceof Individual) {
            $individual2 = Auth::checkIndividualAccess($individual2, false, true);
        }

        if ($individual1 instanceof Individual && $individual2 instanceof Individual) {
            if ($ajax) {
                $this->layout = 'layouts/ajax';
            
                $nodes = app(ExtendedChartService::class)->pedigreeTrees(
                    new Collection([$individual1, $individual2]), 
                    null,
                    PedigreeTreeType::commonAncestors());

                $ret = $this->viewResponse('modules/vesta-lca-chart/chart', [
                    'nodes'          => $nodes,   
                    'style'          => $style,
                    'mainModuleName' => $this->main_module->name(),
                ]);

                return $ret;
            }

            /* I18N: %s are individual’s names */
            $title = I18N::translate('Common ancestors of %1$s and %2$s', $individual1->fullName(), $individual2->fullName());
            $ajax_url = $this->chartUrl($individual1, [
                'ajax'      => true,
                'style'     => $style,
                'xref'      => $xref,
                'xref2'     => $individual2->xref(),
            ]);
        } else {
            $title = I18N::translate('Common ancestors');
            $ajax_url = '';
        }
        
        return $this->viewResponse('modules/vesta-lca-chart/page', [
            'ajax_url'           => $ajax_url,
            'individual1'        => $individual1,
            'individual2'        => $individual2,
            'module'             => $this->name(),
            'style'              => $style,
            'styles'             => $this->styles(I18N::direction()),
            'title'              => $title,
            'tree'               => $tree,
        ]);
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
}

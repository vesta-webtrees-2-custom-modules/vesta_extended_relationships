<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Http\RequestHandlers\MessagePage;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\ModuleChartInterface;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Module\RelationshipsChartModule;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\MessageService;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\UserService;
use Fisharebest\Webtrees\Tree;
use function app;
use function route;
use function view;

class UserRepositoryExt {
    
    private Tree $tree;
    private UserService $user_service;

    public function __construct(
        Tree $tree, 
        UserService $user_service) {
        
        $this->tree         = $tree;
        $this->user_service = $user_service;
    }
    
    //adapted from UserRepository
    public function usersLoggedInQuery(
        string $type,
        string $moduleName): string {
        
        $content   = '';
        $anonymous = 0;
        $logged_in = [];

        foreach ($this->user_service->allLoggedIn() as $user) {
            if (Auth::isAdmin() || $user->getPreference(UserInterface::PREF_IS_VISIBLE_ONLINE) === '1') {
                $logged_in[] = $user;
            } else {
                $anonymous++;
            }
        }

        $count_logged_in = count($logged_in);

        if ($count_logged_in === 0 && $anonymous === 0) {
            $content .= MoreI18N::xlate('No signed-in and no anonymous users');
        }

        if ($anonymous > 0) {
            $content .= '<b>' . I18N::plural('%s anonymous signed-in user', '%s anonymous signed-in users', $anonymous, I18N::number($anonymous)) . '</b>';
        }

        if ($count_logged_in > 0) {
            if ($anonymous !== 0) {
                if ($type === 'list') {
                    $content .= '<br><br>';
                } else {
                    $content .= ' ' . MoreI18N::xlate('and') . ' ';
                }
            }
            $content .= '<b>' . I18N::plural('%s signed-in user', '%s signed-in users', $count_logged_in, I18N::number($count_logged_in)) . '</b>';
            if ($type === 'list') {
                $content .= '<ul>';
            } else {
                $content .= ': ';
            }
        }

        if (Auth::check()) {
            
            $userSelf = Auth::user();
            $individualSelf = Registry::individualFactory()->make($this->tree->getUserPreference($userSelf, UserInterface::PREF_TREE_ACCOUNT_XREF), $this->tree);
            
            $relationshipsChartModule = app(ModuleService::class)
                ->findByComponent(ModuleChartInterface::class, $this->tree, Auth::user())
                ->first(static function (ModuleInterface $module) {
                return $module instanceof RelationshipsChartModule;
            });
            
            foreach ($logged_in as $user) {
                if ($type === 'list') {
                    $content .= '<li>';
                }

                $individual = Registry::individualFactory()->make($this->tree->getUserPreference($user, UserInterface::PREF_TREE_ACCOUNT_XREF), $this->tree);

                if ($individual instanceof Individual && $individual->canShow()) {
                    $content .= '<a href="' . e($individual->url()) . '">' . e($user->realName()) . '</a>';
                } else {
                    $content .= e($user->realName());
                }

                //[RC] adjusted: use proper typography while we're at it,
                //also add css class for optional styling
                $content .= '<span class="vesta-relationship-chart-link">&mdash; ' . e($user->userName()) . '</span>';

                if ($user->getPreference(UserInterface::PREF_CONTACT_METHOD) !== MessageService::CONTACT_METHOD_NONE && Auth::id() !== $user->id()) {
                    $content .= '<a href="' . e(route(MessagePage::class, ['to' => $user->userName(), 'tree' => $this->tree->name()])) . '" class="btn btn-link" title="' . MoreI18N::xlate('Send a message') . '">' . view('icons/email') . '</a>';
                }

                //[RC] adjusted
                if ($individual instanceof Individual && $individual->canShow() && $individualSelf instanceof Individual && $individualSelf->canShow() && ($individualSelf !== $individual) && $relationshipsChartModule instanceof RelationshipsChartModule) {
                    //no need to restrict this to vesta chart
                    /*
                    $link = ExtendedRelationshipModule::getRelationshipLink(
                        $moduleName,
                        $this->tree,
                        MoreI18N::xlate('Relationship to me'),
                        $individual->xref(),
                        $individualSelf->xref(),
                        7); //TODO make configurable?
                    */
                    
                    $link = '<a class="vesta-relationship-chart-link" href="' . e($relationshipsChartModule->chartUrl($individualSelf, ['xref2' => $individual->xref()])) . '" rel="nofollow" title="' . MoreI18N::xlate('Relationship to me') . '">' . MoreI18N::xlate('Relationship to me') . '</a>';
                    
                    $content .= '&mdash; ' . $link;
                }
                    
                if ($type === 'list') {
                    $content .= '</li>';
                }
            }
        }

        if ($type === 'list') {
            $content .= '</ul>';
        }

        return $content;
    }
}

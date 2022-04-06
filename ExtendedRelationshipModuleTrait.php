<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\I18N;
use Vesta\CommonI18N;
use Vesta\ControlPanelUtils\Model\ControlPanelCheckbox;
use Vesta\ControlPanelUtils\Model\ControlPanelFactRestriction;
use Vesta\ControlPanelUtils\Model\ControlPanelPreferences;
use Vesta\ControlPanelUtils\Model\ControlPanelRadioButton;
use Vesta\ControlPanelUtils\Model\ControlPanelRadioButtons;
use Vesta\ControlPanelUtils\Model\ControlPanelSection;
use Vesta\ControlPanelUtils\Model\ControlPanelSubsection;

trait ExtendedRelationshipModuleTrait {

  protected function getMainTitle() {
    return CommonI18N::titleVestaExtendedRelationships();
  }

  public function getShortDescription() {
    return I18N::translate('A module providing various algorithms used to determine relationships. Includes a chart displaying relationships between two individuals, as a replacement for the original \'Relationships\' module.');
  }

  protected function getFullDescription() {
    $description = array();
    $description[] = 
            /* I18N: Module Configuration */I18N::translate('A module providing various algorithms used to determine relationships. Includes an extended \'Relationships\' chart.') . ' ' .
            /* I18N: Module Configuration */I18N::translate('Displays additional relationship information via the extended \'Families\' tab, and the extended \'Facts and Events\' tab.');
    $description[] = 
            /* I18N: Module Configuration */I18N::translate('Intended as a replacement for the original \'Relationships\' module.');
    $description[] = 
            CommonI18N::requires3(CommonI18N::titleVestaCommon(), CommonI18N::titleVestaRelatives(), CommonI18N::titleVestaPersonalFacts());
    return $description;
  }

  protected function createPrefs() {
    $generalSub = array();
    $generalSub[] = new ControlPanelSubsection(
            CommonI18N::displayedTitle(),
            array(/*new ControlPanelCheckbox(
                I18N::translate('Include the %1$s symbol in the module title', $this->getVestaSymbol()),
                null,
                'VESTA',
                '1'),*/
                new ControlPanelCheckbox(
                    CommonI18N::vestaSymbolInChartTitle(),
                    CommonI18N::vestaSymbolInTitle2(),
                    'VESTA_CHART',
                    '1'),
                new ControlPanelCheckbox(
                    CommonI18N::vestaSymbolInListTitle(),
                    null,
                    'VESTA_LIST',
                    '1')));

    $chartSub = array();
    $chartSub[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('Display'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Show common ancestors on top of relationship paths'),
                null,
                'CHART_SHOW_CAS',
                '1')));
    
    $chartSub[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('Debugging'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Show legacy relationship path names'),
                null,
                'CHART_SHOW_LEGACY',
                '1')));
    
    $chartSub[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('Options to show in the chart'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Find a closest relationship via common ancestors'),
                /* I18N: Module Configuration */I18N::translate('Determines the shortest path between two individuals via a LCA (lowest common ancestor), i.e. a common ancestor who only appears on the path once.'),
                'CHART_1',
                '1'),
        new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'),
                /* I18N: Module Configuration */I18N::translate('Each SLCA (smallest lowest common ancestor) essentially represents a part of the tree which both individuals share (as part of their ancestors). More technically, the SLCA set of two individuals is a subset of the LCA set (excluding all LCAs that are themselves ancestors of other LCAs).'),
                'CHART_2',
                '0'),
        new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Find all relationships via lowest common ancestors'),
                /* I18N: Module Configuration */I18N::translate('All paths between the two individuals that contribute to the CoR (Coefficient of Relationship), as defined here:') .
                ' <a href = "http://www.genetic-genealogy.co.uk/Toc115570135.html" target="_blank">Coefficient of Relationship</a>',
                'CHART_3',
                '1'),
        new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Find the closest overall connections (preferably via common ancestors)'),
                /* I18N: Module Configuration */I18N::translate('Prefers partial paths via common ancestors, even if there is no direct common ancestor.'),
                'CHART_4',
                '1'),
        new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Find a closest relationship via common ancestors, or fallback to the closest overall connection'),
                /* I18N: Module Configuration */I18N::translate('For close relationships similar to the previous option, but faster. Internally just a combination of two other methods.'),
                'CHART_7',
                '0'), //just a combination of two other options, not really required in the chart.
        new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Find the closest overall connections'),
                /* I18N: Module Configuration */I18N::translate('Same option as in the original relationship chart.'),
                'CHART_5',
                '1'),
        new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Find other/all overall connections'),
                /* I18N: Module Configuration */I18N::translate('Same option as in the original relationship chart, further configurable via recursion level:'),
                'CHART_6',
                '0'), //not a fan of this ...
        new ControlPanelRadioButtons(
                true,
                array(
            //new ControlPanelRadioButton(I18N::number('none'), null, 0), //redundant, just disable the option instead
            new ControlPanelRadioButton(I18N::number(1), null, 1),
            new ControlPanelRadioButton(I18N::number(2), null, 2),
            new ControlPanelRadioButton(I18N::number(3), null, 3),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('unlimited'), null, self::UNLIMITED_RECURSION)),
                /* I18N: Module Configuration */MoreI18N::xlate('Searching for all possible relationships can take a lot of time in complex trees.'),
                'RELATIONSHIP_RECURSION',
                self::DEFAULT_RECURSION)));

    $familiesSub = array();
    $familiesSub[] = new ControlPanelSubsection(
            CommonI18N::options(),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Allow persistent toggle (user may show/hide relationships)'),
                null,
                'TAB_TOGGLEABLE_RELS',
                '1')/* ,
              //disabled - direct ajax requests are buggy wrt I18N!
              new ControlPanelCheckbox(
              I18N::translate('Allow direct ajax requests (via \'moduleAjax.php\')'),
              I18N::translate('The relationships are calculated asynchronously, via additional requests to the server. These are executed a bit faster if this option is checked. However, this may lead to problems with respect to url rewriting, php open_basedir configuration etc., depending on your server configuration. The safe option is to leave this unchecked.'),
              'TAB_DIRECT_AJAX',
              '0') */));

    $familiesSub[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('How to determine relationships to the default individual'),
            array(new ControlPanelRadioButtons(
                false,
                array(
            new ControlPanelRadioButton(
                    /* I18N: Module Configuration */I18N::translate('Do not show any relationship'),
                    /* I18N: Module Configuration */ I18N::translate('The following options refer to the same algorithms as used in the extended relationships chart:'),
                    '0'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find a closest relationship via common ancestors'), null, '1'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'), null, '2'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find all relationships via lowest common ancestors'), null, '3'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find the closest overall connections (preferably via common ancestors)'), null, '4'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find a closest relationship via common ancestors, or fallback to the closest overall connection'), null, '7'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find the closest overall connections'), null, '5'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find other/all overall connections'), null, '6')),
                null,
                'TAB_REL_TO_DEFAULT_INDI',
                '7'), //fast and reasonable default
        new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Show common ancestors'),
                null,
                'TAB_REL_TO_DEFAULT_INDI_SHOW_CA',
                '1')));

    $familiesSub[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('How to determine relationships between parents'),
            array(new ControlPanelRadioButtons(
                false,
                array(
            new ControlPanelRadioButton(
                    /* I18N: Module Configuration */I18N::translate('Do not show any relationship'),
                    /* I18N: Module Configuration */I18N::translate('The following options refer to the same algorithms as used in the extended relationships chart:'),
                    '0'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find a closest relationship via common ancestors'), null, '1'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'), null, '2'),
            new ControlPanelRadioButton(
                    I18N::translate('Find all relationships via lowest common ancestors'),
                    /* I18N: Module Configuration */I18N::translate('Searching for regular overall connections would be pointless here because there is always a trivial HUSB - WIFE connection.') . ' ' .
                    /* I18N: Module Configuration */I18N::translate('Therefore, if one of the following options is selected, overall connections are determined via dated links, i.e. links that have been established before the date of the respective event.') . ' ' .
                    /* I18N: Module Configuration */I18N::translate('This allows you to present meaningful connections, such as \'widowed husband marries the sister of his dead wife\'.') . ' ' .
                    /* I18N: Module Configuration */I18N::translate('These relationships may only be calculated efficiently by preprocessing the tree data, via the synchronization link at the top of this page.'),
                    '3'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find the closest overall connections (preferably via common ancestors)'), null, '4'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find a closest relationship via common ancestors, or fallback to the closest overall connection'), null, '7'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find the closest overall connections'), null, '5'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find other/all overall connections'), null, '6')),
                null,
                'TAB_REL_OF_PARENTS',
                '1'),
        new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Show common ancestors'),
                null,
                'TAB_REL_OF_PARENTS_SHOW_CA',
                '1')));

    $familiesSub[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('How to determine relationships to spouses'),
            array(new ControlPanelRadioButtons(
                false,
                array(
            new ControlPanelRadioButton(
                    /* I18N: Module Configuration */I18N::translate('Do not show any relationship'),
                    /* I18N: Module Configuration */I18N::translate('The following options refer to the same algorithms as used in the extended relationships chart:'),
                    '0'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find a closest relationship via common ancestors'), null, '1'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'), null, '2'),
            new ControlPanelRadioButton(
                    /* I18N: Module Configuration */I18N::translate('Find all relationships via lowest common ancestors'),
                    /* I18N: Module Configuration */I18N::translate('Searching for regular overall connections would be pointless here because there is always a trivial HUSB - WIFE connection.') . ' ' .
                    /* I18N: Module Configuration */I18N::translate('Therefore, if one of the following options is selected, overall connections are determined via dated links, i.e. links that have been established before the date of the respective event.') . ' ' .
                    /* I18N: Module Configuration */I18N::translate('This allows you to present meaningful connections, such as \'widowed husband marries the sister of his dead wife\'.') . ' ' .
                    /* I18N: Module Configuration */I18N::translate('These relationships may only be calculated efficiently by preprocessing the tree data, via the synchronization link at the top of this page.'),
                    '3'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find the closest overall connections (preferably via common ancestors)'), null, '4'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find a closest relationship via common ancestors, or fallback to the closest overall connection'), null, '7'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find the closest overall connections'), null, '5'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find other/all overall connections'), null, '6')),
                null,
                'TAB_REL_TO_SPOUSE',
                '1'),
        new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Show common ancestors'),
                null,
                'TAB_REL_TO_SPOUSE_SHOW_CA',
                '1')));

    $factsSub = array();
    $factsSub[] = new ControlPanelSubsection(
            CommonI18N::options(),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Allow persistent toggle (user may show/hide relationships)'),
                null,
                'FTAB_TOGGLEABLE_RELS',
                '1')/* ,
              //disabled - direct ajax requests are buggy wrt I18N!
              new ControlPanelCheckbox(
              I18N::translate('Allow direct ajax requests (via \'moduleAjax.php\')'),
              I18N::translate('The relationships are calculated asynchronously, via additional requests to the server. These are executed a bit faster if this option is checked. However, this may lead to problems with respect to url rewriting, php open_basedir configuration etc., depending on your server configuration. The safe option is to leave this unchecked.'),
              'FTAB_DIRECT_AJAX',
              '0') */));

    $factsSub[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('How to determine relationships to the default individual'),
            array(new ControlPanelRadioButtons(
                false,
                array(
            new ControlPanelRadioButton(
                    /* I18N: Module Configuration */I18N::translate('Do not show any relationship'),
                    /* I18N: Module Configuration */I18N::translate('The following options refer to the same algorithms as used in the extended relationships chart:'),
                    '0'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find a closest relationship via common ancestors'), null, '1'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'), null, '2'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find all relationships via lowest common ancestors'), null, '3'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find the closest overall connections (preferably via common ancestors)'), null, '4'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find a closest relationship via common ancestors, or fallback to the closest overall connection'), null, '7'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find the closest overall connections'), null, '5'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find other/all overall connections'), null, '6')),
                null,
                'FTAB_REL_TO_DEFAULT_INDI',
                '7'), //fast and reasonable default
        new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Show common ancestors'),
                null,
                'FTAB_REL_TO_DEFAULT_INDI_SHOW_CA',
                '1')));

    $factsSub[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('How to determine relationships to associated persons'),
            array(new ControlPanelRadioButtons(
                false,
                array(
            new ControlPanelRadioButton(
                    /* I18N: Module Configuration */I18N::translate('Do not show any relationship'),
                    /* I18N: Module Configuration */I18N::translate('The following options refer to the same algorithms as used in the extended relationships chart:'),
                    '0'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find a closest relationship via common ancestors'), null, '1'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'), null, '2'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find all relationships via lowest common ancestors'), null, '3'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find the closest overall connections (preferably via common ancestors)'), null, '4'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find a closest relationship via common ancestors, or fallback to the closest overall connection'), null, '7'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find the closest overall connections'), null, '5'),
            new ControlPanelRadioButton(
                    /* I18N: Module Configuration */I18N::translate('Find other/all overall connections'),
                    /* I18N: Module Configuration */I18N::translate('The following options use dated links, i.e. links that have been established before the date of the respective event.') .
                    /* I18N: Module Configuration */I18N::translate('This seems more useful in most cases (e.g. to determine the relationship to a godparent at the time of the baptism).'),
                    '6'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find the closest overall connections (preferably via common ancestors)'), null, '14'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find a closest relationship via common ancestors, or fallback to the closest overall connection'), null, '17'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find the closest overall connections'), null, '15'),
            new ControlPanelRadioButton(/* I18N: Module Configuration */I18N::translate('Find other/all overall connections'), null, '16')),
                null,
                'TAB_REL_TO_ASSO',
                '15')));

    $factsSub[] = new ControlPanelSubsection(
            MoreI18N::xlate('Associated events'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Only show relationships for specific facts and events'),
                /* I18N: Module Configuration */I18N::translate('Associated facts and events are displayed when the respective toggle checkbox is selected on the tab.') . ' ' .
                /* I18N: Module Configuration */I18N::translate('If this option is checked, relationships to the associated individuals are only shown for the following facts and events.') . ' ' .
                /* I18N: Module Configuration */I18N::translate('In particular if both lists are empty, relationships will never be shown for these facts and events.') . ' ' .
                /* I18N: Module Configuration */I18N::translate('Note that the facts and events to be displayed at all may be filtered via the preferences of the tab.'),
                'TAB_REL_TO_ASSO_RESTRICTED',
                '0'),
        ControlPanelFactRestriction::createWithIndividualFacts(
                CommonI18N::restrictIndi(),
                'TAB_REL_TO_ASSO_RESTRICTED_INDI',
                'CHR,BAPM'),
        ControlPanelFactRestriction::createWithFamilyFacts(
                CommonI18N::restrictFam(),
                'TAB_REL_TO_ASSO_RESTRICTED_FAM',
                'MARR')));

    $sections = array();
    $sections[] = new ControlPanelSection(
            CommonI18N::general(),
            null,
            $generalSub);
    $sections[] = new ControlPanelSection(
            /* I18N: Module Configuration */I18N::translate('Chart Settings'),
            /* I18N: Module Configuration */I18N::translate('If you do not want to use the chart functionality, hide this chart via Control Panel > Charts > %1$s Vesta Extended Relationships (note that the chart is listed under the module name).', $this->getVestaSymbol()),
            $chartSub);

    $sections[] = new ControlPanelSection(
            /* I18N: Module Configuration */I18N::translate('Families Tab Settings'),
            /* I18N: Module Configuration */I18N::translate('If you do not want to change the functionality with respect to the original Families tab, select \'Do not show any relationship\' everywhere.') . ' ' .
            /* I18N: Module Configuration */I18N::translate('In that case, you should also disallow persistent toggle, as it has no visible effect.') . ' ' .
            /* I18N: Module Configuration */I18N::translate('You may also adjust the access level of this part of the module.'),
            $familiesSub);

    $sections[] = new ControlPanelSection(
            CommonI18N::factsAndEventsTabSettings(),
            /* I18N: Module Configuration */I18N::translate('If you do not want to change the functionality with respect to the original Facts and Events tab, select \'Do not show any relationship\' in the first block.') . ' ' .
            /* I18N: Module Configuration */I18N::translate('If you select this option everywhere, you should also disallow persistent toggle, as it has no visible effect.') . ' ' .
            /* I18N: Module Configuration */I18N::translate('You may also adjust the access level of this part of the module.'),
            $factsSub);

    return new ControlPanelPreferences($sections);
  }

  protected abstract function editConfigAfterFaq();
}

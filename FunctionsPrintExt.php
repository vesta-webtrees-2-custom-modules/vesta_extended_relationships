<?php
namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Fisharebest\Webtrees\I18N;

class FunctionsPrintExt {

	//cf helpLink in FunctionsPrint
	public static function helpLink($module, $topic) {
		$url = route('module', [
				'module' => $module,
				'action' => 'Help',
				'topic' => $topic
		]);
    
		return
            '<a href="#" data-toggle="modal" data-target="#wt-ajax-modal" data-href="' . $url . '" title="' . I18N::translate('Help') . '">' .
            view('icons/help') .
            '<span class="sr-only">' . I18N::translate('Help') . '</span>' .
            '</a>';
	}
}

<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

use Fisharebest\Webtrees\I18N;

class HelpTexts {
	
	public static function helpText($help) {
		switch ($help) {
		
		case 'Uncorrected CoR':
			$title = I18N::translate('Uncorrected CoR (Coefficient of Relationship)');
			$text  = 
			'<p>' .
			I18N::translate('The CoR (Coefficient of Relationship) is proportional to the number of genes that two individuals have in common as a result of their genetic relationship. Its calculation is based on Sewall Wright\'s method of path coefficients. Its value represents the proportion of genes held in common by two related individuals over and above those held in common by the whole population. More details here: ').
			'<u><a href = "http://www.genetic-genealogy.co.uk/Toc115570135.html" target="_blank">Coefficient of Relationship</a></u>.' .
			'</p><p>' .
			I18N::translate('Basically, each path (via common ancestors) between two individuals contributes to the CoR, inversely proportional to its length: Each path of length 2 (e.g. between full siblings) adds %1$s, each path of length 4 (e.g. between first cousins) adds %2$s, in general each path of length n adds (0.5)<sup>n</sup>.',
							I18N::percentage(0.25, 2),
							I18N::percentage(0.0625, 2)) .
			'</p><p>' .
			I18N::translate('Under normal circumstances the proportion of genes transmitted from ancestor to descendant &ndash; as estimated by Σ(0.5)<sup>n</sup> &ndash;  and the proportion of genes they hold in common (the true coefficient of relationship) are the same. If there has been any inbreeding, however, there may be a slight discrepancy, as explained here: ') .
			'<u><a href = "http://www.genetic-genealogy.co.uk/Toc115570148.html" target="_blank">Corrected Coefficient of Relationship</a></u>.' .
			'</p><p>' .
			I18N::translate('It is more complicated to determine this exact CoR, and the differences are usually small anyway. Therefore, only the Uncorrected CoR is calculated.') .
							
			'</p>';
			break;
		
		default:
			$title = I18N::translate('Help');
			$text  = I18N::translate('The help text has not been written for this item.');
			break;
		}
		
		return view('modals/help', [
            'title' => $title,
            'text'  => $text,
        ]);
	}
}
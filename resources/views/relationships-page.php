<?php use Fisharebest\Webtrees\Bootstrap4;
use Fisharebest\Webtrees\Functions\FunctionsEdit;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\View;

?>

<h2 class="wt-page-title">
	<?= $title ?>
</h2>

<form class="wt-page-options wt-page-options-relationships-chart d-print-none">
	<input type="hidden" name="route" value="relationships">
	<input type="hidden" name="ged" value="<?= e($tree->getName()) ?>">

	<div class="row form-group">
		<label class="col-sm-3 col-form-label wt-page-options-label" for="xref1">
			<?= I18N::translate('Individual 1') ?>
		</label>
		<div class="col-sm-9 wt-page-options-value">
			<?= FunctionsEdit::formControlIndividual($tree, $individual1, [
				'id'   => 'xref1',
				'name' => 'xref1',
			]) ?>
			<button class="btn btn-link small" id="btn-swap-individuals" type="button">
				<?= /* I18N: Reverse the order of two individuals */
				I18N::translate('Swap individuals') ?>
			</button>
		</div>
	</div>

	<div class="row form-group">
		<label class="col-sm-3 col-form-label wt-page-options-label" for="xref2">
			<?= I18N::translate('Individual 2') ?>
		</label>
		<div class="col-sm-9 wt-page-options-value">
			<?= FunctionsEdit::formControlIndividual($tree, $individual2, [
				'id'   => 'xref2',
				'name' => 'xref2',
			]) ?>
		</div>
	</div>

	<fieldset class="form-group">
		<div class="row">
			<legend class="col-form-label col-sm-3 wt-page-options-label">
			</legend>
			<div class="col-sm-9 wt-page-options-value">
				<?= Bootstrap4::radioButtons('find', $options, $find, false) ?>
			</div>
		</div>
	</fieldset>

	<div class="row form-group">
		<div class="col-form-label col-sm-3 wt-page-options-label"></div>
		<div class="col-sm-9 wt-page-options-value">
			<input class="btn btn-primary" type="submit" value="<?= /* I18N: A button label. */
			I18N::translate('view') ?>">
		</div>
	</div>
</form>

<!-- [RC] continue here -->
<?php if ($individual1 !== null && $individual2 !== null): ?>
	<div class="wt-ajax-load wt-page-content wt-chart wt-relationships-chart" data-ajax-url="<?= e(route('relationships-chart', [
		'xref1'     => $individual1->getXref(),
		'xref2'     => $individual2->getXref(),
		'ged'       => $individual2->getTree()->name(),
		'recursion' => 1/*$recursion*/,
		'ancestors' => 1/*$ancestors*/,
	])) ?>"></div>
<?php endif ?>

<?php View::push('javascript') ?>
<script>
  $('#btn-swap-individuals').click(function () {
    // Swap the (hidden) options of the select
    var select1       = document.querySelector('#xref1');
    var select2       = document.querySelector('#xref2');
    var tmp_html      = select1.innerHTML;
    select1.innerHTML = select2.innerHTML;
    select2.innerHTML = tmp_html;

    // Also swap the select2 element
    var span1       = document.querySelector('#xref1 + span');
    var span2       = document.querySelector('#xref2 + span');
    var tmp_html    = span1.innerHTML;
    span1.innerHTML = span2.innerHTML.replace('xref2', 'xref1');
    span2.innerHTML = tmp_html.replace('xref1', 'xref2');
  });
</script>
<?php View::endpush() ?>

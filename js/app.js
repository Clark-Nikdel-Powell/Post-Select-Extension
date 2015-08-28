jQuery(document).ready(function($) {
	$('[data-id=postselect] .selectoption').each(function() {
		var sel = $(this).find('option:first').val();
		var hid = $(this).data('value');
		$('[data-id=postselect] input[name='+hid+']').val(sel).trigger('change');
	});
	$('[data-id=postselect] .selectoption').change(function() {
		var sel = $(this).find(':selected').val();
		var hid = $(this).data('value');
		$('[data-id=postselect] input[name='+hid+']').val(sel).trigger('change');
	});
});
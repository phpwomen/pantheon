/*
	Advanced Custom Fields: Validated Field
	Justin Silver, http://justin.ag
	DoubleSharp, http://doublesharp.com

	Based on code in Advanced Custom Fields
*/
acf.field_group.submit = function(){
	// reference
	var _this = acf.field_group;

	// close / delete fields
	_this.$fields.find('.field').each(function(){	
		// vars
		var save = _this.get_field_meta( $(this), 'save'),
			ID = _this.get_field_meta( $(this), 'ID'),
			open = $(this).hasClass('open');

		// clone
		if( ID == 'acfcloneindex' ) {
			$(this).remove();
			return;
		}
		
		// close
		if( open ) {
			_this.close_field( $(this) );
		}
		
		// remove unnecessary inputs
		if( save == 'settings' ) {
			// do nothing
		} else if( save == 'meta' ) {
			$(this).children('.field-settings').find('[name^="acf_fields[' + ID + ']"]').remove();
		} else {
			$(this).find('[name^="acf_fields[' + ID + ']"]').remove();
		}
	});
	
	// return
	return true;
}
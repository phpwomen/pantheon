/*
	Advanced Custom Fields: Validated Field
	Justin Silver, http://justin.ag
	DoubleSharp, http://doublesharp.com
*/
var vf = {
	valid 		: false,
	lastclick 	: false,
	reclick		: false,
	debug 		: false,
	drafts		: true,
};

(function($){

	// DOM elements we need to validate the value of
	var inputSelector = 'input[type="text"], input[type="hidden"], textarea, select, input[type="checkbox"]:checked';

	// If a form value changes, mark the form as dirty
	$(document).on('change', inputSelector, function(){
		vf.valid = false;
	});

	// When a .button is clicked we need to track what was clicked
	$(document).on('click', 'form#post .button, form#post input[type=submit]', function(){
		vf.lastclick = $(this);
		// The default 'click' runs first and then calls 'submit' so we need to retrigger after we have tracked the 'lastclick'
		if (vf.reclick){
			vf.reclick = false;
			vf.lastclick.trigger('click');
		}
	});
	
	// Intercept the form submission
	$(document).on('submit', 'form#post', function(){
		// remove error messages since we are going to revalidate
		$('.field_type-validated_field').find('.acf-error-message').remove();

		if ( ! acf.validation.status ){
			$(this).siblings('#acfvf_message').remove();
			return false;
		} else {
			$(this).siblings('#acfvf_message, #message').remove();
		}

		// If we don't have a 'lastclick' this is probably a preview where WordPress calls 'click' first
		if (!vf.lastclick){
			// We need to let our click handler run, then start the whole thing over in our handler
			vf.reclick = true;
			return false;
		}

		// We mith have already checked the form and vf.valid is set and just want all the other 'submit' functions to run, otherwise check the validation
		return vf.valid || do_validation($(this), vf.lastclick);
	});

	// Validate the ACF Validated Fields
	function do_validation(formObj, clickObj){
		// default the form validation to false
		vf.valid = false;
		// we have to know what was clicked to retrigger
		if (!clickObj) return false;
		// validate non-"publish" clicks unless vf.drafts is set to false
		if (!vf.drafts&&clickObj.attr('id')!='publish') return true;
		// gather form fields and values to submit to the server
		var fields = [];

		// inspect each of the validated fields
		formObj.find('.field_type-validated_field').each( function(){
			div = $(this);

			// we want to show some of the hidden fields.
			var validate = true;
			if ( div.is(':hidden') ){
				validate = false;

				// if this field is hidden by a tab group, allow validation
				if ( div.hasClass('acf-tab_group-hide') ){
					validate = true;
					
					// vars
					var $tab_field = div.prevAll('.field_type-tab:first'),
						$tab_group = div.prevAll('.acf-tab-wrap:first');			
					
					// if the tab itself is hidden, bypass validation
					if ( $tab_field.hasClass('acf-conditional_logic-hide') ){
						validate = false;
					} else {
						// activate this tab as it holds hidden required field!
						$tab = $tab_group.find('.acf-tab-button[data-key="' + $tab_field.attr('data-field_key') + '"]');
					}
				}
			}
			
			// if is hidden by conditional logic, ignore
			if ( div.hasClass('acf-conditional_logic-hide') ){
				validate = false;
			}
			
			// if field group is hidden, ignore
			if ( div.closest('.postbox.acf-hidden').exists() ){
				validate = false;		
			}
			
			// we want to validate this field
			if ( validate ){
				if ( div.find('.acf_wysiwyg').exists() && typeof( tinyMCE ) == "object" ){
					// wysiwyg
					var id = div.find('.wp-editor-area').attr('id'),
						editor = tinyMCE.get( id );
					field = { 
						id: div.find('.wp-editor-area').attr('name'),
						value: editor.getContent()
					};
				} else if ( div.find('.acf_relationship, input[type="radio"], input[type="checkbox"]').exists() ) {
					// relationship / radio / checkbox
					sel = '.acf_relationship .relationship_right input, input[type="radio"]:checked, input[type="checkbox"]:checked';
					field = { id: div.find('input[type="hidden"], ' + sel ).attr('name'), value: [] };
					div.find( sel ).each( function(){
						field.value.push( $( this ).val() );
					});
				} else {
					// text / textarea / select
					var text = div.find('input[type="text"], input[type="email"], input[type="number"], input[type="hidden"], textarea, select');
					if ( text.exists() ){
						field = { 
								id: text.attr('name'),
								value: text.val()
						};
					}
				}

				// add to the array to send to the server
				fields.push( field );
			}
		});

		$('.acf_postbox:hidden').remove();

		// if there are no fields, don't make an ajax call.
		if ( ! fields.length ){
			vf.valid = true;
			return true;
		} else {
			// send everything to the server to validate
			var postEl = vf.frontend? 'input[name=post_id]' : '#post_ID';
			$.ajax({
				url: ajaxurl,
				data: {
					action: 'validate_fields',
					post_id: formObj.find(postEl).val(),
					click_id: clickObj.attr('id'),
					frontend: vf.frontend,
					fields: fields
				},
				type: 'POST',
				dataType: 'json',
				success: function(json){
					ajax_returned(json, formObj, clickObj);				
				}, error:function (xhr, ajaxOptions, thrownError){
					ajax_returned(fields, formObj, clickObj);
 				}
			});

			// return false to block the 'submit', we will handle as necessary once we get a response from the server
			return false;
		}
		
		// Process the data returned by the server side validation
		function ajax_returned(fields, formObj, clickObj){
			// now we default to true since the response says if something is invalid
			vf.valid = true;
			// if we got a good response, iterate each response and if it's not valid, set an error message on it
			if (fields){
				for (var i=0; i<fields.length; i++){
					var fld = fields[i];
					if (!fld.valid){
						vf.valid = false;
						msg = $('<div/>').html(fld.message).text();
						input = $('[name="'+fld.id.replace('[', '\\[').replace(']', '\\]')+'"]');
						input.parent().parent().append('<span class="acf-error-message"><i class="bit"></i>' + msg + '</span>');
						field = input.closest('.field');
						field.addClass('error');
						field.find('.widefat').css('width','100%');
					}
				}
			}
			
			// reset all the CSS
			$('#ajax-loading').attr('style','');
			$('.submitbox .spinner').hide();
			$('.submitbox .button').removeClass('button-primary-disabled').removeClass('disabled');
			if ( !vf.valid ){
				// if it wasn't valid, show all the errors
				formObj.before('<div id="acfvf_message" class="error"><p>Validation Failed. See errors below.</p></div>');
				formObj.find('.field_type-validated_field .acf-error-message').show();
			} else if ( vf.debug ){
				// it was valid, but we have debugging on which will confirm the submit
				vf.valid = confirm("The fields are valid, do you want to submit the form?");
			} 
			// if everything is good, reclick which will now bypass the validation
			if (vf.valid) {
				clickObj.click();
			}
		}
	}
})(jQuery);

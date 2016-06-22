(function($){
	
	
	function initialize_field( $el ) {
		
		//$el.doStuff();
		
	}
	
	
	if( typeof acf.add_action !== 'undefined' ) {

		acf.fields.medicine_pack = acf.fields.select.extend({

			type: 'medicine_pack',
			minimumInputLength: 1,
    		quietMillis: 100 //or 100 or 10

		});
	
		/*
		*  ready append (ACF5)
		*
		*  These are 2 events which are fired during the page load
		*  ready = on page load similar to $(document).ready()
		*  append = on new DOM elements appended via repeater field
		*
		*  @type	event
		*  @date	20/07/13
		*
		*  @param	$el (jQuery selection) the jQuery element which contains the ACF fields
		*  @return	n/a
		*/
		
		acf.add_action('ready append', function( $el ){

			// search $el for fields of type 'FIELD_NAME'
			acf.get_fields({ type : 'medicine_pack'}, $el).each(function(){
				
				initialize_field( $(this) );
				
			});
			
		});

		acf.add_filter('prepare_for_ajax', function( args ) {
			
			// context for the lookup
			var fieldKey = args.field_key;
			var wrapper = $('div.acf-' + fieldKey.replace('_','-'));
			var parent = wrapper.parents('div.acf-postbox');
			
			// contextualise this lookup
			var vtmId = $('div[data-type="vtm"] input', parent).val();
			var vmpId = $('div[data-type="vmp"] input', parent).val();
			var vmppId = $('div[data-type="vmpp"] input', parent).val();
			var ampId = $('div[data-type="amp"] input', parent).val();
			
			// capture the value of the parent VTM and set
			if (vtmId) {
				args['vtm_id'] = vtmId;
			}
			if (vmpId) {
				args['vmp_id'] = vmpId;
			}
			if (vmppId) {
				args['vmpp_id'] = vmppId;
			}
			if (ampId) {
				args['amp_id'] = ampId;
			}

			return args;
		})
		
		
	} else {
		
		
		/*
		*  acf/setup_fields (ACF4)
		*
		*  This event is triggered when ACF adds any new elements to the DOM. 
		*
		*  @type	function
		*  @since	1.0.0
		*  @date	01/01/12
		*
		*  @param	event		e: an event object. This can be ignored
		*  @param	Element		postbox: An element which contains the new HTML
		*
		*  @return	n/a
		*/
		
		$(document).on('acf/setup_fields', function(e, postbox){
			
			$(postbox).find('.field[data-field_type="medicine_pack"]').each(function(){
				
				initialize_field( $(this) );
				
			});
		
		});
	
	
	}


})(jQuery);

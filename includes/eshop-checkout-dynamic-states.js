jQuery(document).ready(function($){
	
	window.eShopDynamicStates = window.eShopDynamicStates || {};
	
	/**
	 *  @package eShopDynamicStates
	 *  @desc    Refreshes the states fields 
	 */
	eShopDynamicStates.refresh_states = function(country_code,target_field,prequel,sequel) {
		
		var data = {
			    country_code: country_code,
			    action: eShopDynamicStates.ajaxaction
		};
		
		if (prequel){
			prequel();
		}
		
		$.ajax({
				type    : eShopDynamicStates.method,
				url     : eShopDynamicStates.ajaxurl,
				data    : data,
				dataType: 'json',			
				success : function(ajax_response){
					if (sequel) {
						sequel(ajax_response);
					}
				}
		});
		
	};
	
	
	/**
	 *  @package eShopDynamicStates
	 *  @desc    Replaces the select field with text input and vice-versa according to ajax data 
	 */
	eShopDynamicStates.replace_field = function(jtarget,data){
		var name = jtarget.attr('name'), 
			id   = jtarget.attr('id'),
		    classNames = jtarget.attr('class'),
		    has_elements = 0,
		    replace;
		
		for (i in data) {
			if (data.hasOwnProperty(i)){
				has_elements++;
				break;
			}
		}

		if (has_elements) {
			name = (id == 'state') ? 'state' : 'ship_state';
			replace = $("<select>",{name: name, id: id, 'class': classNames});
			
			// Create dropdown options
			$.each(data, function(key, value) {
				var attrs = {value: value};
				
				if (id == 'state') {
					if (eShopDynamicStates.sel_fields.state && value == eShopDynamicStates.sel_fields.state) {
						attrs.selected = 'selected';
					}
				}
				else if (eShopDynamicStates.sel_fields.ship_altstate && value == eShopDynamicStates.sel_fields.ship_altstate){
					attrs.selected = 'selected';
				}
				
				replace.append($('<option>', attrs)
			           .text(key));
			});
		}
		else {
			name = (id == 'state') ? 'altstate' : 'ship_altstate';
			replace = $("<input/>", {type: 'text', name: name, id: id, 'class': classNames});
			
			if (id == 'state') {
				if (eShopDynamicStates.sel_fields.state ) {
					replace.val(eShopDynamicStates.sel_fields.state);
				}
			}
			else if (eShopDynamicStates.sel_fields.ship_altstate) {
				replace.val(eShopDynamicStates.sel_fields.ship_altstate);
			}
		}
		
		jtarget.replaceWith(replace);
	};
	
	
	/**
	 *  @package eShopDynamicStates
	 *  @var     obj jQuery object being clicked
	 *  @desc    Wrapper for replace_field 
	 */
	eShopDynamicStates.do_action = function(obj) {
		
		var country_code = obj.val(),
		id = obj.attr('id'),
	    target_field,prequel,sequel,repop;
	
		target_field = (id == 'country') ? $('#state') : $('#shipstate'); 
	
		prequel = function(){
			$('#usc_dyn_states_throbber_from_' + id).show();
		};
	
		sequel = function(ajaxresponse){
			$('#usc_dyn_states_throbber_from_' + id).hide();
			eShopDynamicStates.replace_field(target_field,ajaxresponse.data);
			
			if ($.totalStorage('eshop_checkout_state')) {
				$('select#state').val($.totalStorage('eshop_checkout_state'));
			}
			
			if ($.totalStorage('eshop_checkout_ship_state')) {
				$('select#shipstate').val($.totalStorage('eshop_checkout_ship_state'));
			}
		};
	
		eShopDynamicStates.refresh_states(country_code,target_field,prequel,sequel);
	};
	
	
	
	// Move country to before State
	if ($('span.state')) {
		
		$('span.state').before($('span.country'));

		$('span.state').append($('<img />',{src: eShopDynamicStates.includes_url + '/arrows-throbber.gif', 
			id : 'usc_dyn_states_throbber_from_country', 
			style: 'float: right; display:none'}));
	}
	
	if ($('span.ship_state')) {
		
		$('span.ship_state').before($('span.shipcountry'));

		$('span.ship_state').append($('<img />',{src: eShopDynamicStates.includes_url + '/arrows-throbber.gif', 
			id : 'usc_dyn_states_throbber_from_shipcountry', 
			style: 'float: right; display:none'}));
	}
	
	
	// Remove original altstate and ship_altstate
	$('span.altstate,span.ship_altstate').remove();
	
	// Tie events
	$('#country,#shipcountry').change(function(){
		eShopDynamicStates.do_action($(this));
	});
	
	if (typeof $.fn.on !== 'undefined') {
		$('#state').on('change',function(){
			$.totalStorage('eshop_checkout_state',$(this).val());
		});
		
		$('#shipstate').on('change',function(){
			$.totalStorage('eshop_checkout_ship_state',$(this).val());
		});
	}
	else {
		$('#state').live('change',function(){
			$.totalStorage('eshop_checkout_state',$(this).val());
		});
		
		$('#shipstate').live('change',function(){
			$.totalStorage('eshop_checkout_ship_state',$(this).val());
		});
	}
	
	
	$('form.eshopform').submit(function(){
		if ( $('#state').length ) {
			$.totalStorage('eshop_checkout_state',$('#state').val());
		}
		
		if ( $('#shipstate').length ) {
			$.totalStorage('eshop_checkout_ship_state',$('#shipstate').val());
		}
	});
	
	// Kick off process on load
	(function(){
		
		if (eShopDynamicStates.sel_fields.state) {
			$.totalStorage('eshop_checkout_state', eShopDynamicStates.sel_fields.state);
		}
		
		if (eShopDynamicStates.sel_fields.ship_state) {
			$.totalStorage('eshop_checkout_ship_state', eShopDynamicStates.sel_fields.ship_state);
		}
		
		
		if ($('#country')) {
			eShopDynamicStates.do_action($('#country'));
		}
		
		if ($('#shipcountry')) {
			eShopDynamicStates.do_action($('#shipcountry'));
		}
		
	})();
	
});
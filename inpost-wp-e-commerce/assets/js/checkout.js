///
// Checkout.js file
//

///
// user_function function
//
// @param value mixed string
// @return none
//
function user_function(value)
{
        var address = value.split(';');
        document.getElementById('town').value=address[1];
        document.getElementById('street').value=address[2]+address[3];
}

///
// add_inpost_fields function
//
// @params none
// @return none
//
function add_inpost_fields()
{
	var base_url = document.location.origin + document.location.pathname;

	var html = '';

 		html += '<tr id="inpost_added_machine_field" class="inpostshipping_0">'; 
                html += '<td class="wpsc_shipping_quote_price wpsc_shipping_quote_price_inpostshipping_0" colspan="5" >';
               	html += '<label for="terminal" >Select a Locker: </label>';
               	html += '<script type="text/javascript" src="https://geowidget.inpost.co.uk/dropdown.php?field_to_update=inpost_name&field_to_update2=address&user_function=user_function"></script>';
               	html += '<a href="#" onclick="openMap(); return false;"> <span style="font-size:15px; color:blue;">';
	       html += '<img src="';
	       html += base_url + 'wp-content/plugins/inpost-wp-e-commerce/assets/images/map_icon.png"  alt="Map of InPost 24/7 Parcel Lockers" title="Map of InPost 24/7 Parcel Lockers" </span></a>';
                    	html += '<label for="inpost_name" ><span style="color:grey;">Locker ID:</span> </label><input placeholder="Select from MAP" style="color:grey;" type="text" size="15" id="inpost_name" name="attributes[inpost_dest_machine]">';
                  	html += '<input type="hidden" id="address" name="address">';
                  	html += '<input type="hidden" style="color:grey;" type="text" size="30" id="street" name="attributes[inpost_terminal_street]">';
                  	html += '<input type="hidden" style="color:grey;" type="text" size="20" id="town" name="attributes[inpost_terminal_town]">';
                  	html += '</td>';
                  	html += '</tr>';
                  	html += '<tr id="inpost_shipping_plugin_mobile">';
                  	html += '<td class="wpsc_shipping_quote_price wpsc_shipping_quote_price_inpostshipping_0" colspan="5">';
                  		html += '<label for="mobile" >Enter Mobile (07): </label><input type="text" size="10" maxlength="9" id="mobile" name="attributes[inpost_cust_mobile]">';
                  	html += '</td>';
 		html += '</tr>'; 

	jQuery('.inpostshipping_0').after(html);
}

jQuery(document).ready(function() {

	var use_inpost = jQuery('#inpost_can_use').val();

	if(use_inpost == '0')
	{
		alert("The parcel is too large for InPost");

		jQuery('#inpostshipping_0').prop('checked', false);
		jQuery('#inpostshipping_0').prop('disabled', true);

	}
	else
	{
		var inpost = jQuery('#inpostshipping_0').prop('checked');
		if(inpost == true)
		{
			add_inpost_fields();
		}
	}

	jQuery( "form.wpsc_checkout_forms" ).on("submit", function() {
		// Check the Machine ID and Mobile fields for values.

		var ret = true;

		if(jQuery('#inpost_name').val() == '')
		{
			alert('Machine ID must be filled in.');
			ret = false;
		}
		if(jQuery('#mobile').val() == '' ||
			jQuery('#mobile').val().length != 9)
		{
			alert('Mobile number must be filled in.\nAnd nine (9) characters long.');
			ret = false;
		}

		if(ret == true)
		{
			// Copy the Machine ID into our hidden field
			jQuery('#inpost_machine').val(jQuery('#inpost_name').val());
			// Copy the Mobile into our hidden field
			jQuery('#inpost_mobile').val(jQuery('#mobile').val());
		}

		return ret;
	});
});

jQuery( function( $ ) {
	// Shipping calculator
	$( document ).on( 'change', 'select.shipping_method, input[name^=shipping_method]', function() {
	var disable = jQuery('#inpostshipping_0').prop('disabled');

	if(disable == true)
	{
		// We don't need to do anything else if the InPost method is
		// not enabled.
		return;
	}

	var use_inpost = jQuery('#inpost_can_use').val();

	if(use_inpost == '0')
	{
		alert("The parcel is too large for InPost");

		jQuery('#shipping_method_0_inpost_shipping_method').prop('disabled',
			true);

	}
	else
	{
		var inpost = jQuery('#inpostshipping_0').prop('checked');
		if(inpost == true)
		{
			alert("Please select a Locker to send the Parcel to.");
			add_inpost_fields();
		}
		else
		{
			remove_inpost_fields();
		}
	}

	});
});

///
// remove_inpost_fields function
//
// @params none
// @return none
//
function remove_inpost_fields()
{
	// Check that the fields are still there before trying to remove them.
	var field = jQuery('#mobile').get();

	if(field == '')
	{
		return;
	}

	jQuery('#inpost_added_machine_field').remove();
	jQuery('#inpost_shipping_plugin_mobile').remove();
}


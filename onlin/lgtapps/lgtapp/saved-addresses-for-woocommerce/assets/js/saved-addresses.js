/**
 * Saved Addresses For WooCommerce Checkout JS
 *
 * @package saved-addresses-for-woocommerce/assets/js
 */

jQuery(
	function () {
		var ajax_url = saved_addresses_params.ajax_url;

		let defaultBillingAddressID = saved_addresses_params.default_billing_key;
		jQuery( '#billing_address_container_' + defaultBillingAddressID ).find( '.bill_to_this_address_button' ).hide();
		jQuery( '#billing_address_container_' + defaultBillingAddressID ).find( '.billing_to_this_address' ).show();
		jQuery( '.billing_address_selected' ).prepend( '<span class="dashicons dashicons-yes"></span>' );
		// Code to set default billing address on page load.
		jQuery(
			function() {
				setAddress( 'billing', defaultBillingAddressID, 'yes', 'yes' );
			}
		);

		let defaultShippingAddressID = saved_addresses_params.default_shipping_key;
		jQuery( '#shipping_address_container_' + defaultShippingAddressID ).find( '.ship_to_this_address_button' ).hide();
		jQuery( '#shipping_address_container_' + defaultShippingAddressID ).find( '.shipping_to_this_address' ).show();
		jQuery( '.shipping_address_selected' ).prepend( '<span class="dashicons dashicons-yes"></span>' );
		// Code to set default Shipping address on page load.
		jQuery(
			function() {
				setAddress( 'shipping', defaultShippingAddressID, 'yes', 'yes' );
			}
		);

		// Auto fill billing details on the click of 'Bill to this address' button.
		jQuery( document ).on(
			'click',
			'#bill_here_button',
			function (e) {
				e.preventDefault();

				let address_id = jQuery( this ).attr( 'data-address-id' );
				setAddress( 'billing', address_id, 'no', 'yes' );
			}
		);

		// Auto fill shipping details on the click of 'Ship to this address' button.
		jQuery( document ).on(
			'click',
			'#ship_here_button',
			function (e) {
				e.preventDefault();

				let address_id = jQuery( this ).attr( 'data-address-id' );
				setAddress( 'shipping', address_id, 'no', 'yes' );
			}
		);

		// Show the billing form on click of 'Bill to a new address'.
		jQuery( '#bill_to_new_address_button' ).on(
			'click',
			function() {
				jQuery( '.woocommerce-billing-fields' ).find( '.billing_address_form' ).show();
				jQuery( '.billing_address_form input[type="text"]' ).val( '' );
				jQuery( '.billing_address_form input[type="tel"]' ).val( '' );

				jQuery( '.sa_billing_addresses_container' ).find( '.bill_to_this_address_button' ).show();
				jQuery( '.sa_billing_addresses_container' ).find( '.billing_to_this_address' ).hide();
				jQuery( '.sa_billing_addresses_container' ).find( '.dashicons-yes' ).remove();

				// Do blank for hidden field value.
				jQuery( '#saw_billing_address_id' ).val( '' );

				// Scroll to form.
				jQuery( 'html, body' ).animate(
					{
						scrollTop: jQuery( '.billing_address_form' ).offset().top
					},
					1000
				);
			}
		);

		// Show the shipping form on click of 'Ship to a new address'.
		jQuery( '#ship_to_new_address_button' ).on(
			'click',
			function() {
				jQuery( '.woocommerce-shipping-fields' ).find( '.shipping_address_form' ).show();
				jQuery( '#ship-to-different-address-checkbox' ).attr( 'checked', 'checked' );
				jQuery( '.shipping_address_form input[type="text"]' ).val( '' );

				jQuery( '.sa_shipping_addresses_container' ).find( '.ship_to_this_address_button' ).show();
				jQuery( '.sa_shipping_addresses_container' ).find( '.shipping_to_this_address' ).hide();
				jQuery( '.sa_shipping_addresses_container' ).find( '.dashicons-yes' ).remove();

				// Do blank for hidden field value.
				jQuery( '#saw_shipping_address_id' ).val( '' );

				// Scroll to form.
				jQuery( 'html, body' ).animate(
					{
						scrollTop: jQuery( '.shipping_address_form' ).offset().top
					},
					1000
				);
			}
		);

		// Edit Billing Address.
		jQuery( document ).on(
			'click',
			'div.address_container_billing .saw-edit',
			function() {
				let editAddressID = jQuery( this ).attr( 'data-edit-id' );
				setAddress( 'billing', editAddressID, 'yes', 'no' );
				jQuery( '.woocommerce-billing-fields' ).find( '.billing_address_form' ).show();
				// Scroll to form.
				jQuery( 'html, body' ).animate(
					{
						scrollTop: jQuery( '.billing_address_form' ).offset().top
					},
					1000
				);
			}
		);

		// Edit Shipping Address.
		jQuery( document ).on(
			'click',
			'div.address_container_shipping .saw-edit',
			function() {
				let editAddressID = jQuery( this ).attr( 'data-edit-id' );
				setAddress( 'shipping', editAddressID, 'yes', 'no' );
				jQuery( '.woocommerce-shipping-fields' ).find( '.shipping_address_form' ).show();
				// Scroll to form.
				jQuery( 'html, body' ).animate(
					{
						scrollTop: jQuery( '.shipping_address_form' ).offset().top
					},
					1000
				);
			}
		);

		// Delete Billing Address.
		jQuery( document ).on(
			'click',
			'div.address_container_billing .saw-delete',
			function(e) {
				e.preventDefault();

				if ( confirm( saved_addresses_params.confirm_delete_text ) ) {
					let delete_id = jQuery( this ).attr( 'data-delete-id' );

					jQuery.ajax(
						{
							type: 'POST',
							url: ajax_url,
							dataType: 'json',
							data: {
								action: 'delete_billing_address',
								delete_id: delete_id,
								security: saved_addresses_params.saw_delete_address
							},
							success: function( response ) {
								if ( response.deleted ) {
									jQuery( '#billing_address_container_' + delete_id ).fadeOut(
										300,
										function() {
											jQuery( this ).remove();
										}
									);
								}
							},
						}
					);
				}
			}
		);

		// Delete Shipping Address.
		jQuery( document ).on(
			'click',
			'div.address_container_shipping .saw-delete',
			function(e) {
				e.preventDefault();

				if ( confirm( saved_addresses_params.confirm_delete_text ) ) {
					let delete_id = jQuery( this ).attr( 'data-delete-id' );

					jQuery.ajax(
						{
							type: 'POST',
							url: ajax_url,
							dataType: 'json',
							data: {
								action: 'delete_shipping_address',
								delete_id: delete_id,
								security: saved_addresses_params.saw_delete_address
							},
							success: function( response ) {
								if ( response.deleted ) {
									jQuery( '#shipping_address_container_' + delete_id ).fadeOut(
										300,
										function() {
											jQuery( this ).remove();
										}
									);
								}
							},
						}
					);
				}
			}
		);

		// Show or hide the Saved Addresses fields.
		if ( saved_addresses_params.is_user_logged_in == 1 && saved_addresses_params.billing_available == 1 ) {
			jQuery( '.woocommerce-billing-fields' ).find( '.billing_address' ).show();
			jQuery( '.woocommerce-billing-fields' ).find( '.billing_address_form' ).hide();
		}

		// Control shipping fields based on WC shipping setting.
		if ( saved_addresses_params.is_user_logged_in == 1 ) {
			if ( 'shipping' == saved_addresses_params.wc_shipping_option ) {
				jQuery( '.woocommerce-shipping-fields' ).find( '.shipping_address' ).show();
				jQuery( '.woocommerce-shipping-fields' ).find( '.shipping_address_form' ).hide();
			}
		}

		// Change last billing address based on select2 select.
		jQuery( '#wc_saved_billing_addresses' ).on('select2:select', function () {
			let newAddressID = jQuery( '#wc_saved_billing_addresses' ).val();
			let currentAddressData = jQuery( '#wc_saved_billing_addresses' ).find('option:selected').text();

			// Get last address id from HTML.
			let lastAddressID = jQuery('.billing_addresses_container').children().last().attr('id');
			let oldAddressID  = parseInt(lastAddressID.replace(/[^0-9.]/g, ""));

			jQuery.ajax({
				type: 'POST',
				url: ajax_url,
				dataType: 'json',
				data: {
					action: 'update_last_billing_address',
					address_key_to_add: newAddressID,
					address_key_to_remove: oldAddressID,
					security: saved_addresses_params.saw_update_address
				},
				success: function( response ) {
					if ( response ) {
						let addAddress = response.address_to_add;

						jQuery('#billing_address_container_'+oldAddressID).replaceWith(jQuery('<div class="address_container_billing" id="billing_address_container_'+newAddressID+'"><p class="single_address" value="'+addAddress+'">'+addAddress+'</p><div class="bill_to_this_address_button"><input type="button" class="button" id="bill_here_button" data-address-id="'+newAddressID+'" value="Bill to this address"></div><div class="billing_to_this_address" style="display:none;"><div class="billing_address_selected"><span class="dashicons dashicons-yes"></span><span>selected</span></div></div><div class="billing_address_edit_delete"><a class="saw-edit" id="edit_address_'+newAddressID+'" data-edit-id="'+newAddressID+'">Edit</a><a class="saw-delete" id="delete_address_'+newAddressID+'" data-delete-id="'+newAddressID+'">Delete</a></div></div>'));

						// Mark it selected.
						setAddress( 'billing', newAddressID, 'no', 'yes' );

						// Push address removed in select2.
						var data = {
							id: oldAddressID,
							text: response.address_to_remove
						};
						// Set the value, creating a new option if necessary.
						if ( jQuery( '#wc_saved_billing_addresses' ).find( "option[value='" + data.id + "']" ).length ) {
							jQuery( '#wc_saved_billing_addresses' ).val( data.id ).trigger( 'change' );
						} else { 
							// Create a DOM Option and pre-select by default.
							var newOption = new Option(data.text, data.id, true, true);
							// Append it to the select.
							jQuery( '#wc_saved_billing_addresses' ).append( newOption ).trigger( 'change' );
						}

						// Clear selection of value and title.
						jQuery( '#wc_saved_billing_addresses' ).val(null).trigger( 'change' );
						jQuery( '#select2-wc_saved_billing_addresses-container' ).removeAttr( 'title' );

					}
				},
			});

		});

		// Change last shipping address based on select2 select.
		jQuery( '#wc_saved_shipping_addresses' ).on('select2:select', function () {
			let newAddressID = jQuery( '#wc_saved_shipping_addresses' ).val();
			let currentAddressData = jQuery( '#wc_saved_shipping_addresses' ).find('option:selected').text();

			// Get last address id from HTML
			let lastAddressID = jQuery('.shipping_addresses_container').children().last().attr('id');
			let oldAddressID  = parseInt(lastAddressID.replace(/[^0-9.]/g, ""));

			jQuery.ajax({
				type: 'POST',
				url: ajax_url,
				dataType: 'json',
				data: {
					action: 'update_last_shipping_address',
					address_key_to_add: newAddressID,
					address_key_to_remove: oldAddressID,
					security: saved_addresses_params.saw_update_address
				},
				success: function( response ) {
					if ( response ) {
						let addAddress = response.address_to_add;

						jQuery('#shipping_address_container_'+oldAddressID).replaceWith(jQuery('<div class="address_container_shipping" id="shipping_address_container_'+newAddressID+'"><p class="single_address" value="'+addAddress+'">'+addAddress+'</p><div class="ship_to_this_address_button"><input type="button" class="button" id="ship_here_button" data-address-id="'+newAddressID+'" value="Ship to this address"></div><div class="shipping_to_this_address" style="display:none;"><div class="shipping_address_selected"><span class="dashicons dashicons-yes"></span><span>selected</span></div></div><div class="shipping_address_edit_delete"><a class="saw-edit" id="edit_address_'+newAddressID+'" data-edit-id="'+newAddressID+'">Edit</a><a class="saw-delete" id="delete_address_'+newAddressID+'" data-delete-id="'+newAddressID+'">Delete</a></div></div>'));

						// Mark it selected.
						setAddress( 'shipping', newAddressID, 'no', 'yes' );

						// Push address removed in select2.
						var data = {
							id: oldAddressID,
							text: response.address_to_remove
						};
						// Set the value, creating a new option if necessary.
						if ( jQuery( '#wc_saved_shipping_addresses' ).find( "option[value='" + data.id + "']" ).length ) {
							jQuery( '#wc_saved_shipping_addresses' ).val(data.id).trigger( 'change' );
						} else { 
							// Create a DOM Option and pre-select by default.
							var newOption = new Option(data.text, data.id, true, true);
							// Append it to the select.
							jQuery('#wc_saved_shipping_addresses').append(newOption).trigger( 'change' );
						}

						// Clear selection of value and title.
						jQuery( '#wc_saved_shipping_addresses' ).val(null).trigger( 'change' );
						jQuery( '#select2-wc_saved_shipping_addresses-container' ).removeAttr( 'title' );

					}
				},
			});

		});

		// Function to set billing and shipping address.
		function setAddress( addressType, addressID, isDefaultAddress, hideCheckoutForm ) {
			ajaxAction = ( 'shipping' == addressType ) ? 'select_shipping_address' : 'select_billing_address';
			jQuery.ajax(
				{
					type: 'POST',
					url: ajax_url,
					dataType: 'json',
					data: {
						action: ajaxAction,
						address_id: addressID,
						security: saved_addresses_params.saw_select_address
					},
					success: function( response ) {

						if ( ! jQuery.isEmptyObject( response ) ) {

							if ( 'billing' == addressType ) {

								// Update hidden field with Address ID.
								jQuery( '#saw_billing_address_id' ).val( addressID );

								// Auto Fill in the shipping details.
								jQuery( '#billing_first_name' ).val( response.first_name );
								jQuery( '#billing_last_name' ).val( response.last_name );
								jQuery( '#billing_company' ).val( response.company );
								jQuery( '#billing_address_1' ).val( response.address_1 );
								jQuery( '#billing_address_2' ).val( response.address_2 );
								jQuery( '#billing_city' ).val( response.city );
								jQuery( '#billing_postcode' ).val( response.postcode );
								jQuery( '#billing_country' ).val( response.country ).trigger( 'change' );
								jQuery( '#billing_country_chosen' ).find( 'span' ).html( response.country_text ); // can't find.
								jQuery( '#billing_state' ).val( response.state );
								jQuery( '#billing_email' ).val( response.email );
								jQuery( '#billing_phone' ).val( response.phone );
								let stateName = jQuery( '#billing_state option[value="' + response.state + '"]' ).text();
								jQuery( '#s2id_billing_state' ).find( '.select2-chosen' ).html( stateName ).parent().removeClass( 'select2-default' );

								if ( 'no' == isDefaultAddress ) {
									// Remove tick from present.
									jQuery( '.sa_billing_addresses_container' ).find( '.dashicons-yes' ).remove();
									jQuery( '.sa_billing_addresses_container' ).find( '.bill_to_this_address_button' ).show();
									jQuery( '.sa_billing_addresses_container' ).find( '.billing_to_this_address' ).hide();

									// Add the tick icon to the selected address.
									jQuery( '#billing_address_container_' + addressID ).find( '.bill_to_this_address_button' ).hide();
									jQuery( '#billing_address_container_' + addressID ).find( '.billing_to_this_address' ).show();
									jQuery( '.billing_address_selected' ).prepend( '<span class="dashicons dashicons-yes"></span>' );
								}

								// Hide the billing checkout form.
								if ( 'yes' == hideCheckoutForm ) {
									jQuery( '.woocommerce-billing-fields' ).find( '.billing_address_form' ).hide();
								}

							} else if ( 'shipping' == addressType ) {

								// Update hidden field with Address ID.
								jQuery( '#saw_shipping_address_id' ).val( addressID );

								// Auto Fill in the shipping details.
								jQuery( '#shipping_first_name' ).val( response.first_name );
								jQuery( '#shipping_last_name' ).val( response.last_name );
								jQuery( '#shipping_company' ).val( response.company );
								jQuery( '#shipping_address_1' ).val( response.address_1 );
								jQuery( '#shipping_address_2' ).val( response.address_2 );
								jQuery( '#shipping_city' ).val( response.city );
								jQuery( '#shipping_postcode' ).val( response.postcode );
								jQuery( '#shipping_country' ).val( response.country ).trigger( 'change' );
								jQuery( '#shipping_country_chosen' ).find( 'span' ).html( response.country_text ); // can't find.
								jQuery( '#shipping_state' ).val( response.state );
								let stateName = jQuery( '#shipping_state option[value="' + response.state + '"]' ).text();
								jQuery( '#s2id_shipping_state' ).find( '.select2-chosen' ).html( stateName ).parent().removeClass( 'select2-default' );

								if ( 'no' == isDefaultAddress ) {
									// Remove tick from present.
									jQuery( '.sa_shipping_addresses_container' ).find( '.dashicons-yes' ).remove();
									jQuery( '.sa_shipping_addresses_container' ).find( '.ship_to_this_address_button' ).show();
									jQuery( '.sa_shipping_addresses_container' ).find( '.shipping_to_this_address' ).hide();

									// Add the tick icon to the selected address.
									jQuery( '#shipping_address_container_' + addressID ).find( '.ship_to_this_address_button' ).hide();
									jQuery( '#shipping_address_container_' + addressID ).find( '.shipping_to_this_address' ).show();
									jQuery( '.shipping_address_selected' ).prepend( '<span class="dashicons dashicons-yes"></span>' );
								}

								// Hide the shipping form.
								if ( 'yes' == hideCheckoutForm ) {
									jQuery( '.woocommerce-shipping-fields' ).find( '.shipping_address_form' ).hide();
								}

							}

						}
					},
				}
			);
		}

	}
);

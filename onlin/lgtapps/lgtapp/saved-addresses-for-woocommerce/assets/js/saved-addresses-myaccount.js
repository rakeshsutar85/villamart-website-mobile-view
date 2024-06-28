/**
 * Saved Addresses For WooCommerce My Account JS
 *
 * @package saved-addresses-for-woocommerce/assets/js
 */

jQuery(
	function () {
		var ajax_url = save_addresses_myaccount_params.ajax_url;

		// Delete Billing Address.
		jQuery( 'div.account-billing-actions' ).on(
			'click',
			'#delete-billing',
			function(e) {
				e.preventDefault();

				if ( confirm( save_addresses_myaccount_params.confirm_delete_text ) ) {
					let deleteID = jQuery( this ).attr( 'data-delete-id' );

					jQuery.ajax(
						{
							type: 'POST',
							url: ajax_url,
							dataType: 'json',
							data: {
								action: 'delete_billing_address',
								delete_id: deleteID,
								security: save_addresses_myaccount_params.saw_delete_address
							},
							success: function( response ) {
								if ( response.deleted ) {
									// Refresh current page.
									location.reload();
								}
							},
						}
					);
				}
			}
		);

		// Update default Billing Address.
		jQuery( 'div.account-billing-actions' ).on(
			'click',
			'.not-is-default',
			function(e) {
				e.preventDefault();

				let defaultID = jQuery( this ).attr( 'data-default-id' );

				jQuery.ajax(
					{
						type: 'POST',
						url: ajax_url,
						dataType: 'json',
						data: {
							action: 'default_billing_address',
							default_id: defaultID,
							security: save_addresses_myaccount_params.saw_default_address
						},
						success: function( response ) {
							if ( response.default ) {
								if ( jQuery( this ).not( '#billing_address_' + response.default_id ) ) {
									jQuery( ".account-billing-actions" ).find( 'span a' ).removeClass( 'is-default' ).addClass( 'not-is-default' );
									jQuery( ".account-billing-actions" ).find( 'span a' ).prop( 'title', save_addresses_myaccount_params.set_default_address );
									jQuery( ".account-billing-actions" ).find( 'span a' ).text( save_addresses_myaccount_params.set_default );
								}
								jQuery( '#billing_address_' + response.default_id ).find( 'span a' ).removeClass( 'not-is-default' ).addClass( 'is-default' );
								jQuery( '#billing_address_' + response.default_id ).find( 'span a' ).prop( 'title', save_addresses_myaccount_params.default_address );
								jQuery( '#billing_address_' + response.default_id ).find( 'span a' ).text( save_addresses_myaccount_params.default );
							}
						},
					}
				);
			}
		);

		// Delete Shipping Address.
		jQuery( 'div.account-shipping-actions' ).on(
			'click',
			'#delete-shipping',
			function(e) {
				e.preventDefault();

				if ( confirm( save_addresses_myaccount_params.confirm_delete_text ) ) {
					let deleteID = jQuery( this ).attr( 'data-delete-id' );

					jQuery.ajax(
						{
							type: 'POST',
							url: ajax_url,
							dataType: 'json',
							data: {
								action: 'delete_shipping_address',
								delete_id: deleteID,
								security: save_addresses_myaccount_params.saw_delete_address
							},
							success: function( response ) {
								if ( response.deleted ) {
									// Refresh current page.
									location.reload();
								}
							},
						}
					);
				}
			}
		);

		// Update default Shipping Address.
		jQuery( 'div.account-shipping-actions' ).on(
			'click',
			'.not-is-default',
			function(e) {
				e.preventDefault();

				let defaultID = jQuery( this ).attr( 'data-default-id' );

				jQuery.ajax(
					{
						type: 'POST',
						url: ajax_url,
						dataType: 'json',
						data: {
							action: 'default_shipping_address',
							default_id: defaultID,
							security: save_addresses_myaccount_params.saw_default_address
						},
						success: function( response ) {
							if ( response.default ) {
								if ( jQuery( this ).not( '#shipping_address_' + response.default_id ) ) {
									jQuery( ".account-shipping-actions" ).find( 'span a' ).removeClass( 'is-default' ).addClass( 'not-is-default' );
									jQuery( ".account-shipping-actions" ).find( 'span a' ).prop( 'title', save_addresses_myaccount_params.set_default_address );
									jQuery( ".account-shipping-actions" ).find( 'span a' ).text( save_addresses_myaccount_params.set_default );
								}
								jQuery( '#shipping_address_' + response.default_id ).find( 'span a' ).removeClass( 'not-is-default' ).addClass( 'is-default' );
								jQuery( '#shipping_address_' + response.default_id ).find( 'span a' ).prop( 'title', save_addresses_myaccount_params.default_address );
								jQuery( '#shipping_address_' + response.default_id ).find( 'span a' ).text( save_addresses_myaccount_params.default );
							}
						},
					}
				);
			}
		);

	}
);

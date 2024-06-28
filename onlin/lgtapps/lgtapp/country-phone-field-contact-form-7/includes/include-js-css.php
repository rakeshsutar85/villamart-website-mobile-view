<?php

/* Include all js and css files for active theme */
function nb_cpf_embedCssJs() {

    wp_enqueue_style( 'nbcpf-intlTelInput-style', NB_CPF_URL . 'assets/css/intlTelInput.min.css' );
	wp_enqueue_style( 'nbcpf-countryFlag-style', NB_CPF_URL . 'assets/css/countrySelect.min.css' );
	wp_enqueue_script( 'nbcpf-intlTelInput-script', NB_CPF_URL . 'assets/js/intlTelInput.min.js', array( 'jquery' ), false, true );
	wp_enqueue_script( 'nbcpf-countryFlag-script', NB_CPF_URL . 'assets/js/countrySelect.min.js', array( 'jquery' ), false, true );

	wp_localize_script( 'nbcpf-countryFlag-script', 'nbcpf', array(
		'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX
	) );
	
	$nb_cpf_settings_options = get_option( 'nb_cpf_options' );
	$IPaddress  =   $_SERVER['REMOTE_ADDR'];
	
	if(isset( $nb_cpf_settings_options['defaultCountry'] ) && $nb_cpf_settings_options['defaultCountry'] !=''){
		$defaultCountry = 'defaultCountry: "'.strtolower( $nb_cpf_settings_options['defaultCountry'] ).'",';
		
	} else {
		$defaultCountry = '';
		
	}
	if(isset( $nb_cpf_settings_options['onlyCountries'] ) && $nb_cpf_settings_options['onlyCountries'] !=''){
		$onlyCountries = 'onlyCountries: '.json_encode(explode(',',$nb_cpf_settings_options['onlyCountries'])).',';
	}else{
		$onlyCountries = '';
	}
	if(isset( $nb_cpf_settings_options['preferredCountries'] ) && $nb_cpf_settings_options['preferredCountries'] !=''){
		$preferredCountries = 'preferredCountries: '.json_encode(explode(',',$nb_cpf_settings_options['preferredCountries'])).',';
	}else{
		$preferredCountries = '';
	}
	if(isset( $nb_cpf_settings_options['excludeCountries'] ) && $nb_cpf_settings_options['excludeCountries'] !=''){
		$excludeCountries = 'excludeCountries: '.json_encode(explode(',',$nb_cpf_settings_options['excludeCountries'])).',';
	}else{
		$excludeCountries = '';
	}
	
	// phone field settings

	if(isset( $nb_cpf_settings_options['phone_defaultCountry'] ) && $nb_cpf_settings_options['phone_defaultCountry'] !=''){
		$phone_defaultCountry = 'initialCountry: "'.strtolower( $nb_cpf_settings_options['phone_defaultCountry'] ).'",';
	} else {
		$phone_defaultCountry = '';
		
	}
	if(isset( $nb_cpf_settings_options['phone_onlyCountries'] ) && $nb_cpf_settings_options['phone_onlyCountries'] !=''){
		$phone_onlyCountries = 'onlyCountries: '.json_encode(explode(',',$nb_cpf_settings_options['phone_onlyCountries'])).',';
	}else{
		$phone_onlyCountries = '';
	}
	if(isset( $nb_cpf_settings_options['phone_preferredCountries'] ) && $nb_cpf_settings_options['phone_preferredCountries'] !=''){
		$phone_preferredCountries = 'preferredCountries: '.json_encode(explode(',',$nb_cpf_settings_options['phone_preferredCountries'])).',';
	}else{
		$phone_preferredCountries = '';
	}
	if(isset( $nb_cpf_settings_options['phone_excludeCountries'] ) && $nb_cpf_settings_options['phone_excludeCountries'] !=''){
		$phone_excludeCountries = 'excludeCountries: '.json_encode(explode(',',$nb_cpf_settings_options['phone_excludeCountries'])).',';
	}else{
		$phone_excludeCountries = '';
	}
	
	if(isset($nb_cpf_settings_options['phone_nationalMode']) && $nb_cpf_settings_options['phone_nationalMode'] == 1){
		$phone_nationalMode = 'true';
	}else {
		$phone_nationalMode = 'false';
	}
	
	$custom_inline_js = '';
	
	if(isset($phone_defaultCountry) && $phone_defaultCountry == ''){
		$custom_inline_js .= '';
	}

	if( ( isset( $nb_cpf_settings_options['country_auto_select'] ) && $nb_cpf_settings_options['country_auto_select'] == 1 ) || ( isset( $nb_cpf_settings_options['phone_auto_select'] ) && $nb_cpf_settings_options['phone_auto_select'] == 1 ) ){
		$custom_inline_js .= '
		(function($) {
			$(function() {

				function render_country_flags(){

					$(".wpcf7-countrytext").countrySelect({
						'.$defaultCountry.''.$onlyCountries.''.$preferredCountries.''.$excludeCountries.'
					});
					$(".wpcf7-phonetext").intlTelInput({
						autoHideDialCode: false,
						autoPlaceholder: "off",
						nationalMode: '.$phone_nationalMode.',
						separateDialCode: false,
						hiddenInput: "full_number",
						'.$phone_defaultCountry.''.$phone_onlyCountries.''.$phone_preferredCountries.''.$phone_excludeCountries.'	
					});
	
					$(".wpcf7-phonetext").each(function () {
						var hiddenInput = $(this).attr(\'name\');
						//console.log(hiddenInput);
						$("input[name="+hiddenInput+"-country-code]").val($(this).val());
					});
					
					$(".wpcf7-phonetext").on("countrychange", function() {
						// do something with iti.getSelectedCountryData()
						//console.log(this.value);
						var hiddenInput = $(this).attr("name");
						$("input[name="+hiddenInput+"-country-code]").val(this.value);
						
					});';
	
					if(! isset($nb_cpf_settings_options['phone_nationalMode']) || isset($nb_cpf_settings_options['phone_nationalMode']) && $nb_cpf_settings_options['phone_nationalMode'] != 1){
	
						$custom_inline_js .= '$(".wpcf7-phonetext").on("keyup", function() {
							var dial_code = $(this).siblings(".flag-container").find(".country-list li.active span.dial-code").text();
							if(dial_code == "")
							var dial_code = $(this).siblings(".flag-container").find(".country-list li.highlight span.dial-code").text();
							var value   = $(this).val();
							console.log(dial_code, value);
							$(this).val(dial_code + value.substring(dial_code.length));
						 });';
	
					}
	
					$custom_inline_js .= '$(".wpcf7-countrytext").on("keyup", function() {
						var country_name = $(this).siblings(".flag-dropdown").find(".country-list li.active span.country-name").text();
						if(country_name == "")
						var country_name = $(this).siblings(".flag-dropdown").find(".country-list li.highlight span.country-name").text();
						
						var value   = $(this).val();
						//console.log(country_name, value);
						$(this).val(country_name + value.substring(country_name.length));
					});
				}

				var ip_address = "";

				jQuery.ajax({
					//url: "https://ipwho.is/",
					url: "https://reallyfreegeoip.org/json/",
					success: function(response){
						
						//console.log(response);
						//var location = JSON.parse(response);
						console.log(response.country_code);
						if( response.country_code !== undefined){
							//console.log("here");
							$(".wpcf7-countrytext").countrySelect({';
							
							$custom_inline_js .= isset( $nb_cpf_settings_options['country_auto_select'] ) 
							&& $nb_cpf_settings_options['country_auto_select'] == 1 
							? 'defaultCountry: response.country_code.toLowerCase(),' : '';
							
							$custom_inline_js .= $onlyCountries.''.$preferredCountries.''.$excludeCountries.'
							});
							$(".wpcf7-phonetext").intlTelInput({
								autoHideDialCode: false,
								autoPlaceholder: "off",
								nationalMode: '.$phone_nationalMode.',
								separateDialCode: false,
								hiddenInput: "full_number",';
							$custom_inline_js .= isset( $nb_cpf_settings_options['phone_auto_select'] ) 
							&& $nb_cpf_settings_options['phone_auto_select'] == 1 ?
								'initialCountry: response.country_code.toLowerCase(),' : '';
							$custom_inline_js .= $phone_onlyCountries.''.$phone_preferredCountries.''.$phone_excludeCountries.'	
							});
							
							$(".wpcf7-phonetext").each(function () {
								var hiddenInput = $(this).attr(\'name\');
								//console.log(hiddenInput);
								$("input[name="+hiddenInput+"-country-code]").val($(this).val());
							});
							
							$(".wpcf7-phonetext").on("countrychange", function() {
								// do something with iti.getSelectedCountryData()
								//console.log(this.value);
								var hiddenInput = $(this).attr("name");
								$("input[name="+hiddenInput+"-country-code]").val(this.value);
								
							});';

							if(! isset($nb_cpf_settings_options['phone_nationalMode']) || isset($nb_cpf_settings_options['phone_nationalMode']) && $nb_cpf_settings_options['phone_nationalMode'] != 1){

								$custom_inline_js .= '$(".wpcf7-phonetext").on("keyup", function() {
									var dial_code = $(this).siblings(".flag-container").find(".country-list li.active span.dial-code").text();
									if(dial_code == "")
									var dial_code = $(this).siblings(".flag-container").find(".country-list li.highlight span.dial-code").text();
									var value   = $(this).val();
									console.log(dial_code, value);
									$(this).val(dial_code + value.substring(dial_code.length));
								});';

							}
			
							$custom_inline_js .= '$(".wpcf7-countrytext").on("keyup", function() {
								var country_name = $(this).siblings(".flag-dropdown").find(".country-list li.active span.country-name").text();
								if(country_name == "")
								var country_name = $(this).siblings(".flag-dropdown").find(".country-list li.highlight span.country-name").text();
								
								var value   = $(this).val();
								//console.log(country_name, value);
								$(this).val(country_name + value.substring(country_name.length));
							});

						} else {

							render_country_flags();

						}

					},
					error: function(){
						render_country_flags();
					}
				});
			});
		})(jQuery);';

	}else{ 

		$custom_inline_js .= '
		(function($) {
			$(function() {
				$(".wpcf7-countrytext").countrySelect({
					'.$defaultCountry.''.$onlyCountries.''.$preferredCountries.''.$excludeCountries.'
				});
				$(".wpcf7-phonetext").intlTelInput({
					autoHideDialCode: false,
					autoPlaceholder: "off",
					nationalMode: '.$phone_nationalMode.',
					separateDialCode: false,
					hiddenInput: "full_number",
					'.$phone_defaultCountry.''.$phone_onlyCountries.''.$phone_preferredCountries.''.$phone_excludeCountries.'	
				});

				$(".wpcf7-phonetext").each(function () {
					var hiddenInput = $(this).attr(\'name\');
					//console.log(hiddenInput);
					$("input[name="+hiddenInput+"-country-code]").val($(this).val());
				});
				
				$(".wpcf7-phonetext").on("countrychange", function() {
					// do something with iti.getSelectedCountryData()
					//console.log(this.value);
					var hiddenInput = $(this).attr("name");
					$("input[name="+hiddenInput+"-country-code]").val(this.value);
					
				});';

				if(! isset($nb_cpf_settings_options['phone_nationalMode']) || isset($nb_cpf_settings_options['phone_nationalMode']) && $nb_cpf_settings_options['phone_nationalMode'] != 1){

					$custom_inline_js .= '$(".wpcf7-phonetext").on("keyup", function() {
						var dial_code = $(this).siblings(".flag-container").find(".country-list li.active span.dial-code").text();
						if(dial_code == "")
						var dial_code = $(this).siblings(".flag-container").find(".country-list li.highlight span.dial-code").text();
						var value   = $(this).val();
						console.log(dial_code, value);
						$(this).val(dial_code + value.substring(dial_code.length));
					 });';

				}

				$custom_inline_js .= '$(".wpcf7-countrytext").on("keyup", function() {
					var country_name = $(this).siblings(".flag-dropdown").find(".country-list li.active span.country-name").text();
					if(country_name == "")
					var country_name = $(this).siblings(".flag-dropdown").find(".country-list li.highlight span.country-name").text();
					
					var value   = $(this).val();
					//console.log(country_name, value);
					$(this).val(country_name + value.substring(country_name.length));
				});
				
			});
		})(jQuery);';
	
	}
	
	
	wp_add_inline_script('nbcpf-countryFlag-script',$custom_inline_js );
    
}

add_action( 'wp_enqueue_scripts', 'nb_cpf_embedCssJs' );


add_action('wp_ajax_nopriv_auto_country_detection', 'nb_cpf_autoCountryDetection');
add_action('wp_ajax_auto_country_detection', 'nb_cpf_autoCountryDetection' );

function nb_cpf_autoCountryDetection(){

	$nb_cpf_settings_options = get_option( 'nb_cpf_options' );

	//$api_key = isset($nb_cpf_settings_options['ip_api_key']) && $nb_cpf_settings_options['ip_api_key'] != '' ? $nb_cpf_settings_options['ip_api_key'] : '3abce2be42d640a8a98e82806e32cd4f';
	//$api_key = '3abce2be42d640a8a98e82806e32cd4f';
	//$api_url = "https://api.ipgeolocation.io/ipgeo?apiKey=".$api_key.'&fields=country_code2,country_name';

	$ip_address = $_REQUEST['ip'];
	if($ip_address != ''){
		$api_url = 'https://ipwho.is/'.$ip_address;
		$response = wp_safe_remote_get(
			$api_url,
			array(
				'timeout' => 3,
			)
		);
		//print_r($response);
		$response = wp_remote_retrieve_body( $response );

		
		if ( is_wp_error( $response ) ) {
				
			return false; //$error_message = $response->get_error_message();

		} else {
			
			$parse_json = json_decode($response, true);
			//print_r($parse_json);
			echo json_encode($parse_json);
			//$api_data = json_decode( $response['body'], true );
		}

		
	} else {
		return false;
	}

	wp_die();
	

}
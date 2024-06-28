<?php

/*************************************************
## Scripts
*************************************************/
function bacola_gdpr_scripts() {
	wp_register_style( 'klb-gdpr',   plugins_url( 'css/gdpr.css', __FILE__ ), false, '1.0');
	wp_register_script( 'klb-gdpr',  plugins_url( 'js/gdpr.js', __FILE__ ), true );

}
add_action( 'wp_enqueue_scripts', 'bacola_gdpr_scripts' );

/*************************************************
## Bacola GDPR COOKIE
*************************************************/ 
function bacola_gdpr_cookie(){	
	$gdpr  = isset( $_COOKIE['cookie-popup-visible'] ) ? $_COOKIE['cookie-popup-visible'] : 'enable';
	if($gdpr){
		return $gdpr;
	}
}


/*************************************************
## Bacola GDPR WP_Footer
*************************************************/ 

add_action('wp_footer', 'bacola_gdpr_filter'); 
function bacola_gdpr_filter() { 

	if(get_theme_mod('bacola_gdpr_toggle',0) == 1 && bacola_gdpr_cookie() == 'enable'){
		wp_enqueue_script('jquery-cookie');
		wp_enqueue_script('klb-gdpr');
		wp_enqueue_style('klb-gdpr');
		?>
		
		<div class="site-gdpr mobile-menu-active" data-expires="<?php echo esc_attr(get_theme_mod('bacola_gdpr_expire_date')); ?>">
			<div class="gdpr-inner">
				<div class="gdpr-icon">
					<?php if(get_theme_mod('bacola_gdpr_image')){ ?>
						<img src="<?php echo esc_url( wp_get_attachment_url(get_theme_mod( 'bacola_gdpr_image' )) ); ?>">
					<?php } else { ?>
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M491.864 287.228a78.51 78.51 0 01-11.413.837c-35.705 0-65.922-23.357-76.286-55.617-1.772-5.514-6.276-9.76-11.966-10.844-59.295-11.289-104.133-63.345-104.133-125.926 0-26.284 7.934-50.698 21.506-71.032 3.227-4.835.534-11.275-5.168-12.404a248.977 248.977 0 00-48.403-4.74C118.759 7.502 7.503 118.758 7.503 256c0 137.241 111.256 248.498 248.498 248.498 123.689 0 225.965-90.034 245.202-208.318.874-5.368-3.959-9.742-9.339-8.952z" fill="#f5b97d"/><g fill="#cd916e"><circle cx="143.779" cy="175.84" r="32.06"/><circle cx="159.808" cy="336.159" r="24.05"/></g><g fill="#f5b97d"><path d="M359.227 72.475l-2.3 13.799c-1.89 11.341 4.512 22.434 15.277 26.471l20.361 7.635c13.449 5.043 28.291-2.75 31.774-16.685l7.257-29.03c4.523-18.093-12.377-34.226-30.241-28.867l-25.319 7.596a24.052 24.052 0 00-16.809 19.081zM440.371 159.808l-9.681 4.841c-7.593 3.796-10.91 12.853-7.566 20.655l7.789 18.173c4.716 11.003 19.389 13.144 27.052 3.948l12.53-15.036c5.875-7.05 4.645-17.583-2.697-23.089l-10.637-7.978a16.032 16.032 0 00-16.79-1.514z"/><circle cx="488.466" cy="111.71" r="16.03"/></g><g fill="#e3aa75"><path d="M286.062 474.438c-137.242 0-248.498-111.256-248.498-248.498 0-60.968 21.991-116.775 58.428-160.011C41.9 111.513 7.504 179.726 7.504 256c0 137.241 111.256 248.498 248.498 248.498 76.322 0 144.459-34.31 189.997-88.355-43.189 36.374-98.947 58.295-159.937 58.295zM396.131 101.776c-13.446-5.042-21.443-18.897-19.082-33.063l2.741-16.444-3.752 1.126a24.05 24.05 0 00-16.811 19.081l-2.3 13.799c-1.89 11.341 4.512 22.434 15.277 26.471l20.361 7.635c10.798 4.049 22.392-.262 28.386-9.297l-24.82-9.308zM448.017 193.147l-8.821-20.583c-1.657-3.866-1.795-7.982-.791-11.774l-7.715 3.857c-7.592 3.796-10.91 12.853-7.566 20.655l7.789 18.173c4.716 11.003 19.389 13.145 27.052 3.948l3.114-3.737c-5.51-1.123-10.548-4.671-13.062-10.539z"/></g><g fill="#b67f5f"><path d="M160.811 190.87c-17.709 0-32.064-14.356-32.064-32.064 0-4.435.902-8.659 2.53-12.5-11.498 4.873-19.564 16.261-19.564 29.534 0 17.708 14.356 32.064 32.064 32.064 13.274 0 24.662-8.067 29.534-19.564a31.963 31.963 0 01-12.5 2.53zM172.334 347.685c-13.282 0-24.048-10.767-24.048-24.048 0-3.392.719-6.61 1.986-9.537-8.532 3.694-14.511 12.173-14.511 22.062 0 13.282 10.767 24.048 24.048 24.048 9.89 0 18.368-5.979 22.062-14.511a23.937 23.937 0 01-9.537 1.986z"/></g><circle cx="312.117" cy="360.208" r="32.06" fill="#cd916e"/><path d="M329.148 375.239c-17.709 0-32.064-14.356-32.064-32.064 0-4.435.902-8.659 2.53-12.5-11.498 4.873-19.564 16.261-19.564 29.534 0 17.708 14.356 32.064 32.064 32.064 13.274 0 24.662-8.067 29.534-19.564a31.955 31.955 0 01-12.5 2.53z" fill="#b67f5f"/><circle cx="247.648" cy="215.92" r="16.03" fill="#cd916e"/><path d="M143.777 136.275c-21.816 0-39.564 17.749-39.564 39.564s17.749 39.564 39.564 39.564c21.816 0 39.565-17.749 39.565-39.564s-17.75-39.564-39.565-39.564zm0 64.129c-13.545 0-24.564-11.02-24.564-24.564s11.02-24.564 24.564-24.564 24.565 11.02 24.565 24.564-11.021 24.564-24.565 24.564zM272.549 360.21c0 21.816 17.749 39.564 39.564 39.564s39.564-17.749 39.564-39.564-17.749-39.564-39.564-39.564-39.564 17.748-39.564 39.564zm39.565-24.565c13.545 0 24.564 11.02 24.564 24.564s-11.02 24.564-24.564 24.564-24.564-11.02-24.564-24.564 11.019-24.564 24.564-24.564zM159.808 304.613c-17.396 0-31.548 14.153-31.548 31.549s14.152 31.548 31.548 31.548 31.549-14.152 31.549-31.548-14.153-31.549-31.549-31.549zm0 48.097c-9.125 0-16.548-7.423-16.548-16.548 0-9.125 7.423-16.549 16.548-16.549s16.549 7.424 16.549 16.549-7.424 16.548-16.549 16.548zM224.454 215.92c0 12.976 10.557 23.532 23.532 23.532s23.532-10.557 23.532-23.532-10.557-23.532-23.532-23.532-23.532 10.557-23.532 23.532zm23.532-8.532c4.705 0 8.532 3.828 8.532 8.532 0 4.704-3.828 8.532-8.532 8.532-4.704 0-8.532-3.828-8.532-8.532 0-4.704 3.827-8.532 8.532-8.532zM400.297 335.647a7.5 7.5 0 006.702-10.856l-8.016-16.033a7.498 7.498 0 00-10.062-3.354 7.499 7.499 0 00-3.354 10.062l8.016 16.033a7.5 7.5 0 006.714 4.148zM312.12 287.55a7.474 7.474 0 003.348-.793l16.032-8.016a7.499 7.499 0 003.354-10.062 7.498 7.498 0 00-10.062-3.354l-16.032 8.016a7.499 7.499 0 00-3.354 10.062 7.499 7.499 0 006.714 4.147zM88.972 267.37a7.499 7.499 0 0010.062 3.354 7.499 7.499 0 003.354-10.062l-8.016-16.032a7.498 7.498 0 00-10.062-3.354 7.499 7.499 0 00-3.354 10.062l8.016 16.032zM212.568 393.581l-16.032 8.016a7.499 7.499 0 00-3.354 10.062 7.499 7.499 0 0010.062 3.354l16.032-8.016a7.499 7.499 0 003.354-10.062 7.499 7.499 0 00-10.062-3.354zM221.225 90.376l-8.016-8.017a7.5 7.5 0 00-10.606 0 7.5 7.5 0 000 10.606l8.017 8.017a7.474 7.474 0 005.303 2.197 7.5 7.5 0 005.302-12.803zM186.57 266.729a7.5 7.5 0 000 10.606l8.016 8.016c1.464 1.464 3.384 2.197 5.303 2.197s3.839-.732 5.303-2.197a7.5 7.5 0 000-10.606l-8.016-8.016a7.5 7.5 0 00-10.606 0zM280.566 440.37v8.016a7.5 7.5 0 0015 0v-8.016a7.5 7.5 0 00-15 0zM245.273 149.079l8.016-8.016a7.5 7.5 0 000-10.606 7.5 7.5 0 00-10.606 0l-8.016 8.016a7.5 7.5 0 005.303 12.803 7.478 7.478 0 005.303-2.197zM369.571 119.766l20.361 7.636a31.527 31.527 0 0011.072 2.006 31.555 31.555 0 0014.672-3.614 31.579 31.579 0 0015.939-20.28l7.257-29.03c2.787-11.147-.511-22.538-8.822-30.472-8.312-7.935-19.844-10.7-30.85-7.398l-25.318 7.596c-11.435 3.43-20.092 13.255-22.054 25.031l-2.3 13.799c-2.472 14.84 5.957 29.444 20.043 34.726zm-5.246-32.259l2.3-13.799c1.029-6.177 5.57-11.331 11.568-13.13l25.318-7.596a16.81 16.81 0 014.833-.716c4.17 0 8.2 1.591 11.349 4.597 4.359 4.161 6.089 10.136 4.628 15.983l-7.257 29.03c-1.16 4.638-4.129 8.416-8.361 10.638-4.232 2.222-9.027 2.522-13.504.844l-20.361-7.636c-7.389-2.771-11.81-10.431-10.513-18.215zM427.336 157.94c-11.129 5.564-16.007 18.881-11.106 30.318l7.789 18.173c3.251 7.584 10.066 12.824 18.231 14.016a24.02 24.02 0 003.464.252c6.926 0 13.479-3.035 18.012-8.473l12.53-15.036c4.17-5.005 6.051-11.325 5.295-17.795-.756-6.47-4.042-12.187-9.254-16.095l-10.637-7.978c-7.176-5.383-16.619-6.235-24.644-2.222l-9.68 4.84zm16.39 8.576a8.533 8.533 0 013.824-.909c1.806 0 3.597.58 5.11 1.714l10.637 7.978a8.45 8.45 0 013.355 5.836 8.459 8.459 0 01-1.92 6.452l-12.53 15.036c-1.944 2.333-4.783 3.419-7.787 2.98-3.005-.438-5.414-2.291-6.61-5.082l-7.789-18.173a8.541 8.541 0 014.027-10.993l9.683-4.839c-.001 0-.001 0 0 0zM488.468 135.243c12.976 0 23.532-10.557 23.532-23.532S501.443 88.18 488.468 88.18s-23.532 10.557-23.532 23.532 10.556 23.531 23.532 23.531zm0-32.063c4.705 0 8.532 3.828 8.532 8.532 0 4.704-3.828 8.532-8.532 8.532-4.704 0-8.532-3.828-8.532-8.532 0-4.705 3.827-8.532 8.532-8.532z"/><path d="M490.775 279.807a71.404 71.404 0 01-10.323.757c-31.672 0-59.458-20.258-69.146-50.412-2.649-8.243-9.433-14.342-17.704-15.917-56.806-10.815-98.036-60.676-98.036-118.558 0-23.902 7-47.026 20.245-66.87 2.905-4.353 3.45-9.817 1.458-14.617-1.996-4.809-6.261-8.288-11.408-9.307-25.516-5.053-51.697-6.19-77.811-3.377-52.26 5.627-100.969 27.182-140.863 62.338a7.5 7.5 0 109.917 11.253c37.556-33.095 83.391-53.385 132.551-58.676 24.608-2.65 49.267-1.58 73.292 3.178.175.035.368.103.468.343.094.227.017.394-.081.54-14.895 22.318-22.768 48.321-22.768 75.196 0 65.075 46.359 121.133 110.23 133.293 2.874.547 5.261 2.758 6.229 5.77 11.688 36.38 45.215 60.823 83.427 60.823 4.153 0 8.359-.309 12.502-.917.553-.082.835.245.847.328-7.23 44.46-26.873 85.965-56.805 120.03a7.5 7.5 0 0011.269 9.902c31.793-36.184 52.659-80.28 60.342-127.523.781-4.804-.735-9.554-4.162-13.034-3.529-3.584-8.639-5.282-13.67-4.543zM415.01 437.005a240.605 240.605 0 01-159.009 59.993c-33.757 0-66.405-6.84-97.038-20.332-29.596-13.034-55.911-31.618-78.212-55.235-22.266-23.579-39.282-50.858-50.576-81.08-11.744-31.428-16.711-64.588-14.764-98.559 3.035-52.954 24.24-104.336 59.708-144.683a7.499 7.499 0 00-.681-10.584 7.498 7.498 0 00-10.584.681C26.184 130.056 3.662 184.65.436 240.934c-2.067 36.063 3.211 71.278 15.689 104.668 11.999 32.108 30.073 61.086 53.721 86.127 23.685 25.082 51.635 44.819 83.072 58.665 32.55 14.335 67.232 21.603 103.083 21.603a255.606 255.606 0 00168.917-63.731 7.5 7.5 0 10-9.908-11.261z"/></svg>
					<?php } ?>
				</div><!-- gdpr-icon -->
				<div class="gdpr-text"><?php echo bacola_sanitize_data(get_theme_mod('bacola_gdpr_text')); ?></div>
				<div class="gdpr-button">
					<a href="#" class="button"><?php echo esc_html(get_theme_mod('bacola_gdpr_button_text')); ?></a>
				</div><!-- gdpr-button -->
			</div><!-- gdpr-inner -->
		</div><!-- site-gdpr -->
		<?php
	}
}
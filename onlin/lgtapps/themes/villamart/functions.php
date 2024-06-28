<?php
/**
 * functions.php
 * @package WordPress
 * @subpackage Bacola
 * @since Bacola 1.2.6
 * 
 */

/*************************************************
## Admin style and scripts  
*************************************************/ 
update_option( 'envato_purchase_code_32552148', '*******' );
update_option( '_license_key_status', 'valid' );
function bacola_admin_styles() {
	wp_enqueue_style('bacola-klbtheme',   get_template_directory_uri() .'/assets/css/admin/klbtheme.css');
	wp_enqueue_script('bacola-init', 	  get_template_directory_uri() .'/assets/js/init.js', array('jquery','media-upload','thickbox'));
    wp_enqueue_script('bacola-register',  get_template_directory_uri() .'/assets/js/admin/register.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'bacola_admin_styles');

 /*************************************************
## Bacola Fonts
*************************************************/
function bacola_fonts_url_inter() {
	$fonts_url = '';

	$inter = _x( 'on', 'Inter font: on or off', 'bacola' );		

	if ( 'off' !== $inter ) {
		$font_families = array();

		if ( 'off' !== $inter ) {
		$font_families[] = 'Inter:wght@100;200;300;400;500;600;700;800;900';
		}
		
		$query_args = array( 
		'family' => rawurldecode( implode( '|', $font_families ) ), 
		'subset' => rawurldecode( 'latin,latin-ext' ), 
		); 
		 
		$fonts_url = add_query_arg( $query_args, '//fonts.googleapis.com/css2' );
	}
 
	return esc_url_raw( $fonts_url );
}

function bacola_fonts_url_dosis() {
	$fonts_url = '';

	$dosis = _x( 'on', 'Dosis font: on or off', 'bacola' );	

	if ( 'off' !== $dosis ) {
		$font_families = array();

		if ( 'off' !== $dosis ) {
		$font_families[] = 'Dosis:wght@200;300;400;500;600;700;800';
		}
		
		$query_args = array( 
		'family' => rawurldecode( implode( '|', $font_families ) ), 
		'subset' => rawurldecode( 'latin,latin-ext' ), 
		); 
		 
		$fonts_url = add_query_arg( $query_args, '//fonts.googleapis.com/css2' );
	}
 
	return esc_url_raw( $fonts_url );
}

/*************************************************
## Styles and Scripts
*************************************************/ 
define('BACOLA_INDEX_CSS', 	  get_template_directory_uri()  . '/assets/css');
define('BACOLA_INDEX_JS', 	  get_template_directory_uri()  . '/assets/js');
define('BACOLA_INDEX_FONTS',    get_template_directory_uri()  . '/assets/fonts');

function bacola_scripts() {

	if ( is_admin_bar_showing() ) {
		wp_enqueue_style( 'bacola-klbtheme', BACOLA_INDEX_CSS . '/admin/klbtheme.css', false, '1.0');    
	}	

	if ( is_singular() ) wp_enqueue_script( 'comment-reply' );

	wp_enqueue_style( 'bootstrap', 				BACOLA_INDEX_CSS . '/bootstrap.min.css', false, '1.0');
	wp_enqueue_style( 'select2', 				BACOLA_INDEX_CSS . '/select2.min.css', false, '1.0');
	wp_enqueue_style( 'bacola-base', 			BACOLA_INDEX_CSS . '/base.css', false, '1.0');
	wp_style_add_data( 'bacola-base', 'rtl', 'replace' );
	wp_enqueue_style( 'bacola-font-dmsans',  	bacola_fonts_url_inter(), array(), null );
	wp_enqueue_style( 'bacola-font-crimson',  	bacola_fonts_url_dosis(), array(), null );
	wp_enqueue_style( 'bacola-style',         	get_stylesheet_uri() );
	wp_style_add_data( 'bacola-style', 'rtl', 'replace' );

	$mapkey = get_theme_mod('bacola_mapapi');

	wp_enqueue_script( 'imagesloaded');
	wp_enqueue_script( 'bootstrap-bundle',    	 BACOLA_INDEX_JS . '/bootstrap.bundle.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script( 'select2-full',    	 	 BACOLA_INDEX_JS . '/select2.full.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script( 'gsap',    	    		 BACOLA_INDEX_JS . '/vendor/gsap.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script( 'jquery-magnific-popup',  BACOLA_INDEX_JS . '/vendor/jquery.magnific-popup.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script( 'perfect-scrolllbar',     BACOLA_INDEX_JS . '/vendor/perfect-scrollbar.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script( 'slick',    	    	 	 BACOLA_INDEX_JS . '/vendor/slick.min.js', array('jquery'), '1.0', true);
	wp_register_script( 'bacola-googlemap',    '//maps.googleapis.com/maps/api/js?key='. $mapkey .'', array('jquery'), '1.0', true);
	wp_register_script( 'bacola-counter',   	 BACOLA_INDEX_JS . '/custom/counter.js', array('jquery'), '1.0', true);
	wp_register_script( 'bacola-loginform',   	 BACOLA_INDEX_JS . '/custom/loginform.js', array('jquery'), '1.0', true);
	wp_enqueue_script( 'bacola-sidebarfilter',   BACOLA_INDEX_JS . '/custom/sidebarfilter.js', array('jquery'), '1.0', true);
	wp_enqueue_script( 'bacola-productsorting',  BACOLA_INDEX_JS . '/custom/productSorting.js', array('jquery'), '1.0', true);
	wp_enqueue_script( 'bacola-producthover',    BACOLA_INDEX_JS . '/custom/productHover.js', array('jquery'), '1.0', true);
	wp_enqueue_script( 'bacola-cartquantity',    BACOLA_INDEX_JS . '/custom/cartquantity.js', array('jquery'), '1.0', true);
	wp_enqueue_script( 'bacola-sitescroll',      BACOLA_INDEX_JS . '/custom/sitescroll.js', array('jquery'), '1.0', true);
	wp_enqueue_script( 'bacola-bundle',     	 BACOLA_INDEX_JS . '/bundle.js', array('jquery'), '1.0', true);

}
add_action( 'wp_enqueue_scripts', 'bacola_scripts' );

/*************************************************
## Theme Setup
*************************************************/ 

if ( ! isset( $content_width ) ) $content_width = 960;

function bacola_theme_setup() {
	
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-background' );
	add_theme_support( 'post-formats', array('gallery', 'audio', 'video'));
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
	add_theme_support( 'woocommerce', array('gallery_thumbnail_image_width' => 99,'thumbnail_image_width' => 90,) );
	load_theme_textdomain( 'bacola', get_template_directory() . '/languages' );
	remove_theme_support( 'widgets-block-editor' );

}
add_action( 'after_setup_theme', 'bacola_theme_setup' );


/*************************************************
## Include the TGM_Plugin_Activation class.
*************************************************/ 

require_once get_template_directory() . '/includes/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'bacola_register_required_plugins' );

function bacola_register_required_plugins() {

	$url = 'http://klbtheme.com/bacola/plugins/';
	$mainurl = 'http://klbtheme.com/plugins/';

	$plugins = array(
		
        array(
            'name'                  => esc_html__('Meta Box','bacola'),
            'slug'                  => 'meta-box',
        ),

        array(
            'name'                  => esc_html__('Contact Form 7','bacola'),
            'slug'                  => 'contact-form-7',
        ),
		
		array(
            'name'                  => esc_html__('WooCommerce Wishlist','bacola'),
            'slug'                  => 'ti-woocommerce-wishlist',
        ),
		
		array(
            'name'                  => esc_html__('WooCommerce Compare','bacola'),
            'slug'                  => 'woo-smart-compare',
        ),
		
        array(
            'name'                  => esc_html__('Kirki','bacola'),
            'slug'                  => 'kirki',
        ),
		
		array(
            'name'                  => esc_html__('MailChimp Subscribe','bacola'),
            'slug'                  => 'mailchimp-for-wp',
        ),
		
        array(
            'name'                  => esc_html__('Elementor','bacola'),
            'slug'                  => 'elementor',
            'required'              => true,
        ),
		
        array(
            'name'                  => esc_html__('WooCommerce','bacola'),
            'slug'                  => 'woocommerce',
            'required'              => true,
        ),

        array(
            'name'                  => esc_html__('Bacola Core','bacola'),
            'slug'                  => 'bacola-core',
            'source'                => $url . 'bacola-core.zip',
            'required'              => true,
            'version'               => '1.2.7',
            'force_activation'      => false,
            'force_deactivation'    => false,
            'external_url'          => '',
        ),

        array(
            'name'                  => esc_html__('Envato Market','bacola'),
            'slug'                  => 'envato-market',
            'source'                => $mainurl . 'envato-market.zip',
            'required'              => true,
            'version'               => '2.0.7',
            'force_activation'      => false,
            'force_deactivation'    => false,
            'external_url'          => '',
        ),


	);

	$config = array(
		'id'           => 'bacola',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'parent_slug'  => 'themes.php',            // Parent menu slug.
		'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
	);

	tgmpa( $plugins, $config );
}

/*************************************************
## Bacola Register Menu 
*************************************************/

function bacola_register_menus() {
	register_nav_menus( array( 'main-menu' 	   => esc_html__('Primary Navigation Menu','bacola')) );

	if(get_theme_mod('bacola_footer_menu',0) == '1'){
		register_nav_menus( array( 'footer-menu'     => esc_html__('Footer Menu','bacola')) );
	}
	
	$topheader = get_theme_mod('bacola_top_header','0');
	$sidebarmenu = get_theme_mod('bacola_header_sidebar','0');

	if($sidebarmenu == '1'){
		register_nav_menus( array( 'sidebar-menu'     => esc_html__('Sidebar Menu','bacola')) );
	}
	
	if($topheader == '1'){
		register_nav_menus( array( 'canvas-bottom' 	   => esc_html__('Canvas Bottom','bacola')) );
		register_nav_menus( array( 'top-right-menu'    => esc_html__('Top Right Menu','bacola')) );
		register_nav_menus( array( 'top-left-menu'     => esc_html__('Top Left Menu','bacola')) );
	}
}
add_action('init', 'bacola_register_menus');

/*************************************************
## Bacola Main Menu
*************************************************/ 
class bacola_main_walker extends Walker_Nav_Menu {
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		// depth dependent classes
		$indent = ( $depth > 0  ? str_repeat( "\t", $depth ) : '' ); // code indent
		$display_depth = ( $depth + 1); // because it counts the first submenu as 0
		$classes = array(
			'',
			( $display_depth % 2  ? '' : '' ),
			( $display_depth >=2 ? '' : '' ),
			
			);
		$class_names = implode( ' ', $classes );
	  
		// build html
		$output .= "\n" . $indent . '<ul class="sub-menu">' . "\n";
	}

    function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ){
        $id_field = $this->db_fields['id'];
        if ( is_object( $args[0] ) ) {
            $args[0]->has_children = ! empty( $children_elements[$element->$id_field] );
        }
        return parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }
      function start_el(&$output, $object, $depth = 0, $args = Array() , $current_object_id = 0) {
           
           global $wp_query;

           $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

           $class_names = $value = '';
		   
		   $classes = empty( $object->classes ) ? array() : (array) $object->classes;
           $icon_class = $classes[0];
		   $classes = array_slice($classes,1);
		   
		   $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $object ) );
		   
		   if ( $args->has_children ) {
		   $class_names = 'class="dropdown '.esc_attr($icon_class).' '. esc_attr( $class_names ) . '"';
		   } else {
		   $class_names = 'class=" '. esc_attr( $class_names ) . '"';
		   }
			
			$output .= $indent . '<li ' . $value . $class_names .'>';

			$datahover = str_replace(' ','',$object->title);


			$attributes = ! empty( $object->url ) ? ' href="'   . esc_attr( $object->url ) .'"' : '';

				
			$object_output = $args->before;

			$object_output .= '<a'. $attributes .'  >';
			if($icon_class && $icon_class != 'mega-menu'){
			$object_output .= '<i class="'.esc_attr($icon_class).'"></i> ';
			}
			$object_output .= $args->link_before .  apply_filters( 'the_title', $object->title, $object->ID ) . '';
	        $object_output .= $args->link_after;
			$object_output .= '</a>';


			$object_output .= $args->after;

			$output .= apply_filters( 'walker_nav_menu_start_el', $object_output, $object, $depth, $args );            	              	
      }
}

/*************************************************
## Bacola Sidebar Menu
*************************************************/ 
class bacola_sidebar_walker extends Walker_Nav_Menu {
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		// depth dependent classes
		$indent = ( $depth > 0  ? str_repeat( "\t", $depth ) : '' ); // code indent
		$display_depth = ( $depth + 1); // because it counts the first submenu as 0
		$classes = array(
			'',
			( $display_depth % 2  ? '' : '' ),
			( $display_depth >=2 ? '' : '' ),
			
			);
		$class_names = implode( ' ', $classes );
	  
		// build html
		$output .= "\n" . $indent . '<ul class="sub-menu">' . "\n";
	}

    function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ){
        $id_field = $this->db_fields['id'];
        if ( is_object( $args[0] ) ) {
            $args[0]->has_children = ! empty( $children_elements[$element->$id_field] );
        }
        return parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }
      function start_el(&$output, $object, $depth = 0, $args = Array() , $current_object_id = 0) {
           
           global $wp_query;

           $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

           $class_names = $value = '';
		   
		   $classes = empty( $object->classes ) ? array() : (array) $object->classes;
		   $myclasses = empty( $object->classes ) ? array() : (array) $object->classes;
           $icon_class = $classes[0];
		   $classes = array_slice($classes,1);
		   
		 
		   
		   $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $object ) );
		   
		   if ( $args->has_children ) {
		   $class_names = 'class="category-parent parent  '. esc_attr( $class_names ) . '"';
		   }elseif(in_array('bottom',$myclasses)){
		   $class_names = 'class="link-parent  '. esc_attr( $class_names ) . '"';   
		   } else {
		   $class_names = 'class="category-parent  '. esc_attr( $class_names ) . '"';
		   }
			
			$output .= $indent . '<li ' . $value . $class_names .'>';

			$datahover = str_replace(' ','',$object->title);


			$attributes = ! empty( $object->url ) ? ' href="'   . esc_attr( $object->url ) .'"' : '';

				
			$object_output = $args->before;

			$object_output .= '<a'. $attributes .'  >';
			if($icon_class){
			$object_output .= '<i class="'.esc_attr($icon_class).'"></i> ';
			}
			$object_output .= $args->link_before .  apply_filters( 'the_title', $object->title, $object->ID ) . '';
	        $object_output .= $args->link_after;
			$object_output .= '</a>';


			$object_output .= $args->after;

			$output .= apply_filters( 'walker_nav_menu_start_el', $object_output, $object, $depth, $args );            	              	
      }
}

/*************************************************
## Excerpt More
*************************************************/ 

function bacola_excerpt_more($more) {
  global $post;
  return '<div class="klb-readmore entry-button"><a class="button" href="'. esc_url(get_permalink($post->ID)) . '">' . esc_html__('Read More', 'bacola') . '</a></div>';
  }
 add_filter('excerpt_more', 'bacola_excerpt_more');
 
/*************************************************
## Word Limiter
*************************************************/ 
function bacola_limit_words($string, $limit) {
	$words = explode(' ', $string);
	return implode(' ', array_slice($words, 0, $limit));
}

/*************************************************
## Widgets
*************************************************/ 

function bacola_widgets_init() {
	register_sidebar( array(
	  'name' => esc_html__( 'Blog Sidebar', 'bacola' ),
	  'id' => 'blog-sidebar',
	  'description'   => esc_html__( 'These are widgets for the Blog page.','bacola' ),
	  'before_widget' => '<div class="widget %2$s">',
	  'after_widget'  => '</div>',
	  'before_title'  => '<h4 class="widget-title">',
	  'after_title'   => '</h4>'
	) );

	register_sidebar( array(
	  'name' => esc_html__( 'Shop Sidebar', 'bacola' ),
	  'id' => 'shop-sidebar',
	  'description'   => esc_html__( 'These are widgets for the Shop.','bacola' ),
	  'before_widget' => '<div class="widget %2$s">',
	  'after_widget'  => '</div>',
	  'before_title'  => '<h4 class="widget-title">',
	  'after_title'   => '</h4>'
	) );

	register_sidebar( array(
	  'name' => esc_html__( 'Footer First Column', 'bacola' ),
	  'id' => 'footer-1',
	  'description'   => esc_html__( 'These are widgets for the Footer.','bacola' ),
	  'before_widget' => '<div class="klbfooterwidget widget %2$s">',
	  'after_widget'  => '</div>',
	  'before_title'  => '<h4 class="widget-title">',
	  'after_title'   => '</h4>'
	) );

	register_sidebar( array(
	  'name' => esc_html__( 'Footer Second Column', 'bacola' ),
	  'id' => 'footer-2',
	  'description'   => esc_html__( 'These are widgets for the Footer.','bacola' ),
	  'before_widget' => '<div class="klbfooterwidget widget %2$s">',
	  'after_widget'  => '</div>',
	  'before_title'  => '<h4 class="widget-title">',
	  'after_title'   => '</h4>'
	) );

	register_sidebar( array(
	  'name' => esc_html__( 'Footer Third Column', 'bacola' ),
	  'id' => 'footer-3',
	  'description'   => esc_html__( 'These are widgets for the Footer.','bacola' ),
	  'before_widget' => '<div class="klbfooterwidget widget %2$s">',
	  'after_widget'  => '</div>',
	  'before_title'  => '<h4 class="widget-title">',
	  'after_title'   => '</h4>'
	) );

	register_sidebar( array(
	  'name' => esc_html__( 'Footer Fourth Column', 'bacola' ),
	  'id' => 'footer-4',
	  'description'   => esc_html__( 'These are widgets for the Footer.','bacola' ),
	  'before_widget' => '<div class="klbfooterwidget widget %2$s">',
	  'after_widget'  => '</div>',
	  'before_title'  => '<h4 class="widget-title">',
	  'after_title'   => '</h4>'
	) );

	register_sidebar( array(
	  'name' => esc_html__( 'Footer Fifth Column', 'bacola' ),
	  'id' => 'footer-5',
	  'description'   => esc_html__( 'These are widgets for the Footer.','bacola' ),
	  'before_widget' => '<div class="klbfooterwidget widget %2$s">',
	  'after_widget'  => '</div>',
	  'before_title'  => '<h4 class="widget-title">',
	  'after_title'   => '</h4>'
	) );

	register_sidebar( array(
	  'name' => esc_html__( 'Footer Sixth Column', 'bacola' ),
	  'id' => 'footer-6',
	  'description'   => esc_html__( 'These are widgets for the Footer.','bacola' ),
	  'before_widget' => '<div class="klbfooterwidget widget %2$s">',
	  'after_widget'  => '</div>',
	  'before_title'  => '<h4 class="widget-title">',
	  'after_title'   => '</h4>'
	) );
}
add_action( 'widgets_init', 'bacola_widgets_init' );
 
/*************************************************
## Bacola Comment
*************************************************/

if ( ! function_exists( 'bacola_comment' ) ) :
 function bacola_comment( $comment, $args, $depth ) {
  $GLOBALS['comment'] = $comment;
  switch ( $comment->comment_type ) :
   case 'pingback' :
   case 'trackback' :
  ?>

   <article class="post pingback">
   <p><?php esc_html_e( 'Pingback:', 'bacola' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( esc_html__( '(Edit)', 'bacola' ), ' ' ); ?></p>
  <?php
    break;
   default :
  ?>
  
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
		<div id="div-comment-<?php comment_ID(); ?>" class="comment-body">
			<div class="comment-avatar">
				<div class="comment-author vcard">
					<img src="<?php echo get_avatar_url( $comment, 90 ); ?>" alt="<?php comment_author(); ?>" class="avatar">
				</div>
			</div>
			<div class="comment-content">
				<div class="comment-meta">
					<b class="fn"><a class="url"><?php comment_author(); ?></a></b>
					<div class="comment-metadata">
						<time><?php comment_date(); ?></time>
					</div>
				</div>
				<div class="klb-post">
					<?php comment_text(); ?>
					<?php if ( $comment->comment_approved == '0' ) : ?>
					<em><?php esc_html_e( 'Your comment is awaiting moderation.', 'bacola' ); ?></em>
					<?php endif; ?>
				</div>
				<div class="reply">
					<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
				</div>
			</div>

		</div>
	</li>


  <?php
    break;
  endswitch;
 }
endif;

/*************************************************
## Bacola Widget Count Filter
 *************************************************/

function bacola_cat_count_span($links) {
  $links = str_replace('</a> (', '</a> <span class="catcount">(', $links);
  $links = str_replace(')', ')</span>', $links);
  return bacola_sanitize_data($links);
}
add_filter('wp_list_categories', 'bacola_cat_count_span');
 
function bacola_archive_count_span( $links ) {
	$links = str_replace( '</a>&nbsp;(', '</a><span class="catcount">(', $links );
	$links = str_replace( ')', ')</span>', $links );
	return bacola_sanitize_data($links);
}
add_filter( 'get_archives_link', 'bacola_archive_count_span' );


/*************************************************
## Pingback url auto-discovery header for single posts, pages, or attachments
 *************************************************/
function bacola_pingback_header() {
	if ( is_singular() && pings_open() ) {
		echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
	}
}
add_action( 'wp_head', 'bacola_pingback_header' );

/************************************************************
## DATA CONTROL FROM PAGE METABOX OR ELEMENTOR PAGE SETTINGS
*************************************************************/
function bacola_page_settings( $opt_id){
	
	if ( class_exists( '\Elementor\Core\Settings\Manager' ) ) {
		// Get the current post id
		$post_id = get_the_ID();

		// Get the page settings manager
		$page_settings_manager = \Elementor\Core\Settings\Manager::get_settings_managers( 'page' );

		// Get the settings model for current post
		$page_settings_model = $page_settings_manager->get_model( $post_id );

		// Retrieve the color we added before
		$output = $page_settings_model->get_settings( 'bacola_elementor_'.$opt_id );
		
		return $output;
	}
}

/************************************************************
## Elementor Register Location
*************************************************************/
function bacola_register_elementor_locations( $elementor_theme_manager ) {

    $elementor_theme_manager->register_location( 'header' );
    $elementor_theme_manager->register_location( 'footer' );
    $elementor_theme_manager->register_location( 'single' );
	$elementor_theme_manager->register_location( 'archive' );

}
add_action( 'elementor/theme/register_locations', 'bacola_register_elementor_locations' );


/************************************************************
## Elementor Get Templates
*************************************************************/
function bacola_get_elementor_template($template_id){
	if($template_id){
	    $frontend = new \Elementor\Frontend;
	    printf( '<div class="bacola-elementor-template template-'.esc_attr($template_id).'">%1$s</div>', $frontend->get_builder_content_for_display( $template_id, true ) );

	    if ( class_exists( '\Elementor\Plugin' ) ) {
	        $elementor = \Elementor\Plugin::instance();
	        $elementor->frontend->enqueue_styles();
			$elementor->frontend->enqueue_scripts();
	    }
	
	    if ( class_exists( '\ElementorPro\Plugin' ) ) {
	        $elementor_pro = \ElementorPro\Plugin::instance();
	        $elementor_pro->enqueue_styles();
	    }

	}
}
add_action( 'bacola_before_main_shop', 'bacola_get_elementor_template', 10);
add_action( 'bacola_after_main_shop', 'bacola_get_elementor_template', 10);
add_action( 'bacola_before_main_footer', 'bacola_get_elementor_template', 10);
add_action( 'bacola_after_main_footer', 'bacola_get_elementor_template', 10);
add_action( 'bacola_before_main_header', 'bacola_get_elementor_template', 10);
add_action( 'bacola_after_main_header', 'bacola_get_elementor_template', 10);


/************************************************************
## Do Action for Templates and Product Categories
*************************************************************/
function bacola_do_action($hook){
	
	if ( !class_exists( 'woocommerce' ) ) {
		return;
	}

	$categorytemplate = get_theme_mod('bacola_elementor_template_each_shop_category');
	if(is_product_category()){
		if($categorytemplate && array_search(get_queried_object()->term_id, array_column($categorytemplate, 'category_id')) !== false){
			foreach($categorytemplate as $c){
				if($c['category_id'] == get_queried_object()->term_id){
					do_action( $hook, $c[$hook.'_elementor_template_category']);
				}
			}
		} else {
			do_action( $hook, get_theme_mod($hook.'_elementor_template'));
		}
	} else {
		do_action( $hook, get_theme_mod($hook.'_elementor_template'));
	}
	
}

/*************************************************
## Bacola Get Image
*************************************************/
function bacola_get_image($image){
	$app_image = ! wp_attachment_is_image($image) ? $image : wp_get_attachment_url($image);
	
	return esc_html($app_image);
}

/*************************************************
## Bacola Get options
*************************************************/
function bacola_get_option(){	
	$getopt  = isset( $_GET['opt'] ) ? $_GET['opt'] : '';

	return esc_html($getopt);
}

/*************************************************
## Bacola Theme options
*************************************************/

	require_once get_template_directory() . '/includes/metaboxes.php';
	require_once get_template_directory() . '/includes/woocommerce.php';
	require_once get_template_directory() . '/includes/woocommerce-filter.php';
	require_once get_template_directory() . '/includes/sanitize.php';
	require_once get_template_directory() . '/includes/merlin/theme-register.php';
	require_once get_template_directory() . '/includes/merlin/setup-wizard.php';
	require_once get_template_directory() . '/includes/pjax/filter-functions.php';
	require_once get_template_directory() . '/includes/header/main_header.php';
	require_once get_template_directory() . '/includes/footer/main_footer.php';

//################ Disable VC Front-end Editor
//vc_disable_frontend();

/*##########################################################################################################################*/
/*CUSTOM CODE STARTS*/
/*##########################################################################################################################*/


//###################### Enqueue your own stylesheet
function wp_enqueue_woocommerce_style(){
	wp_register_style( 'ideation', get_template_directory_uri() . '/css/woocommerce.css' );
	
	if ( class_exists( 'woocommerce' ) ) {
		wp_enqueue_style( 'ideation' );
	}
}
//add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
//add_action( 'wp_enqueue_scripts', 'wp_enqueue_woocommerce_style' );

//################### Rename My Account Tab Labels
//add_filter( 'woocommerce_account_menu_items', 'lgt_rename_address_my_account', 999 );
function lgt_rename_address_my_account( $items ) {
$items['sumo-pp-my-payments'] = 'Installments';
$items['ideation-user-tickets-area'] = 'Support';
return $items;
}

//###################### Change WooCommerce Add To Cart Button Text
add_filter( 'woocommerce_product_single_add_to_cart_text', 'cw_btntext_cart' );
add_filter( 'woocommerce_product_add_to_cart_text', 'cw_btntext_cart' );
function cw_btntext_cart() {
    return __( 'ADD TO CART', 'woocommerce' );
}

//######################  Limit purchases to one product per order 
add_filter( 'woocommerce_add_to_cart_validation', 'wc_limit_one_per_order', 10, 2 );
function wc_limit_one_per_order( $passed_validation, $product_id ) {
	if ( 31 !== $product_id ) {
		return $passed_validation;
	}

	if ( WC()->cart->get_cart_contents_count() >= 1 ) {
		wc_add_notice( __( 'This product cannot be purchased with other products. Please, empty your cart first and then add it again.', 'woocommerce' ), 'error' );
		return false;
	}

	return $passed_validation;
}
//###################### Hide quantity using CSS
add_action( 'wp_head', 'hide_quantity_using_css' );
function hide_quantity_using_css() {
    if ( is_product() ) {
 ?>
    <style type="text/css">.quantity, .buttons_added { width:0; height:0; display: none; visibility: hidden; }</style>
    <?php
    }
}
//################### ADDING EXTRA STATE STARTS 
add_filter( 'woocommerce_states', 'indian_woocommerce_states' );

function indian_woocommerce_states( $states ) {

$states['IN'] = array(
'AP' => __( 'Andhra Pradesh', 'woocommerce' ),
'AR' => __( 'Arunachal Pradesh', 'woocommerce' ),
'AS' => __( 'Assam', 'woocommerce' ),
'BR' => __( 'Bihar', 'woocommerce' ),
'CT' => __( 'Chhattisgarh', 'woocommerce' ),
'GA' => __( 'Goa', 'woocommerce' ),
'GJ' => __( 'Gujarat', 'woocommerce' ),
'HR' => __( 'Haryana', 'woocommerce' ),
'HP' => __( 'Himachal Pradesh', 'woocommerce' ),
'JK' => __( 'Jammu and Kashmir', 'woocommerce' ),
'JH' => __( 'Jharkhand', 'woocommerce' ),
'KA' => __( 'Karnataka', 'woocommerce' ),
'KL' => __( 'Kerala', 'woocommerce' ),
'LA' => __( 'Ladakh', 'woocommerce' ),
'MP' => __( 'Madhya Pradesh', 'woocommerce' ),
'MH' => __( 'Maharashtra', 'woocommerce' ),
'MN' => __( 'Manipur', 'woocommerce' ),
'ML' => __( 'Meghalaya', 'woocommerce' ),
'MZ' => __( 'Mizoram', 'woocommerce' ),
'NL' => __( 'Nagaland', 'woocommerce' ),
'OD' => __( 'Odisha', 'woocommerce' ),
'PB' => __( 'Punjab', 'woocommerce' ),
'RJ' => __( 'Rajasthan', 'woocommerce' ),
'SK' => __( 'Sikkim', 'woocommerce' ),
'TN' => __( 'Tamil Nadu', 'woocommerce' ),
'TS' => __( 'Telangana', 'woocommerce' ),
'TR' => __( 'Tripura', 'woocommerce' ),
'UK' => __( 'Uttarakhand', 'woocommerce' ),
'UP' => __( 'Uttar Pradesh', 'woocommerce' ),
'WB' => __( 'West Bengal', 'woocommerce' ),
'AN' => __( 'Andaman and Nicobar Islands', 'woocommerce' ),
'CH' => __( 'Chandigarh', 'woocommerce' ),
'DN' => __( 'Dadra and Nagar Haveli', 'woocommerce' ),
'DD' => __( 'Daman and Diu', 'woocommerce' ),
'DL' => __( 'Delhi', 'woocommerce' ),
'LD' => __( 'Lakshadeep', 'woocommerce' ),
'PY' => __( 'Pondicherry (Puducherry)', 'woocommerce' ),
 );
  return $states;
} 
//##############################################################

//######################  Change Default Country & State
//add_filter( 'default_checkout_billing_country', 'change_default_checkout_country' );
//add_filter( 'default_checkout_billing_state', 'change_default_checkout_state' );
function change_default_checkout_country() {
  return 'IN'; // country code
}
function change_default_checkout_state() {
  return 'OD'; // state code
}
//###################### Change Tax name in cart page
add_filter( 'woocommerce_countries_inc_tax_or_vat', function () {
  return __( '(incl. GST)', 'woocommerce' );
});
add_filter( 'woocommerce_countries_ex_tax_or_vat', function () {
  return __( '(ex. GST)', 'woocommerce' );
});
//###################### Change Variation reset link text from clear-->Reset
add_action( 'woocommerce_reset_variations_link' , 'sd_change_clear_text', 15 );
function sd_change_clear_text() {
   echo '<a class="reset_variations" href="#">' . esc_html__( 'Reset', 'woocommerce' ) . '</a>';
 }

//######################  Rename Country & State Label Text in Checkout Page
add_filter( 'woocommerce_default_address_fields' , 'lgt_rename_country', 9999 );
add_filter( 'woocommerce_default_address_fields' , 'lgt_rename_state', 9999 );
function lgt_rename_country( $fields ) {
    $fields['country']['label'] = 'Country';
    return $fields;
}
function lgt_rename_state( $fields ) {
    $fields['state']['label'] = 'State';
    return $fields;
}
//###################### Change checkout field priority
add_filter( 'woocommerce_default_address_fields', 'custom_override_default_locale_fields' );
function custom_override_default_locale_fields( $fields ) {
    $fields['state']['priority'] = 50;
    $fields['address_1']['priority'] = 60;
    $fields['address_2']['priority'] = 70;
    return $fields;
}
//####################################################################
// ########### Add info above the proceed to checkout button
add_action('woocommerce_proceed_to_checkout', 'lgt_custom_checkout_text');
function lgt_custom_checkout_text() {
    //echo '<p><small class="lgt_cart_info">All prices mentioned above are exclusive of GST.<br>GST will be added at the time of final payment.</small></p>';
}

//###################### Remove payment method from emails
add_filter( 'woocommerce_get_order_item_totals', 'custom_woocommerce_get_order_item_totals' );
function custom_woocommerce_get_order_item_totals( $totals ) {
  unset( $totals['payment_method'] );
  return $totals;
}

/*** Force WooCommerce terms and conditions link to open in a new page when clicked on the checkout page */
function golden_oak_web_design_woocommerce_checkout_terms_and_conditions() {
  remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30 );
}
add_action( 'wp', 'golden_oak_web_design_woocommerce_checkout_terms_and_conditions' );

/*################## CUSTOM SHIPPING MESSAGE STARTS###########*/
add_filter( 'woocommerce_no_shipping_available_html', 'my_custom_no_shipping_message' );
add_filter( 'woocommerce_cart_no_shipping_available_html', 'my_custom_no_shipping_message' );
function my_custom_no_shipping_message( $message ) {
	return __( '<strong>SORRY !!</strong> <br>Currently We are unable to deliver to your location. For more details kindly contact us.' );
}
add_filter(  'gettext',  'change_checkout_no_shipping_method_text', 10, 3 );
function change_checkout_no_shipping_method_text( $translated_text, $text, $domain ) {
    if ( is_checkout() && ! is_wc_endpoint_url() ) {
        $original_text = 'No shipping method has been selected. Please double check your address, or contact us if you need any help.';
        $new_text      = '<strong>SORRY !!</strong> <br>Currently We are unable to deliver to your location. For more details kindly contact us.';
        
        if ( $text === $original_text ) {
            $translated_text = $new_text;
        }
    }
    return $translated_text;
}
/*################## CUSTOM SHIPPING MESSAGE ENDS ###########*/

/*#### ADD MULTIPLE PRODUCTS AT A TIME ###### */
function webroom_add_multiple_products_to_cart( $url = false ) {
	// Make sure WC is installed, and add-to-cart qauery arg exists, and contains at least one comma.
	if ( ! class_exists( 'WC_Form_Handler' ) || empty( $_REQUEST['add-to-cart'] ) || false === strpos( $_REQUEST['add-to-cart'], ',' ) ) {
		return;
	}

	// Remove WooCommerce's hook, as it's useless (doesn't handle multiple products).
	remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );

	$product_ids = explode( ',', $_REQUEST['add-to-cart'] );
	$count       = count( $product_ids );
	$number      = 0;

	foreach ( $product_ids as $id_and_quantity ) {
		// Check for quantities defined in curie notation (<product_id>:<product_quantity>)
		
		$id_and_quantity = explode( ':', $id_and_quantity );
		$product_id = $id_and_quantity[0];

		$_REQUEST['quantity'] = ! empty( $id_and_quantity[1] ) ? absint( $id_and_quantity[1] ) : 1;

		if ( ++$number === $count ) {
			// Ok, final item, let's send it back to woocommerce's add_to_cart_action method for handling.
			$_REQUEST['add-to-cart'] = $product_id;

			return WC_Form_Handler::add_to_cart_action( $url );
		}

		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
		$was_added_to_cart = false;
		$adding_to_cart    = wc_get_product( $product_id );

		if ( ! $adding_to_cart ) {
			continue;
		}

		$add_to_cart_handler = apply_filters( 'woocommerce_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

		// Variable product handling
		if ( 'variable' === $add_to_cart_handler ) {
			woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_variable', $product_id );

		// Grouped Products
		} elseif ( 'grouped' === $add_to_cart_handler ) {
			woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_grouped', $product_id );

		// Custom Handler
		} elseif ( has_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler ) ){
			do_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler, $url );

		// Simple Products
		} else {
			woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_simple', $product_id );
		}
	}
}

// Fire before the WC_Form_Handler::add_to_cart_action callback.
add_action( 'wp_loaded', 'webroom_add_multiple_products_to_cart', 15 );


/**
 * Invoke class private method
 *
 * @since   0.1.0
 *
 * @param   string $class_name
 * @param   string $methodName
 *
 * @return  mixed
 */
function woo_hack_invoke_private_method( $class_name, $methodName ) {
	if ( version_compare( phpversion(), '5.3', '<' ) ) {
		throw new Exception( 'PHP version does not support ReflectionClass::setAccessible()', __LINE__ );
	}

	$args = func_get_args();
	unset( $args[0], $args[1] );
	$reflection = new ReflectionClass( $class_name );
	$method = $reflection->getMethod( $methodName );
	$method->setAccessible( true );

	//$args = array_merge( array( $class_name ), $args );
	$args = array_merge( array( $reflection ), $args );
	return call_user_func_array( array( $method, 'invoke' ), $args );
}
/*#### ADD MULTIPLE PRODUCTS AT A TIME ENDS ###### */

/*#### CHECKOUT TEXT CHANGE STARTS ###### */
// Alter WooCommerce Checkout Text
add_filter( 'gettext', function( $checkouttext ) {
    if ( 'Checkout' === $checkouttext ) {
        $checkouttext = 'BUY NOW';
    }
    return $checkouttext;
} );
add_filter( 'gettext', function( $translated_text ) {
    if ( 'Proceed to checkout' === $translated_text ) {
        $translated_text = 'Proceed to Buy Now';
    }
    return $translated_text;
} );
/*#### CHECKOUT TEXT CHANGE STARTS ###### */
/*#### CUSTOM CHECKOUT CUSTOMER ORDER NOTE #### */
/*
function custom_woocommerce_order_notes_placeholder( $placeholder ) {
	$placeholder['order']['order_comments']['placeholder']= 'Add your custom placeholder here...';
	$placeholder['order']['order_comments']['label']='Add your custom label';
    return $placeholder;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_woocommerce_order_notes_placeholder' );
*/
/*#### CUSTOM CHECKOUT CUSTOMER ORDER NOTE #### */

/*##########################################################################################################################*/
/*CUSTOM CODE ENDS*/
/*##########################################################################################################################*/

/*##########################################################################################################################*/
/*FOOTER CUSTOM CODE STARTS*/
/*##########################################################################################################################*/

/**/
//   Disable Autosave
function disableAutoSaveCompletely() {
 wp_deregister_script('autosave');
 }
 add_action( 'wp_print_scripts', 'disableAutoSaveCompletely' );
//1. Disable Plugin Update Notifications  -------------------------------------------------------------------
remove_action('load-update-core.php','wp_update_plugins');
add_filter('pre_site_transient_update_plugins','__return_null');
//2. Disable all the Nags & Notifications :
function remove_core_updates(){
global $wp_version;return(object) array('last_checked'=> time(),'version_checked'=> $wp_version,);
}
add_filter('pre_site_transient_update_core','remove_core_updates');
add_filter('pre_site_transient_update_plugins','remove_core_updates');
add_filter('pre_site_transient_update_themes','remove_core_updates');
//3. Hide the "Please update now" notification ------------------------------------------------------------
function hide_update_notice() {
   remove_action( 'admin_notices', 'update_nag', 3 );
}
add_action( 'admin_notices', 'hide_update_notice', 1 );

//4. Remove Query String from Static Resources -------------------------------------------------------------
function remove_cssjs_ver( $src ) {
 if( strpos( $src, '?ver=' ) )
 $src = remove_query_arg( 'ver', $src );
 return $src;
}
add_filter( 'style_loader_src', 'remove_cssjs_ver', 10, 2 );
add_filter( 'script_loader_src', 'remove_cssjs_ver', 10, 2 );
//5. Remore WP Brand ---------------------------------------------------------------------------------------
function change_footer_admin () {return '&nbsp;';}
add_filter('admin_footer_text', 'change_footer_admin', 9999);
function change_footer_version() {return ' ';}
add_filter( 'update_footer', 'change_footer_version', 9999);

remove_action('wp_head', 'wp_generator');
function wpbeginner_remove_version() {
return '';
}
add_filter('the_generator', 'wpbeginner_remove_version');
	
	function remove_admin_bar_links() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('wp-logo');          // Remove the WordPress logo
    $wp_admin_bar->remove_menu('about');            // Remove the about WordPress link
    $wp_admin_bar->remove_menu('wporg');            // Remove the WordPress.org link
    $wp_admin_bar->remove_menu('documentation');    // Remove the WordPress documentation link
    $wp_admin_bar->remove_menu('support-forums');   // Remove the support forums link
    $wp_admin_bar->remove_menu('feedback');         // Remove the feedback link
    $wp_admin_bar->remove_menu('site-name');        // Remove the site name menu
    $wp_admin_bar->remove_menu('view-site');        // Remove the view site link
    $wp_admin_bar->remove_menu('updates');          // Remove the updates link
    $wp_admin_bar->remove_menu('comments');         // Remove the comments link
    $wp_admin_bar->remove_menu('new-content');      // Remove the content link
    $wp_admin_bar->remove_menu('w3tc');             // If you use w3 total cache remove the performance link
    //$wp_admin_bar->remove_menu('my-account');       // Remove the user details tab
}
add_action( 'wp_before_admin_bar_render', 'remove_admin_bar_links' );

/*##########################################################################################################################*/
/*FOOTER CUSTOM CODE ENDS*/
/*##########################################################################################################################*/
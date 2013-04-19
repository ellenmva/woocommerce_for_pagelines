<?php
/*
	Plugin Name: WooCommerce for Pagelines
	Plugin URI: http://www.pagelines.ellenjanemoore.com/woocommerce
	Author: Ellen
	Author URI: http://www.pagelines.ellenjanemoore.com
	Description: Refines and configures the popular WooCommerce plugin for seamless integration into PageLines. 
	Demo: http://www.pagelines.ellenjanemoore.com/woocommerce
	PageLines: true
	Tags: extension
	Version: 1.02

	Thanks to Mike Jolly, http://mikejolley.com, for creating this plugin to build upon.
	

	Copyright: © 2009-2012 Mike Jolley, © 2013 Ellen Moore.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/**
 * Localisation
 **/
load_plugin_textdomain('wc_pagelines', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


/**
 * WC Pagelines Class
 **/
class WC_Pagelines {
	
	/**
	 * Construct
	 */
	function __construct() {


		$this->base_url = sprintf( '%s/%s', WP_PLUGIN_URL,  basename( dirname( __FILE__ ) ) );
		$this->base_dir = sprintf( '%s/%s', WP_PLUGIN_DIR,  basename( dirname( __FILE__ ) ) );
		$this->base_file = sprintf( '%s/%s/%s', WP_PLUGIN_DIR,  basename(dirname( __FILE__ )), basename( __FILE__ ));
		
		if ( is_admin() ) {

			add_filter( 'postsmeta_settings_array', array( &$this, 'woocommerce_meta' ) );
			add_filter( 'postmeta_settings_array', array( &$this, 'woocommerce_templates' ) );
		} 	
		add_filter( 'postmeta_settings_array', array( &$this, 'woocommerce_product_meta' ) );
    	add_action( 'woocommerce_admin_css', array( &$this,'my_deregister_styles' ));
		add_action('wp_head', array( &$this,'pagelines_woocommerce_scripts'));
		// add_action('wp_head', 'woocommerce_output_related_products');
		add_filter( 'the_sub_templates', array( &$this, 'woocommerce_the_sub_templates'), 10, 2 );
		add_filter( 'pagelines_sections_dirs', array( &$this, 'woocommerce_pagelines_sections_dirs') );	
		add_filter( 'pagelines_lesscode', array( &$this, 'get_less' ), 10, 1 );
		add_action( 'template_redirect', array( &$this, 'woocommerce_integration' ), 10, 1  );			
		add_filter( 'pless_vars', array(&$this,'pagelines_woocommerce_less_vars'));
		add_action( 'init', array(&$this, 'init') );
		}
	
	/**
	 * Init the integration
	 **/
	function init() {
		global $woocommerce;
		
		if ( ! class_exists( 'woocommerce' ) ) return;
		
		// Prevent woocommerce templates being loaded
		remove_filter( 'template_include', array( &$woocommerce, 'template_loader' ) );
		
		// Remove related products (we have them in a section)
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
		
		// Remove upsells (we have them in a section)
		remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display' , 15);
	
		//Remove Woocommerce Tabs and Add Pagelines Tabs
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
		add_action( 'woocommerce_after_single_product_summary', array( &$this,'new_woocommerce_tabs'), 10);
		add_theme_support( 'woocommerce' );
		
		
	}

	

	
	function my_deregister_styles() {
		// Remove style from Pagelines Meta Settings
		wp_deregister_style( 'jquery-ui-style');

	   
	}

	function pagelines_woocommerce_scripts() {
		
			?>
			<script>
			jQuery(document).ready(function(){
				// Add class to body for demo-message to fix positioning
				if(jQuery('p').hasClass('demo_store')) {
				jQuery("body").addClass("demo-message");
			}
				// Remove first and last classes from Related Products and Upsells
				jQuery(".related .products .product").removeClass("first").removeClass("last");
				jQuery(".upsells .products .product").removeClass("first").removeClass("last");
				// Add Active classes for Pagelines Wootabs
				jQuery("#woo-tabs li:first").addClass("active");
				jQuery(".tab-content .tab-pane:first").addClass("active");
				jQuery('#woo-tabs a').click(function (e) {
	  				e.preventDefault();
	  				jQuery(this).tab('show');
				})

			});
			jQuery(window).load(function(){
				// Remove Woocommerce button class and add Pagelines btn class
				jQuery(".woocommerce .button").addClass("btn").removeClass('button');
				jQuery("input").removeClass("checkout-button").removeClass("btn");
				jQuery(".widget_shopping_cart .button").addClass("btn").removeClass('button');
			
				});
			</script>
			<?php
		
		
	}

	
	/**
	 * Set default sections for products
	 **/
	function woocommerce_the_sub_templates( $map, $t ) {
	
		$map['product_archive']['sections'] = ( $t == 'main' ) ? array( 'PageLinesWCBreadcrumbs', 'PageLinesProductLoop', 'PageLinesProductPagination' ) : array( 'PageLinesProductContent' );
		
		$map['product']['sections'] = ( $t == 'main' ) ? array( 'PageLinesWCBreadcrumbs', 'PageLinesProductLoop', 'PageLinesShareBar', 'PageLinesRelatedProducts', 'PageLinesUpsells' ) : array( 'PageLinesProductContent' );
	
		return $map;
	}
	
	/**
	 * Make PageLines look for our custom sections
	 **/
	function woocommerce_pagelines_sections_dirs( $dirs ) {
	
		$dirs['woocommerce'] = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/';
		
		return $dirs;	
	}

	/**
	 *	Disable templates for product variations (which are not visible)
	 */	
	function woocommerce_templates( $dragdrop, $public_post_type, $area ) {
		
		if ( 'product_variation' == $public_post_type )
			return false;
			
		return $dragdrop;
	}

	/**
	 * Create Product Tabs using Pagelines Markup
	 **/
	function pagelines_woocommerce_tabs(){
		$tabs = apply_filters( 'woocommerce_product_tabs', array() );

	if ( ! empty( $tabs ) ) : 
	 
		?>

		<div class="woocommerce-tabs">
			<ul class="nav nav-tabs" id="woo-tabs">
				<?php foreach ( $tabs as $key => $tab ) : ?>
					<li class="<?php echo $key ?>_tab">
						<a href="#tab-<?php echo $key ?>"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', $tab['title'], $key ) ?></a>
					</li>

				<?php endforeach; ?>
			</ul>
			<div class="tab-content">
			<?php foreach ( $tabs as $key => $tab ) : ?>
				<div class="tab-pane" id="tab-<?php echo $key ?>">
					<?php call_user_func( $tab['callback'], $key, $tab ) ?>
				</div>

			<?php endforeach; ?>
		</div></div>

		<?php endif; ?>

	<?php 
	
	}

	function new_woocommerce_tabs(){
		$this->pagelines_woocommerce_tabs();
	}


	function woocommerce_product_meta($d) {
		 $meta = array(
            'woocommerce_templates' => array(
                'metapanel' => $metapanel_options->posts_metapanel( 'woocommerce_templates', 'woocommerce_templates' ),
                'icon'      => $this->base_url.'/icon.png'
            )
        );
        $d = array_merge($d, $meta);

        return $d;
	}
	
	/**
	 *	Add integration to store page
	 */
	function woocommerce_integration() {
		if ( ! $this->check() )
			return;
		if ( is_archive() )
			new PageLinesIntegration( 'product_archive' );
	}


	
	/**
	 *	Add tab to Special Meta
	 */
	function woocommerce_meta( $d ) {
		global $metapanel_options;

		$meta = array(
			'product_archive' => array(
				'metapanel' => $metapanel_options->posts_metapanel( 'product_archive', 'product_archive' ),
				'icon'		=> $this->base_url . '/icon.png'
			) 
		);
		
		$d = array_merge($d, $meta);

		return $d;
	}
	

	// Custom LESS Vars
	function pagelines_woocommerce_less_vars($less){
		global $woocommerce_settings;
		
		$colors = get_option( 'woocommerce_frontend_css_colors' );
				if ( empty( $colors['primary'] ) ) $colors['primary'] = '#ad74a2';
				if ( empty( $colors['secondary'] ) ) $colors['secondary'] = '#f7f6f7';
				if ( empty( $colors['highlight'] ) ) $colors['highlight'] = '#85ad74';
				if ( empty( $colors['content_bg'] ) ) $colors['content_bg'] = '#ffffff';
	            if ( empty( $colors['subtext'] ) ) $colors['subtext'] = '#777777';


		$less['woo-primary']  = $colors['primary'];
		$less['woo-secondary']  = $colors['secondary'];
		$less['woo-hightlight'] = $colors['highlight'];
		$less['woo-contentbg'] = $colors['content_bg'];
		$less['woo-subtext'] = $colors['subtext'];
		

		return $less;
	}

	// Get less files from sections
	function get_less( $less ){

		$less .= pl_file_get_contents( $this->base_dir.'/css/style.less' );
		$less .= pl_file_get_contents( sprintf( '%s/style.less', $this->base_dir ) );
		return $less;
		
	}
	 	
	/**
	 *	Check if we are in Woocommerce and PageLines Framework.
	 */		
	function check() {
			
		if ( ! function_exists( 'is_woocommerce' ) || ! function_exists( 'ploption' ) )
			return false;

		if ( ! is_woocommerce() )
			return false;
			
		return true;
	}
}

// Construct class and store globally for overrides
$GLOBALS['WC_Pagelines'] = new WC_Pagelines;
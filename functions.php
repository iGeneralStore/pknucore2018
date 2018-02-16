<?php

if ( ! defined( 'CASE27_THEME_DIR' ) ) {
	define( 'CASE27_THEME_DIR', get_template_directory() );
}

if ( ! defined( 'CASE27_INTEGRATIONS_DIR' ) ) {
	define( 'CASE27_INTEGRATIONS_DIR', CASE27_THEME_DIR . '/includes/integrations' );
}

if ( ! defined( 'CASE27_ASSETS_DIR' ) ) {
	define( 'CASE27_ASSETS_DIR', CASE27_THEME_DIR . '/assets' );
}

if ( ! defined( 'CASE27_ENV' ) ) {
	define( 'CASE27_ENV', 'production' );
}

if ( ! defined( 'CASE27_THEME_VERSION' ) ) {
	if (CASE27_ENV == 'dev') {
		define( 'CASE27_THEME_VERSION', rand(1, 99999) );
	} else {
		define( 'CASE27_THEME_VERSION', wp_get_theme( get_template() )->get('Version') );
	}
}

if ( ! defined( 'ELEMENTOR_PARTNER_ID' ) ) {
	define( 'ELEMENTOR_PARTNER_ID', 2124 );
}

// Load textdomain early to include strings that are localized before
// the 'after_setup_theme' is called.
load_theme_textdomain( 'my-listing', CASE27_THEME_DIR . '/languages' );

// Load classes.
require_once CASE27_THEME_DIR . '/includes/autoload.php';

// added by KH
add_filter ( 'woocommerce_account_menu_items', 'ss_one_more_link' );
function ss_one_more_link( $menu_links ){
   
    // we will hook "addendpoints" later
    $new = array( 'addendpoints' => '상품 페이지 추가하기' );
   
    // array_slice() is good when you want to add an element between the other ones
    $menu_links = array_slice( $menu_links, 0, 1, true ) 
    + $new
    + array_slice( $menu_links, 1, NULL, true );
   
   
    return $menu_links;
   
   
}
   
add_filter( 'woocommerce_get_endpoint_url', 'ss_hook_endpoint', 10, 4 );
function ss_hook_endpoint( $url, $endpoint, $value, $permalink ){
   
    if( $endpoint === 'addendpoints' ) {
        $url = get_site_url() . '/add-listing';
    }

    return $url;
   
}


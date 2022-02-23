<?php
/**
* Plugin Name:       TotallyCriticalCSS
* Description:       Totally fast and critical CSS
* Version:           1.0.0
* License:     GPL2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( is_admin() ) {
	// we are in admin mode
	add_action( 'admin_init', 'totallycriticalcss_styles' );
	add_action( 'admin_init', 'totallycriticalcss_scripts' );
	add_action( 'admin_menu', 'totallycriticalcss_plugin_admin_page' );
}

/**
* Register and enqueue admin stylesheet
*/
function totallycriticalcss_styles() {
	wp_register_style( 'totallycriticalcss_plugin_stylesheet', plugin_dir_url(__FILE__) . 'admin/css/admin.css' );
	wp_enqueue_style( 'totallycriticalcss_plugin_stylesheet' );
}

/**
* Register and enqueue admin scripts
*/
function totallycriticalcss_scripts() {
	wp_register_script( 'totallycriticalcss_script', plugin_dir_url(__FILE__) . 'admin/js/admin.js', array( 'jquery' ) );
	wp_localize_script( 'totallycriticalcss_script', 'totallycriticalcss_obj',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' )
		)
	);
	wp_enqueue_script( 'totallycriticalcss_script' );
}

/**
* Register metabox
*/
function totallycriticalcss_mb() {
	add_meta_box( 'totallycriticalcss_mb_id', __( 'TotallyCriticalCSS', 'cr_crit' ), 'totallycriticalcss_mb_callback', 'page', 'side', 'high' );
}
function totallycriticalcss_mb_callback( $meta_id  ) {
	$status = get_post_meta( $meta_id->ID, 'totallycriticalcss', true ) ? '<strong style="color: green; text-transform: uppeprcase;">Generated</strong>' : '<strong style="color: red; text-transform: uppeprcase;">Not Generated</strong>';
	$output = 'TotallyCriticalCSS is '. $status;
	echo $output;
}
add_action( 'add_meta_boxes', 'totallycriticalcss_mb' );


/**
* Register admin page and menu.
*/
function totallycriticalcss_plugin_admin_page() {
	add_menu_page(
		'Totally Critical CSS',
		'Totally Critical CSS',
		'manage_options',
		plugin_dir_path(__FILE__) . 'admin/view.php',
		null,
		plugin_dir_url(__FILE__) . 'admin/images/critical.png',
		100
	);
}

/**
* Init
*/
// function totallycriticalcss_init() {
// 	if ( get_option( 'totallycriticalcss_init' ) != 'true' ) {
// 		$posts = new WP_Query('post_type=page&posts_per_page=-1&post_status=publish');
// 		$posts = $posts->posts;
//
// 		//loop through the posts
// 		foreach( $posts as $post ):
// 			//totallycriticalcss($post->ID);
// 		endforeach;
//
// 		//update_option( 'totallycriticalcss_init', 'true' );
// 	}
//
// }
//add_action( 'admin_init', 'totallycriticalcss_init' );

/**
* Store Path
*/
function totallycriticalcss_custom_theme_path() {
	$theme_path = get_option( 'totallycriticalcss_custom_theme_location' ) ? get_option( 'totallycriticalcss_custom_theme_location' ) : get_stylesheet_directory_uri();

	return $theme_path;
}

/**
* Store Stylesheet
*/
function totallycriticalcss_stylesheet_path() {
	$stylesheet_path = get_option( 'totallycriticalcss_custom_stylesheet_location' ) ? get_option( 'totallycriticalcss_custom_stylesheet_location' ) : get_stylesheet_uri();

	return $stylesheet_path;
}

/**
* Update Form Data when submitted
*/
add_action( 'wp_ajax_nopriv_save_admin_page', 'save_admin_page' );
add_action( 'wp_ajax_save_admin_page', 'save_admin_page' );

function save_admin_page() {
	$api_key = $_POST[ 'api_key' ];
	update_option( 'totallycriticalcss_api_key', $api_key );

	$custom_theme = $_POST[ 'custom_theme' ];
	update_option( 'totallycriticalcss_custom_theme_location', $custom_theme );

	$custom_stylesheet = $_POST[ 'custom_stylesheet' ];
	update_option( 'totallycriticalcss_custom_stylesheet_location', $custom_stylesheet );
}

/**
* TotallyCriticalCSS Function
*/
function totallycriticalcss( $id ) {
	$cri = 'https://api.totallycriticalcss.com/v1/?';
	$url = get_permalink( $id );
	$pth = totallycriticalcss_custom_theme_path();
	$css = totallycriticalcss_stylesheet_path();
	$key = get_option( 'totallycriticalcss_api_key' ) ? get_option( 'totallycriticalcss_api_key' ) : 'beadf54f56063cc0cce7ded292b8e099';

	$in = file_get_contents( $cri . 'u=' . $url . '&c=' . $css . '&p=' . $pth . '&k=' . $key, false );

	if( $in ) {
		if ( ! add_post_meta( $id, 'totallycriticalcss', $in, true ) ) {
			update_post_meta( $id, 'totallycriticalcss', $in );
			totallycriticalcss_mb_callback( $id );
		}
	}
}

function inspect_styles() {
	global $wp_styles;
	print_r( $wp_styles->queue );
}
add_action( 'wp_print_styles', 'inspect_styles' );

/**
* On Post Save Function
*/
function totallycriticalcss_post_save( $post_id ) {
	totallycriticalcss( $post_id );
}
add_action( 'save_post', 'totallycriticalcss_post_save' );

// ENQUEUE STYLES & SCRIPTS
function scripts() {

	$totallyCiriticalCSS = get_post_meta( get_the_ID(), 'totallycriticalcss', true );
	$style_name = get_post_field( 'post_name', get_the_ID() );
	// $dequeued_styles = get_option( 'style',  );
	//
	// get_stylesheet()
	//
	// wp_dequeue_style( $handle );
	// wp_deregister_style( $handle );

	if( $totallyCiriticalCSS ) {
		echo '<!-- TotallyCriticalCSS --><style>' . $totallyCiriticalCSS . '</style><!-- /TotallyCriticalCSS -->';
		add_action( 'get_footer', function() {
			wp_enqueue_style( $style_name . '-style', totallycriticalcss_stylesheet_path(), false, null, 'all' );
		} );
	} else {
		wp_enqueue_style( $style_name . '-style', totallycriticalcss_stylesheet_path(), false, null, 'all' );
	}

}
add_action( 'wp_enqueue_scripts', 'scripts' );

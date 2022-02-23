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
	wp_register_script( 'totallycriticalcss_script', plugin_dir_url(__FILE__) . 'admin/js/admin.js', array('jquery') );
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
	add_meta_box( 'totallycriticalcss_mb', __( 'TotallyCriticalCSS', 'cr_crit' ), 'totallycriticalcss_mb_callback', 'page', 'side', 'high' );
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
* Store Stylesheet
*/
function totallycriticalcss_stylesheet_path(){
	return get_template_directory_uri() . '/assets/css/style.min.css';
}
​
/**
* Init
*/
// function totallycriticalcss_init() {
// 	if ( get_option( 'totallycriticalcss_init' ) != 'true' ) {
// 		$posts = new WP_Query('post_type=page&posts_per_page=-1&post_status=publish');
// 		$posts = $posts->posts;
// ​
// 		//loop through the posts
// 		foreach( $posts as $post ):
// 			//totallycriticalcss($post->ID);
// 		endforeach;
// ​
// 		//update_option( 'totallycriticalcss_init', 'true' );
// 	}
// ​
// }
//add_action( 'admin_init', 'totallycriticalcss_init' );

/**
* Update Form Data when submitted
*/
add_action( 'wp_ajax_nopriv_save_admin_page', 'save_admin_page' );
add_action( 'wp_ajax_save_admin_page', 'save_admin_page' );

function save_admin_page() {
	$api_key = $_POST[ 'api_key' ];
	add_option( 'totallycriticalcss_api_key', $api_key );
}
​
​/**
* TotallyCriticalCSS Function
*/
function totallycriticalcss( $id ) {
	$cri = 'https://api.totallycriticalcss.com/v1/?';
	$url = get_permalink( $id );
	$pth = get_template_directory_uri();
	$css = totallycriticalcss_stylesheet_path();
	$key = get_option( 'totallycriticalcss_api_key' ) ? get_option( 'totallycriticalcss_api_key' ) : 'beadf54f56063cc0cce7ded292b8e099';
​
	$in = file_get_contents( $cri . 'u=' . $url . '&c=' . $css . '&p=' . $pth . '&k=' . $key, false, $context );
​
	if( $in ) {
		if ( ! add_post_meta( $id, 'totallycriticalcss', $in, true ) ) {
			update_post_meta ( $id, 'totallycriticalcss', $in );
			totallycriticalcss_mb_callback( $id );
		}
	}
}
​
/**
* On Post Save Function
*/
function totallycriticalcss_post_save( $post_id ){
	totallycriticalcss( $post_id );
}
add_action( 'save_post', 'totallycriticalcss_post_save' );
​
​
// ENQUEUE STYLES & SCRIPTS
function scripts() {
​
	$totallyCiriticalCSS = get_post_meta( get_the_ID(), 'totallycriticalcss', true );
​
	if( $totallyCiriticalCSS ):
		echo '<!-- TotallyCriticalCSS --><style>'.$totallyCiriticalCSS.'</style><!-- /TotallyCriticalCSS -->';
		add_action( 'get_footer', function(){
			wp_enqueue_style( 'directive-style', totallycriticalcss_stylesheet_path(), false, null, 'all' );
		});
	else:
		wp_enqueue_style( 'directive-style', totallycriticalcss_stylesheet_path(), false, null, 'all' );
	endif;
​
}
add_action( 'wp_enqueue_scripts', 'scripts' );

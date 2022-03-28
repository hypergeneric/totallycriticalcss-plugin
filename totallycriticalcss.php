<?php
/**
 * Plugin Name:  TotallyCriticalCSS
 * Plugin URI:   https://wppopupmaker.com/?utm_campaign=plugin-info&utm_source=plugin-header&utm_medium=plugin-uri
 * Description:  Totally fast and critical CSS.
 * Version:      1.0.0
 * Author:       Compiled Rogue
 * Author URI:   https://compiledrogue.com
 * License:      GPL2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  totallycriticalcss
 *
 * @package     TotallyCriticalCSS
 * @author      Compiled Rogue
 * @copyright   Copyright (c) 2022, Compiled Rogue LLC
 */

if ( !function_exists( 'write_log' ) ) {
	function write_log( $log ) {
		if ( true === WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}

if ( ! defined( 'TOTALLYCRITICALCSS_PLUGIN_FILE' ) ) {
	define( 'TOTALLYCRITICALCSS_PLUGIN_FILE', __FILE__ );
}

require_once 'classes/Admin.php';
require_once 'classes/Core.php';
require_once 'classes/Queue.php';

use Classes\Admin\Setup;
use Classes\Core\Critical;
use Classes\Queue\Queue;

if ( ! class_exists( 'TotallyCriticalCSS' ) ) {
	class TotallyCriticalCSS {

		public function __construct() {

			/******************************
			********* SETUP ADMIN *********
			******************************/

			if ( is_admin() ) {
				$admin = new Setup();
				$admin->totallycriticalcss_admin_setup();
			}

			/******************************
			********* SETUP CORE *********
			******************************/
			if ( is_admin() ) {
				$critical = new Critical();
				$critical->totallycriticalcss_save_post_action();
			}

			/******************************
			********* SETUP QUEUE *********
			******************************/
			$queue = new Queue();

		}
		
		public static function clear_tcss_data() {

			global $wpdb;
			$deleted_rows = $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE `meta_key` = 'totallycriticalcss'" );

		}
		
		public static function get_current_sheetlist() {

			$sheets = [];
			$response = wp_remote_get( get_home_url() . "/?totallycriticalcss=preview" );
			if ( is_array( $response ) && ! is_wp_error( $response ) ) {
				$body = $response[ 'body' ];
				$doc  = new DOMDocument();
				$doc->loadHTML( $body, LIBXML_NOWARNING | LIBXML_NOERROR );
				$domcss = $doc->getElementsByTagName( 'link' );
				foreach ( $domcss as $links ) {
					if ( strtolower( $links->getAttribute( 'rel' ) ) == "stylesheet" ) {
						$sheets[] = $links;
					}
				}
			}
			$result = [];
			foreach ( $sheets as $sheet ) { 
				$sheetid = $sheet->getAttribute('id');
				$sheetid_bits = explode( '-', $sheetid );
				array_pop( $sheetid_bits );
				$sheetid_clean = implode( '-', $sheetid_bits );
				$result[$sheetid_clean] = $sheet->getAttribute( 'href' );
			}
			return $result;

		}
		
		public static function install() {

			update_option( 'totallycriticalcss_simplemode', true );
			update_option( 'totallycriticalcss_show_metaboxes', true );
			update_option( 'totallycriticalcss_always_immediate', false );
			update_option( 'totallycriticalcss_adminmode', false );
			update_option( 'totallycriticalcss_selected_cpt', [ 'page', 'post', 'product' ] );

		}

		public static function uninstall() {
			
			delete_option( 'totallycriticalcss_simplemode' );
			delete_option( 'totallycriticalcss_show_metaboxes' );
			delete_option( 'totallycriticalcss_always_immediate' );
			delete_option( 'totallycriticalcss_adminmode' );
			delete_option( 'totallycriticalcss_api_key' );
			delete_option( 'totallycriticalcss_selected_cpt' );
			delete_option( 'totallycriticalcss_custom_dequeue' );
			delete_option( 'totallycriticalcss_custom_routes' );
			delete_option( 'totallycriticalcss_selected_styles' );
			
			TotallyCriticalCSS::clear_tcss_data();

		}

	}
}

if( class_exists( 'TotallyCriticalCSS' ) ) {

	// instantiate the plugin class
	$wp_plugin = new TotallyCriticalCSS();
	
	// Installation and uninstallation hooks
	register_uninstall_hook( TOTALLYCRITICALCSS_PLUGIN_FILE, array( 'TotallyCriticalCSS', 'uninstall' ) );
	register_deactivation_hook( __FILE__, array( 'TotallyCriticalCSS', 'uninstall' ) );
	register_activation_hook( __FILE__, array( 'TotallyCriticalCSS', 'install' ) );
	
}

<?php
/**
 * Plugin Name:  TotallyCriticalCSS
 * Plugin URI:   https://totallycriticalcss.com/
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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once __DIR__ . '/classes/tccss-plugin.php';
require_once __DIR__ . '/classes/tccss-admin-panel.php';
require_once __DIR__ . '/classes/tccss-queue.php';
require_once __DIR__ . '/classes/tccss-processor.php';
require_once __DIR__ . '/classes/tccss-sheetlist.php';
require_once __DIR__ . '/classes/tccss-post.php';
require_once __DIR__ . '/classes/tccss-options.php';

if ( ! class_exists( 'TotallyCriticalCSS' ) ) :

	class TotallyCriticalCSS {
		
		/** @var string The plugin version number. */
		var $version = '1.0.0';
		
		/** @var string Shortcuts. */
		var $plugin;
		var $sheetlist;
		var $processor;
		var $options;
		
		/**
		 * __construct
		 *
		 * A dummy constructor to ensure TotallyCriticalCSS is only setup once.
		 * 
		 * @param   void
		 * @return  void
		 */
		function __construct() {
			// Do nothing.
		}
		
		/**
		 * initialize
		 *
		 * Sets up the TotallyCriticalCSS plugin.
		 *
		 * @param   void
		 * @return  void
		 */
		function initialize() {

			// Define constants.
			$this->define( 'TCCSS', true );
			$this->define( 'TCCSS_FILE', __FILE__ );
			$this->define( 'TCCSS_DIRNAME', dirname( __FILE__ ) );
			$this->define( 'TCCSS_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
			$this->define( 'TCCSS_BASENAME', basename( dirname( __FILE__ ) ) );
			
			// Do all the plugin stuff.
			$this->plugin    = new TCCSS_Plugin();
			$this->sheetlist = new TCCSS_Sheetlist();
			$this->processor = new TCCSS_Processor();
			$this->options   = new TCCSS_Options();
			
			if ( is_admin() ) {
				
				// load up our admin classes
				$admin     = new TCCSS_AdminPanel();
				$post      = new TCCSS_Post();
				
			} else {
				
				// only fire the queue once on front-facing pages only
				add_action( 'template_redirect', function () {
					if ( 
						! is_singular() && 
						! is_page() && 
						! is_single() && 
						! is_archive() && 
						! is_home() &&
						! is_front_page() 
					) {
						return false;
					}
					$queue = new TCCSS_Queue();
				} );
				
			}
			
		}
		
		/**
		 * __call
		 *
		 * Sugar function to access class properties
		 *
		 * @param   string $name The property name.
		 * @return  void
		 */
		public function __call( $name, $arguments ) {
			return $this->{$name};
		}
		
		/**
		 * define
		 *
		 * Defines a constant if doesnt already exist.
		 *
		 * @param   string $name The constant name.
		 * @param   mixed  $value The constant value.
		 * @return  void
		 */
		function define( $name, $value = true ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}
		
		/**
		 * log
		 *
		 * Output logging to the debug.
		 *
		 * @param   mixed  $log The value.
		 * @return  void
		 */
		function log( $log ) {
			if ( true === WP_DEBUG ) {
				if ( is_array( $log ) || is_object( $log ) ) {
					error_log( print_r( $log, true ) );
				} else {
					error_log( $log );
				}
			}
		}
		
	}

	/*
	* tccss
	*
	* The main function responsible for returning the one true TotallyCriticalCSS Instance to functions everywhere.
	* Use this function like you would a global variable, except without needing to declare the global.
	*
	* @param   void
	* @return  TotallyCriticalCSS
	*/
	function tccss() {
		global $tccss;
		// Instantiate only once.
		if ( ! isset( $tccss ) ) {
			$tccss = new TotallyCriticalCSS();
			$tccss->initialize();
		}
		return $tccss;
	}

	// Instantiate.
	tccss();

endif; // class_exists check


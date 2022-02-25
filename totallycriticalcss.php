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

// if ( !function_exists( 'write_log' ) ) {
// 	function write_log( $log ) {
// 		if ( true === WP_DEBUG ) {
// 			if ( is_array( $log ) || is_object( $log ) ) {
// 				error_log( print_r( $log, true ) );
// 			} else {
// 				error_log( $log );
// 			}
// 		}
// 	}
// }

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
				// we are in admin mode
				$admin = new Setup();
				$admin->totallycriticalcss_admin_setup();
			}

			/******************************
			********* SETUP CORE *********
			******************************/

			$critical = new Critical();
			$critical->totallycriticalcss_save_post_action();

			/******************************
			********* SETUP QUEUE *********
			******************************/
			$queue = new Queue();

		}

	}

	$table = new TotallyCriticalCSS();
}

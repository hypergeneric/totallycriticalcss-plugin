<?php

class TCCSS_Plugin {
	
	/**
	 * install
	 *
	 * Run installation functions.
	 *
	 * @param   void
	 * @return  void
	 */
	public static function install() {

		update_option( 'totallycriticalcss_simplemode', true );
		update_option( 'totallycriticalcss_show_metaboxes', true );
		update_option( 'totallycriticalcss_adminmode', false );
		update_option( 'totallycriticalcss_ignore_routes', [ '^my-account/*' ] );
		update_option( 'totallycriticalcss_selected_cpt', [ 'page', 'post', 'product' ] );
		update_option( 'totallycriticalcss_viewport_width', 1400 );
		update_option( 'totallycriticalcss_viewport_height', 1080 );

	}

	/**
	 * uninstall
	 *
	 * Run installation functions.
	 *
	 * @param   void
	 * @return  void
	 */
	public static function uninstall() {
		
		global $wpdb;
		
		// kill the route entries in options
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE 'totallycriticalcss_%'" );
		
		tccss()->plugin()->clear_tccss_data();

	}
	
	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {
		
		register_uninstall_hook( TCCSS_FILE, array( __CLASS__, 'uninstall' ) );
		register_deactivation_hook( TCCSS_FILE, array( __CLASS__, 'uninstall' ) );
		register_activation_hook( TCCSS_FILE, array( __CLASS__, 'install' ) );
		
		if ( is_admin() ) {
			add_filter( 'plugin_action_links_' . TCCSS_BASENAME . '/totallycriticalcss.php', array( $this, 'add_settings_link' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_page' ) );
		}
		
	}
	
	/**
	 * clear_tccss_data
	 *
	 * Run installation functions.
	 *
	 * @param   void
	 * @return  void
	 */
	public function clear_tccss_data() {

		global $wpdb;
		
		// kill all the post meta values
		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE `meta_key` LIKE 'totallycriticalcss_%'" );
		
		// kill the route entries in options
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE 'totallycriticalcss_route_%'" );

	}
	
	/**
	 * add_settings_link
	 *
	 * Add settings link on plugin page
	 *
	 * @param   array $links The links array.
	 * @return  array The links array.
	 */
	public function add_settings_link( $links ) {
		$links[] = '<a href="' . $this->get_admin_url() . '">' . __( 'Settings' ) . '</a>';
		return $links;
	}
	
	/**
	 * admin_init
	 *
	 * Register and enqueue admin stylesheet & scripts
	 *
	 * @param   void
	 * @return  void
	 */
	public function admin_init() {
		// only enqueue these things on the settings page
		if ( $this->get_current_admin_url() == $this->get_admin_url() ) {
			wp_register_style( 'totallycriticalcss_plugin_stylesheet', TCCSS_PLUGIN_DIR . 'admin/css/admin.css', [], TCCSS_VERSION );
			wp_enqueue_style( 'totallycriticalcss_plugin_stylesheet' );
			wp_register_script( 'totallycriticalcss_script', TCCSS_PLUGIN_DIR . 'admin/js/admin.js', array( 'jquery' ), TCCSS_VERSION );
			wp_localize_script( 'totallycriticalcss_script', 'totallycriticalcss_obj',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' )
				)
			);
			wp_enqueue_script( 'totallycriticalcss_script' );
		}
	}
	
	/**
	 * admin_page
	 *
	 * Register admin page and menu.
	 *
	 * @param   void
	 * @return  void
	 */
	public function admin_page() {
		add_submenu_page(
			'options-general.php',
			__( 'Totally Critical CSS', 'tccss' ),
			__( 'Totally Critical CSS', 'tccss' ),
			'administrator',
			TCCSS_DIRNAME,
			array( $this, 'admin_page_settings' ),
			100
		);
	}
	
	/**
	 * admin_page_settings
	 *
	 * Render admin view
	 *
	 * @param   void
	 * @return  void
	 */
	public function admin_page_settings() {
		require_once TCCSS_DIRNAME . '/admin/view.php';
	}
	
	/**
	 * get_current_admin_url
	 *
	 * Get the current admin url.  Thanks WC!
	 *
	 * @param   void
	 * @return  void
	 */
	function get_current_admin_url() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );
		if ( ! $uri ) {
			return '';
		}
		return remove_query_arg( array( '_wpnonce' ), admin_url( $uri ) );
	}
	
	/**
	 * get_admin_url
	 *
	 * Add settings link on plugin page
	 *
	 * @param   void
	 * @return  string the admin url
	 */
	public function get_admin_url() {
		return admin_url( 'options-general.php?page=' . TCCSS_BASENAME );
	}

}

<?php

class TCCSS_Post {
	
	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		}
	}
	
	/**
	 * save_post
	 *
	 * On Post Save Function
	 *
	 * @param   int $id The post id.
	 * @return  int $id The post id.
	 */
	public function save_post( $id, $post ) {
		
		// do not save if this is an auto save routine
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $id;
		}
		
		// only save once! WordPress save's a revision as well.
		if ( wp_is_post_revision( $id ) ) {
			return $id;
		}
		
		// only process critical css for published pages
		if ( $post->post_status != "publish" ) {
			return $id;
		}
		
		tccss()->log( 'save_post: ' . $id );
		
		// set the invalidate flag
		tccss()->options()->setmeta( $id, 'invalidate', true );
		
		return $id;
		
	}
	
	/**
	 * add_meta_boxes
	 *
	 * Register metabox
	 *
	 * @param   void
	 * @return  void
	 */
	public function add_meta_boxes() {
		$show_metaboxes = tccss()->options()->get( 'show_metaboxes' );
		$simplemode     = tccss()->options()->get( 'simplemode' );
		$selected_cpt   = $simplemode ? [ 'page', 'post', 'product' ] : tccss()->options()->get( 'selected_cpt', [] );
		if ( $show_metaboxes ) {
			foreach ( $selected_cpt as $post_type ) {
				add_meta_box( 'totallycriticalcss_metabox_id', __( 'Totally Critical CSS', 'tccss' ), array( $this, 'metabox_callback' ), $post_type, 'side', 'high' );
			}
		}
	}

	/**
	 * metabox_callback
	 *
	 * Display metabox
	 *
	 * @param   int $post The post object.
	 * @return  void
	 */
	public function metabox_callback( $post ) {
		
		$invalidate  = tccss()->options()->getmeta( $post->ID, 'invalidate' );
		$criticalcss = tccss()->options()->getmeta( $post->ID, 'criticalcss', null );
		$adminmode   = tccss()->options()->get( 'adminmode' );
		
		$prefix = __( 'Totally Critical CSS', 'tccss' );
		$color  = 'red';
		$status = __( 'Not Generated', 'tccss' );
		
		if ( $invalidate ) {
			$color  = 'green';
			$status = 'Pending';
		} else {
			if ( $criticalcss ) {
				if ( $criticalcss->success === true ) {
					$color  = 'green';
					$status = __( 'Generated', 'tccss' );
				} else if ( $criticalcss->success === false ) {
					$prefix = __( 'Error', 'tccss' );
					$color  = 'red';
					$status = $criticalcss->message;
				} else {
					$prefix = __( 'Error', 'tccss' );
					$status = __( 'Invalid Server Response', 'tccss' );
				}
			}
		}
		
		printf(
			__( '%1$s: <strong style="color: %2$s; text-transform: uppercase;">%3$s</strong>', 'tccss' ),
			$prefix,
			$color,
			$status
		);
		
		if ( $adminmode && $criticalcss ) {?>
			<style>#tccsstable{border-collapse:collapse;border:1px solid #ddd;border-radius:0;box-shadow:3px 3px 0 0 rgba(0,0,0,.03);width:100%;margin:16px 0}#tccsstable td{padding:5px;border:1px solid #dee2e6}</style>
			<table id="tccsstable">
				<tr>
					<td><strong><?php esc_html_e( 'Success', 'tccss' ); ?></strong></td>
					<td><?php echo $criticalcss->success ? 'true' : 'false'; ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Message', 'tccss' ); ?></strong></td>
					<td><?php echo $criticalcss->message; ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Size', 'tccss' ); ?></strong></td>
					<td><?php echo $this->formatBytes( strlen( $criticalcss->data->css ) ); ?></td>
				</tr>
			</table>
		<?php
		}
		
	}
	
	/**
	 * formatBytes
	 *
	 * Convert bytes to something bigger
	 *
	 * @param   int $size bytes
	 * @param   int $precision precision
	 * @return  void
	 */
	function formatBytes ( $size, $precision = 2 ) {
		$base     = log( $size, 1024 );
		$suffixes = array( '', 'Kb', 'Mb', 'Gb', 'Tb' );   
		return round( pow( 1024, $base - floor( $base ) ), $precision ) .' '. $suffixes[ floor( $base ) ];
	}

}

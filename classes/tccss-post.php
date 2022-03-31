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
		
		$always_immediate = tccss()->options()->get( 'always_immediate' );
		
		if ( $always_immediate ) {
			// if we are doing immediate, just run it now
			tccss()->processor()->single( $id );
		} else {
			// otherwise, just set the invalidation flag as true
			tccss()->options()->setmeta( $id, 'invalidate', true );
		}
		
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
		
		$invalidate = tccss()->options()->getmeta( $post->ID, 'invalidate' );
		$criticalcss = tccss()->options()->getmeta( $post->ID, 'criticalcss', null );
		
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
		
	}

}

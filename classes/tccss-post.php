<?php

class TCCSS_Post {
	
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		}
	}
	
	/**
	* On Post Save Function
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
		
	}
	
	/**
	* Register metabox
	*/
	public function add_meta_boxes() {
		$show_metaboxes = tccss()->options()->get( 'show_metaboxes' );
		$simplemode     = tccss()->options()->get( 'simplemode' );
		$selected_cpt   = $simplemode ? [ 'page', 'post', 'product' ] : tccss()->options()->get( 'selected_cpt', [] );
		if ( $show_metaboxes ) {
			foreach ( $selected_cpt as $post_type ) {
				add_meta_box( 'totallycriticalcss_metabox_id', __( 'TotallyCriticalCSS', 'cr_crit' ), array( $this, 'metabox_callback' ), $post_type, 'side', 'high' );
			}
		}
	}

	/**
	* Display metabox
	*/
	public function metabox_callback( $post ) {
		$invalidate = tccss()->options()->getmeta( $post->ID, 'invalidate' );
		if ( $invalidate ) {
			$status = 'TotallyCriticalCSS is <strong style="color: green; text-transform: uppercase;">Pending</strong>' ;
		} else {
			$criticalcss = tccss()->options()->getmeta( $post->ID, 'criticalcss' );
			if ( ! $criticalcss ) {
				$status = 'TotallyCriticalCSS is <strong style="color: red; text-transform: uppeprcase;">Not Generated</strong>';
			} else {
				if ( $criticalcss == null ) {
					$status = '<strong style="color: red; text-transform: uppeprcase;">Error: Invalid Server Response</strong>';
				} else  {
					$status = $criticalcss->success === true ? 'TotallyCriticalCSS is <strong style="color: green; text-transform: uppercase;">Generated</strong>' : '<strong style="color: red; text-transform: uppeprcase;">Error: ' . $criticalcss->message . '</strong>';
				}
			}
		}
		echo $status;
	}

}

<?php

namespace Nelio_Popups\Frontend;

use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}//end if

function enqueue_popups() {

	if ( is_non_popup_preview() && ! show_popup_in_preview() ) {
		return;
	}//end if

	if ( is_singular( 'nelio_popup' ) ) {
		return;
	}//end if

	nelio_popups_enqueue_style( 'public' );
	nelio_popups_enqueue_style( 'block-customizations', array( 'nelio-popups-public' ) );

	wp_add_inline_style(
		'nelio-popups-public',
		get_style_vars()
	);

	nelio_popups_enqueue_script( 'public' );
	wp_add_inline_script(
		'nelio-popups-public',
		sprintf(
			'NelioPopupsFrontendSettings = %s;',
			wp_json_encode( get_frontend_settings() )
		),
		'before'
	);
}//end enqueue_popups()
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_popups' );

add_action(
	'init',
	function () {
		$popups = array();

		add_action(
			'wp_head',
			function () use ( &$popups ) {
				if ( is_non_popup_preview() && ! show_popup_in_preview() ) {
					return;
				}//end if

				$active_popups = get_active_popups();
				foreach ( $active_popups as $active_popup ) {
					$size  = isset( $active_popup['config']['size'] )
						? $active_popup['config']['size']
						: array(
							'type'  => 'auto',
							'value' => 'normal',
						);
					$width = 'custom' === $size['type']
						? $size['width']['value'] . str_replace( '%', 'vw', $size['width']['unit'] )
						: '';

					$popups[] = sprintf(
						'<div id="%1$s" aria-hidden="true" class="nelio-popup-store %2$s"%3$s><div id="%4$s">%5$s</div></div>',
						esc_attr( 'nelio-popup-store-' . $active_popup['id'] ),
						'auto' === $size['type']
							? esc_attr( "nelio-popup-size--is-auto-{$size['value']}" )
							: esc_attr( "nelio-popup-size--is-{$size['type']}" ),
						empty( $width ) ? '' : " style=\"width:{$width}\"",
						esc_attr( 'nelio-popup-content-' . $active_popup['id'] ),
						do_shortcode( do_blocks( $active_popup['content'] ) )
					);
				}//end foreach
			}
		);

		add_action(
			'wp_footer',
			function () use ( &$popups ) {
				echo implode( "\n", $popups ); // phpcs:ignore
			}
		);
	}
);

// =======
// HELPERS
// =======
// phpcs:ignore
function get_frontend_settings() {
	$settings = array(
		'context' => get_wordpress_context(),
		'popups'  => array_map(
			function ( $popup ) {
				unset( $popup['content'] );
				return $popup;
			},
			get_active_popups()
		),
	);

	/**
	 * Filters the frontend settings.
	 *
	 * @param array $settings frontend settings.
	 *
	 * @since 1.0.0
	 */
	return apply_filters( 'nelio_popups_frontend_settings', $settings );
}//end get_frontend_settings()

function get_wordpress_context() {
	return array(
		'isSingular'   => is_singular(),
		'postId'       => is_singular() ? get_the_ID() : 0,
		'postPopups'   => is_singular() ? get_post_popups( get_the_ID() ) : 'auto',
		'postType'     => get_post_type(),
		'parents'      => is_singular() ? get_post_ancestors( get_the_ID() ) : array(),
		'previewPopup' => get_previewed_popup(),
		'specialPage'  => get_special_page(),
		'template'     => is_singular() ? get_page_template_slug( get_the_ID() ) : '',
	);
}//end get_wordpress_context()

function get_active_popups() {
	$popups = array();
	$query  = new WP_Query(
		array(
			'post_type'      => 'nelio_popup',
			'post_status'    => 'publish',
			'meta_key'       => '_nelio_popups_is_enabled', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			/**
			 * Filters the maximum number of popups that our plugin can load on any given page.
			 *
			 * @param number $popups_per_page number of popups to load. Default: 10.
			 */
			'posts_per_page' => apply_filters( 'nelio_popups_per_page', 10 ),
		)
	);
	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		return array();
	}//end if

	while ( $query->have_posts() ) {
		$query->the_post();
		$popups[] = load_popup();
	}//end while

	wp_reset_postdata();

	$popup_ids = array_column( $popups, 'id' );

	/**
	 * Filters the list of active popups.
	 *
	 * @param int[] $popup_ids list of active popup ids.
	 *
	 * @since 1.0.0
	 */
	$popup_ids = apply_filters( 'nelio_popups_active_popups', $popup_ids );

	return array_filter(
		$popups,
		function ( $popup ) use ( $popup_ids ) {
			return in_array( $popup['id'], $popup_ids, true );
		}
	);
}//end get_active_popups()

function get_post_popups( $post_id ) {
	$popups = get_post_meta( $post_id, '_nelio_popups_active_popup', true );
	$popups = empty( $popups ) ? 'auto' : $popups;
	if ( 'auto' === $popups ) {
		return 'auto';
	}//end if

	$popups = explode( ',', $popups );
	$popups = array_map( 'absint', $popups );
	$popups = array_values( array_filter( $popups ) );
	return $popups;
}//end get_post_popups()

function get_style_vars() {
	$default = array(
		'animate-delay'    => '1s',
		'animate-duration' => '1s',
	);

	/**
	 * Filters frontend style vars.
	 *
	 * @param array $default frontend style vars.
	 *
	 * @since 1.0.0
	 */
	$values = apply_filters( 'nelio_popups_frontend_style_vars', $default );
	$values = wp_parse_args( $values, $default );

	$vars = '';
	foreach ( $values as $name => $value ) {
		$vars .= "--nelio-popups-{$name}: $value;\n";
	}//end foreach
	return ":root {\n{$vars}}";
}//end get_style_vars()

function show_popup_in_preview() {
	/**
	 * Filters if popups should be included in previews or not.
	 *
	 * @param boolean $show_in_preview whether popups should be visibles in previews or not. Default: `false`.
	 *
	 * @since 1.0.0
	 */
	return apply_filters( 'nelio_popups_show_in_preview', false );
}//end show_popup_in_preview()

function is_non_popup_preview() {
	if ( ! is_preview() ) {
		return false;
	}//end if
	$popup = get_previewed_popup();
	return empty( $popup );
}//end is_non_popup_preview()

function get_previewed_popup() {
	$popup_id = isset( $_GET['nelio-popup-preview'] )
		? absint( $_GET['nelio-popup-preview'] )
		: 0;

	$valid = isset( $_GET['nonce'] )
		? wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'nelio-popup-preview_' . $popup_id )
		: false;

	if ( ! $valid ) {
		return false;
	}//end if

	$query = new WP_Query(
		array(
			'p'         => $popup_id,
			'post_type' => 'nelio_popup',
		)
	);
	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		return false;
	}//end if

	$query->the_post();

	$popup = load_popup();

	wp_reset_postdata();
	unset( $popup['content'] );
	return $popup;
}//end get_previewed_popup()

function get_special_page() {
	if ( is_404() ) {
		return '404-page';
	} elseif ( is_home() ) {
		return 'blog-page';
	} elseif ( is_front_page() ) {
		return 'home-page';
	} elseif ( is_search() ) {
		return 'search-result-page';
	} else {
		return 'none';
	}//end if
}//end get_special_page()

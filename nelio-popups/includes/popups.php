<?php

namespace Nelio_Popups\Popups;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}//end if

function register_popups() {

	$labels = array(
		'name'                  => _x( 'Nelio Popups', 'Post Type General Name', 'nelio-popups' ),
		'singular_name'         => _x( 'Popup', 'Post Type Singular Name', 'nelio-popups' ),
		'menu_name'             => __( 'Nelio Popups', 'nelio-popups' ),
		'name_admin_bar'        => __( 'Popup', 'nelio-popups' ),
		'archives'              => __( 'Popup Archives', 'nelio-popups' ),
		'attributes'            => __( 'Popup Attributes', 'nelio-popups' ),
		'parent_item_colon'     => __( 'Parent Popup:', 'nelio-popups' ),
		'all_items'             => __( 'All Popups', 'nelio-popups' ),
		'add_new_item'          => __( 'Add New Popup', 'nelio-popups' ),
		'add_new'               => __( 'Add New', 'nelio-popups' ),
		'new_item'              => __( 'New Popup', 'nelio-popups' ),
		'edit_item'             => __( 'Edit Popup', 'nelio-popups' ),
		'update_item'           => __( 'Update Popup', 'nelio-popups' ),
		'view_item'             => __( 'View Popup', 'nelio-popups' ),
		'view_items'            => __( 'View Popups', 'nelio-popups' ),
		'search_items'          => __( 'Search Popup', 'nelio-popups' ),
		'not_found'             => __( 'Not found', 'nelio-popups' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'nelio-popups' ),
		'featured_image'        => __( 'Featured Image', 'nelio-popups' ),
		'set_featured_image'    => __( 'Set featured image', 'nelio-popups' ),
		'remove_featured_image' => __( 'Remove featured image', 'nelio-popups' ),
		'use_featured_image'    => __( 'Use as featured image', 'nelio-popups' ),
		'insert_into_item'      => __( 'Insert into popup', 'nelio-popups' ),
		'uploaded_to_this_item' => __( 'Uploaded to this popup', 'nelio-popups' ),
		'items_list'            => __( 'Popups list', 'nelio-popups' ),
		'items_list_navigation' => __( 'Popups list navigation', 'nelio-popups' ),
		'filter_items_list'     => __( 'Filter popups list', 'nelio-popups' ),
	);

	$args = array(
		'can_export'          => true,
		'description'         => __( 'Nelio Popups', 'nelio-popups' ),
		'exclude_from_search' => true,
		'has_archive'         => false,
		'hierarchical'        => false,
		'label'               => __( 'Popup', 'nelio-popups' ),
		'labels'              => $labels,
		'menu_icon'           => get_icon(),
		'menu_position'       => 25,
		'public'              => true,
		'publicly_queryable'  => true,
		'rewrite'             => false,
		'show_in_admin_bar'   => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'show_in_rest'        => true,
		'show_ui'             => true,
		'map_meta_cap'        => true,
		'capability_type'     => 'nelio_popup',
		'supports'            => array( 'editor', 'revisions', 'custom-fields', 'title', 'author' ),
	);

	register_post_type( 'nelio_popup', $args );
}//end register_popups()
add_action( 'init', __NAMESPACE__ . '\register_popups', 5 );

function disable_draft_status( $popup_id ) {
	if ( 'nelio_popup' !== get_post_type( $popup_id ) ) {
		return;
	}//end if

	if ( wp_is_post_revision( $popup_id ) || wp_is_post_autosave( $popup_id ) ) {
		return;
	}//end if

	if ( 'draft' !== get_post_status( $popup_id ) ) {
		return;
	}//end if

	wp_update_post(
		array(
			'ID'          => $popup_id,
			'post_status' => 'publish',
		)
	);
}//end disable_draft_status()
add_action( 'save_post', __NAMESPACE__ . '\disable_draft_status' );

function get_preview_url( $url, $post ) {
	if ( 'nelio_popup' !== $post->post_type ) {
		return $url;
	}//end if

	$post_id = $post->ID;
	$url     = add_query_arg( 'nelio-popup-preview', $post_id, home_url() );
	$name    = 'nonce';
	$action  = 'nelio-popup-preview_' . $post_id;
	return add_query_arg( $name, wp_create_nonce( $action ), $url );
}//end get_preview_url()
add_filter( 'preview_post_link', __NAMESPACE__ . '\get_preview_url', 10, 2 );

function get_popup_metas() {
	$metas = array(
		'advanced',
		'animation',
		'conditions',
		'display',
		'location',
		'overlay',
		'size',
		'sound',
		'spacing',
		'target',
		'triggers',
		'wrapper',
	);
	/**
	 * Filters the popup metas.
	 *
	 * @param array $metas popup meta names.
	 *
	 * @since 1.0.21
	 */
	return apply_filters( 'nelio_popups_metas', $metas );
}//end get_popup_metas()

function register_popups_meta() {
	$fields = get_popup_metas();
	foreach ( $fields as $short_field ) {
		$field = "nelio_popups_{$short_field}";
		register_rest_field(
			'nelio_popup',
			$field,
			array(
				'get_callback'    => function ( $params ) use ( $field ) {
					return get_post_meta( $params['id'], "_{$field}", true );
				},
				'update_callback' => function ( $value, $obj ) use ( $field ) {
					update_post_meta( $obj->ID, "_{$field}", $value );
				},
			)
		);
	}//end foreach
}//end register_popups_meta()
add_action( 'init', __NAMESPACE__ . '\register_popups_meta', 5 );

function register_popups_is_enabled() {
	$field = 'nelio_popups_is_enabled';
	register_rest_field(
		'nelio_popup',
		$field,
		array(
			'get_callback'    => function ( $params ) use ( $field ) {
				return ! empty( get_post_meta( $params['id'], "_{$field}", true ) );
			},
			'update_callback' => function ( $value, $obj ) use ( $field ) {
				if ( true === $value ) {
					update_post_meta( $obj->ID, "_{$field}", $value );
				} else {
					delete_post_meta( $obj->ID, "_{$field}" );
				}//end if
			},
		)
	);
}//end register_popups_is_enabled()
add_action( 'rest_api_init', __NAMESPACE__ . '\register_popups_is_enabled' );

function set_columns( $columns ) {
	$custom_columns = array(
		'cb'           => $columns['cb'],
		'title'        => $columns['title'],
		'author'       => $columns['author'],
		'popup_active' => _x( 'Active', 'text', 'nelio-popups' ),
		'popup_class'  => _x( 'CSS Class', 'text', 'nelio-popups' ),
		'date'         => $columns['date'],
	);
	return $custom_columns;
}//end set_columns()
add_filter( 'manage_nelio_popup_posts_columns', __NAMESPACE__ . '\set_columns' );

function set_column_values( $column_name, $post_id ) {
	if ( 'popup_active' === $column_name ) {
		printf(
			'<span class="nelio-popups-active-wrapper" data-id="%s" data-enabled="%s"></span>',
			esc_attr( $post_id ),
			esc_attr( ! empty( get_post_meta( $post_id, '_nelio_popups_is_enabled', true ) ) ? 'true' : 'false' )
		);
	}//end if

	if ( 'popup_class' === $column_name ) {
		printf(
			'<code>nelio-popup-%d</code>',
			esc_html( absint( $post_id ) )
		);
	}//end if
}//end set_column_values()
add_action( 'manage_nelio_popup_posts_custom_column', __NAMESPACE__ . '\set_column_values', 10, 2 );

function customize_row_actions( $actions, $post ) {
	if ( 'nelio_popup' === $post->post_type ) {
		unset( $actions['inline hide-if-no-js'] );
		$actions['view'] = sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( get_preview_url( '', $post ) ),
			esc_html_x( 'Preview', 'command', 'nelio-popups' )
		);
	}//end if

	return $actions;
}//end customize_row_actions()
add_filter( 'post_row_actions', __NAMESPACE__ . '\customize_row_actions', 10, 2 );

function remove_view_link() {
	global $wp_admin_bar;

	if ( get_post_type() === 'nelio_popup' ) {
		$wp_admin_bar->remove_menu( 'view' );
	}//end if
}//end remove_view_link()
add_action( 'wp_before_admin_bar_render', __NAMESPACE__ . '\remove_view_link' );

function maybe_enqueue_popups_list_assets() {
	$screen = get_current_screen();
	if ( empty( $screen ) ) {
		return;
	}//end if

	$haystack = $screen->id;
	$needle   = 'edit-nelio_popup';
	if ( 0 !== substr_compare( $haystack, $needle, -strlen( $needle ) ) ) {
		return;
	}//end if

	wp_enqueue_style( 'wp-components' );
	nelio_popups_register_script( 'popup-list' );
	nelio_popups_enqueue_script( 'popup-list' );
}//end maybe_enqueue_popups_list_assets()
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\maybe_enqueue_popups_list_assets' );

function allowed_block_types( $allowed_block_types, $editor_context ) {
	if ( isset( $editor_context->post ) && 'nelio_popup' === $editor_context->post->post_type ) {
		/**
		* Filters the allowed blocks in a popup.
		*
		* @param bool|string[] $allowed_block_types Array of block type slugs,
		* or boolean to enable/disable all. Default true (all registered block
		* types supported)
		*
		* @since 1.1.0
		*/
		return apply_filters( 'nelio_popups_allowed_blocks', $allowed_block_types );
	}//end if

	return $allowed_block_types;
}//end allowed_block_types()
add_filter( 'allowed_block_types_all', __NAMESPACE__ . '\allowed_block_types', 10, 2 );

// =======
// HELPERS
// =======
// phpcs:ignore
function get_icon() {
	$icon = nelio_popups_path() . '/includes/icon.svg';
	return ! file_exists( $icon )
		? 'dashicons-align-wide'
		: 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( $icon ) ); // phpcs:ignore
}//end get_icon()

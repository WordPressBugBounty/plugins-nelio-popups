<?php

namespace Nelio_Popups\Menu;

defined( 'ABSPATH' ) || exit;

function register_submenus() {
	// phpcs:ignore WordPress.WP.Capabilities.Unknown
	if ( ! current_user_can( 'edit_others_nelio_popups' ) ) {
		return;
	}

	global $submenu;
	$menu_slug = 'edit.php?post_type=nelio_popup';

	if ( ! is_plugin_active( 'nelio-popups-premium/nelio-popups-premium.php' ) ) {
		// phpcs:ignore
		$submenu[ $menu_slug ][] = array(
			_x( 'Premium', 'text', 'nelio-popups' ),
			'edit_others_nelio_popups',
			add_query_arg( // phpcs:ignore
				array(
					'utm_source'   => 'nelio-popups',
					'utm_medium'   => 'plugin',
					'utm_campaign' => 'premium',
					'utm_content'  => 'plugin-submenu',
				),
				_x( 'https://neliosoftware.com/popups/', 'text', 'nelio-popups' )
			),
		);
	}

	// phpcs:ignore
	$submenu[ $menu_slug ][] = array(
		_x( 'Support', 'text', 'nelio-popups' ),
		'edit_others_nelio_popups',
		_x( 'https://wordpress.org/support/plugin/nelio-popups/', 'text', 'nelio-popups' ),
	);
}
add_action( 'admin_menu', __NAMESPACE__ . '\register_submenus' );

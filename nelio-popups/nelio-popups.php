<?php
/**
 * Plugin Name: Nelio Popups
 * Plugin URI:  https://neliosoftware.com/popups/
 * Description: A plugin to create beautiful popups.
 *
 * Author:      Nelio Software
 * Author URI:  https://neliosoftware.com
 * Version:     1.2.7
 * Text Domain: nelio-popups
 *
 * Requires at least: 6.6
 * Requires PHP:      7.4
 *
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}//end if

define( 'NELIO_POPUPS', true );

function nelio_popups_path() {
	return untrailingslashit( plugin_dir_path( __FILE__ ) );
}//end nelio_popups_path()

function nelio_popups_url() {
	return untrailingslashit( plugin_dir_url( __FILE__ ) );
}//end nelio_popups_url()

function nelio_popups_version() {
	$data = get_file_data( __FILE__, array( 'Version' ), 'plugin' );
	return $data[0];
}//end nelio_popups_version()

function nelio_popups_init() {
	require_once nelio_popups_path() . '/includes/popups.php';
	require_once nelio_popups_path() . '/includes/utils.php';

	require_once nelio_popups_path() . '/includes/compat.php';
	require_once nelio_popups_path() . '/includes/gutenberg.php';
	require_once nelio_popups_path() . '/includes/menu.php';
	require_once nelio_popups_path() . '/includes/popup-editor.php';
	require_once nelio_popups_path() . '/includes/popup-capabilities.php';
	require_once nelio_popups_path() . '/includes/rest.php';
	require_once nelio_popups_path() . '/includes/update.php';

	if ( ! is_admin() ) {
		require_once nelio_popups_path() . '/includes/frontend/index.php';
	}//end if
}//end nelio_popups_init()
nelio_popups_init();

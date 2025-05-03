<?php

namespace Nelio_Popups\REST_API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}//end if

function add_title_search_limit_arg( $args, $request ) {
	$args['nelio_popups_search_by_title'] = isset( $request['nelio_popups_search_by_title'] );
	return $args;
}//end add_title_search_limit_arg()
add_filter( 'rest_page_query', __NAMESPACE__ . '\add_title_search_limit_arg', 10, 2 );
add_filter( 'rest_post_query', __NAMESPACE__ . '\add_title_search_limit_arg', 10, 2 );

function maybe_search_by_title( $where, $wp_query ) {
	$search     = $wp_query->get( 's' );
	$only_title = ! empty( $wp_query->get( 'nelio_popups_search_by_title' ) );
	if ( $only_title ) {
		global $wpdb;
		$where .= sprintf(
			" AND {$wpdb->posts}.post_title LIKE '%%%s%%' ",
			esc_sql( $search )
		);
	}//end if
	return $where;
}//end maybe_search_by_title()
add_filter( 'posts_where', __NAMESPACE__ . '\maybe_search_by_title', 10, 2 );

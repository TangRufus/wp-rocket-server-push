<?php
/**
 * Plugin Name:     WP Rocket Server Push
 * Plugin URI:      https://github.com/TangRufus/wp-rocket-server-push
 * GitHub Plugin URI: TangRufus/wp-rocket-server-push
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          Tang Rufus
 * Author URI:      https://www.typist.tech/
 * Text Domain:     wp-rocket-server-push
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         WP_Rocket_Server_Push
 */

/**
 * Maps a WordPress filter to an "as" parameter in a resource hint
 *
 * @since 1.0.0
 *
 * @param string $current_filter pass current_filter()
 *
 * @return string 'style' or 'script'
 */
function resource_type( string $current_filter ) : string {
	return 'style_loader_src' === $current_filter ? 'style' : 'script';
}

/**
 *
 * @since 1.0.0
 *
 * @param string $url
 *
 * @return string $url
 */
function server_push_header( string $url ) : string {
	if ( headers_sent() ) {
		return $url;
	}

	$link_header = sprintf(
		'Link: <%s>; rel=preload; as=%s',
		esc_url( $url ),
		sanitize_html_class( resource_type( current_filter() ) )
	);

	header( $link_header, false );

	return $url;
}

remove_filter( 'style_loader_src', 'rocket_cdn_enqueue', PHP_INT_MAX );
remove_filter( 'script_loader_src', 'rocket_cdn_enqueue', PHP_INT_MAX );

add_filter( 'style_loader_src', 'rocket_cdn_enqueue', (PHP_INT_MAX - 1) );
add_filter( 'script_loader_src', 'rocket_cdn_enqueue', (PHP_INT_MAX - 1) );

add_filter( 'script_loader_src', 'server_push_header', PHP_INT_MAX );
add_filter( 'style_loader_src', 'server_push_header', PHP_INT_MAX );

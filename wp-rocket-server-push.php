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
 * Version:         1.3.0
 *
 * @package         WP_Rocket_Server_Push
 */

if ( is_admin() ) {
	return;
}

/**
 * Cloudflare gives an HTTP 520 error when more than 8k of headers are present. Limiting $this
 * plugin's output to 4k should keep those errors away.
 */

define( 'ROCKET_SERVER_PUSH_MAX_HEADER_SIZE', 1024 * 4 );
$rsp_header_size_accumulator = 0;

/**
 * Maps a WordPress filter to an "as" parameter in a resource hint
 *
 * @since 1.0.0
 *
 * @param string $current_filter pass current_filter().
 *
 * @return string 'style' or 'script'
 */
function rsp_resource_type( string $current_filter ) : string {
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
function rsp_header( $url ) {
	global $rsp_header_size_accumulator;

	if ( empty( $url ) ) {
		return $url;
	}

	$link_header = sprintf(
		'Link: <%s>; rel=preload; as=%s',
		esc_url_raw( $url ),
		sanitize_html_class( rsp_resource_type( current_filter() ) )
	);

	// Early quit if we have hit the header limit.
	if ( ( $rsp_header_size_accumulator + strlen( $link_header ) ) > ROCKET_SERVER_PUSH_MAX_HEADER_SIZE ) {
		return $url;
	}

	$rsp_header_size_accumulator += strlen( $link_header );

	header( $link_header, false );

	return $url;
}

remove_filter( 'style_loader_src', 'rocket_cdn_enqueue', PHP_INT_MAX - 1 );
remove_filter( 'script_loader_src', 'rocket_cdn_enqueue', PHP_INT_MAX - 1 );
add_filter( 'style_loader_src', 'rocket_cdn_enqueue', PHP_INT_MAX - 2 );
add_filter( 'script_loader_src', 'rocket_cdn_enqueue', PHP_INT_MAX - 2 );

remove_filter( 'style_loader_src', 'rocket_browser_cache_busting', PHP_INT_MAX );
remove_filter( 'script_loader_src', 'rocket_browser_cache_busting', PHP_INT_MAX );
add_filter( 'style_loader_src', 'rocket_browser_cache_busting', PHP_INT_MAX - 1 );
add_filter( 'script_loader_src', 'rocket_browser_cache_busting', PHP_INT_MAX - 1 );

add_filter( 'script_loader_src', 'rsp_header', PHP_INT_MAX );
add_filter( 'style_loader_src', 'rsp_header', PHP_INT_MAX );

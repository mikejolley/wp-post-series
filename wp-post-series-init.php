<?php
/**
 * Init the plugin.
 *
 * This runs after PHP version checks to ensure namespace usage does not cause errors.
 *
 * @package MJ/PostSeries
 */

namespace MJ\PostSeries;

defined( 'ABSPATH' ) || exit;

/**
 * Require Autoloader, and ensure build is complete. Otherwise abort.
 */
$autoloader = __DIR__ . '/vendor/autoload.php';
$build      = __DIR__ . '/build/frontend.js';
if ( is_readable( $autoloader ) && is_readable( $build ) ) {
	require $autoloader;
} else {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log(  // phpcs:ignore
			sprintf(
				/* translators: 1: composer command. 2: plugin directory */
				esc_html__( 'Your installation of WP Post Series is incomplete. Please run %1$s within the %2$s directory, or download the built plugin files from wordpress.org.', 'wp-post-series' ),
				'`composer install && && npm install && npm run build`',
				'`' . esc_html( str_replace( ABSPATH, '', __DIR__ ) ) . '`'
			)
		);
	}
	/**
	 * Outputs an admin notice if composer install has not been ran.
	 */
	add_action(
		'admin_notices',
		function() {
			?>
			<div class="notice notice-error">
				<p>
					<?php
					printf(
						/* translators: 1: composer command. 2: plugin directory */
						esc_html__( 'Your installation of WP Post Series is incomplete. Please run %1$s within the %2$s directory, or download the built plugin files from wordpress.org.', 'wp-post-series' ),
						'<code>composer install && && npm install && npm run build</code>',
						'<code>' . esc_html( str_replace( ABSPATH, '', __DIR__ ) ) . '</code>'
					);
					?>
				</p>
			</div>
			<?php
		}
	);
	return;
}

/**
 * Get a post series term using a post ID.
 *
 * @param int $post_id post ID.
 * @return object the term object.
 */
function get_post_series( $post_id ) {
	$series = wp_get_post_terms( $post_id, 'post_series' );

	if ( ! is_wp_error( $series ) && ! empty( $series ) && is_array( $series ) ) {
		$series = current( $series );
	} else {
		$series = false;
	}

	return $series;
}

/**
 * Fetch instance of plugin.
 */
function init() {
	static $container;

	if ( is_null( $container ) ) {
		$container = new \MJ\PostSeries\Registry\Container();
		$container->register(
			\MJ\PostSeries\Plugin::class,
			function( \MJ\PostSeries\Registry\Container $container ) {
				return new \MJ\PostSeries\Plugin( $container, __FILE__ );
			}
		);
		$container->get( \MJ\PostSeries\Plugin::class );
	}

	return $container;
}

add_action( 'plugins_loaded', 'MJ\PostSeries\init', 20 );

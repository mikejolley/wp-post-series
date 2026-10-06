<?php
/**
 * E2E only: `?legacy-series-template` renders the 2.0.0 series-box.php, as a theme override from that version would.
 *
 * @package MJ/PostSeries
 */

add_filter(
	'wp_post_series_locate_template',
	static function ( $template ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Test fixture switch.
		return isset( $_GET['legacy-series-template'] ) ? WP_PLUGIN_DIR . '/wp-post-series/tests/php/fixtures/series-box-2.0.0.php' : $template;
	}
);

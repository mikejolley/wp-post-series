<?php
/**
 * WP Post Series
 *
 * @package           MJ/PostSeries
 * @author            Mike Jolley
 * @copyright         2020 Mike Jolley
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       WP Post Series
 * Plugin URI:        https://wordpress.org/plugins/wp-post-series/
 * Description:       Publish and link together a series of posts using a new "series" taxonomy. Automatically display links to other posts in a series above your content.
 * Version:           2.0.0
 * Author:            Mike Jolley
 * Author URI:        http://mikejolley.com
 * Requires at least: 5.4
 * Tested up to:      5.6
 * Requires PHP:      5.6
 * Text Domain:       wp-post-series
 * Domain Path:       /languages/
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bail early if PHP version dependency is not met.
 */
if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
	return;
}

require __DIR__ . '/wp-post-series-init.php';

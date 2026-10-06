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
 * Version:           2.1.0
 * Author:            Mike Jolley
 * Author URI:        http://mikejolley.com
 * Requires at least: 6.6
 * Tested up to:      7.1
 * Requires PHP:      7.4
 * Text Domain:       wp-post-series
 * Domain Path:       /languages/
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bail early if PHP version dependency is not met.
 */
if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
	return;
}

require __DIR__ . '/wp-post-series-init.php';

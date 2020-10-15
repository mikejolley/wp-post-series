<?php
/**
 * Handle Templates.
 *
 * @package MJ/PostSeries
 */

namespace MJ\PostSeries;

defined( 'ABSPATH' ) || exit;

use MJ\PostSeries\Plugin;
use MJ\PostSeries\Registry\Container;

/**
 * Template class.
 */
class Template {
	/**
	 * Default template directory.
	 *
	 * @var string
	 */
	private $default_template_dir;

	/**
	 * Constructor.
	 *
	 * @param string $default_template_dir Default template directory.
	 */
	public function __construct( $default_template_dir = '' ) {
		$this->default_template_dir = $default_template_dir;
	}

	/**
	 * Get and include template files.
	 *
	 * @param mixed  $template_name Name of template to load.
	 * @param array  $args (default: array()) Args to pass to the template file.
	 * @param string $template_path (default: '') Path to look for template file.
	 * @param string $default_path (default: '') Default path to look for template file.
	 */
	public function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		if ( $args && is_array( $args ) ) {
			// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			extract( $args );
		}
		include $this->locate_template( $template_name, $template_path, $default_path );
	}

	/**
	 * Locate a template and return the path for inclusion.
	 *
	 * This is the load order:
	 *
	 *      yourtheme       /   $template_path  /   $template_name
	 *      yourtheme       /   $template_name
	 *      $default_path   /   $template_name
	 *
	 * @param mixed  $template_name Name of template to load.
	 * @param string $template_path (default: '') Path to look for template file.
	 * @param string $default_path (default: '') Default path to look for template file.
	 * @return string
	 */
	public function locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = 'wp_post_series';
		}
		if ( ! $default_path ) {
			$default_path = $this->default_template_dir;
		}

		// Look within passed path within the theme - this is priority.
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);

		// Get default template.
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		return apply_filters( 'wp_post_series_locate_template', $template, $template_name, $template_path );
	}
}

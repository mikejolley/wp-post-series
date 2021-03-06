<?php
/**
 * Post Series Block Type.
 *
 * @package MJ/PostSeries
 */

namespace MJ\PostSeries\BlockTypes;

defined( 'ABSPATH' ) || exit;

use MJ\PostSeries\PostContent;

/**
 * Post Series Block Type class.
 */
class PostSeries {

	/**
	 * Block namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'mj';

	/**
	 * Block namespace.
	 *
	 * @var string
	 */
	protected $block_name = 'wp-post-series';

	/**
	 * Holds the Content Controller class.
	 *
	 * @var PostContent
	 */
	private $content;

	/**
	 * Constructor.
	 *
	 * @param PostContent $content PostContent controller class instance.
	 */
	public function __construct( PostContent $content ) {
		$this->content = $content;
	}

	/**
	 * Gets the editor script handle.
	 *
	 * @return string
	 */
	public function get_script_handle() {
		return $this->block_name . '-block';
	}

	/**
	 * Registers the block type with WordPress.
	 */
	public function register_block_type() {
		register_block_type(
			$this->namespace . '/' . $this->block_name,
			array(
				'editor_script'   => $this->get_script_handle(),
				'script'          => 'wp-post-series',
				'style'           => 'wp-post-series',
				'render_callback' => array( $this, 'render' ),
				'attributes'      => array(
					'series'          => array(
						'type' => 'string',
					),
					'showDescription' => array(
						'type' => 'boolean',
					),
					'showPosts'       => array(
						'type' => 'boolean',
					),
					'className'       => array(
						'type' => 'string',
					),
					'previewId'       => array(
						'type' => 'number',
					),
				),
				'supports'        => [],
			)
		);
	}

	/**
	 * Append frontend scripts when rendering the block.
	 *
	 * @param array|\WP_Block $attributes Block attributes, or an instance of a WP_Block. Defaults to an empty array.
	 * @param string          $content    Block content. Default empty string.
	 * @return string Rendered block type output.
	 */
	public function render( $attributes = [], $content = '' ) {
		$attributes = wp_parse_args(
			$attributes,
			array(
				'series'          => '',
				'showDescription' => true,
				'showPosts'       => false,
				'className'       => '',
				'previewId'       => 0,
			)
		);
		$post_id    = get_the_ID();

		if ( ! empty( $attributes['previewId'] ) && empty( $attributes['series'] ) ) {
			$series = get_term_by( 'id', absint( $attributes['previewId'] ), 'post_series' );
		} else {
			$series_slug = ! empty( $attributes['series'] ) ? $attributes['series'] : '';
			$series      = $series_slug ? get_term_by( 'slug', $series_slug, 'post_series' ) : \MJ\PostSeries\get_post_series( $post_id );
		}

		if ( ! $series || is_wp_error( $series ) ) {
			return $content;
		}

		return $this->content->render_post_series( $post_id, $series, $attributes['className'], $attributes['showDescription'], $attributes['showPosts'] );
	}
}

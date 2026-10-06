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
	 * Holds the Content Controller class.
	 *
	 * @var PostContent
	 */
	private $content;

	/**
	 * Path to the directory containing the built block.json.
	 *
	 * @var string
	 */
	private $block_dir;

	/**
	 * Constructor.
	 *
	 * @param PostContent $content PostContent controller class instance.
	 * @param string      $block_dir Path to the directory containing the built block.json.
	 */
	public function __construct( PostContent $content, $block_dir ) {
		$this->content   = $content;
		$this->block_dir = $block_dir;
	}

	/**
	 * Registers the block type with WordPress from block.json.
	 */
	public function register_block_type() {
		register_block_type(
			$this->block_dir,
			array(
				'render_callback' => array( $this, 'render' ),
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
	public function render( $attributes = array(), $content = '' ) {
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

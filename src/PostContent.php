<?php
/**
 * Handle post series content.
 *
 * @package MJ/PostSeries
 */

namespace MJ\PostSeries;

defined( 'ABSPATH' ) || exit;

use MJ\PostSeries\Template;

/**
 * PostContent class.
 */
class PostContent {
	/**
	 * Holds the Template Controller class.
	 *
	 * @var Template
	 */
	private $template;

	/**
	 * Constructor.
	 *
	 * @param Template $template Template controller class instance.
	 */
	public function __construct( Template $template ) {
		$this->template = $template;
		$this->init();
	}

	/**
	 * Initialize class features.
	 */
	private function init() {
		add_filter( 'the_content', array( $this, 'render_post_series_box' ) );
	}

	/**
	 * Append/Prepend the series info box to the post content
	 *
	 * @param string $content Post content.
	 * @return string Amended post content.
	 */
	public function render_post_series_box( $content ) {
		global $post;

		if ( ! is_main_query() || empty( $post ) || 'post' !== $post->post_type ) {
			return $content;
		}

		$post_id = absint( $post->ID );
		$series  = get_post_series( $post_id );

		if ( ! $series ) {
			return $content;
		}

		wp_enqueue_script( 'wp-post-series' );

		$term_description      = term_description( $series->term_id, 'post_series' );
		$posts_in_series       = array_values(
			array_map(
				'absint',
				get_posts(
					array(
						'post_type'      => 'post',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'no_found_rows'  => true,
						'orderby'        => 'date',
						'order'          => 'asc',
						'post_status'    => array( 'publish', 'future' ),
						'tax_query'      => array(
							array(
								'taxonomy' => 'post_series',
								'field'    => 'slug',
								'terms'    => $series->slug,
							),
						),
					)
				)
			)
		);
		$post_in_series        = array_search( $post_id, $posts_in_series, true ) + 1;
		$post_series_box_class = 'wp-post-series-box series-' . $series->slug;

		if ( count( $posts_in_series ) > 1 ) {
			$post_series_box_class .= ' wp-post-series-box--expandable';
		}

		ob_start();

		$this->template->get_template(
			'series-box.php',
			array(
				'post_series_id'        => $post_id . '-' . $series->slug,
				'series'                => $series,
				'series_name'           => $this->post_series_name( $series ),
				'description'           => $term_description ? wpautop( wptexturize( $term_description ) ) : '',
				'posts_in_series'       => $posts_in_series,
				'posts_in_series_links' => array_map( array( $this, 'post_series_post_link' ), $posts_in_series ),
				'post_in_series'        => $post_in_series,
				'post_series_box_class' => $post_series_box_class,
				'show_posts_in_series'  => count( $posts_in_series ) > 1,
			)
		);

		$info_box = ob_get_clean();

		// Append or prepend.
		if ( apply_filters( 'wp_post_series_append_info', false ) ) {
			return $content . $info_box;
		}

		return $info_box . $content;
	}

	/**
	 * Render a link to a post in a series.
	 *
	 * @param \WP_Term $term Series term.
	 * @return string
	 */
	protected function post_series_name( $term ) {
		$series_name = esc_html( $term->name );

		if ( apply_filters( 'wp_post_series_enable_archive', false ) ) {
			$series_name = '<a href="' . get_term_link( $term->term_id, 'post_series' ) . '">' . $series_name . '</a>';
		}

		return $series_name;
	}

	/**
	 * Render a link to a post in a series.
	 *
	 * @param int $post_id Post ID to render.
	 * @return string
	 */
	protected function post_series_post_link( $post_id ) {
		$is_current   = get_the_ID() === $post_id;
		$is_published = 'publish' === get_post_status( $post_id );
		$prefix       = '';
		$suffix       = '';

		if ( $is_published && ! $is_current ) {
			$prefix = '<a href="' . get_permalink( $post_id ) . '">';
			$suffix = '</a>';
		} elseif ( $is_current ) {
			$prefix = '<span class="wp-post-series-box__current">';
			$suffix = '</span>';
		}

		$title = get_the_title( $post_id );

		if ( ! $is_published ) {
			$title .= ' <span class="wp-post-series-box__scheduled_text">';
			/* translators: %s scheduled post date */
			$title .= sprintf( __( 'Scheduled for %s', 'wp-post-series' ), get_post_time( get_option( 'date_format' ), false, $post_id, true ) );
			$title .= '</span>';
		}

		return $prefix . $title . $suffix;
	}
}

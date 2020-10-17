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
		add_filter( 'the_content', array( $this, 'filter_the_content' ) );
	}

	/**
	 * Filters the_content hook.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function filter_the_content( $content ) {
		global $post;

		if ( ! is_main_query() || empty( $post ) || 'post' !== $post->post_type ) {
			return $content;
		}

		// Disable automatic insertion if already including the series box e.g. with Gutenberg.
		if ( strstr( $content, 'wp-post-series-box' ) ) {
			return $content;
		}

		$post_id = absint( $post->ID );
		$series  = get_post_series( $post_id );

		if ( ! $series ) {
			return $content;
		}

		$series_html = $this->render_post_series( $post_id, $series );

		// Append or prepend.
		if ( apply_filters( 'wp_post_series_append_info', false ) ) {
			return $content . $series_html;
		}

		return $series_html . $content;
	}

	/**
	 * Render a series.
	 *
	 * @param int      $post_id Current Post ID.
	 * @param \WP_Term $series Series to show.
	 * @param bool     $show_description Whether or not to display the series description.
	 * @param bool     $show_posts Whether or not to display the posts by default, or toggle them.
	 * @return string
	 */
	public function render_post_series( $post_id, $series, $show_description = true, $show_posts = false ) {
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
		$has_multiple_posts    = count( $posts_in_series ) > 1;

		if ( ! $show_posts && $has_multiple_posts ) {
			$post_series_box_class .= ' wp-post-series-box--expandable';
		}

		ob_start();

		$this->template->get_template(
			'series-box.php',
			array(
				'series'                => $series,
				'series_name'           => $this->post_series_name( $series ),
				'series_label'          => $this->post_series_label( $post_id, $series, $posts_in_series ),
				'description'           => $term_description ? wpautop( wptexturize( $term_description ) ) : '',
				'posts_in_series'       => $posts_in_series,
				'posts_in_series_links' => array_map( array( $this, 'post_series_post_link' ), $posts_in_series ),
				'post_in_series'        => $post_in_series,
				'post_series_box_class' => $post_series_box_class,
				'has_multiple_posts'    => count( $posts_in_series ) > 1,
				'show_posts'            => $show_posts,
				'show_description'      => $show_description && $term_description,
			)
		);

		return ob_get_clean();
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
	 * Render the label for the series; this takes the current post into consideration.
	 *
	 * @param int      $post_id Current post ID.
	 * @param \WP_Term $term Series term.
	 * @param array    $posts_in_series List of posts in the series.
	 * @return string
	 */
	protected function post_series_label( $post_id, $term, $posts_in_series ) {
		$series_name    = $this->post_series_name( $term );
		$post_in_series = array_search( $post_id, $posts_in_series, true );

		if ( false === $post_in_series ) {
			return sprintf(
				/* translators: %s series name/link */
				__( 'Series: <em>&ldquo;%s&rdquo;</em>', 'wp-post-series' ),
				$series_name
			);
		}

		return sprintf(
			/* translators: %1$d Post index, %2$d number of posts in series, %3$s series name/link */
			__( 'This is post %1$d of %2$d in the series <em>&ldquo;%3$s&rdquo;</em>', 'wp-post-series' ),
			$post_in_series + 1,
			count( $posts_in_series ),
			$series_name
		);
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

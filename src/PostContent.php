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
	 * IDs of posts the Post Series List block has already rendered a box for during this request.
	 *
	 * @var array<int, true>
	 */
	private $block_rendered_post_ids = array();

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
		// After wpautop (10) and shortcodes (11). At 10, the series box ran through wpautop whenever core had
		// re-added it after rendering a block post earlier on the page (e.g. archives mixing block and classic posts).
		add_filter( 'the_content', array( $this, 'filter_the_content' ), 12 );
	}

	/**
	 * Filters the_content hook.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function filter_the_content( $content ) {
		global $post;

		if ( ! is_main_query() || empty( $post ) || ! in_array( $post->post_type, get_series_post_types(), true ) ) {
			return $content;
		}

		// Auto-generated excerpts run the_content; keep the series box out of them.
		if ( doing_filter( 'get_the_excerpt' ) ) {
			return $content;
		}

		$post_id = absint( $post->ID );

		// The block already shows the series for this post, e.g. in the post content, a template part or a widget.
		if ( isset( $this->block_rendered_post_ids[ $post_id ] ) || $this->current_template_has_block() ) {
			return $content;
		}

		$series = get_post_series( $post_id );

		/**
		 * Filters whether to automatically add the series box to the post content.
		 *
		 * @param bool           $auto_insert Whether to add the series box. Default true.
		 * @param int            $post_id     Post ID.
		 * @param \WP_Term|false $series      The post's series, or false if it has none.
		 */
		if ( ! $series || ! apply_filters( 'wp_post_series_auto_insert', true, $post_id, $series ) ) {
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
	 * Record that the Post Series List block rendered a series box for a post, so it isn't added again.
	 *
	 * @param int $post_id Post ID.
	 */
	public function mark_block_rendered( $post_id ) {
		$this->block_rendered_post_ids[ absint( $post_id ) ] = true;
	}

	/**
	 * Whether the block template being rendered (block themes) contains the Post Series List block. The block may
	 * render after the post content, so it can't be detected by the time the_content runs.
	 *
	 * @return bool
	 */
	protected function current_template_has_block() {
		global $_wp_current_template_content;

		return is_string( $_wp_current_template_content ) && has_block( 'mj/wp-post-series', $_wp_current_template_content );
	}

	/**
	 * Render a series.
	 *
	 * @param int      $post_id Current Post ID.
	 * @param \WP_Term $series Series to show.
	 * @param string   $class_name Custom classname.
	 * @param bool     $show_description Whether or not to display the series description.
	 * @param bool     $show_posts Whether or not to display the posts by default, or toggle them.
	 * @return string
	 */
	public function render_post_series( $post_id, $series, $class_name = '', $show_description = true, $show_posts = false ) {
		wp_enqueue_script( 'wp-post-series' );

		// Feed readers don't load the stylesheet, so list the posts without the toggle.
		if ( is_feed() ) {
			$show_posts = true;
		}

		$term_description = term_description( $series->term_id );

		// Query full post objects (not IDs) so they are cached for the title/permalink/status lookups below.
		$series_posts          = get_posts(
			array(
				'post_type'              => get_series_post_types(),
				'posts_per_page'         => -1,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'orderby'                => 'date',
				'order'                  => 'asc',
				'post_status'            => array( 'publish', 'future' ),
				'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Querying by series is the point of the plugin.
					array(
						'taxonomy' => 'post_series',
						'field'    => 'slug',
						'terms'    => $series->slug,
					),
				),
			)
		);
		$posts_in_series       = wp_list_pluck( $series_posts, 'ID' );
		$post_index            = array_search( $post_id, $posts_in_series, true );
		$post_in_series        = false === $post_index ? 0 : $post_index + 1;
		$post_series_box_class = trim( 'wp-post-series-box series-' . $series->slug . ' ' . $class_name );
		$has_multiple_posts    = count( $posts_in_series ) > 1;
		$is_expandable         = ! $show_posts && $has_multiple_posts;

		if ( $is_expandable ) {
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
				'has_multiple_posts'    => $has_multiple_posts,
				'is_expandable'         => $is_expandable,
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
			$term_link = get_term_link( (int) $term->term_id, 'post_series' );

			if ( ! is_wp_error( $term_link ) ) {
				$series_name = '<a href="' . esc_url( $term_link ) . '">' . $series_name . '</a>';
			}
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
				__( 'Series: <em>%s</em>', 'wp-post-series' ),
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
			$prefix = '<a href="' . esc_url( get_permalink( $post_id ) ) . '">';
			$suffix = '</a>';
		} elseif ( $is_current ) {
			$prefix = '<span class="wp-post-series-box__current">';
			$suffix = '</span>';
		}

		$title = get_the_title( $post_id );

		if ( '' === trim( wp_strip_all_tags( $title ) ) ) {
			$title = __( '(no title)', 'wp-post-series' );
		}

		if ( ! $is_published ) {
			$title .= ' <span class="wp-post-series-box__scheduled_text">';
			/* translators: %s scheduled post date */
			$title .= sprintf( __( 'Scheduled for %s', 'wp-post-series' ), get_post_time( get_option( 'date_format' ), false, $post_id, true ) );
			$title .= '</span>';
		}

		return $prefix . $title . $suffix;
	}
}

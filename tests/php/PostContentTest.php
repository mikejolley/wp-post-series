<?php
/**
 * Tests for the automatically inserted series box.
 *
 * @package MJ/PostSeries
 */

namespace MJ\PostSeries\Tests;

use WP_Term;

/**
 * PostContent tests.
 */
class PostContentTest extends TestCase {
	/**
	 * Series used by most tests.
	 *
	 * @var WP_Term
	 */
	private $series;

	/**
	 * Post IDs in the series, in order.
	 *
	 * @var int[]
	 */
	private $post_ids;

	/**
	 * Set up a three-post series.
	 */
	public function set_up() {
		parent::set_up();
		$this->series   = $this->create_series();
		$this->post_ids = $this->create_series_posts( $this->series, 3 );
	}

	public function test_series_box_is_prepended_to_post_content() {
		$this->go_to_post( $this->post_ids[1] );

		$content = apply_filters( 'the_content', get_post()->post_content );

		$this->assertStringStartsWith( '<div class="wp-post-series-box series-learn-php', trim( $content ) );
		$this->assertStringContainsString( 'This is post 2 of 3 in the series', $content );
		$this->assertStringContainsString( 'Body of part 2.', $content );
	}

	public function test_series_box_is_appended_when_filtered() {
		add_filter( 'wp_post_series_append_info', '__return_true' );
		$this->go_to_post( $this->post_ids[1] );

		$content = apply_filters( 'the_content', get_post()->post_content );

		$this->assertGreaterThan( strpos( $content, 'Body of part 2.' ), strpos( $content, 'wp-post-series-box' ) );
	}

	public function test_post_without_series_is_unchanged() {
		$post_id = self::factory()->post->create( array( 'post_content' => 'Standalone.' ) );
		$this->go_to_post( $post_id );

		$this->assertStringNotContainsString( 'wp-post-series-box', apply_filters( 'the_content', get_post()->post_content ) );
	}

	public function test_pages_are_unchanged() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_content' => 'A page.',
			)
		);
		$this->go_to( get_permalink( $page_id ) );
		the_post();

		$this->assertStringNotContainsString( 'wp-post-series-box', apply_filters( 'the_content', get_post()->post_content ) );
	}

	public function test_series_box_is_not_duplicated_when_post_contains_the_block() {
		$post_id = self::factory()->post->create( array( 'post_content' => '<!-- wp:mj/wp-post-series /-->' ) );
		wp_set_post_terms( $post_id, array( $this->series->term_id ), 'post_series' );
		$this->go_to_post( $post_id );

		$content = apply_filters( 'the_content', get_post()->post_content );

		$this->assertSame( 1, substr_count( $content, 'class="wp-post-series-box ' ) );
	}

	public function test_series_box_is_not_reformatted_after_a_block_post_on_the_same_page() {
		// Rendering block content makes core re-add wpautop at priority 10, after any callback already there.
		$block_post_id = self::factory()->post->create( array( 'post_content' => '<!-- wp:paragraph --><p>Blocks.</p><!-- /wp:paragraph -->' ) );
		$this->go_to_post( $block_post_id );
		apply_filters( 'the_content', get_post()->post_content );

		$this->go_to_post( $this->post_ids[1] );
		$content = apply_filters( 'the_content', get_post()->post_content );

		$this->assertSame( 1, substr_count( $content, 'class="wp-post-series-box ' ) );
		$this->assertStringNotContainsString( '<br />', $content );
		$this->assertDoesNotMatchRegularExpression( '/<p>\s*<label/', $content );
		$this->assertStringContainsString( '<p>Body of part 2.</p>', $content, 'The post content itself is still formatted.' );
	}

	public function test_series_box_is_not_added_when_the_block_template_contains_the_block() {
		global $_wp_current_template_content;

		$_wp_current_template_content = '<!-- wp:mj/wp-post-series /--><!-- wp:post-content /-->';
		$this->go_to_post( $this->post_ids[1] );
		$content                      = apply_filters( 'the_content', get_post()->post_content );
		$_wp_current_template_content = null;

		$this->assertStringNotContainsString( 'wp-post-series-box', $content );
	}

	public function test_series_box_is_not_added_after_the_block_rendered_for_the_post() {
		$this->go_to_post( $this->post_ids[1] );
		// E.g. the block in a template part or widget area rendered before the post content.
		do_blocks( '<!-- wp:mj/wp-post-series /-->' );

		$this->assertStringNotContainsString( 'wp-post-series-box', apply_filters( 'the_content', get_post()->post_content ) );
	}

	public function test_series_box_is_still_added_when_content_mentions_its_class() {
		$post_id = self::factory()->post->create( array( 'post_content' => '<code>.wp-post-series-box { color: red; }</code>' ) );
		wp_set_post_terms( $post_id, array( $this->series->term_id ), 'post_series' );
		$this->go_to_post( $post_id );

		$this->assertSame( 1, substr_count( apply_filters( 'the_content', get_post()->post_content ), 'class="wp-post-series-box ' ) );
	}

	public function test_automatic_insertion_can_be_disabled() {
		add_filter( 'wp_post_series_auto_insert', '__return_false' );
		$this->go_to_post( $this->post_ids[1] );

		$this->assertStringNotContainsString( 'wp-post-series-box', apply_filters( 'the_content', get_post()->post_content ) );
	}

	public function test_untitled_posts_are_listed_with_a_placeholder_title() {
		$untitled_id = self::factory()->post->create(
			array(
				'post_title' => '',
				'post_date'  => gmdate( 'Y-m-d H:i:s' ),
			)
		);
		wp_set_post_terms( $untitled_id, array( $this->series->term_id ), 'post_series' );

		$html = $this->post_content()->render_post_series( $this->post_ids[0], $this->series );

		$this->assertStringContainsString( '<a href="' . esc_url( get_permalink( $untitled_id ) ) . '">(no title)</a>', $html );
	}

	public function test_feeds_list_posts_without_the_toggle() {
		$this->go_to( get_feed_link() );

		$html = $this->post_content()->render_post_series( $this->post_ids[0], $this->series );

		$this->assertStringNotContainsString( 'type="checkbox"', $html );
		$this->assertStringNotContainsString( 'Show all posts in this series', $html );
		$this->assertStringContainsString( 'Learn PHP part 2', $html );
	}

	public function test_series_box_is_not_added_to_generated_excerpts() {
		$this->go_to_post( $this->post_ids[1] );

		$excerpt = get_the_excerpt();

		$this->assertStringNotContainsString( 'This is post', $excerpt );
		$this->assertStringContainsString( 'Body of part 2.', $excerpt );
	}

	public function test_current_post_is_highlighted_and_others_are_linked() {
		$this->go_to_post( $this->post_ids[1] );

		$html = $this->post_content()->render_post_series( $this->post_ids[1], $this->series );

		$this->assertStringContainsString( '<span class="wp-post-series-box__current">Learn PHP part 2</span>', $html );
		$this->assertStringContainsString( '<a href="' . esc_url( get_permalink( $this->post_ids[0] ) ) . '">Learn PHP part 1</a>', $html );
	}

	public function test_scheduled_posts_are_listed_without_a_link() {
		$future_id = self::factory()->post->create(
			array(
				'post_title'  => 'Coming soon',
				'post_status' => 'future',
				'post_date'   => gmdate( 'Y-m-d H:i:s', strtotime( '+5 days' ) ),
			)
		);
		wp_set_post_terms( $future_id, array( $this->series->term_id ), 'post_series' );
		$this->go_to_post( $this->post_ids[0] );

		$html = $this->post_content()->render_post_series( $this->post_ids[0], $this->series );

		$this->assertStringContainsString( 'Coming soon <span class="wp-post-series-box__scheduled_text">Scheduled for', $html );
		$this->assertStringNotContainsString( get_permalink( $future_id ), $html );
		$this->assertStringContainsString( 'of 4 in the series', $html );
	}

	public function test_series_query_count_does_not_grow_with_series_size() {
		global $wpdb;

		$this->set_permalink_structure( '/%category%/%postname%/' );
		$large_series = $this->create_series( 'Large series' );
		$large_ids    = $this->create_series_posts( $large_series, 12 );

		$count_queries = function ( $post_id, $series ) use ( $wpdb ) {
			wp_cache_flush();
			$before = $wpdb->num_queries;
			$this->post_content()->render_post_series( $post_id, $series );
			return $wpdb->num_queries - $before;
		};

		// Warm up first; the first render does one-off work unrelated to series size.
		$count_queries( $this->post_ids[0], $this->series );

		$this->assertSame( $count_queries( $this->post_ids[0], $this->series ), $count_queries( $large_ids[0], $large_series ) );
	}

	public function test_series_name_links_to_archive_when_enabled() {
		add_filter( 'wp_post_series_enable_archive', '__return_true' );

		$html = $this->post_content()->render_post_series( $this->post_ids[0], $this->series );

		$this->assertStringContainsString( '<a href="' . esc_url( get_term_link( $this->series ) ) . '">Learn PHP</a>', $html );
	}

	public function test_series_name_is_not_linked_when_term_link_fails() {
		add_filter( 'wp_post_series_enable_archive', '__return_true' );
		$missing_term = new WP_Term(
			(object) array(
				'term_id'  => PHP_INT_MAX,
				'name'     => 'Deleted series',
				'slug'     => 'deleted-series',
				'taxonomy' => 'post_series',
			)
		);

		$html = $this->post_content()->render_post_series( $this->post_ids[0], $missing_term );

		$this->assertStringContainsString( 'Series: <em>Deleted series</em>', $html );
	}

	public function test_post_in_series_template_variable() {
		add_filter(
			'wp_post_series_locate_template',
			static function () {
				return __DIR__ . '/fixtures/post-in-series.php';
			}
		);
		$other_post_id = self::factory()->post->create();

		$this->assertSame( '2', $this->post_content()->render_post_series( $this->post_ids[1], $this->series ) );
		$this->assertSame( '0', $this->post_content()->render_post_series( $other_post_id, $this->series ), 'A post outside the series has no position.' );
	}
}

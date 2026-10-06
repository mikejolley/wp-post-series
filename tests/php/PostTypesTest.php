<?php
/**
 * Tests for using series with other post types.
 *
 * @package MJ/PostSeries
 */

namespace MJ\PostSeries\Tests;

use MJ\PostSeries\TaxonomyController;

/**
 * Post type support tests.
 */
class PostTypesTest extends TestCase {
	/**
	 * Register a custom post type and opt it in to series.
	 */
	public function set_up() {
		parent::set_up();

		register_post_type(
			'book',
			array(
				'public'       => true,
				'show_in_rest' => true,
			)
		);
		add_filter( 'wp_post_series_post_types', array( $this, 'add_book_post_type' ) );
		$this->controller()->register_taxonomies();
	}

	/**
	 * Restore the default post types.
	 */
	public function tear_down() {
		remove_filter( 'wp_post_series_post_types', array( $this, 'add_book_post_type' ) );
		_unregister_post_type( 'book' );
		$this->controller()->register_taxonomies();
		parent::tear_down();
	}

	/**
	 * Filter callback adding the book post type.
	 *
	 * @param string[] $post_types Post types.
	 * @return string[]
	 */
	public function add_book_post_type( $post_types ) {
		$post_types[] = 'book';
		return $post_types;
	}

	/**
	 * Get the plugin's TaxonomyController instance.
	 *
	 * @return TaxonomyController
	 */
	private function controller() {
		return \MJ\PostSeries\init()->get( TaxonomyController::class );
	}

	public function test_posts_only_by_default() {
		remove_filter( 'wp_post_series_post_types', array( $this, 'add_book_post_type' ) );

		$this->assertSame( array( 'post' ), \MJ\PostSeries\get_series_post_types() );
	}

	public function test_taxonomy_is_registered_for_filtered_post_types() {
		$this->assertSame( array( 'post', 'book' ), get_taxonomy( 'post_series' )->object_type );
	}

	public function test_series_box_is_added_to_custom_post_types() {
		$series  = $this->create_series();
		$post_id = $this->create_series_posts( $series, 1 )[0];
		$book_id = self::factory()->post->create(
			array(
				'post_type'    => 'book',
				'post_content' => 'Book body.',
				'post_date'    => gmdate( 'Y-m-d H:i:s' ),
			)
		);
		wp_set_post_terms( $book_id, array( $series->term_id ), 'post_series' );
		$this->go_to( get_permalink( $book_id ) );
		the_post();

		$content = apply_filters( 'the_content', get_post()->post_content );

		$this->assertStringContainsString( 'This is post 2 of 2 in the series', $content );
		$this->assertStringContainsString( get_permalink( $post_id ), $content, 'Series lists posts of every supported type.' );
	}

	public function test_unsupported_post_types_are_unchanged() {
		register_post_type( 'movie', array( 'public' => true ) );
		$movie_id = self::factory()->post->create(
			array(
				'post_type'    => 'movie',
				'post_content' => 'Movie body.',
			)
		);
		$this->go_to( get_permalink( $movie_id ) );
		the_post();

		$this->assertStringNotContainsString( 'wp-post-series-box', apply_filters( 'the_content', get_post()->post_content ) );
		_unregister_post_type( 'movie' );
	}

	public function test_admin_list_table_supports_custom_post_types() {
		$series  = $this->create_series();
		$book_id = self::factory()->post->create( array( 'post_type' => 'book' ) );
		wp_set_post_terms( $book_id, array( $series->term_id ), 'post_series' );

		$columns = apply_filters( 'manage_posts_columns', array( 'title' => 'Title' ), 'book' );
		$this->assertArrayHasKey( 'post_series', $columns );
		$this->assertArrayNotHasKey( 'post_series', apply_filters( 'manage_posts_columns', array( 'title' => 'Title' ), 'movie' ) );

		$cell = get_echo( 'do_action', array( 'manage_posts_custom_column', 'post_series', $book_id ) );
		$this->assertSame( '<a href="' . esc_url( admin_url( 'edit.php?post_series=learn-php&post_type=book' ) ) . '">Learn PHP</a>', $cell );

		$filter = get_echo( 'do_action', array( 'restrict_manage_posts', 'book', 'top' ) );
		$this->assertStringContainsString( '<select name="post_series">', $filter );
		$this->assertSame( '', get_echo( 'do_action', array( 'restrict_manage_posts', 'movie', 'top' ) ) );
	}
}

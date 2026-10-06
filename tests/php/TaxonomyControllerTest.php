<?php
/**
 * Tests for the post_series taxonomy and admin UI.
 *
 * @package MJ/PostSeries
 */

namespace MJ\PostSeries\Tests;

use MJ\PostSeries\TaxonomyController;

/**
 * TaxonomyController tests.
 */
class TaxonomyControllerTest extends TestCase {
	/**
	 * Get the plugin's TaxonomyController instance.
	 *
	 * @return TaxonomyController
	 */
	private function controller() {
		return \MJ\PostSeries\init()->get( TaxonomyController::class );
	}

	public function test_taxonomy_is_registered_for_posts_and_rest() {
		$taxonomy = get_taxonomy( 'post_series' );

		$this->assertNotFalse( $taxonomy );
		$this->assertSame( array( 'post' ), $taxonomy->object_type );
		$this->assertTrue( $taxonomy->show_in_rest );
		$this->assertFalse( $taxonomy->hierarchical );
	}

	public function test_taxonomy_is_named_series_without_changing_its_key() {
		$taxonomy = get_taxonomy( 'post_series' );

		$this->assertSame( 'Series', $taxonomy->labels->name );
		$this->assertSame( 'Series', $taxonomy->labels->singular_name );
		$this->assertSame( 'Edit Series', $taxonomy->labels->edit_item );
		$this->assertSame( 'post_series', $taxonomy->rest_base ? $taxonomy->rest_base : $taxonomy->name );
		$this->assertSame( 'post_series', $taxonomy->query_var );
	}

	public function test_labels_do_not_fall_back_to_tag_wording() {
		$labels = (array) get_taxonomy( 'post_series' )->labels;

		foreach ( $labels as $key => $label ) {
			$this->assertDoesNotMatchRegularExpression( '/\btags?\b/i', (string) $label, "Label {$key} mentions tags." );
		}
		$this->assertSame( '&larr; Go to Series', $labels['back_to_items'] );
	}

	public function test_series_column_is_added_after_categories() {
		$columns = apply_filters(
			'manage_posts_columns',
			array(
				'title'      => 'Title',
				'categories' => 'Categories',
				'date'       => 'Date',
			),
			'post'
		);

		$this->assertSame( array( 'title', 'categories', 'post_series', 'date' ), array_keys( $columns ) );
	}

	public function test_series_column_is_added_without_a_categories_column() {
		$this->assertSame( array( 'title', 'post_series' ), array_keys( $this->controller()->add_post_series_column( array( 'title' => 'Title' ) ) ) );
		$this->assertSame( array( 'post_series' ), array_keys( $this->controller()->add_post_series_column( array() ) ) );
		$this->assertSame( array( 'post_series' ), array_keys( $this->controller()->add_post_series_column( null ) ) );
	}

	public function test_meta_box_for_post_without_series() {
		$this->create_series();
		$post = self::factory()->post->create_and_get();

		$html = get_echo( array( $this->controller(), 'post_series_meta_box' ), array( $post ) );

		$this->assertStringContainsString( 'name="tax_input[post_series]"', $html );
		$this->assertStringContainsString( '<option value="learn-php" >Learn PHP</option>', $html );
	}

	public function test_meta_box_selects_current_series() {
		$series  = $this->create_series();
		$post_id = $this->create_series_posts( $series, 1 )[0];

		$html = get_echo( array( $this->controller(), 'post_series_meta_box' ), array( get_post( $post_id ) ) );

		$this->assertStringContainsString( "<option value=\"learn-php\"  selected='selected'>Learn PHP</option>", $html );
	}

	public function test_series_column_content() {
		global $post;

		$series     = $this->create_series();
		$post       = get_post( $this->create_series_posts( $series, 1 )[0] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- The column callback reads the global post.
		$no_series  = self::factory()->post->create_and_get();
		$get_output = function () {
			return get_echo( array( $this->controller(), 'post_series_column_content' ), array( 'post_series' ) );
		};

		$this->assertSame( '<a href="' . esc_url( admin_url( 'edit.php?post_series=learn-php' ) ) . '">Learn PHP</a>', $get_output() );

		$post = $no_series; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$this->assertSame( 'N/A', $get_output() );
	}

	public function test_posts_list_series_filter() {
		global $typenow;

		$series = $this->create_series();
		$this->create_series_posts( $series, 1 );
		$typenow                 = 'post'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$_REQUEST['post_series'] = 'learn-php';

		$html = get_echo( array( $this->controller(), 'filter_posts_by_series' ) );

		unset( $_REQUEST['post_series'] );

		$this->assertStringContainsString( '<select name="post_series">', $html );
		$this->assertStringContainsString( "<option value=\"learn-php\"  selected='selected'>Learn PHP</option>", $html );
	}
}

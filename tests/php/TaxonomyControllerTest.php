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

	public function test_series_column_is_added_after_categories() {
		$columns = apply_filters(
			'manage_edit-post_columns', // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Core hook name.
			array(
				'title'      => 'Title',
				'categories' => 'Categories',
				'date'       => 'Date',
			)
		);

		$this->assertSame( array( 'title', 'categories', 'post_series', 'date' ), array_keys( $columns ) );
	}

	public function test_series_column_is_added_when_categories_column_is_missing() {
		$columns = $this->controller()->add_post_series_column(
			array(
				'title' => 'Title',
				'date'  => 'Date',
			)
		);

		$this->assertArrayHasKey( 'post_series', $columns );
	}

	public function test_series_column_handles_empty_and_invalid_columns() {
		$this->assertSame( array( 'post_series' ), array_keys( $this->controller()->add_post_series_column( array() ) ) );
		$this->assertSame( array( 'post_series' ), array_keys( $this->controller()->add_post_series_column( null ) ) );
	}

	public function test_meta_box_for_post_without_series() {
		$this->create_series();
		$post = self::factory()->post->create_and_get();

		ob_start();
		$this->controller()->post_series_meta_box( $post );
		$html = ob_get_clean();

		$this->assertStringContainsString( 'name="tax_input[post_series]"', $html );
		$this->assertStringContainsString( '<option value="learn-php" >Learn PHP</option>', $html );
	}

	public function test_meta_box_selects_current_series() {
		$series  = $this->create_series();
		$post_id = $this->create_series_posts( $series, 1 )[0];

		ob_start();
		$this->controller()->post_series_meta_box( get_post( $post_id ) );
		$html = ob_get_clean();

		$this->assertStringContainsString( "<option value=\"learn-php\"  selected='selected'>Learn PHP</option>", $html );
	}

	public function test_series_column_content() {
		global $post;

		$series     = $this->create_series();
		$post       = get_post( $this->create_series_posts( $series, 1 )[0] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- The column callback reads the global post.
		$no_series  = self::factory()->post->create_and_get();
		$get_output = function () {
			ob_start();
			$this->controller()->post_series_column_content( 'post_series' );
			return ob_get_clean();
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

		ob_start();
		$this->controller()->filter_posts_by_series();
		$html = ob_get_clean();

		unset( $_REQUEST['post_series'] );

		$this->assertStringContainsString( '<select name="post_series">', $html );
		$this->assertStringContainsString( "<option value=\"learn-php\"  selected='selected'>Learn PHP</option>", $html );
	}
}

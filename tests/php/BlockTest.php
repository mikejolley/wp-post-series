<?php
/**
 * Tests for the Post Series List block.
 *
 * @package MJ/PostSeries
 */

namespace MJ\PostSeries\Tests;

use WP_Block_Type_Registry;

/**
 * Block tests.
 */
class BlockTest extends TestCase {
	public function test_block_is_registered_from_block_json() {
		$block = WP_Block_Type_Registry::get_instance()->get_registered( 'mj/wp-post-series' );

		$this->assertNotNull( $block );
		$this->assertSame( 3, $block->api_version );
		$this->assertNotEmpty( $block->editor_script_handles );
		$this->assertSame( array( 'wp-post-series' ), $block->view_script_handles );
		$this->assertSame( array( 'wp-post-series' ), $block->style_handles );
		$this->assertTrue( is_callable( $block->render_callback ) );
	}

	public function test_block_is_distinct_from_the_core_terms_block_for_series() {
		$block = WP_Block_Type_Registry::get_instance()->get_registered( 'mj/wp-post-series' );
		$terms = wp_list_filter( WP_Block_Type_Registry::get_instance()->get_registered( 'core/post-terms' )->get_variations(), array( 'name' => 'post_series' ) );

		$this->assertSame( 'Post Series List', $block->title );
		$this->assertSame( 'Series', current( $terms )['title'] );
		$this->assertContains( 'series', $block->keywords );
		$this->assertContains( 'navigation', $block->keywords );
	}

	public function test_renders_selected_series_on_a_page() {
		$series = $this->create_series();
		$this->create_series_posts( $series, 2 );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$this->go_to( get_permalink( $page_id ) );
		the_post();

		$html = do_blocks( '<!-- wp:mj/wp-post-series {"series":"learn-php"} /-->' );

		$this->assertStringContainsString( 'Series: <em>Learn PHP</em>', $html );
		$this->assertTrue( wp_script_is( 'wp-post-series', 'enqueued' ) );
	}

	public function test_renders_current_post_series_by_default() {
		$series   = $this->create_series();
		$post_ids = $this->create_series_posts( $series, 2 );
		$this->go_to_post( $post_ids[1] );

		$html = do_blocks( '<!-- wp:mj/wp-post-series /-->' );

		$this->assertStringContainsString( 'This is post 2 of 2 in the series', $html );
	}

	public function test_renders_nothing_without_a_series() {
		$post_id = self::factory()->post->create();
		$this->go_to_post( $post_id );

		$this->assertSame( '', do_blocks( '<!-- wp:mj/wp-post-series /-->' ) );
		$this->assertSame( '', do_blocks( '<!-- wp:mj/wp-post-series {"series":"does-not-exist"} /-->' ) );
	}

	public function test_preview_id_selects_series_for_editor_preview() {
		$series  = $this->create_series( 'Preview series' );
		$post_id = self::factory()->post->create();
		$this->create_series_posts( $series, 2 );
		$this->go_to_post( $post_id );

		$html = do_blocks( '<!-- wp:mj/wp-post-series {"previewId":' . $series->term_id . '} /-->' );

		$this->assertStringContainsString( 'Preview series', $html );
	}

	public function test_display_attributes() {
		$series = $this->create_series();
		$this->create_series_posts( $series, 2 );

		$collapsed = do_blocks( '<!-- wp:mj/wp-post-series {"series":"learn-php","className":"is-custom"} /-->' );
		$this->assertStringContainsString( 'wp-post-series-box--expandable', $collapsed );
		$this->assertStringContainsString( 'is-custom', $collapsed );
		$this->assertStringContainsString( 'wp-post-series-box__description', $collapsed );

		$expanded = do_blocks( '<!-- wp:mj/wp-post-series {"series":"learn-php","showPosts":true,"showDescription":false} /-->' );
		$this->assertStringNotContainsString( 'wp-post-series-box--expandable', $expanded );
		$this->assertStringNotContainsString( 'wp-post-series-box__toggle_checkbox', $expanded );
		$this->assertStringNotContainsString( 'wp-post-series-box__description', $expanded );
	}
}

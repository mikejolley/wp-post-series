<?php
/**
 * Backwards compatibility with 2.0.0.
 *
 * @package MJ/PostSeries
 */

namespace MJ\PostSeries\Tests;

/**
 * Backwards compatibility tests.
 */
class BackCompatTest extends TestCase {
	public function test_theme_override_of_2_0_0_template_still_renders() {
		add_filter(
			'wp_post_series_locate_template',
			static function () {
				return __DIR__ . '/fixtures/series-box-2.0.0.php';
			}
		);
		$series   = $this->create_series();
		$post_ids = $this->create_series_posts( $series, 3 );
		$this->go_to_post( $post_ids[1] );

		$html = $this->post_content()->render_post_series( $post_ids[1], $series );

		$this->assertStringContainsString( 'class="wp-post-series-box__toggle_checkbox" type="checkbox"', $html );
		$this->assertMatchesRegularExpression( '/<label\s+class="wp-post-series-box__label"\s+for="collapsible-series-learn-php[^"]+"\s+tabindex="0"/', $html );
		$this->assertStringContainsString( 'This is post 2 of 3 in the series', $html );
		$this->assertStringContainsString( '<span class="wp-post-series-box__current">Learn PHP part 2</span>', $html );
	}

	public function test_public_functions_and_hooks() {
		$series  = $this->create_series();
		$post_id = $this->create_series_posts( $series, 1 )[0];

		$this->assertSame( $series->term_id, \MJ\PostSeries\get_post_series( $post_id )->term_id );
		$this->assertFalse( \MJ\PostSeries\get_post_series( self::factory()->post->create() ) );
		$this->assertInstanceOf( \MJ\PostSeries\Registry\Container::class, \MJ\PostSeries\init() );
		$this->assertSame( 20, has_action( 'plugins_loaded', 'MJ\PostSeries\init' ) );
	}

	public function test_block_markup_saved_by_2_0_0_still_renders() {
		$series = $this->create_series();
		$this->create_series_posts( $series, 2 );

		$html = do_blocks( '<!-- wp:mj/wp-post-series {"series":"learn-php","showDescription":false,"showPosts":true,"className":"is-style-custom"} /-->' );

		$this->assertStringContainsString( 'wp-post-series-box series-learn-php is-style-custom', $html );
		$this->assertStringNotContainsString( 'wp-post-series-box__description', $html );
		$this->assertStringNotContainsString( 'wp-post-series-box--expandable', $html );
	}

	public function test_frontend_handles_are_unchanged() {
		$this->assertTrue( wp_style_is( 'wp-post-series', 'registered' ) );
		$this->assertTrue( wp_script_is( 'wp-post-series', 'registered' ) );
		$this->assertStringEndsWith( '/build/post-series.css', wp_styles()->registered['wp-post-series']->src );
		$this->assertStringEndsWith( '/build/frontend.js', wp_scripts()->registered['wp-post-series']->src );
	}
}

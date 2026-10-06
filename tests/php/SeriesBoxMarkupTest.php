<?php
/**
 * Tests for the series box markup.
 *
 * @package MJ/PostSeries
 */

namespace MJ\PostSeries\Tests;

use DOMDocument;
use DOMXPath;

/**
 * Series box markup tests.
 */
class SeriesBoxMarkupTest extends TestCase {
	/**
	 * Render a series box and load it into a DOM.
	 *
	 * @param int  $post_count Posts in the series.
	 * @param bool $show_posts Whether posts are always shown.
	 * @return DOMXPath
	 */
	private function render_box( $post_count, $show_posts = false ) {
		add_filter( 'wp_post_series_enable_archive', '__return_true' );
		$series   = $this->create_series( 'Learn PHP', "First paragraph.\n\nSecond paragraph." );
		$post_ids = $this->create_series_posts( $series, $post_count );
		$html     = $this->post_content()->render_post_series( $post_ids[0], $series, '', true, $show_posts );

		$dom = new DOMDocument();
		$dom->loadHTML( '<?xml encoding="utf-8"?><body>' . $html . '</body>', LIBXML_NOERROR );

		return new DOMXPath( $dom );
	}

	public function test_labels_only_contain_phrasing_content() {
		$xpath = $this->render_box( 3 );

		$this->assertSame( 0, $xpath->query( '//label//p | //label//div | //label//ol' )->length, 'Labels may only contain phrasing content.' );
		$this->assertSame( 1, $xpath->query( '//div[contains(@class, "wp-post-series-box__label")]/p[contains(@class, "wp-post-series-box__name")]' )->length );
		$this->assertSame( 2, $xpath->query( '//div[contains(@class, "wp-post-series-box__description")]/p' )->length );
	}

	public function test_expandable_box_has_an_accessible_toggle() {
		$xpath    = $this->render_box( 3 );
		$checkbox = $xpath->query( '//input[@type="checkbox"]' )->item( 0 );

		$this->assertNotNull( $checkbox );
		$this->assertFalse( $checkbox->hasAttribute( 'tabindex' ) );

		$posts = $xpath->query( '//div[contains(@class, "wp-post-series-box__posts")]' )->item( 0 );
		$this->assertSame( $posts->getAttribute( 'id' ), $checkbox->getAttribute( 'aria-controls' ) );

		$label = $xpath->query( '//label[@for="' . $checkbox->getAttribute( 'id' ) . '"]' )->item( 0 );
		$this->assertNotNull( $label, 'The checkbox needs a label.' );
		$this->assertSame( 'Show all posts in this series', trim( $label->textContent ) ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOMNode property.
		$this->assertSame( 0, $xpath->query( '//*[@tabindex]' )->length, 'Only native controls should be focusable.' );
	}

	public function test_series_link_is_outside_the_toggle_label() {
		$xpath = $this->render_box( 3 );

		$this->assertSame( 1, $xpath->query( '//p[contains(@class, "wp-post-series-box__name")]//a' )->length );
		$this->assertSame( 0, $xpath->query( '//label//a' )->length );
	}

	public function test_single_post_series_has_no_toggle() {
		$xpath = $this->render_box( 1 );

		$this->assertSame( 0, $xpath->query( '//input | //label' )->length );
		$this->assertSame( 0, $xpath->query( '//div[contains(@class, "wp-post-series-box__posts")]' )->length );
	}

	public function test_always_visible_posts_have_no_toggle() {
		$xpath = $this->render_box( 3, true );

		$this->assertSame( 0, $xpath->query( '//input | //label' )->length );
		$this->assertSame( 1, $xpath->query( '//div[contains(@class, "wp-post-series-box__posts")]' )->length );
	}

	public function test_toggle_ids_are_unique_per_box() {
		$series   = $this->create_series();
		$post_ids = $this->create_series_posts( $series, 2 );

		$first  = $this->post_content()->render_post_series( $post_ids[0], $series );
		$second = $this->post_content()->render_post_series( $post_ids[0], $series );

		preg_match( '/id="([^"]+)"/', $first, $first_id );
		preg_match( '/id="([^"]+)"/', $second, $second_id );

		$this->assertNotSame( $first_id[1], $second_id[1] );
	}
}

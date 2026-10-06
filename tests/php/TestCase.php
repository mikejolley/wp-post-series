<?php
/**
 * Shared helpers for plugin tests.
 *
 * @package MJ/PostSeries
 */

namespace MJ\PostSeries\Tests;

use MJ\PostSeries\PostContent;
use WP_Term;
use WP_UnitTestCase;

/**
 * Base test case.
 */
abstract class TestCase extends WP_UnitTestCase {
	/**
	 * Create a series term.
	 *
	 * @param string $name Series name.
	 * @param string $description Series description.
	 * @return WP_Term
	 */
	protected function create_series( $name = 'Learn PHP', $description = 'A beginner series.' ) {
		return self::factory()->term->create_and_get(
			array(
				'taxonomy'    => 'post_series',
				'name'        => $name,
				'description' => $description,
			)
		);
	}

	/**
	 * Create published posts in a series, oldest first.
	 *
	 * @param WP_Term $series Series term.
	 * @param int     $count Number of posts.
	 * @return int[] Post IDs in series order.
	 */
	protected function create_series_posts( WP_Term $series, $count ) {
		$ids = array();

		for ( $i = 1; $i <= $count; $i++ ) {
			$ids[] = self::factory()->post->create(
				array(
					'post_title'   => $series->name . ' part ' . $i,
					'post_content' => 'Body of part ' . $i . '.',
					'post_excerpt' => '',
					'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( 100 - $i ) . ' days' ) ),
				)
			);
			wp_set_post_terms( end( $ids ), array( $series->term_id ), 'post_series' );
		}

		return $ids;
	}

	/**
	 * Get the plugin's PostContent instance.
	 *
	 * @return PostContent
	 */
	protected function post_content() {
		return \MJ\PostSeries\init()->get( PostContent::class );
	}

	/**
	 * Load a post as the main query, as on a single post page.
	 *
	 * @param int $post_id Post ID.
	 */
	protected function go_to_post( $post_id ) {
		$this->go_to( get_permalink( $post_id ) );
		the_post();
	}
}

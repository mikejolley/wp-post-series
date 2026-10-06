<?php
/**
 * Tests for template loading.
 *
 * @package MJ/PostSeries
 */

namespace MJ\PostSeries\Tests;

use MJ\PostSeries\Template;

/**
 * Template tests.
 */
class TemplateTest extends TestCase {
	/**
	 * Render a fixture template.
	 *
	 * @param string $template_name Template file.
	 * @param array  $args Template args.
	 * @return string
	 */
	private function render( $template_name, $args = array() ) {
		$template = new Template( __DIR__ . '/fixtures/' );

		ob_start();
		$template->get_template( $template_name, $args );
		return ob_get_clean();
	}

	public function test_args_are_available_to_the_template() {
		$this->assertSame( 'echo-arg: hello', $this->render( 'echo-arg.php', array( 'value' => 'hello' ) ) );
	}

	public function test_args_cannot_change_which_template_is_included() {
		$html = $this->render(
			'echo-arg.php',
			array(
				'value'         => 'hello',
				'template_name' => 'other.php',
				'default_path'  => '/tmp/',
			)
		);

		$this->assertSame( 'echo-arg: hello', $html );
	}

	public function test_locate_template_can_be_filtered() {
		add_filter(
			'wp_post_series_locate_template',
			static function () {
				return __DIR__ . '/fixtures/other.php';
			}
		);

		$this->assertSame( 'other', $this->render( 'echo-arg.php', array( 'value' => 'hello' ) ) );
	}
}

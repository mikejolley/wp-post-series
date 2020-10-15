<?php
/**
 * Loads plugin functionality.
 *
 * @package MJ/PostSeries
 */

namespace MJ\PostSeries;

defined( 'ABSPATH' ) || exit;

use MJ\PostSeries\Registry\Container;
use MJ\PostSeries\TaxonomyController;
use MJ\PostSeries\PostContent;

/**
 * Takes care of bootstrapping the plugin.
 */
class Plugin {
	/**
	 * Main __FILE__ reference.
	 *
	 * @var string
	 */
	private $file = '';

	/**
	 * Holds the Dependency Injection Container
	 *
	 * @var Container
	 */
	private $container;

	/**
	 * Constructor.
	 *
	 * @param Container $container  The Dependency Injection Container.
	 * @param string    $file Main plugin __FILE__ reference.
	 */
	public function __construct( Container $container, $file ) {
		$this->file      = $file;
		$this->container = $container;
		$this->init();
	}

	/**
	 * Initialize class features.
	 */
	private function init() {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		$this->container->register(
			TaxonomyController::class,
			function( Container $container ) {
				return new TaxonomyController();
			}
		);
		$this->container->register(
			Template::class,
			function( Container $container ) {
				$default_template_dir = dirname( $this->file ) . '/templates/';
				return new Template( $default_template_dir );
			}
		);
		$this->container->register(
			PostContent::class,
			function( Container $container ) {
				return new PostContent( $container->get( Template::class ) );
			}
		);

		$this->container->get( TaxonomyController::class );
		$this->container->get( PostContent::class );
	}

	/**
	 * Init localizations.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wp-post-series', false, dirname( plugin_basename( $this->file ) ) . '/languages/' );
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public function enqueue_scripts() {
		$asset_path   = dirname( __DIR__ ) . '/build/frontend.asset.php';
		$asset        = require $asset_path;
		$dependencies = isset( $asset['dependencies'] ) ? $asset['dependencies'] : array();
		$version      = ! empty( $asset['version'] ) ? $asset['version'] : filemtime( $asset_path );
		wp_register_script( 'wp-post-series', plugins_url( 'build/frontend.js', $this->file ), $dependencies, $version, true );
		wp_enqueue_style( 'wp-post-series-frontend', plugins_url( 'build/post-series.css', $this->file ), '', filemtime( dirname( __DIR__ ) . '/build/post-series.css' ) );
	}
}

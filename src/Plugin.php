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
use MJ\PostSeries\BlockTypes\PostSeries;

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
		add_action( 'init', array( $this, 'register_assets' ) );
		add_action( 'init', array( $this, 'register_block_types' ) );
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
		$this->container->register(
			PostSeries::class,
			function( Container $container ) {
				return new PostSeries( $container->get( PostContent::class ) );
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
	 * Register block type scripts and styles.
	 */
	public function register_assets() {
		wp_register_style( 'wp-post-series', plugins_url( 'build/post-series.css', $this->file ), '', filemtime( dirname( __DIR__ ) . '/build/post-series.css' ) );
		wp_register_script( 'wp-post-series-vendors', plugins_url( 'build/vendors.js', $this->file ), [], filemtime( dirname( __DIR__ ) . '/build/vendors.js' ), false );
		$this->register_script_asset( 'wp-post-series', plugins_url( 'build/frontend.js', $this->file ), dirname( __DIR__ ) . '/build/frontend.asset.php', [], true );

		$handle = $this->container->get( PostSeries::class )->get_script_handle();
		$this->register_script_asset( $handle, plugins_url( 'build/' . $handle . '.js', $this->file ), dirname( __DIR__ ) . '/build/' . $handle . '.asset.php', [ 'wp-post-series-vendors' ] );
	}

	/**
	 * Register block types.
	 */
	public function register_block_types() {
		$this->container->get( PostSeries::class )->register_block_type();
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'wp-post-series' );
	}

	/**
	 * Enqueue a script asset with correct dependencies and version.
	 *
	 * @param string $handle Script handle.
	 * @param string $script Script URL.
	 * @param string $asset_path Path to asset file.
	 * @param array  $dependencies Static list of dependencies.
	 * @param bool   $in_footer Should script be added to the footer.
	 */
	protected function register_script_asset( $handle, $script, $asset_path, $dependencies = [], $in_footer = false ) {
		$asset        = require $asset_path;
		$dependencies = array_merge( $dependencies, isset( $asset['dependencies'] ) ? $asset['dependencies'] : array() );
		$version      = ! empty( $asset['version'] ) ? $asset['version'] : filemtime( $asset_path );
		wp_register_script( $handle, $script, $dependencies, $version, $in_footer );
	}
}

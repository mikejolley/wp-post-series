<?php
/**
 * Plugin Name: WP Post Series
 * Plugin URI: https://github.com/mikejolley/wp-post-series
 * Description: Lets you setup a simple series of posts using taxonomies. Posts within a series will show an information box above the content automatically with links to other posts in the series and a description.
 * Version: 1.1.0
 * Author: Mike Jolley
 * Author URI: http://mikejolley.com
 * Requires at least: 3.8
 * Tested up to: 4.5
 *
 * Text Domain: wp-post-series
 * Domain Path: /languages/
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

class WP_Post_Series {

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		// Define constants
		define( 'WP_POST_SERIES_VERSION', '1.1.0' );
		define( 'WP_POST_SERIES_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'WP_POST_SERIES_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

		// Init
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );

		// Admin columns
		add_filter( 'manage_edit-post_columns', array( $this, 'columns' ) );
		add_action( 'manage_post_posts_custom_column', array( $this, 'custom_columns' ), 2 );

		// Admin filtering
		add_action( "restrict_manage_posts", array( $this, "posts_in_series" ) );

		// Frontend display
		add_filter( 'the_content', array( $this, "add_series_to_content" ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
	}

	/**
	 * Load the plugin textdomain for localistion
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wp-post-series', false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );
	}

	/**
	 * Register the taxonomies
	 */
	public function register_taxonomies() {

		$plural           = __( 'Post series', 'wp-post-series' );
		$singular         = __( 'Post series', 'wp-post-series' );

		register_taxonomy( "post_series",
	        array( "post" ),
	        array(
	            'hierarchical' 			=> false,
	            'label' 				=> $plural,
	            'labels' => array(
	            	'menu_name'         => __( 'Series', 'wp-post-series' ),
                    'name' 				=> $plural,
                    'singular_name' 	=> $singular,
                    'search_items' 		=> sprintf( __( 'Search %s', 'wp-post-series' ), $plural ),
                    'all_items' 		=> sprintf( __( 'All %s', 'wp-post-series' ), $plural ),
                    'parent_item' 		=> sprintf( __( '%s', 'wp-post-series' ), $singular ),
                    'parent_item_colon' => sprintf( __( '%s:', 'wp-post-series' ), $singular ),
                    'edit_item' 		=> sprintf( __( 'Edit %s', 'wp-post-series' ), $singular ),
                    'update_item' 		=> sprintf( __( 'Update %s', 'wp-post-series' ), $singular ),
                    'add_new_item' 		=> sprintf( __( 'Add New %s', 'wp-post-series' ), $singular ),
                    'new_item_name' 	=> sprintf( __( 'New %s Name', 'wp-post-series' ),  $singular )
            	),
	            'show_ui' 				=> true,
	            'query_var' 			=> true,
	            'rewrite' 				=> apply_filters( 'wp_post_series_enable_archive', false ),
	            'meta_box_cb'           => array( $this, 'post_series_meta_box' )
	        )
	    );
	}

	/**
	 * Get a post's series
	 * @param  int $post_id post ID
	 * @return object the term object
	 */
	public function get_post_series( $post_id ) {
		$series = wp_get_post_terms( $post_id, 'post_series' );

		if ( ! is_wp_error( $series ) && ! empty( $series ) && is_array( $series ) ) {
			$series = current( $series );
		} else {
			$series = false;
		}

		return $series;
	}

	/**
	 * Get the ID of a post's series
	 * @param  int $post_id post ID
	 * @return int series ID
	 */
	public function get_post_series_id( $post_id ) {
		$series = $this->get_post_series( $post_id );

		if ( $series ) {
			return $series->term_id;
		} else {
			return 0;
		}
	}

	/**
	 * Output the list of post series and allow admin to assign to a post. Uses a select box.
	 * @param  array $post Post being edited
	 */
	public function post_series_meta_box( $post ) {

		// Get the current series for the post if set
		$current_series = $this->get_post_series_id( $post->ID );

		// Get list of all series and the taxonomy
		$tax            = get_taxonomy( 'post_series' );
		$all_series     = get_terms( 'post_series', array( 'hide_empty' => false, 'orderby' => 'name' ) );

		?>
		<div id="taxonomy-<?php echo $tax->name; ?>" class="categorydiv">
			<label class="screen-reader-text" for="new_post_series_parent">
				<?php echo $tax->labels->parent_item_colon; ?>
			</label>
			<select name="tax_input[post_series]" style="width:100%">
				<option value="0"><?php echo '&mdash; ' . $tax->labels->parent_item . ' &mdash;'; ?></option>
				<?php foreach ( $all_series as $series ) : ?>
					<option value="<?php echo esc_attr( $series->slug ); ?>" <?php selected( $current_series, $series->term_id ); ?>><?php echo esc_html( $series->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
	}

	/**
	 * Output admin column headers
	 * @param  array $columns existing columns
	 * @return array new columns
	 */
	public function columns( $columns ) {
		if ( ! is_array( $columns ) ) {
			$new_columns = array();
		}

		foreach ( $columns as $key => $column ) {
			$new_columns[ $key ] = $column;

			if ( 'categories' == $key ) {
				$new_columns['post_series'] = __( 'Series', 'wp-post-series' );
			}
		}

		return $new_columns;
	}

	/**
	 * Output admin column values
	 * @param  string $column key for the column
	 */
	public function custom_columns( $column ) {
		global $post;

		if ( 'post_series' == $column ) {
			$current_series = $this->get_post_series( $post->ID );

			if ( $current_series ) {
				echo '<a href="' . esc_url( admin_url( 'edit.php?post_series=' . $current_series->slug ) ) . '">' . esc_html( $current_series->name ) . '</a>';
			} else {
				_e( 'N/A', 'wp-post-series' );
			}
		}
	}

	/**
	 * Filter posts by a particular series
	 */
	public function posts_in_series() {
		global $typenow, $wp_query;

	    if ( $typenow != 'post' ) {
	    	return;
	    }

	    $current_series = isset( $_REQUEST['post_series'] ) ? sanitize_text_field( $_REQUEST['post_series'] ) : '';
	    $all_series     = get_terms( 'post_series', array( 'hide_empty' => true, 'orderby' => 'name' ) );

	    if ( empty( $all_series ) ) {
	    	return;
	    }
	    ?>
	    <select name="post_series">
			<option value=""><?php _e( 'Show all series', 'wp-post-series' ) ?></option>
			<?php foreach ( $all_series as $series ) : ?>
				<option value="<?php echo esc_attr( $series->slug ); ?>" <?php selected( $current_series, $series->slug ); ?>><?php echo esc_html( $series->name ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Append/Prepend the series info box to the post content
	 * @param  string $content Post content
	 * @return string Ammended post content
	 */
	public function add_series_to_content( $content ) {
		global $post;

		if ( ! is_main_query() || 'post' !== $post->post_type ) {
			return $content;
		}

		$series = $this->get_post_series( $post->ID );

		if ( ! $series ) {
			return $content;
		}

		wp_enqueue_script( 'wp-post-series' );

		// Create series info box
		$term_description = term_description( $series->term_id, 'post_series' );
		$posts_in_series  = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'orderby'        => 'date',
			'order'          => 'asc',
			'post_status'    => array( 'publish', 'future' ),
			'tax_query'      => array(
				array(
					'taxonomy' => 'post_series',
					'field'    => 'slug',
					'terms'    => $series->slug
				)
			)
		) );

		$post_in_series = 1;

		foreach ( $posts_in_series as $post_id ) {
			if ( $post_id == $post->ID ) {
				break;
			}
			$post_in_series ++;
		}

		// add the series slug to the post series box class
		$post_series_box_class = 'wp-post-series-box series-'. $series->slug;

		if ( is_single() && sizeof( $posts_in_series ) > 1 ) {
			$post_series_box_class .= ' expandable';
		}

		ob_start();

		$this->get_template( 'series-box.php', array(
			'series'				=> $series,
			'description'			=> $term_description,
			'posts_in_series'		=> $posts_in_series,
			'post_in_series'		=> $post_in_series,
			'post_series_box_class'	=> $post_series_box_class
		) );

		$info_box = ob_get_clean();

		// Append or prepend
		$append = apply_filters( 'wp_post_series_append_info', false );

		if ( $append ) {
			$content = $content . $info_box;
		} else {
			$content = $info_box . $content;
		}

		return $content;
	}

	/**
	 * Get and include template files.
	 *
	 * @param mixed $template_name
	 * @param array $args (default: array())
	 * @param string $template_path (default: '')
	 * @param string $default_path (default: '')
	 */
	public function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		if ( $args && is_array( $args ) ) {
			extract( $args );
		}
		include( $this->locate_template( $template_name, $template_path, $default_path ) );
	}

	/**
	 * Locate a template and return the path for inclusion.
	 *
	 * This is the load order:
	 *
	 *		yourtheme		/	$template_path	/	$template_name
	 *		yourtheme		/	$template_name
	 *		$default_path	/	$template_name
	 *
	 * @param mixed $template_name
	 * @param string $template_path (default: '')
	 * @param string $default_path (default: '')
	 * @return string
	 */
	public function locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = 'wp_post_series';
		}
		if ( ! $default_path ) {
			$default_path  = WP_POST_SERIES_PLUGIN_DIR . '/templates/';
		}

		// Look within passed path within the theme - this is priority
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name
			)
		);

		// Get default template
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		// Return what we found
		return apply_filters( 'wp_post_series_locate_template', $template, $template_name, $template_path );
	}

	/**
	 * frontend_scripts function.
	 *
	 * @access public
	 * @return void
	 */
	public function frontend_scripts() {
		wp_register_script( 'wp-post-series', WP_POST_SERIES_PLUGIN_URL . '/assets/js/post-series.js', array( 'jquery' ), WP_POST_SERIES_VERSION, true );
		wp_enqueue_style( 'wp-post-series-frontend', WP_POST_SERIES_PLUGIN_URL . '/assets/css/post-series.css' );
	}
}

new WP_Post_Series();

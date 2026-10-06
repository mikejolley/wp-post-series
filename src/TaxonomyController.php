<?php
/**
 * Register the post series taxonomy.
 *
 * @package MJ/PostSeries
 */

namespace MJ\PostSeries;

defined( 'ABSPATH' ) || exit;

/**
 * TaxonomyController class.
 */
class TaxonomyController {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize class features.
	 */
	private function init() {
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_filter( 'manage_posts_columns', array( $this, 'add_post_series_column' ), 10, 2 );
		add_filter(
			'manage_pages_columns',
			function ( $columns ) {
				return $this->add_post_series_column( $columns, 'page' );
			}
		);
		add_action( 'manage_posts_custom_column', array( $this, 'post_series_column_content' ), 2, 2 );
		add_action( 'manage_pages_custom_column', array( $this, 'post_series_column_content' ), 2, 2 );
		add_action( 'restrict_manage_posts', array( $this, 'filter_posts_by_series' ) );
	}

	/**
	 * Register the taxonomies
	 */
	public function register_taxonomies() {
		$plural   = __( 'Series', 'wp-post-series' );
		$singular = __( 'Series', 'wp-post-series' );

		register_taxonomy(
			'post_series',
			get_series_post_types(),
			array(
				'hierarchical' => false,
				'label'        => $plural,
				'labels'       => array(
					'menu_name'                  => __( 'Series', 'wp-post-series' ),
					'name'                       => $plural,
					'singular_name'              => $singular,
					/* Translators: %s taxonomy name */
					'search_items'               => sprintf( __( 'Search %s', 'wp-post-series' ), $plural ),
					/* Translators: %s taxonomy name */
					'all_items'                  => sprintf( __( 'All %s', 'wp-post-series' ), $plural ),
					'parent_item'                => $singular,
					/* Translators: %s taxonomy name */
					'parent_item_colon'          => sprintf( __( '%s:', 'wp-post-series' ), $singular ),
					/* Translators: %s taxonomy name */
					'edit_item'                  => sprintf( __( 'Edit %s', 'wp-post-series' ), $singular ),
					/* Translators: %s taxonomy name */
					'update_item'                => sprintf( __( 'Update %s', 'wp-post-series' ), $singular ),
					/* Translators: %s taxonomy name */
					'add_new_item'               => sprintf( __( 'Add New %s', 'wp-post-series' ), $singular ),
					/* Translators: %s taxonomy name */
					'new_item_name'              => sprintf( __( 'New %s Name', 'wp-post-series' ), $singular ),
					// Non-hierarchical taxonomies otherwise fall back to "Tags" wording.
					'view_item'                  => __( 'View Series', 'wp-post-series' ),
					'popular_items'              => __( 'Popular series', 'wp-post-series' ),
					'separate_items_with_commas' => __( 'Separate series with commas', 'wp-post-series' ),
					'add_or_remove_items'        => __( 'Add or remove series', 'wp-post-series' ),
					'choose_from_most_used'      => __( 'Choose from the most used series', 'wp-post-series' ),
					'not_found'                  => __( 'No series found.', 'wp-post-series' ),
					'no_terms'                   => __( 'No series', 'wp-post-series' ),
					'items_list_navigation'      => __( 'Series list navigation', 'wp-post-series' ),
					'items_list'                 => __( 'Series list', 'wp-post-series' ),
					'back_to_items'              => __( '&larr; Go to Series', 'wp-post-series' ),
					'item_link'                  => __( 'Series Link', 'wp-post-series' ),
					'item_link_description'      => __( 'A link to a series.', 'wp-post-series' ),
				),
				'show_ui'      => true,
				'show_in_rest' => true,
				'query_var'    => true,
				'rewrite'      => apply_filters( 'wp_post_series_enable_archive', false ),
				'meta_box_cb'  => array( $this, 'post_series_meta_box' ),
			)
		);
	}

	/**
	 * Classic editor meta box.
	 *
	 * Render the list of post series and allow admin to assign them to a post.
	 *
	 * @param array $post Post being edited.
	 */
	public function post_series_meta_box( $post ) {
		$current_series = get_post_series( $post->ID );
		if ( $current_series && is_object( $current_series ) ) {
			$current_series_id = $current_series->term_id;
		} else {
			$current_series_id = 0;
		}

		$taxonomy_data     = get_taxonomy( 'post_series' );
		$post_series_terms = get_terms(
			array(
				'taxonomy'   => 'post_series',
				'hide_empty' => false,
				'orderby'    => 'name',
			)
		);

		?>
		<div id="taxonomy-<?php echo esc_attr( $taxonomy_data->name ); ?>" class="categorydiv">
			<label class="screen-reader-text" for="new_post_series_parent">
				<?php echo esc_html( $taxonomy_data->labels->parent_item_colon ); ?>
			</label>
			<select name="tax_input[post_series]" style="width:100%">
				<option value="0"><?php echo '&mdash; ' . esc_html( $taxonomy_data->labels->parent_item ) . ' &mdash;'; ?></option>
				<?php foreach ( $post_series_terms as $series ) : ?>
					<option value="<?php echo esc_attr( $series->slug ); ?>" <?php selected( $current_series_id, $series->term_id ); ?>><?php echo esc_html( $series->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
	}

	/**
	 * Add admin column headers.
	 *
	 * @param  array  $columns existing columns.
	 * @param  string $post_type Post type of the list table.
	 * @return array new columns.
	 */
	public function add_post_series_column( $columns, $post_type = 'post' ) {
		if ( ! is_array( $columns ) ) {
			$columns = array();
		}

		if ( ! in_array( $post_type, get_series_post_types(), true ) ) {
			return $columns;
		}

		$new_columns = array();

		foreach ( $columns as $key => $column ) {
			$new_columns[ $key ] = $column;

			if ( 'categories' === $key ) {
				$new_columns['post_series'] = __( 'Series', 'wp-post-series' );
			}
		}

		// Categories column may have been removed; add ours anyway.
		if ( ! isset( $new_columns['post_series'] ) ) {
			$new_columns['post_series'] = __( 'Series', 'wp-post-series' );
		}

		return $new_columns;
	}

	/**
	 * Output admin column value.
	 *
	 * @param string $column key for the column.
	 * @param int    $post_id Post ID. Defaults to the global post.
	 */
	public function post_series_column_content( $column, $post_id = 0 ) {
		if ( 'post_series' === $column ) {
			$post           = get_post( $post_id ? $post_id : null );
			$current_series = get_post_series( $post->ID );

			if ( $current_series ) {
				$query_args = array( 'post_series' => $current_series->slug );

				if ( 'post' !== $post->post_type ) {
					$query_args['post_type'] = $post->post_type;
				}

				echo '<a href="' . esc_url( add_query_arg( $query_args, admin_url( 'edit.php' ) ) ) . '">' . esc_html( $current_series->name ) . '</a>';
			} else {
				esc_html_e( 'N/A', 'wp-post-series' );
			}
		}
	}

	/**
	 * Filter posts by a particular series
	 *
	 * @param string $post_type Post type of the list table. Defaults to the current screen's.
	 */
	public function filter_posts_by_series( $post_type = '' ) {
		global $typenow;

		if ( ! in_array( $post_type ? $post_type : $typenow, get_series_post_types(), true ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_series    = isset( $_REQUEST['post_series'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_series'] ) ) : '';
		$post_series_terms = get_terms(
			array(
				'taxonomy'   => 'post_series',
				'hide_empty' => true,
				'orderby'    => 'name',
			)
		);

		if ( empty( $post_series_terms ) ) {
			return;
		}
		?>
		<select name="post_series">
			<option value=""><?php esc_html_e( 'Show all series', 'wp-post-series' ); ?></option>
			<?php foreach ( $post_series_terms as $series ) : ?>
				<option value="<?php echo esc_attr( $series->slug ); ?>" <?php selected( $current_series, $series->slug ); ?>><?php echo esc_html( $series->name ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}
}

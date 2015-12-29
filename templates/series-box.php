<aside class="<?php echo $post_series_box_class; ?>">
	<p class="wp-post-series-name">
		<?php
			if ( apply_filters( 'wp_post_series_enable_archive', false ) ) {
				$series_name = '<a href="' . get_term_link( $series->term_id, 'post_series' ) . '">' . esc_html( $series->name ) . '</a>';
			} else {
				$series_name = esc_html( $series->name );
			}
			printf( __( 'This is post %d of %d in the series <em>&ldquo;%s&rdquo;</em>', 'wp-post-series' ), $post_in_series, sizeof( $posts_in_series ), $series_name );
		?>
	</p>

	<?php if ( is_single() && sizeof( $posts_in_series ) > 1 ) : ?>

		<nav class="wp-post-series-nav">
			<ol>
				<?php foreach ( $posts_in_series as $key => $post_id ) : ?>
					<li>
						<?php if ( ! is_single( $post_id ) && 'publish' === get_post_status( $post_id ) ) echo '<a href="' . get_permalink( $post_id ) . '">'; ?>
						<?php echo 'publish' === get_post_status( $post_id ) ? get_the_title( $post_id ) : sprintf( __( '%s &ndash; <em>Scheduled for %s</em>', 'wp-post-series' ), get_the_title( $post_id ), get_post_time( get_option( 'date_format' ), false, $post_id, true ) ); ?>
						<?php if ( ! is_single( $post_id ) && 'publish' === get_post_status( $post_id ) ) echo '</a>'; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</nav>
	<?php endif; ?>

	<?php if ( is_single() ) : ?>
		<?php if ( $description ) : ?>
			<div class="wp-post-series-description"><?php echo wpautop( wptexturize( $description ) ); ?></div>
		<?php endif; ?>

	<?php endif; ?>
</aside>

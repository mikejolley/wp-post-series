<?php
/**
 * Post Series Information Template.
 *
 * @package MJ/PostSeries
 */
?>
<aside class="<?php echo esc_attr( $post_series_box_class ); ?>">
	<input id="collapsible-series-<?php echo esc_attr( $post_series_id ); ?>" class="series-toggle" type="checkbox">
	<label for="collapsible-series-<?php echo esc_attr( $post_series_id ); ?>" class="series-toggle-label" tabindex="0">
		<p class="wp-post-series-box__name wp-post-series-name">
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %1$d Post index, %2$d number of posts in series, %3$s series name/link */
					__( 'This is post %1$d of %2$d in the series <em>&ldquo;%3$s&rdquo;</em>', 'wp-post-series' ),
					$post_in_series,
					count( $posts_in_series ),
					$series_name
				)
			);
			?>
		</p>

		<?php if ( $description ) : ?>
			<div class="wp-post-series-box__description wp-post-series-description">
				<?php echo wp_kses_post( $description ); ?>
			</div>
		<?php endif; ?>
	</label>
	<?php if ( $show_posts_in_series ) : ?>
		<div class="wp-post-series-box__posts">
			<ol>
				<?php foreach ( $posts_in_series_links as $link ) : ?>
					<li><?php echo wp_kses_post( $link ); ?></li>
				<?php endforeach; ?>
			</ol>
		</div>
	<?php endif; ?>
</aside>

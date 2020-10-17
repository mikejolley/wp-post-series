<?php
/**
 * Post Series Information Template.
 *
 * @package MJ/PostSeries
 */

$toggle_id = uniqid( 'collapsible-series-' . $series->slug );
?>
<div class="<?php echo esc_attr( $post_series_box_class ); ?>">
	<?php if ( ! $show_posts ) : ?>
		<input id="<?php echo esc_attr( $toggle_id ); ?>" class="wp-post-series-box__toggle_checkbox" type="checkbox">
	<?php endif; ?>

	<label
		class="wp-post-series-box__label"
		<?php if ( ! $show_posts ) : ?>
			for="<?php echo esc_attr( $toggle_id ); ?>"
			tabindex="0"
		<?php endif; ?>
		>
		<p class="wp-post-series-box__name wp-post-series-name">
			<?php echo wp_kses_post( $series_label ); ?>
		</p>
		<?php if ( $show_description ) : ?>
			<div class="wp-post-series-box__description wp-post-series-description">
				<?php echo wp_kses_post( $description ); ?>
			</div>
		<?php endif; ?>
	</label>

	<?php if ( $has_multiple_posts ) : ?>
		<div class="wp-post-series-box__posts">
			<ol>
				<?php foreach ( $posts_in_series_links as $link ) : ?>
					<li><?php echo wp_kses_post( $link ); ?></li>
				<?php endforeach; ?>
			</ol>
		</div>
	<?php endif; ?>
</div>

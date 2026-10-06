<?php
/**
 * Post Series Information Template.
 *
 * When the box is expandable, a visually hidden checkbox controls the post list (CSS-only, so it works without
 * JavaScript). The toggle label is laid over the header so the whole header is clickable.
 *
 * @package MJ/PostSeries
 */

$toggle_id = uniqid( 'collapsible-series-' . $series->slug );
$posts_id  = $toggle_id . '-posts';
?>
<div class="<?php echo esc_attr( $post_series_box_class ); ?>">
	<?php if ( $is_expandable ) : ?>
		<input id="<?php echo esc_attr( $toggle_id ); ?>" class="wp-post-series-box__toggle_checkbox" type="checkbox" aria-controls="<?php echo esc_attr( $posts_id ); ?>">
	<?php endif; ?>

	<div class="wp-post-series-box__label">
		<p class="wp-post-series-box__name wp-post-series-name">
			<?php echo wp_kses_post( $series_label ); ?>
		</p>
		<?php if ( $show_description ) : ?>
			<div class="wp-post-series-box__description wp-post-series-description">
				<?php echo wp_kses_post( $description ); ?>
			</div>
		<?php endif; ?>
		<?php if ( $is_expandable ) : ?>
			<label class="wp-post-series-box__toggle" for="<?php echo esc_attr( $toggle_id ); ?>">
				<span class="wp-post-series-box__toggle_text"><?php esc_html_e( 'Show all posts in this series', 'wp-post-series' ); ?></span>
			</label>
		<?php endif; ?>
	</div>

	<?php if ( $has_multiple_posts ) : ?>
		<div class="wp-post-series-box__posts" id="<?php echo esc_attr( $posts_id ); ?>">
			<ol>
				<?php foreach ( $posts_in_series_links as $series_post_link ) : ?>
					<li><?php echo wp_kses_post( $series_post_link ); ?></li>
				<?php endforeach; ?>
			</ol>
		</div>
	<?php endif; ?>
</div>

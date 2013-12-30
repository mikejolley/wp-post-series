jQuery('.wp-post-series-show-nav').click(function() {
	jQuery(this).closest('.wp-post-series-box').find('.wp-post-series-nav').slideDown();
	jQuery(this).fadeOut();
	return false;
});
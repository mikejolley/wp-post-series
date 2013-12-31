jQuery('.wp-post-series-box.expandable').click(function(e) {
	if ( jQuery( e.target ).is('a') ) {
        return true;
    }
	jQuery(this).closest('.wp-post-series-box').find('.wp-post-series-nav').slideToggle();
	return false;
});
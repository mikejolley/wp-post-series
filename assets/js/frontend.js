/*
 * Checkboxes toggle with Space natively; Enter is added as well. Listening on the document also covers boxes added
 * after load (e.g. infinite scroll). Theme overrides of the 2.0.0 template made the label focusable instead; drop
 * that so the (now focusable) checkbox is the single control.
 */
document
	.querySelectorAll( '.wp-post-series-box__label[tabindex]' )
	.forEach( ( label ) => label.removeAttribute( 'tabindex' ) );

document.addEventListener( 'keydown', ( e ) => {
	if (
		e.key === 'Enter' &&
		e.target.matches?.( '.wp-post-series-box__toggle_checkbox' )
	) {
		e.preventDefault();
		e.target.click();
	}
} );

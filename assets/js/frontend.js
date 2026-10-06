/*
 * Checkboxes toggle with Space natively; Enter is added as well. Theme overrides of the 2.0.0 template made the
 * label focusable instead; drop that so the (now focusable) checkbox is the single control.
 */
document
	.querySelectorAll( '.wp-post-series-box__label[tabindex]' )
	.forEach( ( label ) => label.removeAttribute( 'tabindex' ) );

document
	.querySelectorAll( '.wp-post-series-box__toggle_checkbox' )
	.forEach( ( toggle ) => {
		toggle.addEventListener( 'keydown', ( e ) => {
			if ( e.key === 'Enter' ) {
				e.preventDefault();
				toggle.click();
			}
		} );
	} );

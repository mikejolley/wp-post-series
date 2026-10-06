const labels = document.querySelectorAll( '.wp-post-series-box__label' );

Array.from( labels ).forEach( ( label ) => {
	label.addEventListener( 'keydown', ( e ) => {
		if ( e.key === ' ' || e.key === 'Enter' ) {
			e.preventDefault();
			label.click();
		}
	} );
} );

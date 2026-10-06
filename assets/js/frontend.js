// Checkboxes toggle with Space natively; also support Enter, as the previous markup did.
const toggles = document.querySelectorAll(
	'.wp-post-series-box__toggle_checkbox'
);

Array.from( toggles ).forEach( ( toggle ) => {
	toggle.addEventListener( 'keydown', ( e ) => {
		if ( e.key === 'Enter' ) {
			e.preventDefault();
			toggle.click();
		}
	} );
} );

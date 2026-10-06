/*
 * Checkboxes toggle with Space natively; Enter is added as well. Focusable labels come from theme overrides of the
 * 2.0.0 template, where the label itself was the keyboard toggle.
 */
const toggles = document.querySelectorAll(
	'.wp-post-series-box__toggle_checkbox, .wp-post-series-box__label[tabindex]'
);

Array.from( toggles ).forEach( ( toggle ) => {
	const isLegacyLabel = toggle.tagName === 'LABEL';

	toggle.addEventListener( 'keydown', ( e ) => {
		if ( e.key === 'Enter' || ( isLegacyLabel && e.key === ' ' ) ) {
			e.preventDefault();
			toggle.click();
		}
	} );
} );

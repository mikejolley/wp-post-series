/**
 * Extends the @wordpress/scripts config. Blocks are discovered from block.json files under assets/js; the
 * frontend script and stylesheet are also used by the automatically inserted series box, so they're separate entries.
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		...defaultConfig.entry(),
		frontend: './assets/js/frontend.js',
		'post-series': './assets/css/post-series.scss',
	},
};

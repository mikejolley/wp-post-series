/**
 * Extends the @wordpress/scripts ESLint config. WordPress packages (other than the bundled @wordpress/icons) are
 * externals that WordPress provides at runtime, so they aren't installed; treat them as built-in for import rules.
 */
const defaultConfig = require( '@wordpress/scripts/config/eslint.config.cjs' );

module.exports = [
	...defaultConfig,
	{
		settings: {
			'import/core-modules': [
				'@wordpress/api-fetch',
				'@wordpress/block-editor',
				'@wordpress/blocks',
				'@wordpress/components',
				'@wordpress/data',
				'@wordpress/element',
				'@wordpress/i18n',
				'@wordpress/server-side-render',
			],
		},
	},
];

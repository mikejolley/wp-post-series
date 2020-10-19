module.exports = {
	extends: [
		'plugin:@wordpress/eslint-plugin/recommended',
		'prettier',
		'plugin:jest/recommended',
		'plugin:react-hooks/recommended',
	],
	env: {
		'jest/globals': true,
	},
	globals: {
		page: true,
		browser: true,
		context: true,
		jestPuppeteer: true,
		fetchMock: true,
		jQuery: 'readonly',
	},
	plugins: ['jest'],
	rules: {
		'@wordpress/dependency-group': 'off',
		'valid-jsdoc': 'off',
		radix: 'error',
		yoda: ['error', 'never'],
	},
};

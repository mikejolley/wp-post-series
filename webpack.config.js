// Require path.
const path = require('path');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');
const MinifyPlugin = require('babel-minify-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const frontConfig = {
	mode: 'production',
	entry: {
		frontend: './assets/js/frontend.js',
	},
	output: {
		filename: '[name].js',
		path: path.resolve(__dirname, 'build'),
	},
	module: {
		rules: [
			{
				test: /\.jsx?$/,
				include: [path.resolve(__dirname, 'assets/js')],
				use: {
					loader: 'babel-loader?cacheDirectory',
					options: {
						presets: [
							[
								'@babel/preset-env',
								{
									modules: false,
									targets: {
										browsers: [
											'extends @wordpress/browserslist-config',
										],
									},
								},
							],
						],
						plugins: [
							require.resolve('@babel/plugin-transform-runtime'),
						].filter(Boolean),
					},
				},
			},
		],
	},
	plugins: [
		new DependencyExtractionWebpackPlugin({
			injectPolyfill: true,
		}),
		new MinifyPlugin(),
	],
};
const blocksConfig = {
	mode: 'production',
	devtool: false,
	entry: {
		'wp-post-series-block': './assets/js/post-series-block/index.js',
	},
	output: {
		path: path.resolve(__dirname, 'build'),
		filename: '[name].js',
		library: ['mj', 'blocks', '[name]'],
		libraryTarget: 'this',
		// This fixes an issue with multiple webpack projects using chunking
		// overwriting each other's chunk loader function.
		// See https://webpack.js.org/configuration/output/#outputjsonpfunction
		jsonpFunction: 'webpackMjBlocksJsonp',
	},
	optimization: {
		splitChunks: {
			minSize: 0,
			cacheGroups: {
				commons: {
					test: /[\\/]node_modules[\\/]/,
					name: 'vendors',
					chunks: 'all',
					enforce: true,
				},
			},
		},
	},
	module: {
		rules: [
			{
				test: /\.jsx?$/,
				include: [path.resolve(__dirname, 'assets/js')],
				exclude: /node_modules/,
				use: {
					loader: 'babel-loader?cacheDirectory',
					options: {
						presets: ['@wordpress/babel-preset-default'],
						plugins: [
							require.resolve(
								'babel-plugin-transform-react-remove-prop-types'
							),
							require.resolve(
								'@babel/plugin-proposal-class-properties'
							),
						].filter(Boolean),
					},
				},
			},
		],
	},
	plugins: [
		new DependencyExtractionWebpackPlugin({
			injectPolyfill: true,
		}),
		new MinifyPlugin(),
	],
};
const styleConfig = {
	mode: 'production',
	entry: {
		'post-series': './assets/css/post-series.scss',
	},
	output: {
		path: path.resolve(__dirname, 'build'),
		filename: `[name]-style.js`,
	},
	plugins: [
		new MiniCssExtractPlugin({
			filename: `[name].css`,
		}),
	],
	module: {
		rules: [
			{
				test: /\.s[ac]ss$/i,
				use: [
					MiniCssExtractPlugin.loader,
					{ loader: 'css-loader', options: { importLoaders: 1 } },
					'postcss-loader',
					'sass-loader',
				],
			},
		],
	},
};

module.exports = [frontConfig, blocksConfig, styleConfig];

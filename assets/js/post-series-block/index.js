/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { postList as icon } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import edit from './edit.js';

registerBlockType('mj/wp-post-series', {
	title: __('Post Series', 'wp-post-series'),
	icon,
	keywords: [__('series', 'wp-post-series'), __('post', 'wp-post-series')],
	category: 'widgets',
	description: __(
		'Show a list of posts in the same series.',
		'wp-post-series'
	),
	supports: {
		html: false,
		multiple: true,
	},
	example: { attributes: {} },
	attributes: {
		series: {
			type: 'string',
			default: '',
		},
		showDescription: {
			type: 'boolean',
			default: true,
		},
		showPosts: {
			type: 'boolean',
			default: false,
		},
	},
	edit,
	save() {
		return null;
	},
});

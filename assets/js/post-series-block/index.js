/**
 * External dependencies
 */
import { postList as icon } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import metadata from './block.json';
import edit from './edit.js';

registerBlockType( metadata, {
	icon,
	edit,
	save() {
		return null;
	},
} );

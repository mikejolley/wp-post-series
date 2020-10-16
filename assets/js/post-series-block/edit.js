/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { withInstanceId } from '@wordpress/compose';
import { Disabled } from '@wordpress/components';

/**
 * Internal dependencies
 */
import Block from './block.js';

/**
 * Edit Component.
 */
const Edit = ({ attributes, instanceId, setAttributes }) => {
	return (
		<Disabled>
			<Block attributes={attributes || {}} isEditor={true} />
		</Disabled>
	);
};

Edit.propTypes = {};

export default withInstanceId(Edit);

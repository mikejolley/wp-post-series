/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { Icon, postList } from '@wordpress/icons';
import { ServerSideRender } from '@wordpress/editor';
import { Placeholder } from '@wordpress/components';

const EmptyPlaceholder = ( { message } ) => (
	<Placeholder
		icon={ <Icon icon={ postList } /> }
		label={ __( 'Post Series', 'wp-post-series' ) }
		className="wp-block-post-series"
	>
		{ message
			? message
			: __(
					'This block shows a list of posts within the selected series.',
					'wp-post-series'
			  ) }
	</Placeholder>
);

/**
 * Component displaying a post series.
 *
 * @param {Object} props             Incoming props.
 * @param {Array} [props.attributes] Block attributes.
 * @param {number} [props.currentPostSeriesId] Current post series ID--may not be saved yet.
 * @return {*} The component.
 */
const PostSeriesBlock = ( { attributes, currentPostSeriesId } ) => {
	// If we are not loading terms and the post has no assigned series, show a placeholder.
	if ( attributes.series === '' && currentPostSeriesId === 0 ) {
		return (
			<EmptyPlaceholder
				message={ __(
					'This block shows a list of posts in a series. To get started, select a post series for this post in the document settings panel.',
					'wp-post-series'
				) }
			/>
		);
	}
	const ssrAttributes = attributes;

	if ( attributes.series === '' ) {
		ssrAttributes.previewId = currentPostSeriesId;
	}

	return (
		<ServerSideRender
			block="mj/wp-post-series"
			attributes={ ssrAttributes }
			EmptyResponsePlaceholder={ EmptyPlaceholder }
		/>
	);
};

export default PostSeriesBlock;

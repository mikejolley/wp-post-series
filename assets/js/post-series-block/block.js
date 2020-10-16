/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { Icon, postList } from '@wordpress/icons';
import { ServerSideRender } from '@wordpress/editor';
import { Placeholder } from '@wordpress/components';

const EmptyPlaceholder = () => (
	<Placeholder
		icon={<Icon icon={postList} />}
		label={__('Post Series', 'wp-post-series')}
		className="wp-block-post-series"
	>
		{__(
			'This block shows a list of posts in a series. Either there are no posts in the selected series, or the current post is not within a series.',
			'wp-post-series'
		)}
	</Placeholder>
);

/**
 * Component displaying a product search form.
 *
 * TODO:
 * Add option to select a series to show, default to current
 * Render the series
 * Option to toggle heading
 * Option to toggle series description
 * Option to control series default visibility
 * Placeholder if series is not assigned to post. Use SSR.
 */
const PostSeriesBlock = ({ attributes }) => {
	return (
		<ServerSideRender
			block="mj/wp-post-series"
			attributes={attributes}
			EmptyResponsePlaceholder={EmptyPlaceholder}
		/>
	);
};

export default PostSeriesBlock;

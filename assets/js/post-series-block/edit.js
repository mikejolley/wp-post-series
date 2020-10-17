/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import {
	Disabled,
	PanelBody,
	ToggleControl,
	SelectControl,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import Block from './block.js';
import withPostSeriesTerms from '../hocs/with-post-series-terms';

/**
 * Edit Component.
 */
const Edit = ({ attributes, setAttributes, termsList, termsLoading }) => {
	const { series, showDescription, showPosts } = attributes;
	return (
		<>
			<InspectorControls key="inspector">
				<PanelBody title={__('Content', 'wp-post-series')} initialOpen>
					{!termsLoading && (
						<SelectControl
							label={__('Show series', 'wp-post-series')}
							value={series}
							options={[
								{
									label: __(
										'Current Post Series',
										'wp-post-series'
									),
									value: '',
								},
								...termsList.map((term) => {
									return {
										label: term.name,
										value: term.slug,
									};
								}),
							]}
							onChange={(chosenSeries) =>
								setAttributes({ series: chosenSeries })
							}
						/>
					)}
					<ToggleControl
						label={__('Show series description', 'wp-post-series')}
						help={
							showDescription
								? __(
										'Series description is visible.',
										'wp-post-series'
								  )
								: __(
										'Series description is hidden.',
										'wp-post-series'
								  )
						}
						checked={showDescription}
						onChange={() =>
							setAttributes({ showDescription: !showDescription })
						}
					/>
					<ToggleControl
						label={__('Always show post list', 'wp-post-series')}
						help={
							showPosts
								? __(
										'Series posts are always visible.',
										'wp-post-series'
								  )
								: __(
										'Series posts can be toggled.',
										'wp-post-series'
								  )
						}
						checked={showPosts}
						onChange={() =>
							setAttributes({ showPosts: !showPosts })
						}
					/>
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<Block attributes={attributes || {}} isEditor={true} />
			</Disabled>
		</>
	);
};

Edit.propTypes = {};

export default withPostSeriesTerms(Edit);

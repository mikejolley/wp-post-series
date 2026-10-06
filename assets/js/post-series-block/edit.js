/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	Disabled,
	PanelBody,
	ToggleControl,
	SelectControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import Block from './block.js';
import withPostSeriesTerms from '../hocs/with-post-series-terms';

/**
 * Edit Component.
 *
 * @param {Object}                       props                 Incoming props.
 * @param {Array}                        [props.attributes]    Block attributes.
 * @param {(attributes: Object) => void} [props.setAttributes] Set block attributes.
 * @param {Array}                        [props.termsList]     Array of post_series terms.
 * @param {boolean}                      [props.termsLoading]  True when terms are being loaded from the API.
 * @return {Element} The component.
 */
const Edit = ( { attributes, setAttributes, termsList, termsLoading } ) => {
	const { series, showDescription, showPosts } = attributes;
	const blockProps = useBlockProps();

	/**
	 * First post series term assigned to the post (unsaved), or 0. The store and attribute are missing outside of
	 * the post editor (e.g. site and widget editors), and on post types without the post_series taxonomy.
	 */
	const currentPostSeriesId = useSelect(
		( select ) =>
			select( 'core/editor' )?.getEditedPostAttribute(
				'post_series'
			)?.[ 0 ] || 0,
		[]
	);

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Content', 'wp-post-series' ) }
					initialOpen
				>
					{ ! termsLoading && (
						<SelectControl
							label={ __( 'Show series', 'wp-post-series' ) }
							value={ series }
							options={ [
								{
									label: __(
										'Current Post Series',
										'wp-post-series'
									),
									value: '',
								},
								...termsList.map( ( term ) => {
									return {
										label: term.name,
										value: term.slug,
									};
								} ),
							] }
							onChange={ ( chosenSeries ) =>
								setAttributes( { series: chosenSeries } )
							}
						/>
					) }
					<ToggleControl
						label={ __(
							'Show series description',
							'wp-post-series'
						) }
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
						checked={ showDescription }
						onChange={ () =>
							setAttributes( {
								showDescription: ! showDescription,
							} )
						}
					/>
					<ToggleControl
						label={ __(
							'Always show post list',
							'wp-post-series'
						) }
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
						checked={ showPosts }
						onChange={ () =>
							setAttributes( { showPosts: ! showPosts } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<Disabled>
					<Block
						attributes={ attributes }
						currentPostSeriesId={ currentPostSeriesId }
					/>
				</Disabled>
			</div>
		</>
	);
};

Edit.propTypes = {};

export default withPostSeriesTerms( Edit );

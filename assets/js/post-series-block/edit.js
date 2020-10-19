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
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Block from './block.js';
import withPostSeriesTerms from '../hocs/with-post-series-terms';

/**
 * Edit Component.
 *
 * @param {Object} props             Incoming props.
 * @param {Array} [props.attributes] Block attributes.
 * @param {Function} [props.setAttributes] Set block attributes.
 * @param {Array} [props.termsList] Array of post_series terms.
 * @param {boolean} [props.termsLoading] True when terms are being loaded from the API.
 * @return {*} The component.
 */
const Edit = ( { attributes, setAttributes, termsList, termsLoading } ) => {
	const { series, showDescription, showPosts } = attributes;

	/**
	 * Track the post series term assigned to the post (unsaved).
	 *
	 * @type {Array} editingPostSeries Array of term IDs.
	 */
	const editingPostSeries = useSelect( ( select ) => {
		const store = select( 'core/editor' );
		return store.getEditedPostAttribute( 'post_series' );
	}, [] );

	const currentPostSeriesId = useMemo( () => {
		if ( ! editingPostSeries[ 0 ] ) {
			return 0;
		}
		return editingPostSeries[ 0 ];
	}, [ editingPostSeries ] );

	return (
		<>
			<InspectorControls key="inspector">
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
			<Disabled>
				<Block
					attributes={ attributes }
					currentPostSeriesId={ currentPostSeriesId }
				/>
			</Disabled>
		</>
	);
};

Edit.propTypes = {};

export default withPostSeriesTerms( Edit );

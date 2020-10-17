/**
 * External dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { useState, useEffect } from '@wordpress/element';
import { find } from 'lodash';

/**
 * Get attribute data (name, taxonomy etc) from server data.
 *
 * @param {string} matchField Field to match on. e.g. id or slug.
 * @param {number} matchValue Value to look for.
 * @param {Array} terms List of term objects.
 * @return {Object|null}
 */
export const getTermBy = (matchField = 'id', matchValue, terms) => {
	return terms ? find(terms, [matchField, matchValue]) : null;
};

/**
 * HOC that loads post series terms from the API.
 *
 * @param {Function} OriginalComponent Component being wrapped.
 */
export const withPostSeriesTerms = (OriginalComponent) => {
	return (props) => {
		const [termsList, setTermsList] = useState({});
		const [loading, setLoading] = useState(true);

		useEffect(() => {
			apiFetch({ path: '/wp/v2/post_series?per_page=-1' })
				.then((terms) => {
					setTermsList(terms);
				})
				.catch(async () => {
					setTermsList([]);
				})
				.finally(() => {
					setLoading(false);
				});
		}, []);

		return (
			<OriginalComponent
				{...props}
				termsLoading={loading}
				termsList={termsList}
			/>
		);
	};
};

export default withPostSeriesTerms;

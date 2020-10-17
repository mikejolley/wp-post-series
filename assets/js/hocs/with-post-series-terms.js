/**
 * External dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { useState, useEffect } from '@wordpress/element';

/**
 * HOC that loads post series terms from the API.
 *
 * @param {Function} OriginalComponent Component being wrapped.
 */
const withPostSeriesTerms = (OriginalComponent) => {
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

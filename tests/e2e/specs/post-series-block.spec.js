/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

const BLOCK_ERROR = 'This block has encountered an error';

test.describe( 'Post Series block', () => {
	let series;
	let posts;

	test.beforeAll( async ( { requestUtils } ) => {
		// The PHPUnit installer shares this site and leaves a non-existent theme active.
		await requestUtils.activateTheme( 'twentytwentyfive' );
		await requestUtils.activatePlugin( 'wp-post-series' );
		series = await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/post_series',
			data: { name: 'E2E Series', description: 'Series for tests.' },
		} );
		posts = [];
		for ( const title of [ 'E2E part 1', 'E2E part 2' ] ) {
			posts.push(
				await requestUtils.createPost( {
					title,
					status: 'publish',
					post_series: [ series.id ],
				} )
			);
		}
	} );

	test.afterAll( async ( { requestUtils } ) => {
		await requestUtils.deleteAllPosts();
		await requestUtils.deleteAllPages();
		await requestUtils.rest( {
			method: 'DELETE',
			path: `/wp/v2/post_series/${ series.id }`,
			params: { force: true },
		} );
	} );

	test( 'previews the current post series in the post editor', async ( {
		admin,
		editor,
	} ) => {
		await admin.editPost( posts[ 1 ].id );
		await editor.insertBlock( { name: 'mj/wp-post-series' } );

		await expect(
			editor.canvas.locator( '.wp-post-series-box__name' )
		).toContainText( 'This is post 2 of 2 in the series' );
		await expect( editor.canvas.getByText( BLOCK_ERROR ) ).toHaveCount( 0 );
	} );

	test( 'works on a page, where posts series are not available', async ( {
		admin,
		editor,
	} ) => {
		await admin.createNewPost( { postType: 'page' } );
		await editor.insertBlock( {
			name: 'mj/wp-post-series',
			attributes: { series: series.slug },
		} );

		await expect(
			editor.canvas.locator( '.wp-post-series-box__name' )
		).toContainText( 'Series: E2E Series' );
		await expect( editor.canvas.getByText( BLOCK_ERROR ) ).toHaveCount( 0 );
	} );

	test( 'works in the site editor', async ( { admin, editor } ) => {
		await admin.visitSiteEditor( {
			postId: 'twentytwentyfive//single',
			postType: 'wp_template',
			canvas: 'edit',
		} );
		await editor.insertBlock( {
			name: 'mj/wp-post-series',
			attributes: { series: series.slug },
		} );

		await expect(
			editor.canvas.locator( '.wp-post-series-box__name' )
		).toContainText( 'Series: E2E Series' );
		await expect( editor.canvas.getByText( BLOCK_ERROR ) ).toHaveCount( 0 );
	} );

	test( 'series box toggles from the keyboard on the frontend', async ( {
		page,
	} ) => {
		await page.goto( posts[ 0 ].link );

		const box = page.locator( '.wp-post-series-box' );
		const postList = box.locator( '.wp-post-series-box__posts' );

		await expect( box ).toContainText( 'This is post 1 of 2' );
		await expect( postList ).toBeHidden();

		await box.locator( '.wp-post-series-box__label' ).focus();
		await page.keyboard.press( 'Enter' );

		await expect( postList ).toBeVisible();
		await expect( postList.getByRole( 'link' ) ).toHaveText( 'E2E part 2' );
	} );
} );

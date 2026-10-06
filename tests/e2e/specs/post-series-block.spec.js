/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

const BLOCK_ERROR = 'This block has encountered an error';

test.describe( 'Post Series List block', () => {
	let series;
	let posts;

	test.beforeAll( async ( { requestUtils } ) => {
		// The site editor test edits a Twenty Twenty-Five template.
		await requestUtils.activateTheme( 'twentytwentyfive' );
		await requestUtils.activatePlugin( 'wp-post-series' );
		series = await requestUtils.createRecord( 'post_series', {
			name: 'E2E Series',
			description: 'Series for <a href="/about/">tests</a>.',
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

	test( 'series box toggle is accessible on the frontend', async ( {
		page,
	} ) => {
		await page.goto( posts[ 0 ].link );

		const box = page.locator( '.wp-post-series-box' );
		const postList = box.locator( '.wp-post-series-box__posts' );
		const toggle = box.getByRole( 'checkbox', {
			name: 'Show all posts in this series',
		} );

		await expect( box ).toContainText( 'This is post 1 of 2' );
		await expect( postList ).toBeHidden();
		// Collapsed links must not be reachable by keyboard or screen readers.
		await expect( postList.getByRole( 'link' ) ).toHaveCount( 0 );

		// Clicking anywhere in the header toggles the list.
		await box.locator( '.wp-post-series-box__label' ).click();
		await expect( toggle ).toBeChecked();
		await expect( postList ).toBeVisible();
		await expect( postList.getByRole( 'link' ) ).toHaveText( 'E2E part 2' );

		// Links in the header stay clickable above the toggle overlay.
		await box
			.locator( '.wp-post-series-box__description' )
			.getByRole( 'link', { name: 'tests' } )
			.click( { trial: true } );

		// Space (native) and Enter both toggle from the keyboard.
		await toggle.focus();
		await page.keyboard.press( 'Space' );
		await expect( toggle ).not.toBeChecked();
		await expect( postList ).toBeHidden();
		await page.keyboard.press( 'Enter' );
		await expect( toggle ).toBeChecked();
		await expect( postList ).toBeVisible();
	} );

	test( 'theme overrides of the 2.0.0 template still toggle from the keyboard', async ( {
		page,
	} ) => {
		await page.goto( `${ posts[ 0 ].link }?legacy-series-template` );

		const box = page.locator( '.wp-post-series-box' );
		const postList = box.locator( '.wp-post-series-box__posts' );
		const legacyLabel = box.locator( 'label.wp-post-series-box__label' );
		const toggle = box.locator( '.wp-post-series-box__toggle_checkbox' );

		// The label is no longer a separate tab stop; the checkbox is the control.
		await expect( legacyLabel ).not.toHaveAttribute( 'tabindex' );
		await expect( postList ).toBeHidden();

		await toggle.focus();
		await page.keyboard.press( 'Enter' );
		await expect( postList ).toBeVisible();
		await page.keyboard.press( 'Space' );
		await expect( postList ).toBeHidden();

		await legacyLabel.click();
		await expect( postList ).toBeVisible();
	} );
} );

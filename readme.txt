=== WP Post Series ===
Contributors: mikejolley
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=mike.jolley@me.com&currency_code=&amount=&return=&item_name=Buy+me+a+coffee+for+WP+Post+Series
Tags: series, post series, organize, course, book
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.1.0

Publish and link together a series of posts using a new "series" taxonomy. Automatically display links to other posts in a series above your content.

== Description ==

WP Post Series is a _lightweight_ plugin for making a series of posts and showing information about the series on the post page. 

Posts in a series will automatically show a series box (prepended before the post content), or you can insert them manually using the Post Series List block.

= Features =

* Add post series using the familiar WordPress UI and give each one a description.
* Assign post series to your posts.
* Filter posts in the backend by series.
* Show the series above the post content or using the Post Series List block in the editor.
* Developer friendly code — Custom taxonomies & template files.

= Contributing and reporting bugs =

You can contribute code and localizations to this plugin via GitHub: [https://github.com/mikejolley/wp-post-series](https://github.com/mikejolley/wp-post-series)

= Support =

Use the WordPress.org forums for community support - I cannot offer support directly for free. If you spot a bug, you can of course log it on [Github](https://github.com/mikejolley/wp-post-series) instead where I can act upon it more efficiently.

If you want help with a customisation, hire a developer!

== Installation ==

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser. To do an automatic install, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type "WP Post Series" and click Search Plugins. Once you've found the plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by clicking _Install Now_.

= Manual installation =

The manual installation method involves downloading the plugin and uploading it to your webserver via your favourite FTP application.

* Download the plugin file to your computer and unzip it
* Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress `wp-content/plugins/` directory.
* Activate the plugin from the Plugins menu within the WordPress admin.

== Frequently Asked Questions ==

= Can I use series with pages or custom post types? =

Yes. Series are available on posts by default. Add other post types with the `wp_post_series_post_types` filter, for example in your theme's functions.php:

`add_filter( 'wp_post_series_post_types', function ( $post_types ) {
	$post_types[] = 'book';
	return $post_types;
} );`

== Screenshots ==

1. Post Series Display 
2. Post Series Block Settings

== Changelog ==

= 2.1.0 =
* Accessibility - The series box toggle is now a labelled checkbox that screen readers and keyboards can operate, and collapsed posts are hidden from them. The header markup is valid HTML (no block elements inside a `<label>`). If you have customized series-box.php, update it based on the new version.
* Fix - Post Series block crashing in the editor on current WordPress versions, and when used on pages or in the site and widget editors.
* Fix - Series box no longer appears in automatically generated excerpts.
* Fix - PHP warning in the classic editor meta box for posts without a series. Props [@pixeline](https://github.com/pixeline).
* Fix - Series column now shows in the posts list even when the Categories column is removed.
* Fix - Series admin screens no longer use "Tags" wording, such as "Go to Tags" after editing a series. (#30)
* Feature - Series can be used with other post types via the `wp_post_series_post_types` filter. (#33)
* Tweak - The block is now called "Post Series List" and the taxonomy "Series", so they're distinct from WordPress's own Terms block for series. Existing blocks and series are unaffected.
* Performance - Series lists no longer run a database query per post.
* Fix - Fatal error when the series archive link can't be generated.
* Fix - `$post_in_series` template variable is now `0` when the post isn't in the displayed series (was `1`).
* Fix - Template arguments can no longer change which template file is loaded.
* Dev - Block updated to API version 3 and registered with block.json, so it runs in the iframed editor.
* Dev - The editor script handle is now `mj-wp-post-series-editor-script` (from block.json); the `wp-post-series-block` and `wp-post-series-vendors` handles were removed. Frontend handles are unchanged.
* Dev - Build tooling moved to @wordpress/scripts.
* Dev - Added PHPUnit and end-to-end test suites, run in CI with wp-env.
* Dev - Requires WordPress 6.6 and PHP 7.4.

= 2.0.0 =
* Refactor - Improved template markup and default styling. If you have customized the series-box.php file, be sure to update it based on the new version to take advantage of the new functionality.
* Feature - New Post Series Block for use in the new editor.
* Feature - If the post does not contain the post series block, post series are still injected via the_content hook.
* Refactor - Rewritten majority of plugin using more up to date standards and namespaces.
* Refactor - Content toggle no longer relies om jQuery.
* Fix - Made series taxonomy visible in the Gutenberg editor.

= 1.1.0 =
* Scheduled post handling! Scheduled posts will contribute to your series count, and the title and scheduled date will be listed along with your other series items.
* Removed bundled language files.
* Added POT file.

= 1.0.1 =
* Added CSS Class for Series.
* Fix taxonomy class name.
* Show description of series even if the number of posts == 1.
* Fix link to repo in readme.
* Added swedish translation.
* Tweaked styles to work with default themes.

= 1.0.0 =
* First stable release.

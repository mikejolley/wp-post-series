=== WP Post Series ===
Contributors: mikejolley
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=mike.jolley@me.com&currency_code=&amount=&return=&item_name=Buy+me+a+coffee+for+WP+Post+Series
Tags: series, post series, organize, course, book
Requires at least: 5.0
Tested up to: 5.6
Requires PHP: 5.6
Stable tag: 1.1.0

Publish and link together a series of posts using a new "series" taxonomy. Automatically display links to other posts in a series above your content.

== Description ==

WP Post Series is a _lightweight_ plugin for making a series of posts and showing information about the series on the post page. The information box is prepended to the post content, and it can work with any theme (given a bit of CSS styling) - no setup required.

= Features =

* Add post series using the familiar WordPress UI and give each one a description.
* Assign post series to your posts.
* Filter posts in the backend by series.
* Shows the series above the post content.
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
* Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's `wp-content/plugins/` directory.
* Activate the plugin from the Plugins menu within the WordPress admin.

== Screenshots ==

1. Post Series Display 
2. Post Series Taxonomy Screen 
3. Post Series Selection within the editor

== Changelog ==

= 2.0.0 =
* Refactor - Rewritten majority of plugin using more up to date standards and namespaces.
* Refactor - Improved template markup and default styling.
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

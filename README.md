=== RSS Feed Fixer ===
Contributors: barzik
Tags: rtl, rss, feed,
Requires at least: 3.5.1
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

RSS Feed Fixer is changing the feed and enabling RTL support in readers that does not support RTL. 

== Description ==

RSS Feed Fixer is designed to allow RTL language WordPress blogs to be viewed in RSS readers that does not support RTL.
Feedly is the best example. Without RSS Feed Fixer, the RTL content in Feedly is impossible to view. With RSS Feed Fixer
you user will be able to view the content or the excerpt in RTL form. 

After activating RSS Feed Fixer all the paragraph elements in the content or the excerpts will include rtl dir attribute
and will be aligned to the right.

This plugin does not have admin interface and it is very easy to use. Please pay attention that feedly and other RSS 
readers sometime cache the feeds, so the it is possible that the changes will be seen in the posts that are being 
published after the plugin activation.

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'RSS Feed Fixer'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `rtl-feed-fixer.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `rtl-feed-fixer.zip`
2. Extract the `rtl-feed-fixer` directory to your computer
3. Upload the `rtl-feed-fixer` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard



== Screenshots ==

1. Feedly example: before.

2. Feedly example: after.

== Changelog ==

= 1.0 =
* Initial version
* automated testing

== Testing ==
This plugin includes automated testing in standard WordPress develop environment.
To run the test, please make sure that the plugin will be under WordPress develop plugins branch or make sure that
export WP_DEVELOP_DIR="/path/to/wordpress/develop/"

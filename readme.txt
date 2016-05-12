=== FEEDZY RSS Feeds ===
Contributors: themeisle,codeinwp
Tags: RSS, SimplePie, shortcode, feed, thumbnail, image, rss feeds, aggregator, tinyMCE, WYSIWYG, MCE, UI, flux, plugin, WordPress, widget, importer, XML, ATOM, API, parser
Requires at least: 3.7
Tested up to: 4.5.2
Stable tag: 2.8.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

 
FEEDZY RSS Feeds is a small & lightweight plugin. Fast and easy to use, it aggregates RSS feeds into your site with shortcodes & widgets. 


== Description ==

FEEDZY RSS Feeds is a small and lightweight RSS aggregator plugin. Fast and very easy to use, it allows you to aggregate multiple RSS feeds into your WordPress site through fully customizable shortcodes & widgets.

The plugin uses the SimplePie php CLASS natively included in WordPress. SimplePie is a RSS parser that can read the information contained in a feed, process it, and finally display it.

FEEDZY RSS Feeds therefore supports any additional library and uses only the bare minimum to ensure good performance (minimalistic CSS + cache). This will ensure compatibility with any hosting provider that supports WordPress, but if for any reason it doesn't work for yours feel free to <a href="http://themeisle.com/contact/" rel="nofollow">contact us</a>.

You may use this plugin in your widgets and your pages and reuse the shortcode + widget several times within the same page.

By activating this plugin, your cover picture will be inserted into your RSS feeds. By doing so, you'll make it will easier for external sites to retrieve images from your feeds.

**Please ask for help or report bugs if anything goes wrong. It is the best way to make the community benefit!**


 = Shortcode Parameters =

`
* feeds
* max
* feed_title
* target
* title
* meta
* summary
* summarylength
* thumb
* default
* size
* keywords_title
`


**Plugin is now using the TinyMCE API to improve UI and makes it easy to insert shortcodes!**


= Basic example =

`[feedzy-rss feeds="http://themeisle.com/feed"]`


= Advanced example =

`[feedzy-rss feeds="http://themeisle.com/feed" max="2" feed_title="yes" target="_blank" title="50" meta="yes" summary="yes" summarylength="300" thumb="yes" size="100" default="http://your-site/default-image.jpg" keywords_title="WordPress"]`


= Available Hooks =

`
* feedzy_feed_items
* feedzy_item_keyword
* feedzy_item_attributes
* feedzy_thumb_output
* feedzy_title_output
* feedzy_meta_args
* feedzy_meta_output
* feedzy_summary_input
* feedzy_summary_output
* feedzy_global_output
* feedzy_thumb_sizes
* feedzy_feed_blacklist_images
* feedzy_default_image
* feedzy_default_error
* shortcode_atts_feedzy_default
`
This plugin is maintained and supported by Themeisle, check out some of the other <a href="http://themeisle.com/wordpress-plugins/" rel="friend">WordPress plugins</a> and <a href="http://themeisle.com/wordpress-themes/free/" rel="friend">free themes</a> we've developed.
 
= Languages =

* English
* French
* Serbian [Borisa Djuraskovic](http://www.webhostinghub.com/ "Borisa Djuraskovic")
* Japanese [sama55](http://askive.cmsbox.jp/ "sama55")
* German [ralfsteck](https://profiles.wordpress.org/ralfsteck/ "ralfsteck")
* Spanish [Angel Calzado](http://sintaxisweb.es "Angel Calzado")

Become a translator and send me your translation! [Contact-me](http://themeisle.com/contact "Contact")

== Installation ==

1. Upload and activate the plugin (or install it through the WP admin console)
2. Insert shortcode ! ;-)

== Frequently Asked Questions ==

= Is it responsive friendly? =

Yes it is.

= My feed is not displayed and the following message appears "Sorry, this feed is currently unavailable or does not exists anymore." =

You have to check first if your feed is valid. Please test it here: https://validator.w3.org/feed/


== Screenshots ==

1. Simple example
2. Inserting a shortcode in the WYSIWYG
3. Widget admin
4. Widget render


== Changelog ==

= 2.8 - 04/14/2016 = 
* Tested on WP 4.5 with success! 
* themeisle added as plugin author 
* Fix a PHP7 warning in the widget contructor regarding [this topic](https://wordpress.org/support/topic/php7-debug-error?replies=1)

= 2.7.1 =
* Remove unnecessary and redundant feedzy_wp_widget_box div container in the widget's body
* Best compliance with WordPress coding standards
* Fix a PHP warning on SimplePie error display & log
* Readme.txt update

= 2.7 =
* Better displaying of fetching feeds errors (see error message div error-attr).
* Write errors in the WP log file
* New hook: feedzy_default_error to filter error message
* New hook: shortcode_atts_feedzy_default to filter default shortcode attibutes
* Add a link to validate feed in the TinyMCE popup modal
* French translation update
* Remove unnecessary functions
* New constant FEEDZY_VERSION
* readme.txt and hooks documentation update

= 2.6.2 =
* Spanish translation thanks to [Angel Calzado](http://sintaxisweb.es "Angel Calzado")
* PHP issue fix in feedzy_returnImage() "strpos() expects parameter 1 to be string"
* Remove SSL from feeds URL to prevent fetching errors
* New hook: feedzy_default_image
* readme.txt and hooks documentation update

= 2.6.1 =
* Replace jQuery live() function by on() function to prevent JS error (fix the issue with Visual composer)
* Improve image's fetching on itunes feeds
* New feature: default WP smilies are now blacklisted from image fetching
* New hook: feedzy_feed_blacklist_images
* Tested on WP 4.3 with success!

= 2.6 =
* Fix a conflict with PageBuilder where Feedzy button does not show up in the visual editor
* Fix a typo in feedzy-rss-feeds-shortcode.php on the thumbnails span
* Replace WP_Widget by __construct() to initiate the widget because it has been deprecated since version 4.3.0
* German translation thanks to [ralfsteck](https://profiles.wordpress.org/ralfsteck/ "ralfsteck")
* Tested on WP 4.3 with success!

= 2.5.1 =
* Japanese translation thanks to [sama55](http://askive.cmsbox.jp/ "sama55")
* Image optimisation (feedzy-default.jpg)
* Improve image's fetching to avoid catching audio or video files from item description

= 2.5 =
* Improve author name fetching
* Better HTML marckup
* Fix PHP warning within the widget
* Fix CSS on IE when displaying images as a fallback
* CSS stylesheet update
* New hook: feedzy_item_attributes

= 2.4.3 =
* Improve image fetching (again...)
* Fix an issue on files encoding (UTF8)
* Minor PHP improvements on the main shortcode's function

= 2.4.2 =
* Minor fix on already encoded images names

= 2.4.1 =
* Fix an issue on img url encode
* Retrive img url in url parameters
* Fix minor PHP warning
* New hook: feedzy_add_classes_item

= 2.4 =
* New feature: 'auto' thumbs option added
* New hook: feedzy_thumb_sizes
* Fix issue on max number of feeds to display
* Fix HTML decode in the feed's title
* Minor PHP improvements
* readme.txt and hooks documentation update

= 2.3 =
* New hook: feedzy_feed_items
* New hook: feedzy_item_keyword
* Introduce SimplePie native strip_htmltags() method 
* Use PNG icon as SVG fallback on the visual editor button
* Improve plugin's files structure
* readme.txt and hooks documentation update

= 2.2.2 =
* New hook: feedzy_meta_args

= 2.2.1 =
* Minor security improvements
* Better WordPress coding standard respect
* Minor fix if does not provide item's author
* Translations update

= 2.2 =
* Minor PHP improvements
* Remove logo from plugin meta
* New hook: feedzy_summary_input
* $feedURL argument added on every available hooks
* French translation update
* readme.txt and hooks documentation update

= 2.1 =
* internationalization of feeds dates and times (date_i18n)

= 2.0 =
* Widget added
* Translation update
* Better plugin file structure
* Improve image fetching with multiple enclosures
* Tested on WP 4.1 with success!

= 1.7.1 =
* Fix typo in PHP which cause issue on fetching images

= 1.7 =
* Minor Template and CSS changes
* New hook: feedzy_thumb_output
* New hook: feedzy_title_output
* New hook: feedzy_meta_output
* New hook: feedzy_summary_output
* New hook: feedzy_global_output
* readme.txt update

= 1.6 =
* Minor CSS fix
* Add actions: add_action('rss_item', 'feedzy_include_thumbnail_RSS'); & add_action('rss2_item', 'feedzy_include_thumbnail_RSS')

= 1.5.4 =
* Plugin meta translation
* Remove unnecessary spaces

= 1.5.3 =
* TinyMCE UI translation
* Better fetching image
* Space between items is calculated based on thumbs size

= 1.5.2 =
* Plugin meta update

= 1.5.1 =
* New logo
* Minor CSS fixes

= 1.5 =
* New param added to filter item with keywords
* Default thumb added
* Fix minor php issue
* Rename files of the plugin
* New logo + screenshot (assets)

= 1.4 =
* Add "default" parameter to fill image container if no image is fetch or if it is offline
* Add more control over numeric format in max, size, title & summarylength parameters

= 1.03 =
* Shortcode can now be displayed everywhere in the page (CSS is loaded via global var)

= 1.02 =
* Error on svn tag

= 1.01 =
* Minor CSS fix.
* Minor PHP changes.
* Readme.txt updated

= 1.0 =
* First release.

== Upgrade Notice ==

= 1.5 =
* IMPORTANT: You have to reactivate the plugin after its update!

= 1.0 =
* First release.

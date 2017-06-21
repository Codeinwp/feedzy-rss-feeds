=== FEEDZY RSS Feeds Lite ===
Contributors: themeisle,codeinwp,hardeepasrani
Tags: RSS, SimplePie, shortcode, feed, thumbnail, image, rss feeds, aggregator, tinyMCE, WYSIWYG, MCE, UI, flux, plugin, WordPress, widget, importer, XML, ATOM, API, parser
Requires at least: 3.7
Tested up to: 4.8.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


FEEDZY RSS Feeds is a small & lightweight plugin. Fast and easy to use, it aggregates RSS feeds into your site with shortcodes & widgets.


== Description ==

FEEDZY RSS Feeds is a small and lightweight RSS aggregator plugin. Fast and very easy to use, it allows you to aggregate multiple RSS feeds into your WordPress site through fully customizable shortcodes & widgets.

> **Time-saving features available in the FULL version:**
>
> * Convert feed items to WordPress posts/pages/any
> * Multiple templates for feed items
> * Automatically build affiliate links
> * Parse price from product feeds
> * Blacklist specific keywords from feeds
> * Priority email support from the developer of the plugin
> * Support and updates for 12 months
>
> **[Learn more about Feedzy Full version]( https://themeisle.com/plugins/feedzy-rss-feeds/ )**


The plugin uses the SimplePie php CLASS natively included in WordPress. SimplePie is a RSS parser that can read the information contained in a feed, process it, and finally display it.

**Using the latest version you can now group feeds into categories and reuse them across your content without worrying of their url**


FEEDZY RSS Feeds therefore supports any additional library and uses only the bare minimum to ensure good performance (minimalistic CSS + cache). This will ensure compatibility with any hosting provider that supports WordPress, but if for any reason it doesn't work for yours feel free to <a href="http://themeisle.com/contact/" rel="nofollow">contact us</a>.


 = See how Feedzy can integrate with your website  =

* [Shop feed – 3 columns layout](https://demo.themeisle.com/feedzy-rss-feeds/shop-feed/)
* [Feed categories – 1 columns layout](https://demo.themeisle.com/feedzy-rss-feeds/group-feeds-categories/)
* [Large image square grid – 2 columns layout](https://demo.themeisle.com/feedzy-rss-feeds/2-columns-large-images-square-grid/)
* [Square grid template – 3 columns layout](https://demo.themeisle.com/feedzy-rss-feeds/square-template/)
* [Round grid templates – 3 columns layout](https://demo.themeisle.com/feedzy-rss-feeds/round-grid-template/)
* [Standard grid layout – 2 columns layout](https://demo.themeisle.com/feedzy-rss-feeds/grid-layout-feed-items/)
* [Blog layout – 1 column layout](https://demo.themeisle.com/feedzy-rss-feeds/blog-layout/)


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
* feedzy_item_filter
* feedzy_author_url
* feedzy_item_url_filter
* feedzy_feed_timestamp
* shortcode_atts_feedzy_default
`
This plugin is maintained and supported by Themeisle, check out some of the other <a href="http://themeisle.com/wordpress-plugins/" rel="nofollow">WordPress plugins</a> and <a href="http://themeisle.com/wordpress-themes/free/" rel="nofollow">free themes</a> we've developed.

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

= How to change cache lifetime for a specific feed =
http://docs.themeisle.com/article/547-how-to-change-cache-lifetime-for-a-specific-feed

= How to change thumbs size and aspect ratio =
http://docs.themeisle.com/article/551-how-to-change-thumbs-size-and-aspect-ratio

= What hooks and filters are available in Feedzy =
http://docs.themeisle.com/article/540-what-hooks-and-filters-are-available-in-feedzy

= How to change the blacklist image name =
http://docs.themeisle.com/article/552-how-to-change-the-blacklist-image-name

= How to decode item title =
http://docs.themeisle.com/article/548-how-to-decode-item-title-with

= How to remove featured image from blog feed =
http://docs.themeisle.com/article/554-how-to-remove-featured-image-from-blog-feed

= How to keep html in feed items content =
http://docs.themeisle.com/article/542-how-to-keep-html-in-feed-items-content

= How to remove plugin css =
http://docs.themeisle.com/article/545-how-to-remove-plugin-css

= How to remove links =
http://docs.themeisle.com/article/541-how-to-remove-links

= How to add a read more link =
http://docs.themeisle.com/article/544-how-to-add-a-read-more-link

= How to remove time from publication date =
http://docs.themeisle.com/article/553-how-to-remove-time-from-publication-date

= How to handle publication date and author content =
http://docs.themeisle.com/article/549-how-to-handle-time-and-date-content

= How to use your own inline css =
http://docs.themeisle.com/article/546-how-to-use-your-own-inline-css

= How to remove the end hellip =
http://docs.themeisle.com/article/543-how-to-remove-the-end-hellip

= How to display items in a random order =
http://docs.themeisle.com/article/550-how-to-display-items-in-a-random-order

= How to sort items alphabetically by their title =
http://docs.themeisle.com/article/633-how-to-sort-feed-items-by-title

= How to display RSS feed time and date in local time =
http://docs.themeisle.com/article/567-how-to-display-rss-feed-time-and-date-in-local-time

= How to change author url =
http://docs.themeisle.com/article/636-how-to-change-author-url

= How remove feed items duplicates by url =
http://docs.themeisle.com/article/638-how-to-eliminate-duplicate-feed-item

= How to use feedzy categories =
http://docs.themeisle.com/article/640-how-to-use-feedzy-categories

== Screenshots ==

1. Simple example
2. Inserting a shortcode in the WYSIWYG
3. Widget admin
4. Widget render


== Changelog ==
= 3.1.7 = 

* Added new sdk logic.
* Improved compatibility with the pro version.



= 3.1.5 - 30/05/2017 =
* Fixed issues with sdk notifications.
* Added more compatibility with pro version.

= 3.1.4 - 29/05/2017 =
* Added new doc on how to use feedzy categories.

= 3.1.3 - 29/05/2017 =
* Added new SDK features.
* Fixed some edge case issues on image parsing.

= 3.1.2 - 22/05/2017 =
* Fixed author protocol.
* Added core fetch_feed method.

= 3.1.1 - 22/05/2017 =
* Fixed span alt tag, replaced with title.

= 3.1.0 - 17/05/2017 =
* Added feed categories for grouping urls.
* Added support for feed to post feature.
* Fixed regex for jpeg image ( Reported by @piccart )
* Added filter for author url ( Thanks to @piccart )

= 3.0.10 - 24/04/2017 =
* Fixed wrong image regex.
* Fixed image compression.

= 3.0.9 - 21/02/2017 =
* Added wrong feed title check.

= 3.0.8 - 20/02/2017 =
* Added $sizes param to feedzy_thumb_output.
* Added check when title is empty.
* Fixed image encoding url.

= 3.0.6 - 27/01/2017 =
* Added feedzy_feed_timestamp filter.
* Fixed issue with edge cases feed urls.
* Fixed error when using [] on string vars.


= 3.0.5 - 06/01/2017 =
* Fixed issue with google news feed

= 3.0.4 - 06/01/2017 =
* Fixed thumb='auto' behaviour

= 3.0.3 - 06/01/2017 =
* Fixed blog feed feature image filter
* Improved documentation and examples

= 3.0.2 - 06/01/2017 =
* Fixed default class  back

= 3.0.1 - 05/01/2017 =
* Fixed html markup error which was breaking user websites

= 3.0.0 - 10/11/2016 =
* Refactored code base from 2.8.1 to OOP style
* Added support for PRO version
* Added new hooks feedzy_item_filter, feedzy_item_url_filter


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

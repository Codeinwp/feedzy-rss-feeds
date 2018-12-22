=== FEEDZY RSS Feeds Lite ===
Contributors: themeisle,codeinwp,hardeepasrani,contactashish13
Tags: RSS feed, autoblogging, autoblog, rss aggregator, feed to post 
Requires at least: 3.7
Requires PHP: 5.3
Tested up to: 5.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


FEEDZY RSS Feeds is an easy-to-use plugin giving you RSS aggregator and autoblogging functionality. Bring the best RSS feeds to your site.

## Description ##

## RSS FEEDS WITH FEEDZY: AUTOBLOGGING MADE SIMPLE ##

Feedzy is an easy-to-install RSS aggregator WordPress plugin which lets you collate content from all over the internet onto your site. Add your RSS feeds, and then use shortcodes and RSS feeds to get your site running within minutes.

The plugin is easy to use, but we’ve got you covered on all the features you need, including responsive design, caching control, autoblogging (PRO), extra templates and custom post types (PRO) and a lot more.

## A FEW REASONS WHY YOU’LL LOVE FEEDZY RSS FEEDS LITE:  ##

**1. Simple to install**
Install the plugin, add your RSS feeds, and then use shortcodes and widgets to display content on your site. Easy!


**2. Pretty as a picture**
Create beautiful RSS feeds with images, from all your favorite sites (yes, including complex media from sites like YouTube).


**3. Responsive & intuitive**
No matter the platform, your content will be fully responsive across mobile and tablet.


**4. Caching control**
Choose right from the shortcode how often you want content from your RSS feeds to get updated.
 
**5. Choose your categories**
Group your RSS feeds into categories, and reuse them across your content without worrying about their URL.
 
**6. You’re in great company**
Join over 20,000 Feedzy users, and the half a million WordPress sites using ThemeIsle prodcuts :)


> **UPGRADE TO THE FEEDZY PRO FOR THESE ADDED FEATURES:**
>
> * Integration with WordAI to avoid duplicate content
> * Automatically convert RSS feed items to WordPress pages & posts (feed to post)
> * Choice of templates to display content, including audio playback template
> * Automatically add your affiliate links to RSS feeds for business autoblogging
> * Parse and display pricing from product feeds
> * Control your feed: blacklist specific keywords
> * Unlimited support and updates from our team for the duration of your licence
>
> **[Learn more about Feedzy Pro]( https://themeisle.com/plugins/feedzy-rss-feeds/ )**

 ## See how Feedzy can integrate with your website

* [Audio playback template](https://demo.themeisle.com/feedzy-rss-feeds/audio-feed-template/)
* [Shop feed – 3 columns layout](https://demo.themeisle.com/feedzy-rss-feeds/shop-feed/)
* [Feed categories – 1 columns layout](https://demo.themeisle.com/feedzy-rss-feeds/group-feeds-categories/)
* [Large image square grid – 2 columns layout](https://demo.themeisle.com/feedzy-rss-feeds/2-columns-large-images-square-grid/)
* [Square grid template – 3 columns layout](https://demo.themeisle.com/feedzy-rss-feeds/square-template/)
* [Round grid templates – 3 columns layout](https://demo.themeisle.com/feedzy-rss-feeds/round-grid-template/)
* [Standard grid layout – 2 columns layout](https://demo.themeisle.com/feedzy-rss-feeds/grid-layout-feed-items/)
* [Blog layout – 1 column layout](https://demo.themeisle.com/feedzy-rss-feeds/blog-layout/)


You can use this plugin in your widgets or pages, reusing the shortcode and widget several times within the same page.
 
By activating this plugin, your cover image will be inserted into your RSS feeds, making it will easier for external sites to retrieve images from your feeds.

## TECHNICAL NOTES:

FEEDZY RSS Feeds supports any additional library and takes up minimal space in your cache/CSS to ensure high performance. This ensures compatibility with any hosting provider that supports WordPress, but if for any reason it doesn’t work for yours feel free to [contact us](http://themeisle.com/contact/).

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
* refresh
* size
* keywords_title
`


**Plugin is now using the TinyMCE API to improve UI and makes it easy to insert shortcodes!**


= Basic example =

`[feedzy-rss feeds="http://themeisle.com/feed"]`


= Advanced example =

`[feedzy-rss feeds="http://themeisle.com/feed" max="2" feed_title="yes" target="_blank"  refresh="12_hours" title="50" meta="yes" summary="yes" summarylength="300" thumb="yes" size="100" default="http://your-site/default-image.jpg" keywords_title="WordPress"]`


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

= Where do i find the plugin documentation = 
[http://docs.themeisle.com/article/658-feedzy-rss-feeds](http://docs.themeisle.com/article/658-feedzy-rss-feeds)

= How to fix images that are not showing in the feed = 
[http://docs.themeisle.com/article/666-how-to-fix-images-that-are-not-showing-in-the-feed](http://docs.themeisle.com/article/666-how-to-fix-images-that-are-not-showing-in-the-feed)

= How to change cache lifetime for a specific feed =
[http://docs.themeisle.com/article/547-how-to-change-cache-lifetime-for-a-specific-feed](http://docs.themeisle.com/article/547-how-to-change-cache-lifetime-for-a-specific-feed)

= How to change thumbs size and aspect ratio =
[http://docs.themeisle.com/article/551-how-to-change-thumbs-size-and-aspect-ratio](http://docs.themeisle.com/article/551-how-to-change-thumbs-size-and-aspect-ratio)

= What hooks and filters are available in Feedzy =
[http://docs.themeisle.com/article/540-what-hooks-and-filters-are-available-in-feedzy](http://docs.themeisle.com/article/540-what-hooks-and-filters-are-available-in-feedzy)

= How to change the blacklist image name =
[http://docs.themeisle.com/article/552-how-to-change-the-blacklist-image-name](http://docs.themeisle.com/article/552-how-to-change-the-blacklist-image-name)

= How to decode item title =
[http://docs.themeisle.com/article/548-how-to-decode-item-title-with](http://docs.themeisle.com/article/548-how-to-decode-item-title-with)

= How to remove featured image from blog feed =
[http://docs.themeisle.com/article/554-how-to-remove-featured-image-from-blog-feed](http://docs.themeisle.com/article/554-how-to-remove-featured-image-from-blog-feed)

= How to keep html in feed items content =
[http://docs.themeisle.com/article/542-how-to-keep-html-in-feed-items-content](http://docs.themeisle.com/article/542-how-to-keep-html-in-feed-items-content)

= How to remove plugin css =
[http://docs.themeisle.com/article/545-how-to-remove-plugin-css](http://docs.themeisle.com/article/545-how-to-remove-plugin-css)

= How to remove links =
[http://docs.themeisle.com/article/541-how-to-remove-links](http://docs.themeisle.com/article/541-how-to-remove-links)

= How to add a read more link =
[http://docs.themeisle.com/article/544-how-to-add-a-read-more-link](http://docs.themeisle.com/article/544-how-to-add-a-read-more-link)

= How to remove time from publication date =
[http://docs.themeisle.com/article/553-how-to-remove-time-from-publication-date](http://docs.themeisle.com/article/553-how-to-remove-time-from-publication-date)

= How to handle publication date and author content =
[http://docs.themeisle.com/article/549-how-to-handle-time-and-date-content](http://docs.themeisle.com/article/549-how-to-handle-time-and-date-content)

= How to use your own inline css =
[http://docs.themeisle.com/article/546-how-to-use-your-own-inline-css](http://docs.themeisle.com/article/546-how-to-use-your-own-inline-css)

= How to remove the end hellip =
[http://docs.themeisle.com/article/543-how-to-remove-the-end-hellip](http://docs.themeisle.com/article/543-how-to-remove-the-end-hellip)

= How to display items in a random order =
[http://docs.themeisle.com/article/550-how-to-display-items-in-a-random-order](http://docs.themeisle.com/article/550-how-to-display-items-in-a-random-order)

= How to sort items alphabetically by their title =
[http://docs.themeisle.com/article/633-how-to-sort-feed-items-by-title](http://docs.themeisle.com/article/633-how-to-sort-feed-items-by-title)

= How to display RSS feed time and date in local time =
[http://docs.themeisle.com/article/567-how-to-display-rss-feed-time-and-date-in-local-time](http://docs.themeisle.com/article/567-how-to-display-rss-feed-time-and-date-in-local-time)

= How to change author url =
[http://docs.themeisle.com/article/636-how-to-change-author-url](http://docs.themeisle.com/article/636-how-to-change-author-url)

= How remove feed items duplicates by url =
[http://docs.themeisle.com/article/638-how-to-eliminate-duplicate-feed-item](http://docs.themeisle.com/article/638-how-to-eliminate-duplicate-feed-item)

= How to use feedzy categories =
[http://docs.themeisle.com/article/640-how-to-use-feedzy-categories](http://docs.themeisle.com/article/640-how-to-use-feedzy-categories)

= How to add a read more link to Feedzy = 
 [http://docs.themeisle.com/article/544-how-to-add-a-read-more-link-to-feedzy](http://docs.themeisle.com/article/544-how-to-add-a-read-more-link-to-feedzy) 

 = How to move Feedzy templates to your theme = 
 [http://docs.themeisle.com/article/573-how-to-move-feedzy-templates-to-your-theme](http://docs.themeisle.com/article/573-how-to-move-feedzy-templates-to-your-theme) 

 = How to eliminate duplicate feed items. = 
 [http://docs.themeisle.com/article/638-how-to-eliminate-duplicate-feed-items](http://docs.themeisle.com/article/638-how-to-eliminate-duplicate-feed-items) 

 = How to check whether the RSS feed is valid or not in Feedzy = 
 [http://docs.themeisle.com/article/716-feedzy-how-to-check-whether-the-rss-feed-is-valid-or-not](http://docs.themeisle.com/article/716-feedzy-how-to-check-whether-the-rss-feed-is-valid-or-not) 

 = How to change user agent in Feedzy = 
 [http://docs.themeisle.com/article/713-how-to-change-user-agent-in-feedzy](http://docs.themeisle.com/article/713-how-to-change-user-agent-in-feedzy) 

 = How to use proxy settings in Feedzy = 
 [http://docs.themeisle.com/article/714-how-to-use-proxy-settings-in-feezy](http://docs.themeisle.com/article/714-how-to-use-proxy-settings-in-feezy) 

 = How to import posts from feeds in Feedzy = 
 [http://docs.themeisle.com/article/742-how-to-import-posts-from-feeds-in-feedzy](http://docs.themeisle.com/article/742-how-to-import-posts-from-feeds-in-feedzy) 

 = Where can I find the import posts options in Feedzy = 
 [http://docs.themeisle.com/article/743-where-can-i-find-the-import-posts-options-in-feedzy](http://docs.themeisle.com/article/743-where-can-i-find-the-import-posts-options-in-feedzy) 

 = How to use WordAI to Rephrase RSS content in Feedzy = 
 [http://docs.themeisle.com/article/746-how-to-use-wordai-to-rephrase-rss-content-in-feedzy](http://docs.themeisle.com/article/746-how-to-use-wordai-to-rephrase-rss-content-in-feedzy) 

 = Install and use the premium version of Feedzy RSS Feeds = 
 [http://docs.themeisle.com/article/783-install-and-use-the-premium-version-of-feedzy-rss-feeds](http://docs.themeisle.com/article/783-install-and-use-the-premium-version-of-feedzy-rss-feeds) 

 = Feedzy RSS Feeds Documentation = 
 [https://docs.themeisle.com/article/658-feedzy-rss-feeds-documentation](https://docs.themeisle.com/article/658-feedzy-rss-feeds-documentation) 

 = What actions and filters are available in Feedzy = 
 [https://docs.themeisle.com/article/540-what-actions-and-filters-are-available-in-feedzy](https://docs.themeisle.com/article/540-what-actions-and-filters-are-available-in-feedzy) 

 = How to change thumbs size and aspect ratio Feedzy = 
 [https://docs.themeisle.com/article/551-how-to-change-thumbs-size-and-aspect-ratio-feedzy](https://docs.themeisle.com/article/551-how-to-change-thumbs-size-and-aspect-ratio-feedzy) 

 = How to display RSS feed time and date in local time = 
 [https://docs.themeisle.com/article/567-how-to-display-rss-feed-time-and-date-in-local-time](https://docs.themeisle.com/article/567-how-to-display-rss-feed-time-and-date-in-local-time) 

 = How to add a read more link to Feedzy = 
 [https://docs.themeisle.com/article/544-how-to-add-a-read-more-link-to-feedzy](https://docs.themeisle.com/article/544-how-to-add-a-read-more-link-to-feedzy) 

 = How to use feedzy categories = 
 [https://docs.themeisle.com/article/640-how-to-use-feedzy-categories](https://docs.themeisle.com/article/640-how-to-use-feedzy-categories) 

 = How to use your own inline css = 
 [https://docs.themeisle.com/article/546-how-to-use-your-own-inline-css](https://docs.themeisle.com/article/546-how-to-use-your-own-inline-css) 

 = How to change cache lifetime for a specific feed = 
 [https://docs.themeisle.com/article/547-how-to-change-cache-lifetime-for-a-specific-feed](https://docs.themeisle.com/article/547-how-to-change-cache-lifetime-for-a-specific-feed) 

 = How to remove featured image from blog feed = 
 [https://docs.themeisle.com/article/554-how-to-remove-featured-image-from-blog-feed](https://docs.themeisle.com/article/554-how-to-remove-featured-image-from-blog-feed) 

 = How to handle publication date and author content = 
 [https://docs.themeisle.com/article/549-how-to-handle-publication-date-and-author-content](https://docs.themeisle.com/article/549-how-to-handle-publication-date-and-author-content) 

 = How to keep html in feed items content = 
 [https://docs.themeisle.com/article/542-how-to-keep-html-in-feed-items-content](https://docs.themeisle.com/article/542-how-to-keep-html-in-feed-items-content) 

 = How to fix images that are not showing in the feed = 
 [https://docs.themeisle.com/article/666-how-to-fix-images-that-are-not-showing-in-the-feed](https://docs.themeisle.com/article/666-how-to-fix-images-that-are-not-showing-in-the-feed) 

 = How to display items in a random order = 
 [https://docs.themeisle.com/article/550-how-to-display-items-in-a-random-order](https://docs.themeisle.com/article/550-how-to-display-items-in-a-random-order) 

 = How to remove links = 
 [https://docs.themeisle.com/article/541-how-to-remove-links](https://docs.themeisle.com/article/541-how-to-remove-links) 

 = How to move Feedzy templates to your theme = 
 [https://docs.themeisle.com/article/573-how-to-move-feedzy-templates-to-your-theme](https://docs.themeisle.com/article/573-how-to-move-feedzy-templates-to-your-theme) 

 = How to remove plugin css = 
 [https://docs.themeisle.com/article/545-how-to-remove-plugin-css](https://docs.themeisle.com/article/545-how-to-remove-plugin-css) 

 = How to remove time from publication date = 
 [https://docs.themeisle.com/article/553-how-to-remove-time-from-publication-date](https://docs.themeisle.com/article/553-how-to-remove-time-from-publication-date) 

 = How to remove the end hellip = 
 [https://docs.themeisle.com/article/543-how-to-remove-the-end-hellip](https://docs.themeisle.com/article/543-how-to-remove-the-end-hellip) 

 = How to decode item title = 
 [https://docs.themeisle.com/article/548-how-to-decode-item-title](https://docs.themeisle.com/article/548-how-to-decode-item-title) 

 = How to sort feed items by title = 
 [https://docs.themeisle.com/article/633-how-to-sort-feed-items-by-title](https://docs.themeisle.com/article/633-how-to-sort-feed-items-by-title) 

 = How to import posts from feeds in Feedzy = 
 [https://docs.themeisle.com/article/742-how-to-import-posts-from-feeds-in-feedzy](https://docs.themeisle.com/article/742-how-to-import-posts-from-feeds-in-feedzy) 

 = How to change author url = 
 [https://docs.themeisle.com/article/636-how-to-change-author-url](https://docs.themeisle.com/article/636-how-to-change-author-url) 

 = How to eliminate duplicate feed items. = 
 [https://docs.themeisle.com/article/638-how-to-eliminate-duplicate-feed-items](https://docs.themeisle.com/article/638-how-to-eliminate-duplicate-feed-items) 

 = How to check whether the RSS feed is valid or not in Feedzy = 
 [https://docs.themeisle.com/article/716-how-to-check-whether-the-rss-feed-is-valid-or-not-in-feedzy](https://docs.themeisle.com/article/716-how-to-check-whether-the-rss-feed-is-valid-or-not-in-feedzy) 

 = How to change the blacklist image name = 
 [https://docs.themeisle.com/article/552-how-to-change-the-blacklist-image-name](https://docs.themeisle.com/article/552-how-to-change-the-blacklist-image-name) 

 = How to use proxy settings in Feedzy = 
 [https://docs.themeisle.com/article/714-how-to-use-proxy-settings-in-feedzy](https://docs.themeisle.com/article/714-how-to-use-proxy-settings-in-feedzy) 

 = Where can I find the import posts options in Feedzy = 
 [https://docs.themeisle.com/article/743-where-can-i-find-the-import-posts-options-in-feedzy](https://docs.themeisle.com/article/743-where-can-i-find-the-import-posts-options-in-feedzy) 

 = How to change user agent in Feedzy = 
 [https://docs.themeisle.com/article/713-how-to-change-user-agent-in-feedzy](https://docs.themeisle.com/article/713-how-to-change-user-agent-in-feedzy) 

 = How to use WordAI to Rephrase RSS content in Feedzy = 
 [https://docs.themeisle.com/article/746-how-to-use-wordai-to-rephrase-rss-content-in-feedzy](https://docs.themeisle.com/article/746-how-to-use-wordai-to-rephrase-rss-content-in-feedzy) 

 = Install and use the premium version of Feedzy RSS Feeds = 
 [https://docs.themeisle.com/article/783-install-and-use-the-premium-version-of-feedzy-rss-feeds](https://docs.themeisle.com/article/783-install-and-use-the-premium-version-of-feedzy-rss-feeds) 

 = How to sort feed items by date = 
 [https://docs.themeisle.com/article/817-how-to-sort-feed-items-by-date](https://docs.themeisle.com/article/817-how-to-sort-feed-items-by-date) 

 = How to add rel="nofollow" to feed links = 
 [https://docs.themeisle.com/article/839-how-to-add-relnofollow-to-feed-links](https://docs.themeisle.com/article/839-how-to-add-relnofollow-to-feed-links) 

 = What to do when you get Warning: ./cache is not writeable = 
 [https://docs.themeisle.com/article/840-what-to-do-when-you-get-warning-cache-is-not-writeable](https://docs.themeisle.com/article/840-what-to-do-when-you-get-warning-cache-is-not-writeable) 

 = How to exclude feeds with certain words in it = 
 [https://docs.themeisle.com/article/850-how-to-exclude-feeds-with-certain-words-in-it](https://docs.themeisle.com/article/850-how-to-exclude-feeds-with-certain-words-in-it) 

 = How to add canonical tags for imported posts = 
 [https://docs.themeisle.com/article/841-how-to-add-canonical-tags-for-imported-posts](https://docs.themeisle.com/article/841-how-to-add-canonical-tags-for-imported-posts) 

 = How to display thumbnail image from the feeds = 
 [https://docs.themeisle.com/article/871-how-to-display-thumbnail-image-from-the-feeds](https://docs.themeisle.com/article/871-how-to-display-thumbnail-image-from-the-feeds) 

 = How to change feed items order = 
 [https://docs.themeisle.com/article/864-how-to-change-feed-items-order](https://docs.themeisle.com/article/864-how-to-change-feed-items-order) 

 = How to use WordAI or SpinnerChief to Rephrase RSS content in Feedzy = 
 [https://docs.themeisle.com/article/746-how-to-use-wordai-or-spinnerchief-to-rephrase-rss-content-in-feedzy](https://docs.themeisle.com/article/746-how-to-use-wordai-or-spinnerchief-to-rephrase-rss-content-in-feedzy) 

 = How to add affiliate referrals to feed URLs in Feedzy = 
 [https://docs.themeisle.com/article/715-how-to-add-affiliate-referrals-to-feed-urls-in-feedzy](https://docs.themeisle.com/article/715-how-to-add-affiliate-referrals-to-feed-urls-in-feedzy) 

 = How price is displayed from the feed = 
 [https://docs.themeisle.com/article/923-how-price-is-displayed-from-the-feed](https://docs.themeisle.com/article/923-how-price-is-displayed-from-the-feed) 

 = How to find Feed URL for Feedzy RSS Feeds = 
 [https://docs.themeisle.com/article/799-how-to-find-feed-url-for-feedzy-rss-feeds](https://docs.themeisle.com/article/799-how-to-find-feed-url-for-feedzy-rss-feeds) 

 = In Feedzy how do I... = 
 [https://docs.themeisle.com/article/942-in-feedzy-how-do-i](https://docs.themeisle.com/article/942-in-feedzy-how-do-i) 

 == Screenshots ==

1. Simple example
2. Inserting a shortcode in the WYSIWYG
3. Widget admin
4. Widget render


== Changelog ==
= 3.3.2 - 2018-12-22  = 

* Option to handle HTTP images in the shortcode
* Option to specify nofollow for links in the shortcode
* Fix Gutenberg block
* Add video tutorials under Help menu
* Add support for extracting price from custom feed tags


= 3.3.1 - 2018-11-05  = 

* Import Posts enabled for plan 1 users
* Fixed issue with some idiosyncratic feeds


= 3.3.0 - 2018-08-21  = 

* Improve readme plugin description.
* Improves compatibility with Gutenberg plugin.
* Improves image detection from feeds.


= 3.2.12 - 2018-08-16  = 

* Fixed compatibility with the Gutenberg block
* Added option to disable the featured image from being added to the website RSS feed
* Fixed problem with excluding keywords not working
* Updated the readme file


= 3.2.11 - 2018-06-26  = 

* New Gutenberg block for Feedzy RSS Feeds
* Fixed curl SSL problem with Feeds with HTTPS
* Fix content type, conflicting with Gutenberg
* Added compatibility with the pro version for full text import


= 3.2.10 - 2018-04-02  = 

* Adds shortcode attribute for feed items order ( title ASC/DESC, date ASC/DESC).
* Improve documentation and examples. 


= 3.2.9 - 2018-03-07  = 

* Automatically fix deprecated google  news feeds. 
* Improve compatibility with the pro version.


= 3.2.8 - 2018-02-20  = 

* Fix issue with medium feeds.
* Improves extensibility using various hooks. 
* Fix feeds without schema protocol.


= 3.2.7 - 2018-01-05  = 

* Fix compatibility with SiteOrigin Page Builder.
* Adds full content import from feed.
* Fix issue with img scraped from articles.


= 3.2.6 - 2017-11-16  = 

* Adds compatibility with WordPress 4.9


= 3.2.5 - 2017-11-03  =
 * Fix for double slash issue in image path.
 * Fix for private ips when proxy is used.
 * Add FAQ in sync with helpscout docs.



= 3.2.4 - 2017-10-13  =

* Fix for assets enqueue, loading them where are needed only.
* Removes duplicates readme.md files.


= 3.2.1 - 2017-10-12  = 

* Adds global settings page. 
* Adds User Agent and Proxy settings. 
* Fix for some edge cases regarding images in the feed.


= 3.2.0 - 2017-08-17  = 

* Fix for image URL issue following some strange patterns. 
* Added fallback for broken feed, now if one feed from the list is not working, others will will be used.
* Added shortcode parameter for feed cache control.


= 3.1.10 - 2017-08-03  = 

* Fixed typos in shortcode builder.
* Fixed image encoding issue.


= 3.1.9 - 2017-07-21  = 

* Fixed issue with fetching images containg GET parameters.


= 3.1.8 - 2017-07-17  = 

* Fixed image fetching issues.
* Fixed link opening behaviour
* Improved description ( Thanks to @chesio )


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


 ### v3.4.3 - 2020-08-26 
 **Changes:** 
 * [Fix] HTML tags being trimmed on save of the import job
* [Fix] Issue with nonce not being checked correctly
 
 ### v3.4.2 - 2020-08-12 
 **Changes:** 
 * [Fix] Compatibility with WP 5.5
* [Feat] Link to items imported across runs in Feed2Post
 
 ### v3.4.1 - 2020-07-23 
 **Changes:** 
 * [Feat] Feed2Post - Provide more info on imported content and possible errors
* [Feat] Feed2Post - New Purge & Reset button which allows to clear data of already imported items to reimport those again
* [Fix] Feed2Post - Change cache time to 55 minutes that new items can be imported in the next run
* [Fix] Conflict with Ultimate CSV Importer
* [Fix] Sync item image options between classic and block editor
* [Fix] Posts keeps "uncategorized" category in non-English sites
 
 ### v3.4.0 - 2020-05-28 
 **Changes:** 
 * [Feat] Options to import feeds to posts
* [Feat] New [#item_source] tag for Feed to Post to display the feed source name
* [Feat] Improved Settings page style and layout
* [Feat] Use SimplePieItem's get_id to determine the uniqueness of feed items
* [Fix] WP 5.4 Feedzy block compatibility
* [Fix] Feed Caching time stuck to 12 hours
* [Fix] PHP Notice: Undefined index errors in the widget
* [Fix] Bulk activation of plugin aborts activation of subsequent plugins
* [Fix] Wrong shortcode mentioned in the Support tab
* [Fix] Notice: Undefined index: host when item has no link element
 
 ### v3.3.19 - 2020-04-08 
 **Changes:** 
 * Tested up to 5.4
 
 ### v3.3.18 - 2020-03-24 
 **Changes:** 
 * [Feat] Add support for lazyloading feed items
* [Fix] multiple_meta and offset parameters in the Feedzy widget
* [Fix] Missing Feedzy button in the Classic editor in Gutenberg
* [Fix] Conflict with RSS Aggregator in the Gutenberg editor
* [Fix] Notices when using Avada theme
* [Fix] Warnings when using multiple feeds in the shortcode
 
 ### v3.3.17 - 2020-01-30 
 **Changes:** 
 * Allow user to dictate order of meta data in the editor as well
* Ability to filter each meta data
* Fixed offset option not working correctly in the editor
* Default number of items now resets to 5
* Fixed invalid feeds causing the plugin to hang
 
 ### v3.3.16 - 2020-01-07 
 **Changes:** 
 * Fix fatal error with new version of SimplePie
* Allow user to dictate order of meta data
* Do not use force_feed for multi feeds
 
 ### v3.3.15 - 2020-01-01 
 **Changes:** 
 * fix Gutenberg bug that limits max items per feed
 
 ### v3.3.14 - 2019-12-31 
 **Changes:** 
 * Show detailed error message to logged in users if feed is not working
* Add offset parameter to skip items in a feed
* When using multiple sources, optionally show feed title
* Fix support for additional class(es) in Gutenberg
 
 ### v3.3.13 - 2019-11-30 
 **Changes:** 
 * Scrub item titles for HTML entities
* Fix widget to use all settings configured
* Fix issue with saving description length
 
 ### v3.3.12 - 2019-11-11 
 **Changes:** 
 * Tested up to 5.3
 
 ### v3.3.11 - 2019-09-24 
 **Changes:** 
 * Fix issue with replacing ellipsis
* Fix issue in widget where error message cannot be overridden
* Fix issues with some summaries getting truncated
 
 ### v3.3.10 - 2019-08-20 
 **Changes:** 
 * Fix issue with undefined index: proxy
 
 ### v3.3.9 - 2019-08-13 
 **Changes:** 
 * Fix PHP notice that shows up if meta=no
 
 ### v3.3.8 - 2019-08-12 
 **Changes:** 
 * - Fix issue with AMP pages not showing image
* - In the short code, separate behavior of meta into author, date and time
* - Add option to remove title entirely
* - Don't show [...] if summary is shorter than required
* - Add option to use default sorting when generating the short code
* - Add ability to show date/time in local timezone
 
 ### v3.3.7 - 2019-06-15 
 **Changes:** 
 * Fix: Not working in the block editor
* Feat: Referral URL can now include the URL of the item as a parameter
* Fix: Image size on mobile was overflowing the viewport
* Fix: Shortcode builder icon not visible in classic editor
* Fix: Widget options not visible in theme customizer
 
 ### v3.3.6 - 2019-05-03 
 **Changes:** 
 * Add filter to disable DB caching
* Fix issue with HTML tags not closed when feed has no items
* Fix issue with CSS file being loaded everywhere
* Tested up to WP 5.2
 
 ### v3.3.5 - 2019-02-24 
 **Changes:** 
 * Tested with WP 5.1
* Fix issue with single feeds that have errors
 
 ### v3.3.4 - 2019-02-08 
 **Changes:** 
 * Multifeed shows an error and no content if even one feed has an error
 
 ### v3.3.3 - 2019-01-31 
 **Changes:** 
 * Customize error message when no items in the feed
* Outgoing links should have rel=noopener
* Fixed fatal error in Feedzy_Rss_Feeds_Admin_Abstract::feedzy_retrieve_image
 
 ### v3.3.2 - 2018-12-22 
 **Changes:** 
 * Option to handle HTTP images in the shortcode
* Option to specify nofollow for links in the shortcode
* Fix Gutenberg block
* Add video tutorials under Help menu
* Add support for extracting price from custom feed tags
 
 ### v3.3.1 - 2018-11-05 
 **Changes:** 
 * Import Posts enabled for plan 1 users
* Fixed issue with some idiosyncratic feeds
 
 ### v3.3.0 - 2018-08-21 
 **Changes:** 
 * Improve readme plugin description.
* Improves compatibility with Gutenberg plugin.
* Improves image detection from feeds.
 
 ### v3.2.12 - 2018-08-16 
 **Changes:** 
 * Fixed compatibility with the Gutenberg block
* Added option to disable the featured image from being added to the website RSS feed
* Fixed problem with excluding keywords not working
* Updated the readme file
 
 ### v3.2.11 - 2018-06-26 
 **Changes:** 
 * New Gutenberg block for Feedzy RSS Feeds
* Fixed curl SSL problem with Feeds with HTTPS
* Fix content type, conflicting with Gutenberg
* Added compatibility with the pro version for full text import
 
 ### v3.2.10 - 2018-04-02 
 **Changes:** 
 * Adds shortcode attribute for feed items order ( title ASC/DESC, date ASC/DESC).
* Improve documentation and examples. 
 
 ### v3.2.9 - 2018-03-07 
 **Changes:** 
 * Automatically fix deprecated google  news feeds. 
* Improve compatibility with the pro version.
 
 ### v3.2.8 - 2018-02-20 
 **Changes:** 
 * Fix issue with medium feeds.
* Improves extensibility using various hooks. 
* Fix feeds without schema protocol.
 
 ### v3.2.7 - 2018-01-05 
 **Changes:** 
 * Fix compatibility with SiteOrigin Page Builder.
* Adds full content import from feed.
* Fix issue with img scraped from articles.
 
 ### v3.2.6 - 2017-11-16 
 **Changes:** 
 * Adds compatibility with WordPress 4.9
 
 ### v3.2.5 - 2017-11-03 
 **Changes:** 
 * Fix for double slash issue in image path. 
 * Fix for private ips when proxy is used. 
 * Add FAQ in sync with helpscout docs.
  
 ### v3.2.4 - 2017-10-13 
 **Changes:**
 * Fix for assets enqueue, loading them where are needed only.
* Removes duplicates readme.md files.
 
 ### v3.2.1 - 2017-10-12 
 **Changes:** 
 * Adds global settings page. 
* Adds User Agent and Proxy settings. 
* Fix for some edge cases regarding images in the feed.
 
 ### v3.2.0 - 2017-08-17 
 **Changes:** 
 * Fix for image URL issue following some strange patterns. 
* Added fallback for broken feed, now if one feed from the list is not working, others will will be used.
* Added shortcode parameter for feed cache control.
 
 ### v3.1.10 - 2017-08-03 
 **Changes:** 
 * Fixed typos in shortcode builder.
* Fixed image encoding issue.
 
 ### v3.1.9 - 2017-07-21 
 **Changes:** 
 * Fixed issue with fetching images containg GET parameters.
 
 ### v3.1.8 - 2017-07-17 
 **Changes:** 
 * Fixed image fetching issues.
* Fixed link opening behaviour
* Improved description ( Thanks to @chesio )
 
 ### v3.1.7 - 2017-06-21 
 **Changes:** 
 * Added new sdk logic.
* Improved compatibility with the pro version.
 
 ### v3.1.6 - 2017-06-02 
 **Changes:** 
 - Added sdk test.
 
 ### v3.1.5 - 2017-05-31 
 **Changes:** 
 - Fixed sdk notifications issues.
- Added compatibility with pro version.
 
 ### v3.1.4 - 2017-05-30 
 **Changes:** 
 - Added new doc for feedzy categories.
- Bump themeisle-sdk version.
 
 ### v3.1.3 - 2017-05-29 
 **Changes:** 
 - * Added new SDK features.
- * Fixed some edge case issues on image parsing.
 
 ### v3.1.2 - 2017-05-25 
 **Changes:** 
 - Release 3.1.2
 
 ### v3.1.1 - 2017-05-22 
 **Changes:** 
 - Replace alt in span with title
 
 ### v3.1.0 - 2017-05-17 
 **Changes:** 
 - Added feed to post compatibility
- Added categories to group urls
- Added filter for author url
- Fixed regex for jpeg images.
 
 ### v3.0.12 - 2017-04-24 
 **Changes:** 
 - Fixed svn commit.
 
 ### v3.0.11 - 2017-04-24 
 **Changes:** 
 - Changed deploy mechanism.
 

### 3.0.10 - 24/04/2017

**Changes:** 

- Fixed wrong image regex.
- Fixed image compression.
- Added wraith.


### 3.0.9 - 21/02/2017

**Changes:** 

- Fixed wrong empty title check.


### 3.0.8 - 20/02/2017

**Changes:** 

- Added sizes param to feedzy_thumb_output.

- Dont show items with empty title.


### 3.0.7 - 03/02/2017

**Changes:** 

- Fixed is_new when pro is active.

- Fixed redundant auto options.

- Fixed auto option in widget for image option.


### 3.0.6 - 27/01/2017

**Changes:** 

- 

- Added feedzy_feed_timestamp filter.

- Fixed issue with edge cases feed urls.

- Fixed error when using [] on string vars.


### 3.0.5 - 13/01/2017

**Changes:** 

- Fixed issue with google news feeds


### 3.0.4 - 11/01/2017

**Changes:** 

- Release 3.0.4


### 3.0.3 - 10/01/2017

**Changes:** 

- Added compatibility with the new pro options

- Added new documentation help

- Added legacy filters and functions


### 3.0.2 - 06/01/2017

**Changes:** 

- Added default image class back


### 3.0.1 - 05/01/2017

**Changes:** 

- Fixed html markup error


### 3.0.0 - 03/01/2017

**Changes:** 

- Release 3.0.0 version

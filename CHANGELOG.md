##### [Version 4.3.2](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.3.1...v4.3.2) (2023-11-03)

Bug Fixes
- Enhanced security

##### [Version 4.3.1](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.3.0...v4.3.1) (2023-11-02)

- Enhanced security related to roles of users

#### [Version 4.3.0](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.2.8...v4.3.0) (2023-10-23)

### **New Features**

- **Paraphrasing using OpenAI / Chat GPT [PRO]:** This feature enables users to utilize OpenAI's Chat GPT for paraphrasing text. It can assist in rephrasing and generating alternative wording for content.
- **Content summarization support using OpenAI / Chat GPT  [PRO]:** With this feature, users can access support for summarizing longer pieces of content, making it easier to condense and understand the main points of a text.
- **Added support to create a dynamic category [PRO]:** Feedzy can now create WordPress categories automatically during the import by taking the category name from the RSS XML Feed.
- **Added individual fallback image setting [PRO]:** This feature allows users to set specific backup images for individual import jobs, not only a general fallback image for all import jobs.
- **Ability to trim content to a particular amount of characters:** Users can now trim or limit the length of content to a specified number of characters, helping to meet length requirements or constraints.
- **Integrated Search & Replace in the content of the tag feature:** This integration enables users to search for specific content within tags and replace it with alternative text, streamlining content management.
- **Added action chain process support:** This feature facilitates the creation of multiple actions for a single Feedzy magic tag. You can now add a tag for the import job and use multiple actions on it (_paraphrase, summarize, trim, etc._).

### **Improvements**
- Added feedback popup in the import screen

### **Bug Fixes**
- Fixed compatibility issue PHP 8.1+
- Fixed compatibility issue with WP version lower than 5.8
- Fixed categories feed URL sanitization issue

##### [Version 4.2.8](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.2.7...v4.2.8) (2023-08-03)

- Improved lazy load endpoint performance
- Fixed minor layout issue
- Fixed external image support issue with Elementor

##### [Version 4.2.7](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.2.6...v4.2.7) (2023-07-06)

- Fixed image displaying issue in Gutenberg block
- Fixed import job page loading issue
- Changed top-level menu page accessibility per roles

##### [Version 4.2.6](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.2.5...v4.2.6) (2023-06-09)

- Removed branding label

##### [Version 4.2.5](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.2.4...v4.2.5) (2023-06-05)

- Fixed compatibility issue with the FSE editor
- Fixed onboarding wizard js console error
- Added About Us page integration

##### [Version 4.2.4](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.2.3...v4.2.4) (2023-05-01)

- Fixed duplicate items import issue
- Fixed conflict with the ACF plugin
- Added cron job retry method
- Fixed set custom featured image issue

##### [Version 4.2.3](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.2.2...v4.2.3) (2023-04-07)

- Fixed duplicated/deleted items and missing featured images issue
- Fixed translation service issue with the Portuguese language [PRO]

##### [Version 4.2.2](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.2.1...v4.2.2) (2023-03-31)

- Changed the document URL for the support page [#734](https://github.com/Codeinwp/feedzy-rss-feeds/issues/734)
- Added an option to delete all imported posts when purging an import job
- Fixed multiple attachments generating issue
- Update Dependencies and WordPress core tested up to version 6.2
- Added custom cron scheduler support [PRO]

##### [Version 4.2.1](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.2.0...v4.2.1) (2023-02-27)

- fix uncategorized category is being added by default to imported post
- fix assigning a different author doesn't work with item_full_content tag
- pro version compatibility

#### [Version 4.2.0](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.1.1...v4.2.0) (2023-02-24)

### Features: 

- Add onboarding screen
- Improve caching mechanism for feeds
- Ensure consistency between parameters in widgets/shortcodes/elementor contexts
- Add a no-style attribute when displaying feeds which removes any extra style 

### Fixes: 
- Minor UI fixes
- Fix redirect to archive page issues
- Fix display title on multiple feeds 
- Fix the refresh option on elementor 
- Fix additional classes parameter in Gutenberg

##### [Version 4.1.1](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.1.0...v4.1.1) (2023-01-03)

- fix className parameter in the shortcode
- Improve plugin security

#### [Version 4.1.0](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.0.5...v4.1.0) (2022-12-06)


- Improve tag items selections on the import screen [#701](https://github.com/Codeinwp/feedzy-rss-feeds/issues/701) 
- Allow adding all the Magic tags in one flow [#697](https://github.com/Codeinwp/feedzy-rss-feeds/issues/697) 
- Improve image import support in PRO
- Improve full content import in various languages

##### [Version 4.0.5](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.0.4...v4.0.5) (2022-10-26)

* Fix possible cache expiration filter change.
* Fix compatibilities with keyword filtering on full content and Elementor context.

##### [Version 4.0.4](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.0.3...v4.0.4) (2022-10-04)

* Fix cache time overwrite with filter
* Fix edge case fatal error when using with Elementor builder

##### [Version 4.0.3](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.0.2...v4.0.3) (2022-09-27)

- Fix Feedzy RSS feed cache not working on the widgets.
- Remove legacy elementor widget for new users
- Fix external image support on import for some websites
- Enter key on feed fields should load the feed automatically

##### [Version 4.0.2](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.0.1...v4.0.2) (2022-09-09)

#### Fixes
- Fix compatibility issues with Jnews-essential
- Fix duplicate upsell card
- Fix Feedzy icon not visible in the classic editor
- Change red color after import 
- Update dependencies

##### [Version 4.0.1](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v4.0.0...v4.0.1) (2022-07-19)

#### Fixes: 
* Fix typo in map content description
* Fix compatibility with WordAI and Spinnerchief connection

#### [Version 4.0.0](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.8.3...v4.0.0) (2022-07-14)

#### Features: 
* Major UI update to make the plugin much easier and cleaner to use.
* Display image from description element
* Adds lazy load render images support
* Adds support for translations
* Adds support for paraphrased content

#### Fixes: 
- Fix Deprecated functions error messages with PHP 8.1
- Fix emoji is imported as a featured image when no image is found in a post
- Fix edge case when valid feed shows up as invalid when used as part of the Feedzy category

##### [Version 3.8.3](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.8.2...v3.8.3) (2022-03-16)

#### Fixes
- A warning is printed when filter for Item Full content is used and no items found for the import
- Fix broken style on twentytwentytwo theme

##### [Version 3.8.2](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.8.1...v3.8.2) (2022-01-28)

- Add a new filter to add the custom refresh time
- Remove elementor hidden WP widget feature so feedzy widget can be used
- Fix keyword filter issue with date filter
- Fix cURL timeout error
- Add new import job setting field and manipulate custom tag data
- Fix trimming title issue for Elementor & Gutenberg

##### [Version 3.8.1](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.8.0...v3.8.1) (2021-12-20)

- Show the import job title in post row action
- Add default thumbnail image support in the external image
- [#item_url] magic tag allows opening in a new tab
- Style for the input fields of setting form

#### [Version 3.8.0](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.7.5...v3.8.0) (2021-10-19)

#### Features: 
 - Adds compatibility with pro features for [Elementor Template Builder](https://docs.themeisle.com/article/1396-elementor-compatibility-in-feedzy) and Dynamic Tags support.
 - Adds compatibility for pro [Enhanced keyword](https://docs.themeisle.com/article/1154-how-to-use-feed-to-post-feature-in-feedzy#filters) filtering support.

#### Fixes
- Fix category dropdown in Gutenberg Block
- Magic tags that are unavailable with the free version listed in one line.
- Fix typo in the import setup

##### [Version 3.7.5](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.7.4...v3.7.5) (2021-09-06)

#### Fixes
- Fix broken image issues with certain feeds
- Adds new line character support for import content
- Fix import on custom values when full content is used

##### [Version 3.7.4](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.7.3...v3.7.4) (2021-09-01)

* Fix regression issue with Elementor widget not showing with lower WP versions.
* Fix regression with cron import not working with lower WP versions.

##### [Version 3.7.3](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.7.2...v3.7.3) (2021-08-27)

#### Features
* Support default Gutenberg editor instead of importing content in the Classic block 
* Adds support for Feedzy widget in the widget block editor 
* Adds default thumbnail image support when no image is available

#### Fixes
* Title Character Limit and the Description Character Limit parameter in the Feedzy Block
* Displaying Default Thumbnail Image does not show in Block or Shortcode approach
* Custom tag is trimmed on save of the import if used inside <iframe> 
* Keyword filters break the import with PHP 8.0

##### [Version 3.7.2](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.7.1...v3.7.2) (2021-08-04)

#### Features
- Add auto-populate dropdown for available meta fields for import wizzard

#### Fixes

- Fix broken icon issue in chosen dropdown
- Fix PHP notices on widget block area
- Add image dimensions support 
- Fix PHP8 fatal error when use multiple feed in visual editor

##### [Version 3.7.1](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.7.0...v3.7.1) (2021-07-07)

### Fixes 
- Particular RSS XML Feed showed as invalid if you select Post author to be shown in the backend
- Add default feed values if they are empty
- Fix source name when author unavailable in Gutenberg block
- Change 2nd-time validation message when author empty
- Fix broken style issue in feedzy list page
- Fix fewer columns issue in Gutenberg block
- Fix broken dropdown style issue in admin

#### [Version 3.7.0](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.6.4...v3.7.0) (2021-05-12)

### Features
- Add a new feed setting option to remove duplicates post
- Add WPML and Polylang support to import content.
- Add constant support to allow unsafe HTML as FEEDZY_ALLOW_UNSAFE_HTML
### Fixes
- Improve feed validation
- Fix thumbnails dimension issue in front-end

##### [Version 3.6.4](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.6.3...v3.6.4) (2021-04-28)

* Fix PHP notice in miscellaneous settings

##### [Version 3.6.3](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.6.2...v3.6.3) (2021-04-26)

* Fix save of custom field name and value on import

##### [Version 3.6.2](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.6.1...v3.6.2) (2021-04-23)

* Fix feed validation when DC is missing.
* Fix custom fields import broken markup.

##### [Version 3.6.1](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.6.0...v3.6.1) (2021-04-21)

* fix possible conflict with early use of wp_verify_nonce

#### [Version 3.6.0](https://github.com/Codeinwp/feedzy-rss-feeds/compare/v3.5.2...v3.6.0) (2021-04-20)

#### Fixes
* Fix PHP notices reported on import when debug mode is on
* Fix inconsistent behavior with certain valid feed URLs
* Improve compatibilities with the latest PHP and WordPress versions
* Improve compatibilities with non-Latin charsets

#### Feature 
* Add ability to use external images on import for featured images.

### v3.5.2 - 2020-12-24 
 **Changes:** 
 * [Fix] Compatibility with WP 5.6
* [Fix] Composer requiring PHP greater than 7.1.0
 
 ### v3.5.1 - 2020-10-30 
 **Changes:** 
 * [Fix] Importing random images with https://source.unsplash.com/random generator link in Feed to Post
* [Fix] Importing fixed featured image in Feed to Post
* [Fix] Enclosures that do no specify image extension are not imported even if the type is image/jpeg
 
 ### v3.5.0 - 2020-10-12 
 **Changes:** 
 * [Feat] Improved interface for adding new imports and for the imports listing page
* [Feat] Improved checks for feeds validity
 
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

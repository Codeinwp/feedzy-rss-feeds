# Feedzy RSS Feeds Lite
[![Build Status](https://api.travis-ci.org/Codeinwp/feedzy-rss-feeds.svg?branch=development)](https://travis-ci.org/Codeinwp/feedzy-rss-feeds)
[![Code Climate](https://codeclimate.com/github/Codeinwp/feedzy-rss-feeds/badges/gpa.svg)](https://codeclimate.com/github/Codeinwp/feedzy-rss-feeds)
[![Issue Count](https://codeclimate.com/github/Codeinwp/feedzy-rss-feeds/badges/issue_count.svg)](https://codeclimate.com/github/Codeinwp/feedzy-rss-feeds)

## Description
FEEDZY RSS Feeds is a small and lightweight RSS aggregator plugin. Fast and very easy to use, it allows you to aggregate multiple RSS feeds into your WordPress site through fully customizable shortcodes & widgets.

> **Time-saving features available in the FULL version:**
>
> * Multiple templates for feed items
> * Automatically build affiliate links
> * Parse price from product feeds
> * Priority email support from the developer of the plugin
> * Support and updates for 12 months
>
> **[Learn more about Feedzy Full version]( https://themeisle.com/plugins/feedzy-rss-feeds-lite/ )**

The plugin uses the SimplePie php CLASS natively included in WordPress. SimplePie is a RSS parser that can read the information contained in a feed, process it, and finally display it.

FEEDZY RSS Feeds therefore supports any additional library and uses only the bare minimum to ensure good performance (minimalistic CSS + cache). This will ensure compatibility with any hosting provider that supports WordPress, but if for any reason it doesn't work for yours feel free to <a href="http://themeisle.com/contact/" rel="nofollow">contact us</a>.

You may use this plugin in your widgets and your pages and reuse the shortcode + widget several times within the same page.

By activating this plugin, your cover picture will be inserted into your RSS feeds. By doing so, you'll make it will easier for external sites to retrieve images from your feeds.

**Please ask for help or report bugs if anything goes wrong. It is the best way to make the community benefit!**

## Shortcode Parameters

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

**Plugin is now using the TinyMCE API to improve UI and makes it easy to insert shortcodes!**


## Basic example
```
[feedzy-rss feeds="http://themeisle.com/feed"]
```


## Advanced example

```
[feedzy-rss feeds="http://themeisle.com/feed" max="2" feed_title="yes" target="_blank" title="50" meta="yes" summary="yes" summarylength="300" thumb="yes" size="100" default="http://your-site/default-image.jpg" keywords_title="WordPress"]
```


## Available Hooks

```
feedzy_feed_items
feedzy_item_keyword
feedzy_item_attributes
feedzy_thumb_output
feedzy_title_output
feedzy_meta_args
feedzy_meta_output
feedzy_summary_input
feedzy_summary_output
feedzy_global_output
feedzy_thumb_sizes
feedzy_feed_blacklist_images
feedzy_default_image
feedzy_default_error
feedzy_item_filter
feedzy_item_url_filter
shortcode_atts_feedzy_default
```

This plugin is maintained and supported by Themeisle, check out some of the other <a href="http://themeisle.com/wordpress-plugins/" rel="nofollow">WordPress plugins</a> and <a href="http://themeisle.com/wordpress-themes/free/" rel="nofollow">free themes</a> we've developed.

# Languages

* English
* French
* Serbian [Borisa Djuraskovic](http://www.webhostinghub.com/ "Borisa Djuraskovic")
* Japanese [sama55](http://askive.cmsbox.jp/ "sama55")
* German [ralfsteck](https://profiles.wordpress.org/ralfsteck/ "ralfsteck")
* Spanish [Angel Calzado](http://sintaxisweb.es "Angel Calzado")

Become a translator and send me your translation! [Contact-me](http://themeisle.com/contact "Contact")

## Installation

1. Upload and activate the plugin (or install it through the WP admin console)
2. Insert shortcode ! ;-)

### Frequently Asked Questions

#### Is it responsive friendly?

Yes it is.

#### My feed is not displayed and the following message appears "Sorry, this feed is currently unavailable or does not exists anymore."

You have to check first if your feed is valid. Please test it here: https://validator.w3.org/feed/


### Screenshots

1. Simple example
2. Inserting a shortcode in the WYSIWYG
3. Widget admin
4. Widget render
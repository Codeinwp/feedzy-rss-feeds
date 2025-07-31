<?php
/**
 * The admin abstract class tests.
 *
 * @since      5.0.9
 *
 * @package    feedzy-rss-feeds
 */
class Test_Abstract_Admin extends WP_UnitTestCase {
	
	/**
	 * Instance of the class being tested.
	 */
	private $feedzy_abstract;
	
	/**
	 * Set up test environment.
	 */
	public function setUp(): void {
		parent::setUp();
		
		// Create a concrete implementation of the abstract class for testing
		$this->feedzy_abstract = new class extends Feedzy_Rss_Feeds_Admin_Abstract {
			protected $plugin_name = 'feedzy-rss-feeds';
		};
		
		// Ensure SimplePie is loaded
		if (!class_exists('SimplePie')) {
			require_once(ABSPATH . WPINC . '/class-simplepie.php');
		}
	}

	/**
	 * Clean up after tests.
	 */
	public function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * Test feedzy_retrieve_image method with enclosure containing image.
	 */
	public function test_feedzy_retrieve_image_with_enclosure_image() {
		// Create a SimplePie feed with test data
		$feed = new SimplePie();
		$feed->set_raw_data('<?xml version="1.0" encoding="UTF-8"?>
			<rss version="2.0">
				<channel>
					<title>Test Feed</title>
					<item>
						<title>Test Item</title>
						<enclosure url="https://example.com/image.jpg" type="image/jpeg" />
					</item>
				</channel>
			</rss>');
		$feed->init();
		
		$items = $feed->get_items();
		$item = $items[0];
		
		$result = $this->feedzy_abstract->feedzy_retrieve_image($item);
		
		$this->assertEquals('https://example.com/image.jpg', $result);
	}

	/**
	 * Test feedzy_retrieve_image method with iTunes podcast image.
	 */
	public function test_feedzy_retrieve_image_with_itunes_image() {
		// Create a SimplePie feed with iTunes namespace
		$feed = new SimplePie();
		$feed->set_raw_data('<?xml version="1.0" encoding="UTF-8"?>
			<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">
				<channel>
					<title>Test Podcast</title>
					<item>
						<title>Test Episode</title>
						<itunes:image href="https://example.com/podcast.jpg" />
					</item>
				</channel>
			</rss>');
		$feed->init();
		
		$items = $feed->get_items();
		$item = $items[0];
		
		$result = $this->feedzy_abstract->feedzy_retrieve_image($item);
		
		$this->assertEquals('https://example.com/podcast.jpg', $result);
	}

	/**
     * Test feedzy_retrieve_image method with content image.
     */
    public function test_feedzy_retrieve_image_with_content_image() {
        // Create a SimplePie feed with image in content
        $feed = new SimplePie();
        $feed->set_raw_data('<?xml version="1.0" encoding="UTF-8"?>
            <rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
                <channel>
                    <title>Test Feed</title>
                    <item>
                        <title>Test Item</title>
                        <content:encoded><![CDATA[<p>Some content <img src="https://example.com/content.jpg" alt="test"></p>]]></content:encoded>
                    </item>
                </channel>
            </rss>');
        $feed->init();
        
        $items = $feed->get_items();
        
        // Check if we have items before accessing
        $this->assertNotEmpty($items, 'No items found in feed');
        
        $item = $items[0];
        
        $result = $this->feedzy_abstract->feedzy_retrieve_image($item);
        
        $this->assertEquals('https://example.com/content.jpg', $result);
    }

	/**
	 * Test feedzy_retrieve_image method with description image.
	 */
	public function test_feedzy_retrieve_image_with_description_image() {
		// Create a SimplePie feed with image in description
		$feed = new SimplePie();
		$feed->set_raw_data('<?xml version="1.0" encoding="UTF-8"?>
			<rss version="2.0">
				<channel>
					<title>Test Feed</title>
					<item>
						<title>Test Item</title>
						<description><![CDATA[<p>Description with <img src="https://example.com/desc.jpg" alt="description"></p>]]></description>
					</item>
				</channel>
			</rss>');
		$feed->init();
		
		$items = $feed->get_items();

        // Check if we have items before accessing
        $this->assertNotEmpty($items, 'No items found in feed');

		$item = $items[0];
		
		$result = $this->feedzy_abstract->feedzy_retrieve_image($item);
		
		$this->assertEquals('https://example.com/desc.jpg', $result);
	}

	/**
	 * Test feedzy_retrieve_image method with HTTP to HTTPS conversion.
	 */
	public function test_feedzy_retrieve_image_http_to_https_conversion() {
		// Create a SimplePie feed with HTTP image
		$feed = new SimplePie();
		$feed->set_raw_data('<?xml version="1.0" encoding="UTF-8"?>
			<rss version="2.0">
				<channel>
					<title>Test Feed</title>
					<item>
						<title>Test Item</title>
						<enclosure url="http://example.com/image.jpg" type="image/jpeg" />
					</item>
				</channel>
			</rss>');
		$feed->init();
		
		$items = $feed->get_items();

         // Check if we have items before accessing
        $this->assertNotEmpty($items, 'No items found in feed');

		$item = $items[0];
		
		$sc = array(
			'http' => 'https',
			'feeds' => 'https://example.com/feed'
		);
		
		$result = $this->feedzy_abstract->feedzy_retrieve_image($item, $sc);
		
		$this->assertEquals('https://example.com/image.jpg', $result);
	}

	/**
	 * Test feedzy_retrieve_image method with default image fallback.
	 */
	public function test_feedzy_retrieve_image_with_default_fallback() {
		// Create a SimplePie feed with HTTP image and default setting
		$feed = new SimplePie();
		$feed->set_raw_data('<?xml version="1.0" encoding="UTF-8"?>
			<rss version="2.0">
				<channel>
					<title>Test Feed</title>
					<item>
						<title>Test Item</title>
						<enclosure url="http://example.com/image.jpg" type="image/jpeg" />
					</item>
				</channel>
			</rss>');
		$feed->init();
		
		$items = $feed->get_items();

        // Check if we have items before accessing
        $this->assertNotEmpty($items, 'No items found in feed');

		$item = $items[0];
		
		$sc = array(
			'http' => 'default',
			'default' => 'https://example.com/default.jpg',
			'feeds' => 'https://example.com/feed'
		);
		
		$result = $this->feedzy_abstract->feedzy_retrieve_image($item, $sc);
		
		$this->assertEquals('https://example.com/default.jpg', $result);
	}

    /**
     * Test feedzy_retrieve_image method with no image available.
     */
    public function test_feedzy_retrieve_image_with_no_image() {
        // Create a SimplePie feed without any images
        $feed = new SimplePie();
        $feed->set_raw_data('<?xml version="1.0" encoding="UTF-8"?>
            <rss version="2.0">
                <channel>
                    <title>Test Feed</title>
                    <item>
                        <title>Test Item</title>
                        <description><![CDATA[<p>This is just text content without any images.</p>]]></description>
                        <link>https://example.com/post</link>
                    </item>
                </channel>
            </rss>');
        $feed->init();
        
        $items = $feed->get_items();
        $this->assertNotEmpty($items, 'No items found in feed');
        
        $item = $items[0];
        
        // Test without shortcode attributes (no default image)
        $result = $this->feedzy_abstract->feedzy_retrieve_image($item);
        
        // Should return empty string when no image is found and no default is set
        $this->assertEquals('', $result);
    }

    /**
     * Test feedzy_retrieve_image method with no image but with default fallback.
     */
    public function test_feedzy_retrieve_image_with_no_image_uses_default() {
        // Create a SimplePie feed without any images
        $feed = new SimplePie();
        $feed->set_raw_data('<?xml version="1.0" encoding="UTF-8"?>
            <rss version="2.0">
                <channel>
                    <title>Test Feed</title>
                    <item>
                        <title>Test Item</title>
                        <description><![CDATA[<p>This is just text content without any images.</p>]]></description>
                        <link>https://example.com/post</link>
                    </item>
                </channel>
            </rss>');
        $feed->init();
        
        $items = $feed->get_items();
        $this->assertNotEmpty($items, 'No items found in feed');
        
        $item = $items[0];
        
        // Test with shortcode attributes including a default image
        $sc = array(
            'default' => 'https://example.com/default-image.jpg',
            'feeds' => 'https://example.com/feed'
        );
        
        // Mock the normalize_urls method to return the feed URL
        $feedzy_abstract = new class extends Feedzy_Rss_Feeds_Admin_Abstract {
            protected $plugin_name = 'feedzy-rss-feeds';
            
            public function normalize_urls($feeds) {
                return 'https://example.com/feed';
            }
        };
        
        // Apply filter to set default image
        add_filter('feedzy_default_image', function($default, $feed_url) use ($sc) {
            return $sc['default'];
        }, 10, 2);
        
        $result = $feedzy_abstract->feedzy_retrieve_image($item, $sc);
        
        // Should return the default image when no image is found in the feed
        $this->assertEquals('https://example.com/default-image.jpg', $result);
        
        // Clean up the filter
        remove_all_filters('feedzy_default_image');
    }

    /**
     * Test feedzy_retrieve_image method with complex content but no valid images.
     */
    public function test_feedzy_retrieve_image_with_blacklisted_images_only() {
        // Create a SimplePie feed with only blacklisted images
        $feed = new SimplePie();
        $feed->set_raw_data('<?xml version="1.0" encoding="UTF-8"?>
            <rss version="2.0">
                <channel>
                    <title>Test Feed</title>
                    <item>
                        <title>Test Item</title>
                        <description><![CDATA[
                            <p>This content has emoticons 
                            <img src="https://example.com/icon_smile.gif" alt=":)">
                            <img src="https://example.com/icon_wink.gif" alt=";)">
                            <img src="https://s.w.org/images/core/emoji/smile.png" alt=":)">
                            but no real images.</p>
                        ]]></description>
                    </item>
                </channel>
            </rss>');
        $feed->init();
        
        $items = $feed->get_items();
        $this->assertNotEmpty($items, 'No items found in feed');
        
        $item = $items[0];
        
        $result = $this->feedzy_abstract->feedzy_retrieve_image($item);
        
        // Should return empty string as all images are blacklisted
        $this->assertEquals('', $result);
    }

	/**
	 * Test feedzy_return_image method with valid image.
	 */
	public function test_feedzy_return_image_with_valid_image() {
		$html = '<p>Content with <img src="https://example.com/valid.jpg" alt="Valid"> image</p>';
		
		$result = $this->feedzy_abstract->feedzy_return_image($html);
		
		$this->assertEquals('https://example.com/valid.jpg', $result);
	}

	/**
	 * Test feedzy_return_image method with blacklisted image.
	 */
	public function test_feedzy_return_image_with_blacklisted_image() {
		$html = '<p>Content with <img src="https://example.com/icon_smile.gif" alt="Smile"></p>';
		
		$result = $this->feedzy_abstract->feedzy_return_image($html);
		
		$this->assertNull($result);
	}

	/**
	 * Test feedzy_return_image method with multiple images.
	 */
	public function test_feedzy_return_image_with_multiple_images() {
		$html = '<p><img src="https://example.com/icon_smile.gif"><img src="https://example.com/valid.jpg"></p>';
		
		$result = $this->feedzy_abstract->feedzy_return_image($html);
		
		$this->assertEquals('https://example.com/valid.jpg', $result);
	}

	/**
	 * Test feedzy_return_image method with no images.
	 */
	public function test_feedzy_return_image_with_no_images() {
		$html = '<p>Content without any images</p>';
		
		$result = $this->feedzy_abstract->feedzy_return_image($html);
		
		$this->assertNull($result);
	}

	/**
	 * Test feedzy_scrape_image method with standard img tag.
	 */
	public function test_feedzy_scrape_image_standard_img_tag() {
		$img_html = '<img src="https://example.com/image.jpg" alt="Test Image">';
		
		$result = $this->feedzy_abstract->feedzy_scrape_image($img_html);
		
		$this->assertEquals('https://example.com/image.jpg', $result);
	}

	/**
	 * Test feedzy_scrape_image method with single quotes.
	 */
	public function test_feedzy_scrape_image_single_quotes() {
		$img_html = "<img src='https://example.com/image.jpg' alt='Test Image'>";
		
		$result = $this->feedzy_abstract->feedzy_scrape_image($img_html);
		
		$this->assertEquals('https://example.com/image.jpg', $result);
	}

	/**
	 * Test feedzy_scrape_image method with no quotes.
	 */
	public function test_feedzy_scrape_image_no_quotes() {
		$img_html = '<img src=https://example.com/image.jpg>';
		
		$result = $this->feedzy_abstract->feedzy_scrape_image($img_html);
		
		$this->assertEquals('https://example.com/image.jpg', $result);
	}

	/**
	 * Test feedzy_scrape_image method with spaces.
	 */
	public function test_feedzy_scrape_image_with_spaces() {
		$img_html = '<  img   src = "https://example.com/image.jpg"  >';
		
		$result = $this->feedzy_abstract->feedzy_scrape_image($img_html);
		
		$this->assertEquals('https://example.com/image.jpg', $result);
	}

	/**
	 * Test feedzy_scrape_image method with invalid HTML.
	 */
	public function test_feedzy_scrape_image_invalid_html() {
		$img_html = '<div>Not an image</div>';
		$default_link = 'https://default.com/image.jpg';
		
		$result = $this->feedzy_abstract->feedzy_scrape_image($img_html, $default_link);
		
		$this->assertEquals($default_link, $result);
	}

	/**
	 * Test is_image_url method with valid image URLs.
	 */
	public function test_is_image_url_valid_urls() {
		$valid_urls = array(
			'https://example.com/image.jpg',
			'https://example.com/image.jpeg',
			'https://example.com/image.png',
			'https://example.com/image.gif',
			'https://example.com/image.webp',
			'https://example.com/image.svg',
			'https://example.com/image.JPG',
			'https://example.com/path/to/image.PNG',
		);
		
		foreach ($valid_urls as $url) {
			$this->assertTrue($this->feedzy_abstract->is_image_url($url), "Failed for URL: $url");
		}
	}

	/**
	 * Test is_image_url method with invalid URLs.
	 */
	public function test_is_image_url_invalid_urls() {
		$invalid_urls = array(
			'https://example.com/document.pdf',
			'https://example.com/video.mp4',
			'https://example.com/no-extension',
			'https://example.com/',
			'not-a-url',
			'',
			null,
			array('not', 'a', 'string'),
		);
		
		foreach ($invalid_urls as $url) {
			$this->assertFalse($this->feedzy_abstract->is_image_url($url), "Failed for URL: " . print_r($url, true));
		}
	}

	/**
	 * Test extract_image_from_enclosure for direct testing.
	 */
	public function test_extract_image_from_enclosure_with_media_thumbnail() {
		// Create a SimplePie feed with media:thumbnail
		$feed = new SimplePie();
		$feed->set_raw_data('<?xml version="1.0" encoding="UTF-8"?>
			<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">
				<channel>
					<title>Test Feed</title>
					<item>
						<title>Test Item</title>
						<media:thumbnail url="https://example.com/thumb.jpg" />
					</item>
				</channel>
			</rss>');
		$feed->init();
		
		$items = $feed->get_items();
		$item = $items[0];
		$enclosures = $item->get_enclosures();
		
		if (!empty($enclosures)) {
			$result = $this->feedzy_abstract->extract_image_from_enclosure($enclosures[0]);
			$this->assertEquals('https://example.com/thumb.jpg', $result);
		}
	}
}
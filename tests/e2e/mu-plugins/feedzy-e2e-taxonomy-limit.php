<?php
/**
 * Plugin Name: Feedzy E2E Taxonomy Limit
 * Description: Lists one term per taxonomy in Post Taxonomy on requests that post feedzy_e2e_taxonomy_limit, so e2e tests can cover saved terms past the limit.
 *
 * @package feedzy-rss-feeds
 */

add_filter(
	'feedzy_post_taxonomy_limit',
	function ( $limit ) {
		return isset( $_POST['feedzy_e2e_taxonomy_limit'] ) ? 1 : $limit;
	}
);

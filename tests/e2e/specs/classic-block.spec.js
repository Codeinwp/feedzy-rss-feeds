/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe('Feedzy Classic Block', () => {
	test('check validation for invalid URL', async ({ editor, page, admin }) => {
		await admin.createNewPost();

        await editor.insertBlock({
			name: 'feedzy-rss-feeds/feedzy-block',
		});

        await page.getByPlaceholder('Enter URL or group of your').fill(
			'http://invalid-url.com/feed'
		);

        await page.getByRole('button', { name: 'Load Feed' }).click();

        await page.waitForSelector('.feedzy-validation-results');
        
		await expect( page.locator('.feedzy-validation-results .is-error').getByText('http://invalid-url.com/feed', { exact: true }) ).toBeVisible();
	});

    test('check validation for invalid and valid URL', async ({ editor, page, admin }) => {
		await admin.createNewPost();

        await editor.insertBlock({
			name: 'feedzy-rss-feeds/feedzy-block',
		});

        await page.getByPlaceholder('Enter URL or group of your').fill(
			'http://invalid-url.com/feed, https://www.nasa.gov/feeds/iotd-feed/'
		);

        await page.getByRole('button', { name: 'Load Feed' }).click();

        await page.waitForSelector('.feedzy-validation-results');
        
		await expect( page.locator('.feedzy-validation-results .is-error').getByText('http://invalid-url.com/feed', { exact: true }) ).toBeVisible();

        await expect( page.locator('.feedzy-validation-results .is-success').getByText('https://www.nasa.gov/feeds/iotd-feed/', { exact: true }) ).toBeVisible();
	});

});
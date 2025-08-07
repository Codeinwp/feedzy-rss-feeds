/* eslint-disable @wordpress/no-unsafe-wp-apis */
/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import {
	BaseControl,
	Button,
	Placeholder,
	Spinner,
	Notice,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

/**
 * Internal dependencies.
 */
import FeedControl from './components/FeedControl';

const BlockPlaceholder = ({ attributes, setAttributes, onSaveFeed }) => {
	const [isValidating, setIsValidating] = useState(false);
	const [validationResults, setValidationResults] = useState([]);

	const { categories, isLoading } = useSelect((select) => {
		const { getEntityRecords, isResolving } = select(coreStore);

		return {
			categories: getEntityRecords('postType', 'feedzy_categories') ?? [],
			isLoading: isResolving('getEntityRecords', [
				'postType',
				'feedzy_categories',
			]),
		};
	}, []);

	const handleLoadFeed = async () => {
		if (!attributes?.feed?.source) {
			return;
		}

		setIsValidating(true);
		setValidationResults([]);

		const isCategory = categories.some(
			(cat) => cat.id === attributes.feed.source
		);

		if (isCategory && 'group' === attributes.feed.type) {
			onSaveFeed();
			setIsValidating(false);
			return;
		}

		try {
			const formData = new FormData();
			formData.append('action', 'feedzy_validate_feed');
			formData.append('feed_url', attributes.feed.source);
			formData.append('nonce', window.feedzyData?.nonce);

			const response = await fetch(window.feedzyData?.url, {
				method: 'POST',
				body: formData,
			});

			const data = await response.json();

			if (data.success && data.data?.results) {
				const results = data.data.results;
				setValidationResults(results);

				const hasErrors = results.some(
					(result) => result.status === 'error'
				);

				if (!hasErrors) {
					onSaveFeed();
				}
			} else if (!data.success) {
				setValidationResults([
					{
						status: 'error',
						message:
							data.data?.message ||
							__('Validation failed', 'feedzy-rss-feeds'),
					},
				]);
			}
		} catch (error) {
			setValidationResults([
				{
					status: 'error',
					message: __(
						'Failed to validate feed. Please check your connection and try again.',
						'feedzy-rss-feeds'
					),
				},
			]);
		} finally {
			setIsValidating(false);
		}
	};

	const handleFeedChange = (value) => {
		setAttributes({ feed: value });
	};

	const renderValidationResults = () => {
		if (!validationResults || validationResults.length === 0) {
			return null;
		}

		return (
			<div
				className="feedzy-validation-results"
				style={{
					display: 'flex',
					flexDirection: 'column',
					gap: '10px',
					marginTop: '15px',
				}}
			>
				{validationResults.map((result, index) => (
					<Notice
						key={`result-${index}`}
						status={result.status}
						isDismissible={false}
					>
						{result.url && (
							<>
								<strong>{result.url}</strong>
								<br />
							</>
						)}
						{result.message}
					</Notice>
				))}
			</div>
		);
	};

	return (
		<Placeholder
			key="placeholder"
			icon="rss"
			label={__('Feedzy RSS Feeds', 'feedzy-rss-feeds')}
		>
			{(isLoading || isValidating) && (
				<div key="loading" className="wp-block-embed is-loading">
					<Spinner />
					<p>
						{isValidating
							? __(
									'Validating and fetching feed…',
									'feedzy-rss-feeds'
								)
							: __('Loading…', 'feedzy-rss-feeds')}
					</p>
				</div>
			)}

			{!isLoading && !isValidating && (
				<>
					<BaseControl
						label={__('Feed Source', 'feedzy-rss-feeds')}
						id="feed-source-control"
					>
						<FeedControl
							value={attributes?.feed}
							options={[
								...categories.map((category) => ({
									label: category?.title?.rendered,
									value: category.id,
								})),
							]}
							onChange={handleFeedChange}
						/>

						{renderValidationResults()}

						<p>
							{__(
								'Enter the full URL of the feed source you wish to display here, or select a Feed Group. Also you can add multiple URLs separated with a comma. You can manage your feed groups from',
								'feedzy-rss-feeds'
							)}{' '}
							<a
								href="edit.php?post_type=feedzy_categories"
								title={__('Feedzy Groups', 'feedzy-rss-feeds')}
								target="_blank"
							>
								{__('here', 'feedzy-rss-feeds')}
							</a>
						</p>
					</BaseControl>

					<div>
						<Button variant="primary" onClick={() => handleLoadFeed()}>
							{__('Load Feed', 'feedzy-rss-feeds')}
						</Button>
					</div>
				</>
			)}
		</Placeholder>
	);
};

export default BlockPlaceholder;

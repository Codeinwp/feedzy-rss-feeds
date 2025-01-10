/* eslint-disable @wordpress/no-unsafe-wp-apis */
/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import {
	BaseControl,
	Button,
	Placeholder,
	Spinner,
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';

import { store as coreStore } from '@wordpress/core-data';

/**
 * Internal dependencies.
 */
import FeedControl from './components/FeedControl';

const BlockPlaceholder = ({ attributes, setAttributes, onSaveFeed }) => {
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

	return (
		<Placeholder
			key="placeholder"
			icon="rss"
			label={__('Feedzy RSS Feeds', 'feedzy-rss-feeds')}
		>
			{isLoading && (
				<div key="loading" className="wp-block-embed is-loading">
					<Spinner />
					<p>{__('Fetching…', 'feedzy-rss-feeds')}</p>
				</div>
			)}

			{!isLoading && (
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
							onChange={(value) => setAttributes({ feed: value })}
						/>

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
						<Button
							variant="primary"
							onClick={() => {
								if (attributes?.feed?.source) {
									onSaveFeed();
								}
							}}
						>
							{__('Load Feed', 'feedzy-rss-feeds')}
						</Button>

						<Button
							variant="link"
							href="https://validator.w3.org/feed/"
							target="_blank"
						>
							{__('Validate', 'feedzy-rss-feeds')}
						</Button>
					</div>
				</>
			)}
		</Placeholder>
	);
};

export default BlockPlaceholder;

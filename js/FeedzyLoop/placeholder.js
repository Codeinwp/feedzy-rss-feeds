/* eslint-disable @wordpress/no-unsafe-wp-apis */
/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import {
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	Button,
	Placeholder,
	SelectControl,
	Spinner,
	TextControl,
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';

import { store as coreStore } from '@wordpress/core-data';

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

	const onChangeFeed = ({ type, value }) => {
		setAttributes({
			feed: {
				...attributes.feed,
				[type]: value,
			},
		});
	};

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
					<ToggleGroupControl
						isBlock
						label={__('Feed Source', 'feedzy-rss-feeds')}
						value={attributes?.feed?.type}
						onChange={(value) =>
							onChangeFeed({ type: 'type', value })
						}
					>
						<ToggleGroupControlOption
							label={__('Feed URL', 'feedzy-rss-feeds')}
							value="url"
						/>
						<ToggleGroupControlOption
							label={__('Feed Group', 'feedzy-rss-feeds')}
							value="group"
						/>
					</ToggleGroupControl>

					{'url' === attributes?.feed?.type && (
						<TextControl
							label={__('Feed URLs', 'feedzy-rss-feeds')}
							placeholder={__(
								'Enter feed URLs separated by commas.',
								'feedzy-rss-feeds'
							)}
							value={
								Array.isArray(attributes?.feed?.source)
									? attributes.feed.source.join(', ')
									: ''
							}
							onChange={(value) =>
								onChangeFeed({
									type: 'source',
									value: value
										.split(',')
										.map((item) => item.trim()),
								})
							}
						/>
					)}

					{'group' === attributes?.feed?.type && (
						<SelectControl
							label={__('Feed Group', 'feedzy-rss-feeds')}
							options={[
								{
									label: __(
										'Select a group',
										'feedzy-rss-feeds'
									),
									value: '',
									disabled: true,
								},
								...categories.map((category) => ({
									label: category?.title?.rendered,
									value: category.id,
								})),
							]}
							value={attributes?.feed?.source ?? ''}
							onChange={(value) =>
								onChangeFeed({
									type: 'source',
									value: parseInt(value, 10),
								})
							}
						/>
					)}

					<Button variant="primary" onClick={onSaveFeed}>
						{__('Save', 'feedzy-rss-feeds')}
					</Button>
				</>
			)}
		</Placeholder>
	);
};

export default BlockPlaceholder;

/* eslint-disable @wordpress/no-unsafe-wp-apis */
/**
 * WordPress dependencies.
 */
import {
	store as blocksStore,
	createBlocksFromInnerBlocksTemplate,
	serialize,
} from '@wordpress/blocks';

import {
	store as blockEditorStore,
	__experimentalBlockVariationPicker as BlockVariationPicker,
	__experimentalUseBlockPreview as useBlockPreview,
	useBlockProps,
	InnerBlocks,
	BlockContextProvider,
	useInnerBlocksProps,
} from '@wordpress/block-editor';

import {
	Placeholder as BlockEditorPlaceholder,
	Spinner,
} from '@wordpress/components';

import { useDispatch, useSelect } from '@wordpress/data';

import { useState, useMemo, memo, useEffect } from '@wordpress/element';

import apiFetch from '@wordpress/api-fetch';

import { addQueryArgs, buildQueryString } from '@wordpress/url';

import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies.
 */
import metadata from './block.json';
import Placeholder from './placeholder';
import Controls from './controls';

const { name } = metadata;

const LoadingResponsePlaceholder = () => (
	<BlockEditorPlaceholder>
		<Spinner />
	</BlockEditorPlaceholder>
);

// Component for rendering actual editable inner blocks for active feed item
function FeedItemInnerBlocks({ feedItem, classList }) {
	const innerBlocksProps = useInnerBlocksProps(
		{
			className: `wp-block-feedzy-feed-item ${classList}`,
			'data-feed-item-id': feedItem.id,
		},
		{
			templateLock: false,
			__unstableDisableLayoutClassNames: true,
		}
	);
	return <div {...innerBlocksProps} />;
}

// Component for rendering interactive preview of feed items
function FeedItemBlockPreview({
	blocks,
	feedItem,
	classList,
	isHidden,
	setActiveFeedItemId,
}) {
	const blockPreviewProps = useBlockPreview({
		blocks,
		props: {
			className: `wp-block-feedzy-feed-item ${classList}`,
			'data-feed-item-id': feedItem.id,
		},
	});

	const handleOnClick = () => {
		setActiveFeedItemId(feedItem.id);
	};

	const handleOnKeyPress = (event) => {
		if (event.key === 'Enter' || event.key === ' ') {
			event.preventDefault();
			setActiveFeedItemId(feedItem.id);
		}
	};

	const style = {
		display: isHidden ? 'none' : undefined,
		cursor: 'pointer',
		outline: 'none',
	};

	return (
		<div
			{...blockPreviewProps}
			tabIndex={0}
			role="button"
			onClick={handleOnClick}
			onKeyPress={handleOnKeyPress}
			style={style}
			aria-label={`Edit feed item: ${feedItem.title}`}
		/>
	);
}

const MemoizedFeedItemBlockPreview = memo(FeedItemBlockPreview);

// Helper function to check if value is in array
function inArray(value, array) {
	return Array.isArray(array) && array.includes(value);
}

// Hook to fetch and parse RSS feed data using the real API
function useFeedData(feedSource, feedType, attributes = {}) {
	const [feedData, setFeedData] = useState(null);
	const [isLoading, setIsLoading] = useState(false);
	const [error, setError] = useState(null);

	useEffect(() => {
		if (!feedSource) {
			setFeedData(null);
			setIsLoading(false);
			setError(null);
			return;
		}

		const loadFeed = async () => {
			setIsLoading(true);
			setError(null);

			try {
				// Make API call to WordPress REST endpoint
				const response = await apiFetch({
					path: `/feedzy/v1/loop/feed?${buildQueryString(attributes)}`,
					method: 'GET',
				});

				console.log(response);

				if (response.error) {
					throw new Error(response.error);
				}

				// Set the transformed feed data
				setFeedData(response);
				setIsLoading(false);
			} catch (err) {
				console.error('Feed loading error:', err);
				setError(err.message || 'Failed to load feed');
				setIsLoading(false);
				setFeedData(null);
			}
		};

		loadFeed();
	}, [feedSource, feedType, JSON.stringify(attributes)]);

	return { feedData, isLoading, error };
}

const Edit = ({ attributes, setAttributes, clientId }) => {
	const blockProps = useBlockProps();

	const [isEditing, setIsEditing] = useState(!attributes?.feed?.source);
	const [isPreviewing, setIsPreviewing] = useState(false);
	const [activeFeedItemId, setActiveFeedItemId] = useState();

	const { clearSelectedBlock, replaceInnerBlocks } =
		useDispatch(blockEditorStore);

	const isSelected = useSelect(
		(select) => {
			const { isBlockSelected, hasSelectedInnerBlock } =
				select(blockEditorStore);
			return (
				isBlockSelected(clientId) ||
				hasSelectedInnerBlock(clientId, true)
			);
		},
		[clientId]
	);

	const innerBlocksContent = useSelect(
		(select) => {
			const { getBlock } = select(blockEditorStore);
			const block = getBlock(clientId);
			return serialize(block?.innerBlocks) ?? '';
		},
		[clientId]
	);

	const blocks = useSelect(
		(select) => {
			const { getBlocks } = select(blockEditorStore);
			return getBlocks(clientId);
		},
		[clientId]
	);

	const hasInnerBlocks = useSelect(
		(select) => 0 < select(blockEditorStore).getBlocks(clientId).length,
		[clientId]
	);

	const variations = useSelect((select) => {
		const { getBlockVariations } = select(blocksStore);
		return getBlockVariations(name, 'block');
	}, []);

	const defaultVariation = useSelect((select) => {
		const { getDefaultBlockVariation } = select(blocksStore);
		return getDefaultBlockVariation(name, 'block');
	}, []);

	// Fetch RSS feed data using real API
	const {
		feedData,
		isLoading: isFeedLoading,
		error: feedError,
	} = useFeedData(attributes?.feed?.source, attributes?.feed?.type, {
		...attributes,
		query: attributes?.query || {},
		layout: attributes?.layout || {},
		conditions: attributes?.conditions || {},
	});

	// Create block contexts for each feed item
	const feedItemContexts = useMemo(() => feedData, [feedData]);

	const onSaveFeed = () => {
		setIsEditing(false);
	};

	const onChangeQuery = ({ type, value }) => {
		setAttributes({
			query: {
				...attributes.query,
				[type]: value,
			},
		});
	};

	const onChangeLayout = ({ type, value }) => {
		setAttributes({
			layout: {
				...attributes.layout,
				[type]: value,
			},
		});
	};

	// Editing state - show feed configuration
	if (isEditing) {
		return (
			<div {...blockProps}>
				<Placeholder
					attributes={attributes}
					setAttributes={setAttributes}
					onSaveFeed={onSaveFeed}
				/>
			</div>
		);
	}

	// Server-side preview state
	if (isPreviewing && innerBlocksContent) {
		return (
			<>
				<Controls
					attributes={attributes}
					isEditing={isEditing}
					isPreviewing={isPreviewing}
					setAttributes={setAttributes}
					onChangeLayout={onChangeLayout}
					onChangeQuery={onChangeQuery}
					setIsEditing={setIsEditing}
					setIsPreviewing={setIsPreviewing}
				/>

				<div {...blockProps}>
					<ServerSideRender
						block="feedzy-rss-feeds/loop"
						attributes={{
							...attributes,
							innerBlocksContent,
						}}
						LoadingResponsePlaceholder={LoadingResponsePlaceholder}
					/>
				</div>
			</>
		);
	}

	// Interactive preview state - show feed items with interactive editing
	if (hasInnerBlocks && feedItemContexts && !isEditing && !isPreviewing) {
		if (isFeedLoading) {
			return (
				<>
					<Controls
						attributes={attributes}
						isEditing={isEditing}
						isPreviewing={isPreviewing}
						setAttributes={setAttributes}
						onChangeLayout={onChangeLayout}
						onChangeQuery={onChangeQuery}
						setIsEditing={setIsEditing}
						setIsPreviewing={setIsPreviewing}
					/>
					<div {...blockProps}>
						<LoadingResponsePlaceholder />
					</div>
				</>
			);
		}

		if (feedError) {
			return (
				<>
					<Controls
						attributes={attributes}
						isEditing={isEditing}
						isPreviewing={isPreviewing}
						setAttributes={setAttributes}
						onChangeLayout={onChangeLayout}
						onChangeQuery={onChangeQuery}
						setIsEditing={setIsEditing}
						setIsPreviewing={setIsPreviewing}
					/>
					<div {...blockProps}>
						<div className="feedzy-error">
							<p>
								<strong>Error loading feed:</strong> {feedError}
							</p>
							<button
								onClick={() => setIsEditing(true)}
								className="button button-primary"
							>
								Edit Feed Settings
							</button>
						</div>
					</div>
				</>
			);
		}

		// Interactive preview with real feed data
		return (
			<>
				<Controls
					attributes={attributes}
					isEditing={isEditing}
					isPreviewing={isPreviewing}
					setAttributes={setAttributes}
					onChangeLayout={onChangeLayout}
					onChangeQuery={onChangeQuery}
					setIsEditing={setIsEditing}
					setIsPreviewing={setIsPreviewing}
				/>

				<div {...blockProps}>
					<div className="wp-block-feedzy-feed-items">
						{feedItemContexts.map((feedItemContext) => (
							<BlockContextProvider
								key={feedItemContext.item_id}
								value={{
									'feedzy-rss-feeds/feedItem':
										feedItemContext,
								}}
							>
								{/* Render editable inner blocks for active feed item */}
								{feedItemContext.item_id ===
								(activeFeedItemId ||
									feedItemContexts[0]?.item_id) ? (
									<FeedItemInnerBlocks
										feedItem={{
											id: feedItemContext.item_id,
											title: feedItemContext.item_title,
										}}
										classList={feedItemContext.classList}
									/>
								) : null}

								{/* Render interactive preview for inactive feed items */}
								<MemoizedFeedItemBlockPreview
									blocks={blocks}
									feedItem={{
										id: feedItemContext.item_id,
										title: feedItemContext.item_title,
									}}
									classList={feedItemContext.classList}
									setActiveFeedItemId={setActiveFeedItemId}
									isHidden={
										feedItemContext.item_id ===
										(activeFeedItemId ||
											feedItemContexts[0]?.item_id)
									}
								/>
							</BlockContextProvider>
						))}
					</div>
				</div>
			</>
		);
	}

	// Default state - show block variation picker or inner blocks
	return (
		<>
			<Controls
				attributes={attributes}
				isEditing={isEditing}
				isPreviewing={isPreviewing}
				setAttributes={setAttributes}
				onChangeLayout={onChangeLayout}
				onChangeQuery={onChangeQuery}
				setIsEditing={setIsEditing}
				setIsPreviewing={setIsPreviewing}
			/>

			<div {...blockProps}>
				{hasInnerBlocks ? (
					<InnerBlocks />
				) : (
					<BlockVariationPicker
						variations={variations}
						onSelect={(nextVariation = defaultVariation) => {
							if (nextVariation) {
								setAttributes(nextVariation.attributes);
								replaceInnerBlocks(
									clientId,
									createBlocksFromInnerBlocksTemplate(
										nextVariation.innerBlocks
									),
									true
								);
								clearSelectedBlock();
							}
						}}
					/>
				)}
			</div>
		</>
	);
};

export default Edit;

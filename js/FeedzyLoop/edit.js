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

import { useState, useMemo, memo } from '@wordpress/element';

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

// Hook to fetch and parse RSS feed data
function useFeedData(feedUrl) {
	const [feedData, setFeedData] = useState(null);
	const [isLoading, setIsLoading] = useState(false);
	const [error, setError] = useState(null);

	// In a real implementation, you'd use useSelect with REST API or apiFetch
	// This is a simplified example
	useMemo(() => {
		if (!feedUrl) {
			return;
		}

		setIsLoading(true);
		setError(null);

		// Simulate API call - replace with actual RSS parsing
		const mockFeedItems = [
			{
				id: 1,
				title: 'Sample Feed Item 1',
				excerpt: 'This is the first feed item excerpt...',
				url: 'https://example.com/post-1',
				date: '2025-01-15',
				author: 'John Doe',
				image: 'https://example.com/image1.jpg',
			},
			{
				id: 2,
				title: 'Sample Feed Item 2',
				excerpt: 'This is the second feed item excerpt...',
				url: 'https://example.com/post-2',
				date: '2025-01-14',
				author: 'Jane Smith',
				image: 'https://example.com/image2.jpg',
			},
			{
				id: 3,
				title: 'Sample Feed Item 3',
				excerpt: 'This is the third feed item excerpt...',
				url: 'https://example.com/post-3',
				date: '2025-01-13',
				author: 'Bob Johnson',
				image: 'https://example.com/image3.jpg',
			},
		];

		// Simulate async operation
		setTimeout(() => {
			setFeedData(mockFeedItems);
			setIsLoading(false);
		}, 1000);
	}, [feedUrl]);

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

	// Fetch RSS feed data for interactive preview
	const {
		feedData,
		isLoading: isFeedLoading,
		error: feedError,
	} = useFeedData(attributes?.feed?.source);

	// Create block contexts for each feed item
	const feedItemContexts = useMemo(
		() =>
			feedData?.map((feedItem) => ({
				item_id: feedItem.id,
				item_title: feedItem.title,
				item_excerpt: feedItem.excerpt,
				item_url: feedItem.url,
				item_date: feedItem.date,
				item_author: feedItem.author,
				item_image: feedItem.image,
				classList: `feed-item-${feedItem.id}`,
			})),
		[feedData]
	);

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
						<p>Error loading feed: {feedError}</p>
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
								key={feedItemContext.feedItemId}
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

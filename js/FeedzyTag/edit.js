/* eslint-disable @wordpress/no-unsafe-wp-apis */
/**
 * WordPress dependencies.
 */
import {
	useBlockProps,
	InspectorControls,
	BlockControls,
} from '@wordpress/block-editor';

import {
	PanelBody,
	TextControl,
	ToolbarButton,
	Button,
	SelectControl,
} from '@wordpress/components';

import { __ } from '@wordpress/i18n';

import { edit as editIcon, check, close } from '@wordpress/icons';

import { useState } from '@wordpress/element';

// Tag mapping for RSS feed data - maps tag names to feedItem properties
const FEED_TAGS = {
	item_title: {
		label: __('Post Title', 'feedzy-rss-feeds'),
		property: 'title',
		fallback: 'Sample Post Title',
	},
	item_content: {
		label: __('Post Content', 'feedzy-rss-feeds'),
		property: 'content',
		fallback:
			'This is a sample post excerpt that would normally come from the RSS feed...',
	},
	item_excerpt: {
		label: __('Post Excerpt', 'feedzy-rss-feeds'),
		property: 'excerpt',
		fallback: 'This is a sample post excerpt...',
	},
	item_date_formatted: {
		label: __('Post Date', 'feedzy-rss-feeds'),
		property: 'date',
		fallback: 'January 15, 2025',
		formatter: (date) => {
			if (!date) {
				return 'January 15, 2025';
			}
			return new Date(date).toLocaleDateString('en-US', {
				year: 'numeric',
				month: 'long',
				day: 'numeric',
			});
		},
	},
	item_author: {
		label: __('Post Author', 'feedzy-rss-feeds'),
		property: 'author',
		fallback: 'John Doe',
	},
	item_url: {
		label: __('Post URL', 'feedzy-rss-feeds'),
		property: 'url',
		fallback: 'https://example.com/sample-post',
	},
	item_img: {
		label: __('Post Image', 'feedzy-rss-feeds'),
		property: 'image',
		fallback: 'https://via.placeholder.com/300x200?text=Sample+Image',
		isImage: true,
	},
};

// Component to render the actual tag content - displays only ONE property
function TagContent({ tag, feedItem, isPreview = false }) {
	// Get ONLY the specific property for this tag
	const rawValue = feedItem[tag] ?? 'No content found!';

	// Default text rendering for any other tag
	return (
		<span
			style={{
				...(isPreview && { color: '#666' }),
			}}
		>
			{rawValue}
		</span>
	);
}

function Edit({ attributes, setAttributes, context }) {
	const { tag } = attributes;
	const [isEditing, setIsEditing] = useState(!tag);
	const [tempValue, setTempValue] = useState(tag || '');
	const blockProps = useBlockProps();

	// Get feed item context for preview
	const feedItem = context?.['feedzy-rss-feeds/feedItem'] ?? [];
	const hasContext = feedItem && Object.keys(feedItem).length > 0;

	const handleSave = () => {
		setAttributes({ tag: tempValue });
		setIsEditing(false);
	};

	const handleCancel = () => {
		setTempValue(tag || '');
		setIsEditing(false);
	};

	const handleKeyPress = (event) => {
		if (event.key === 'Enter') {
			handleSave();
		} else if (event.key === 'Escape') {
			handleCancel();
		}
	};

	const tagOptions = Object.keys(FEED_TAGS).map((tagKey) => ({
		value: tagKey,
		label: FEED_TAGS[tagKey].label,
	}));

	return (
		<div {...blockProps}>
			<BlockControls>
				<ToolbarButton
					icon={editIcon}
					label={__('Edit Tag', 'feedzy-rss-feeds')}
					onClick={() => {
						setTempValue(tag || '');
						setIsEditing(true);
					}}
					isPressed={isEditing}
				/>
			</BlockControls>

			<InspectorControls>
				<PanelBody title={__('Tag Settings', 'feedzy-rss-feeds')}>
					<SelectControl
						label={__('Select Tag', 'feedzy-rss-feeds')}
						value={tag || ''}
						onChange={(value) => setAttributes({ tag: value })}
						options={[
							{
								value: '',
								label: __('Choose a tag…', 'feedzy-rss-feeds'),
							},
							...tagOptions,
						]}
					/>
					<TextControl
						label={__('Custom Tag Value', 'feedzy-rss-feeds')}
						value={tag || ''}
						onChange={(value) => setAttributes({ tag: value })}
						help={__(
							'Or enter a custom tag name',
							'feedzy-rss-feeds'
						)}
					/>

					{hasContext && (
						<div
							style={{
								marginTop: '16px',
								padding: '12px',
								backgroundColor: '#f0f0f0',
								borderRadius: '4px',
							}}
						>
							<strong>
								{__(
									'Preview Data Available:',
									'feedzy-rss-feeds'
								)}
							</strong>
							<ul
								style={{
									margin: '8px 0 0 0',
									paddingLeft: '16px',
									fontSize: '12px',
								}}
							>
								{Object.entries(feedItem).map(
									([key, value]) => (
										<li key={key}>
											<code>{key}</code>:{' '}
											{String(value).substring(0, 50)}
											{String(value).length > 50 && '...'}
										</li>
									)
								)}
							</ul>
						</div>
					)}
				</PanelBody>
			</InspectorControls>

			{isEditing ? (
				<div
					style={{
						display: 'flex',
						alignItems: 'center',
						gap: '8px',
						padding: '8px',
						backgroundColor: '#f9f9f9',
						border: '1px solid #ddd',
						borderRadius: '4px',
					}}
				>
					<SelectControl
						value={tempValue}
						onChange={setTempValue}
						options={[
							{
								value: '',
								label: __('Choose a tag…', 'feedzy-rss-feeds'),
							},
							...tagOptions,
						]}
						style={{ minWidth: '200px' }}
					/>
					<span>{__('or', 'feedzy-rss-feeds')}</span>
					<input
						type="text"
						value={tempValue}
						onChange={(e) => setTempValue(e.target.value)}
						onKeyDown={handleKeyPress}
						placeholder={__('Custom tag…', 'feedzy-rss-feeds')}
						style={{
							padding: '4px 8px',
							border: '1px solid #ddd',
							borderRadius: '2px',
							fontSize: '14px',
							minWidth: '150px',
						}}
					/>
					<Button
						icon={check}
						size="small"
						variant="primary"
						onClick={handleSave}
					/>
					<Button
						icon={close}
						size="small"
						variant="secondary"
						onClick={handleCancel}
					/>
				</div>
			) : (
				<div
					style={{
						minHeight: '24px',
						padding: '8px',
						border: hasContext
							? '1px solid #0073aa'
							: '1px solid #ddd',
						borderRadius: '4px',
						backgroundColor: hasContext ? '#f0f8ff' : '#f9f9f9',
						position: 'relative',
					}}
				>
					{tag ? (
						<TagContent
							tag={tag}
							feedItem={feedItem}
							isPreview={hasContext}
						/>
					) : (
						<span style={{ color: '#999', fontStyle: 'italic' }}>
							{__('No tag set', 'feedzy-rss-feeds')}
						</span>
					)}
				</div>
			)}
		</div>
	);
}

export default Edit;

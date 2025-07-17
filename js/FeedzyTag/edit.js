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
	RadioControl,
} from '@wordpress/components';

import { __ } from '@wordpress/i18n';

import { edit as editIcon, check, close } from '@wordpress/icons';

import { useState } from '@wordpress/element';

// Component to render the actual tag content - displays only ONE property
function TagContent({ tag, feedItem }) {
	// Get ONLY the specific property for this tag
	const rawValue = feedItem[tag] ?? 'No content found!';

	// Default text rendering for any other tag
	return <span>{rawValue}</span>;
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
					<TextControl
						label={__('Custom Tag Value', 'feedzy-rss-feeds')}
						value={tag || ''}
						onChange={(value) => setAttributes({ tag: value })}
						help={__(
							'Enter a custom tag name.',
							'feedzy-rss-feeds'
						)}
					/>

					{hasContext && (
						<RadioControl
							label={__(
								'Select from available data:',
								'feedzy-rss-feeds'
							)}
							onChange={(value) => setAttributes({ tag: value })}
							options={Object.entries(feedItem).map(
								([key, value]) => ({
									label: key,
									value: key,
									description: `${String(value).substring(0, 80)}${String(value).length > 80 ? '...' : ''}`,
								})
							)}
							selected={tag || ''}
						/>
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
				<div>
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

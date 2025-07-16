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
} from '@wordpress/components';

import { __ } from '@wordpress/i18n';

import { edit as editIcon, check, close } from '@wordpress/icons';

import { useState } from '@wordpress/element';

function Edit({ attributes, setAttributes }) {
	const { tag } = attributes;
	const [isEditing, setIsEditing] = useState(!tag);
	const [tempValue, setTempValue] = useState(tag || '');
	const blockProps = useBlockProps();

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
						label={__('Tag Value', 'feedzy-rss-feeds')}
						value={tag || ''}
						onChange={(value) => setAttributes({ tag: value })}
					/>
				</PanelBody>
			</InspectorControls>

			{isEditing ? (
				<div
					style={{
						display: 'flex',
						alignItems: 'center',
						gap: '8px',
					}}
				>
					<input
						type="text"
						value={tempValue}
						onChange={(e) => setTempValue(e.target.value)}
						onKeyDown={handleKeyPress}
						style={{
							padding: '4px 8px',
							border: '1px solid #ddd',
							borderRadius: '2px',
							fontSize: '14px',
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
				<div>{tag || __('No tag set', 'feedzy-rss-feeds')}</div>
			)}
		</div>
	);
}

export default Edit;

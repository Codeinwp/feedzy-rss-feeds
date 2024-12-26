/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import { serialize } from '@wordpress/blocks';

import {
	store as blockEditorStore,
	useBlockProps,
	BlockControls,
	InnerBlocks,
	InspectorControls,
} from '@wordpress/block-editor';

import {
	PanelBody,
	RangeControl,
	SelectControl,
	ToolbarButton,
	ToolbarGroup,
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';

import { useState } from '@wordpress/element';

import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies.
 */
import Placeholder from './placeholder';

const Edit = ({ attributes, setAttributes, clientId }) => {
	const blockProps = useBlockProps();

	const [isEditing, setIsEditing] = useState(!attributes?.feed?.source);

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

	if (!isSelected && innerBlocksContent) {
		return (
			<div {...blockProps}>
				<ServerSideRender
					block="feedzy-rss-feeds/loop"
					attributes={{
						...attributes,
						innerBlocksContent,
					}}
				/>
			</div>
		);
	}

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon="edit"
						title={__('Edit Feed', 'feedzy-rss-feeds')}
						onClick={() => setIsEditing(true)}
					/>
				</ToolbarGroup>
			</BlockControls>

			<InspectorControls>
				<PanelBody title={__('Settings', 'feedzy-rss-feeds')}>
					<RangeControl
						label={__('Column Count', 'feedzy-rss-feeds')}
						value={attributes?.layout?.columnCount || 1}
						onChange={(value) =>
							onChangeLayout({ type: 'columnCount', value })
						}
						min={1}
						max={5}
					/>

					<RangeControl
						label={__('Number of Items', 'feedzy-rss-feeds')}
						value={attributes?.query?.max || 5}
						onChange={(value) =>
							onChangeQuery({ type: 'max', value })
						}
						min={1}
						max={20}
					/>

					<SelectControl
						label={__('Sorting Order', 'feedzy-rss-feeds')}
						value={attributes?.query?.sort}
						options={[
							{
								label: __('Default', 'feedzy-rss-feeds'),
								value: 'default',
							},
							{
								label: __(
									'Date Descending',
									'feedzy-rss-feeds'
								),
								value: 'date_desc',
							},
							{
								label: __('Date Ascending', 'feedzy-rss-feeds'),
								value: 'date_asc',
							},
							{
								label: __(
									'Title Descending',
									'feedzy-rss-feeds'
								),
								value: 'title_desc',
							},
							{
								label: __(
									'Title Ascending',
									'feedzy-rss-feeds'
								),
								value: 'title_asc',
							},
						]}
						onChange={(value) =>
							onChangeQuery({ type: 'sort', value })
						}
					/>

					<SelectControl
						label={__('Feed Caching Time', 'feedzy-rss-feeds')}
						value={attributes?.query?.refresh || '12_hours'}
						options={[
							{
								label: __('1 Hour', 'feedzy-rss-feeds'),
								value: '1_hours',
							},
							{
								label: __('2 Hours', 'feedzy-rss-feeds'),
								value: '3_hours',
							},
							{
								label: __('12 Hours', 'feedzy-rss-feeds'),
								value: '12_hours',
							},
							{
								label: __('1 Day', 'feedzy-rss-feeds'),
								value: '1_days',
							},
							{
								label: __('3 Days', 'feedzy-rss-feeds'),
								value: '3_days',
							},
							{
								label: __('15 Days', 'feedzy-rss-feeds'),
								value: '15_days',
							},
						]}
						onChange={(value) =>
							onChangeQuery({ type: 'refresh', value })
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<InnerBlocks
					template={[
						[
							'core/group',
							{
								layout: {
									type: 'constrained',
								},
								style: {
									spacing: {
										padding: {
											top: 'var:preset|spacing|30',
											bottom: 'var:preset|spacing|30',
											left: 'var:preset|spacing|30',
											right: 'var:preset|spacing|30',
										},
										margin: {
											top: 'var:preset|spacing|30',
											bottom: 'var:preset|spacing|30',
										},
									},
								},
							},
							[
								[
									'core/image',
									{
										url: window.feedzyloopjs.defaultImage,
										alt: '{{feedzy_title}}',
										href: '{{feedzy_url}}',
									},
								],
								[
									'core/paragraph',
									{
										content:
											'<a href="http://{{feedzy_url}}">{{feedzy_title}}</a>',
									},
								],
								[
									'core/paragraph',
									{
										content: '{{feedzy_meta}}',
										fontSize: 'medium',
									},
								],
								[
									'core/paragraph',
									{
										content: '{{feedzy_description}}',
										fontSize: 'small',
									},
								],
							],
						],
					]}
				/>
			</div>
		</>
	);
};

export default Edit;

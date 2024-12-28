/* eslint-disable @wordpress/no-unsafe-wp-apis */
/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import {
	store as blocksStore,
	createBlocksFromInnerBlocksTemplate,
	serialize,
} from '@wordpress/blocks';

import {
	store as blockEditorStore,
	__experimentalBlockVariationPicker as BlockVariationPicker,
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

import { useDispatch, useSelect } from '@wordpress/data';

import { useState } from '@wordpress/element';

import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies.
 */
import metadata from './block.json';
import Placeholder from './placeholder';
import ConditionsControl from '../Conditions/ConditionsControl';

const { name } = metadata;

const Edit = ({ attributes, setAttributes, clientId }) => {
	const blockProps = useBlockProps();

	const [isEditing, setIsEditing] = useState(!attributes?.feed?.source);

	const { replaceInnerBlocks, selectBlock } = useDispatch(blockEditorStore);

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

				<PanelBody
					title={[
						__('Filter items', 'feedzy-rss-feeds'),
						!feedzyData.isPro && (
							<span className="fz-pro-label">Pro</span>
						),
					]}
					initialOpen={false}
					className={
						feedzyData.isPro
							? 'feedzy-item-filter'
							: 'feedzy-item-filter fz-locked'
					}
				>
					<ConditionsControl
						conditions={
							attributes?.conditions || {
								conditions: [],
								match: 'all',
							}
						}
						setConditions={(conditions) => {
							setAttributes({ conditions });
						}}
					/>
				</PanelBody>
			</InspectorControls>

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
							}
							selectBlock(clientId);
						}}
					/>
				)}
			</div>
		</>
	);
};

export default Edit;

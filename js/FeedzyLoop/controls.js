/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import { BlockControls, InspectorControls } from '@wordpress/block-editor';

import {
	Button,
	ExternalLink,
	PanelBody,
	RangeControl,
	SelectControl,
	ToolbarButton,
	ToolbarGroup,
} from '@wordpress/components';

import { useState } from '@wordpress/element';

/**
 * Internal dependencies.
 */
import ConditionsControl from '../Conditions/ConditionsControl';
import PatternSelector from './components/PatternSelector';

const Controls = ({
	attributes,
	isEditing,
	isPreviewing,
	setAttributes,
	onChangeLayout,
	onChangeQuery,
	setIsEditing,
	setIsPreviewing,
}) => {
	const [isPatternModalOpen, setIsPatternModalOpen] = useState(false);

	return (
		<>
			{isPatternModalOpen && (
				<PatternSelector setOpen={setIsPatternModalOpen} />
			)}

			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon="edit"
						title={__('Edit Feed', 'feedzy-rss-feeds')}
						onClick={() => setIsEditing(true)}
					/>
				</ToolbarGroup>

				<ToolbarGroup>
					<ToolbarButton onClick={() => setIsPatternModalOpen(true)}>
						{__('Replace', 'feedzy-rss-feeds')}
					</ToolbarButton>
				</ToolbarGroup>

				<ToolbarGroup>
					<ToolbarButton
						onClick={() => setIsPreviewing(!isPreviewing)}
					>
						{isPreviewing
							? __('Hide Preview', 'feedzy-rss-feeds')
							: __('Show Preview', 'feedzy-rss-feeds')}
					</ToolbarButton>
				</ToolbarGroup>
			</BlockControls>

			<InspectorControls>
				{!isEditing && (
					<PanelBody
						initialOpen={false}
						title={__('Feed Source', 'feedzy-rss-feeds')}
						key="source"
					>
						<Button
							variant="secondary"
							onClick={() => setIsEditing(true)}
							style={{
								width: '100%',
								justifyContent: 'center',
							}}
						>
							{__('Edit Feed', 'feedzy-rss-feeds')}
						</Button>
					</PanelBody>
				)}

				<PanelBody
					title={__('Settings', 'feedzy-rss-feeds')}
					key="settings"
				>
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

					<ExternalLink
						href="https://docs.themeisle.com/article/2217-feedzy-loop#magic_tags"
						target="_blank"
					>
						{__('Feedzy Loop Documentation', 'feedzy-rss-feeds')}
					</ExternalLink>

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
						!window.feedzyData.isPro && (
							<span className="fz-pro-label">Pro</span>
						),
					]}
					initialOpen={false}
					key="filters"
					className="feedzy-item-filter"
				>
					{!window.feedzyData.isPro && (
						<div className="fz-upsell-notice">
							{__(
								'Unlock this feature and more advanced options with',
								'feedzy-rss-feeds'
							)}{' '}
							<ExternalLink href="https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/?utm_source=wpadmin&utm_medium=blockeditor&utm_campaign=keywordsfilter&utm_content=feedzy-rss-feeds">
								{__('Feedzy Pro', 'feedzy-rss-feeds')}
							</ExternalLink>
						</div>
					)}

					<ConditionsControl
						conditions={
							window.feedzyData.isPro
								? attributes?.conditions || {
										conditions: [],
										match: 'all',
									}
								: {
										match: 'all',
										conditions: [
											{
												field: 'title',
												operator: 'contains',
												value: 'Sports',
											},
										],
									}
						}
						setConditions={(conditions) => {
							setAttributes({ conditions });
						}}
					/>
				</PanelBody>
			</InspectorControls>
		</>
	);
};

export default Controls;

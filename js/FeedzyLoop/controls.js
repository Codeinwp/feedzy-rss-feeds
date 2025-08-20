/**
 * WordPress dependencies.
 */
import { __, sprintf } from '@wordpress/i18n';

import { BlockControls, InspectorControls } from '@wordpress/block-editor';

import {
	Button,
	ExternalLink,
	PanelBody,
	RangeControl,
	SelectControl,
	ToolbarButton,
	ToolbarGroup,
	TextControl,
	BaseControl,
} from '@wordpress/components';

import { Fragment } from '@wordpress/element';

/**
 * Internal dependencies.
 */
import ConditionsControl from '../Conditions/ConditionsControl';
import FallbackImageLoader from './components/FallbackImageLoader.jsx';

// Make this available to all components in this module
function decodeHtmlEntities(str) {
	if (typeof str !== 'string') {
		return str;
	}
	const textarea = document.createElement('textarea');
	textarea.innerHTML = str;
	return textarea.value;
}

const Controls = ({
	attributes,
	isEditing,
	isPreviewing,
	setAttributes,
	onChangeLayout,
	onChangeQuery,
	setIsEditing,
	setIsPreviewing,
	variations,
	setVariations,
}) => {
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

			<InspectorControls key="inspector">
				<div>
					{!isEditing && (
						<CustomInspectorControls
							attributes={attributes}
							isEditing={isEditing}
							setIsEditing={setIsEditing}
							onChangeLayout={onChangeLayout}
							onChangeQuery={onChangeQuery}
							setAttributes={setAttributes}
							variations={variations}
							setVariations={setVariations}
						/>
					)}
				</div>
			</InspectorControls>

			<InspectorControls group="advanced">
				<div>
					{!isEditing && (
						<CustomAdvancedControls
							attributes={attributes}
							onChangeQuery={onChangeQuery}
						/>
					)}
				</div>
			</InspectorControls>
		</>
	);
};

function CustomInspectorControls({
	attributes,
	isEditing,
	setIsEditing,
	onChangeLayout,
	onChangeQuery,
	setAttributes,
	variations,
	setVariations,
}) {
	return (
		<Fragment>
			<PanelBody
				title={__('Settings', 'feedzy-rss-feeds')}
				key="settings"
			>
				<BaseControl
					label={__('Layout', 'feedzy-rss-feeds')}
					id="feedzy-loop-layout"
				>
					<div className="fz-block-variation-picker">
						{variations?.map((variation) => (
							<Button
								key={variation.name}
								variant={'link'}
								onClick={() => setVariations(variation)}
							>
								{variation.icon?.()}
							</Button>
						))}
					</div>
				</BaseControl>

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
					onChange={(value) => onChangeQuery({ type: 'max', value })}
					min={1}
					max={20}
				/>

				<ExternalLink
					href="https://docs.themeisle.com/article/2217-feedzy-loop#magic_tags"
					target="_blank"
				>
					{__('Feedzy Loop Documentation', 'feedzy-rss-feeds')}
				</ExternalLink>
			</PanelBody>

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
				title={__('Item Image Options', 'feedzy-rss-feeds')}
				initialOpen={false}
				className="feedzy-image-options"
			>
				<SelectControl
					label={__(
						'Display first image if available?',
						'feedzy-rss-feeds'
					)}
					value={attributes.thumb}
					options={[
						{
							label: __(
								'Yes (without a fallback image)',
								'feedzy-rss-feeds'
							),
							value: 'auto',
						},
						{
							label: __(
								'Yes (with a fallback image)',
								'feedzy-rss-feeds'
							),
							value: 'yes',
						},
						{
							label: __('No', 'feedzy-rss-feeds'),
							value: 'no',
						},
					]}
					onChange={(value) => setAttributes({ thumb: value })}
					className="feedzy-thumb"
				/>

				{attributes?.thumb !== 'no' && (
					<Fragment>
						{attributes?.thumb !== 'auto' && (
							<FallbackImageLoader
								imageValue={attributes?.fallbackImage}
								onChangeImage={(imageData) =>
									setAttributes({
										fallbackImage: imageData,
									})
								}
								onRemoveImage={() =>
									setAttributes({
										fallbackImage: undefined,
									})
								}
								label={__(
									'Fallback image if no image is found.',
									'feedzy-rss-feeds'
								)}
							/>
						)}
					</Fragment>
				)}
			</PanelBody>

			<PanelBody
				title={[__('Filter items', 'feedzy-rss-feeds')]}
				initialOpen={false}
				key="filters"
				className="feedzy-item-filter"
			>
				{!window.feedzyData?.isPro && (
					<div className="fz-upsell-notice">
						{__(
							'Unlock more advanced options with',
							'feedzy-rss-feeds'
						)}{' '}
						<ExternalLink href="https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/?utm_source=wpadmin&utm_medium=blockeditor&utm_campaign=keywordsfilter&utm_content=feedzy-rss-feeds">
							{__('Feedzy Pro', 'feedzy-rss-feeds')}
						</ExternalLink>
					</div>
				)}

				<ConditionsControl
					conditions={
						window.feedzyData?.isPro
							? attributes?.conditions || {
									conditions: [],
									match: 'all',
								}
							: attributes?.conditions || {
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

			<PanelBody
				title={[
					__('Referral URL', 'feedzy-rss-feeds'),
					!window.feedzyData?.isPro && (
						<span className="fz-pro-label">Pro</span>
					),
				]}
				initialOpen={false}
				className={
					window.feedzyData?.isPro
						? 'feedzy-pro-options'
						: 'feedzy-pro-options fz-locked'
				}
			>
				{!window.feedzyData?.isPro && (
					<div className="fz-upsell-notice">
						{__(
							'Unlock this feature and more advanced options with',
							'feedzy-rss-feeds'
						)}{' '}
						<ExternalLink href="https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/?utm_source=wpadmin&utm_medium=blockeditor&utm_campaign=refferal&utm_content=feedzy-rss-feeds">
							{__('Feedzy Pro', 'feedzy-rss-feeds')}
						</ExternalLink>
					</div>
				)}
				<TextControl
					label={__('Referral URL parameters.', 'feedzy-rss-feeds')}
					help={__('Without ("?")', 'feedzy-rss-feeds')}
					placeholder={decodeHtmlEntities(
						'(' +
							sprintf(
								// translators: %s is the list of examples.
								__('eg: %s', 'feedzy-rss-feeds'),
								'promo_code=feedzy_is_awesome'
							) +
							')'
					)}
					value={attributes?.referral_url}
					onChange={(value) => {
						window.tiTrk
							?.with?.('feedzy')
							?.add?.({ feature: 'block-referral-url' });
						setAttributes({ referral_url: value });
					}}
				/>
			</PanelBody>
		</Fragment>
	);
}

function CustomAdvancedControls({ attributes, onChangeQuery }) {
	return (
		<Fragment>
			<SelectControl
				label={__('Sorting Order', 'feedzy-rss-feeds')}
				value={attributes?.query?.sort}
				options={[
					{
						label: __('Default', 'feedzy-rss-feeds'),
						value: 'default',
					},
					{
						label: __('Date Descending', 'feedzy-rss-feeds'),
						value: 'date_desc',
					},
					{
						label: __('Date Ascending', 'feedzy-rss-feeds'),
						value: 'date_asc',
					},
					{
						label: __('Title Descending', 'feedzy-rss-feeds'),
						value: 'title_desc',
					},
					{
						label: __('Title Ascending', 'feedzy-rss-feeds'),
						value: 'title_asc',
					},
				]}
				onChange={(value) => onChangeQuery({ type: 'sort', value })}
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
				onChange={(value) => onChangeQuery({ type: 'refresh', value })}
			/>
		</Fragment>
	);
}

export default Controls;

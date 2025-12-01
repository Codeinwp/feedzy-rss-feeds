// jshint ignore: start

/**
 * Block dependencies
 */
import { Component, createInterpolateElement, Fragment } from '@wordpress/element';
import RadioImageControl from './radio-image-control/';

/**
 * External dependencies
 */
import classnames from 'classnames';
import { __, sprintf } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import {
	BaseControl,
	ExternalLink,
	PanelBody,
	RangeControl,
	TextControl,
	TextareaControl,
	Button,
	ToggleControl,
	SelectControl,
	Dashicon,
} from '@wordpress/components';
import { FallbackImageLoader } from '../FeedzyLoop/components/FallbackImageLoader';

/**
 * Create an Inspector Controls wrapper Component
 */
class Inspector extends Component {
	constructor() {
		super(...arguments);
		this.state = {
			tab: 'content',
		};
	}

	render() {
		const refreshFeed = applyFilters('feedzy_widget_refresh_feed', [
			{ label: __('1 Hour', 'feedzy-rss-feeds'), value: '1_hours' },
			{ label: __('2 Hours', 'feedzy-rss-feeds'), value: '3_hours' },
			{ label: __('12 Hours', 'feedzy-rss-feeds'), value: '12_hours' },
			{ label: __('1 Day', 'feedzy-rss-feeds'), value: '1_days' },
			{ label: __('3 Days', 'feedzy-rss-feeds'), value: '3_days' },
			{ label: __('15 Days', 'feedzy-rss-feeds'), value: '15_days' },
		]);

		const decodeHtmlEntities = (str) => {
			if (typeof str !== 'string') {
				return str;
			}
			const textarea = document.createElement('textarea');
			textarea.innerHTML = str;
			return textarea.value;
		};

		return (
			<Fragment>
				<PanelBody className="fz-section-header-panel">
					<Button
						className={classnames('header-tab', {
							'is-selected': 'content' === this.state.tab,
						})}
						onClick={() => this.setState({ tab: 'content' })}
					>
						<span>
							<Dashicon icon="editor-table" />
							{__('Content', 'feedzy-rss-feeds')}
						</span>
					</Button>
					<Button
						className={classnames('header-tab', {
							'is-selected': 'style' === this.state.tab,
						})}
						onClick={() => this.setState({ tab: 'style' })}
					>
						<span>
							<Dashicon icon="admin-customizer" />
							{__('Style', 'feedzy-rss-feeds')}
						</span>
					</Button>
					<Button
						className={classnames('header-tab', {
							'is-selected': 'advanced' === this.state.tab,
						})}
						onClick={() => this.setState({ tab: 'advanced' })}
					>
						<span>
							<Dashicon icon="admin-generic" />
							{__('Advanced', 'feedzy-rss-feeds')}
						</span>
					</Button>
				</PanelBody>
				{'content' === this.state.tab && (
					<Fragment>
						<PanelBody
							title={__('General', 'feedzy-rss-feeds')}
							initialOpen={true}
						>
							{this.props.attributes.status !== 0 && (
								<Fragment>
									<TextControl
										label={__(
											'Feed Source',
											'feedzy-rss-feeds'
										)}
										className="feedzy-source"
										value={this.props.attributes.feeds}
										onChange={this.props.edit.onChangeFeed}
									/>
									<Button
										isLarge
										isPrimary
										type="submit"
										onClick={this.props.edit.loadFeed}
										className="loadFeed"
									>
										{__('Load Feed', 'feedzy-rss-feeds')}
									</Button>
								</Fragment>
							)}
							{'fetched' === this.props.state.route && (
								<Fragment>
									<RangeControl
										label={__(
											'Number of Items',
											'feedzy-rss-feeds'
										)}
										value={
											Number(this.props.attributes.max) ||
											5
										}
										onChange={this.props.edit.onChangeMax}
										min={1}
										max={
											this.props.attributes.feedData.items
												.length || 10
										}
										beforeIcon="sort"
										className="feedzy-max"
									/>
									<SelectControl
										label={__(
											'Sorting Order',
											'feedzy-rss-feeds'
										)}
										value={this.props.attributes.sort}
										options={[
											{
												label: __(
													'Default',
													'feedzy-rss-feeds'
												),
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
												label: __(
													'Date Ascending',
													'feedzy-rss-feeds'
												),
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
										onChange={this.props.edit.onSort}
										className="feedzy-sort"
									/>
								</Fragment>
							)}
						</PanelBody>

						<PanelBody
							title={__('Item Options', 'feedzy-rss-feeds')}
							initialOpen={false}
							className="feedzy-item-options"
						>
							<ToggleControl
								label={__(
									'Display item Title',
									'feedzy-rss-feeds'
								)}
								checked={!!this.props.attributes.itemTitle}
								onChange={this.props.edit.onToggleItemTitle}
								className="feedzy-summary"
							/>

							{this.props.attributes.itemTitle && (
								<TextControl
									label={__(
										'Title Character Limit',
										'feedzy-rss-feeds'
									)}
									help={__(
										'Leave empty to show full title. A value of 0 will remove the title.',
										'feedzy-rss-feeds'
									)}
									type="number"
									value={this.props.attributes.title}
									onChange={this.props.edit.onTitle}
									className="feedzy-title-length"
								/>
							)}

							<ToggleControl
								label={__(
									'Display post description?',
									'feedzy-rss-feeds'
								)}
								checked={!!this.props.attributes.summary}
								onChange={this.props.edit.onToggleSummary}
								className="feedzy-summary"
							/>

							{this.props.attributes.summary && (
								<TextControl
									label={__(
										'Description Character Limit',
										'feedzy-rss-feeds'
									)}
									help={__(
										'Leave empty to show full description.',
										'feedzy-rss-feeds'
									)}
									type="number"
									value={this.props.attributes.summarylength}
									onChange={this.props.edit.onSummaryLength}
									className="feedzy-summary-length"
									min={0}
								/>
							)}
						</PanelBody>

						<PanelBody
							title={[
								__('Filter items', 'feedzy-rss-feeds'),
							]}
							initialOpen={false}
							className='feedzy-item-filter'
						>
							{!window.feedzyjs.isPro && (
								<div className="fz-upsell-notice">
									{createInterpolateElement(
										__(
											'Unlock more advanced options with <a>Feedzy Pro</a>',
											'feedzy-rss-feeds'
										),
										{
											a: (
												<ExternalLink href="https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/?utm_source=wpadmin&utm_medium=blockeditor&utm_campaign=keywordsfilter&utm_content=feedzy-rss-feeds" />
											),
										}
									)}
								</div>
							)}
							<TextControl
								label={__(
									'Only display if selected field contains:',
									'feedzy-rss-feeds'
								)}
								help={__(
									'Use comma(,) and plus(+) keyword',
									'feedzy-rss-feeds'
								)}
								value={this.props.attributes.keywords_title}
								onChange={this.props.edit.onKeywordsTitle}
								className="feedzy-include"
							/>
							<SelectControl
								label={__(
									'Select a field if you want to include keyword.',
									'feedzy-rss-feeds'
								)}
								value={this.props.attributes.keywords_inc_on}
								options={[
									{
										label: __('Title', 'feedzy-rss-feeds'),
										value: 'title',
									},
									{
										label: __('Author', 'feedzy-rss-feeds'),
										value: 'author',
										disabled: !window.feedzyjs.isPro,
									},
									{
										label: __(
											'Description',
											'feedzy-rss-feeds'
										),
										value: 'description',
										disabled: !window.feedzyjs.isPro,
									},
								]}
								onChange={this.props.edit.onKeywordsIncludeOn}
							/>
							<TextControl
								label={__(
									'Exclude if selected field contains:',
									'feedzy-rss-feeds'
								)}
								help={__(
									'Use comma(,) and plus(+) keyword',
									'feedzy-rss-feeds'
								)}
								value={this.props.attributes.keywords_ban}
								onChange={this.props.edit.onKeywordsBan}
								className="feedzy-ban"
							/>
							<SelectControl
								label={__(
									'Select a field if you want to exclude keyword.',
									'feedzy-rss-feeds'
								)}
								value={this.props.attributes.keywords_exc_on}
								options={[
									{
										label: __('Title', 'feedzy-rss-feeds'),
										value: 'title',
									},
									{
										label: __('Author', 'feedzy-rss-feeds'),
										value: 'author',
										disabled: !window.feedzyjs.isPro,
									},
									{
										label: __(
											'Description',
											'feedzy-rss-feeds'
										),
										value: 'description',
										disabled: !window.feedzyjs.isPro,
									},
								]}
								onChange={this.props.edit.onKeywordsExcludeOn}
							/>
							
							<div className={
								!window.feedzyjs.isPro
									? 'fz-locked'
									: ''
								}
							>
								<p className="fz-main-label">
									{__(
										'Filter feed item by date range.',
										'feedzy-rss-feeds'
									)}
								</p>
								<TextControl
									type="datetime-local"
									label={__('From:', 'feedzy-rss-feeds')}
									value={this.props.attributes.from_datetime}
									onChange={this.props.edit.onFromDateTime}
								/>
								<TextControl
									type="datetime-local"
									label={__('To:', 'feedzy-rss-feeds')}
									value={this.props.attributes.to_datetime}
									onChange={this.props.edit.onToDateTime}
								/>
							</div>
						</PanelBody>
					</Fragment>
				)}

				{'fetched' === this.props.state.route &&
					'style' === this.state.tab && (
						<Fragment>
							<PanelBody
								title={__(
									'Item Image Options',
									'feedzy-rss-feeds'
								)}
								initialOpen={true}
								className="feedzy-image-options"
							>
								<SelectControl
									label={__(
										'Display first image if available?',
										'feedzy-rss-feeds'
									)}
									value={this.props.attributes.thumb}
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
									onChange={this.props.edit.onThumb}
									className="feedzy-thumb"
								/>

								{this.props.attributes.thumb !== 'no' && (
									<Fragment>
										{this.props.attributes.thumb !==
											'auto' && (
											<FallbackImageLoader
												imageValue={
													this.props.attributes
														.default
												}
												onChangeImage={
													this.props.edit.onDefault
												}
												onRemoveImage={() =>
													this.props.edit.onDefault(
														undefined
													)
												}
												label={__(
													'Fallback image if no image is found.',
													'feedzy-rss-feeds'
												)}
											/>
										)}
										<TextControl
											label={__(
												'Thumbnails dimension.',
												'feedzy-rss-feeds'
											)}
											type="number"
											value={this.props.attributes.size}
											onChange={this.props.edit.onSize}
										/>
										<SelectControl
											label={__(
												'Aspect Ratio',
												'feedzy-rss-feeds'
											)}
											value={
												this.props.attributes
													.aspectRatio
											}
											options={[
												{
													label: __(
														'Original',
														'feedzy-rss-feeds'
													),
													value: 'auto',
												},
												{
													label: __(
														'1:1 (Square)',
														'feedzy-rss-feeds'
													),
													value: '1',
												},
												{
													label: __(
														'4:3 (Standard)',
														'feedzy-rss-feeds'
													),
													value: '4/3',
												},
												{
													label: __(
														'3:4 (Portrait)',
														'feedzy-rss-feeds'
													),
													value: '3/4',
												},
												{
													label: __(
														'3:2 (Classic)',
														'feedzy-rss-feeds'
													),
													value: '3 / 2',
												},
												{
													label: __(
														'2:3 (Clasic Portrait)',
														'feedzy-rss-feeds'
													),
													value: '2/3',
												},
												{
													label: __(
														'16:9 (Widescreen)',
														'feedzy-rss-feeds'
													),
													value: '16/9',
												},
												{
													label: __(
														'9:16 (Vertical)',
														'feedzy-rss-feeds'
													),
													value: '9/16',
												},
											]}
											onChange={
												this.props.edit.onAspectRatio
											}
											className="feedzy-aspect-ratio-select"
										/>
									</Fragment>
								)}

								<ToggleControl
									label={__(
										'Disable default style?',
										'feedzy-rss-feeds'
									)}
									checked={
										!!this.props.attributes.disableStyle
									}
									onChange={
										this.props.edit.onToggleDisableStyle
									}
									className="feedzy-summary"
									help={__(
										'If disabled, it will be considered the global setting.',
										'feedzy-rss-feeds'
									)}
								/>
							</PanelBody>

							<PanelBody
								title={[
									__('Feed Layout', 'feedzy-rss-feeds'),
									!window.feedzyjs.isPro && (
										<span className="fz-pro-label">
											Pro
										</span>
									),
								]}
								initialOpen={false}
								className={
									window.feedzyjs.isPro
										? 'feedzy-layout'
										: 'feedzy-layout fz-locked'
								}
							>
								{!window.feedzyjs.isPro && (
									<div className="fz-upsell-notice">
										{createInterpolateElement(
											__(
												'Unlock this feature and more advanced options with <a>Feedzy Pro</a>',
												'feedzy-rss-feeds'
											),
											{
												a: (
													<ExternalLink href="https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/?utm_source=wpadmin&utm_medium=blockeditor&utm_campaign=layouts&utm_content=feedzy-rss-feeds" />
												),
											}
										)}
									</div>
								)}

								<RangeControl
									label={__('Columns', 'feedzy-rss-feeds')}
									help={__(
										'How many columns we should use to display the feed items?',
										'feedzy-rss-feeds'
									)}
									value={this.props.attributes.columns || 1}
									onChange={this.props.edit.onColumns}
									min={1}
									max={6}
									beforeIcon="sort"
									allowReset
								/>

								<RadioImageControl
									label={__('Template', 'feedzy-rss-feeds')}
									selected={this.props.attributes.template}
									options={[
										{
											label: __(
												'Default',
												'feedzy-rss-feeds'
											),
											src:
												window.feedzyjs.imagepath +
												'feedzy-default-template.png',
											value: 'default',
										},
										{
											label: __(
												'Round',
												'feedzy-rss-feeds'
											),
											src:
												window.feedzyjs.imagepath +
												'feedzy-style1-template.png',
											value: 'style1',
										},
										{
											label: __(
												'Cards',
												'feedzy-rss-feeds'
											),
											src:
												window.feedzyjs.imagepath +
												'feedzy-style2-template.png',
											value: 'style2',
										},
									]}
									onChange={this.props.edit.onTemplate}
								/>
							</PanelBody>
						</Fragment>
					)}
				{'fetched' === this.props.state.route &&
					'advanced' === this.state.tab && (
						<Fragment>
							<PanelBody
								title={__(
									'Feed Items Custom Options',
									'feedzy-rss-feeds'
								)}
								className="feedzy-advanced-options"
								initialOpen={true}
							>
								<BaseControl>
									<TextControl
										label={__(
											'Meta fields to show',
											'feedzy-rss-feeds'
										)}
										help={sprintf(
											//translators: %s: the keyword for the user to introduce.
											__(
												'Leave empty to display all and "%s" to display nothing.',
												'feedzy-rss-feeds'
											),
											'no'
										)}
										placeholder={decodeHtmlEntities(
											'(' +
												sprintf(
													// translators: %s is the list of examples.
													__(
														'eg: %s',
														'feedzy-rss-feeds'
													),
													window.feedzyjs.isPro
														? 'author, date, time, tz=local, categories'
														: 'author, date, time, tz=local'
												) +
												')'
										)}
										value={this.props.attributes.metafields}
										onChange={this.props.edit.onChangeMeta}
										className="feedzy-meta"
									/>
									<TextControl
										label={__(
											'Meta fields for sources.',
											'feedzy-rss-feeds'
										)}
										placeholder={decodeHtmlEntities(
											'(' +
												sprintf(
													// translators: %s is the list of examples.
													__(
														'eg: %s',
														'feedzy-rss-feeds'
													),
													'source'
												) +
												')'
										)}
										value={
											this.props.attributes.multiple_meta
										}
										onChange={
											this.props.edit.onChangeMultipleMeta
										}
										className="feedzy-multiple-meta"
									/>
								</BaseControl>

								<ToggleControl
									label={__(
										'Display price if available?',
										'feedzy-rss-feeds'
									)}
									help={
										this.props.attributes.price &&
										this.props.attributes.template ===
											'default'
											? __(
													'Choose a different template for this to work.',
													'feedzy-rss-feeds'
												)
											: null
									}
									checked={!!this.props.attributes.price}
									onChange={this.props.edit.onTogglePrice}
									className={
										window.feedzyjs.isPro
											? 'feedzy-pro-price'
											: 'feedzy-pro-price fz-locked'
									}
								/>

								{this.props.attributes.feedData.channel !==
									null && (
									<ToggleControl
										label={__(
											'Display feed title?',
											'feedzy-rss-feeds'
										)}
										checked={
											!!this.props.attributes.feed_title
										}
										onChange={
											this.props.edit.onToggleFeedTitle
										}
										className="feedzy-title"
									/>
								)}

								<SelectControl
									label={__(
										'Open Links In',
										'feedzy-rss-feeds'
									)}
									value={this.props.attributes.target}
									options={[
										{
											label: __(
												'New Tab',
												'feedzy-rss-feeds'
											),
											value: '_blank',
										},
										{
											label: __(
												'Same Tab',
												'feedzy-rss-feeds'
											),
											value: '_self',
										},
									]}
									onChange={this.props.edit.onTarget}
								/>

								<SelectControl
									label={__(
										'Make this link a "nofollow" link?',
										'feedzy-rss-feeds'
									)}
									value={this.props.attributes.follow}
									options={[
										{
											label: __('No', 'feedzy-rss-feeds'),
											value: 'no',
										},
										{
											label: __(
												'Yes',
												'feedzy-rss-feeds'
											),
											value: 'yes',
										},
									]}
									onChange={this.props.edit.onLinkNoFollow}
								/>

								<RangeControl
									label={__(
										'Ignore first N items',
										'feedzy-rss-feeds'
									)}
									value={
										Number(this.props.attributes.offset) ||
										0
									}
									onChange={this.props.edit.onChangeOffset}
									min={0}
									max={
										this.props.attributes.feedData.items
											.length
									}
									beforeIcon="sort"
									className="feedzy-offset"
								/>

								<ToggleControl
									label={__(
										'Lazy load feed?',
										'feedzy-rss-feeds'
									)}
									checked={!!this.props.attributes.lazy}
									onChange={this.props.edit.onToggleLazy}
									className="feedzy-lazy"
									help={__(
										'Only on the front end.',
										'feedzy-rss-feeds'
									)}
								/>
							</PanelBody>

							<PanelBody
								title={[
									__('Referral URL', 'feedzy-rss-feeds'),
									!window.feedzyjs.isPro && (
										<span className="fz-pro-label">
											Pro
										</span>
									),
								]}
								initialOpen={false}
								className={
									window.feedzyjs.isPro
										? 'feedzy-pro-options'
										: 'feedzy-pro-options fz-locked'
								}
							>
								{!window.feedzyjs.isPro && (
									<div className="fz-upsell-notice">
										{createInterpolateElement(
											__(
												'Unlock this feature and more advanced options with <a>Feedzy Pro</a>',
												'feedzy-rss-feeds'
											),
											{
												a: (
													<ExternalLink href="https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/?utm_source=wpadmin&utm_medium=blockeditor&utm_campaign=refferal&utm_content=feedzy-rss-feeds" />
												),
											}
										)}
									</div>
								)}
								<TextControl
									label={__(
										'Referral URL parameters.',
										'feedzy-rss-feeds'
									)}
									help={__(
										'Without ("?")',
										'feedzy-rss-feeds'
									)}
									placeholder={decodeHtmlEntities(
										'(' +
											sprintf(
												// translators: %s is the list of examples.
												__(
													'eg: %s',
													'feedzy-rss-feeds'
												),
												'promo_code=feedzy_is_awesome'
											) +
											')'
									)}
									value={this.props.attributes.referral_url}
									onChange={this.props.edit.onReferralURL}
								/>
							</PanelBody>

							<PanelBody
								title={__(
									'Additional options',
									'feedzy-rss-feeds'
								)}
								initialOpen={false}
								className="feedzy-additional-options"
							>
								<TextControl
									label={__(
										'Wrap custom class',
										'feedzy-rss-feeds'
									)}
									value={this.props.attributes.className}
									onChange={this.props.edit.onclassName}
								/>

								<SelectControl
									label={__('Dry run?', 'feedzy-rss-feeds')}
									value={this.props.attributes._dryrun_}
									options={[
										{
											label: __('No', 'feedzy-rss-feeds'),
											value: 'no',
										},
										{
											label: __(
												'Yes',
												'feedzy-rss-feeds'
											),
											value: 'yes',
										},
									]}
									onChange={this.props.edit.onDryRun}
								/>

								<TextControl
									label={__(
										'Dry run tags',
										'feedzy-rss-feeds'
									)}
									value={this.props.attributes._dry_run_tags_}
									onChange={this.props.edit.onDryRunTags}
								/>

								<TextareaControl
									label={__(
										'Message to show when feed is empty',
										'feedzy-rss-feeds'
									)}
									value={this.props.attributes.error_empty}
									onChange={this.props.edit.onErrorEmpty}
								/>

								<SelectControl
									label={__(
										'Feed Caching Time',
										'feedzy-rss-feeds'
									)}
									value={this.props.attributes.refresh}
									options={refreshFeed}
									onChange={this.props.edit.onRefresh}
									className="feedzy-refresh"
								/>
							</PanelBody>
						</Fragment>
					)}
			</Fragment>
		);
	}
}

export default Inspector;

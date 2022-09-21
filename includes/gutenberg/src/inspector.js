// jshint ignore: start

/**
 * Block dependencies
 */
import RadioImageControl from './radio-image-control/';

/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;

const { applyFilters } = wp.hooks;

const {
	InspectorControls,
	MediaUpload,
} = wp.blockEditor || wp.editor;

const {
	Component,
	Fragment
} = wp.element;

const {
	BaseControl,
	ExternalLink,
	PanelBody,
	RangeControl,
	TextControl,
	Button,
	ToggleControl,
	SelectControl,
	ResponsiveWrapper,
	Dashicon
} = wp.components;

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
		let http_help = '';
		let refreshFeed = applyFilters('feedzy_widget_refresh_feed', [{ label: __('1 Hour'), value: '1_hours', }, { label: __('2 Hours'), value: '3_hours', }, { label: __('12 Hours'), value: '12_hours', }, { label: __('1 Day'), value: '1_days', }, { label: __('3 Days'), value: '3_days', }, { label: __('15 Days'), value: '15_days', }]);
		if (this.props.attributes.http === 'https') {
			http_help += __('Please verify that the images exist on HTTPS.');
		}

		return (
			<Fragment>
				<InspectorControls key="inspector">
					<PanelBody className="fz-section-header-panel">
						<Button
							className={classnames(
								'header-tab',
								{ 'is-selected': 'content' === this.state.tab }
							)}
							onClick={() => this.setState({ tab: 'content' })}
						>
							<span>
								<Dashicon icon="editor-table" />
								{__('Content')}
							</span>
						</Button>
						<Button
							className={classnames(
								'header-tab',
								{ 'is-selected': 'style' === this.state.tab }
							)}
							onClick={() => this.setState({ tab: 'style' })}
						>
							<span>
								<Dashicon icon="admin-customizer" />
								{__('Style')}
							</span>
						</Button>
						<Button
							className={classnames(
								'header-tab',
								{ 'is-selected': 'advanced' === this.state.tab }
							)}
							onClick={() => this.setState({ tab: 'advanced' })}
						>
							<span>
								<Dashicon icon="admin-generic" />
								{__('Advanced')}
							</span>
						</Button>
					</PanelBody>
					{('content' === this.state.tab) && (
						<Fragment>
							<PanelBody
								title={__('Feed Source')}
								initialOpen={true}
							>
								{(this.props.attributes.status !== 0) && [
									<TextControl
										label={__('Feed Source')}
										className="feedzy-source"
										value={this.props.attributes.feeds}
										onChange={this.props.edit.onChangeFeed}
									/>,
									<Button
										isLarge
										isPrimary
										type="submit"
										onClick={this.props.edit.loadFeed}
										className="loadFeed"
									>
										{__('Load Feed')}
									</Button>
								]}
								{('fetched' === this.props.state.route) && [
									<RangeControl
										label={__('Number of Items')}
										value={Number(this.props.attributes.max) || 5}
										onChange={this.props.edit.onChangeMax}
										min={1}
										max={this.props.attributes.feedData['items'].length || 10}
										beforeIcon="sort"
										className="feedzy-max"
									/>,
									<SelectControl
										label={__('Sorting Order')}
										value={this.props.attributes.sort}
										options={[
											{
												label: __('Default'),
												value: 'default',
											},
											{
												label: __('Date Descending'),
												value: 'date_desc',
											},
											{
												label: __('Date Ascending'),
												value: 'date_asc',
											},
											{
												label: __('Title Descending'),
												value: 'title_desc',
											},
											{
												label: __('Title Ascending'),
												value: 'title_asc',
											},
										]}
										onChange={this.props.edit.onSort}
										className="feedzy-sort"
									/>,
									<SelectControl
										label={__('Feed Caching Time')}
										value={this.props.attributes.refresh}
										options={refreshFeed}
										onChange={this.props.edit.onRefresh}
										className="feedzy-refresh"
									/>
								]}
							</PanelBody>

							<PanelBody title={__('Item Options')} initialOpen={false} className='feedzy-item-options'>
								<SelectControl
									label={__('Open Links In')}
									value={this.props.attributes.target}
									options={[
										{
											label: __('New Tab'),
											value: '_blank',
										},
										{
											label: __('Same Tab'),
											value: '_self',
										},
									]}
									onChange={this.props.edit.onTarget}
								/>

								<ToggleControl
									label={__('Display item Title')}
									checked={!!this.props.attributes.itemTitle}
									onChange={this.props.edit.onToggleItemTitle}
									className="feedzy-summary"
								/>

								{(this.props.attributes.itemTitle) && (
									<TextControl
										label={__('Title Character Limit')}
										help={__('Leave empty to show full title. A value of 0 will remove the title.')}
										type="number"
										value={this.props.attributes.title}
										onChange={this.props.edit.onTitle}
										className="feedzy-title-length"
									/>
								)}

								<ToggleControl
									label={__('Display post description?')}
									checked={!!this.props.attributes.summary}
									onChange={this.props.edit.onToggleSummary}
									className="feedzy-summary"
								/>

								{(this.props.attributes.summary) && (
									<TextControl
										label={__('Description Character Limit')}
										help={__('Leave empty to show full description.')}
										type="number"
										value={this.props.attributes.summarylength}
										onChange={this.props.edit.onSummaryLength}
										className="feedzy-summary-length"
										min={0}
									/>
								)}
							</PanelBody>

							<PanelBody
								title={
									[
										__('Filter items'),
										!feedzyjs.isPro && <span className="fz-pro-label">Pro</span>
									]
								}
								initialOpen={false}
								className={feedzyjs.isPro ? 'feedzy-item-filter' : 'feedzy-item-filter fz-locked'}
							>
								{!feedzyjs.isPro && <div className="fz-upsell-notice">{__('Unlock this feature and more advanced options with')} <ExternalLink href="https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/?utm_source=wpadmin&utm_medium=blockeditor&utm_campaign=keywordsfilter&utm_content=feedzy-rss-feeds">{__('Feedzy Pro')}</ExternalLink></div>}
								<TextControl
									label={__('Only display if selected field contains:')}
									help={__('Use comma(,) and plus(+) keyword')}
									value={this.props.attributes.keywords_title}
									onChange={this.props.edit.onKeywordsTitle}
									className="feedzy-include"
								/>
								<SelectControl
									label={__('Select a field if you want to inc keyword.')}
									value={this.props.attributes.keywords_inc_on}
									options={[
										{
											label: __('Title'),
											value: 'title',
										},
										{
											label: __('Author'),
											value: 'author',
										},
										{
											label: __('Description'),
											value: 'description',
										},
									]}
									onChange={this.props.edit.onKeywordsIncludeOn}
								/>
								<TextControl
									label={__('Exclude if selected field contains:')}
									help={__('Use comma(,) and plus(+) keyword')}
									value={this.props.attributes.keywords_ban}
									onChange={this.props.edit.onKeywordsBan}
									className="feedzy-ban"
								/>
								<SelectControl
									label={__('Select a field if you want to exc keyword.')}
									value={this.props.attributes.keywords_exc_on}
									options={[
										{
											label: __('Title'),
											value: 'title',
										},
										{
											label: __('Author'),
											value: 'author',
										},
										{
											label: __('Description'),
											value: 'description',
										},
									]}
									onChange={this.props.edit.onKeywordsExcludeOn}
								/>
								<p className="fz-main-label">{__('Filter feed item by date range.')}</p>
								<TextControl
									type='datetime-local'
									label={__('From:')}
									value={this.props.attributes.from_datetime}
									onChange={this.props.edit.onFromDateTime}
								/>
								<TextControl
									type='datetime-local'
									label={__('To:')}
									value={this.props.attributes.to_datetime}
									onChange={this.props.edit.onToDateTime}
								/>
							</PanelBody>
						</Fragment>
					)}

					{('fetched' === this.props.state.route && 'style' === this.state.tab) && [
						<Fragment>
							<PanelBody
								title={__('Item Image Options')}
								initialOpen={false}
								className='feedzy-image-options'
							>
								<SelectControl
									label={__('Display first image if available?')}
									value={this.props.attributes.thumb}
									options={[
										{
											label: __('Yes (without  a fallback image)'),
											value: 'auto',
										},
										{
											label: __('Yes (with a fallback image)'),
											value: 'yes',
										},
										{
											label: __('No'),
											value: 'no',
										},
									]}
									onChange={this.props.edit.onThumb}
									className="feedzy-thumb"
								/>

								{(this.props.attributes.thumb !== 'no') && [
									(this.props.attributes.thumb !== 'auto') && (
										<div className="feedzy-blocks-base-control">
											<label className="blocks-base-control__label" for="inspector-media-upload">{__('Fallback image if no image is found.')}</label>
											<MediaUpload
												type="image"
												id="inspector-media-upload"
												value={this.props.attributes.default}
												onSelect={this.props.edit.onDefault}
												render={({ open }) => [
													(this.props.attributes.default !== undefined) && [
														<ResponsiveWrapper
															naturalWidth={this.props.attributes.default.width}
															naturalHeight={this.props.attributes.default.height}
														>
															<img src={this.props.attributes.default.url} alt={__('Featured image')} />
														</ResponsiveWrapper>,
														<Button
															isLarge
															isSecondary
															onClick={() => this.props.setAttributes({ default: undefined })}
															style={{ marginTop: '10px' }}
														>
															{__('Remove Image')}
														</Button>
													],
													<Button
														isLarge
														isPrimary
														onClick={open}
														style={{ marginTop: '10px' }}
														className={(this.props.attributes.default === undefined) && 'feedzy_image_upload'}
													>
														{__('Upload Image')}
													</Button>
												]}
											/>
										</div>
									),
									<TextControl
										label={__('Thumbnails dimension.')}
										type="number"
										value={this.props.attributes.size}
										onChange={this.props.edit.onSize}
									/>,
									<SelectControl
										label={__('How should we treat HTTP images?')}
										value={this.props.attributes.http}
										options={[
											{
												label: __('Show with HTTP link'),
												value: 'auto',
											},
											{
												label: __('Force HTTPS'),
												value: 'https',
											},
											{
												label: __('Ignore and show the default image instead'),
												value: 'default',
											},
										]}
										onChange={this.props.edit.onHTTP}
										className="feedzy-http"
										help={http_help}
									/>
								]}
							</PanelBody>

							<PanelBody
								title={
									[
										__('Feed Layout'),
										!feedzyjs.isPro && <span className="fz-pro-label">Pro</span>
									]
								}
								initialOpen={false}
								className={feedzyjs.isPro ? 'feedzy-layout' : 'feedzy-layout fz-locked'}
							>
								{!feedzyjs.isPro && <div className="fz-upsell-notice">{__('Unlock this feature and more advanced options with')} <ExternalLink href="https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/?utm_source=wpadmin&utm_medium=blockeditor&utm_campaign=layouts&utm_content=feedzy-rss-feeds">{__('Feedzy Pro')}</ExternalLink></div>}

								<RangeControl
									label={__('Columns')}
									help={__('How many columns we should use to display the feed items?')}
									value={this.props.attributes.columns || 1}
									onChange={this.props.edit.onColumns}
									min={1}
									max={6}
									beforeIcon="sort"
									allowReset
								/>

								<RadioImageControl
									label={__('Template')}
									selected={this.props.attributes.template}
									options={[
										{
											label: __('Default'),
											src: feedzyjs.imagepath + 'feedzy-default-template.png',
											value: 'default',
										},
										{
											label: __('Round'),
											src: feedzyjs.imagepath + 'feedzy-style1-template.png',
											value: 'style1',
										},
										{
											label: __('Cards'),
											src: feedzyjs.imagepath + 'feedzy-style2-template.png',
											value: 'style2',
										},
									]}
									onChange={this.props.edit.onTemplate}
								/>
							</PanelBody>
						</Fragment>
					]}
					{('fetched' === this.props.state.route && 'advanced' === this.state.tab) && [
						<Fragment>
							<PanelBody
								title={__('Feed Items Custom Options')}
								className='feedzy-advanced-options'
								initialOpen={false}
							>
								<BaseControl>
									<TextControl
										label={feedzyjs.isPro ? __('Should we display additional meta fields out of author, date, time or categories? (comma-separated list, in order of display).') : __('Should we display additional meta fields out of author, date or time? (comma-separated list, in order of display).')}
										help={__('Leave empty to display all and "no" to display nothing.')}
										placeholder={feedzyjs.isPro ? __('(eg: author, date, time, tz=local, categories)') : __('(eg: author, date, time, tz=local)')}
										value={this.props.attributes.metafields}
										onChange={this.props.edit.onChangeMeta}
										className="feedzy-meta"
									/>
									<TextControl
										label={__('When using multiple sources, should we display additional meta fields? - source (comma-separated list).')}
										placeholder={__('(eg: source)')}
										value={this.props.attributes.multiple_meta}
										onChange={this.props.edit.onChangeMultipleMeta}
										className="feedzy-multiple-meta"
									/>

									<ExternalLink href="https://docs.themeisle.com/article/1089-how-to-display-author-date-or-time-from-the-feed">
										{__('You can find more info about available meta field values here.')}
									</ExternalLink>
								</BaseControl>

								<ToggleControl
									label={__('Display price if available?')}
									help={(this.props.attributes.price && this.props.attributes.template === 'default') ? __('Choose a different template for this to work.') : null}
									checked={!!this.props.attributes.price}
									onChange={this.props.edit.onTogglePrice}
									className={feedzyjs.isPro ? 'feedzy-pro-price' : 'feedzy-pro-price fz-locked'}
								/>

								{((this.props.attributes.feedData['channel'] !== null)) && (
									<ToggleControl
										label={__('Display feed title?')}
										checked={!!this.props.attributes.feed_title}
										onChange={this.props.edit.onToggleFeedTitle}
										className="feedzy-title"
									/>
								)}

								<RangeControl
									label={__('Ignore first N items')}
									value={Number(this.props.attributes.offset) || 0}
									onChange={this.props.edit.onChangeOffset}
									min={0}
									max={this.props.attributes.feedData['items'].length}
									beforeIcon="sort"
									className="feedzy-offset"
								/>

								<ToggleControl
									label={__('Lazy load feed?')}
									checked={!!this.props.attributes.lazy}
									onChange={this.props.edit.onToggleLazy}
									className="feedzy-lazy"
									help={__('Only on the front end.')}
								/>

							</PanelBody>

							<PanelBody
								title={
									[
										__('Referral URL'),
										!feedzyjs.isPro && <span className="fz-pro-label">Pro</span>
									]
								}
								initialOpen={false}
								className={feedzyjs.isPro ? 'feedzy-pro-options' : 'feedzy-pro-options fz-locked'}
							>
								{!feedzyjs.isPro && <div className="fz-upsell-notice">{__('Unlock this feature and more advanced options with')} <ExternalLink href="https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/?utm_source=wpadmin&utm_medium=blockeditor&utm_campaign=refferal&utm_content=feedzy-rss-feeds">{__('Feedzy Pro')}</ExternalLink></div>}
								<TextControl
									label={__('Referral URL parameters.')}
									help={__('Without ("?")')}
									placeholder={_('(eg. promo_code=feedzy_is_awesome)')}
									value={this.props.attributes.referral_url}
									onChange={this.props.edit.onReferralURL}
								/>
							</PanelBody>
						</Fragment>
					]}
				</InspectorControls>
			</Fragment>
		)
	}
}

export default Inspector;

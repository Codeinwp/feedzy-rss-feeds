/**
 * WordPress dependencies
 */
import {
	ExternalLink,
	Placeholder,
	TextControl,
	Button,
	Spinner,
} from '@wordpress/components';

import queryString from 'query-string';
import Inspector from './inspector';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { Component, Fragment } from '@wordpress/element';
import {
	unescapeHTML,
	filterData,
	inArray,
	arrangeMeta,
	filterCustomPattern,
} from './utils';

class Editor extends Component {
	/*eslint-env es6*/
	constructor() {
		super(...arguments);

		this.loadFeed = this.loadFeed.bind(this);
		this.loadCategories = this.loadCategories.bind(this);
		this.metaExists = this.metaExists.bind(this);
		this.multipleMetaExists = this.multipleMetaExists.bind(this);
		this.getImageURL = this.getImageURL.bind(this);
		this.getValidateURL = this.getValidateURL.bind(this);

		this.onChangeFeed = this.onChangeFeed.bind(this);
		this.onChangeMax = this.onChangeMax.bind(this);
		this.onChangeOffset = this.onChangeOffset.bind(this);
		this.onToggleFeedTitle = this.onToggleFeedTitle.bind(this);
		this.onRefresh = this.onRefresh.bind(this);
		this.onSort = this.onSort.bind(this);
		this.onTarget = this.onTarget.bind(this);
		this.onTitle = this.onTitle.bind(this);
		this.onChangeMeta = this.onChangeMeta.bind(this);
		this.onChangeMultipleMeta = this.onChangeMultipleMeta.bind(this);
		this.onToggleSummary = this.onToggleSummary.bind(this);
		this.onToggleLazy = this.onToggleLazy.bind(this);
		this.onSummaryLength = this.onSummaryLength.bind(this);
		this.onKeywordsTitle = this.onKeywordsTitle.bind(this);
		this.onKeywordsBan = this.onKeywordsBan.bind(this);
		this.onThumb = this.onThumb.bind(this);
		this.onDefault = this.onDefault.bind(this);
		this.onSize = this.onSize.bind(this);
		this.onReferralURL = this.onReferralURL.bind(this);
		this.onColumns = this.onColumns.bind(this);
		this.onTemplate = this.onTemplate.bind(this);
		this.onTogglePrice = this.onTogglePrice.bind(this);
		this.onKeywordsIncludeOn = this.onKeywordsIncludeOn.bind(this);
		this.onKeywordsExcludeOn = this.onKeywordsExcludeOn.bind(this);
		this.onFromDateTime = this.onFromDateTime.bind(this);
		this.onToDateTime = this.onToDateTime.bind(this);
		this.feedzyCategoriesList = this.feedzyCategoriesList.bind(this);
		this.onToggleItemTitle = this.onToggleItemTitle.bind(this);
		this.onToggleDisableStyle = this.onToggleDisableStyle.bind(this);
		this.handleKeyUp = this.handleKeyUp.bind(this);
		this.handleKeyUp = this.handleKeyUp.bind(this);
		this.onLinkNoFollow = this.onLinkNoFollow.bind(this);
		this.onErrorEmpty = this.onErrorEmpty.bind(this);
		this.onclassName = this.onclassName.bind(this);
		this.onDryRun = this.onDryRun.bind(this);
		this.onDryRunTags = this.onDryRunTags.bind(this);
		this.state = {
			// home: when the block is just added
			// fetched: when the feed is fetched
			// reload: when the feed needs to be refetched
			route: this.props.attributes.route,
			loading: false,
			error: false,
		};
	}

	async componentDidMount() {
		this.loadFeed();

		if (this.props.attributes.categories === undefined) {
			if (!this.props.attributes.meta) {
				this.props.setAttributes({
					meta: true,
					metafields: 'no',
				});
			}
			setTimeout(() => {
				this.loadCategories();
			});
		}
	}

	async componentDidUpdate(prevProps) {
		if ('reload' === this.state.route) {
			this.loadFeed();
		}
	}

	loadFeed() {
		let url = this.props.attributes.feeds;
		if (url === undefined) {
			return;
		}

		if (inArray(url, this.props.attributes.categories)) {
			const category = url;
			url = queryString.stringify(
				{ category },
				{ arrayFormat: 'bracket' }
			);
		} else {
			url = url
				.replace(/\s/g, '')
				.split(',')
				.filter((item) => item !== '');
			url = queryString.stringify({ url }, { arrayFormat: 'bracket' });
		}

		this.setState({
			route: 'home',
			loading: true,
		});

		apiFetch({
			path: `/feedzy/v1/feed?${url}`,
			method: 'POST',
			data: this.props.attributes,
		})
			.then((data) => {
				if (this.unmounting) {
					return data;
				}
				if (!data.error) {
					this.props.setAttributes({ feedData: data });
					this.setState({
						route: 'fetched',
						loading: false,
					});
					return data;
				}
				this.setState({
					route: 'home',
					loading: false,
					error: true,
				});
				return data;
			})
			.catch((err) => {
				this.setState({
					route: 'home',
					loading: false,
					error: true,
				});
				return err;
			});
	}

	loadCategories() {
		apiFetch({ path: '/wp/v2/feedzy_categories?per_page=100' })
			.then((data) => {
				if (this.unmounting) {
					return data;
				}
				let i = 0;
				const categories = [];
				data.forEach((item) => {
					categories[i] = item.slug;
					i = i + 1;
				});
				const _this = this;
				_this.props.setAttributes({ categories });
				const editorCanvas = window.jQuery(
					'iframe[name="editor-canvas"]'
				);
				let $target = window.jQuery('.feedzy-source input');
				if (editorCanvas.length > 0) {
					$target = window.jQuery(
						'.feedzy-source input',
						editorCanvas.contents()
					);
				}
				$target.autocomplete({ disabled: false }).autocomplete({
					classes: {
						'ui-autocomplete': 'feedzy-ui-autocomplete',
					},
					source: categories,
					minLength: 0,
					select(event, ui) {
						_this.props.setAttributes({ feeds: ui.item.label });
					},
				});
			})
			.catch((err) => {
				return err;
			});
	}

	metaExists(value) {
		return (
			0 <=
				this.props.attributes.metafields
					.replace(/\s/g, '')
					.split(',')
					.indexOf(value) || '' === this.props.attributes.metafields
		);
	}

	multipleMetaExists(value) {
		return (
			0 <=
				this.props.attributes.multiple_meta
					.replace(/\s/g, '')
					.split(',')
					.indexOf(value) ||
			'' === this.props.attributes.multiple_meta
		);
	}

	getImageURL(item, background) {
		let url;
		if (item.thumbnail) {
			url = item.thumbnail;
		} else if (this.props.attributes.default) {
			url = this.props.attributes.default.url;
		} else {
			url = window.feedzyjs.imagepath + 'feedzy.svg';
		}

		url = url.replace(/http:/g, 'https:');

		if (background) {
			url = 'url("' + url + '")';
		}
		return url;
	}

	onChangeFeed(value) {
		window.tiTrk?.with('feedzy').set(`feedzy-url-feed`, {
			feature: 'block-url-feed',
			featureValue: value,
		});
		this.props.setAttributes({ feeds: value });
	}
	onChangeMax(value) {
		this.props.setAttributes({ max: !value ? 5 : Number(value) });
	}
	onChangeOffset(value) {
		this.props.setAttributes({ offset: Number(value) });
	}
	toggleFeedTitle() {
		this.props.setAttributes({
			feed_title: !this.props.attributes.feed_title,
		});
	}
	onRefresh(value) {
		window.tiTrk?.with('feedzy').set(`feedzy-caching`, {
			feature: 'block-caching-refresh',
			featureValue: value,
		});
		this.props.setAttributes({ refresh: value });
	}
	onSort(value) {
		this.props.setAttributes({ sort: value });
	}
	onTarget(value) {
		this.props.setAttributes({ target: value });
	}
	onTitle(value) {
		if ('' !== value) {
			value = Number(value);
			if (value < 0) {
				value = 0;
			}
		}
		this.props.setAttributes({ title: value });
	}
	onChangeMeta(value) {
		window.tiTrk?.with('feedzy').set(`feedzy-meta`, {
			feature: 'block-meta-fields',
			featureValue: value,
		});
		this.props.setAttributes({ metafields: value });
	}
	onChangeMultipleMeta(value) {
		window.tiTrk?.with('feedzy').set(`feedzy-multiple-meta`, {
			feature: 'block-multiple-meta-fields',
			featureValue: value,
		});
		this.props.setAttributes({ multiple_meta: value });
	}
	toggleSummary() {
		this.props.setAttributes({ summary: !this.props.attributes.summary });
	}
	onToggleLazy(value) {
		window.tiTrk?.with('feedzy').set(`feedzy-lazy-loading`, {
			feature: 'block-lazy-loading-feed',
			featureValue: value,
		});
		this.props.setAttributes({ lazy: !this.props.attributes.lazy });
	}
	onSummaryLength(value) {
		this.props.setAttributes({ summarylength: Number(value) });
	}
	onKeywordsTitle(value) {
		this.props.setAttributes({ keywords_title: value });
	}
	onKeywordsBan(value) {
		this.props.setAttributes({ keywords_ban: value });
	}
	onThumb(value) {
		this.props.setAttributes({ thumb: value });
	}
	onDefault(value) {
		this.props.setAttributes({ default: value });
		this.setState({
			route: 'reload',
		});
	}
	onSize(value) {
		this.props.setAttributes({ size: !value ? 150 : Number(value) });
	}
	onReferralURL(value) {
		window.tiTrk?.with('feedzy').add({ feature: 'block-referral-url' });
		this.props.setAttributes({ referral_url: value });
	}
	onColumns(value) {
		window.tiTrk?.with('feedzy').set(`feedzy-columns`, {
			feature: 'block-columns',
			featureValue: value,
		});
		this.props.setAttributes({ columns: value });
	}
	onTemplate(value) {
		window.tiTrk?.with('feedzy').set(`feedzy-template`, {
			feature: 'block-template',
			featureValue: value,
		});
		this.props.setAttributes({ template: value });
	}
	togglePrice() {
		window.tiTrk?.with('feedzy').set(`feedzy-price`, {
			feature: 'block-price',
			featureValue: !this.props.attributes.price,
		});
		this.props.setAttributes({ price: !this.props.attributes.price });
	}
	onKeywordsIncludeOn(value) {
		this.props.setAttributes({ keywords_inc_on: value });
	}
	onKeywordsExcludeOn(value) {
		this.props.setAttributes({ keywords_exc_on: value });
	}
	onFromDateTime(value) {
		this.props.setAttributes({ from_datetime: value });
	}
	onToDateTime(value) {
		this.props.setAttributes({ to_datetime: value });
	}
	feedzyCategoriesList() {
		const editorCanvas = window.jQuery('iframe[name="editor-canvas"]');
		let $target = window.jQuery('.feedzy-source input');
		if (editorCanvas.length > 0) {
			$target = window.jQuery(
				'.feedzy-source input',
				editorCanvas.contents()
			);
		}
		$target.autocomplete({ disabled: false }).autocomplete('search', '');
	}
	getValidateURL() {
		let url = 'https://validator.w3.org/feed/';
		if (this.props.attributes.feeds) {
			url += 'check.cgi?url=' + this.props.attributes.feeds;
		}
		return url;
	}
	onToggleItemTitle() {
		this.props.setAttributes({
			itemTitle: !this.props.attributes.itemTitle,
		});
	}
	onToggleDisableStyle() {
		this.props.setAttributes({
			disableStyle: !this.props.attributes.disableStyle,
		});
	}
	onLinkNoFollow(value) {
		this.props.setAttributes({ follow: value });
	}
	onErrorEmpty(value) {
		this.props.setAttributes({ error_empty: value });
	}
	onclassName(value) {
		this.props.setAttributes({ className: value });
	}
	onDryRun(value) {
		window.tiTrk?.with('feedzy').add({ feature: 'block-dry-run' });
		this.props.setAttributes({ _dryrun_: value });
	}
	onDryRunTags(value) {
		window.tiTrk?.with('feedzy').set(`feedzy-dry-run-tags`, {
			feature: 'block-dry-run-tags',
			featureValue: value,
		});
		this.props.setAttributes({ _dry_run_tags_: value });
	}

	handleKeyUp(event) {
		if (13 === event.keyCode) {
			this.loadFeed();
		}
	}

	render() {
		return (
			<Fragment>
				{'fetched' === this.state.route && (
					<Inspector edit={this} state={this.state} {...this.props} />
				)}
				{'home' === this.state.route && (
					<div className={this.props.className}>
						<Placeholder
							key="placeholder"
							icon="rss"
							label={__('Feedzy RSS Feeds', 'feedzy-rss-feeds')}
						>
							{this.state.loading ? (
								<div
									key="loading"
									className="wp-block-embed is-loading"
								>
									<Spinner />
									<p>{__('Fetching…', 'feedzy-rss-feeds')}</p>
								</div>
							) : (
								<Fragment>
									<div className="feedzy-source-wrap">
										<TextControl
											type="url"
											className="feedzy-source"
											placeholder={__(
												'Enter URL or group of your feed here…',
												'feedzy-rss-feeds'
											)}
											onChange={this.onChangeFeed}
											onKeyUp={this.handleKeyUp}
											value={this.props.attributes.feeds}
										/>
										<button
											className="dashicons dashicons-arrow-down-alt2"
											onClick={this.feedzyCategoriesList}
											onKeyDown={(e) => {
												if (
													e.key === 'Enter' ||
													e.key === ' '
												) {
													this.feedzyCategoriesList();
												}
											}}
											aria-label="Show categories list"
										></button>
									</div>
									<Button
										isLarge
										isPrimary
										type="submit"
										onClick={this.loadFeed}
									>
										{__('Load Feed', 'feedzy-rss-feeds')}
									</Button>
									<ExternalLink
										href={this.getValidateURL()}
										title={__(
											'Validate Feed',
											'feedzy-rss-feeds'
										)}
									>
										{__('Validate', 'feedzy-rss-feeds')}
									</ExternalLink>

									{this.state.error && (
										<div>
											{__(
												'Feed URL is invalid or unreachable by WordPress SimplePie and will NOT display items.',
												'feedzy-rss-feeds'
											)}
										</div>
									)}
									<p>
										{__(
											"Enter the full URL of the feed source you wish to display here, or the name of a group you've created. Also you can add multiple URLs just separate them with a comma. You can manage your groups feed from",
											'feedzy-rss-feeds'
										)}{' '}
										<a
											href="edit.php?post_type=feedzy_categories"
											title={__(
												'Feedzy Groups',
												'feedzy-rss-feeds'
											)}
											target="_blank"
										>
											{__('here', 'feedzy-rss-feeds')}
										</a>
									</p>
								</Fragment>
							)}
						</Placeholder>
					</div>
				)}
				{!!(
					'fetched' === this.state.route &&
					this.props.attributes.feedData !== undefined
				) && (
					<div className="feedzy-rss">
						{this.props.attributes.feed_title &&
							this.props.attributes.feedData.channel !== null && (
								<div className="rss_header">
									<h2>
										<a
											className="rss_title"
											href={
												this.props.attributes.feedData
													.channel.link || '#'
											}
										>
											{unescapeHTML(
												this.props.attributes.feedData
													.channel.title
											)}
										</a>
										<span className="rss_description">
											{' ' +
												unescapeHTML(
													this.props.attributes
														.feedData.channel
														.description
												)}
										</span>
									</h2>
								</div>
							)}
						<ul
							className={`feedzy-${this.props.attributes.template}`}
						>
							{filterData(
								this.props.attributes.feedData.items,
								this.props.attributes.sort,
								filterCustomPattern(
									this.props.attributes.keywords_title
								),
								filterCustomPattern(
									this.props.attributes.keywords_ban
								),
								this.props.attributes.max,
								this.props.attributes.offset,
								this.props.attributes.keywords_inc_on,
								this.props.attributes.keywords_exc_on,
								this.props.attributes.from_datetime,
								this.props.attributes.to_datetime
							).map((item, i) => {
								const itemDateTime =
									(item.date || '') +
									' ' +
									(item.time || '') +
									' UTC +0000';
								let itemDate = unescapeHTML(item.date) || '';
								let itemTime = unescapeHTML(item.time) || '';
								const categories =
									unescapeHTML(item.categories) || '';
								if (this.metaExists('tz=local')) {
									let itemDateTimeObj = new Date(
										itemDateTime
									);
									itemDateTimeObj =
										itemDateTimeObj.toUTCString();
									itemDate = window.moment
										.utc(itemDateTimeObj)
										.format('MMMM D, YYYY');
									itemTime = window.moment
										.utc(itemDateTimeObj)
										.format('h:mm A');
								}

								let author =
									item.creator && this.metaExists('author')
										? item.creator
										: '';
								if (
									this.props.attributes.multiple_meta !==
										'' &&
									this.props.attributes.multiple_meta !== 'no'
								) {
									if (
										(this.multipleMetaExists('source') ||
											this.multipleMetaExists('yes')) &&
										author !== '' &&
										item.source !== ''
									) {
										author =
											author + ' (' + item.source + ')';
									} else if (
										(this.multipleMetaExists('source') ||
											this.multipleMetaExists('yes')) &&
										item.source !== ''
									) {
										author = item.source;
									}
								}

								if (
									item.thumbnail === '' &&
									this.props.attributes.thumb === 'auto' &&
									!item.default_img?.endsWith(
										'img/feedzy.svg'
									)
								) {
									item.thumbnail = item.default_img;
								}

								const metaValues = new Object();
								metaValues.author =
									__('by', 'feedzy-rss-feeds') + ' ' + author;
								metaValues.date = sprintf(
									// translators: %s: the date of the imported content.
									__('on %s', 'feedzy-rss-feeds'),
									unescapeHTML(itemDate)
								);
								metaValues.time = sprintf(
									// translators: %s: the time of the imported content.
									__('at %s', 'feedzy-rss-feeds'),
									unescapeHTML(itemTime)
								);
								metaValues.categories = sprintf(
									// translators: %s: the category of the imported content.
									__('in %s', 'feedzy-rss-feeds'),
									unescapeHTML(categories)
								);

								return (
									<li
										key={i}
										style={{ padding: '15px 0 25px' }}
										className={`rss_item feedzy-rss-col-${this.props.attributes.columns}`}
									>
										{((item.thumbnail &&
											this.props.attributes.thumb ===
												'auto') ||
											this.props.attributes.thumb ===
												'yes') && (
											<div
												className="rss_image"
												style={{
													width:
														this.props.attributes
															.size + 'px',
													height:
														this.props.attributes
															.size + 'px',
												}}
											>
												<a
													href={item.link}
													title={unescapeHTML(
														item.title
													)}
													style={{
														width:
															this.props
																.attributes
																.size + 'px',
														height:
															this.props
																.attributes
																.size + 'px',
													}}
												>
													<span
														className="fetched"
														style={{
															width:
																this.props
																	.attributes
																	.size +
																'px',
															height:
																this.props
																	.attributes
																	.size +
																'px',
															backgroundImage:
																this.getImageURL(
																	item,
																	true
																),
														}}
														title={unescapeHTML(
															item.title
														)}
													></span>
												</a>
											</div>
										)}
										<div className="rss_content_wrap">
											{this.props.attributes.itemTitle &&
											this.props.attributes.title !==
												0 ? (
												<span className="title">
													<a href={item.link}>
														{this.props.attributes
															.title &&
														unescapeHTML(item.title)
															.length >
															this.props
																.attributes
																.title
															? unescapeHTML(
																	item.title
																).substring(
																	0,
																	this.props
																		.attributes
																		.title
																) + '...'
															: unescapeHTML(
																	item.title
																)}
													</a>
												</span>
											) : (
												''
											)}
											<div className="rss_content">
												{this.props.attributes
													.metafields !== 'no' && (
													<small className="meta">
														{arrangeMeta(
															metaValues,
															this.props
																.attributes
																.metafields
														)}
													</small>
												)}
												{this.props.attributes
													.summary && (
													<p className="description">
														{this.props.attributes
															.summarylength &&
														unescapeHTML(
															item.description
														).length >
															this.props
																.attributes
																.summarylength
															? unescapeHTML(
																	item.description
																).substring(
																	0,
																	this.props
																		.attributes
																		.summarylength
																) + ' [...]'
															: unescapeHTML(
																	item.description
																)}
													</p>
												)}
												{window.feedzyjs.isPro &&
													item.media &&
													item.media.src && (
														<audio
															controls
															controlsList="nodownload"
														>
															<source
																src={
																	item.media
																		.src
																}
																type={
																	item.media
																		.type
																}
															/>
															{__(
																'Your browser does not support the audio element. But you can check this for the original link:',
																'feedzy-rss-feeds'
															)}{' '}
															<a
																href={
																	item.media
																		.src
																}
															>
																{item.media.src}
															</a>
														</audio>
													)}
												{window.feedzyjs.isPro &&
													this.props.attributes
														.price &&
													item.price &&
													this.props.attributes
														.template !==
														'default' && (
														<div className="price-wrap">
															<a>
																<button className="price">
																	{item.price}
																</button>
															</a>
														</div>
													)}
											</div>
										</div>
									</li>
								);
							})}
						</ul>
					</div>
				)}
			</Fragment>
		);
	}
}

export default Editor;

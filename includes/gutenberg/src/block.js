// jshint ignore: start

/**
 * Block dependencies
 */
import './style.scss';
import queryString from 'query-string';
import blockAttributes from './attributes';
import Inspector from './inspector';
import { unescapeHTML, filterData, inArray } from './utils';

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;

const { registerBlockType } = wp.blocks;

const {
	Placeholder,
	TextControl,
	Button,
	Spinner,
} = wp.components;

/**
 * Register block
 */
export default registerBlockType( 'feedzy-rss-feeds/feedzy-block', {
	title: __( 'Feedzy RSS Feeds' ),
	category: 'common',
	icon: 'rss',
	keywords: [
		__( 'Feedzy RSS Feeds' ),
		__( 'RSS' ),
		__( 'Feeds' ),
	],
	supports: {
		html: false,
	},
	attributes: blockAttributes,
	edit: props => {
		const onChangeFeeds = value => {
			props.setAttributes( { feeds: value } );
		};
		const onChangeMax = value => {
			props.setAttributes( { max: value.toString() } );
		};
		const toggleFeedTitle = value => {
			props.setAttributes( { feed_title: ! props.attributes.feed_title } );
		};
		const onRefresh = value => {
			props.setAttributes( { refresh: value } );
		};
		const onSort = value => {
			props.setAttributes( { sort: value } );
		};
		const onTarget = value => {
			props.setAttributes( { target: value } );
		};
		const onTitle = value => {
			props.setAttributes( { title: value } );
		};
		const toggleMeta = value => {
			props.setAttributes( { meta: ! props.attributes.meta } );
		};
		const toggleSummary = value => {
			props.setAttributes( { summary: ! props.attributes.summary } );
		};
		const onSummaryLength = value => {
			props.setAttributes( { summarylength: value } );
		};
		const onKeywordsTitle = value => {
			props.setAttributes( { keywords_title: value } );
		};
		const onKeywordsBan = value => {
			props.setAttributes( { keywords_ban: value } );
		};
		const onThumb = value => {
			props.setAttributes( { thumb: value } );
		};
		const onDefault = value => {
			props.setAttributes( { default: value } );
		};
		const onSize = value => {
			props.setAttributes( { size: value } );
		};
		const onReferralURL = value => {
			props.setAttributes( { referral_url: value } );
		};
		const onColumns = value => {
			props.setAttributes( { columns: value } );
		};
		const onTemplate = value => {
			props.setAttributes( { template: value } );
		};
		const togglePrice = value => {
			props.setAttributes( { price: ! props.attributes.price } );
		};
		const loadFeed = () => {
			props.setAttributes( { status: 1 } );

			let url = props.attributes.feeds;

			if ( url === undefined ) {
				return props.setAttributes( { status: 3 } );
			}

			if ( inArray( url, props.attributes.categories ) ) {
				let category = url;
				url = queryString.stringify( { category }, { arrayFormat: 'bracket' } );
			} else {
				url = url.
						replace( /\s/g, '' )
						.split( ',' )
						.filter( item => item !== '' );
				url = queryString.stringify( { url}, { arrayFormat: 'bracket' } );
			}

			wp.apiRequest( { path: `/feedzy/v1/feed?${ url }` } )
				.then(
					( data ) => {
						if ( this.unmounting ) {
							return data;
						}
						if ( ! data['error'] ){
							props.setAttributes( { feedData: data } );
							props.setAttributes( { status: 2 } );
							return data;
						} else {
							props.setAttributes( { status: 3 } );
							return data;
						}
					},
				).fail(
					err => {
						props.setAttributes( { status: 3 } );
						return err;
					}
				);
		};
		const loadCategories = () => {
			wp.apiRequest( { path: '/wp/v2/feedzy_categories' } )
				.then(
					( data ) => {
						if ( this.unmounting ) {
							return data;
						}
						let i = 0;
						let categories = [];
						data.forEach( item => {
							categories[i] = item.slug;
							i = i + 1;
						} );
						props.setAttributes( { categories: categories } );
						jQuery( '.feedzy-source input' ).autocomplete({
							source: categories,
							select: function( event, ui ) {
								props.setAttributes( { feeds: ui.item.label } );
							}
						});
					},
				).fail(
					err => {
						return err;
					}
				);
		};
		if ( props.attributes.categories === undefined ) {
			loadCategories();
		}
		return [
			// Inspector
			!! props.isSelected && (
				<Inspector 
					{ ...{ onChangeFeeds, onChangeMax, toggleFeedTitle, onRefresh, onSort, onTarget, onTitle, toggleMeta, toggleSummary, onSummaryLength, onKeywordsTitle, onKeywordsBan, onThumb, onDefault, onSize, onReferralURL, onColumns, onTemplate, togglePrice, loadFeed, ...props } }
				/>
			),
			props.attributes.status !== 2 && (
				<div className={ props.className }>
					<Placeholder
						key="placeholder"
						icon="rss"
						label={ __( 'Feedzy RSS Feeds' ) }
					>
					{ ( props.attributes.status === 1 ) ?
					(
						<div key="loading" className="wp-block-embed is-loading">
							<Spinner />
							<p>{ __( 'Fetching…' ) }</p>
						</div>
					):
					[
						( props.attributes.status === 3 ) &&  <span>{ __( 'Feed URL Invalid') }</span>,
						<TextControl
							type="url"
							className="feedzy-source"
							placeholder={ __( 'Enter URL or category of your feed here…' ) }
							onChange={ onChangeFeeds }
							value={ props.attributes.feeds }
						/>,
						<Button
							isLarge
							type="submit"
							onClick={ loadFeed }
						>
							{ __( 'Load Feed' ) }
						</Button>
					] }
					</Placeholder>
				</div>
			),
			!! ( props.attributes.status === 2 && props.attributes.feedData !== undefined ) && (
				<div className="feedzy-rss">
					{ ( ( props.attributes.feed_title ) && ( props.attributes.feedData['channel'] !== null ) ) && (
						<div className="rss_header">
							<h2>
								<a className="rss_title">
									{ unescapeHTML( props.attributes.feedData['channel']['title'] ) }
								</a>
								<span className="rss_description">
									{ ' ' + unescapeHTML( props.attributes.feedData['channel']['description'] ) }
								</span>
							</h2>
						</div>
					) }
					<ul className={ `feedzy-${ props.attributes.template }` }>
						{ filterData( props.attributes.feedData['items'], props.attributes.sort, props.attributes.keywords_title, props.attributes.keywords_ban, props.attributes.max ).map( ( item, i ) => {
							return (
								<li key={i} style={ { padding: '15px 0 25px' } } className={ `rss_item feedzy-rss-col-${ props.attributes.columns }` }>
									{ ( ( item['thumbnail'] && props.attributes.thumb === 'auto' ) || props.attributes.thumb === 'yes' ) && (
										<div className="rss_image" style={ { width: props.attributes.size + 'px', height: props.attributes.size + 'px' } }>
											<a title={ unescapeHTML( item['title'] ) } style={ { width: props.attributes.size + 'px', height: props.attributes.size + 'px' } }>
												<span className="fetched" style={ { backgroundImage: 'url(' + ( ( item['thumbnail'] ) ? item['thumbnail'] : ( ( props.attributes.default ) ? props.attributes.default.url : feedzyjs.imagepath + 'feedzy-default.jpg' ) ) + ')' } } title={ unescapeHTML( item['title'] ) }></span>
											</a>
										</div>
									) }
									<div className="rss_content_wrap">
										<span className="title">
											<a>
												{ ( props.attributes.title && unescapeHTML( item['title'] ).length > props.attributes.title ) ? (
													unescapeHTML( item['title'] ).substring( 0, props.attributes.title ) + '...'
												):
													unescapeHTML( item['title'] )
												}
											</a>
										</span>
										<div className="rss_content">
											{ ( props.attributes.meta ) && (
												<small className="meta">
													{ ( item['creator'] ) && [
														__( 'by' ),
														' ',
														<a>{ unescapeHTML( item['creator'] ) }</a>,
														' '
													] }
													{ __( 'on' ) } { unescapeHTML( item['date'] ) } { __( 'at' ) } { unescapeHTML( item['time'] ) }
												</small>
											) }
											{ ( props.attributes.summary ) && (
												<p className="description">
												{ ( props.attributes.summarylength && unescapeHTML( item['description'] ).length > props.attributes.summarylength ) ? (
													unescapeHTML( item['description'] ).substring( 0, props.attributes.summarylength ) + ' […]'
												):
													unescapeHTML( item['description'] )
												}
												</p>
											) }
											{ ( feedzyjs.isPro && item['media'] && item['media']['src'] ) && (
												<audio controls controlsList="nodownload">
													<source src={ item['media']['src'] }  type={ item['media']['type'] } />
													{ __( 'Your browser does not support the audio element. But you can check this for the original link: ' ) }
													<a href={ item['media']['src'] } >{ item['media']['src'] }</a>
												</audio>
											) }
											{ ( feedzyjs.isPro && props.attributes.price && item['price'] && props.attributes.template !== 'default' ) && (
												<div className="price-wrap">
													<a><button className="price">{ item['price'] }</button></a>
												</div>
											) }
										</div>
									</div>
								</li>
							)
						})}
					</ul>
				</div>
			)
		];
	},
	save() {
			// Rendering in PHP
			return null;
	},
});

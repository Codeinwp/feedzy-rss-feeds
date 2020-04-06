// jshint ignore: start

/**
 * Block dependencies
 */
import './style.scss';
import queryString from 'query-string';
import blockAttributes from './attributes';
import Inspector from './inspector';
import { unescapeHTML, filterData, inArray, arrangeMeta } from './utils';

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;

const { registerBlockType } = wp.blocks;

const {
	ExternalLink,
	Placeholder,
	TextControl,
	Button,
	Spinner,
} = wp.components;

const { date } = wp.date;

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
			props.setAttributes( { max: ! value ? 5 : Number( value ) } );
		};
		const onChangeOffset = value => {
			props.setAttributes( { offset: Number( value ) } );
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
			props.setAttributes( { title: Number( value ) } );
		};
		const changeMeta = value => {
			props.setAttributes( { metafields: value } );
		}
		const changeMultipleMeta = value => {
			props.setAttributes( { multiple_meta: value } );
		}
		const toggleSummary = value => {
			props.setAttributes( { summary: ! props.attributes.summary } );
		};
		const toggleLazy = value => {
			props.setAttributes( { lazy: ! props.attributes.lazy } );
		};
		const onSummaryLength = value => {
			props.setAttributes( { summarylength: Number( value ) } );
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
			props.setAttributes( { size: ! value ? 150 : Number( value ) } );
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
		const metaExists = value => {
			return ( 0 <= ( props.attributes.metafields.replace(/\s/g,'').split( ',' ) ).indexOf( value ) || '' === props.attributes.metafields );
		};
		const multipleMetaExists = value => {
			return ( 0 <= ( props.attributes.multiple_meta.replace(/\s/g,'').split( ',' ) ).indexOf( value ) || '' === props.attributes.multiple_meta );
		};
		if ( props.attributes.categories === undefined ) {
			if ( ! props.attributes.meta ) {
				props.setAttributes( {
					meta: true,
					metafields: 'no'
				} );
			}
			loadCategories();
		}
		return [
			// Inspector
			!! props.isSelected && (
				<Inspector 
					{ ...{ onChangeFeeds, onChangeMax, onChangeOffset, toggleFeedTitle, onRefresh, onSort, onTarget, onTitle, changeMeta, changeMultipleMeta, toggleSummary, toggleLazy, onSummaryLength, onKeywordsTitle, onKeywordsBan, onThumb, onDefault, onSize, onReferralURL, onColumns, onTemplate, togglePrice, loadFeed, ...props } }
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
						</Button>,
                        <ExternalLink href="https://validator.w3.org/feed/" title={ __( 'Validate Feed ' ) }>
                        </ExternalLink>,
						( props.attributes.status === 3 ) && <div>{ __( 'Feed URL is invalid. Invalid feeds will NOT display items.') }</div>
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
						{ filterData( props.attributes.feedData['items'], props.attributes.sort, props.attributes.keywords_title, props.attributes.keywords_ban, props.attributes.max, props.attributes.offset ).map( ( item, i ) => {
							const itemDateTime = ( item['date'] || '' ) + ' ' + ( item['time'] || '' ) + ' UTC +0000';
							let itemDate = unescapeHTML( item['date'] ) || '';
							let itemTime = unescapeHTML( item['time'] ) || '';
							let categories = unescapeHTML( item['categories'] ) || '';
							if ( metaExists( 'tz=local' ) ) {
								itemDate = date( 'F jS, \o', itemDateTime );
								itemTime = date( 'h:i A', itemDateTime );
							}

                            let author = item['creator'] && metaExists( 'author' ) ? item['creator'] : '';
                            if ( props.attributes.multiple_meta !== '' && props.attributes.multiple_meta !== 'no') {
                                if ( ( multipleMetaExists( 'source' ) || multipleMetaExists( 'yes' ) ) && author !== '' && item['source'] !== '' ) {
                                    author = author + ' (' + item['source'] + ')';
                                }
                            }

                            let meta_values = new Object();
                            meta_values['author'] = __( 'by' ) + ' ' + author;
                            meta_values['date'] = __( 'on' ) + ' ' + unescapeHTML( itemDate );
                            meta_values['time'] = __( 'at' ) + ' ' + unescapeHTML( itemTime );
                            meta_values['categories'] = __( 'in' ) + ' ' + unescapeHTML( categories );

							return (
								<li key={i} style={ { padding: '15px 0 25px' } } className={ `rss_item feedzy-rss-col-${ props.attributes.columns }` }>
									{ ( ( item['thumbnail'] && props.attributes.thumb === 'auto' ) || props.attributes.thumb === 'yes' ) && (
										<div className="rss_image" style={ { width: props.attributes.size + 'px', height: props.attributes.size + 'px' } }>
											<a title={ unescapeHTML( item['title'] ) } style={ { width: props.attributes.size + 'px', height: props.attributes.size + 'px' } }>
												<span className="fetched" style={ { width: props.attributes.size + 'px', height: props.attributes.size + 'px', backgroundImage: 'url(' + ( ( item['thumbnail'] ) ? item['thumbnail'] : ( ( props.attributes.default ) ? props.attributes.default.url : feedzyjs.imagepath + 'feedzy-default.jpg' ) ) + ')' } } title={ unescapeHTML( item['title'] ) }></span>
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
											{ ( props.attributes.metafields !== 'no' ) && (
												<small className="meta">
                                                { arrangeMeta( meta_values, props.attributes.metafields ) }
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

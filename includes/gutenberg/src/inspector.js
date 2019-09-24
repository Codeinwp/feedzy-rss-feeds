// jshint ignore: start

/**
 * Block dependencies
 */
import RadioImageControl from './radio-image-control/';

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;

const {
	InspectorControls,
	MediaUpload,
} = wp.editor;

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
} = wp.components;

/**
* Create an Inspector Controls wrapper Component
*/

const Inspector = props => {
    return (
		<InspectorControls key="inspector">
			{ ( props.attributes.status !== 0 ) && (
				<PanelBody>
					<TextControl
						label={ __( 'Feed Source' ) }
						className="feedzy-source"
						value={ props.attributes.feeds }
						onChange={ props.onChangeFeeds }
					/>

					<Button
						isLarge
						type="submit"
						onClick={ props.loadFeed }
						className="loadFeed"
					>
						{ __( 'Load Feed' ) }
					</Button>
				</PanelBody>
			) }

			{ ( props.attributes.status === 2 ) && [
				<PanelBody
					title={ __( 'Feed Settings' ) }
					initialOpen={ true }
				>
					<RangeControl
						label={ __( 'Number of Feeds' ) }
						value={ Number( props.attributes.max ) || 5 }
						onChange={ props.onChangeMax }
						min={ 1 }
						max={ props.attributes.feedData['items'].length || 10 }
						beforeIcon="sort"
					/>

					{ ( ( props.attributes.feedData['channel'] !== null ) ) && (
						<ToggleControl
							label={ __( 'Display feed title?' ) }
							checked={ !! props.attributes.feed_title }
							onChange={ props.toggleFeedTitle }
						/>
					) }

					<SelectControl
						label={ __( 'Feed Caching Time' ) }
						value={ props.attributes.refresh }
						options={ [
							{
								label: __( '1 Hour' ),
								value: '1_hours',
							},
							{
								label: __( '2 Hours' ),
								value: '3_hours',
							},
							{
								label: __( '12 Hours' ),
								value: '12_hours',
							},
							{
								label: __( '1 Day' ),
								value: '1_days',
							},
							{
								label: __( '3 Days' ),
								value: '3_days',
							},
							{
								label: __( '15 Days' ),
								value: '15_days',
							},
						] }
						onChange={ props.onRefresh }
					/>

					<SelectControl
						label={ __( 'Sorting Order' ) }
						value={ props.attributes.sort }
						options={ [
							{
								label: __( 'Default' ),
								value: 'default',
							},
							{
								label: __( 'Date Descending' ),
								value: 'date_desc',
							},
							{
								label: __( 'Date Ascending' ),
								value: 'date_asc',
							},
							{
								label: __( 'Title Descending' ),
								value: 'title_desc',
							},
							{
								label: __( 'Title Ascending' ),
								value: 'title_asc',
							},
						] }
						onChange={ props.onSort }
					/>
				</PanelBody>,
				<PanelBody title={ __( 'Item Options' ) } initialOpen={ false } >
					<SelectControl
						label={ __( 'Open Links In' ) }
						value={ props.attributes.target }
						options={ [
							{
								label: __( 'New Tab' ),
								value: '_blank',
							},
							{
								label: __( 'Same Tab' ),
								value: '_self',
							},
						] }
						onChange={ props.onTarget }
					/>

					<TextControl
						label={ __( 'Title Character Limit' ) }
						help={ __( 'Leave empty to show full title.' ) }
						type="number"
						value={ props.attributes.title }
						onChange={ props.onTitle }
					/>

					<BaseControl>
						<TextControl
							label={ feedzyjs.isPro ? __( 'Should we display additional meta fields out of author, date, time or categories? (comma-separated list).' ) : __( 'Should we display additional meta fields out of author, date or time? (comma-separated list).' ) }
							placeholder={ feedzyjs.isPro ? __( '(eg: author, date, time, tz=local or categories)' ) : __( '(eg: author, date, time, tz=local)' ) }
							value={ props.attributes.metafields }
							onChange={ props.changeMeta }
						/>

						<ExternalLink href="https://docs.themeisle.com/article/1089-how-to-display-author-date-or-time-from-the-feed">
							{ __( 'You can find more info about available meta field values here.' ) }
						</ExternalLink>
					</BaseControl>

					<ToggleControl
						label={ __( 'Display post description?' ) }
						checked={ !! props.attributes.summary }
						onChange={ props.toggleSummary }
					/>

					{ ( props.attributes.summary ) && (
						<TextControl
							label={ __( 'Description Character Limit' ) }
							help={ __( 'Leave empty to show full description.' ) }
							type="number"
							value={ props.attributes.summarylength }
							onChange={ props.onSummaryLength }
						/>
					) }

					{ ( ( feedzyjs.isPro ) && [
						<TextControl
							label={ __( 'Only display if title contains:' ) }
							help={ __( 'Comma-separated list/case sensitive.' ) }
							value={ props.attributes.keywords_title }
							onChange={ props.onKeywordsTitle }
						/>,
						<TextControl
							label={ __( 'Exclude if title contains:' ) }
							help={ __( 'Comma-separated list/case sensitive.' ) }
							value={ props.attributes.keywords_ban }
							onChange={ props.onKeywordsBan }
						/>
					 ] ) }
				</PanelBody>,
				<PanelBody title={ __( 'Item Image Options' ) } initialOpen={ false } >
					<SelectControl
						label={ __( 'Display first image if available?' ) }
						value={ props.attributes.thumb }
						options={ [
							{
								label: __( 'Auto' ),
								value: 'auto',
							},
							{
								label: __( 'Yes' ),
								value: 'yes',
							},
							{
								label: __( 'No' ),
								value: 'no',
							},
						] }
						onChange={ props.onThumb }
					/>

					{ ( props.attributes.thumb !== 'no' ) && [
						( props.attributes.thumb !== 'auto' ) && (
							<div className="feedzy-blocks-base-control">
								<label className="blocks-base-control__label" for="inspector-media-upload">{ __( 'Default thumbnail URL if no image is found.' ) }</label>
								<MediaUpload
									type="image"
									id="inspector-media-upload"
									value={ props.attributes.default }
									onSelect={ props.onDefault }
									render={ ( { open } ) => [
										( props.attributes.default !== undefined ) && [
											<ResponsiveWrapper
												naturalWidth={ props.attributes.default.width }
												naturalHeight={ props.attributes.default.height }
											>
												<img src={ props.attributes.default.url } alt={ __( 'Featured image' ) } />
											</ResponsiveWrapper>,
											<Button
												isLarge
												onClick={ () => props.setAttributes( { default: undefined } ) }
												style={ { marginTop: '10px' } }
											>
												{ __( 'Remove Image' ) }
											</Button>
										],
										<Button
											isLarge
											onClick={ open }
											style={ { marginTop: '10px' } }
											className={ ( props.attributes.default === undefined ) && 'feedzy_image_upload' }
										>
											{ __( 'Upload Image' ) }
										</Button>
									] }
								/>
							</div>
						),
						<TextControl
							label={ __( 'Thumbnails dimension.' ) }
							type="number"
							value={ props.attributes.size }
							onChange={ props.onSize }
						/>
					] }
				</PanelBody>,
				( ( feedzyjs.isPro ) && (
					<PanelBody title={ __( 'Pro Features' ) } initialOpen={ false } >
						<ToggleControl
							label={ __( 'Display price if available?' ) }
							help={ ( props.attributes.price && props.attributes.template === 'default' ) ? __( 'Choose a different template for this to work.' ) : null }
							checked={ !! props.attributes.price }
							onChange={ props.togglePrice }
						/>

						<TextControl
							label={ __( 'Referral URL parameters.' ) }
							help={ __( 'Without ("?")' ) }
							placeholder={ _( '(eg. promo_code=feedzy_is_awesome)' ) }
							value={ props.attributes.referral_url }
							onChange={ props.onReferralURL }
						/>

						<RangeControl
							label={ __( 'Columns' ) }
							help={ __( 'How many columns we should use to display the feed items?' ) }
							value={ props.attributes.columns || 1	}
							onChange={ props.onColumns }
							min={ 1 }
							max={ 6 }
							beforeIcon="sort"
							allowReset
						/>

						<RadioImageControl
							label={ __( 'Feed Template' ) }
							selected={ props.attributes.template }
							options={ [
								{
									label: __( 'Default' ),
									src: feedzyjs.imagepath + 'feedzy-default-template.jpg',
									value: 'default',
								},
								{
									label: __( 'Style 1' ),
									src: feedzyjs.imagepath + 'feedzy-style1-template.jpg',
									value: 'style1',
								},
								{
									label: __( 'Style 2' ),
									src: feedzyjs.imagepath + 'feedzy-style2-template.jpg',
									value: 'style2',
								},
							] }
							onChange={ props.onTemplate }
						/>
					</PanelBody>
				) )
			] }
		</InspectorControls>
    )
}

export default Inspector;

import React from 'react';
import ReactDOM from 'react-dom';
import { __ } from '@wordpress/i18n';

import {
	Button,
	Modal,
	ExternalLink,
	Panel,
	PanelBody,
	PanelRow,
	BaseControl,
	TextControl,
	TextareaControl,
	Notice,
	Popover,
	ItemGroup,
	Item
} from '@wordpress/components';

import {
	Icon,
	dragHandle,
	close,
	plus,
	trash
} from '@wordpress/icons';

const UpgradeNotice = () => {
	if ( feedzyData.isPro ) {
		return(
			<></>
		);
	}
	return(
		<>
		<div className="fz-notice-wrap">
			<Notice status="info" isDismissible={false} className="fz-upgrade-notice"><p><span>PRO</span> {__('This action is a Premium feature. ')}</p> <ExternalLink href="https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/?utm_source=wpadmin&utm_medium=import&utm_campaign=upsell-content&utm_content=feedzy-rss-feeds">{ __( 'Update to Feedzy PRO' ) }</ExternalLink></Notice>
		</div>
		</>
	);
};

export default function Actions( props ) {
	if ( props.data && props.data.length === 0 ) {
		return(
			<></>
		);
	}

	return(
		<>
			<Panel header="" className="fz-action-panel" initialOpen={ false }>
				<ul>
					{props.data.map( ( item, index ) => {
						if ( 'trim' === item.id ) {
							return(
								<li className="fz-action-control" key={index}>
									<div className="fz-action-event">
										<PanelBody title={ __( 'Trim Content', 'feedzy-rss-feeds' ) } icon={ dragHandle } initialOpen={ false }>
											<PanelRow>
												<BaseControl>
													<TextControl
														type="number"
														help={ __( 'Define the trimmed content length', 'feedzy-rss-feeds' ) }
														label={__( 'Enter number of words', 'feedzy-rss-feeds' )}
														placeholder="45"
														value={ item.data.trimLength || '' }
														max=""
														min="1"
														step="1"
														onChange={ ( currentValue ) => props.onChangeHandler( { 'index': index, 'trimLength': currentValue ?? '' } ) }
													/>
												</BaseControl>
											</PanelRow>
										</PanelBody>
									</div>
									<div className="fz-trash-action">
										<button type="button" onClick={() => { props.removeCallback(index) }}>
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
												<path d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z" fill="black"/>
											</svg>
										</button>
									</div>
								</li>
							);
						}

						if ( 'search_replace' === item.id ) {
							return(
								<li className="fz-action-control">
									<div className="fz-action-event">
										<PanelBody title={ __( 'Search and Replace', 'feedzy-rss-feeds' ) } icon={ dragHandle } initialOpen={ false }>
											<PanelRow>
												<BaseControl>
													<TextControl
														type="text"
														label={__( 'Search', 'feedzy-rss-feeds' )}
														placeholder={__( 'Enter term', 'feedzy-rss-feeds' )}
														value={ item.data.search || '' }
														onChange={ ( currentValue ) => props.onChangeHandler( { 'index': index, 'search': currentValue ?? '' } ) }
													/>
												</BaseControl>
												<BaseControl>
													<TextControl
														type="text"
														label={__( 'Replace with', 'feedzy-rss-feeds' )}
														placeholder={__( 'Enter term', 'feedzy-rss-feeds' )}
														value={ item.data.searchWith || '' }
														onChange={ ( currentValue ) => props.onChangeHandler( { 'index': index, 'searchWith': currentValue ?? '' } ) }
													/>
												</BaseControl>
											</PanelRow>
										</PanelBody>
									</div>
									<div className="fz-trash-action">
										<button type="button" onClick={() => { props.removeCallback(index) }}>
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
												<path d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z" fill="black"/>
											</svg>
										</button>
									</div>
								</li>
							);
						}

						if ( 'fz_paraphrase' === item.id ) {
							return(
								<li className="fz-action-control">
									<div className="fz-action-event">
										<PanelBody title={ __( 'Paraphrase with Feedzy', 'feedzy-rss-feeds' ) } icon={ dragHandle } initialOpen={ false } className="fz-hide-icon">
											<PanelRow>
												<Notice status="warning" isDismissible={false} className="fz-credit-notice"><p><span></span> {__( 'You need more credits to use this actions!', 'feedzy-rss-feeds' )}</p><ExternalLink href="https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/?utm_source=wpadmin&utm_medium=import&utm_campaign=upsell-content&utm_content=feedzy-rss-feeds">{ __( 'Buy Credits' ) }</ExternalLink></Notice>
											</PanelRow>
										</PanelBody>
									</div>
									<div className="fz-trash-action">
										<button type="button" onClick={() => { props.removeCallback(index) }}>
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
												<path d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z" fill="black"/>
											</svg>
										</button>
									</div>
								</li>
							);
						}

						if ( 'chat_cpt_paraphrase' === item.id ) {
							return(
								<li className="fz-action-control fz-chat-cpt-action">
									<div className="fz-action-event">
										<PanelBody title={ __( 'Paraphrase with Chat GPT' ) } icon={ dragHandle } initialOpen={ false }>
											<PanelRow>
												<UpgradeNotice />
												<BaseControl>
													<TextareaControl
														label={ __( 'Main Prompt', 'feedzy-rss-feeds' ) }
														help={__( 'You can use { content } in the textarea such as: "Rephrase my {content} for better SEO.".', 'feedzy-rss-feeds' )}
														value={ item.data.ChatGPT || '' }
														onChange={ ( currentValue ) => props.onChangeHandler( { 'index': index, 'ChatGPT': currentValue ?? '' } ) }
													/>
												</BaseControl>
											</PanelRow>
										</PanelBody>
									</div>
									<div className="fz-trash-action">
										<button type="button" onClick={() => { props.removeCallback(index) }}>
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
												<path d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z" fill="black"/>
											</svg>
										</button>
									</div>
								</li>
							);
						}

						if ( 'fz_summarize' === item.id ) {
							return(
								<li className="fz-action-control">
									<div className="fz-action-event">
										<PanelBody title={ __( 'Summarise with Feedzy', 'feedzy-rss-feeds' ) } icon={ dragHandle } initialOpen={ false } className="fz-hide-icon">
											<UpgradeNotice />
										</PanelBody>
									</div>
									<div className="fz-trash-action">
										<button type="button" onClick={() => { props.removeCallback(index) }}>
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
												<path d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z" fill="black"/>
											</svg>
										</button>
									</div>
								</li>
							);
						}

						if ( 'fz_translate' === item.id ) {
							return(
								<li className="fz-action-control">
									<div className="fz-action-event">
										<PanelBody title={ __( 'Translate with Feedzy', 'feedzy-rss-feeds' ) } icon={ dragHandle } initialOpen={ false } className="fz-hide-icon">
											<UpgradeNotice />
										</PanelBody>
									</div>
									<div className="fz-trash-action">
										<button type="button" onClick={() => { props.removeCallback(index) }}>
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
												<path d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z" fill="black"/>
											</svg>
										</button>
									</div>
								</li>
							);
						}

						if ( 'spinnerchief' === item.id ) {
							return(
								<li className="fz-action-control">
									<div className="fz-action-event">
										<PanelBody title={ __( 'Spin using Spinnerchief', 'feedzy-rss-feeds' ) } icon={ dragHandle } initialOpen={ false } className="fz-hide-icon">
											<UpgradeNotice />
										</PanelBody>
									</div>
									<div className="fz-trash-action">
										<button type="button" onClick={() => { props.removeCallback(index) }}>
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
												<path d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z" fill="black"/>
											</svg>
										</button>
									</div>
								</li>
							);
						}

						if ( 'wordAI' === item.id ) {
							return(
								<li className="fz-action-control">
									<div className="fz-action-event">
										<PanelBody title={ __( 'Spin using WordAI', 'feedzy-rss-feeds' ) } icon={ dragHandle } initialOpen={ false } className="fz-hide-icon">
											<UpgradeNotice />
										</PanelBody>
									</div>
									<div className="fz-trash-action">
										<button type="button" onClick={() => { props.removeCallback(index) }}>
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
												<path d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z" fill="black"/>
											</svg>
										</button>
									</div>
								</li>
							);
						}
						return( <></>);
					})}
				</ul>
			</Panel>
		</>
	);
}
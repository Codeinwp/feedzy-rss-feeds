import React from 'react';
import { useRef, useEffect } from '@wordpress/element';
import { SortableElement, sortableHandle } from 'react-sortable-hoc';
import { __, sprintf } from '@wordpress/i18n';
import { unescape } from 'lodash';
import {
	Icon,
	dragHandle,
	close,
	plus,
	trash,
	external,
} from '@wordpress/icons';

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
	Item,
	ToggleControl,
	SelectControl,
} from '@wordpress/components';

import RewriteActionItem from './RewriteActionItem';
import ModelSelect from './ModelSelect';
import ProviderSelect from './ProviderSelect';

const DragHandle = sortableHandle(() => (
	<Icon icon={dragHandle} size={18} className="components-panel__icon" />
));

const SEO_FIELDS = [
	{ value: 'seo_title',        label: () => __( 'SEO Title', 'feedzy-rss-feeds' ) },
	{ value: 'meta_description', label: () => __( 'Meta Description', 'feedzy-rss-feeds' ) },
	{ value: 'focus_keyword',    label: () => __( 'Focus Keyword', 'feedzy-rss-feeds' ) },
];
const ALL_SEO_FIELD_VALUES = SEO_FIELDS.map( ( f ) => f.value );

const SeoFieldsSelect = ( { currentFields, onChange, disabled } ) => {
	const selectRef  = useRef( null );
	const chosenInit = useRef( false );

	useEffect( () => {
		if ( ! selectRef.current || typeof jQuery === 'undefined' ) {
			return;
		}
		const $select = jQuery( selectRef.current );
		$select.chosen( { width: '100%' } );
		chosenInit.current = true;

		$select.on( 'change.feedzy-seo-fields', function () {
			const selected = Array.from( this.selectedOptions ).map( ( o ) => o.value );
			onChange( selected );
		} );

		return () => {
			$select.off( 'change.feedzy-seo-fields' );
			if ( chosenInit.current ) {
				try {
					$select.chosen( 'destroy' );
				} catch ( e ) {} // eslint-disable-line no-empty
				chosenInit.current = false;
			}
		};
	}, [] ); // eslint-disable-line react-hooks/exhaustive-deps

	useEffect( () => {
		if ( ! selectRef.current || ! chosenInit.current || typeof jQuery === 'undefined' ) {
			return;
		}
		const options = selectRef.current.options;
		for ( let i = 0; i < options.length; i++ ) {
			options[ i ].selected = currentFields.includes( options[ i ].value );
		}
		jQuery( selectRef.current ).trigger( 'chosen:updated' );
	}, [ currentFields ] );

	return (
		<select
			ref={ selectRef }
			multiple
			className="feedzy-chosen form-control"
			disabled={ disabled }
			defaultValue={ currentFields }
			onChange={ () => {} }
		>
			{ SEO_FIELDS.map( ( field ) => (
				<option key={ field.value } value={ field.value }>
					{ field.label() }
				</option>
			) ) }
		</select>
	);
};

const UpgradeNotice = ({ higherPlanNotice, utmCampaign }) => {
	const upsellLink = `https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/?utm_source=wpadmin&utm_medium=import&utm_campaign=${utmCampaign}&utm_content=feedzy-rss-feeds`;
	if (feedzyData.isPro) {
		if (higherPlanNotice) {
			return (
				<>
					<div className="fz-notice-wrap">
						<Notice
							status="info"
							isDismissible={false}
							className="fz-upgrade-notice"
						>
							<p>
								<span>PRO</span>{' '}
								{__(
									'This action requires an upgrade to a higher plan.',
									'feedzy-rss-feeds'
								)}
							</p>{' '}
							<ExternalLink href={upsellLink}>
								{__(
									'Upgrade Feedzy PRO Plan',
									'feedzy-rss-feeds'
								)}
							</ExternalLink>
						</Notice>
					</div>
				</>
			);
		}
		return <></>;
	}
	return (
		<>
			<div className="fz-notice-wrap">
				<Notice
					status="info"
					isDismissible={false}
					className="fz-upgrade-notice"
				>
					<p>
						<span>PRO</span>{' '}
						{__(
							'This action is a Premium feature.',
							'feedzy-rss-feeds'
						)}
					</p>{' '}
					<ExternalLink href={upsellLink}>
						{__('Upgrade to Feedzy PRO', 'feedzy-rss-feeds')}
					</ExternalLink>
				</Notice>
			</div>
		</>
	);
};

const CreditNotice = () => {
	return (
		<>
			<div className="fz-notice-wrap">
				<Notice
					status="warning"
					isDismissible={false}
					className="fz-credit-notice"
				>
					<p>
						<span></span>{' '}
						{__(
							'You need more credits to use this actions!',
							'feedzy-rss-feeds'
						)}
					</p>
					<ExternalLink href="https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/?utm_source=wpadmin&utm_medium=import&utm_campaign=upsell-content&utm_content=feedzy-rss-feeds">
						{__('Buy Credits', 'feedzy-rss-feeds')}
					</ExternalLink>
				</Notice>
			</div>
		</>
	);
};

const getCurrentLanguage = () => {
	// This is for backward compatibility for the previous language control that sit outside the action modal.
	const langInput = document.getElementById('feedzy_auto_translate_lang');
	return langInput ? langInput.value : '';
};

const SortableItem = ({ propRef, loopIndex, item }) => {
	const counter = loopIndex + 1;
	const isInitialOpen = loopIndex === propRef?.lastAddedActionIdx;

	if ('trim' === item.id) {
		return (
			<li className="fz-action-control" data-counter={counter}>
				<div className="fz-action-event">
					<PanelBody
						title={__('Trim Content', 'feedzy-rss-feeds')}
						icon={DragHandle}
						initialOpen={isInitialOpen}
					>
						<PanelRow>
							<BaseControl>
								<TextControl
									type="number"
									help={__(
										'Define the trimmed content length',
										'feedzy-rss-feeds'
									)}
									label={__(
										'Enter number of words',
										'feedzy-rss-feeds'
									)}
									placeholder="45"
									value={item.data.trimLength || ''}
									max=""
									min="1"
									step="1"
									onChange={(currentValue) =>
										propRef.onChangeHandler({
											index: loopIndex,
											trimLength: currentValue ?? '',
										})
									}
								/>
							</BaseControl>
						</PanelRow>
					</PanelBody>
				</div>
				<div className="fz-trash-action">
					<button
						type="button"
						onClick={() => {
							propRef.removeCallback(loopIndex);
						}}
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="24"
							height="24"
							viewBox="0 0 24 24"
							fill="none"
						>
							<path
								d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z"
								fill="black"
							/>
						</svg>
					</button>
				</div>
			</li>
		);
	}

	if ('search_replace' === item.id) {
		return (
			<li className="fz-action-control" data-counter={counter}>
				<div className="fz-action-event">
					<PanelBody
						title={__('Search and Replace', 'feedzy-rss-feeds')}
						icon={DragHandle}
						initialOpen={isInitialOpen}
					>
						<PanelRow>
							<BaseControl>
								<SelectControl
									label={__('Type', 'feedzy-rss-feeds')}
									value={item.data.mode || 'text'}
									options={[
										{
											label: __(
												'Text',
												'feedzy-rss-feeds'
											),
											value: 'text',
										},
										{
											label: __(
												'Regex',
												'feedzy-rss-feeds'
											),
											value: 'regex',
										},
										{
											label: __(
												'Wildcard',
												'feedzy-rss-feeds'
											),
											value: 'wildcard',
										},
									]}
									onChange={(currentValue) =>
										propRef.onChangeHandler({
											index: loopIndex,
											mode: currentValue ?? 'text',
										})
									}
								/>
							</BaseControl>
							<BaseControl>
								<TextControl
									type="text"
									label={__('Search', 'feedzy-rss-feeds')}
									placeholder={__(
										'Enter term or regex',
										'feedzy-rss-feeds'
									)}
									value={
										item.data.search
											? unescape(
													item.data.search.replaceAll(
														'&#039;',
														"'"
													)
												)
											: ''
									}
									onChange={(currentValue) =>
										propRef.onChangeHandler({
											index: loopIndex,
											search: currentValue ?? '',
										})
									}
								/>
							</BaseControl>
							<BaseControl>
								<TextControl
									type="text"
									label={__(
										'Replace with',
										'feedzy-rss-feeds'
									)}
									placeholder={__(
										'Enter term',
										'feedzy-rss-feeds'
									)}
									value={
										item.data.searchWith
											? unescape(
													item.data.searchWith.replaceAll(
														'&#039;',
														"'"
													)
												)
											: ''
									}
									onChange={(currentValue) =>
										propRef.onChangeHandler({
											index: loopIndex,
											searchWith: currentValue ?? '',
										})
									}
								/>
							</BaseControl>
						</PanelRow>
					</PanelBody>
				</div>
				<div className="fz-trash-action">
					<button
						type="button"
						onClick={() => {
							propRef.removeCallback(loopIndex);
						}}
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="24"
							height="24"
							viewBox="0 0 24 24"
							fill="none"
						>
							<path
								d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z"
								fill="black"
							/>
						</svg>
					</button>
				</div>
			</li>
		);
	}

	if ('fz_paraphrase' === item.id) {
		return (
			<li className="fz-action-control" data-counter={counter}>
				<div className="fz-action-event">
					<PanelBody
						title={__('Paraphrase with Feedzy', 'feedzy-rss-feeds')}
						icon={DragHandle}
						initialOpen={isInitialOpen}
						className="fz-hide-icon"
					>
						<UpgradeNotice
							higherPlanNotice={
								!feedzyData.isBusinessPlan &&
								!feedzyData.isAgencyPlan
							}
							utmCampaign="action-paraphrase-feedzy"
						/>
					</PanelBody>
				</div>
				<div className="fz-trash-action">
					<button
						type="button"
						onClick={() => {
							propRef.removeCallback(loopIndex);
						}}
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="24"
							height="24"
							viewBox="0 0 24 24"
							fill="none"
						>
							<path
								d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z"
								fill="black"
							/>
						</svg>
					</button>
				</div>
			</li>
		);
	}

	if ('chat_gpt_rewrite' === item.id) {
		return (
			<RewriteActionItem
				counter={counter}
				item={item}
				loopIndex={loopIndex}
				propRef={propRef}
			/>
		);
	}

	if ('fz_translate' === item.id) {
		return (
			<li
				className="fz-action-control fz-translate-action"
				data-counter={counter}
			>
				<div className="fz-action-event">
					<PanelBody
						title={__('Translate with Feedzy', 'feedzy-rss-feeds')}
						icon={DragHandle}
						initialOpen={isInitialOpen}
						className="fz-hide-icon"
					>
						<PanelRow>
							<UpgradeNotice
								higherPlanNotice={!feedzyData.isAgencyPlan}
								utmCampaign="action-translate-feedzy"
							/>
							<BaseControl className="mb-20">
								<SelectControl
									label={__(
										'Target Language',
										'feedzy-rss-feeds'
									)}
									value={
										item.data.lang || getCurrentLanguage()
									}
									options={Object.entries(
										window.feedzyData.languageList
									).map(([key, value]) => ({
										label: value,
										value: key,
									}))}
									onChange={(currentValue) =>
										propRef.onChangeHandler({
											index: loopIndex,
											lang: currentValue ?? '',
										})
									}
									disabled={!feedzyData.isAgencyPlan}
								/>
							</BaseControl>
						</PanelRow>
					</PanelBody>
				</div>
				<div className="fz-trash-action">
					<button
						type="button"
						onClick={() => {
							propRef.removeCallback(loopIndex);
						}}
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="24"
							height="24"
							viewBox="0 0 24 24"
							fill="none"
						>
							<path
								d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z"
								fill="black"
							/>
						</svg>
					</button>
				</div>
			</li>
		);
	}

	if ('spinnerchief' === item.id) {
		return (
			<li className="fz-action-control" data-counter={counter}>
				<div className="fz-action-event">
					{feedzyData.isPro &&
						feedzyData.isAgencyPlan &&
						!feedzyData.apiLicenseStatus.spinnerChiefStatus &&
						(feedzyData.isHighPrivileges ? (
							<span className="error-message">
								{__('Invalid API Key', 'feedzy-rss-feeds')}{' '}
								<ExternalLink href="admin.php?page=feedzy-integration&tab=spinnerchief">
									<Icon
										icon={external}
										size={16}
										fill="#F00"
									/>
								</ExternalLink>
							</span>
						) : (
							<span className="error-message">
								{__(
									'Invalid API Key, Please contact the administrator',
									'feedzy-rss-feeds'
								)}
							</span>
						))}
					<PanelBody
						title={__(
							'Spin using SpinnerChief',
							'feedzy-rss-feeds'
						)}
						icon={DragHandle}
						initialOpen={isInitialOpen}
						className="fz-hide-icon"
					>
						<UpgradeNotice
							higherPlanNotice={!feedzyData.isAgencyPlan}
							utmCampaign="action-spinnerchief"
						/>
					</PanelBody>
				</div>
				<div className="fz-trash-action">
					<button
						type="button"
						onClick={() => {
							propRef.removeCallback(loopIndex);
						}}
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="24"
							height="24"
							viewBox="0 0 24 24"
							fill="none"
						>
							<path
								d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z"
								fill="black"
							/>
						</svg>
					</button>
				</div>
			</li>
		);
	}

	if ('wordAI' === item.id) {
		return (
			<li className="fz-action-control" data-counter={counter}>
				<div className="fz-action-event">
					{feedzyData.isPro &&
						feedzyData.isAgencyPlan &&
						!feedzyData.apiLicenseStatus.wordaiStatus &&
						(feedzyData.isHighPrivileges ? (
							<span className="error-message">
								{__('Invalid API Key', 'feedzy-rss-feeds')}{' '}
								<ExternalLink href="admin.php?page=feedzy-integration&tab=wordai">
									<Icon
										icon={external}
										size={16}
										fill="#F00"
									/>
								</ExternalLink>
							</span>
						) : (
							<span className="error-message">
								{__(
									'Invalid API Key, Please contact the administrator',
									'feedzy-rss-feeds'
								)}
							</span>
						))}
					<PanelBody
						title={__('Spin using WordAI', 'feedzy-rss-feeds')}
						icon={DragHandle}
						initialOpen={isInitialOpen}
						className="fz-hide-icon"
					>
						<UpgradeNotice
							higherPlanNotice={!feedzyData.isAgencyPlan}
							utmCampaign="action-wordai"
						/>
					</PanelBody>
				</div>
				<div className="fz-trash-action">
					<button
						type="button"
						onClick={() => {
							propRef.removeCallback(loopIndex);
						}}
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="24"
							height="24"
							viewBox="0 0 24 24"
							fill="none"
						>
							<path
								d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z"
								fill="black"
							/>
						</svg>
					</button>
				</div>
			</li>
		);
	}

	if ('fz_image' === item.id) {
		const isPro = feedzyData.isPro;
		const isThemeisleAIEnabled = feedzyData.isThemeisleAIEnabled;
		const providerLicenseStatus = feedzyData.apiLicenseStatus.openaiStatus;
		const defaultModel = window.feedzyData?.integrations?.openAIImageModel || 'gpt-image-2';
		const selectedAIModel = item.data.aiModel || defaultModel;

		return (
			<li
				className="fz-action-control fz-chat-cpt-action"
				data-counter={counter}
			>
				<div className="fz-action-event">
					{feedzyData.isPro &&
						(feedzyData.isBusinessPlan ||
							feedzyData.isAgencyPlan) &&
						!feedzyData.apiLicenseStatus.openaiStatus &&
						!isThemeisleAIEnabled &&
						(feedzyData.isHighPrivileges ? (
							<span className="error-message">
								{__('Invalid API Key', 'feedzy-rss-feeds')}{' '}
								<ExternalLink href="admin.php?page=feedzy-integration&tab=openai">
									<Icon
										icon={external}
										size={16}
										fill="#F00"
									/>
								</ExternalLink>
							</span>
						) : (
							<span className="error-message">
								{__(
									'Invalid API Key, Please contact the administrator',
									'feedzy-rss-feeds'
								)}
							</span>
						))}
					<PanelBody
						title={__(
							'Generate Image with OpenAI',
							'feedzy-rss-feeds'
						)}
						icon={DragHandle}
						initialOpen={isInitialOpen}
					>
						<PanelRow>
							<UpgradeNotice
								higherPlanNotice={
									!feedzyData.isBusinessPlan &&
									!feedzyData.isAgencyPlan
								}
								utmCampaign="action-generate-image-chatgpt"
							/>
							<BaseControl className="mb-20">
								<ToggleControl
									checked={
										item.data.generateOnlyMissingImages ??
										true
									}
									label={__(
										'Generate only for missing images',
										'feedzy-rss-feeds'
									)}
									onChange={(currentValue) =>
										propRef.onChangeHandler({
											index: loopIndex,
											generateOnlyMissingImages:
												currentValue ?? '',
										})
									}
									help={__(
										"Only generate the featured image if it's missing in the source XML RSS Feed.",
										'feedzy-rss-feeds'
									)}
									disabled={
										!feedzyData.isPro ||
										(!feedzyData.apiLicenseStatus
											.openaiStatus &&
											!isThemeisleAIEnabled)
									}
								/>
							</BaseControl>
							{!isThemeisleAIEnabled && (
								<BaseControl __nextHasNoMarginBottom className="mb-20">
									<SelectControl
										__nextHasNoMarginBottom
										label={__('Choose Model', 'feedzy-rss-feeds')}
										value={selectedAIModel}
										onChange={(currentValue) => {
											propRef.onChangeHandler({
												index: loopIndex,
												aiModel:
													currentValue !== defaultModel ? currentValue : '',
											});
										}}
										disabled={!isPro || !providerLicenseStatus}
									>
										{window.feedzyData.openAIImageModels.length > 0 && (
											<optgroup label={__('Latest models', 'feedzy-rss-feeds')}>
												{window.feedzyData.openAIImageModels.map((model) => (
													<option key={model} value={model}>
														{model}
													</option>
												))}
											</optgroup>
										)}
										{window.feedzyData.deprecatedOpenAIImageModels.length > 0 && (
											<optgroup
												label={__('Deprecated models', 'feedzy-rss-feeds')}
											>
												{window.feedzyData.deprecatedOpenAIImageModels.map(
													(model) => (
														<option key={model} value={model}>
															{model}
														</option>
													)
												)}
											</optgroup>
										)}
									</SelectControl>
								</BaseControl>
							)}
							<BaseControl __nextHasNoMarginBottom>
								<TextareaControl
									__nextHasNoMarginBottom
									label={__(
										'Additional Prompt',
										'feedzy-rss-feeds'
									)}
									value={
										item.data.generateImagePrompt
											? unescape(
													item.data.generateImagePrompt.replaceAll(
														'&#039;',
														"'"
													)
												)
											: ''
									}
									onChange={(currentValue) =>
										propRef.onChangeHandler({
											index: loopIndex,
											generateImagePrompt:
												currentValue ?? '',
										})
									}
									help={__(
										'Add specific instructions to customize the image generation. By default, images are based on the item’s title and content. Use this field to guide the style of the image, for example: Realistic, artistic, comic-style, etc.',
										'feedzy-rss-feeds'
									)}
									disabled={
										!feedzyData.isPro ||
										(!feedzyData.apiLicenseStatus
											.openaiStatus &&
											!isThemeisleAIEnabled)
									}
								/>
							</BaseControl>
						</PanelRow>
					</PanelBody>
				</div>
				<div className="fz-trash-action">
					<button
						type="button"
						onClick={() => {
							propRef.removeCallback(loopIndex);
						}}
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="24"
							height="24"
							viewBox="0 0 24 24"
							fill="none"
						>
							<path
								d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z"
								fill="black"
							/>
						</svg>
					</button>
				</div>
			</li>
		);
	}

	if ('modify_links' === item.id) {
		return (
			<li
				className="fz-action-control fz-modify-links"
				data-counter={counter}
			>
				<div className="fz-action-event">
					<PanelBody
						title={__('Modify Links', 'feedzy-rss-feeds')}
						icon={DragHandle}
						initialOpen={isInitialOpen}
					>
						<PanelRow>
							<UpgradeNotice
								higherPlanNotice={false}
								utmCampaign="action-modify-links"
							/>
							<BaseControl className="mb-20">
								<ToggleControl
									checked={item.data.remove_links ?? false}
									label={__(
										'Remove links from the content?',
										'feedzy-rss-feeds'
									)}
									onChange={(currentValue) =>
										propRef.onChangeHandler({
											index: loopIndex,
											remove_links: currentValue ?? '',
										})
									}
									disabled={!feedzyData.isPro}
								/>
							</BaseControl>
							{true !== item.data.remove_links && (
								<BaseControl className="mb-20">
									<SelectControl
										label={__(
											'Open Links In',
											'feedzy-rss-feeds'
										)}
										value={item.data.target || ''}
										options={[
											{
												label: __(
													'Default',
													'feedzy-rss-feeds'
												),
												value: '',
											},
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
										onChange={(currentValue) =>
											propRef.onChangeHandler({
												index: loopIndex,
												target: currentValue ?? '',
											})
										}
										disabled={!feedzyData.isPro}
									/>
								</BaseControl>
							)}
							{true !== item.data.remove_links && (
								<BaseControl>
									<SelectControl
										label={__(
											'Make this link a "nofollow" link?',
											'feedzy-rss-feeds'
										)}
										value={item.data.follow || ''}
										onChange={(currentValue) =>
											propRef.onChangeHandler({
												index: loopIndex,
												follow: currentValue ?? '',
											})
										}
										options={[
											{
												label: __(
													'Default',
													'feedzy-rss-feeds'
												),
												value: '',
											},
											{
												label: __(
													'No',
													'feedzy-rss-feeds'
												),
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
										disabled={!feedzyData.isPro}
									/>
								</BaseControl>
							)}
						</PanelRow>
					</PanelBody>
				</div>
				<div className="fz-trash-action">
					<button
						type="button"
						onClick={() => {
							propRef.removeCallback(loopIndex);
						}}
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="24"
							height="24"
							viewBox="0 0 24 24"
							fill="none"
						>
							<path
								d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z"
								fill="black"
							/>
						</svg>
					</button>
				</div>
			</li>
		);
	}

	if ('fz_seo_metadata' === item.id) {
		const {
			isPro,
			isBusinessPlan,
			isAgencyPlan,
			isHighPrivileges,
			apiLicenseStatus,
			isThemeisleAIEnabled,
			isSeoPluginActive,
		} = window.feedzyData;

		let defaultProvider;
		if (apiLicenseStatus.openaiStatus) {
			defaultProvider = 'openai';
		} else if (apiLicenseStatus.openRouterStatus) {
			defaultProvider = 'openrouter';
		} else {
			defaultProvider = 'openai';
		}

		const selectedProvider = item.data.aiProvider || defaultProvider;
		const providerLicenseStatus =
			selectedProvider === 'openai'
				? apiLicenseStatus.openaiStatus
				: apiLicenseStatus.openRouterStatus;

		const defaultModel =
			selectedProvider === 'openai'
				? window.feedzyData?.integrations?.openAIModel
				: '';
		const selectedAIModel = item.data.aiModel || defaultModel;

		const showError =
			isPro &&
			( isBusinessPlan || isAgencyPlan ) &&
			! providerLicenseStatus &&
			! isThemeisleAIEnabled;
		const showUpgradeNotice = ! isBusinessPlan && ! isAgencyPlan;
		const isDisabled = ! isPro || ( ! providerLicenseStatus && ! isThemeisleAIEnabled ) || ! isSeoPluginActive;

		const examplePrompt =
			__( 'Example: Generate SEO-optimized metadata for this article.', 'feedzy-rss-feeds' );

		return (
			<li
				className="fz-action-control fz-chat-cpt-action"
				data-counter={counter}
			>
				<div className="fz-action-event">
					{ showError && (
						<span className="error-message">
							{ isHighPrivileges ? (
								<>
									{ __( 'Invalid API Key', 'feedzy-rss-feeds' ) }{ ' ' }
									<ExternalLink
										href={ `admin.php?page=feedzy-integration&tab=${ selectedProvider }` }
									>
										<Icon icon={ external } size={ 16 } fill="#F00" />
									</ExternalLink>
								</>
							) : __(
								'Invalid API Key, Please contact the administrator',
								'feedzy-rss-feeds'
							) }
						</span>
					) }
					<PanelBody
						title={ __( 'Generate SEO Metadata', 'feedzy-rss-feeds' ) }
						icon={ DragHandle }
						initialOpen={ isInitialOpen }
					>
						<PanelRow>
							{ ! isSeoPluginActive && (
								<div className="fz-notice-wrap">
									<Notice
										status="warning"
										isDismissible={ false }
										className="fz-upgrade-notice"
									>
										<p>
											{ __(
												'No supported SEO plugin is active.',
												'feedzy-rss-feeds'
											) }
										</p>
									</Notice>
								</div>
							) }
							<UpgradeNotice
								higherPlanNotice={ showUpgradeNotice }
								utmCampaign="action-seo-metadata"
							/>
							{ ! isThemeisleAIEnabled && (
								<ProviderSelect
									selectedProvider={selectedProvider}
									loopIndex={loopIndex}
									propRef={propRef}
									isPro={isPro}
								/>
							) }
							{ ! isThemeisleAIEnabled && selectedProvider === 'openai' && (
								<ModelSelect
									selectedProvider={selectedProvider}
									selectedAIModel={selectedAIModel}
									defaultModel={defaultModel}
									loopIndex={loopIndex}
									propRef={propRef}
									isPro={isPro}
									providerLicenseStatus={providerLicenseStatus}
								/>
							) }
							<BaseControl __nextHasNoMarginBottom className="mb-20">
								<BaseControl.VisualLabel>
									{ __( 'SEO Fields to Generate', 'feedzy-rss-feeds' ) }
								</BaseControl.VisualLabel>
								<SeoFieldsSelect
									currentFields={
										Array.isArray( item.data.seoFields )
											? item.data.seoFields
											: ALL_SEO_FIELD_VALUES
									}
									onChange={ ( selected ) =>
										propRef.onChangeHandler( {
											index: loopIndex,
											seoFields: selected,
										} )
									}
									disabled={ isDisabled }
								/>
							</BaseControl>
							<BaseControl __nextHasNoMarginBottom>
								<TextareaControl
									__nextHasNoMarginBottom
									label={ __( 'Additional Prompt', 'feedzy-rss-feeds' ) }
									value={
										item.data.seoPrompt
											? unescape( item.data.seoPrompt.replaceAll( '&#039;', "'" ) )
											: ''
									}
									help={ examplePrompt }
									onChange={ ( currentValue ) =>
										propRef.onChangeHandler( {
											index: loopIndex,
											seoPrompt: currentValue ?? '',
										} )
									}
									disabled={ isDisabled }
								/>
							</BaseControl>
						</PanelRow>
					</PanelBody>
				</div>
				<div className="fz-trash-action">
					<button
						type="button"
						onClick={ () => propRef.removeCallback( loopIndex ) }
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="24"
							height="24"
							viewBox="0 0 24 24"
							fill="none"
						>
							<path
								d="M20 5.0002H14.3C14.3 3.7002 13.3 2.7002 12 2.7002C10.7 2.7002 9.7 3.7002 9.7 5.0002H4V7.0002H5.5V7.3002L7.2 18.4002C7.3 19.4002 8.2 20.1002 9.2 20.1002H14.9C15.9 20.1002 16.7 19.4002 16.9 18.4002L18.6 7.3002V7.0002H20V5.0002ZM16.8 7.0002L15.1 18.1002C15.1 18.2002 15 18.3002 14.8 18.3002H9.1C9 18.3002 8.8 18.2002 8.8 18.1002L7.2 7.0002H16.8Z"
								fill="black"
							/>
						</svg>
					</button>
				</div>
			</li>
		);
	}
};

export default SortableElement(SortableItem);

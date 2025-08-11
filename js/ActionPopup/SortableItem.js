import React from 'react';
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

const DragHandle = sortableHandle(() => (
	<Icon icon={dragHandle} size={18} className="components-panel__icon" />
));

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

const aiModels = {
	openai: {
		latest: [
			{ label: 'GPT-5 Nano', value: 'gpt-nano' },
			{ label: 'GPT-5 Mini', value: 'gpt-5-mini' },
			{ label: 'GPT-5', value: 'gpt-5' },
			{ label: 'GPT-4.1 Nano', value: 'gpt-4.1-nano' },
			{ label: 'GPT-4.1 Mini', value: 'gpt-4.1-mini' },
			{ label: 'GPT-4.1', value: 'gpt-4.1' },
			{ label: 'GPT-4o Mini', value: 'gpt-4o-mini' },
			{ label: 'GPT-4o', value: 'gpt-4o' },
			{ label: 'O4 Mini', value: 'o4-mini' },
			{ label: 'O3 Mini', value: 'o3-mini' },
			{ label: 'O1 Pro', value: 'o1-pro' },
			{ label: 'O1', value: 'o1' },
		],
		older: [
			{ label: 'GPT-4', value: 'gpt-4' },
			{
				label: 'GPT-3.5 Turbo Instruct',
				value: 'gpt-3.5-turbo-instruct',
			},
			{ label: 'Babbage-002', value: 'babbage-002' },
			{ label: 'Davinci-002', value: 'davinci-002' },
		],
	},
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

	window.console.log(item.data);
	if ('trim' === item.id) {
		return (
			<li className="fz-action-control" data-counter={counter}>
				<div className="fz-action-event">
					<PanelBody
						title={__('Trim Content', 'feedzy-rss-feeds')}
						icon={DragHandle}
						initialOpen={false}
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
						initialOpen={false}
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
						initialOpen={false}
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
		let defaultProvider = 'openai';
		let providerLicenseStatus = false;
		if (feedzyData.apiLicenseStatus.openaiStatus) {
			defaultProvider = 'openai';
		} else if (feedzyData.apiLicenseStatus.openRouterStatus) {
			defaultProvider = 'openrouter';
		}

		const selectedProvider = item.data.aiProvider || defaultProvider;
		if ('openai' === selectedProvider) {
			providerLicenseStatus = feedzyData.apiLicenseStatus.openaiStatus;
		} else if ('openrouter' === selectedProvider) {
			providerLicenseStatus =
				feedzyData.apiLicenseStatus.openRouterStatus;
		}

		let defaultModel = '';
		if (selectedProvider === 'openai') {
			defaultModel = feedzyData?.integrationSettings?.openai_api_model;
		} else if (selectedProvider === 'openrouter') {
			defaultModel =
				feedzyData?.integrationSettings?.openrouter_api_model;
		}

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
						!providerLicenseStatus &&
						(feedzyData.isHighPrivileges ? (
							<span className="error-message">
								{__('Invalid API Key', 'feedzy-rss-feeds')}{' '}
								<ExternalLink
									href={`admin.php?page=feedzy-integration&tab=${item.data.aiProvider || defaultProvider}`}
								>
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
						title={__('Rewrite with AI', 'feedzy-rss-feeds')}
						icon={DragHandle}
						initialOpen={false}
					>
						<PanelRow>
							<UpgradeNotice
								higherPlanNotice={
									!feedzyData.isBusinessPlan &&
									!feedzyData.isAgencyPlan
								}
								utmCampaign="action-paraphrase-chatgpt"
							/>
							<BaseControl
								__nextHasNoMarginBottom
								className="mb-20"
							>
								<SelectControl
									__nextHasNoMarginBottom
									label={__(
										'Choose an AI Provider',
										'feedzy-rss-feeds'
									)}
									value={selectedProvider}
									options={[
										{
											label: __(
												'OpenAI',
												'feedzy-rss-feeds'
											),
											value: 'openai',
										},
										{
											label: __(
												'OpenRouter',
												'feedzy-rss-feeds'
											),
											value: 'openrouter',
										},
									]}
									onChange={(currentValue) => {
										propRef.onChangeHandler({
											index: loopIndex,
											aiProvider: currentValue ?? '',
											aiModel: '',
										});
									}}
									disabled={!feedzyData.isPro}
								/>
							</BaseControl>

							{selectedProvider && aiModels[selectedProvider] && (
								<BaseControl
									__nextHasNoMarginBottom
									className="mb-20"
								>
									<SelectControl
										__nextHasNoMarginBottom
										label={__(
											'Choose Model',
											'feedzy-rss-feeds'
										)}
										value={selectedAIModel}
										onChange={(currentValue) => {
											propRef.onChangeHandler({
												index: loopIndex,
												aiModel:
													currentValue !==
													defaultModel
														? currentValue
														: '',
											});
										}}
										disabled={
											!feedzyData.isPro ||
											!providerLicenseStatus
										}
									>
										{aiModels[selectedProvider].latest
											.length > 0 && (
											<optgroup
												label={__(
													'Latest models',
													'feedzy-rss-feeds'
												)}
											>
												{aiModels[
													selectedProvider
												].latest.map((model) => (
													<option
														key={model.value}
														value={model.value}
													>
														{model.label}
													</option>
												))}
											</optgroup>
										)}

										{aiModels[selectedProvider].older
											.length > 0 && (
											<optgroup
												label={__(
													'Older models',
													'feedzy-rss-feeds'
												)}
											>
												{aiModels[
													selectedProvider
												].older.map((model) => (
													<option
														key={model.value}
														value={model.value}
													>
														{model.label}
													</option>
												))}
											</optgroup>
										)}
									</SelectControl>
								</BaseControl>
							)}

							<BaseControl __nextHasNoMarginBottom>
								<TextareaControl
									__nextHasNoMarginBottom
									label={__(
										'Main Prompt',
										'feedzy-rss-feeds'
									)}
									help={sprintf(
										// translators: %1$s is the tag content: {content}
										__(
											'You can use %1$s in the textarea such as: "Rephrase my %1$s for better SEO."',
											'feedzy-rss-feeds'
										),
										'{content}'
									)}
									value={
										item.data.ChatGPT
											? unescape(
													item.data.ChatGPT.replaceAll(
														'&#039;',
														"'"
													)
												)
											: ''
									}
									onChange={(currentValue) =>
										propRef.onChangeHandler({
											index: loopIndex,
											ChatGPT: currentValue ?? '',
											aiProvider: selectedProvider,
										})
									}
									disabled={
										!feedzyData.isPro ||
										!providerLicenseStatus
									}
								/>
								<div className="fz-prompt-button">
									<Button
										variant="secondary"
										onClick={() =>
											propRef.updatePromptText({
												index: loopIndex,
												type: 'summarize',
											})
										}
										disabled={
											!feedzyData.isPro ||
											!providerLicenseStatus
										}
									>
										{__('Summarize', 'feedzy-rss-feeds')}
									</Button>
									<Button
										variant="secondary"
										onClick={() =>
											propRef.updatePromptText({
												index: loopIndex,
												type: 'paraphase',
											})
										}
										disabled={
											!feedzyData.isPro ||
											!providerLicenseStatus
										}
									>
										{__('Paraphrase', 'feedzy-rss-feeds')}
									</Button>
									<Button
										variant="secondary"
										onClick={() =>
											propRef.updatePromptText({
												index: loopIndex,
												type: 'change_tone',
											})
										}
										disabled={
											!feedzyData.isPro ||
											!providerLicenseStatus
										}
									>
										{__('Change tone', 'feedzy-rss-feeds')}
									</Button>
								</div>
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
						initialOpen={false}
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
						initialOpen={false}
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
						initialOpen={false}
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
						initialOpen={false}
					>
						<PanelRow>
							<UpgradeNotice
								higherPlanNotice={
									!feedzyData.isBusinessPlan &&
									!feedzyData.isAgencyPlan
								}
								utmCampaign="action-generate-image-chatgpt"
							/>
							<BaseControl>
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
										!feedzyData.apiLicenseStatus
											.openaiStatus
									}
								/>
							</BaseControl>
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
										!feedzyData.apiLicenseStatus
											.openaiStatus
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
						initialOpen={false}
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
};

export default SortableElement(SortableItem);

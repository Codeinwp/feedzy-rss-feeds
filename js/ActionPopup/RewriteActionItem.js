import { __, sprintf } from '@wordpress/i18n';
import { unescape } from 'lodash';
import { Icon, dragHandle, external } from '@wordpress/icons';

import {
	Button,
	ExternalLink,
	PanelBody,
	PanelRow,
	BaseControl,
	TextareaControl,
	SelectControl,
	Notice,
} from '@wordpress/components';

import { sortableHandle } from 'react-sortable-hoc';

const DragHandle = sortableHandle(() => (
	<Icon icon={dragHandle} size={18} className="components-panel__icon" />
));

const UpgradeNotice = ({ higherPlanNotice, utmCampaign }) => {
	const upsellLink = `https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/?utm_source=wpadmin&utm_medium=import&utm_campaign=${utmCampaign}&utm_content=feedzy-rss-feeds`;
	if (window.feedzyData.isPro) {
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
									'Upgrade to Feedzy PRO',
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

const ErrorMessage = ({ provider, isHighPrivileges }) => {
	if (isHighPrivileges) {
		return (
			<span className="error-message">
				{__('Invalid API Key', 'feedzy-rss-feeds')}{' '}
				<ExternalLink
					href={`admin.php?page=feedzy-integration&tab=${provider}`}
				>
					<Icon icon={external} size={16} fill="#F00" />
				</ExternalLink>
			</span>
		);
	}

	return (
		<span className="error-message">
			{__(
				'Invalid API Key, Please contact the administrator',
				'feedzy-rss-feeds'
			)}
		</span>
	);
};

const ProviderSelect = ({ selectedProvider, loopIndex, propRef, isPro }) => (
	<BaseControl __nextHasNoMarginBottom className="mb-20">
		<SelectControl
			__nextHasNoMarginBottom
			label={__('Choose an AI Provider', 'feedzy-rss-feeds')}
			value={selectedProvider}
			options={[
				{ label: __('OpenAI', 'feedzy-rss-feeds'), value: 'openai' },
				{
					label: __('OpenRouter', 'feedzy-rss-feeds'),
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
			disabled={!isPro}
		/>
	</BaseControl>
);

const ModelSelect = ({
	selectedProvider,
	selectedAIModel,
	defaultModel,
	loopIndex,
	propRef,
	isPro,
	providerLicenseStatus,
}) => {
	if (selectedProvider !== 'openai') {
		return null;
	}

	return (
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
				{window.feedzyData.activeOpenAIModels.length > 0 && (
					<optgroup label={__('Latest models', 'feedzy-rss-feeds')}>
						{window.feedzyData.activeOpenAIModels.map((model) => (
							<option key={model} value={model}>
								{model}
							</option>
						))}
					</optgroup>
				)}
				{window.feedzyData.deprecatedOpenAIModels.length > 0 && (
					<optgroup
						label={__('Deprecated models', 'feedzy-rss-feeds')}
					>
						{window.feedzyData.deprecatedOpenAIModels.map(
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
	);
};

const PromptControls = ({
	item,
	loopIndex,
	propRef,
	isPro,
	providerLicenseStatus,
	selectedProvider,
}) => (
	<BaseControl __nextHasNoMarginBottom>
		<TextareaControl
			__nextHasNoMarginBottom
			label={__('Main Prompt', 'feedzy-rss-feeds')}
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
					? unescape(item.data.ChatGPT.replaceAll('&#039;', "'"))
					: ''
			}
			onChange={(currentValue) =>
				propRef.onChangeHandler({
					index: loopIndex,
					ChatGPT: currentValue ?? '',
					aiProvider: selectedProvider,
				})
			}
			disabled={!isPro || !providerLicenseStatus}
		/>
		<div className="fz-prompt-button">
			{['summarize', 'paraphase', 'change_tone'].map((type) => (
				<Button
					key={type}
					variant="secondary"
					onClick={() =>
						propRef.updatePromptText({ index: loopIndex, type })
					}
					disabled={!isPro || !providerLicenseStatus}
				>
					{type === 'summarize' &&
						__('Summarize', 'feedzy-rss-feeds')}
					{type === 'paraphase' &&
						__('Paraphrase', 'feedzy-rss-feeds')}
					{type === 'change_tone' &&
						__('Change tone', 'feedzy-rss-feeds')}
				</Button>
			))}
		</div>
	</BaseControl>
);

const RewriteActionItem = ({ counter, item, loopIndex, propRef }) => {
	const {
		isPro,
		isBusinessPlan,
		isAgencyPlan,
		isHighPrivileges,
		apiLicenseStatus,
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
		isPro && (isBusinessPlan || isAgencyPlan) && !providerLicenseStatus;
	const showUpgradeNotice = !isBusinessPlan && !isAgencyPlan;

	return (
		<li
			className="fz-action-control fz-chat-cpt-action"
			data-counter={counter}
		>
			<div className="fz-action-event">
				{showError && (
					<ErrorMessage
						provider={selectedProvider}
						isHighPrivileges={isHighPrivileges}
					/>
				)}
				<PanelBody
					title={__('Rewrite with AI', 'feedzy-rss-feeds')}
					icon={DragHandle}
					initialOpen={false}
				>
					<PanelRow>
						<UpgradeNotice
							higherPlanNotice={showUpgradeNotice}
							utmCampaign="action-paraphrase-chatgpt"
						/>
						<ProviderSelect
							selectedProvider={selectedProvider}
							loopIndex={loopIndex}
							propRef={propRef}
							isPro={isPro}
						/>
						<ModelSelect
							selectedProvider={selectedProvider}
							selectedAIModel={selectedAIModel}
							defaultModel={defaultModel}
							loopIndex={loopIndex}
							propRef={propRef}
							isPro={isPro}
							providerLicenseStatus={providerLicenseStatus}
						/>
						<PromptControls
							item={item}
							loopIndex={loopIndex}
							propRef={propRef}
							isPro={isPro}
							providerLicenseStatus={providerLicenseStatus}
							selectedProvider={selectedProvider}
						/>
					</PanelRow>
				</PanelBody>
			</div>
			<div className="fz-trash-action">
				<button
					type="button"
					onClick={() => propRef.removeCallback(loopIndex)}
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
};

export default RewriteActionItem;

import {
	BaseControl,
	SelectControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

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
                help={
                    __(
                        'Tip: High-reasoning or heavier models may time out on massive articles. If results are not generated correctly, try selecting a faster, lightweight model from the list.',
                        'feedzy-rss-feeds'
                    )
                }
            >
                {Array.isArray(window.feedzyData.recommendedAIModels) &&
                    window.feedzyData.recommendedAIModels.length > 0 && (
                        <optgroup
                            label={__(
                                'Recommended models',
                                'feedzy-rss-feeds'
                            )}
                        >
                            {window.feedzyData.recommendedAIModels.map((model) => (
                                <option key={model} value={model}>
                                    {model}
                                </option>
                            ))}
                        </optgroup>
                    )
                }
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

export default ModelSelect;
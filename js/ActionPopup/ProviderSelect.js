import {
	BaseControl,
	SelectControl,
} from '@wordpress/components';

import { __ } from "@wordpress/i18n";

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

export default ProviderSelect;
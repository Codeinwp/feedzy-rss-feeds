/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import {
	BlockControls,
	store as blockEditorStore,
} from '@wordpress/block-editor';

import { ToolbarButton, ToolbarGroup } from '@wordpress/components';

import { createHigherOrderComponent } from '@wordpress/compose';

import { useSelect, useDispatch } from '@wordpress/data';

import { addFilter } from '@wordpress/hooks';

const defaultImage = window.feedzyData.defaultImage;

const withFeedzyLoopImage = createHigherOrderComponent((BlockEdit) => {
	return (props) => {
		if ('core/image' !== props.name) {
			return <BlockEdit {...props} />;
		}

		const isLoopChild = useSelect((select) => {
			return (
				select(blockEditorStore).getBlockParentsByBlockName(
					props.clientId,
					'feedzy-rss-feeds/loop'
				).length > 0
			);
		});
		console.log(props);

		// if (props.context?.['feedzy-rss-feeds/feedItem']) {
		// 	props.attributes.url =
		// 		props.context?.['feedzy-rss-feeds/feedItem']?.item_img_path;
		// }

		return (
			<>
				<BlockEdit {...props} />

				{isLoopChild && (
					<BlockControls>
						<ToolbarGroup>
							<ToolbarButton
								onClick={() => {
									props.setAttributes({
										metadata: {
											bindings: {
												url: {
													source: 'feedzy-rss-feeds/feed',
												},
											},
										},
									});
								}}
							>
								{__('Use as Feed Image', 'feedzy-rss-feeds')}
							</ToolbarButton>
						</ToolbarGroup>
					</BlockControls>
				)}
			</>
		);
	};
}, 'withMasonryExtension');

addFilter('editor.BlockEdit', 'feedzy-loop/image', withFeedzyLoopImage);

function addCustomAttributes(settings, name) {
	if ('core/image' === name) {
		settings.attributes = {
			...settings.attributes,
			feedzyTag: {
				type: 'string',
			},
		};
		const context = new Set(settings?.usesContext ?? []);
		context.add('feedzy-rss-feeds/feedItem');
		settings.usesContext = Array.from(context);
	}

	return settings;
}

addFilter(
	'blocks.registerBlockType',
	'feedzy-loop/attributes',
	addCustomAttributes
);

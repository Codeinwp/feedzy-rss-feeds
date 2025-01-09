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

import { useSelect } from '@wordpress/data';

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

		return (
			<>
				<BlockEdit {...props} />

				{isLoopChild && (
					<BlockControls>
						<ToolbarGroup>
							<ToolbarButton
								onClick={() => {
									props.setAttributes({
										url: defaultImage,
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

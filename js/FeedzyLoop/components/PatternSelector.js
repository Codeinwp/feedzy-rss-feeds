/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import { createBlocksFromInnerBlocksTemplate } from '@wordpress/blocks';

import {
	store as blockEditorStore,
	BlockPreview,
} from '@wordpress/block-editor';

import { Modal } from '@wordpress/components';

import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies.
 */
import variations from '../variations';

const PatternSelector = ({ setOpen }) => {
	const currentBlock = useSelect((select) =>
		select(blockEditorStore).getSelectedBlock()
	);

	const { clearSelectedBlock, replaceBlock } = useDispatch(blockEditorStore);

	return (
		<Modal
			title={__('Choose a Pattern', 'feedzy-rss-feeds')}
			onRequestClose={() => setOpen(false)}
			size="fill"
		>
			<div className="fz-pattern-selector">
				{variations.map((variation) => {
					const block = {
						...currentBlock,
						attributes: {
							feed: currentBlock?.attributes?.feed,
							...variation?.attributes,
						},
						innerBlocks: createBlocksFromInnerBlocksTemplate(
							variation?.innerBlocks
						),
					};

					const onClick = () => {
						replaceBlock(currentBlock.clientId, block);
						clearSelectedBlock();
						setOpen(false);
					};

					return (
						<div
							key={variation.name}
							onClick={onClick}
							onKeyDown={(e) => {
								if (e.key === 'Enter' || e.key === ' ') {
									onClick();
								}
							}}
							role="button"
							tabIndex="0"
							className="fz-pattern"
						>
							<BlockPreview blocks={block} viewportWidth={1400} />

							<div>{variation.title}</div>
						</div>
					);
				})}
			</div>
		</Modal>
	);
};

export default PatternSelector;

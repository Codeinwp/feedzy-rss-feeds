/* eslint-disable @wordpress/no-unsafe-wp-apis */
/**
 * WordPress dependencies.
 */
import {
	store as blocksStore,
	createBlocksFromInnerBlocksTemplate,
	serialize,
} from '@wordpress/blocks';

import {
	store as blockEditorStore,
	__experimentalBlockVariationPicker as BlockVariationPicker,
	useBlockProps,
	InnerBlocks,
} from '@wordpress/block-editor';

import {
	Placeholder as BlockEditorPlaceholder,
	Spinner,
} from '@wordpress/components';

import { useDispatch, useSelect } from '@wordpress/data';

import { useState } from '@wordpress/element';

import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies.
 */
import metadata from './block.json';
import Placeholder from './placeholder';
import Controls from './controls';

const { name } = metadata;

const LoadingResponsePlaceholder = () => (
	<BlockEditorPlaceholder>
		<Spinner />
	</BlockEditorPlaceholder>
);

const Edit = ({ attributes, setAttributes, clientId }) => {
	const blockProps = useBlockProps();

	const [isEditing, setIsEditing] = useState(!attributes?.feed?.source);
	const [isPreviewing, setIsPreviewing] = useState(false);

	const { clearSelectedBlock, replaceInnerBlocks } =
		useDispatch(blockEditorStore);

	const isSelected = useSelect(
		(select) => {
			const { isBlockSelected, hasSelectedInnerBlock } =
				select(blockEditorStore);
			return (
				isBlockSelected(clientId) ||
				hasSelectedInnerBlock(clientId, true)
			);
		},
		[clientId]
	);

	const innerBlocksContent = useSelect(
		(select) => {
			const { getBlock } = select(blockEditorStore);
			const block = getBlock(clientId);

			return serialize(block?.innerBlocks) ?? '';
		},
		[clientId]
	);

	const hasInnerBlocks = useSelect(
		(select) => 0 < select(blockEditorStore).getBlocks(clientId).length,
		[clientId]
	);

	const variations = useSelect((select) => {
		const { getBlockVariations } = select(blocksStore);
		return getBlockVariations(name, 'block');
	}, []);

	const defaultVariation = useSelect((select) => {
		const { getDefaultBlockVariation } = select(blocksStore);
		return getDefaultBlockVariation(name, 'block');
	}, []);

	const onSaveFeed = () => {
		setIsEditing(false);
	};

	const onChangeQuery = ({ type, value }) => {
		setAttributes({
			query: {
				...attributes.query,
				[type]: value,
			},
		});
	};

	const onChangeLayout = ({ type, value }) => {
		setAttributes({
			layout: {
				...attributes.layout,
				[type]: value,
			},
		});
	};

	if (isEditing) {
		return (
			<div {...blockProps}>
				<Placeholder
					attributes={attributes}
					setAttributes={setAttributes}
					onSaveFeed={onSaveFeed}
				/>
			</div>
		);
	}

	if ((!isSelected || isPreviewing) && innerBlocksContent) {
		return (
			<>
				<Controls
					attributes={attributes}
					isEditing={isEditing}
					isPreviewing={isPreviewing}
					setAttributes={setAttributes}
					onChangeLayout={onChangeLayout}
					onChangeQuery={onChangeQuery}
					setIsEditing={setIsEditing}
					setIsPreviewing={setIsPreviewing}
				/>

				<div {...blockProps}>
					<ServerSideRender
						block="feedzy-rss-feeds/loop"
						attributes={{
							...attributes,
							innerBlocksContent,
						}}
						LoadingResponsePlaceholder={LoadingResponsePlaceholder}
					/>
				</div>
			</>
		);
	}

	return (
		<>
			<Controls
				attributes={attributes}
				isEditing={isEditing}
				isPreviewing={isPreviewing}
				setAttributes={setAttributes}
				onChangeLayout={onChangeLayout}
				onChangeQuery={onChangeQuery}
				setIsEditing={setIsEditing}
				setIsPreviewing={setIsPreviewing}
			/>

			<div {...blockProps}>
				{hasInnerBlocks ? (
					<InnerBlocks />
				) : (
					<BlockVariationPicker
						variations={variations}
						onSelect={(nextVariation = defaultVariation) => {
							if (nextVariation) {
								setAttributes(nextVariation.attributes);
								replaceInnerBlocks(
									clientId,
									createBlocksFromInnerBlocksTemplate(
										nextVariation.innerBlocks
									),
									true
								);
								clearSelectedBlock();
							}
						}}
					/>
				)}
			</div>
		</>
	);
};

export default Edit;

/* eslint-disable @wordpress/no-unsafe-wp-apis */
/**
 * WordPress dependencies.
 */

import { __, sprintf } from '@wordpress/i18n';

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
	Notice,
	Spinner,
} from '@wordpress/components';

import { useDispatch, useSelect } from '@wordpress/data';

import { Fragment, useEffect, useState } from '@wordpress/element';

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
	const [isPreviewing, setIsPreviewing] = useState(true);
	const [showPreviewNotice, setShowPreviewNotice] = useState(false);

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

	useEffect(() => {
		const isPreviewNoticeHidden = localStorage.getItem(
			'feedzy-hide-preview-notice'
		);
		if (!isPreviewNoticeHidden) {
			setShowPreviewNotice(true);
		}
	}, []);

	const setVariations = (nextVariation = defaultVariation) => {
		if (nextVariation) {
			setAttributes({
				layout: {
					name: nextVariation.name,
				},
				...nextVariation.attributes,
			});

			replaceInnerBlocks(
				clientId,
				createBlocksFromInnerBlocksTemplate(nextVariation.innerBlocks),
				true
			);
			clearSelectedBlock();
		}
	};

	let blockContent;

	if (isEditing) {
		blockContent = (
			<Placeholder
				attributes={attributes}
				setAttributes={setAttributes}
				onSaveFeed={onSaveFeed}
			/>
		);
	} else if ((!isSelected || isPreviewing) && innerBlocksContent) {
		blockContent = (
			<Fragment>
				{showPreviewNotice && (
					<Notice
						status="info"
						isDismissible={true}
						onRemove={() => {
							setShowPreviewNotice(false);
							localStorage.setItem(
								'feedzy-hide-preview-notice',
								'true'
							);
						}}
					>
						<p>
							<strong>
								{__(
									"You're in Preview Mode – This shows how your feed will look to visitors.",
									'feedzy-rss-feeds'
								)}
							</strong>
						</p>
						<p>
							{sprintf(
								// translators: %1$s is button label "Hide Preview".
								__(
									'To customize each element (title, meta, description) and adjust layouts, spacing, colors, and typography, click "%1$s" in the toolbar above to enter the advanced editor.',
									'feedzy-rss-feeds'
								),
								__('Hide Preview', 'feedzy-rss-feeds')
							)}
						</p>
					</Notice>
				)}
				<ServerSideRender
					block="feedzy-rss-feeds/loop"
					attributes={{
						...attributes,
						innerBlocksContent,
					}}
					LoadingResponsePlaceholder={LoadingResponsePlaceholder}
				/>
			</Fragment>
		);
	} else if (!hasInnerBlocks && !isEditing) {
		blockContent = (
			<BlockVariationPicker
				variations={variations}
				onSelect={setVariations}
			/>
		);
	} else {
		blockContent = <InnerBlocks />;
	}

	return (
		<Fragment>
			<Controls
				attributes={attributes}
				isEditing={isEditing}
				isPreviewing={isPreviewing}
				setAttributes={setAttributes}
				onChangeLayout={onChangeLayout}
				onChangeQuery={onChangeQuery}
				setIsEditing={setIsEditing}
				setIsPreviewing={setIsPreviewing}
				variations={variations}
				setVariations={setVariations}
			/>
			<div {...blockProps}>{blockContent}</div>
		</Fragment>
	);
};

export default Edit;

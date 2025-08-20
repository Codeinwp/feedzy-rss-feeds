/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { MediaUpload } from '@wordpress/block-editor';
import {
	Button,
	ResponsiveWrapper,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalHStack as HStack,
} from '@wordpress/components';

export function FallbackImageLoader({
	imageValue,
	onChangeImage,
	onRemoveImage,
	label = __('Fallback image if no image is found.', 'feedzy-rss-feeds'),
}) {
	const handleSelect = (media) => {
		const imageData = {
			url: media.url,
			width: media.width,
			height: media.height,
			id: media.id,
		};
		onChangeImage(imageData);
	};

	const handleRemove = () => {
		onRemoveImage();
	};

	return (
		<div className="feedzy-blocks-base-control">
			<label
				className="blocks-base-control__label"
				htmlFor="inspector-media-upload"
			>
				{label}
			</label>
			<MediaUpload
				type="image"
				id="inspector-media-upload"
				value={imageValue}
				onSelect={handleSelect}
				render={({ open }) => (
					<Fragment>
						{imageValue !== undefined && (
							<ResponsiveWrapper
								naturalWidth={imageValue.width}
								naturalHeight={imageValue.height}
							>
								<img
									src={imageValue.url}
									alt={__(
										'Featured image',
										'feedzy-rss-feeds'
									)}
								/>
							</ResponsiveWrapper>
						)}

						<HStack>
							{imageValue !== undefined && (
								<Button
									isLarge
									isSecondary
									onClick={handleRemove}
									style={{
										marginTop: '10px',
									}}
								>
									{__('Remove Image', 'feedzy-rss-feeds')}
								</Button>
							)}

							<Button
								isLarge
								isPrimary
								onClick={open}
								style={{
									marginTop: '10px',
								}}
								className={
									imageValue === undefined &&
									'feedzy_image_upload'
								}
							>
								{__('Upload Image', 'feedzy-rss-feeds')}
							</Button>
						</HStack>
					</Fragment>
				)}
			/>
		</div>
	);
}

export default FallbackImageLoader;

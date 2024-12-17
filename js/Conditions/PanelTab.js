/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import { Button } from '@wordpress/components';

import { useState } from '@wordpress/element';

const PanelTab = ({
	label,
	onDelete,
	initialOpen = false,
	children
}) => {
	const [ isOpen, setOpen ] = useState( initialOpen );

	return (
		<div className="fz-panel-tab">
			<div className="fz-panel-tab__header">
				<div
                    className="fz-panel-tab__header__label"
                    onClick={ () => setOpen( ! isOpen ) }
                >
                    { label }
                </div>

				<Button
					icon={ isOpen ? 'arrow-up-alt2' : 'arrow-down-alt2' }
					label={ isOpen ? __( 'Close Settings', 'feedzy-rss-feeds' ) : __( 'Open Settings', 'feedzy-rss-feeds' ) }
					showTooltip={ true }
					onClick={ () => setOpen( ! isOpen ) }
				/>

				<Button
					icon="no-alt"
					label={ __( 'Delete', 'feedzy-rss-feeds' ) }
					showTooltip={ true }
					onClick={ onDelete }
				/>
			</div>

			{ isOpen && <div className="fz-panel-tab__content">{ children }</div> }
		</div>
	);
};

export default PanelTab;

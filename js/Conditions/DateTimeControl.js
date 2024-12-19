/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import {
	BaseControl,
	Button,
	DateTimePicker,
	Dropdown
} from '@wordpress/components';

import {
	format,
	__experimentalGetSettings
} from '@wordpress/date';

const DateTimeControl = ({
	label,
	value,
	onChange
}) => {
	const settings = __experimentalGetSettings();

	return (
		<BaseControl
			label={ label }
		>
			<Dropdown
				position="bottom left"
				renderToggle={ ({ onToggle, isOpen }) => (
					<>
						<Button
							onClick={ onToggle }
							variant="secondary"
							aria-expanded={ isOpen }
						>
							{ value ? format( settings.formats.datetime, value ) : __( 'Select Date', 'feedzy-rss-feeds' ) }
						</Button>
					</>
				) }
				renderContent={ () => (
					<DateTimePicker
						currentDate={ value }
						onChange={ onChange }
					/>
				) }
			/>
		</BaseControl>
	);
};

export default DateTimeControl;

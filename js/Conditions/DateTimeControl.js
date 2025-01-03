/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import {
	BaseControl,
	Button,
	DateTimePicker,
	Dropdown,
} from '@wordpress/components';

// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
import { format, __experimentalGetSettings } from '@wordpress/date';

const DateTimeControl = ({ index, label, value, onChange }) => {
	const settings = __experimentalGetSettings();

	return (
		<BaseControl label={label} id={`date-time-control-${index}`}>
			<Dropdown
				position="bottom left"
				renderToggle={({ onToggle, isOpen }) => (
					<>
						<Button
							onClick={onToggle}
							variant="secondary"
							aria-expanded={isOpen}
						>
							{value
								? format(settings.formats.datetime, value)
								: __('Select Date', 'feedzy-rss-feeds')}
						</Button>
					</>
				)}
				renderContent={() => (
					<DateTimePicker currentDate={value} onChange={onChange} />
				)}
			/>
		</BaseControl>
	);
};

export default DateTimeControl;

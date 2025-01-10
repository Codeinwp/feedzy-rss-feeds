/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import { useState, useEffect, useRef } from '@wordpress/element';

const FeedControl = ({ value, options, onChange }) => {
	const [isOpen, setIsOpen] = useState(false);
	const [inputValue, setInputValue] = useState('');
	const [selectedOption, setSelectedOption] = useState(null);
	const dropdownRef = useRef(null);

	// Initialize component state based on value prop
	useEffect(() => {
		if (value?.type === 'group' && value.source) {
			const selected = options.find((opt) => opt.value === value.source);
			setSelectedOption(selected || null);
			setInputValue('');
		} else if (value?.type === 'url' && Array.isArray(value.source)) {
			setSelectedOption(null);
			setInputValue(value.source.join(', '));
		}
	}, [value, options]);

	useEffect(() => {
		const handleClickOutside = (event) => {
			if (
				dropdownRef.current &&
				!dropdownRef.current.contains(event.target)
			) {
				setIsOpen(false);
			}
		};

		document.addEventListener('mousedown', handleClickOutside);
		return () =>
			document.removeEventListener('mousedown', handleClickOutside);
	}, []);

	const handleSelectOption = (option) => {
		setSelectedOption(option);
		setInputValue('');
		setIsOpen(false);
		onChange({
			type: 'group',
			source: option.value,
		});
	};

	const handleInputChange = (e) => {
		const current = e.target.value;
		setInputValue(current);
		setSelectedOption(null);
	};

	const handleInputBlur = () => {
		onChange({
			type: 'url',
			source: inputValue
				? inputValue
						.split(',')
						.map((url) => url.trim())
						.filter(Boolean)
				: [],
		});
	};

	const handleClear = () => {
		setSelectedOption(null);
		setInputValue('');
		onChange({
			type: 'url',
			source: [],
		});
	};

	return (
		<div className="fz-url-category-input" ref={dropdownRef}>
			<input
				type="text"
				value={selectedOption ? selectedOption.label : inputValue}
				onChange={handleInputChange}
				onBlur={handleInputBlur}
				placeholder={__(
					'Enter URLs or select a Feed Group',
					'feedzy-rss-feeds'
				)}
				disabled={selectedOption !== null}
				className="fz-input-field"
			/>
			<div className="fz-buttons-container">
				{selectedOption && (
					<button
						onClick={handleClear}
						className="fz-clear-button"
						title={__('Clear', 'feedzy-rss-feeds')}
					>
						<svg
							width="14"
							height="14"
							viewBox="0 0 24 24"
							fill="none"
							xmlns="http://www.w3.org/2000/svg"
						>
							<path
								d="M18 6L6 18M6 6l12 12"
								stroke="currentColor"
								strokeWidth="2"
								strokeLinecap="round"
								strokeLinejoin="round"
							/>
						</svg>
					</button>
				)}
				<button
					onClick={() => setIsOpen(!isOpen)}
					className="fz-dropdown-button"
					title={__('Select Feed Group', 'feedzy-rss-feeds')}
				>
					<svg
						width="12"
						height="12"
						viewBox="0 0 12 12"
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
						style={{
							transform: isOpen
								? 'rotate(180deg)'
								: 'rotate(0deg)',
							transition: 'transform 0.2s',
						}}
					>
						<path
							d="M2 4L6 8L10 4"
							stroke="currentColor"
							strokeWidth="2"
							strokeLinecap="round"
							strokeLinejoin="round"
						/>
					</svg>
				</button>
			</div>

			{isOpen && (
				<div className="fz-dropdown-menu">
					{options.map((option) => (
						<button
							key={option.value}
							onClick={() => handleSelectOption(option)}
							className={`fz-dropdown-item ${selectedOption?.value === option.value ? 'fz-selected' : ''}`}
						>
							{option.label}
						</button>
					))}
				</div>
			)}
		</div>
	);
};

export default FeedControl;

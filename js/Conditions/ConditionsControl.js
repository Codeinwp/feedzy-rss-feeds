/**
 * External dependencies.
 */
import classNames from 'classnames';

/**
 * WordPress dependencies.
 */
import { __, sprintf } from '@wordpress/i18n';
import { Button, SelectControl, TextControl } from '@wordpress/components';
import { Icon, plus } from '@wordpress/icons';
import { useState } from '@wordpress/element';

/**
 * Internal dependencies.
 */
import PanelTab from './PanelTab';
import DateTimeControl from './DateTimeControl';

const isPro = window.feedzyData.isPro;
const SUPPORTED_FIELDS = [
	{
		label: __('Title', 'feedzy-rss-feeds'),
		value: 'title',
	},
	{
		label: __('Description', 'feedzy-rss-feeds'),
		value: 'description',
		disabled: !isPro,
	},
	{
		label: __('Full Content', 'feedzy-rss-feeds'),
		value: 'fullcontent',
		disabled: !isPro,
	},
	{
		label: __('Author', 'feedzy-rss-feeds'),
		value: 'author',
		unsupportedOperators: ['greater_than', 'gte', 'less_than', 'lte'],
		disabled: !isPro,
	},
	{
		label: __('Date', 'feedzy-rss-feeds'),
		value: 'date',
		unsupportedOperators: [
			'has_value',
			'equals',
			'not_equals',
			'empty',
			'contains',
			'not_contains',
		],
		disabled: !isPro,
	},
	{
		label: __('Featured Image', 'feedzy-rss-feeds'),
		value: 'featured_image',
		unsupportedOperators: ['greater_than', 'gte', 'less_than', 'lte'],
		disabled: !isPro,
	},
	{
		label: __('Link', 'feedzy-rss-feeds'),
		value: 'link',
		unsupportedOperators: ['greater_than', 'gte', 'less_than', 'lte'],
		disabled: !isPro,
	},
];

const ConditionsControl = ({ conditions, setConditions }) => {
	const [modalOpen, setModelOpen] = useState(false);
	const onChangeMatch = (value) => {
		setConditions({
			...conditions,
			match: value,
		});
	};

	const el = document.querySelector('.editor-sidebar__panel-tabs');
	const addCondition = () => {
		if (!isPro && 1 <= conditions.conditions.length) {
			// the Inspector panel use sticky position with their own stacking context,
			// which causes them to appear above our popup overlay. We set their z-index to 0 so the popup covers them.
			if (el) {
				el.style.zIndex = 0;
			}
			setModelOpen(true);
			return;
		}

		const conditionsCopy = [...conditions.conditions];

		conditionsCopy.push({
			field: SUPPORTED_FIELDS[0].value,
			operator: 'contains',
		});

		setConditions({
			...conditions,
			conditions: conditionsCopy,
		});
	};

	const removeCondition = (index) => {
		const conditionsCopy = [...conditions.conditions];
		conditionsCopy.splice(index, 1);

		setConditions({
			...conditions,
			conditions: conditionsCopy,
		});
	};

	const onChangeCondition = (index, value, key) => {
		const conditionsCopy = [...conditions.conditions];

		conditionsCopy[index][key] = value;

		// We need to make sure we don't have unsupported operators for the selected field.
		if (key === 'field') {
			const field = SUPPORTED_FIELDS.find((i) => i.value === value);
			if (
				field.unsupportedOperators?.includes(
					conditionsCopy[index].operator
				)
			) {
				conditionsCopy[index].operator = Object.keys(
					window?.feedzyConditionsData?.operators
				).filter((i) => !field.unsupportedOperators?.includes(i))[0];
			}

			conditionsCopy[index].value = '';
		}

		setConditions({
			...conditions,
			conditions: conditionsCopy,
		});
	};

	const closeModal = () => {
		if (el) {
			el.style.zIndex = 0;
		}
		setModelOpen(false);
	};

	return (
		<>
			<div className="fz-condition-control">
				<SelectControl
					label={__('Include If', 'feedzy-rss-feeds')}
					value={conditions.match}
					options={[
						{
							label: __(
								'All conditions are met',
								'feedzy-rss-feeds'
							),
							value: 'all',
						},
						{
							label: __(
								'Any condition is met',
								'feedzy-rss-feeds'
							),
							value: 'any',
						},
					]}
					onChange={onChangeMatch}
				/>

				{conditions.conditions.map((condition, index) => {
					const field = SUPPORTED_FIELDS.find(
						(i) => i.value === condition.field
					);
					const operators = Object.keys(
						window?.feedzyConditionsData?.operators
					).filter(
						(key) => !field?.unsupportedOperators?.includes(key)
					);

					return (
						<PanelTab
							key={index}
							label={`${field?.label} ${window.feedzyConditionsData.operators[condition.operator]} ${condition?.value || ''}`}
							onDelete={() => removeCondition(index)}
							initialOpen={index === 0}
						>
							<SelectControl
								label={__('Field', 'feedzy-rss-feeds')}
								value={condition?.field}
								options={SUPPORTED_FIELDS}
								onChange={(value) =>
									onChangeCondition(index, value, 'field')
								}
							/>

							<SelectControl
								label={__(
									'Compare Operator',
									'feedzy-rss-feeds'
								)}
								options={operators.map((key) => ({
									label: window.feedzyConditionsData
										.operators[key],
									value: key,
								}))}
								help={
									['contains', 'not_contains'].includes(
										condition?.operator
									)
										? __(
												'You can use comma(,) and plus(+) keyword.',
												'feedzy-rss-feeds'
											)
										: ''
								}
								value={condition?.operator}
								onChange={(value) =>
									onChangeCondition(index, value, 'operator')
								}
							/>

							{!['has_value', 'empty'].includes(
								condition?.operator
							) && (
								<>
									{condition?.field === 'date' ? (
										<DateTimeControl
											id={index}
											label={__(
												'Value',
												'feedzy-rss-feeds'
											)}
											value={condition?.value}
											onChange={(value) =>
												onChangeCondition(
													index,
													value,
													'value'
												)
											}
										/>
									) : (
										<TextControl
											label={__(
												'Value',
												'feedzy-rss-feeds'
											)}
											value={condition?.value}
											onChange={(value) =>
												onChangeCondition(
													index,
													value,
													'value'
												)
											}
										/>
									)}
								</>
							)}
						</PanelTab>
					);
				})}

				<div
					className={classNames('fz-action-btn mt-24', {
						'is-upsell':
							!isPro && 1 <= conditions.conditions.length,
					})}
				>
					<Button
						variant="secondary"
						onClick={addCondition}
						className="fz-new-action"
					>
						{__('Add Condition', 'feedzy-rss-feeds')}{' '}
						<Icon icon={plus} />
					</Button>
				</div>
			</div>
			{modalOpen && (
				<div
					id="feedzy-add-filter-condition"
					className="wp-core-ui feedzy-modal"
				>
					<div className="modal-content">
						<button
							className="fz-notice close-modal"
							onClick={closeModal}
						>
							<span className="dashicons dashicons-no-alt"></span>
							<span className="screen-reader-text">
								{__('Dismiss this dialog', 'feedzy-rss-feeds')}
							</span>
						</button>
						<div className="modal-header">
							<h2>
								{__(
									'Upgrade to Use Unlimited Conditions',
									'feedzy-rss-feeds'
								)}
							</h2>
							<p style={{ color: 'red' }}>
								{__(
									'Filter Condition limit reached',
									'feedzy-rss-feeds'
								)}
								<span>
									{'(' +
										sprintf(
											// translators: %1$s is the number of imports used, %2$s is the total number of imports allowed.
											__(
												'%1$s/%2$s used',
												'feedzy-rss-feeds'
											),
											'1',
											'1'
										) +
										')'}
								</span>
							</p>
						</div>
						<div className="modal-body">
							<p>
								{__(
									"Your current plan supports only one filter condition. Upgrade to unlock unlimited import configurations and make the most of Feedzy's powerful features!",
									'feedzy-rss-feeds'
								)}
							</p>
						</div>
						<div className="modal-footer">
							<div className="button-container">
								<a
									href="https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/?utm_source=wpadmin&utm_medium=post&utm_campaign=filterCondition&utm_content=feedzy-rss-feeds"
									target="_blank"
									rel="noreferrer "
									className="button button-primary button-large"
								>
									{__('Upgrade to PRO', 'feedzy-rss-feeds')}
								</a>
							</div>
							<span>
								{__(
									'30-day money-back guarantee. No questions asked.',
									'feedzy-rss-feeds'
								)}
							</span>
						</div>
					</div>
				</div>
			)}
		</>
	);
};

export default ConditionsControl;

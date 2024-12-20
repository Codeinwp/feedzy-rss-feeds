/**
 * WordPress dependencies.
 */
import domReady from '@wordpress/dom-ready';

import { createRoot, useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies.
 */
import ConditionsControl from './ConditionsControl';

const dummyConditions = {
	match: 'all',
	conditions: [
		{
			field: 'title',
			operator: 'contains',
			value: 'Sports',
		},
	],
};

const App = () => {
	const [conditions, setConditions] = useState({
		conditions: [],
		match: 'all',
	});

	useEffect(() => {
		if (!feedzyData.isPro) {
			setConditions(dummyConditions);
			return;
		}

		const field = document.getElementById('feed-post-filters-conditions');
		if (field && field.value) {
			const parsedConditions = JSON.parse(field.value);
			if (parsedConditions && parsedConditions.conditions) {
				parsedConditions.conditions = parsedConditions.conditions.map(
					(condition) => {
						// We do all these schananigans to make sure we JS doesn't confuse regex for special characters.
						if (typeof condition.value === 'string') {
							condition.value = condition.value
								.replace(/\u0008/g, '\\b')
								.replace(/\u000C/g, '\\f')
								.replace(/\n/g, '\\n')
								.replace(/\r/g, '\\r')
								.replace(/\t/g, '\\t');
						}
						return condition;
					}
				);
				setConditions(parsedConditions);
			} else {
				setConditions({ conditions: [], match: 'all' });
			}
		} else {
			setConditions({ conditions: [], match: 'all' });
		}
	}, []);

	useEffect(() => {
		if (!feedzyData.isPro) {
			return;
		}

		document.getElementById('feed-post-filters-conditions').value =
			JSON.stringify(conditions);
	}, [conditions]);

	return (
		<ConditionsControl
			conditions={conditions}
			setConditions={setConditions}
		/>
	);
};

domReady(() => {
	const root = createRoot(document.getElementById('fz-conditions'));
	root.render(<App />);
});

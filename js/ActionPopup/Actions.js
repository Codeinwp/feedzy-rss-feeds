import React from 'react';
import ReactDOM from 'react-dom';
import { SortableContainer } from 'react-sortable-hoc';
import SortableItem from './SortableItem';
import { Panel } from '@wordpress/components';

const Actions = ( props ) => {
	if ( props.data && props.data.length === 0 ) {
		return(
			<></>
		);
	}

	return (
		<>
			<Panel header="" className="fz-action-panel" initialOpen={ false }>
				<ul>
					{props.data.map((value, index) => (
						<SortableItem key={`item-${index}`} index={index} item={value} loopIndex={index} propRef={props}/>
					))}
				</ul>
			</Panel>
		</>
	);
};
export default SortableContainer(Actions);
/**
 * WordPress dependencies.
 */
import domReady from '@wordpress/dom-ready';

import {
    createRoot,
    useEffect,
    useState
} from '@wordpress/element';

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
            value: 'Sports'
        }
    ]
};

const App = () => {
    const [ conditions, setConditions ] = useState( {
        conditions: [],
        match: 'all'
    } );

    useEffect( () => {
        if ( ! feedzyData.isPro ) {
            setConditions( dummyConditions );
            return
        }

        const field = document.getElementById( 'feed-post-filters-conditions' );
        if ( field && field.value ) {
            const parsedConditions = JSON.parse( field.value );
            setConditions( parsedConditions && parsedConditions.conditions ? parsedConditions : { conditions: [], match: 'all' } );
        }
    }, [] );

    useEffect( () => {
        if ( ! feedzyData.isPro ) {
            return
        }

        document.getElementById( 'feed-post-filters-conditions' ).value = JSON.stringify( conditions );
    }, [ conditions ] );

    return (
        <ConditionsControl
            conditions={ conditions }
            setConditions={ setConditions }
        />
    );
};

domReady( () => {
	const root = createRoot( document.getElementById( 'fz-conditions' ) );
	root.render( <App /> );
});

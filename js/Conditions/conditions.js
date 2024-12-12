/**
 * WordPress dependencies.
 */
import { Disabled } from '@wordpress/components';

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

const App = () => {
    const [ conditions, setConditions ] = useState( {
        conditions: [],
        match: 'all'
    } );

    useEffect( () => {
        const field = document.getElementById( 'feed-post-filters-conditions' );
        if ( field && field.value ) {
            const parsedConditions = JSON.parse( field.value );
            console.log( parsedConditions && parsedConditions.conditions ? parsedConditions : { conditions: [], match: 'all' } )
            setConditions( parsedConditions && parsedConditions.conditions ? parsedConditions : { conditions: [], match: 'all' } );
        }
    }, [] );

    useEffect( () => {
        document.getElementById( 'feed-post-filters-conditions' ).value = JSON.stringify( conditions );
    }, [ conditions ] );

    if ( ! feedzyData.isPro ) {
        return (
            <Disabled>
                <ConditionsControl
                    conditions={ conditions }
                    setConditions={ setConditions }
                />
            </Disabled>
        );
    }

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

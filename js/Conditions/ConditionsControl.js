/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import {
    Button,
    SelectControl,
    TextControl
} from '@wordpress/components';

import {
    Icon,
	plus
} from '@wordpress/icons';

/**
 * Internal dependencies.
 */
import PanelTab from './PanelTab';
import DateTimeControl from './DateTimeControl';

const SUPPORTED_FIELDS = [
    {
        label: __( 'Title', 'feedzy-rss-feeds' ),
        value: 'title'
    },
    {
        label: __( 'Description', 'feedzy-rss-feeds' ),
        value: 'description'
    },
    {
        label: __( 'Full Content', 'feedzy-rss-feeds' ),
        value: 'fullcontent'
    },
    {
        label: __( 'Author', 'feedzy-rss-feeds' ),
        value: 'author',
        unsupportedOperators: [
            'greater_than',
            'gte',
            'less_than',
            'lte'
        ]
    },
    {
        label: __( 'Date', 'feedzy-rss-feeds' ),
        value: 'date',
        unsupportedOperators: [
            'has_value',
            'equals',
            'not_equals',
            'empty',
            'contains',
            'not_contains'
        ]
    },
    {
        label: __( 'Featured Image', 'feedzy-rss-feeds' ),
        value: 'featured_image',
        unsupportedOperators: [
            'greater_than',
            'gte',
            'less_than',
            'lte'
        ]
    }
];

const ConditionsControl = ({
    conditions,
    setConditions
}) => {
    const onChangeMatch = ( value ) => {
        setConditions( {
            ...conditions,
            match: value
        } );
    };

    const addCondition = () => {
        const conditionsCopy = [ ...conditions.conditions ];

        conditionsCopy.push({
            field: SUPPORTED_FIELDS[0].value,
            operator: 'contains'
        });

        setConditions( {
            ...conditions,
            conditions: conditionsCopy
        } );
    };

    const removeCondition = ( index ) => {
        const conditionsCopy = [ ...conditions.conditions ];
        conditionsCopy.splice( index, 1 );

        setConditions( {
            ...conditions,
            conditions: conditionsCopy
        } );
    };

    const onChangeCondtion = ( index, value, key ) => {
        const conditionsCopy = [ ...conditions.conditions ];

        conditionsCopy[index][key] = value;

        // We need to make sure we don't have unsupported operators for the selected field.
        if ( key === 'field' ) {
            const field = SUPPORTED_FIELDS.find( field => field.value === value );
            if ( field.unsupportedOperators?.includes( conditionsCopy[index].operator ) ) {
                conditionsCopy[index].operator = Object.keys( window?.feedzyConditionsData?.operators ).filter( key => ! field.unsupportedOperators?.includes( key ) )[0];
            }
        }

        setConditions( {
            ...conditions,
            conditions: conditionsCopy
        } );
    };

    return (
        <div className="fz-condition-control">
            <SelectControl
                label={ __( 'Show If', 'feedzy-rss-feeds' ) }
                value={ conditions.match }
                options={ [
                    {
                        label: __( 'All', 'feedzy-rss-feeds' ),
                        value: 'all'
                    },
                    {
                        label: __( 'Any', 'feedzy-rss-feeds' ),
                        value: 'any'
                    }
                ] }
                onChange={ onChangeMatch }
            />

            { conditions.conditions.map( ( condition, index ) => {
                const field = SUPPORTED_FIELDS.find( field => field.value === condition.field );
                const operators = Object.keys( window?.feedzyConditionsData?.operators ).filter( key => ! field.unsupportedOperators?.includes( key ) );

                return (
                    <PanelTab
                        key={ index }
                        label={ `${ field?.label } ${ window.feedzyConditionsData.operators[condition.operator] } ${ condition?.value || '' }` }
                        onDelete={ () => removeCondition( index ) }
                    >
                        <SelectControl
                            label={ __( 'Field', 'feedzy-rss-feeds' ) }
                            value={ condition?.field }
                            options={ SUPPORTED_FIELDS }
                            onChange={ ( value ) => onChangeCondtion( index, value, 'field' ) }
                        />

                        <SelectControl
                            label={ __( 'Compare Operator', 'feedzy-rss-feeds' ) }
                            options={ operators.map( key => ( {
                                label: window.feedzyConditionsData.operators[key],
                                value: key
                            } ) ) }
                            help={ [ 'contains', 'not_contains' ].includes( condition?.operator ) ? __( 'You can use comma(,) and plus(+) keyword.', 'feedzy-rss-feeds' ) : '' }
                            value={ condition?.operator }
                            onChange={ ( value ) => onChangeCondtion( index, value, 'operator' ) }
                        />

                        { ! [ 'has_value', 'empty' ].includes( condition?.operator ) && (
                            <>
                                { condition?.field === 'date' ? (
                                    <DateTimeControl
                                        label={ __( 'Value', 'feedzy-rss-feeds' ) }
                                        value={ condition?.value }
                                        onChange={ ( value ) => onChangeCondtion( index, value, 'value' ) }
                                    />
                                ) : (
                                    <TextControl
                                        label={ __( 'Value', 'feedzy-rss-feeds' ) }
                                        value={ condition?.value }
                                        onChange={ ( value ) => onChangeCondtion( index, value, 'value' ) }
                                    />
                                ) }
                            </>
                        ) }
                    </PanelTab>
                );
            } ) }

            <div className="fz-action-btn mt-24">
                <Button
                    variant="secondary"
                    onClick={ addCondition }
                    className="fz-new-action"
                >
                    { __( 'Add Condition', 'feedzy-rss-feeds' ) } <Icon icon={ plus } />
                </Button>
            </div>
        </div>
    );
};

export default ConditionsControl;

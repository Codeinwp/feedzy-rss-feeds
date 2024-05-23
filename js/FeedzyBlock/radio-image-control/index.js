// jshint ignore: start

/**
 * Block dependencies
 */
import './style.scss';

/**
 * Internal dependencies
 */
const { isEmpty } = lodash;

const { BaseControl } = wp.components;

const { withInstanceId } = wp.compose;


function RadioImageControl( { label, selected, help, instanceId, onChange, disabled, options = [] } ) {
	const id = `inspector-radio-image-control-${ instanceId }`;
	const onChangeValue = ( event ) => onChange( event.target.value );

	return ! isEmpty( options ) && (
		<BaseControl label={ label } id={ id } help={ help } className="components-radio-image-control feedzy-template">
			<div className="components-radio-image-control__container">
				{ options.map( ( option, index ) =>
					<div
						key={ `${ id }-${ index }` }
						className="components-radio-image-control__option"
					>
						<input
							id={ `${ id }-${ index }` }
							className="components-radio-image-control__input"
							type="radio"
							name={ id }
							value={ option.value }
							onChange={ onChangeValue }
							checked={ option.value === selected }
							aria-describedby={ !! help ? `${ id }__help` : undefined }
							disabled={ disabled }
						/>
						<label htmlFor={ `${ id }-${ index }` } title={ option.label }>
							<img src={ option.src } />
							<span class="image-clickable"></span>
						</label>
						<span>{ option.label }</span>
					</div>
				) }
			</div>
		</BaseControl>
	);
}

export default withInstanceId( RadioImageControl );

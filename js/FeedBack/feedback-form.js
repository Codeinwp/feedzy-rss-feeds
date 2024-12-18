/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import {
	useEffect,
	useState
} from '@wordpress/element';

import {
	Button,
	Spinner,
	TextareaControl
} from '@wordpress/components';

import { __ } from '@wordpress/i18n';

const { pluginVersion } = window.feedzyObj || {};

const collectedInfo = [
	{
		name: __( 'Plugin version',  'feedzy-rss-feeds' ),
		value: pluginVersion
	},
	{
		name: __( 'Feedback', 'feedzy-rss-feeds' ),
		value: __( 'Text from the above text area', 'feedzy-rss-feeds' )
	}
];

const helpTextByStatus = {
	error: __( 'There has been an error. Your feedback couldn\'t be sent.', 'feedzy-rss-feeds' ),
	emptyFeedback: __( 'Please provide a feedback before submitting the form.', 'feedzy-rss-feeds' )
};

/**
 * Displays a button that opens a modal for sending feedback
 *
 * @param {import('./type').FeedbackFormProps} props
 * @return
 */
const FeedbackForm = ({
	source,
	status,
	setStatus
}) => {
	const [ feedback, setFeedback ] = useState( '' );
	const [ showInfo, setShowInfo ] = useState( false );

	useEffect( () => {
		const info = document.querySelector( '.fz-feedback-form .info' );
		if ( info ) {
			info.style.height = showInfo ? `${ info.querySelector( '.wrapper' )?.clientHeight }px` : '0';
		}

	}, [ showInfo ]);

	const sendFeedback = () => {
		const trimmedFeedback = feedback.trim();
		if ( 5 >= trimmedFeedback.length ) {
			setStatus( 'emptyFeedback' );
			return;
		}

		setStatus( 'loading' );
		try {
			fetch( 'https://api.themeisle.com/tracking/feedback', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json, */*;q=0.1',
					'Cache-Control': 'no-cache'
				},
				body: JSON.stringify({
					slug: 'feedzy-rss-feeds',
					version: pluginVersion,
					feedback: trimmedFeedback,
					data: {
						'feedback-area': source
					}
				})
			}).then( r => {
				if ( ! r.ok ) {
					setStatus( 'error' );
					return;
				}

				setStatus( 'submitted' );
			})?.catch( ( error ) => {
				console.warn( error.message );
				setStatus( 'error' );
			});
		} catch ( error ) {
			console.warn( error.message );
			setStatus( 'error' );
		}
	};

	return (
		<form
			className="fz-feedback-form"
			onSubmit={ e => {
				e.preventDefault();
				sendFeedback();
			} }
		>
			<TextareaControl
				className={ classnames({
					'invalid': 'emptyFeedback' === status,
					'f-error': 'error' === status
				}) }
				placeholder={ __( 'We would love to hear how we can help you better with Feedzy', 'feedzy-rss-feeds' ) }
				value={ feedback }
				rows={7}
				cols={50}
				onChange={ value => {
					setFeedback( value );
					if ( 5 < value.trim().length ) {
						setStatus( 'notSubmitted' );
					}
				} }
				help={ helpTextByStatus[status] || false }
				autoFocus
			/>
			<div className="info">
				<div className="wrapper">
					<p>{ __( 'We value privacy, that\'s why no domain name, email address or IP addresses are collected after you submit the survey. Below is a detailed view of all data that Themeisle will receive if you fill in this survey.', 'feedzy-rss-feeds' ) }</p>
					{ collectedInfo.map( ( row, index ) => {
						return (
							<div className="info-row" key={ index }>
								<p><b>{ row.name }</b></p>
								<p>{ row.value }</p>
							</div>
						);
					}) }
				</div>
			</div>
			<div className="buttons-wrap">
				<Button
					className="toggle-info"
					aria-expanded={ showInfo }
					variant="link"
					isLink
					onClick={() => setShowInfo( ! showInfo )}
				>
					{ __( 'What info do we collect?', 'feedzy-rss-feeds' ) }
				</Button>
				<Button
					className="f-send"
					variant="primary"
					type="submit"
					isPrimary
					disabled={ 'loading' === status }
				>
					{ 'loading' === status ? <Spinner/> : __( 'Send feedback', 'feedzy-rss-feeds' ) }
				</Button>
			</div>
		</form>
	);
};

export default FeedbackForm;
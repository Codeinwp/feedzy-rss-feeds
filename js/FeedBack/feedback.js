import React from 'react';
import ReactDOM from 'react-dom';

/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import {
	Fragment,
	useState
} from '@wordpress/element';

import {
	Modal
} from '@wordpress/components';

import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import FeedbackForm from './feedback-form';

const finishIcon = `${ window.feedzyObj.assetsUrl }img/finish-feedback.svg`;

const FeedBack = () => {
	const [ isOpen, setOpen ] = useState( false );
	const [ status, setStatus ] = useState( 'notSubmitted' );
	const closeModal = () => setOpen( false );

	document.querySelectorAll( '[role="button"]' ).forEach((button) => {
		button.addEventListener('keydown', (evt) => { 
			if(evt.keyCode === 13 || evt.keyCode === 32) {
				button.click();
			}
		});
	});
	document.querySelector( '#fz-feedback-btn' ).addEventListener( 'click', () => setOpen( true ) );

	return (
		<Fragment>
		{ isOpen && (
			<Modal
				className={ classnames( 'fz-feedback-modal', { 'no-header': 'submitted' === status }) }
				overlayClassName="fz-feedback-modal-feedback-modal-overlay"
				title={ __( 'What\'s one thing you need in Feedzy?', 'feedzy-rss-feeds' ) }
				onRequestClose={ closeModal }
				shouldCloseOnClickOutside={ false }
				closeButtonLabel={ __( 'Close', 'feedzy-rss-feeds' ) }
			>
			{ 'submitted' !== status ? (
				<FeedbackForm
					source="dashboard"
					status={ status }
					setStatus={ setStatus }
				/>
			) : (
				<div className="finish-feedback">
					<img
						src={ finishIcon }
					/>
					<p className="f-title">{ __( 'Thank you for your feedback', 'feedzy-rss-feeds' ) }</p>
					<p className="f-description">{ __( 'Your feedback is highly appreciated and will help us to improve Feedzy RSS Feeds.', 'feedzy-rss-feeds' ) }</p>
				</div>
			) }
		</Modal>
		) }
		</Fragment>
	);
};

ReactDOM.render(
<FeedBack />,
document.querySelector('#fz-feedback-modal')
);
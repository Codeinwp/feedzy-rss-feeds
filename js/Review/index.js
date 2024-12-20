/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import apiFetch from '@wordpress/api-fetch';

import domReady from '@wordpress/dom-ready';

import {
    Button,
    Modal
} from '@wordpress/components';

import {
    createRoot,
    useState
} from '@wordpress/element';

const App = () => {
    const [ isOpen, setOpen ] = useState( true );

    const closeModal = async () => {
        console.log( 'closeModal' );
        try {
            await apiFetch({
                path: '/wp/v2/settings',
                method: 'POST',
                data: {
                    feedzy_review_notice: 'yes',
                },
            });
        } catch ( error ) {
            console.error( __( 'Error updating setting:', 'feedzy-rss-feeds' ), error );
        } finally {
            setOpen( false );
        }
    };

    if ( ! isOpen ) {
        return null;
    }

    return (
        <Modal
            title={ __( 'Congrats, You\'ve Reached an Impressive Milestone! 🎉', 'feedzy-rss-feeds' ) }
            size="medium"
            shouldCloseOnClickOutside={ false }
            onRequestClose={ closeModal }
        >
            <p>{ __( 'You\'ve successfully imported', 'feedzy-rss-feeds' ) }<strong>{ __( ' more than 100 posts', 'feedzy-rss-feeds' ) }</strong>{ __( ' using Feedzy!', 'feedzy-rss-feeds' ) }</p>
            <p>{ __( 'If you\'re enjoying Feedzy, we\'d be thrilled if you could leave us a', 'feedzy-rss-feeds' ) } <strong>{ __( '5-star review', 'feedzy-rss-feeds' ) }</strong> (<span style={{ color: 'gold' }}>★★★★★</span>) { __( 'on WordPress.org. Your support helps us grow and deliver even better features.', 'feedzy-rss-feeds' ) }</p>

            <div className="buttons-wrap" style={{ display: 'flex', gap: '10px', marginTop: '20px' }}>
                <Button
                    href="https://wordpress.org/support/plugin/feedzy-rss-feeds/reviews/#new-post"
                    target="_blank"
                    variant="primary"
                    className="fz-feedback-button"
                    style={{ borderRadius: '5px' }}
                >
                    { __( 'Rate Feedzy on WordPress.org', 'feedzy-rss-feeds' ) }
                </Button>
            </div>
        </Modal>
    );
};

domReady( () => {
    const modalContainer = document.createElement( 'div' );
    modalContainer.id = 'fz-review-modal';
    document.body.appendChild( modalContainer );
	const root = createRoot( document.getElementById( 'fz-review-modal' ) );
	root.render( <App /> );
});

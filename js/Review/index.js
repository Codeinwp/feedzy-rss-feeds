/**
 * WordPress dependencies.
 */
import {
    __,
    sprintf
} from '@wordpress/i18n';

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
            title={ __( 'Congrats, You\'ve Reached an Impressive Milestone!', 'feedzy-rss-feeds' ) + ' 🎉' }
            size="medium"
            shouldCloseOnClickOutside={ false }
            onRequestClose={ closeModal }
        >
            <p
                dangerouslySetInnerHTML={{
                    __html: sprintf(
                    // translators: %1$s is an opening strong tag, %2$s is a closing strong tag, %3$s is the number of posts imported.
                    __(
                        "You've successfully imported %1$s more than %3$s posts %2$s using Feedzy!",
                        'feedzy-rss-feeds'
                    ),
                    '<strong>', // %1$s
                    '</strong>', // %2$s
                    100 // %3$s
                    ),
                }}
            />

            <p
                dangerouslySetInnerHTML={{
                    __html: sprintf(
                    // translators: %1$s is an opening strong tag, %2$s is a closing strong tag, %3$s is for the 5-star rating stars.
                    __(
                        "If you're enjoying Feedzy, we'd be thrilled if you could leave us a %1$s5-star review%2$s (%3$s) on WordPress.org. Your support helps us grow and deliver even better features.",
                        'feedzy-rss-feeds'
                    ),
                    '<strong>', // %1$s
                    '</strong>', // %2$s
                    '<span style="color:gold;">★★★★★</span>' // %3$s
                    ),
                }}
            />

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

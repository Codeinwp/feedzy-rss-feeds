import React from 'react';
import ReactDOM from 'react-dom';
import Joyride from 'react-joyride';

/**
 * WordPress dependencies.
 */
import { debounce } from 'lodash';

import { __ } from '@wordpress/i18n';

import {
  Button,
  Modal
} from '@wordpress/components';

import {
  Fragment,
  useEffect,
  useRef,
  useState
} from '@wordpress/element';


const Onboarding = () => {
  const [ isOpen, setOpen ] = useState( true );
  const [ runTour, setRunTour ] = useState( false );

  const settingsRef = useRef( null );

  useEffect( () => {
    window.wp.api.loadPromise.then( () => {
      settingsRef.current = new window.wp.api.models.Settings();
    });
  }, []);

  const steps = [
    {
      target: '#post_title',
      content: __( 'Add import name', 'feedzy-rss-feeds' ),
    },
    {
      target: '.fz-input-group-left .fz-input-icon',
      content: __( 'Add your feed source URL', 'feedzy-rss-feeds' ),
    },
    {
      target: '#fz-import-filters',
      content: __( 'Config feedzy additional setting', 'feedzy-rss-feeds' ),
    },
    {
      target: '#fz-import-map-content',
      content: __( 'Map import fields', 'feedzy-rss-feeds' ),
    },
    {
      target: '#fz-import-general-settings',
      content: __( 'General import seetings', 'feedzy-rss-feeds' ),
    }
  ];

  const skipTour = debounce( status => {
    if ( isOpen ) {
      setOpen( false );
    }

    if ( 'ready' !== status && 'finished' !== status && 'skipped' !== status ) {
      return;
    }

    const model = new window.wp.api.models.Settings({
      // eslint-disable-next-line camelcase
      feedzy_import_tour: false
    });

    const save = model.save();

    save.success( () => {
      settingsRef.current.fetch();
    });

    save.error( ( response ) => {
      console.warning( response.responseJSON.message );
    });
  }, 1000 );

  return (
    <Fragment>
      { isOpen && (
        <Modal
          title={ __( 'Welcome to Feedzy!', 'feedzy-rss-feeds' ) }
          isDismissible={ false }
          className="feedzy-onboarding-modal"
        >
          <div className="feedzy-onboarding-modal-content">
            { __( 'Would you like to start the onboarding wizard which will help you personalize the plugin for yourself?', 'feedzy-rss-feeds' ) }
          </div>

          <div className="feedzy-onboarding-modal-action">
            <Button
              isPrimary
              onClick={ () => {
                setOpen( false );
                setRunTour( true );
              } }
            >
              { __( 'Start', 'feedzy-rss-feeds' ) }
            </Button>

            <Button
              isSecondary
              onClick={ () => skipTour( 'skipped' ) }
            >
              { __( 'Skip', 'feedzy-rss-feeds' ) }
            </Button>
          </div>
        </Modal>
      ) }

      <Joyride
        continuous={ true }
        run={ runTour }
        steps={ steps }
        scrollToFirstStep
        showSkipButton
        locale={ {
          back: __( 'Back', 'feedzy-rss-feeds' ),
          close: __( 'Close', 'feedzy-rss-feeds' ),
          last: __( 'Finish', 'feedzy-rss-feeds' ),
          next: __( 'Next', 'feedzy-rss-feeds' ),
          skip: __( 'Skip', 'feedzy-rss-feeds' )
        } }
        callback={ data => skipTour( data.status ) }
      />
    </Fragment>
  );
}

ReactDOM.render(
  <Onboarding />,
  document.querySelector('#fz-on-boarding')
);
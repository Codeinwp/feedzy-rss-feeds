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

  const userRef = useRef( null );

  useEffect( () => {
    window.wp.api.loadPromise.then( () => {
      userRef.current = new window.wp.api.models.User( { id: 'me' } );
    });
  }, []);

  let telemetryEnabled = false;

  function activateTelemetry() {
    if ( ! telemetryEnabled ) {
      return;
    }

    const model = new window.wp.api.models.Settings({
      // Update the 'feedzy_rss_feeds_logger_flag' option
      feedzy_rss_feeds_logger_flag: 'yes'
    });

    const save = model.save();
 
    save.error( ( response ) => {
      console.warn( response.responseJSON.message );
    });
  }

  const steps = [
    {
      target: '#post_title',
      content: __( 'Choose a descriptive name for your importing schedule to help you easily find it later.', 'feedzy-rss-feeds' ),
    },
    {
      target: '.fz-input-group-left .fz-input-icon',
      content: __( 'Add here your RSS feed URL which Feedzy will use to fetch the content from. You can use Feedzy\'s Feed categories too.', 'feedzy-rss-feeds' ),
    },
    {
      target: '#fz-import-filters',
      content: __( 'Choose the data which you want to import from the feed. Filter by time or keywords.', 'feedzy-rss-feeds' ),
    },
    {
      target: '#fz-import-map-content',
      content: __( 'Choose how you would like to import the data into your site. Map the Feed content to a specific post type and customize the imported posts. Extend to PRO to import full content from feed articles, paraphrase the content before import or translate automatically.', 'feedzy-rss-feeds' ),
    },
    {
      target: '#fz-import-general-settings',
      content: __( 'Customize the importing schedule by cleaning up old imports, remove duplicates or number of imported items per run.', 'feedzy-rss-feeds' ),
    },
    {
      target: '#fz-import-general-settings', // replace with the selector for where you want the button to appear
      content: (
        <div>
          <p>{ __( 'Enable telemetry to help us improve the plugin by sending anonymous usage data. Data is private and not shared third-party entities.', 'feedzy-rss-feeds' ) }</p>
          <label>
            <input
              type="checkbox"
              onChange={ () => { telemetryEnabled = !telemetryEnabled } }
            />
            { __( 'Enable telemetry', 'feedzy-rss-feeds' ) }
          </label>
        </div>
      ),
      disableBeacon: true,
    }
  ];

  const skipTour = debounce( status => {
    if ( isOpen ) {
      setOpen( false );
    }

    if ( 'ready' !== status && 'finished' !== status && 'skipped' !== status ) {
      return;
    }

    const model = new window.wp.api.models.User({
      // eslint-disable-next-line camelcase
      id: 'me',
      meta: {
        feedzy_import_tour: false
      }
    });

    const save = model.save();

    save.success( () => {
      userRef.current.fetch();
    });

    save.error( ( response ) => {
      console.warn( response.responseJSON.message );
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
            { __( 'Would you like to start the onboarding wizard which will help you explore the plugin features?', 'feedzy-rss-feeds' ) }
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
        callback={ data => {
          if ( 'ready' === data.status ) {
            activateTelemetry();
          }

          skipTour( data.status );
        } }
      />
    </Fragment>
  );
}

ReactDOM.render(
  <Onboarding />,
  document.querySelector('#fz-on-boarding')
);
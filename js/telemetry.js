/**
 * Exclude fields from telemetry tracking. They are not relevant or contain sensitive data.
 */
const excludedFieldsSlugs = [
    '_wp',
    'proxy',
    '_key',
    '_pass',
    'username',
    'post_',
    'auto',
    'nonce',
    'password',
];

/**
 * Collect telemetry data from the submit event of the settings forms.
 * 
 */
window.addEventListener( 'load', function() {
    if ( ! window.tiTrk ) {
      return;
    }

    /**
     * Forms to track.
     */
    const envs = [
        [ 'main-settings', 'form:has(.fz-form-wrap)' ],
        [ 'categories-settings', 'form:has(#feedzy_category_feeds)'],
        [ 'import-settings', 'form:has(#feedzy-import-form)'],
    ]
        .map( ( [ feature, formSelector ] ) => {
            return [ feature, document.querySelector( formSelector ) ];
        } )
        .filter( ( [ feature, formElement ] ) => {
            return formElement;
        } );

    if ( ! envs.length ) {
        return;
    }
    
    envs.forEach( ( [ feature, form ] ) => {
        form.addEventListener( 'submit', function(e) {
            const formData = new FormData( form );
            const trackingPayload = {};
        
            for ( const [ name, value ] of formData.entries() ) {
                if ( typeof value === 'undefined' || value === null ) {
                    continue;
                }

                if ( excludedFieldsSlugs.some( ( slug ) => name.includes( slug ) ) ) {
                    continue;
                }
        
                trackingPayload[ name ] = value;
            }
    
            if ( ! Object.keys( trackingPayload ).length ) {
              return;
            }
        
            window.tiTrk.with('feedzy').add( {
              action: 'snapshot',
              feature: feature,
              featureValue: trackingPayload
            })

            window.tiTrk.uploadEvents();
        });
    });
  });
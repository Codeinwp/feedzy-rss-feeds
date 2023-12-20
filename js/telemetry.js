/**
 * Exclude fields from telemetry tracking. They are not relevant or contain sensitive data.
 */
const excludedFieldsSlugs = [
    '_wp',
    'proxy',
    '_key',
    '_pass',
    'username',
    'auto',
    'nonce',
    'password',
    'user_ID',
    'post_author',
    '_post_status',
    'default_thumbnail_id',
    'default-thumbnail-id'
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
        { pageName: 'main-settings', formSelector: 'form:has(input[value*="feedzy-settings"])', includeFilter: [] },
        { pageName: 'categories-settings', formSelector: 'form:has(#feedzy_category_feeds)', includeFilter: [ 'feedzy_' ] },
        { pageName: 'import-settings', formSelector: 'form:has(#feedzy-import-form)', includeFilter: [ 'feedzy_meta'] },
    ]
        .map( env => {
            env.formSelector = document.querySelector( env.formSelector )
            return env;
        } )
        .filter( env => {
            return env.formSelector;
        } );

    if ( ! envs.length ) {
        return;
    }
   
    envs.forEach( env => {
        env.formSelector.addEventListener( 'submit', function() {
            const formData = new FormData( env.formSelector );
        
            for ( const [ name, value ] of formData.entries() ) {
                if ( typeof value === 'undefined' || value === null ) {
                    continue;
                }

                if ( excludedFieldsSlugs.some( ( slug ) => name.includes( slug ) ) ) {
                    continue;
                }

                if ( env.includeFilter.length && ! env.includeFilter.some( ( slug ) => name.includes( slug ) ) ) {
                    continue;
                }

                window.tiTrk.with('feedzy').add({
                    action: 'snapshot',
                    feature: env.pageName,
                    featureComponent: name,
                    featureValue: value,
                  });
            }

            window.tiTrk.uploadEvents();
        });
    });
  });
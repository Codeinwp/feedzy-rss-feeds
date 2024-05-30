const { defineConfig } = require('cypress')

module.exports = defineConfig({
  env: {
    login: 'wordpress',
    pass: 'wordpress',
    settings: {
      tabs: 8,
    },
    shortcode: {
      single:
        "[feedzy-rss feeds='https://s3.amazonaws.com/verti-utils/sample-feed.xml' max='11' offset='1' feed_title='yes' refresh='1_hours' meta='yes' multiple_meta='yes' summary='yes' price='yes' mapping='price=im:price' thumb='yes' keywords_title='God, Mendes, Cyrus, Taylor' keywords_ban='Cyrus' template='style1']",
      single_results: 9,
      lazy: "[feedzy-rss feeds='https://s3.amazonaws.com/verti-utils/sample-feed.xml' max='11' offset='1' feed_title='yes' refresh='1_hours' meta='yes' multiple_meta='yes' summary='yes' price='yes' mapping='price=im:price' thumb='yes' keywords_title='God, Mendes, Cyrus, Taylor' keywords_ban='Cyrus' template='style1' lazy='yes']",
      multiple:
        "[feedzy-rss feeds='https://s3.amazonaws.com/verti-utils/sample-feed-multiple1.xml, https://s3.amazonaws.com/verti-utils/sample-feed-multiple2.xml' max='10' feed_title='no' refresh='1_hours' meta='yes' multiple_meta='yes' summary='yes' thumb='yes' template='style1']",
      multiple_results: 10,
    },
    gutenberg: {
      url: 'https://s3.amazonaws.com/verti-utils/sample-feed.xml',
      max: 10,
      offset: 1,
      include: 'God, Mendes, Cyrus, Taylor',
      ban: 'Cyrus',
      results: 9,
      meta: 'yes',
      multiple_meta: 'yes',
      thumb: 'yes',
    },
    'import-feed': {
      locked: 7,
      url: 'https://s3.amazonaws.com/verti-utils/sample-feed-import.xml',
      invalidurl: 'test',
      title: '[#item_date], [#item_custom_guid], [#item_title]',
      content: '[#item_content] start:[#item_categories]:end',
      items: '10',
      taxonomy: ['c_feedzy-1', 't_feedzy-1'],
      image: {
        url: 'https://source.unsplash.com/random',
        tag: '[#item_image]',
      },
      fullcontent: {
        content: '[#item_full_content]',
        items: '1',
      },
      wait: 10000,
      tags: {
        disallowed: {
          free: [
            'full_content',
            'wordai',
            'spinnerchief',
            'title_wordai',
            'content_wordai',
            'full_content_wordai',
            'title_spinnerchief',
            'content_spinnerchief',
            'full_content_spinnerchief',
          ],
        },
        mandatory: {
          free: [
            'item_content',
            'item_description',
            'item_image',
            'item_url',
            'item_categories',
            'item_source',
          ],
        },
      },
    },
  },
  projectId: 'frqcxp',
  experimentalStudio: true,
  numTestsKeptInMemory: 0,
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      return require('./cypress/plugins/index.js')(on, config);
    },
    baseUrl: 'http://localhost:8080',
    specPattern: 'cypress/e2e/**/*.{js,jsx,ts,tsx}',
    pageLoadTimeout : 300000,
  },
  blockHosts: [
    'app.formbricks.com'
  ],
})

const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
    ...defaultConfig,
    entry: {
        'block': './js/FeedzyBlock/block.js',
        'import-onboarding': './js/Onboarding/import-onboarding.js',
        'feedback': './js/FeedBack/feedback.js',
        'action-popup': './js/ActionPopup/action-popup.js',
    },
    output: {
        ...defaultConfig.output,
        filename: '[name].js',
        path: __dirname + '/js/build'
    }
};

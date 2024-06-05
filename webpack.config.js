const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
    ...defaultConfig,
    devtool: 'inline-source-map',
    entry: {
        'block': './js/FeedzyBlock/block.js',
        'import-onboarding': './js/Onboarding/import-onboarding.js',
        'feedback': './js/FeedBack/feedback.js',
        'action-popup': './js/ActionPopup/action-popup.js',
    },
    output: {
        ...defaultConfig.output,
        filename: '[name].min.js',
        path: __dirname + '/js/build'
    }
};

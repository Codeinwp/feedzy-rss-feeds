const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const config = {
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
    },
};

module.exports = (env, argv) => {
    if (argv.mode === 'development') {
        config.devtool = 'inline-source-map';
    }

    return config;
}

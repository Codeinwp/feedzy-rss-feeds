// jshint ignore: start
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
// const BrowserSyncPlugin = require( 'browser-sync-webpack-plugin' );

// Set different CSS extraction for editor only and common block styles
const blockCSSPlugin = new MiniCssExtractPlugin({
    filename: './includes/gutenberg/build/block.css',
});

// Configuration for the ExtractTextPlugin.
const extractConfig = {
    use: [
        {loader: 'raw-loader'},
        {
            loader: 'postcss-loader',
            options: {
                plugins: [require('autoprefixer')],
            },
        },
        {
            loader: 'sass-loader',
            query: {
                outputStyle:
                    'production' === process.env.NODE_ENV ? 'compressed' : 'nested',
            },
        },
    ],
};

module.exports = {
	 	...defaultConfig,
    entry: {
        './includes/gutenberg/block': './includes/gutenberg/src/block.js',
        './js/Onboarding/import-onboarding.min': './js/Onboarding/import-onboarding.js',
        './js/FeedBack/feedback.min': './js/FeedBack/feedback.js',
        './js/ActionPopup/action-popup.min': './js/ActionPopup/action-popup.js',
    },
};

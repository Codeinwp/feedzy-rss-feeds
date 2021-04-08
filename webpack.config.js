// jshint ignore: start

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
    entry: {
        './includes/gutenberg/build/block': './includes/gutenberg/src/block.js',
    },
    output: {
        path: path.resolve(__dirname),
        filename: '[name].js',
    },
    watch: 'production' === process.env.NODE_ENV ? false : true,
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                use: {
                    loader: 'babel-loader',
                },
            },
            {
                test: /style\.s?css$/,
                use: [ MiniCssExtractPlugin.loader,
                    {loader: 'raw-loader'},
                    {
                        loader: 'postcss-loader',
                        options: {
                            postcssOptions: {
                                plugins: [
                                    [
                                        "autoprefixer",
                                        {
                                            // Options
                                        },
                                    ],
                                ],
                            },
                        },
                    },
                    {
                        loader: 'sass-loader',
                    }
                    ],
            },
        ],
    },
    plugins: [
        blockCSSPlugin,
        // new BrowserSyncPlugin({
        //   // Load localhost:3333 to view proxied site
        //   host: 'localhost',
        //   port: '3333',
        //   // Change proxy to your local WordPress URL
        //   proxy: 'https://gutenberg.local'
        // })
    ],
};

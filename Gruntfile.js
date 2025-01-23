/**
 * Grunt File
 *
 * @package feedzy-rss-feeds
 */
module.exports = function (grunt) {
    grunt.initConfig({
        wp_readme_to_markdown: {
            files: {
                'readme.md': 'readme.txt'
            },
        },
        version: {

            project: {
                src: [
                    'package.json'
                ]
            },
            style: {
                options: {
                    prefix: 'Version\\:\.*\\s'
                },
                src: [
                    'feedzy-rss-feed.php',
                    'css/feedzy-rss-feeds.css',
                ]
            },
            readmetxt: {
                options: {
                    prefix: 'Stable tag:\\s*'
                },
                src: [
                    'readme.txt'
                ]
            },
            class: {
                options: {
                    prefix: '\\.*version\.*\\s=\.*\\s\''
                },
                src: [
                    'includes/feedzy-rss-feeds.php',
                ]
            }
        }
    });
    grunt.loadNpmTasks('grunt-version');
    grunt.loadNpmTasks('grunt-wp-readme-to-markdown');
};

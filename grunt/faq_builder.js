/**
 * Faq builder for Grunt
 */
//jshint ignore: start
module.exports = {
	options: {
		filename: 'readme.txt',
		api_key: process.env.DOCS_API,
		collection_id: process.env.DOCS_COLLECTION,
		category_id: process.env.DOCS_CATEGORY,
		template: "= {article_title} = \n [{article_link}]({article_link}) \n\n ",
	},
	helpscout: {},
};

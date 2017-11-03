/**
 * Faq builder for Grunt
 */
//jshint ignore: start
module.exports = {
	options: {
		filename: 'readme.txt',
		api_key: process.env.HS_DOCS_API_KEY,
		collection_id: process.env.HS_DOCS_CATEGORY_ID,
		category_id: process.env.HS_DOCS_COLLECTION_ID,
		template: "= {article_title} = \n [{article_link}]({article_link}) \n\n ",
	},
	helpscout: {},
};

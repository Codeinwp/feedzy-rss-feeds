#!/usr/bin/env node
// Read GitHub Action environment variables.
const repoName = process.env.GITHUB_REPO_NAME || '';
const branchName = process.env.DEV_ZIP_BRANCH_NAME || '';
const gitSha8 = process.env.DEV_ZIP_GIT_SHA_8 || '';

// Create the blueprint object with necessary schema and options.
const blueprint = {
	preferredVersions: {
		php: '8.0',
	},
	plugins: [
		'wpide',
		`https://verti-artifacts.s3.amazonaws.com/${repoName}-${branchName}-${gitSha8}/feedzy-rss-feeds.zip`,
	],
	login: true,
	landingPage: '/wp-admin/post-new.php?post_type=feedzy_imports',
	features: {
		networking: true,
	},
};

// Convert the blueprint object to JSON and then encode it in Base64.
const blueprintJson = JSON.stringify(blueprint);
const encodedBlueprint = Buffer.from(blueprintJson).toString('base64');

// Output the full preview link.
process.stdout.write('https://playground.wordpress.net/#' + encodedBlueprint);

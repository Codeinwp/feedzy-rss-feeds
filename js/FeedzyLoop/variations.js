/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

const variations = [
	{
		name: 'feedzy-default',
		title: __('Default', 'feedzy-rss-feeds'),
		description: __(
			'Display curated RSS content in a dynamic, customizable loop directly in the Block Editor—no coding required.',
			'feedzy-rss-feeds'
		),
		icon: (
			<img
				src={
					window.feedzyData.imagepath + 'feedzy-default-template.png'
				}
				alt={__('Default', 'feedzy-rss-feeds')}
			/>
		),
		isDefault: true,
		innerBlocks: [
			[
				'core/group',
				{
					layout: {
						type: 'constrained',
					},
					style: {
						spacing: {
							padding: {
								top: 'var:preset|spacing|30',
								bottom: 'var:preset|spacing|30',
								left: 'var:preset|spacing|30',
								right: 'var:preset|spacing|30',
							},
							margin: {
								top: 'var:preset|spacing|30',
								bottom: 'var:preset|spacing|30',
							},
						},
					},
				},
				[
					[
						'core/image',
						{
							url: window.feedzyData.defaultImage,
							alt: '{{feedzy_title}}',
							href: '{{feedzy_url}}',
						},
					],
					[
						'core/paragraph',
						{
							content:
								'<a href="{{feedzy_url}}">{{feedzy_title}}</a>',
						},
					],
					[
						'core/paragraph',
						{
							content: '{{feedzy_meta}}',
							fontSize: 'medium',
						},
					],
					[
						'core/paragraph',
						{
							content: '{{feedzy_description}}',
							fontSize: 'small',
						},
					],
				],
			],
		],
		scope: ['block'],
	},
	{
		name: 'feedzy-round',
		title: __('Round', 'feedzy-rss-feeds'),
		description: __(
			'Display the feed items in a round style.',
			'feedzy-rss-feeds'
		),
		icon: (
			<img
				src={window.feedzyData.imagepath + 'feedzy-style1-template.png'}
				alt={__('Default', 'feedzy-rss-feeds')}
			/>
		),
		attributes: {
			layout: {
				columnCount: 3,
			},
		},
		isDefault: false,
		innerBlocks: [
			[
				'core/group',
				{
					layout: {
						type: 'constrained',
					},
					style: {
						spacing: {
							padding: {
								top: 'var:preset|spacing|30',
								bottom: 'var:preset|spacing|30',
								left: 'var:preset|spacing|30',
								right: 'var:preset|spacing|30',
							},
							margin: {
								top: 'var:preset|spacing|30',
								bottom: 'var:preset|spacing|30',
							},
						},
					},
				},
				[
					[
						'core/image',
						{
							url: window.feedzyData.defaultImage,
							alt: '{{feedzy_title}}',
							href: '{{feedzy_url}}',
							width: '150px',
							height: '150px',
							scale: 'cover',
							align: 'center',
							className: 'is-style-rounded',
						},
					],
					[
						'core/paragraph',
						{
							content:
								'<a href="{{feedzy_url}}">{{feedzy_title}}</a>',
						},
					],
					[
						'core/paragraph',
						{
							content: '{{feedzy_meta}}',
							fontSize: 'medium',
						},
					],
					[
						'core/paragraph',
						{
							content: '{{feedzy_description}}',
							fontSize: 'small',
						},
					],
				],
			],
		],
		scope: ['block'],
	},
	{
		name: 'feedzy-card',
		title: __('Card', 'feedzy-rss-feeds'),
		description: __(
			'Display the feed items in a card style.',
			'feedzy-rss-feeds'
		),
		icon: (
			<img
				src={window.feedzyData.imagepath + 'feedzy-style2-template.png'}
				alt={__('Default', 'feedzy-rss-feeds')}
			/>
		),
		attributes: {
			layout: {
				columnCount: 3,
			},
		},
		isDefault: false,
		innerBlocks: [
			[
				'core/group',
				{
					layout: {
						type: 'constrained',
					},
					style: {
						spacing: {
							margin: {
								top: 'var:preset|spacing|30',
								bottom: 'var:preset|spacing|30',
							},
						},
						border: {
							color: '#e3edeb',
							width: '1px',
							radius: '5px',
						},
					},
				},
				[
					[
						'core/group',
						{
							layout: {
								type: 'constrained',
							},
							style: {
								spacing: {
									padding: {
										top: 'var:preset|spacing|30',
										bottom: 'var:preset|spacing|30',
										left: 'var:preset|spacing|30',
										right: 'var:preset|spacing|30',
									},
								},
							},
						},
						[
							[
								'core/image',
								{
									url: window.feedzyData.defaultImage,
									alt: '{{feedzy_title}}',
									href: '{{feedzy_url}}',
								},
							],
							[
								'core/paragraph',
								{
									content:
										'<strong><a href="{{feedzy_url}}">{{feedzy_title}}</a></strong>',
								},
							],
						],
					],
					[
						'core/group',
						{
							layout: {
								type: 'constrained',
							},
							style: {
								spacing: {
									padding: {
										top: 'var:preset|spacing|30',
										bottom: 'var:preset|spacing|30',
										left: 'var:preset|spacing|30',
										right: 'var:preset|spacing|30',
									},
								},
								color: {
									background: '#f1f5f4',
								},
								border: {
									color: '#e3edeb',
									width: '1px',
									radius: '5px',
								},
							},
						},
						[
							[
								'core/paragraph',
								{
									content: '{{feedzy_meta}}',
									fontSize: 'medium',
								},
							],
						],
					],
					[
						'core/group',
						{
							layout: {
								type: 'constrained',
							},
							style: {
								spacing: {
									padding: {
										top: 'var:preset|spacing|30',
										bottom: 'var:preset|spacing|30',
										left: 'var:preset|spacing|30',
										right: 'var:preset|spacing|30',
									},
								},
							},
						},
						[
							[
								'core/paragraph',
								{
									content: '{{feedzy_description}}',
									fontSize: 'small',
								},
							],
						],
					],
				],
			],
		],
		scope: ['block'],
	},
];

export default variations;

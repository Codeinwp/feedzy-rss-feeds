/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

import { SVG, Rect, Path, Circle } from '@wordpress/components';

const Default = () => (
	<SVG
		xmlns="http://www.w3.org/2000/svg"
		viewBox="0 0 200 100"
		width="100"
		height="50"
	>
		<Rect width="200" height="100" fill="#F3F4F6" />
		<Rect x="10" y="10" width="180" height="45" fill="#D1D5DB" />
		<Path d="M95 25 L85 35 L105 35 Z" fill="#9CA3AF" />
		<Circle cx="110" cy="28" r="4" fill="#9CA3AF" />
		<Rect x="10" y="65" width="140" height="6" rx="2" fill="#4B5563" />
		<Rect x="10" y="77" width="180" height="4" rx="1" fill="#9CA3AF" />
		<Rect x="10" y="85" width="160" height="4" rx="1" fill="#9CA3AF" />
	</SVG>
);

const StyleOne = () => (
	<SVG
		xmlns="http://www.w3.org/2000/svg"
		viewBox="0 0 200 100"
		width="100"
		height="50"
	>
		<Rect width="200" height="100" fill="#F3F4F6" />
		<Circle cx="35" cy="25" r="15" fill="#D1D5DB" />
		<Rect x="10" y="45" width="50" height="6" rx="2" fill="#4B5563" />
		<Rect x="10" y="55" width="50" height="4" rx="1" fill="#9CA3AF" />
		<Rect x="10" y="63" width="45" height="4" rx="1" fill="#9CA3AF" />
		<Rect x="10" y="71" width="48" height="4" rx="1" fill="#9CA3AF" />
		<Rect x="10" y="79" width="42" height="4" rx="1" fill="#9CA3AF" />
		<Circle cx="100" cy="25" r="15" fill="#D1D5DB" />
		<Rect x="75" y="45" width="50" height="6" rx="2" fill="#4B5563" />
		<Rect x="75" y="55" width="50" height="4" rx="1" fill="#9CA3AF" />
		<Rect x="75" y="63" width="45" height="4" rx="1" fill="#9CA3AF" />
		<Rect x="75" y="71" width="48" height="4" rx="1" fill="#9CA3AF" />
		<Rect x="75" y="79" width="42" height="4" rx="1" fill="#9CA3AF" />
		<Circle cx="165" cy="25" r="15" fill="#D1D5DB" />
		<Rect x="140" y="45" width="50" height="6" rx="2" fill="#4B5563" />
		<Rect x="140" y="55" width="50" height="4" rx="1" fill="#9CA3AF" />
		<Rect x="140" y="63" width="45" height="4" rx="1" fill="#9CA3AF" />
		<Rect x="140" y="71" width="48" height="4" rx="1" fill="#9CA3AF" />
		<Rect x="140" y="79" width="42" height="4" rx="1" fill="#9CA3AF" />
	</SVG>
);

const StyleTwo = () => (
	<SVG
		xmlns="http://www.w3.org/2000/svg"
		viewBox="0 0 200 100"
		width="100"
		height="50"
	>
		<Rect width="200" height="100" fill="#F3F4F6" />
		<Rect
			x="5"
			y="5"
			width="60"
			height="90"
			rx="3"
			fill="white"
			stroke="#E5E7EB"
		/>
		<Rect x="10" y="10" width="50" height="50" fill="#D1D5DB" />
		<Rect x="10" y="65" width="50" height="6" rx="2" fill="#4B5563" />
		<Rect x="10" y="75" width="50" height="4" rx="1" fill="#9CA3AF" />
		<Rect x="10" y="83" width="45" height="4" rx="1" fill="#9CA3AF" />
		<Rect
			x="70"
			y="5"
			width="60"
			height="90"
			rx="3"
			fill="white"
			stroke="#E5E7EB"
		/>
		<Rect x="75" y="10" width="50" height="50" fill="#D1D5DB" />
		<Rect x="75" y="65" width="50" height="6" rx="2" fill="#4B5563" />
		<Rect x="75" y="75" width="50" height="4" rx="1" fill="#9CA3AF" />
		<Rect x="75" y="83" width="45" height="4" rx="1" fill="#9CA3AF" />
		<Rect
			x="135"
			y="5"
			width="60"
			height="90"
			rx="3"
			fill="white"
			stroke="#E5E7EB"
		/>
		<Rect x="140" y="10" width="50" height="50" fill="#D1D5DB" />
		<Rect x="140" y="65" width="50" height="6" rx="2" fill="#4B5563" />
		<Rect x="140" y="75" width="50" height="4" rx="1" fill="#9CA3AF" />
		<Rect x="140" y="83" width="45" height="4" rx="1" fill="#9CA3AF" />
	</SVG>
);

const variations = [
	{
		name: 'feedzy-default',
		title: __('Default', 'feedzy-rss-feeds'),
		description: __(
			'Display curated RSS content in a dynamic, customizable loop directly in the Block Editor—no coding required.',
			'feedzy-rss-feeds'
		),
		icon: Default,
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
		icon: StyleOne,
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
		icon: StyleTwo,
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

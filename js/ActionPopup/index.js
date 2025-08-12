import React from 'react';
import ReactDOM from 'react-dom';
import { arrayMoveImmutable } from 'array-move';
import Actions from './Actions.js';

import { __ } from '@wordpress/i18n';

import { Button, Modal, ExternalLink, Popover } from '@wordpress/components';

import {
	Fragment,
	useEffect,
	useRef,
	useState,
	useCallback,
} from '@wordpress/element';

import { Icon, dragHandle, close, plus, trash } from '@wordpress/icons';

const ActionModal = () => {
	// useRef
	const userRef = useRef(null);
	// State
	const [isOpen, setOpen] = useState(false);
	const [isHideMsg, setHideMeg] = useState(false);
	const [isVisible, setIsVisible] = useState(false);
	const [action, setAction] = useState([]);
	const [shortCode, setShortCode] = useState('');
	const [fieldName, setFieldName] = useState('');
	const [editModeTag, setEditModeTag] = useState(null);
	const [isDisabledAddNew, setDisabledAddNew] = useState(false);
	const [isLoading, setLoading] = useState(false);
	const [currentCustomRow, setcurrentCustomRow] = useState(null);

	// Close the popup when click on outside the modal.
	const exitModalOnOutsideClick = useCallback(
		(e) => {
			if (!isVisible || !e.target.closest('.fz-action-popup')) {
				return;
			}
			toggleVisible(false);
		},
		[isVisible]
	);

	useEffect(() => {
		window.wp.api.loadPromise.then(() => {
			// Fetch user.
			userRef.current = new window.wp.api.models.User({ id: 'me' });
			userRef.current.fetch();
		});
	}, []);

	useEffect(() => {
		document.addEventListener('click', exitModalOnOutsideClick);
		return () => {
			document.removeEventListener('click', exitModalOnOutsideClick);
		};
	}, [isVisible, exitModalOnOutsideClick]);

	const handleChange = (args) => {
		const id = args.index;
		delete args.index;
		const prevState = action[id].data || {};
		const updatedState = { ...prevState, ...args };
		action[id].data = updatedState;
		setAction(() => [
			...action.filter((e) => {
				return e;
			}),
		]);
	};

	const demoPromptText = (args) => {
		const id = args.index;
		const type = args.type;
		delete args.index;
		delete args.type;
		const prevState = action[id].data || {};
		const updatedState = { ...prevState, ...args };
		if ('summarize' === type) {
			updatedState.ChatGPT =
				'Summarize this article {content} for better SEO.';
		} else if ('paraphase' === type) {
			updatedState.ChatGPT = 'Rephrase my {content} for better SEO.';
		} else if ('change_tone' === type) {
			updatedState.ChatGPT =
				'Change tone of my {content} for a more friendly approach.';
		}
		action[id].data = updatedState;
		setAction(() => [
			...action.filter((e) => {
				return e;
			}),
		]);
	};

	const openModal = () => {
		setLoading(true);
		setOpen(true);
	};
	const toggleVisible = (status) => {
		if (status) {
			setIsVisible((state) => !state);
		} else {
			setIsVisible(status);
		}
	};
	const closeModal = () => {
		setOpen(false);
		toggleVisible(false);
		setEditModeTag(null);
		setDisabledAddNew(false);
		setAction([]);
		setLoading(false);
		setcurrentCustomRow(null);
	};
	const hideIntroMessage = (status) => setHideMeg(status);
	const removeAction = (index) => {
		delete action[index];
		setAction(() => [
			...action.filter((e) => {
				return e;
			}),
		]);
		setDisabledAddNew(false);
	};

	const addAction = (actionId) => {
		const actionData = {
			id: actionId,
			tag: shortCode,
			data: {},
		};

		if (
			[
				'fz_translate',
				'fz_paraphrase',
				'fz_summarize',
				'wordAI',
				'spinnerchief',
			].indexOf(actionId) > -1
		) {
			actionData.data[actionId] = true;
		}

		if (['fz_translate'].indexOf(actionId) > -1) {
			const langInput = document.getElementById(
				'feedzy_auto_translate_lang'
			);
			actionData.data.lang = langInput ? langInput.value : 'eng_Latn';
		}

		if (['fz_image'].indexOf(actionId) > -1) {
			actionData.data.generateOnlyMissingImages = true;
		}

		const newAction = [actionData];
		setDisabledAddNew(() => 'item_image' === shortCode);
		setAction((prevState) => [...prevState, ...newAction]);
		toggleVisible(false);
	};

	const onSortEnd = ({ oldIndex, newIndex }) => {
		setAction((prevItem) =>
			arrayMoveImmutable(prevItem, oldIndex, newIndex)
		);
	};

	/**
	 * Hide action popup.
	 */
	const hideActionIntroMessage = () => {
		if (isOpen) {
			hideIntroMessage(false);
		}
		const model = new window.wp.api.models.User({
			// eslint-disable-next-line camelcase
			id: 'me',
			meta: {
				feedzy_hide_action_message: true,
			},
		});

		const save = model.save();

		save.success(() => {
			userRef.current.fetch();
		});

		save.error((response) => {
			console.warn(response.responseJSON.message);
		});
	};

	/**
	 * Save actions.
	 */
	const saveAction = () => {
		if ('function' !== typeof jQuery) {
			return;
		}

		action?.forEach((item, index) => {
			window?.tiTrk?.with('feedzy').add({
				feature: 'import_action',
				featureValue: item?.id,
				groupId: item?.tag ?? '',
			});
		});

		// Serialize the action.
		let _action = encodeURIComponent(JSON.stringify(action));
		if (action.length === 0) {
			setAction([]);
			_action = encodeURIComponent(
				JSON.stringify([{ id: '', tag: shortCode, data: {} }])
			);
		}
		if ('import_custom_field' === fieldName) {
			if (currentCustomRow) {
				currentCustomRow.value = _action;
			}
			closeModal();
			return;
		}

		const inputField = jQuery(
			`[name="feedzy_meta_data[${fieldName}]"]:is(textarea, input)`
		).data('tagify');

		if ('import_post_featured_img' === fieldName) {
			inputField.removeAllTags();
			inputField.addEmptyTag();
			inputField.clearPersistedData();
		}
		if (null === editModeTag || 'import_post_featured_img' === fieldName) {
			const tagElm = inputField.createTagElem({ value: _action });
			inputField.injectAtCaret(tagElm);
			const elm = inputField.insertAfterTag(tagElm);
			inputField.placeCaretAfterNode(elm);
		} else {
			inputField.replaceTag(editModeTag.closest('.fz-content-action'), {
				value: _action,
			});
		}
		closeModal();
	};

	const helperContainer = () => {
		return document.querySelector('.fz-action-popup .fz-action-panel ul');
	};

	// Click to open action popup.
	document.querySelectorAll('[data-action_popup]').forEach((actionItem) => {
		actionItem.addEventListener('click', (event) => {
			event.preventDefault();
			if (userRef.current) {
				if (
					!userRef.current.attributes.meta.feedzy_hide_action_message
				) {
					hideActionIntroMessage();
				} else {
					hideIntroMessage(true);
				}
			}
			const tag = event.target.getAttribute('data-action_popup') || '';
			const dataFieldName =
				event.target.getAttribute('data-field-name') || '';
			if ('' === tag) {
				event.target.closest('.dropdown-item').click();
				return;
			}
			setShortCode(tag);
			setFieldName(dataFieldName);
			openModal();
		});
	});

	// Init custom field actions.
	const initCustomFieldActions = () => {
		const customFieldElement =
			document.querySelectorAll('.custom_fields .fz-action-icon') || [];
		if (customFieldElement.length === 0) {
			return;
		}
		customFieldElement.forEach((actionButton) => {
			actionButton.addEventListener('click', (event) => {
				event.preventDefault();
				if (userRef.current) {
					if (
						!userRef.current.attributes.meta
							.feedzy_hide_action_message
					) {
						hideActionIntroMessage();
					} else {
						hideIntroMessage(true);
					}
				}

				let editAction = event?.target?.nextElementSibling?.value || '';
				if (editAction) {
					editAction = JSON.parse(decodeURIComponent(editAction));
					setAction(() => [
						...editAction.filter((e) => {
							return e.id !== '';
						}),
					]);
				}
				setShortCode('custom_field');
				setFieldName('import_custom_field');
				setcurrentCustomRow(event?.target?.nextElementSibling);
				openModal();
			});
		});
	};
	// Attach click event to the newly added custom field row.
	document.addEventListener('feedzy_new_row_added', initCustomFieldActions);

	const initEditHooks = () => {
		if (isLoading) {
			return;
		}
		// Click to open edit action popup.
		setTimeout(function () {
			const editActionElement =
				document.querySelectorAll(
					'.fz-content-action .tagify__filter-icon'
				) || [];
			if (editActionElement.length === 0) {
				initCustomFieldActions();
				initEditHooks();
				return;
			}
			if (editActionElement.length > 0) {
				editActionElement.forEach((editItem) => {
					editItem.addEventListener('click', (event) => {
						if (event.target.parentNode) {
							let editAction =
								event.target.getAttribute('data-actions') || '';
							const fieldId =
								event.target.getAttribute('data-field_id') ||
								'';
							editAction = JSON.parse(
								decodeURIComponent(editAction)
							);
							setAction(() =>
								editAction
									.filter((e) => e.id !== '')
									.map((e) => {
										// Replace 'fz_summarize' with 'chat_gpt_rewrite' for backward compatible.
										if (e.id === 'fz_summarize') {
											return {
												...e,
												id: 'chat_gpt_rewrite',
												data: {
													ChatGPT:
														'Summarize this article {content} for better SEO.',
												},
											};
										}
										return e;
									})
							);
							const magicTag = editAction[0] || {};
							const tag = magicTag.tag;
							setEditModeTag(event.target.parentNode);
							setDisabledAddNew(
								() =>
									Object.keys(magicTag.data).length &&
									'item_image' === tag
							);
							const actionGroup = document.querySelector(
								'.' + fieldId
							);
							actionGroup
								.querySelector(
									'[data-action_popup="' + tag + '"]'
								)
								.click();
						}
					});
				});
			}
			initCustomFieldActions();
		}, 500);
	};
	initEditHooks();
	return (
		<Fragment>
			{isOpen && (
				<Modal
					isDismissible={false}
					onRequestClose={closeModal}
					className="fz-action-popup"
					overlayClassName="fz-popup-wrap"
				>
					<div className="fz-action-content">
						<div className="fz-action-header">
							<div className="fz-modal-title">
								<h2>
									{__(
										'Add actions to this tag',
										'feedzy-rss-feeds'
									)}
								</h2>{' '}
								{!isHideMsg && (
									<span>
										{__('New!', 'feedzy-rss-feeds')}
									</span>
								)}
							</div>
							<Button
								variant="secondary"
								className="fz-close-popup"
								onClick={closeModal}
							>
								<Icon icon={close} />
							</Button>
						</div>
						<div className="fz-action-body">
							{!isHideMsg && (
								<div className="fz-action-intro">
									<p>
										{__(
											'Feedzy now supports adding and chaining actions into a single tag. Add an action by clicking the Add new button below. You can add multiple actions in each tag.',
											'feedzy-rss-feeds'
										)}
										<br />
										<ExternalLink href="https://docs.themeisle.com/article/1154-how-to-use-feed-to-post-feature-in-feedzy#tag-actions">
											{__(
												'Learn more about this feature.',
												'feedzy-rss-feeds'
											)}
										</ExternalLink>
									</p>
								</div>
							)}

							{action.length === 0 && (
								<div className="fz-action-intro">
									<p>
										{__(
											'If no action is needed, continue with using the original tag by clicking on the Save Actions button.',
											'feedzy-rss-feeds'
										)}
									</p>
								</div>
							)}

							{action.length > 0 && (
								<Actions
									data={action}
									removeCallback={removeAction}
									onChangeHandler={handleChange}
									updatePromptText={demoPromptText}
									onSortEnd={onSortEnd}
									useDragHandle
									lockAxis="y"
									helperClass="draggable-item"
									distance={1}
									lockToContainerEdges={true}
									lockOffset="0%"
								/>
							)}

							<div className="fz-action-btn">
								<div className="fz-action-relative">
									<Button
										isSecondary
										className="fz-new-action"
										onClick={() => {
											toggleVisible(true);
										}}
										disabled={isDisabledAddNew}
									>
										{__('Add new', 'feedzy-rss-feeds')}{' '}
										<Icon icon={plus} />
									</Button>
									{isVisible && (
										<div className="popover-action-list">
											<ul>
												{'item_image' === shortCode
													? [
															window.feedzyData
																.isPro &&
															(window.feedzyData
																.isBusinessPlan ||
																window
																	.feedzyData
																	.isAgencyPlan) ? (
																<li key="action-1">
																	<button
																		onClick={() =>
																			addAction(
																				'fz_image'
																			)
																		}
																	>
																		{__(
																			'Generate with OpenAI',
																			'feedzy-rss-feeds'
																		)}
																	</button>
																</li>
															) : (
																<li
																	key="action-1-disabled"
																	className="fz-action-disabled"
																>
																	{__(
																		'Generate with OpenAI',
																		'feedzy-rss-feeds'
																	)}
																	<span className="pro-label">
																		PRO
																	</span>
																</li>
															),
														]
													: [
															<li key="action-2">
																<button
																	className="feedzy-action-button"
																	onClick={() =>
																		addAction(
																			'trim'
																		)
																	}
																>
																	{__(
																		'Trim Content',
																		'feedzy-rss-feeds'
																	)}
																</button>
															</li>,
															window.feedzyData
																.isPro &&
															window.feedzyData
																.isAgencyPlan ? (
																<li key="action-3">
																	<button
																		className="feedzy-action-button"
																		onClick={() =>
																			addAction(
																				'fz_translate'
																			)
																		}
																	>
																		{__(
																			'Translate with Feedzy',
																			'feedzy-rss-feeds'
																		)}
																	</button>
																</li>
															) : (
																<li
																	key="action-3-disabled"
																	className="fz-action-disabled"
																>
																	{__(
																		'Translate with Feedzy',
																		'feedzy-rss-feeds'
																	)}
																	<span className="pro-label">
																		PRO
																	</span>
																</li>
															),
															<li key="action-4">
																<button
																	className="feedzy-action-button"
																	onClick={() =>
																		addAction(
																			'search_replace'
																		)
																	}
																>
																	{__(
																		'Search / Replace',
																		'feedzy-rss-feeds'
																	)}
																</button>
															</li>,
															'item_categories' !==
																shortCode &&
																(window
																	.feedzyData
																	.isPro ? (
																	<li key="action-5">
																		<button
																			className="feedzy-action-button"
																			onClick={() =>
																				addAction(
																					'modify_links'
																				)
																			}
																		>
																			{__(
																				'Modify Links',
																				'feedzy-rss-feeds'
																			)}
																		</button>
																	</li>
																) : (
																	<li
																		key="action-5-disabled"
																		className="fz-action-disabled"
																	>
																		{__(
																			'Modify Links',
																			'feedzy-rss-feeds'
																		)}
																		<span className="pro-label">
																			PRO
																		</span>
																	</li>
																)),
															'item_categories' !==
																shortCode &&
																(window
																	.feedzyData
																	.isPro &&
																(window
																	.feedzyData
																	.isBusinessPlan ||
																	window
																		.feedzyData
																		.isAgencyPlan) ? (
																	<li key="action-6">
																		<button
																			className="feedzy-action-button"
																			onClick={() =>
																				addAction(
																					'fz_paraphrase'
																				)
																			}
																		>
																			{__(
																				'Paraphrase with Feedzy',
																				'feedzy-rss-feeds'
																			)}
																		</button>
																	</li>
																) : (
																	<li
																		key="action-6-disabled"
																		className="fz-action-disabled"
																	>
																		{__(
																			'Paraphrase with Feedzy',
																			'feedzy-rss-feeds'
																		)}
																		<span className="pro-label">
																			PRO
																		</span>
																	</li>
																)),
															'item_categories' !==
																shortCode &&
																(window
																	.feedzyData
																	.isPro &&
																window
																	.feedzyData
																	.isAgencyPlan ? (
																	<li key="action-7">
																		<button
																			className="feedzy-action-button"
																			onClick={() =>
																				addAction(
																					'spinnerchief'
																				)
																			}
																		>
																			{__(
																				'Spin using SpinnerChief',
																				'feedzy-rss-feeds'
																			)}
																		</button>
																	</li>
																) : (
																	<li
																		key="action-7-disabled"
																		className="fz-action-disabled"
																	>
																		{__(
																			'Spin using SpinnerChief',
																			'feedzy-rss-feeds'
																		)}
																		<span className="pro-label">
																			PRO
																		</span>
																	</li>
																)),
															'item_categories' !==
																shortCode &&
																(window
																	.feedzyData
																	.isPro &&
																window
																	.feedzyData
																	.isAgencyPlan ? (
																	<li key="action-8">
																		<button
																			className="feedzy-action-button"
																			onClick={() =>
																				addAction(
																					'wordAI'
																				)
																			}
																		>
																			{__(
																				'Spin using WordAI',
																				'feedzy-rss-feeds'
																			)}
																		</button>
																	</li>
																) : (
																	<li
																		key="action-8-disabled"
																		className="fz-action-disabled"
																	>
																		{__(
																			'Spin using WordAI',
																			'feedzy-rss-feeds'
																		)}
																		<span className="pro-label">
																			PRO
																		</span>
																	</li>
																)),
															'item_categories' !==
																shortCode &&
																(window
																	.feedzyData
																	.isPro &&
																(window
																	.feedzyData
																	.isBusinessPlan ||
																	window
																		.feedzyData
																		.isAgencyPlan) ? (
																	<li key="action-9">
																		<button
																			className="feedzy-action-button"
																			onClick={() =>
																				addAction(
																					'chat_gpt_rewrite'
																				)
																			}
																		>
																			{__(
																				'Rewrite with AI',
																				'feedzy-rss-feeds'
																			)}
																		</button>
																	</li>
																) : (
																	<li
																		key="action-9-disabled"
																		className="fz-action-disabled"
																	>
																		{__(
																			'Rewrite with AI',
																			'feedzy-rss-feeds'
																		)}
																		<span className="pro-label">
																			PRO
																		</span>
																	</li>
																)),
														]}
												<li className="link-item">
													<ExternalLink href="https://docs.themeisle.com/article/1154-how-to-use-feed-to-post-feature-in-feedzy#tag-actions">
														{__(
															'Learn more about this feature.',
															'feedzy-rss-feeds'
														)}
													</ExternalLink>
												</li>
											</ul>
										</div>
									)}
								</div>
								{action && (
									<Button
										isPrimary
										className="fz-save-action"
										onClick={() => {
											saveAction();
										}}
									>
										{__('Save Actions', 'feedzy-rss-feeds')}
									</Button>
								)}
							</div>
						</div>
					</div>
				</Modal>
			)}
		</Fragment>
	);
};

ReactDOM.render(<ActionModal />, document.querySelector('#fz-action-popup'));

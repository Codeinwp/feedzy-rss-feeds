import React from 'react';
import ReactDOM from 'react-dom';
import { arrayMoveImmutable } from 'array-move';
import Actions from './Actions.js';

import { __ } from '@wordpress/i18n';

import {
	Button,
	Modal,
	ExternalLink,
	Popover
} from '@wordpress/components';

import {
	Fragment,
	useEffect,
	useRef,
	useState
} from '@wordpress/element';

import {
	Icon,
	dragHandle,
	close,
	plus,
	trash
} from '@wordpress/icons';

const ActionModal = () => {
	// useRef
	const settingsRef = useRef(null);
	const feedzyImportRef = useRef(null);
	// State
	const [ isOpen, setOpen ] = useState(false);
	const [ isHideMsg, setHideMeg ] = useState(false);
	const [ isVisible, setIsVisible ] = useState( false );
	const [ action, setAction ] = useState([]);
	const [ shortCode, setShortCode ] = useState('');
	const [ editModeTag, setEditModeTag ] = useState(null);

	useEffect( () => {
		window.wp.api.loadPromise.then( () => {
			// Fetch settings.
			settingsRef.current = new window.wp.api.models.Settings();
			settingsRef.current.fetch();
		});
	}, []);

	const handleChange = (args) => {
		let id = args.index;
		delete args.index;
		let prevState = action[id].data || {};
		let updatedState = {...prevState, ...args};
		action[id]['data'] = updatedState;
		setAction( () => ([...action.filter((e)=>{return e})]));

	};
	const openModal = () => setOpen(true);
	const toggleVisible = (status) => {
		if ( status ) {
			setIsVisible( (state) => !state );
		} else {
			setIsVisible(status);
		}
	};
	const closeModal = () => {
		setOpen(false);
		toggleVisible(false);
		setEditModeTag(null);
		setAction([]);
	};
	const hideIntroMessage = ( status ) => setHideMeg( status );
	const removeAction = ( index ) => {
		delete action[index];
		setAction( () => ([...action.filter((e)=>{return e})]));
	};

	const addAction = ( actionId ) => {
		let actionData = {
			id: actionId,
			tag: shortCode,
			data: {

			}
		};

		if ( ['fz_translate', 'fz_paraphrase', 'fz_summarize', 'wordAI', 'spinnerchief'].indexOf( actionId ) > -1 ) {
			actionData.data[actionId] = true;
		}
		let newAction = [actionData];
		setAction(prevState => ([...prevState, ...newAction]));
		toggleVisible(false);
	};

	const onSortEnd = ({ oldIndex, newIndex }) => {
		setAction(prevItem => (arrayMoveImmutable(prevItem, oldIndex, newIndex)));
	};

	/**
	 * Hide action popup.
	 */
	const hideActionIntroMessage = () => {
		if ( isOpen ) {
			hideIntroMessage(false);
		}
		const model = new window.wp.api.models.Settings({
			// eslint-disable-next-line camelcase
			feedzy_hide_action_message: true
		});

		const save = model.save();

		save.success( () => {
			settingsRef.current.fetch();
		});

		save.error( ( response ) => {
			console.warning( response.responseJSON.message );
		});
	};

	/**
	 * Save actions.
	 */
	const saveAction = () => {
		if ( 'function' !== typeof jQuery ) {
			return;
		}
		let _action = encodeURIComponent( JSON.stringify( action ) );
		if ( action.length === 0 ) {
			setAction([]);
			_action = encodeURIComponent( JSON.stringify( [ { id: '', tag: shortCode, data: {} } ] ) );
		}
		let postContent = jQuery( 'textarea.fz-textarea-tagify' ).data('tagify');
		if ( null === editModeTag ) {
			let tagElm = postContent.createTagElem({value: _action})
			postContent.injectAtCaret(tagElm)
			let elm = postContent.insertAfterTag(tagElm)
			postContent.placeCaretAfterNode(elm)
		} else {
			postContent.replaceTag(editModeTag.closest( '.fz-content-action' ), {value: _action});
		}
		closeModal();
	};

	const helperContainer = () => {
		return document.querySelector( '.fz-action-popup .fz-action-panel ul' );
	};

	// Close the popup when click on outside the modal.
	document.body.addEventListener( 'click', function( e ) {
		if ( isVisible ) {
			if ( e.target.closest( '.popover-action-list' ) ) {
				return;
			}
			toggleVisible(false);
		}
	} );

	// Click to open action popup.
	document.querySelectorAll( '[data-action_popup]' ).forEach( actionItem => {
		actionItem.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			if ( settingsRef.current ) {
				if ( ! settingsRef.current.attributes.feedzy_hide_action_message ) {
					hideActionIntroMessage();
				} else {
					hideIntroMessage(true);
				}
			}
			let tag = event.target.getAttribute( 'data-action_popup' ) || '';
			if ( '' === tag ) {
				event.target.closest('.dropdown-item').click();
				return;
			}
			setShortCode( tag );
			openModal();
		} );
	} );

	// Click to open edit action popup.
	setTimeout( function() {
		const editActionElement = document.querySelectorAll( '.fz-content-action .tagify__filter-icon' ) || [];
		if ( editActionElement.length > 0 ) {
			editActionElement.forEach( editItem => {
				editItem.addEventListener( 'click', ( event ) => {
					if ( event.target.parentNode ) {
						let editAction = event.target.getAttribute( 'data-actions' ) || '';
						editAction = JSON.parse( decodeURIComponent( editAction ) );
						setAction( () => ([...editAction.filter((e)=>{return e.id !== ''})]));
						let magicTag = editAction[0] || {};
						let tag = magicTag.tag;
						setEditModeTag(event.target);
						document.querySelector( '[data-action_popup="' + tag + '"]' ).click();
					}
				} );
			} );
		}
	}, 500 );

	return (
		<Fragment>
		{ isOpen && (
			<Modal isDismissible={ false } className="fz-action-popup" overlayClassName="fz-popup-wrap">
				<div className="fz-action-content">
					<div className="fz-action-header">
						<div className="fz-modal-title">
							<h2>{ __('Add actions to this tag', 'feedzy-rss-feeds') }</h2> { ! isHideMsg && ( <span>{ __( 'New!', 'feedzy-rss-feeds' ) }</span> ) }
						</div>
						<Button variant="secondary" className="fz-close-popup" onClick={ closeModal }><Icon icon={ close } /></Button>
					</div>
					<div className="fz-action-body">
						{ ! isHideMsg && (
							<div className="fz-action-intro">
								<p>{ __( 'Feedzy now supports adding and chaining actions into a single tag. Add an action by clicking the Add new button below. You can add multiple actions in each tag.', 'feedzy-rss-feeds' ) }<br/>
								<ExternalLink href="https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation">{ __( 'Learn more about this feature.', 'feedzy-rss-feeds' ) }</ExternalLink></p>
							</div>
						) }

						{ action.length === 0 && (
							<div className="fz-action-intro">
								<p>{ __( 'If no action is needed, continue with using the original tag by clicking on the Save Actions button.', 'feedzy-rss-feeds' ) }</p>
							</div>
						) }
						
						{action.length > 0 && ( <Actions data={action} removeCallback={removeAction} onChangeHandler={handleChange} onSortEnd={onSortEnd} useDragHandle lockAxis="y" helperClass="draggable-item" distance={1} lockToContainerEdges={true} lockOffset="0%"/> )}

						<div className="fz-action-btn">
							<div className="fz-action-relative">
								<Button isSecondary className="fz-new-action" onClick={ () => { toggleVisible(true) } }>
									{ __( 'Add new', 'feedzy-rss-feeds' ) } <Icon icon={ plus } />
								</Button>
								{ isVisible && (
									<div className="popover-action-list">
										<ul>
											<li onClick={ () => addAction('trim') }>{__( 'Trim Content', 'feedzy-rss-feeds' )}</li>
											{
												feedzyData.isPro && feedzyData.isAgencyPlan ? (
													<li onClick={ () => addAction('fz_translate') }>{__( 'Translate with Feedzy', 'feedzy-rss-feeds' )}</li>
												) : (
													<li onClick={ () => addAction('fz_translate') }>{__( 'Translate with Feedzy', 'feedzy-rss-feeds' )} <span className="pro-label">PRO</span></li>
												)
											}
											<li onClick={ () => addAction('search_replace') }>{__( 'Search / Replace', 'feedzy-rss-feeds' )}</li>
											{
												'item_categories' !== shortCode && (
													feedzyData.isPro && ( feedzyData.isBusinessPlan || feedzyData.isAgencyPlan ) ? (
														<li onClick={ () => addAction('fz_paraphrase') }>{__( 'Paraphrase with Feedzy', 'feedzy-rss-feeds' )}</li>
													) : (
														<li onClick={ () => addAction('fz_paraphrase') }>{__( 'Paraphrase with Feedzy', 'feedzy-rss-feeds' )} <span className="pro-label">PRO</span></li>
													)
												)
											}
											{
												'item_categories' !== shortCode && (
													feedzyData.isPro && feedzyData.isAgencyPlan ? (
														<li onClick={ () => addAction('spinnerchief') }>{__( 'Spin using SpinnerChief', 'feedzy-rss-feeds' )}</li>
													) : (
														<li onClick={ () => addAction('spinnerchief') }>{__( 'Spin using SpinnerChief', 'feedzy-rss-feeds' )} <span className="pro-label">PRO</span></li>
													)
												)
											}
											{
												'item_categories' !== shortCode && (
													feedzyData.isPro && feedzyData.isAgencyPlan ? (
														<li onClick={ () => addAction('wordAI') }>{__( 'Spin using WordAI', 'feedzy-rss-feeds' )}</li>
													) : (
														<li onClick={ () => addAction('wordAI') }>{__( 'Spin using WordAI', 'feedzy-rss-feeds' )} <span className="pro-label">PRO</span></li>
													)
												)
											}
											{
												'item_categories' !== shortCode && (
													feedzyData.isPro && ( feedzyData.isBusinessPlan || feedzyData.isAgencyPlan ) ? (
														<li onClick={ () => addAction('chat_gpt_rewrite') }>{__( 'Paraphrase with ChatGPT', 'feedzy-rss-feeds' )}</li>
													) : (
														<li onClick={ () => addAction('chat_gpt_rewrite') }>{__( 'Paraphrase with ChatGPT', 'feedzy-rss-feeds' )} <span className="pro-label">PRO</span></li>
													)
												)
											}
											{
												'item_categories' !== shortCode && (
													feedzyData.isPro && ( feedzyData.isBusinessPlan || feedzyData.isAgencyPlan ) ? (
														<li onClick={ () => addAction('fz_summarize') }>{__( 'Summarize with Feedzy', 'feedzy-rss-feeds' )}</li>
													) : (
														<li onClick={ () => addAction('fz_summarize') }>{__( 'Summarize with Feedzy', 'feedzy-rss-feeds' )} <span className="pro-label">PRO</span></li>
													)
												)
											}
											<li className="link-item"><ExternalLink href="https://docs.themeisle.com/article/1154-how-to-use-feed-to-post-feature-in-feedzy#tag-actions">{ __( 'Learn more about this feature.', 'feedzy-rss-feeds' ) }</ExternalLink></li>
										</ul>
									</div>
								)}
							</div>
						{ action && ( <Button isPrimary className="fz-save-action" onClick={ () => { saveAction() } }>{ __( 'Save Actions', 'feedzy-rss-feeds' ) }</Button> ) }
						</div>
					</div>
				</div>
			</Modal>
			) }
		</Fragment>
		);
	};

ReactDOM.render(
	<ActionModal />,
	document.querySelector('#fz-action-popup')
);
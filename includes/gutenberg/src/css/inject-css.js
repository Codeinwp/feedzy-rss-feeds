
/**
 * WordPress dependencies.
 */
const isEqual = require( 'lodash.isequal' );
const flattenDeep = require( 'lodash.flattendeep' );

const { parse } = wp.blocks;

const {
	select,
	subscribe
} = wp.data;

let isInitialCall = true;
// _.noConflict();
const addStyle = style => {
	const iFrame = window.parent.document.querySelector( 'iframe[name="editor-canvas"]' )?.contentWindow;
	let anchor = iFrame?.document.head || document.head;
	let element = anchor.querySelector( '#fz-css-editor-styles' );

	if ( isInitialCall && iFrame ) {
		iFrame.addEventListener( 'DOMContentLoaded', function() {
			setTimeout( () => {

				// A small delay for the iFrame to properly initialize.
				addStyle( style );
			}, 500 );
		});

		isInitialCall = false;
		return;
	}

	if ( null === element ) {
		element = document.createElement( 'style' );
		element.setAttribute( 'type', 'text/css' );
		element.setAttribute( 'id', 'fz-css-editor-styles' );
		anchor?.appendChild( element );
	}

	if ( element.textContent === style ) {
		return null;
	}
	element.textContent = style;

	return element.textContent;
};

/*
 * This function will get the `customCss` value from all the blocks and its children
 */
const getCustomCssFromBlocks = ( blocks, reusableBlocks ) => {
	if ( ! blocks ) {
		return '';
	}

	// Return the children of the block. The result is an array deeply nested that match the structure of the block in the editor.
	const getChildrenFromBlock = ( block ) => {
		const children = [];
		if ( 'feedzy-rss-feeds/feedzy-block' === block.name && null !== reusableBlocks ) {
			const reBlocks = reusableBlocks.find( i => block.attributes.ref === i.id );
			if ( reBlocks && reBlocks.content ) {
				const content = reBlocks.content.hasOwnProperty( 'raw' ) ? reBlocks.content.raw : reBlocks.content;
				children.push(  parse( content ).map( ( child ) => [ child, getChildrenFromBlock( child ) ])  );
			}
		}

		if ( undefined !== block.innerBlocks && 0 < ( block.innerBlocks ).length ) {
			children.push( block.innerBlocks.map( ( child ) => [ child, getChildrenFromBlock( child ) ]) );
		}

		return children;
	};

	// Get all the blocks and their children
	const allBlocks = blocks.map( ( block ) => {
		return [ block, getChildrenFromBlock( block ) ];
	});

	// Transform the deply nested array in a simple one and then get the `customCss` value where it is the case
	const extractCustomCss = flattenDeep( allBlocks ).map( ( block ) => {
		if ( block.attributes && block.attributes.customCSS ) {
			if ( block.attributes.customCSS && ( null !== block.attributes.customCSS ) ) {
				let customCSS = block.attributes.customCSS;
				let blockClientId = block.clientId.substr( 0, 8 );
				let suffixClass = '.fz-custom-style-' + blockClientId;
				customCSS = customCSS.replace( /^\s+|\s+$/gm, '' );
				let newStyle = customCSS.split( '}' );
				newStyle = newStyle.filter( el => el !== '' );
				newStyle.map( function( item, i ) {
					if ( -1 !== item.indexOf( '.feedzy-rss' ) ) {
						newStyle[ i ] = suffixClass + item;
					} else {
						newStyle[ i ] = suffixClass + ' ' + item;
					}
				} );
				newStyle = newStyle.join( '}' );
				customCSS = newStyle + '}';
				return customCSS + '\n';
			}
		}
		return '';
	});

	// Build the global style
	const style = extractCustomCss.reduce( ( acc, localStyle ) => acc + localStyle, '' );
	return style;
};

let previousBlocks = [];
let previewView = false;

export const onDeselect = () => {
	const { getBlocks } = select( 'core/block-editor' );
	const blocks = getBlocks();
	const reusableBlocks = select( 'core' ).getEntityRecords( 'postType', 'wp_block', { context: 'view' });
	const blocksStyle = getCustomCssFromBlocks( blocks, reusableBlocks );
	addStyle( blocksStyle );
};

subscribe( () => {
	const { getBlocks } = select( 'core/block-editor' );
	const __experimentalGetPreviewDeviceType = select( 'core/edit-post' ) ? select( 'core/edit-post' ).__experimentalGetPreviewDeviceType() : false;
	const blocks = getBlocks();
	const reusableBlocks = select( 'core' ).getEntityRecords( 'postType', 'wp_block', { context: 'view' });

	if ( ! isEqual( previousBlocks, blocks ) || previewView !== __experimentalGetPreviewDeviceType ) {
		const blocksStyle = getCustomCssFromBlocks( blocks, reusableBlocks );
		if ( blocksStyle ) {
			if ( previewView !== __experimentalGetPreviewDeviceType && 'Desktop' === previewView ) {
				setTimeout( () => {

					// A small delay for the iFrame to properly initialize.
					addStyle( blocksStyle );
				}, 500 );
			} else {
				addStyle( blocksStyle );
			}
		}

		previousBlocks = blocks;
		previewView = __experimentalGetPreviewDeviceType;
	}
});

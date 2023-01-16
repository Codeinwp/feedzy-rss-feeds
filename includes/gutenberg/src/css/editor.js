/**
 * WordPress dependencies.
 */
const { __ } = wp.i18n;

const {
	Button,
	Notice
} = wp.components;

const {
	Fragment,
	useEffect,
	useRef,
	memo,
	useState
} = wp.element;

let inputTimeout = null;

window.feedzyCSSLintIgnored = [];

const CSSEditor = ({
	attributes,
	setAttributes,
	clientId
}) => {

	const editorRef = useRef( null );
	const [ errors, setErrors ] = useState([]);
	const [ customCSS, setCustomCSS ] = useState( null );
	const [ editorValue, setEditorValue ] = useState( null );

	const getClassName = () => {
		const uniqueId = clientId.substr( 0, 8 );

		if ( customCSS?.replace( /\s+/g, '' ) === ( 'selector {\n}\n' ).replace( /\s+/g, '' ) ) {
			return attributes.className;
		}

		return  attributes.className ?
			( ! attributes.className.includes( 'fzcss-' ) ? [ ...attributes.className.split( ' ' ), `fzcss-${ uniqueId }` ].join( ' ' ) : attributes.className ) :
			`fzcss-${ uniqueId }`;
	};

	const checkInput = ( editor, ignoreErrors = false ) => {
		let editorErrors = editor?.state?.lint?.marked?.filter( ({ __annotation }) => 'error' === __annotation?.severity )?.map( ({ __annotation }) => __annotation?.message );

		if ( ignoreErrors && 0 < editorErrors?.length ) {
			window.feedzyCSSLintIgnored = editorErrors;
		}

		editorErrors = editorErrors?.filter( error => ! window.feedzyCSSLintIgnored.includes( error ) );

		setErrors( editorErrors );
		if ( ! ignoreErrors && 0 < editorErrors?.length ) {
			return;
		}
		setEditorValue( editor?.getValue() );
	};

	useEffect( () => {
		const classes = attributes.customCSS && attributes.className?.includes( 'fzcss-' ) ? attributes.className.split( ' ' ).find( i => i.includes( 'fzcss' ) ) : null;
		let initialValue = 'selector {\n}\n';

		if ( attributes.customCSS ) {
			const regex = new RegExp( '.' + classes, 'g' );
			initialValue = ( attributes.customCSS ).replace( regex, 'selector' );
		}

		editorRef.current = wp.CodeMirror( document.getElementById( 'fz-css-editor' ), {
			value: initialValue,
			autoCloseBrackets: true,
			continueComments: true,
			lineNumbers: true,
			lineWrapping: true,
			matchBrackets: true,
			lint: true,
			gutters: [ 'CodeMirror-lint-markers' ],
			styleActiveLine: true,
			styleActiveSelected: true,
			mode: 'css',
			extraKeys: {
				'Ctrl-Space': 'autocomplete',
				'Alt-F': 'findPersistent',
				'Cmd-F': 'findPersistent'
			}
		});

		editorRef.current.on( 'change', () => {
			clearTimeout( inputTimeout );
			inputTimeout = setTimeout( () => {
				checkInput( editorRef.current );
			}, 500 );
		});
	}, []);

	useEffect( () => {
		const regex = new RegExp( 'selector', 'g' );
		setCustomCSS( editorValue?.replace( regex, `.${ getClassName().split( ' ' ).find( i => i.includes( 'fzcss' ) ) }` ) );
	}, [ editorValue ]);

	useEffect( () => {
		if ( ( 'selector {\n}\n' ).replace( /\s+/g, '' ) === customCSS?.replace( /\s+/g, '' ) ) {
			setAttributes({ customCSS: null });
			return;
		}
		if ( customCSS ) {
			setAttributes({ customCSS });
		}
	}, [ customCSS ]);

	useEffect( () => {
		setAttributes({
			hasCustomCSS: true,
			className: getClassName()
		});
	}, [ attributes ]);

	return (
		<Fragment>
			<p>{__( 'Add your custom CSS.', 'feedzy-rss-feeds' )}</p>

			<div id="fz-css-editor" className="fz-css-editor" />

			{ 0 < errors?.length && (
				<div className='fz-css-errors'>
					<Notice
						status="error"
						isDismissible={ false }
					>
						{ __( 'Attention needed! We found following errors with your code:', 'feedzy-rss-feeds' ) }
					</Notice>

					<pre>
						<ul>
							{
								errors.map( ( e, i ) => {
									return (
										<li key={ i } >{ e }</li>
									);
								})
							}
						</ul>
					</pre>

					<Button
						variant='secondary'
						onClick={() => checkInput( editorRef.current, true )}
						style={{ width: 'max-content', marginBottom: '20px' }}
					>
						{ __( 'Override', 'feedzy-rss-feeds' ) }
					</Button>
				</div>
			) }

			<p>{__( 'Use', 'feedzy-rss-feeds' )} <code>selector</code> {__( 'to target block wrapper.', 'feedzy-rss-feeds' )}</p>
			<br />
			<p>{__( 'Example:', 'feedzy-rss-feeds' )}</p>

			<pre className="fz-css-editor-help">
				{'selector {\n    background: #000;\n}\n\nselector img {\n    border-radius: 100%;\n}'}
			</pre>

			<p>{__( 'You can also use other CSS syntax here, such as media queries.', 'feedzy-rss-feeds' )}</p>
		</Fragment>
	);
};

export default memo( CSSEditor );

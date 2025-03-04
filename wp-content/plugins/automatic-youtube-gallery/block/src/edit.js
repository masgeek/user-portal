/**
 * Import block dependencies
 */
import ServerSideRender from '@wordpress/server-side-render';

import classnames from 'classnames';

import { debounce } from '@wordpress/compose';

import {	 
	InspectorControls,
	PanelColorSettings,
	useBlockProps
} from '@wordpress/block-editor';

import {
	Disabled,
	PanelBody,
	PanelRow,
	RangeControl,
	SelectControl,
	Spinner,
	TextControl,
	TextareaControl,
	ToggleControl
} from '@wordpress/components';

import { 
	useCallback,
	useEffect,
	useRef,
	useState
} from '@wordpress/element';

import { applyFilters } from '@wordpress/hooks';

/**
 * Describes the structure of the block in the context of the editor.
 * This represents what the editor will render when the block is used.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit( { attributes, setAttributes, className, clientId } ) {

	attributes.uid = clientId;

	const [ isLoading, setIsLoading ] = useState( false );
	const [ proxyAttributes, setProxyAttributes ] = useState( attributes );

	const MyLoadingComponent = () => (
		<div className="automatic-youtube-gallery-block-spinner">
			<Spinner />
			{ ayg_block.i18n.is_loading }
		</div>
	);

	const MyServerSideRender = () => (
		<ServerSideRender
			block="automatic-youtube-gallery/block"
			attributes={ Object.assign( {}, proxyAttributes, { is_admin: true } ) }
		/>
   	);

   	const MemoizedServerSideRender = useCallback( MyServerSideRender, [ proxyAttributes ] );

	const debounceProxyAttributes = () => {
		setIsLoading( false );
		setProxyAttributes( { ...attributes } );		
	}

	const debouncedProxyAttributes = debounce( debounceProxyAttributes, 1000 );

	const getControl = ( field, index ) => {
		if ( ! canShowControl( field.name ) ) {
			return '';
		}

		const placeholder = field.placeholder ? field.placeholder : '';
		const description = field.description ? field.description : '';

		switch ( field.type ) {		
			case 'number':
				return <PanelRow key={ index }>
					<RangeControl	
						label={ field.label }
						help={ description }
						placeholder={ placeholder }
						value={ attributes[ field.name ] }
						min={ field.min }
						max={ field.max }
						onChange={ onChange( field.name ) }
					/>
				</PanelRow>
			case 'textarea':
				return <PanelRow key={ index }>
					<TextareaControl
						label={ field.label }
						help={ description }
						placeholder={ placeholder }
						value={ attributes[ field.name ] }
						onChange={ onChange( field.name ) }
					/>
				</PanelRow>
			case 'select':
			case 'radio':
				let options = [];

				for ( let key in field.options ) {
					options.push({
						label: field.options[ key ],
						value: key
					});
				};

				return <PanelRow key={ index }>
					<SelectControl
						label={ field.label }
						help={ description }						
						options={ options }
						value={ attributes[ field.name ] }
						onChange={ onChange( field.name ) }
					/>
				</PanelRow>
			case 'checkbox':
				return <PanelRow key={ index }>
					<ToggleControl
						label={ field.label }
						help={ description }
						checked={ attributes[ field.name ] }
						onChange={ toggleAttribute( field.name ) }
					/>
				</PanelRow>
			case 'color':
				return <PanelRow key={ index }>
					<PanelColorSettings
						title={ field.label }
						colorSettings={ [
							{
								label: ayg_block.i18n.selected_color,
								value: attributes[ field.name ],
								onChange: onChange( field.name ),							
							}
						] }
					>						
					</PanelColorSettings>
				</PanelRow>
			default:
				return <PanelRow key={ index }>
					<TextControl	
						label={ field.label }
						help={ description }
						placeholder={ placeholder }
						value={ attributes[ field.name ] }
						onChange={ onChange( field.name ) }
					/>
				</PanelRow>
		}		
	}

	const canShowPanel = ( panel ) => {
		let value = true;

		switch ( panel ) {
			case 'gallery':
				if ( 'video' == attributes.type || 'livestream' == attributes.type ) {
					value = false;
				}
				break;
		}		

		return applyFilters( 'ayg_block_toggle_panels', value, panel, attributes );
	}

	const canShowControl = ( control ) => {
		let value = true;

		switch ( control ) {
			case 'playlist':			
			case 'username':
			case 'search':
			case 'video':
			case 'videos':
				if ( control != attributes.type ) {
					value = false;
				}
				break;
			case 'channel':
				if ( 'channel' != attributes.type && 'livestream' != attributes.type ) {
					value = false;
				}
				break;
			case 'order':
			case 'limit':
				if ( 'search' != attributes.type ) {
					value = false;
				}
				break;
			case 'cache':
			case 'player_title':
			case 'player_description':
			case 'loop':
				if ( 'livestream' == attributes.type ) {
					value = false;
				}
				break;
			case 'autoadvance':
				if ( 'video' == attributes.type || 'livestream' == attributes.type ) {
					value = false;
				}
				break;
			case 'more_button_label':
				if ( 'pager' == attributes.pagination_type ) {
					value = false;
				}
				break;
			case 'previous_button_label':
			case 'next_button_label':
				if ( 'more' == attributes.pagination_type ) {
					value = false;
				}
				break;
		}

		return applyFilters( 'ayg_block_toggle_controls', value, control, attributes );
	}

	const onChange = ( attribute ) => {
		return ( newValue ) => {
			setAttributes( { [ attribute ]: newValue } );	
		};
	}

	const toggleAttribute = ( attribute ) => {
		return ( newValue ) => {
			setAttributes( { [ attribute ]: newValue } );
		};
	}	

	useEffect(() => {
		setIsLoading( true );
		debouncedProxyAttributes();
	  }, [ attributes ] );

	const mounted = useRef();	
	useEffect( () => {		
		if ( ! mounted.current ) {
			// Do componentDidMount logic
			mounted.current = true;
		} else {
			// Do componentDidUpdate logic
			applyFilters( 'ayg_block_init', attributes );
		}
	} );

	const classes = classnames( className, {
		'is-loading': isLoading,
	} );

	const blockProps = useBlockProps( {
		className: classes,
	} );

	return (
		<>
			<InspectorControls>
				{Object.keys( ayg_block.options ).map(( key, index ) => {
					return (
						canShowPanel( key ) && <PanelBody 
							key={ 'automatic-youtube-gallery-block-panel-' + index } 
							title={ ayg_block.options[ key ].label }
							initialOpen={ 0 == index ? true : false }
							className="automatic-youtube-gallery-block-panel">

							{Object.keys( ayg_block.options[ key ].fields ).map(( _key, _index ) => {
								return getControl( ayg_block.options[ key ].fields[ _key ], 'automatic-youtube-gallery-block-control-' + _index );
							})}

						</PanelBody>
					)
				})}
			</InspectorControls>			

			<div { ...blockProps }>
				<Disabled>
					{ isLoading && <MyLoadingComponent /> }
					<MemoizedServerSideRender />
				</Disabled>
			</div>
		</>
	);	
}

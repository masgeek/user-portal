(function( $ ) {
	'use strict';

	/**
 	 * Display the media uploader.
 	 *
 	 * @since 1.0.0
 	 */
	function ayg_render_media_uploader( $elem ) { 
    	var file_frame, attachment;
 
     	// If an instance of file_frame already exists, then we can open it rather than creating a new instance
    	if ( file_frame ) {
        	file_frame.open();
        	return;
    	}; 

     	// Use the wp.media library to define the settings of the media uploader
    	file_frame = wp.media.frames.file_frame = wp.media({
        	frame: 'post',
        	state: 'insert',
        	multiple: false
    	});
 
     	// Setup an event handler for what to do when a media has been selected
    	file_frame.on( 'insert', function() { 
        	// Read the JSON data returned from the media uploader
    		attachment = file_frame.state().get( 'selection' ).first().toJSON();
		
			// First, make sure that we have the URL of the media to display
    		if ( 0 > $.trim( attachment.url.length ) ) {
        		return;
    		};
		
			// Set the data
			$elem.prev( '.ayg-settings-url' )
				.val( attachment.url ); 
    	});
 
    	// Now display the actual file_frame
    	file_frame.open(); 
	};

	/**
 	 * Get Youtube playlist ID from Youtube URL.
 	 *
 	 * @since  1.0.0
	 * @param  {string} url - URL from which to extract the ID.
     * @return {string}
 	 */
	function ayg_get_youtube_playlist_id( url ) {
		var id = /[&|\?]list=([a-zA-Z0-9_-]+)/gi.exec( url );
  		return ( id && id.length > 0 ) ? id[1] : url;		  
	}

	 /**
 	 * Get Youtube channel ID from Youtube URL.
 	 *
 	 * @since  1.0.0
	 * @param  {string} url - URL from which to extract the ID.
     * @return {object}
 	 */
	function ayg_get_youtube_channel_id( url ) {
		var type = 'channel';
		var id = url;

		url = url.replace( /(>|<)/gi, '' ).split( /(\/channel\/|\/user\/)/ );

		if ( url[2] !== undefined ) {
			id = url[2].split( /[^0-9a-z_-]/i );
			id = id[0];
		}

		if ( /\/user\//.test( url ) ) { 
			type = 'username';
		}

		return {
			type: type,
			id: id
		};		  
	}

	/**
 	 * Get Youtube video ID from Youtube URL.
 	 *
 	 * @since  1.0.0
	 * @param  {string} url - URL from which to extract the ID.
     * @return {string}
 	 */
	 function ayg_get_youtube_video_id( url ) {
		var id = url;

		url = url.replace( /(>|<)/gi, '' ).split( /(vi\/|v=|\/v\/|youtu\.be\/|\/embed\/)/ );

		if ( url[2] !== undefined ) {
		  id = url[2].split( /[^0-9a-z_\-]/i );
		  id = id[0];
		}

		return id;		  
	}

	/**
 	 * Widget: Initiate color picker 
 	 *
 	 * @since 1.0.0
 	 */
	function ayg_widget_color_picker( widget ) {
		widget.find( '.ayg-color-picker' ).wpColorPicker( {
			change: _.throttle( function() { // For Customizer
				$( this ).trigger( 'change' );
			}, 3000 )
		});
	}

	function on_ayg_widget_update( event, widget ) {
		ayg_widget_color_picker( widget );
	}

	/**
	 * Called when the page has loaded.
	 *
	 * @since 1.0.0
	 */
	$(function() {
		// Common: Initialize the color picker
		$( '.ayg-color-picker' ).wpColorPicker();

		// Common: Init the popup.
		if ( $.fn.magnificPopup ) {
			$( '.ayg-modal-button' ).magnificPopup({
				type: 'inline',
				mainClass: 'mfp-fade'
			});
		}

		// Dashboard: Save API Key
		$( '#ayg-button-save-api-key' ).on( 'click', function( e ) {																			  
			e.preventDefault();
			
			$( this ).prop( 'disabled', true );
			$( '.ayg-ajax-status', '#ayg-table-api-key' ).html( '<span class="spinner"></span>' );

			var data = {
				'action': 'ayg_save_api_key',
				'api_key': $( '#ayg-api-key' ).val(),
				'security': ayg_admin.ajax_nonce
			};

			if ( ! data.api_key ) {
				$( this ).prop( 'disabled', false );
				$( '.ayg-ajax-status', '#ayg-table-api-key' ).html( '<span class="ayg-text-error">' + ayg_admin.i18n.invalid_api_key + '</span>' );
				
				return false;
			}
			
			$.post( ajaxurl, data, function( response ) {
				window.location.reload(); // Reload document
			});			
		});

		// Dashboard: Generate shortcode
		$( '#ayg-generate-shortcode' ).on( 'click', function( e ) { 
			e.preventDefault();			

			// Attributes
			var props = {};
			
			$( '.ayg-editor-control', '#ayg-shortcode-builder' ).each(function() {							
				var $elem = $( this ).find( '.ayg-editor-field' );
				var type  = $elem.attr( 'type' );
				var key   = $elem.attr( 'name' );				
				var value = $elem.val();
				var def   = $elem.data( 'default' );
				
				// field type = checkbox
				if ( 'checkbox' == type ) {
					value = $elem.is( ':checked' ) ? 1 : 0;
				}

				// source = playlist
				if ( 'playlist' == key ) {
					value = ayg_get_youtube_playlist_id( value );
				}

				// source = channel
				if ( 'channel' == key ) {
					var result = ayg_get_youtube_channel_id( value );
					
					key   = result.type;
					value = result.id;

					if ( props.hasOwnProperty( 'type' ) && 'channel' == props.type ) {
						props.type = key;
					}
				}

				// source = video
				if ( 'video' == key ) {
					value = ayg_get_youtube_video_id( value );
				}

				// source = videos
				if ( 'videos' == key ) {
					var lines = value.split( '\n' ),
						ids = [];

					lines.map(function( url ) {
						ids.push( ayg_get_youtube_video_id( url ) );
					});
					
					value = ids.join( ',' );
				}
				
				// Add only if the user input differ from the global configuration
				if ( value != def ) {
					props[ key ] = value;
				}				
			});

			var attrs = '';
			for ( var key in props ) {
				if ( props.hasOwnProperty( key ) ) {
					attrs += ( ' ' + key + '="' + props[ key ] + '"' );
				}
			}

			// Shortcode output		
			$( '#aiovg-shortcode').val( '[automatic_youtube_gallery' + attrs + ']' ); 
		});

		// Editor: Toggle between field sections
		$( document ).on( 'click', '.ayg-editor-section-header', function( e ) {
			var $elem = $( this ).parent();

			if ( ! $elem.hasClass( 'ayg-active' ) ) {
				$( this ).closest( '.ayg-editor' )
					.find( '.ayg-editor-section.ayg-active' )
					.toggleClass( 'ayg-active' )
					.find( '.ayg-editor-controls' )
					.slideToggle();
			}			

			$elem.toggleClass( 'ayg-active' )
				.find( '.ayg-editor-controls' )
				.slideToggle();
		});

		// Editor: Toggle fields based on the source type
		$( document ).on( 'change', '.ayg-editor-field-type', function() {
			var $container = $( this ).closest( '.ayg-editor' );			
			var value = $( this ).val();			

			$container.removeClass(function( index, classes ) {
				var matches = classes.match( /\ayg-editor-field-type-\S+/ig );
				return ( matches ) ? matches.join( ' ' ) : '';
			});

			$container.addClass( 'ayg-editor-field-type-' + value );
		});

		// Editor: Toggle fields based on the theme
		$( document ).on( 'change', '.ayg-editor-field-theme', function() {
			var $container = $( this ).closest( '.ayg-editor' );			
			var value = $( this ).val();			

			$container.removeClass(function( index, classes ) {
				var matches = classes.match( /\ayg-editor-field-theme-\S+/ig );
				return ( matches ) ? matches.join( ' ' ) : '';
			});

			$container.addClass( 'ayg-editor-field-theme-' + value );
		});	

		// Editor: Toggle fields based on the pagination type
		$( document ).on( 'change', '.ayg-editor-field-pagination_type', function() {			
			var $container = $( this ).closest( '.ayg-editor' );
			var value = $( this ).val();

			$container.removeClass(function( index, classes ) {
				var matches = classes.match( /\ayg-editor-field-pagination_type-\S+/ig );
				return ( matches ) ? matches.join( ' ' ) : '';
			});

			$container.addClass( 'ayg-editor-field-pagination_type-' + value );
		});		

		// Settings: Toggle fields based on the theme
		$( 'tr.theme select', '#ayg-settings' ).on( 'change', function() {
			var $container = $( '#ayg-settings' );		
			var value = $( this ).val();			

			$container.removeClass(function( index, classes ) {
				var matches = classes.match( /theme-\S+/ig );
				return ( matches ) ? matches.join( ' ' ) : '';
			});

			$container.addClass( 'theme-' + value );
		});

		// Settings: Toggle fields based on the pagination type
		$( 'tr.pagination_type select', '#ayg-settings' ).on( 'change', function() {
			var $container = $( '#ayg-settings' );			
			var value = $( this ).val();			

			$container.removeClass(function( index, classes ) {
				var matches = classes.match( /pagination_type-\S+/ig );
				return ( matches ) ? matches.join( ' ' ) : '';
			});

			$container.addClass( 'pagination_type-' + value );
		});

		// Settings: Toggle fields based on the player type
		$( 'tr.player_type input[type="radio"]', '#ayg-settings' ).on( 'change', function() {	
			var $container = $( '#ayg-settings' );		
			var value = $container.find( 'tr.player_type input[type="radio"]:checked' ).val();			

			$container.removeClass(function( index, classes ) {
				var matches = classes.match( /player_type-\S+/ig );
				return ( matches ) ? matches.join( ' ' ) : '';
			});

			$container.addClass( 'player_type-' + value );
		});

		// Settings: Browse button
		$( '.ayg-settings-browse' ).on( 'click', function( e ) {																	  
			e.preventDefault();			
			ayg_render_media_uploader( $( this ) );			
		});
		
		// Settings: Delete cache
		$( '#ayg-button-delete-cache' ).on( 'click', function( e ) {																			  
			e.preventDefault();
			
			$( this ).prop( 'disabled', true );
			$( '.ayg-ajax-status', '#ayg-table-delete-cache' ).html( '<span class="spinner"></span>' );

			var data = {
				'action': 'ayg_delete_cache',
				'security': ayg_admin.ajax_nonce
			};
			
			$.post( ajaxurl, data, function( response ) {
				$( this ).prop( 'disabled', false );
				$( '.ayg-ajax-status', '#ayg-table-delete-cache' ).html( '<span class="ayg-text-success">' + ayg_admin.i18n.cleared + '</span>' );
			});			
		});

		// Widget: Initiate color picker 
		$( '#widgets-right .widget:has(.ayg-color-picker)' ).each(function() {
			ayg_widget_color_picker( $( this ) );
		});

		$( document ).on( 'widget-added widget-updated', on_ayg_widget_update );
	});

})( jQuery );

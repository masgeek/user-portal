'use strict';

/**
 * Theme: Classic.
 */
class AYGThemeClassicElement extends HTMLElement {

    /**
     * Element created.
     */
    constructor() {
        super();

        // Set references to the DOM elements used by the component
        this.$el = null;        
        this.$selectedVideo = null;
        this.$pagination = null;
        this.$nextButton = null;  
        
        this.player = null;

        // Set references to the private properties used by the component
        this._params = {};
        this._selectedVideoId = '';
        this._paginationType = 'none';
        this._pageTopOffset = ayg_config.top_offset;
    }

    /**
     * Browser calls this method when the element is added to the document.
     * (can be called many times if an element is repeatedly added/removed)
     */
    connectedCallback() { 
        this.$el = jQuery( this );        
        this.$selectedVideo = this.$el.find( '.ayg-video' ).eq(0);
        this.$pagination = this.$el.find( '.ayg-pagination' ); 
        
        this.player = this.$el.find( 'ayg-player' ).get(0);

		this._params = this.$el.data( 'params' );
        this._selectedVideoId = this.$selectedVideo.find( '.ayg-thumbnail' ).data( 'id' );		
		
		if ( this.$pagination.length > 0 ) {
			this.$nextButton = this.$pagination.find( '.ayg-pagination-next-btn' );
			this._paginationType = this.$nextButton.data( 'type' );
		}		

		this.$el.on( 'click', '.ayg-video', ( event ) => this._onThumbnailClicked( event ) );        
        this.$pagination.on( 'videos.updated', ( event ) => this._onVideosUpdated( event ) );

        this.player.addEventListener( 'ended', () => this._onVideoEnded() );
    }

    /**
     * Browser calls this method when the element is removed from the document.
     * (can be called many times if an element is repeatedly added/removed)
     */
    disconnectedCallback() {
        this.$el.off( 'click', '.ayg-video', ( event ) => this._onThumbnailClicked( event ) );        
        this.$pagination.off( 'videos.updated', ( event ) => this._onVideosUpdated( event ) );

        this.player.removeEventListener( 'ended', () => this._onVideoEnded() );
    }

    /**
     * Define private methods.
     */

    _onThumbnailClicked( event ) {
        this.$selectedVideo = jQuery( event.currentTarget );

        if ( this.$selectedVideo.hasClass( 'ayg-active' ) ) {
			return false;
		}

        this.$el.find( '.ayg-active' ).removeClass( 'ayg-active' );			
        this.$selectedVideo.addClass( 'ayg-active' );

        // Change Video
        this._selectedVideoId = this.$selectedVideo.find( '.ayg-thumbnail' ).data( 'id' );
        
        const title = this.$selectedVideo.find( '.ayg-thumbnail' ).data( 'title' );	
        const description = this.$selectedVideo.find( '.ayg-thumbnail-description' ).html();
        const poster = this.$selectedVideo.find( '.ayg-thumbnail-image' ).attr( 'src' );

        this.player.change({
            id:  this._selectedVideoId,
            title: title,
            poster: poster
        });
        
        if ( this._params.player_title == 1 ) {            			
            this.$el.find( '.ayg-player-title' ).html( title );
        }

        if ( this._params.player_description == 1 ) {            
            this.$el.find( '.ayg-player-description' ).html( description );
        }
        
        // Scroll to Top
        jQuery( 'html, body' ).animate({
            scrollTop: this.$el.offset().top - this._pageTopOffset
        }, 500, () => {
            // Change URL in Browser Address Bar
            const url = this.$selectedVideo.find( '.ayg-thumbnail' ).data( 'url' );
            if ( url != '' ) {
                window.history.replaceState( null, null, url );
            }				
        });	
        
        // Load Next Page
        if ( this._params.autoadvance == 1 && this._paginationType == 'more' ) {
            if ( this.$selectedVideo.is( ':last-child' ) && this.$nextButton.is( ':visible' ) ) {					
                this.$nextButton.trigger( 'click' );
            }
        }
    }   

    _onVideosUpdated( event ) {
        if ( this.$el.find( '.ayg-active' ).length > 0 ) {
            return false;
        }

        if ( this.$el.find( '.ayg-video-' +  this._selectedVideoId ).length > 0 ) {
            this.$selectedVideo = this.$el.find( '.ayg-video-' +  this._selectedVideoId ).addClass( 'ayg-active' );
        }
    }

    _onVideoEnded() {
        if ( this._params.autoadvance == 1 ) {
            this.player.stop();

            if ( this.$selectedVideo.is( ':visible' ) ) {
                if ( this.$selectedVideo.is( ':last-child' ) ) {
                    if ( this._paginationType == 'more' || this._paginationType == 'none' ) {
                        if ( this._params.loop == 1 ) {
                            this.$el.find( '.ayg-video' ).eq(0).trigger( 'click' );
                        }
                    } else {
                        // Load Next Page
                        if ( this.$nextButton.is( ':visible' ) ) {					
                            this.$nextButton.trigger( 'click' );

                            const intervalHandler = setInterval(() => {												
                                if ( this.$el.find( '.ayg-pagination.ayg-loading' ).length == 0 ) {
                                    clearInterval( intervalHandler );
                                    this.$el.find( '.ayg-video' ).eq(0).trigger( 'click' );
                                }												
                            }, 1000);									
                        }									
                    }
                } else {
                    this.$selectedVideo.next( '.ayg-video' ).trigger( 'click' );
                }
            } else {
                this.$el.find( '.ayg-video' ).eq(0).trigger( 'click' );
            }
        } else {
            if ( this._params.loop == 1 ) {
                this.player.play();
            } else {
                this.player.stop();
            }
        }
    }

}

// Register custom element
document.addEventListener( 'DOMContentLoaded', function() {
    customElements.define( 'ayg-theme-classic', AYGThemeClassicElement );
});
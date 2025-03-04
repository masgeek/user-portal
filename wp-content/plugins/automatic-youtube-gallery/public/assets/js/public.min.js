'use strict';

const AYGPlayerTemplate = document.createElement( 'template' );

AYGPlayerTemplate.innerHTML = `
    <style>
        :host {                             
            display: block;  
            width: 100%;      
            contain: content;
        }

        :host([hidden]) {
            display: none;
        }

        :host([ratio="auto"]) {
            position: absolute;
            inset: 0;
            height: 100%;
        }

        #root {
            display: block;
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
            cursor: pointer;
            line-height: 1.25;
            font-size: 16px;
        }

        :host([ratio="auto"]) #root {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }

        :host:not([ratio="auto"]) #root {      
            position: relative;
            padding-bottom: calc(100% / (16 / 9));
            width: 100%;
            height: 0;
        }
    
        iframe {
            position: absolute;
            inset: 0;
            z-index: 1;
            border: 0;
            width: 100%;
            height: 100%;                   
        }        

        #play-button {
            display: block;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate3d(-50%, -50%, 0); 
            transition: all 0.2s cubic-bezier(0, 0, 0.2, 1); 
            z-index: 1;
            border: 0;        
            background: center/72px 48px no-repeat url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 72 48'%3E%3Cpath fill='%23f00' fill-opacity='.9' d='M66.5 7.7c-.8-2.9-2.5-5.4-5.4-6.2C55.8.1 34 0 34 0S12.2.1 6.9 1.6c-3 .7-4.6 3.2-5.4 6.1a89.6 89.6 0 000 32.5c.8 3 2.5 5.5 5.4 6.3C12.2 47.9 34 48 34 48s21.8-.1 27.1-1.6c3-.7 4.6-3.2 5.4-6.1C68 35 68 24 68 24s0-11-1.5-16.3z'/%3E%3Cpath fill='%23fff' d='M45 24L27 14v20'/%3E%3C/svg%3E");
            cursor: pointer;
            width: 72px;
            height: 48px;
            filter: grayscale(1);   
        }       
        
        #root:hover > #play-button,
        #play-button:focus {
            filter: none;
        }

        /* Cookie consent */
        #cookieconsent-modal {  
            box-sizing: border-box;
            display: none;          
            position: absolute; 
            top: 50%;
            left: 50%;
            transform: translate3d(-50%, -50%, 0);
            z-index: 1;
            border-radius: 3px; 
            background: rgba(0, 0, 0, 0.7);
            padding: 1em;
            width: 90%;
            max-width: 640px;            
            color: #fff;
        }  
        
        @media only screen and (max-width: 320px) {
            #cookieconsent-modal {
                width: 100%;
                height: 100%;
            }
        }

        #cookieconsent-button {
            display: block;
            margin: auto;
            margin-top: 0.75em;
            border: 0;
            border-radius: 3px;  
            background: #e70808;
            cursor: pointer; 
            padding: 0.5em 1em;   
            color: #fff; 
        }

        #cookieconsent-button:hover,
        #cookieconsent-button:focus {
            background: #fff;
            color: #333;
        }

        #root.cookieconsent {
            cursor: unset;
        }

        #root.cookieconsent > #play-button {
            display: none;
        }

        #root.cookieconsent > #cookieconsent-modal {
            display: block;
        }

        /* Post-click styles */
        #root.initialized {
            cursor: unset;
        }

        #root.initialized > #play-button,
        #root.initialized > #cookieconsent-modal {            
            display: none;
        }
    </style>
    <div id="root">
        <button type="button" id="play-button" aria-label="Play Video"></button>
        <div id="cookieconsent-modal">
            <div id="cookieconsent-message">Please accept YouTube cookies to play this video. By accepting you will be accessing content from YouTube, a service provided by an external third party.</div>
            <button type="button" id="cookieconsent-button">I Agree</button>
        </div>
        <slot name="player"></slot>
    </div>
`;

class AYGPlayerElement extends HTMLElement{constructor(){super();let e=this.attachShadow({mode:"open"});this.shadowRoot.appendChild(AYGPlayerTemplate.content.cloneNode(!0)),this.rootEl=e.querySelector("#root"),this.playButtonEl=e.querySelector("#play-button"),this.cookieConsentMessageEl=e.querySelector("#cookieconsent-message"),this.cookieConsentButtonEl=e.querySelector("#cookieconsent-button"),this.playerEl=null,this._isRendered=!1,this._isCookieConsentAdded=!1,this._isPosterImageAdded=!1,this._isPlayerAdded=!1,this._forcePlayerElement=navigator.vendor.includes("Apple")||navigator.userAgent.includes("Mobi"),this._intersectionObserver=null,this._isInViewport=!1,this._hasPlayerControls=!0,this._hasAutoplayRequested=!1,this._hasMuted=!1,this._hasYTApiEnabled=!1,this._playerApi=null,this._playerType=ayg_config.player_type,this._playerColor=ayg_config.player_color,this._hasCookieConsent=1==parseInt(ayg_config.cookieconsent),this._cookieConsentMessage=ayg_config.cookieconsent_message||"",this._cookieConsentButtonLabel=ayg_config.cookieconsent_button_label||"",this._ajaxUrl=ayg_config.ajax_url,this._ajaxNonce=ayg_config.ajax_nonce}connectedCallback(){if(!this.src)return!1;let e=new URL(this.src),t=new URLSearchParams(e.search);this._hasPlayerControls=!(t.has("controls")&&(0==t.get("controls")||!1==t.get("controls"))),this._hasAutoplayRequested=t.has("autoplay")&&(1==t.get("autoplay")||!0==t.get("autoplay")),this._hasMuted=t.has("mute")&&(1==t.get("mute")||!0==t.get("mute")),this._hasYTApiEnabled=t.has("enablejsapi")&&(1==t.get("enablejsapi")||!0==t.get("enablejsapi")),"custom"==this._playerType&&(this._forcePlayerElement=!0),this.lazyLoad||(this._forcePlayerElement=!0),this.poster||(this._forcePlayerElement=!0),this._hasAutoplayRequested&&(this._forcePlayerElement=!0),this._render(),this.addEventListener("pointerover",()=>this._warmConnections(),{once:!0}),this.addEventListener("focusin",()=>this._warmConnections(),{once:!0}),this.addEventListener("click",()=>this._addPlayer(!0)),this.cookieConsentButtonEl.addEventListener("click",()=>this._onCookieConsent())}disconnectedCallback(){this.removeEventListener("pointerover",()=>this._warmConnections(),{once:!0}),this.removeEventListener("focusin",()=>this._warmConnections(),{once:!0}),this.removeEventListener("click",()=>this._addPlayer(!0)),this.cookieConsentButtonEl.removeEventListener("click",()=>this._onCookieConsent())}static get observedAttributes(){return["ratio"]}attributeChangedCallback(e,t,s){if(t==s)return!1;"ratio"===e&&("auto"==s?this.rootEl.style.paddingBottom=0:this.rootEl.style.paddingBottom=`${parseFloat(s)}%`)}get title(){return this.getAttribute("title")||""}set title(e){this.setAttribute("title",e)}get src(){let e=this.getAttribute("src")||"";return AYGPlayerElement.isValidUrl(e)?e:""}set src(e){AYGPlayerElement.isValidUrl(e)&&this.setAttribute("src",e)}get poster(){let e=this.getAttribute("poster")||"";return AYGPlayerElement.isValidUrl(e)?e:""}set poster(e){AYGPlayerElement.isValidUrl(e)&&this.setAttribute("poster",e)}get lazyLoad(){return this.hasAttribute("lazyload")}_render(){return!this._isRendered&&(this.lazyLoad&&!this._isInViewport?(this._initIntersectionObserver(),!1):this._hasCookieConsent?(this._addCookieConsent(),!1):void(this._isRendered=!0,this._forcePlayerElement?this._addPlayer():this._addPosterImage()))}_addCookieConsent(){if(this._isCookieConsentAdded)return!1;this._isCookieConsentAdded=!0,this._addPosterImage(),this._cookieConsentMessage&&(this.cookieConsentMessageEl.innerHTML=this._cookieConsentMessage),this._cookieConsentButtonLabel&&(this.cookieConsentButtonEl.innerHTML=this._cookieConsentButtonLabel),this._addClass("cookieconsent")}_onCookieConsent(){this._isRendered=!0;let e=document.querySelectorAll("ayg-player");for(let t=0;t<e.length;t++)e[t].removeCookieConsent();this._addPlayer(!0),this._setCookie()}_addPosterImage(){if(this._isPosterImageAdded)return!1;this._isPosterImageAdded=!0,this.poster&&(this.rootEl.style.backgroundImage=`url("${this.poster}")`)}_addPlayer(e=!1){if(this._isPlayerAdded||this._hasCookieConsent)return!1;this._isPlayerAdded=!0,this._addClass("initialized");let t=this._createIframeEmbed(e);if("custom"==this._playerType){let s=document.createElement("div");s.setAttribute("slot","player"),s.style="--plyr-color-main: "+this._playerColor,s.append(t),this.playerEl=s,this.append(s),this._initPlyrApi(e)}else this.playerEl=t,this.rootEl.append(t),t.focus(),this._initYTApi(e)}_createIframeEmbed(e){let t=document.createElement("iframe");if(t.id="player",t.width=560,t.height=315,t.title=this.title,t.allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share",t.allowFullscreen=!0,e){let s=new URL(this.src),i=s.searchParams;i.set("autoplay",1),s.search=i.toString(),t.src=s.toString()}else t.src=this.src;return t.dataset.poster=this.poster,t}_initPlyrApi(e){let t={resetOnEnd:!0,fullscreen:{enabled:!0,iosNative:!0}};e&&(t.autoplay=!0),this._hasMuted&&(t.muted=!0);let s=["play-large"];if(this._hasPlayerControls){s=["play-large","play","current-time","progress","duration","mute","volume","fullscreen"];let i=/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent);i&&(s=["play-large","play","progress","current-time","mute","fullscreen"])}t.controls=s,this._plyr=new Plyr(this.playerEl,t),this._plyr.on("ready",e=>{this._playerApi=e.detail.plyr.embed,this._plyr.autoplay=!0});let a=!1;this._plyr.on("playing",e=>{a||(a=!0,e.target.className+=" plyr--initialized",this._hasPlayerControls||(e.target.className+=" plyr--no-controls"));let t=document.querySelectorAll("ayg-player");for(let s=0;s<t.length;s++)t[s]!=this&&t[s].pause()}),this._plyr.on("ended",e=>{e.target.className+=" plyr--stopped"})}_initYTApi(e){if(!this._hasYTApiEnabled)return!1;this._loadYTApi().then(()=>{this._playerApi=new YT.Player(this.playerEl,{events:{onReady:t=>{e&&this.play()},onStateChange:e=>{if(0==e.data&&this._dispatchEvent("ended"),1==e.data){let t=document.querySelectorAll("ayg-player");for(let s=0;s<t.length;s++)t[s]!=this&&t[s].pause()}}}})})}_loadYTApi(){return new Promise(e=>{if(void 0===window.YT&&void 0===AYGPlayerElement.isApiLoaded){AYGPlayerElement.isApiLoaded=!0;var t=document.createElement("script");t.src="https://www.youtube.com/iframe_api";var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(t,s)}if(void 0!==window.YT&&window.YT.loaded)e();else{let i=setInterval(function(){void 0!==window.YT&&window.YT.loaded&&(clearInterval(i),e())},10)}})}_initIntersectionObserver(){if(this._intersectionObserver)return!1;this._intersectionObserver=new IntersectionObserver((e,t)=>{e.forEach(e=>{e.isIntersecting?(this._isInViewport=!0,this._render(),this._isRendered&&t.unobserve(this)):this._isInViewport=!1})},{root:null,rootMargin:"0px",threshold:0}),this._intersectionObserver.observe(this)}_warmConnections(){if(AYGPlayerElement.isPreconnected)return!1;this.src.indexOf("www.youtube-nocookie.com")>-1?AYGPlayerElement.addPrefetch("preconnect","https://www.youtube-nocookie.com"):AYGPlayerElement.addPrefetch("preconnect","https://www.youtube.com"),AYGPlayerElement.addPrefetch("preconnect","https://www.google.com"),AYGPlayerElement.addPrefetch("preconnect","https://googleads.g.doubleclick.net"),AYGPlayerElement.addPrefetch("preconnect","https://static.doubleclick.net"),AYGPlayerElement.isPreconnected=!0}_hasClass(e){return this.rootEl.classList.contains(e)}_addClass(e){this.rootEl.classList.add(e)}_removeClass(e){this.rootEl.classList.remove(e)}_dispatchEvent(e){let t=new CustomEvent(e,{detail:{},bubbles:!0,cancelable:!0});this.dispatchEvent(t)}async _setCookie(){try{let e=new FormData;e.append("action","ayg_set_cookie"),e.append("security",this._ajaxNonce),fetch(this._ajaxUrl,{method:"POST",body:e})}catch(t){}}static isValidUrl(e){if(""==e)return!1;try{return new URL(e),!0}catch(t){return!1}}static addPrefetch(e,t){let s=document.createElement("link");s.rel=e,s.href=t,document.head.append(s)}removeCookieConsent(){this._hasCookieConsent=!1,this._removeClass("cookieconsent"),this._render()}play(){if(!this._playerApi)return!1;this._playerApi.playVideo&&this._playerApi.playVideo()}pause(){if(!this._playerApi)return!1;this._playerApi.pauseVideo&&this._playerApi.pauseVideo()}change(e){if(this._playerApi)e.hasOwnProperty("id")&&this._playerApi.loadVideoById&&this._playerApi.loadVideoById(e.id);else{if(e.hasOwnProperty("id")){let t=new URL(this.src);t.pathname=`/embed/${e.id}`;let s=t.searchParams;s.set("autoplay",1),t.search=s.toString(),this.src=t.toString(),this._isPlayerAdded&&this.playerEl.setAttribute("src",this.src)}e.hasOwnProperty("poster")&&(this.poster=e.poster,this._isPosterImageAdded&&(this._isPlayerAdded?this.rootEl.style.backgroundImage="none":this.rootEl.style.backgroundImage=`url("${this.poster}")`)),this._isPlayerAdded||this._hasCookieConsent||this._addPlayer(!0)}e.hasOwnProperty("title")&&(this.title=e.title)}stop(){if(!this._playerApi)return!1;this._playerApi.stopVideo&&this._playerApi.stopVideo()}}class AYGDescriptionElement extends HTMLElement{constructor(){super(),this._showMoreButtonLabel=ayg_config.i18n.show_more,this._showLessButtonLabel=ayg_config.i18n.show_less}connectedCallback(){jQuery(this).on("click",".ayg-player-description-toggle-btn",e=>this._toggle(e))}disconnectedCallback(){jQuery(this).off("click",".ayg-player-description-toggle-btn",e=>this._toggle(e))}_toggle(e){e.preventDefault();let t=jQuery(this).find(".ayg-player-description-dots"),s=jQuery(this).find(".ayg-player-description-more");t.is(":visible")?(e.currentTarget.innerHTML=this._showLessButtonLabel,t.hide(),s.fadeIn()):s.fadeOut(()=>{e.currentTarget.innerHTML=this._showMoreButtonLabel,t.show()})}}class AYGPaginationElement extends HTMLElement{constructor(){super(),this.$el=null,this.$videos=null,this.$nextButton=null,this.$previousButton=null,this._formData={},this._ajaxUrl=ayg_config.ajax_url,this._ajaxNonce=ayg_config.ajax_nonce,this._totalPages=1,this._paged=1,this._pageTokens=[""]}connectedCallback(){this.$el=jQuery(this),this.$videos=this.$el.closest(".ayg").find(".ayg-videos"),this._formData=this.$el.data("params"),this._formData.action="ayg_load_more_videos",this._formData.security=this._ajaxNonce,this._totalPages=parseInt(this._formData.total_pages),this.$el.on("click",".ayg-pagination-next-btn",e=>this._next(e)),this.$el.on("click",".ayg-pagination-prev-btn",e=>this._previous(e))}disconnectedCallback(){this.$el.off("click",".ayg-pagination-next-btn",e=>this._next(e)),this.$el.off("click",".ayg-pagination-prev-btn",e=>this._previous(e))}_next(e){this.$el.addClass("ayg-loading"),this.$nextButton=jQuery(e.currentTarget);let t=this.$nextButton.data("type");this._formData.pageToken=this._formData.next_page_token,this._pageTokens[this._paged]=this._formData.pageToken,this._fetch(this._formData,e=>{if(e.success){switch(this._paged=Math.min(this._paged+1,this._totalPages),this._formData.next_page_token="",this._paged<this._totalPages&&e.data.next_page_token&&(this._formData.next_page_token=e.data.next_page_token),t){case"more":this.$videos.append(e.data.html);break;case"next":this.$el.find(".ayg-pagination-prev-btn").show(),this.$el.find(".ayg-pagination-current-page-number").html(this._paged),this.$videos.html(e.data.html)}""==this._formData.next_page_token&&this.$nextButton.hide(),this.$el.trigger("videos.updated")}this.$el.removeClass("ayg-loading")})}_previous(e){this.$el.addClass("ayg-loading"),this.$previousButton=jQuery(e.currentTarget),this._paged=Math.max(this._paged-1,1),this._formData.pageToken=this._pageTokens[this._paged-1],this._fetch(this._formData,e=>{e.success&&(this._formData.next_page_token="",e.data.next_page_token&&(this._formData.next_page_token=e.data.next_page_token),this.$videos.html(e.data.html),this.$el.find(".ayg-pagination-next-btn").show(),this.$el.find(".ayg-pagination-current-page-number").html(this._paged),1==this._paged&&this.$previousButton.hide(),this.$el.trigger("videos.updated")),this.$el.removeClass("ayg-loading")})}_fetch(e,t){jQuery.post(this._ajaxUrl,e,t,"json")}}function getAYGPlayerHtml(e,t){var s="https://www.youtube.com";1==ayg_config.privacy_enhanced_mode&&(s="https://www.youtube-nocookie.com"),e.src=s+"/embed/"+e.id+"?enablejsapi=1&playsinline=1&rel=0",ayg_config.hasOwnProperty("origin")&&ayg_config.origin.length>0&&(e.src+="&origin="+ayg_config.origin),1==(t.hasOwnProperty("autoplay")?parseInt(t.autoplay):0)&&(e.src+="&autoplay=1"),1==(t.hasOwnProperty("muted")?parseInt(t.muted):0)&&(e.src+="&mute=1"),0==(t.hasOwnProperty("controls")?parseInt(t.controls):1)&&(e.src+="&controls=0"),1==(t.hasOwnProperty("modestbranding")?parseInt(t.modestbranding):0)&&(e.src+="&modestbranding=1"),1==(t.hasOwnProperty("cc_load_policy")?parseInt(t.cc_load_policy):0)&&(e.src+="&cc_load_policy=1"),0==(t.hasOwnProperty("iv_load_policy")?parseInt(t.iv_load_policy):0)&&(e.src+="&iv_load_policy=3"),t.hasOwnProperty("hl")&&t.hl.length>0&&(e.src+="&hl="+t.hl),t.hasOwnProperty("cc_lang_pref")&&t.cc_lang_pref.length>0&&(e.src+="&cc_lang_pref="+t.cc_lang_pref);var i='<ayg-player class="mfp-prevent-close"';return i+=' title="'+e.title+'"',i+=' src="'+e.src+'"',i+=' poster="'+e.poster+'"',i+=' ratio="'+e.ratio+'"',i+=">",i+="</ayg-player>"}!function(e){e(function(){let t=ayg_config.gallery_id;""!=t&&e("#ayg-"+t).length&&(history.scrollRestoration?history.scrollRestoration="manual":window.onbeforeunload=function(){window.scrollTo(0,0)},e("html, body").animate({scrollTop:e("#ayg-"+t).offset().top-ayg_config.top_offset},500))})}(jQuery),document.addEventListener("DOMContentLoaded",function(){customElements.define("ayg-player",AYGPlayerElement),customElements.define("ayg-description",AYGDescriptionElement),customElements.define("ayg-pagination",AYGPaginationElement)});
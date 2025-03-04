(()=>{var e={942:(e,t)=>{var a;!function(){"use strict";var n={}.hasOwnProperty;function o(){for(var e="",t=0;t<arguments.length;t++){var a=arguments[t];a&&(e=r(e,l(a)))}return e}function l(e){if("string"==typeof e||"number"==typeof e)return e;if("object"!=typeof e)return"";if(Array.isArray(e))return o.apply(null,e);if(e.toString!==Object.prototype.toString&&!e.toString.toString().includes("[native code]"))return e.toString();var t="";for(var a in e)n.call(e,a)&&e[a]&&(t=r(t,a));return t}function r(e,t){return t?e?e+" "+t:e+t:e}e.exports?(o.default=o,e.exports=o):void 0===(a=function(){return o}.apply(t,[]))||(e.exports=a)}()}},t={};function a(n){var o=t[n];if(void 0!==o)return o.exports;var l=t[n]={exports:{}};return e[n](l,l.exports,a),l.exports}a.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return a.d(t,{a:t}),t},a.d=(e,t)=>{for(var n in t)a.o(t,n)&&!a.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},a.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{"use strict";const e=window.wp.blocks,t=window.wp.serverSideRender;var n=a.n(t),o=a(942),l=a.n(o);const r=window.wp.compose,s=window.wp.blockEditor,i=window.wp.components,c=window.wp.element,p=window.wp.hooks,u=window.ReactJSXRuntime,b=JSON.parse('{"UU":"automatic-youtube-gallery/block"}');(0,e.registerBlockType)(b.UU,{attributes:function(){var e,t,a={is_admin:{type:"boolean",default:!1},uid:{type:"string",default:""}};for(var n in ayg_block.options){var o=ayg_block.options[n].fields;for(var l in o)a[o[l].name]={type:(e=o[l].type,t=void 0,t="string","number"==e?t="number":"checkbox"==e&&(t="boolean"),t),default:o[l].value}}return a}(),edit:function({attributes:e,setAttributes:t,className:a,clientId:o}){e.uid=o;const[b,d]=(0,c.useState)(!1),[y,g]=(0,c.useState)(e),m=()=>(0,u.jsxs)("div",{className:"automatic-youtube-gallery-block-spinner",children:[(0,u.jsx)(i.Spinner,{}),ayg_block.i18n.is_loading]}),h=(0,c.useCallback)((()=>(0,u.jsx)(n(),{block:"automatic-youtube-gallery/block",attributes:Object.assign({},y,{is_admin:!0})})),[y]),v=(0,r.debounce)((()=>{d(!1),g({...e})}),1e3),f=e=>a=>{t({[e]:a})};(0,c.useEffect)((()=>{d(!0),v()}),[e]);const x=(0,c.useRef)();(0,c.useEffect)((()=>{x.current?(0,p.applyFilters)("ayg_block_init",e):x.current=!0}));const k=l()(a,{"is-loading":b}),w=(0,s.useBlockProps)({className:k});return(0,u.jsxs)(u.Fragment,{children:[(0,u.jsx)(s.InspectorControls,{children:Object.keys(ayg_block.options).map(((a,n)=>(t=>{let a=!0;return"gallery"===t&&("video"!=e.type&&"livestream"!=e.type||(a=!1)),(0,p.applyFilters)("ayg_block_toggle_panels",a,t,e)})(a)&&(0,u.jsx)(i.PanelBody,{title:ayg_block.options[a].label,initialOpen:0==n,className:"automatic-youtube-gallery-block-panel",children:Object.keys(ayg_block.options[a].fields).map(((n,o)=>((a,n)=>{if(!(t=>{let a=!0;switch(t){case"playlist":case"username":case"search":case"video":case"videos":t!=e.type&&(a=!1);break;case"channel":"channel"!=e.type&&"livestream"!=e.type&&(a=!1);break;case"order":case"limit":"search"!=e.type&&(a=!1);break;case"cache":case"player_title":case"player_description":case"loop":"livestream"==e.type&&(a=!1);break;case"autoadvance":"video"!=e.type&&"livestream"!=e.type||(a=!1);break;case"more_button_label":"pager"==e.pagination_type&&(a=!1);break;case"previous_button_label":case"next_button_label":"more"==e.pagination_type&&(a=!1)}return(0,p.applyFilters)("ayg_block_toggle_controls",a,t,e)})(a.name))return"";const o=a.placeholder?a.placeholder:"",l=a.description?a.description:"";switch(a.type){case"number":return(0,u.jsx)(i.PanelRow,{children:(0,u.jsx)(i.RangeControl,{label:a.label,help:l,placeholder:o,value:e[a.name],min:a.min,max:a.max,onChange:f(a.name)})},n);case"textarea":return(0,u.jsx)(i.PanelRow,{children:(0,u.jsx)(i.TextareaControl,{label:a.label,help:l,placeholder:o,value:e[a.name],onChange:f(a.name)})},n);case"select":case"radio":let c=[];for(let e in a.options)c.push({label:a.options[e],value:e});return(0,u.jsx)(i.PanelRow,{children:(0,u.jsx)(i.SelectControl,{label:a.label,help:l,options:c,value:e[a.name],onChange:f(a.name)})},n);case"checkbox":return(0,u.jsx)(i.PanelRow,{children:(0,u.jsx)(i.ToggleControl,{label:a.label,help:l,checked:e[a.name],onChange:(r=a.name,e=>{t({[r]:e})})})},n);case"color":return(0,u.jsx)(i.PanelRow,{children:(0,u.jsx)(s.PanelColorSettings,{title:a.label,colorSettings:[{label:ayg_block.i18n.selected_color,value:e[a.name],onChange:f(a.name)}]})},n);default:return(0,u.jsx)(i.PanelRow,{children:(0,u.jsx)(i.TextControl,{label:a.label,help:l,placeholder:o,value:e[a.name],onChange:f(a.name)})},n)}var r})(ayg_block.options[a].fields[n],"automatic-youtube-gallery-block-control-"+o)))},"automatic-youtube-gallery-block-panel-"+n)))}),(0,u.jsx)("div",{...w,children:(0,u.jsxs)(i.Disabled,{children:[b&&(0,u.jsx)(m,{}),(0,u.jsx)(h,{})]})})]})}})})()})();
!function(){"use strict";var e=window.wp.element,t=JSON.parse('{"$schema":"https://json.schemastore.org/block.json","apiVersion":2,"name":"igd/uploader","version":"1.0.0","title":"File Uploader","category":"igd-category","icon":"cloud-upload","description":"Insert a file uploader to upload files to a specific Google Drive folder.","supports":{"html":false},"attributes":{"isInit":{"type":"boolean","default":true},"isEdit":{"type":"boolean","default":true},"data":{"type":"object","default":{"status":"on","type":"uploader","folders":[],"showFiles":true,"showFolders":true,"fileNumbers":1000,"sort":{"sortBy":"name","sortDirection":"asc"},"width":"100%","height":"auto","view":"grid","maxFileSize":"","uploaderStyle":"simple","openNewTab":true,"download":true,"displayFor":"everyone","displayUsers":["everyone"],"displayExcept":[]}}},"keywords":["file uploader","uploader","google","drive","integrate google drive"],"textdomain":"integrate-google-drive","editorScript":"file:./index.js","editorStyle":"file:./index.css"}'),l=window.wp.components,o=window.wp.blockEditor;const{registerBlockType:n}=wp.blocks;n("igd/uploader",{...t,icon:(0,e.createElement)("svg",{width:"20",height:"13",viewBox:"0 0 18 12",fill:"none",xmlns:"http://www.w3.org/2000/svg"},(0,e.createElement)("path",{fileRule:"evenodd",clipRule:"evenodd",d:"M8.94978 0.0173135C8.69841 0.047158 8.25974 0.137046 7.99943 0.212093C6.84326 0.545287 5.77653 1.34783 5.17576 2.33647C5.03386 2.56997 4.99717 2.60933 4.93671 2.59301C4.2653 2.41175 3.66477 2.45279 3.06949 2.72061C2.39216 3.02535 1.92881 3.53622 1.74603 4.17977C1.65258 4.50874 1.64684 5.00238 1.73276 5.32357L1.79515 5.55691L1.71174 5.60718C1.15835 5.94076 0.671241 6.4283 0.399515 6.92059C-0.345099 8.26966 -0.0288307 9.93182 1.18117 11.0288C1.65976 11.4627 2.19293 11.7531 2.79843 11.9097C3.06244 11.978 3.13086 11.98 5.43773 11.9899L7.80601 12L7.79689 10.2161L7.78781 8.43221L7.06245 8.41608C6.28347 8.39875 6.2653 8.39475 6.16576 8.21836C6.10023 8.10227 6.10393 8.00416 6.17837 7.88107C6.30118 7.67803 8.69305 4.67277 8.78446 4.60669C8.91999 4.50867 9.15996 4.53535 9.28344 4.66219C9.3895 4.77108 11.7084 7.708 11.8157 7.86936C11.8564 7.9305 11.8897 8.01681 11.8897 8.06117C11.8897 8.17422 11.7662 8.34719 11.6587 8.38462C11.6091 8.40191 11.2605 8.41608 10.8841 8.41608H10.1998V10.2067V11.9974H12.2022C14.4069 11.9974 14.5338 11.9889 15.116 11.8007C15.7454 11.5974 16.2364 11.3124 16.7151 10.8724C17.3482 10.2907 17.744 9.61873 17.9343 8.80215C18.021 8.4304 18.0221 7.66425 17.9365 7.29783C17.5881 5.80605 16.4693 4.64902 14.9814 4.24156C14.8207 4.19755 14.6475 4.15344 14.5965 4.14354C14.5133 4.1274 14.5012 4.10653 14.4789 3.93972C14.3505 2.98218 13.8532 2.07626 13.0558 1.34757C12.25 0.611139 11.3545 0.192702 10.2625 0.0424151C9.95137 -0.000399644 9.21714 -0.0144669 8.94978 0.0173135Z",fill:"url(#paint0_linear_858_4480)"}),(0,e.createElement)("defs",null,(0,e.createElement)("linearGradient",{id:"paint0_linear_858_4480",x1:"1.86486",y1:"-8.29995e-08",x2:"6.51901",y2:"14.0838",gradientUnits:"userSpaceOnUse"},(0,e.createElement)("stop",{stopColor:"#61E89D"}),(0,e.createElement)("stop",{offset:"1",stopColor:"#2DCA74"})))),edit:function(t){let{attributes:n,setAttributes:i}=t;const{ModuleBuilderModal:r,Shortcode:a}=igd,{data:d,isInit:s}=n,c=()=>{Swal.fire({html:'<div id="igd-gutenberg-module-builder" class="igd-module-builder-modal-wrap"></div>',showConfirmButton:!1,customClass:{container:"igd-module-builder-modal-container"},didOpen(t){const l=document.getElementById("igd-gutenberg-module-builder");wp.element.render((0,e.createElement)(r,{initData:d,onUpdate:e=>{i({data:e,isInit:!1}),Swal.close()},onClose:()=>Swal.close()}),l)},willClose(e){const t=document.getElementById("igd-gutenberg-module-builder");ReactDOM.unmountComponentAtNode(t)}})},u=!!igd.isPro,p=u?wp.i18n.__("Configure","integrate-google-drive"):wp.i18n.__("Get Pro","integrate-google-drive"),g=u?"admin-generic":"lock";return(0,e.createElement)("div",(0,o.useBlockProps)(),(0,e.createElement)(o.InspectorControls,null,(0,e.createElement)(l.PanelBody,{title:"Settings",icon:"dashicons-shortcode",initialOpen:!0},(0,e.createElement)(l.PanelRow,null,(0,e.createElement)("button",{type:"button",className:"igd-btn btn-primary",onClick:()=>{u?c():window.location.href=igd.upgradeUrl}},(0,e.createElement)("i",{className:`dashicons dashicons-${g}`}),(0,e.createElement)("span",null,p))))),(0,e.createElement)(o.BlockControls,null,(0,e.createElement)(l.ToolbarGroup,null,(0,e.createElement)(l.ToolbarButton,{icon:g,label:p,text:p,showTooltip:!0,onClick:()=>{u?c():window.location.href=igd.upgradeUrl}}))),s?(0,e.createElement)("div",{className:"module-builder-placeholder "+(u?"":"pro-placeholder")},(0,e.createElement)("img",{src:`${igd.pluginUrl}/assets/images/shortcode-builder/types/uploader.svg`}),(0,e.createElement)("h3",null,wp.i18n.__("File Uploader","integrate-google-drive")),u?(0,e.createElement)("p",null,wp.i18n.__("Please configure the module first to display the content.","integrate-google-drive")):(0,e.createElement)(e.Fragment,null,(0,e.createElement)("h4",null,wp.i18n.__("Unlock Pro Features","integrate-google-drive")),(0,e.createElement)("p",null,wp.i18n.__("Please upgrade to the pro version to use this module.","integrate-google-drive"))),(0,e.createElement)("button",{type:"button",className:"igd-btn btn-primary",onClick:()=>{u?c():window.location.href=igd.upgradeUrl}},(0,e.createElement)("i",{className:`dashicons dashicons-${g}`}),(0,e.createElement)("span",null,p))):(0,e.createElement)(a,{data:d,isPreview:!0}))}})}();
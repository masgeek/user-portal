(()=>{"use strict";function t(t){if(t<1)return 0;var e=Math.floor(Math.log(t)/Math.log(1024));return 1*(t/Math.pow(1024,e)).toFixed(2)+" "+["Byte","KB","MB","GB","TB"][e]}var e,n,o;e=jQuery,n=igd.ModuleBuilderModal,o={init:function(){e(document).on("click",".igd-tutor-button",o.handleVideoSelector),e(document).on("click",".video_source_wrap_google_drive .tutor-igd-delete-video",o.removeVideo),e(window).on("tutor_modal_shown",(function(){e(".tutorIgdUploadAttachmentBtn").length||e(".tutorUploadAttachmentBtn").after('<button type="button" class="tutor-btn tutor-btn-outline-primary tutorIgdUploadAttachmentBtn"><span class="tutor-icon-brand-google-drive tutor-mr-8"></span><span>'.concat(wp.i18n.__("Add Attachments","tutor"),"</span></button>")),o.initAttachments()})),e(document).on("click",".tutorIgdUploadAttachmentBtn",o.handleAttachmentSelection),e(document).on("click",".tutor-igd-delete-attachment",o.deleteAttachment)},handleVideoSelector:function(t){t.preventDefault();var a=e(this);Swal.fire({html:'<div id="igd-select-files" class="igd-module-builder-modal-wrap"></div>',showConfirmButton:!1,customClass:{container:"igd-module-builder-modal-container"},didOpen:function(t){ReactDOM.render(React.createElement(n,{initData:{},onUpdate:function(t){Swal.close(),o.addVideo(a,t[0])},onClose:function(){return Swal.close()},isLMS:"tutor"}),document.getElementById("igd-select-files"))},willClose:function(t){ReactDOM.unmountComponentAtNode(document.getElementById("igd-select-files"))}})},addVideo:function(n,o){var a=o.id,i=o.accountId,d=o.name,c=o.size,r=o.metaData.duration,l=void 0===r?0:r,u=n.closest(".video_source_wrap_google_drive");u.addClass("tutor-has-video");var s="".concat(igd.ajaxUrl,"?action=igd_stream&id=").concat(a,"&accountId=").concat(i);e('input[name="video[source_google_drive]"]',u).val(s),e('input[name="video[name_google_drive]"]',u).val(d),e('input[name="video[size_google_drive]"]',u).val(c);var m=l/1e3,v=Math.floor(m/3600),g=Math.floor(m%3600/60),h=Math.floor(m%60),p=u.closest(".tutor_lesson_modal_form").find(".tutor-option-field-video-duration"),f=e('input[name="video[runtime][hours]"]',p);v>0&&f.val(String(v).padStart(2,"0"));var _=e('input[name="video[runtime][minutes]"]',p);g>0&&_.val(String(g).padStart(2,"0"));var w=e('input[name="video[runtime][seconds]"]',p);h>0&&w.val(String(h).padStart(2,"0")),e(".video-data-title",u).text(d),e(".video-data-size",u).text(t(c))},removeVideo:function(t){t.preventDefault();var n=e(this).closest(".video_source_wrap_google_drive");n.removeClass("tutor-has-video"),e('input[name="video[source_google_drive]"]',n).val(""),e('input[name="video[name_google_drive]"]',n).val(""),e('input[name="video[size_google_drive]"]',n).val(""),e(".video-data-title",n).text(""),e(".video-data-size",n).text("")},handleAttachmentSelection:function(t){t.preventDefault();var a=e(this);Swal.fire({html:'<div id="igd-select-files" class="igd-module-builder-modal-wrap igd-tutor-attachment-modal"></div>',showConfirmButton:!1,customClass:{container:"igd-module-builder-modal-container"},didOpen:function(t){ReactDOM.render(React.createElement(n,{initData:{},onUpdate:function(t){Swal.close();var e=t.folders,n=(void 0===e?[]:e).map((function(t){return{id:t.id,accountId:t.accountId,name:t.name,size:t.size}})),i=a.closest(".tutor_lesson_modal_form");o.addAttachment(i,n)},onClose:function(){return Swal.close()},isSelectFiles:!0}),document.getElementById("igd-select-files"))},willClose:function(t){ReactDOM.unmountComponentAtNode(document.getElementById("igd-select-files"))}})},initAttachments:function(){var t=e(".tutor_lesson_modal_form"),n=t.find('input[type="hidden"][name^="igd_tutor_attachments"]'),a=[];n.each((function(){var t=e(this),n=t.attr("name").match(/^igd_tutor_attachments\[(.+?)\]\[(.+?)\]$/);if(n){var o=n[1],i=n[2],d=a.find((function(t){return t.id===o}));d||(d={id:o},a.push(d)),d[i]=t.val()}})),a.length&&o.addAttachment(t,a,!1)},addAttachment:function(n,o){var a=!(arguments.length>2&&void 0!==arguments[2])||arguments[2],i='<div class="tutor-col-lg-6 tutor-col-xl-4 tutor-mb-16 igd-tutor-attachment" data-id="">\n    <div class="tutor-card">\n     <div class="tutor-card-body">\n      <div class="tutor-row tutor-align-center">\n       <div class="tutor-col tutor-overflow-hidden">\n       <i class="tutor-icon-brand-google-drive tutor-color-primary"></i>\n        <div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-text-ellipsis tutor-mb-4 attachment-name"></div>\n        <div class="tutor-fs-7 tutor-color-muted">'.concat(wp.i18n.__("Size:","integrate-google-drive"),'  <span class="attachment-size"></span></div>\n       </div>\n\n       <div class="tutor-col-auto">\n        <span class="tutor-igd-delete-attachment tutor-iconic-btn tutor-iconic-btn-secondary" role="button">\n         <span class="tutor-icon-times" aria-hidden="true"></span>\n        </span>\n       </div>\n      </div>\n     </div>\n    </div>\n   </div>'),d=n.find(".tutor-attachment-cards");o.forEach((function(o){var c=o.id,r=o.accountId,l=o.name,u=o.size,s=e(i);if(s.attr("data-id",c),s.find(".attachment-name").text(l),s.find(".attachment-size").text(t(o.size)),d.append(s),a){var m=e('<input type="hidden" name="igd_tutor_attachments['.concat(c,'][id]" value="').concat(c,'">')),v=e('<input type="hidden" name="igd_tutor_attachments['.concat(c,'][accountId]" value="').concat(r,'">')),g=e('<input type="hidden" name="igd_tutor_attachments['.concat(c,'][name]" value="').concat(l,'">')),h=e('<input type="hidden" name="igd_tutor_attachments['.concat(c,'][size]" value="').concat(u,'">'));n.append(m,v,g,h)}}))},deleteAttachment:function(t){t.preventDefault();var n=e(this).closest(".tutor-col-lg-6"),o=n.data("id"),a=n.closest(".tutor_lesson_modal_form");a.find('input[name="igd_tutor_attachments['.concat(o,'][id]"]')).remove(),a.find('input[name="igd_tutor_attachments['.concat(o,'][accountId]"]')).remove(),a.find('input[name="igd_tutor_attachments['.concat(o,'][name]"]')).remove(),a.find('input[name="igd_tutor_attachments['.concat(o,'][size]"]')).remove(),n.remove()}},e(document).ready(o.init)})();
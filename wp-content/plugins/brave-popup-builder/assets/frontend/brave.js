var brave_isMobile = window.matchMedia("only screen and (max-width: 760px)").matches;
var brave_isTab = window.matchMedia("screen and (max-width: 1200px) and (min-width: 760px)").matches;
var brave_currentDevice = brave_isMobile ? 'mobile' : 'desktop';
var brave_back_pressed = 0;

function brave_number_padding(num) {  if(num){ var theNum = parseInt(num, 10); return theNum < 10 ? parseInt("0"+theNum, 10) : theNum } }

function brave_change_slide(elementID, goto, slideWidth){
   var sliderMargin = -(goto * slideWidth)+'px'
   var theCarousel = document.getElementById('brave_carousel__slides-'+elementID);
   var theCarouselNav = document.getElementById('brave_carousel__navs-'+elementID);
   
   if(theCarousel){
      theCarousel.style.marginLeft = sliderMargin;
      if(theCarouselNav){
         var allCarouselNavs = theCarouselNav.querySelectorAll(":scope li");
         for (var i = 0; i < allCarouselNavs.length; i++) { allCarouselNavs[i].classList.remove('slide__nav__active');  }
         document.getElementById('brave_carousel__nav-'+elementID+'_'+goto).classList.add('slide__nav__active');         
      }
   }
}

function brave_autochange_slide(elementID){
   var theSlider = document.getElementById('brave_carousel__slides-'+elementID);
   var currentslide = document.querySelector('#brave_carousel__navs-'+elementID).dataset.currentslide;
   currentslide = parseInt(currentslide, 10)
   var slideWidth = parseInt(theSlider.dataset.width, 10);
   var totalSlides = parseInt(theSlider.dataset.totalslides, 10);

   var carouselHovered = theSlider.dataset.hovered;
   if(carouselHovered === 'true'){ return }
   //console.log(elementID, currentslide, slideWidth, totalSlides);
   

   brave_change_slide(elementID, currentslide, slideWidth);

   if(totalSlides === (currentslide+1)){
      document.querySelector('#brave_carousel__navs-'+elementID).dataset.currentslide = 0;
   }else{
      document.querySelector('#brave_carousel__navs-'+elementID).dataset.currentslide = currentslide + 1;
   }
}

function brave_carousel_pause(elementID, resume=false){
   var theSlider = document.getElementById('brave_carousel__slides-'+elementID);
   if(theSlider){
      theSlider.dataset.hovered = true;
      if(resume){
         theSlider.dataset.hovered = false;
      }
   }
}
function brave_toggle_item(elementID, itemClass){
   var theToggleElm = document.getElementById('brave_carousel__slides-'+elementID);
   var selectedItem = theToggleElm.getElementsByClassName(itemClass);
   var allToggleDesc = theToggleElm.querySelectorAll('bravepopup_carousel__slide__content');
   for (var i = 0; i < allToggleDesc.length; i++) { allToggleDesc[i].classList.remove('brave__toggle__active');  }
   if(selectedItem[0]){ 
      if(selectedItem[0].classList.contains('brave__toggle__active')){
         selectedItem[0].classList.remove('brave__toggle__active');  
      }else{
         selectedItem[0].classList.add('brave__toggle__active');  
      }
   };
}
function brave_countdown(elementID, theDate, theHour, theMins, hideDays, hideHours, auto=null) {
   if(!theDate) { return null}

   //console.log(theDate, theHour, theMins, hideDays, auto);
   
   var dateString = theDate;
   var time = {hour: theHour, minutes: theMins};
   var brave_endDate;
   if(!auto){
       var parts = dateString.split('/');
       var brave_day = parseInt(parts[0], 10);
       var brave_month = parseInt(parts[1], 10) - 1;
       var brave_year = parseInt(parts[2], 10);
       brave_endDate =  new Date(brave_year, brave_month, brave_day);
       brave_endDate.setHours(time.hour);
       brave_endDate.setMinutes(time.minutes);
       brave_endDate.setSeconds(0);
   }else{
       if(auto){
         brave_endDate =  auto;
       }

   }

   var brave_startDate = new Date().getTime();

   var brave_days, brave_hours, brave_minutes, brave_seconds;
   var timeRemaining = parseInt(((brave_endDate.getTime() - brave_startDate) / 1000), 10);
   
   //console.log( brave_endDate.getTime(), brave_startDate, timeRemaining);
   
   if (timeRemaining >= 0) {
       if(!hideDays){
         brave_days = parseInt(timeRemaining / 86400);
         timeRemaining = (timeRemaining % 86400);
       }
       if(!hideHours){
         brave_hours = parseInt(timeRemaining / 3600);
         timeRemaining = (timeRemaining % 3600);
       }

       brave_minutes = parseInt(timeRemaining / 60);
       timeRemaining = (timeRemaining % 60);
       
       brave_seconds = parseInt(timeRemaining);
   }

   var daySpan = document.getElementById('brave_rem_days-'+elementID);
   var hourSpan = document.getElementById('brave_rem_hours-'+elementID);
   var minSpan = document.getElementById('brave_rem_minutes-'+elementID);
   var secSpan = document.getElementById('brave_rem_seconds-'+elementID);

   if(daySpan){ daySpan.innerHTML = timeRemaining >= 0 ? brave_days : 0}
   if(hourSpan){ hourSpan.innerHTML = timeRemaining >= 0 ? brave_hours : 0}
   if(minSpan){ minSpan.innerHTML = timeRemaining >= 0 ? brave_minutes : 0}
   if(secSpan){ secSpan.innerHTML = timeRemaining >= 0 ? brave_seconds : 0}

}

function brave_check_field_condition(event, fieldID, formID, fieldType='input'){

   var brave_form_conditions = brave_popup_formData && brave_popup_formData[formID] && brave_popup_formData[formID].conditionsMatch || {};
   var brave_form_cond_vals = brave_popup_formData && brave_popup_formData[formID] && brave_popup_formData[formID].conditionsVals || {};
   var conditionRules = brave_popup_formData && brave_popup_formData[formID] && brave_popup_formData[formID].conditions || {};
   var inputData = event.target.value;

   if(fieldType === 'checkbox'){
      inputData = '';
      var checkBoxes =  document.querySelectorAll('#brave_form_field'+fieldID+' input');
      for (var index = 0; index < checkBoxes.length; index++) {
            var checkBoxDom = checkBoxes[index];
            if(!inputData && checkBoxDom.checked){ inputData = checkBoxDom.value}
      }
   }

   conditionRules.forEach(function(fieldCondition){ 
      var fieldKey = fieldCondition.field; var depKey = fieldCondition.fieldDependent;
      //console.log(fieldID, fieldCondition);
      //console.log(depKey, brave_form_conditions[depKey]);
      if(fieldKey === fieldID && brave_form_conditions[depKey]){
         if(inputData && fieldCondition.action === 'exist' && !brave_form_conditions[depKey][fieldKey]){ brave_form_conditions[depKey][fieldKey] = true; }
         if(!inputData && fieldCondition.action === 'exist' && brave_form_conditions[depKey][fieldKey]){ brave_form_conditions[depKey][fieldKey] = false; }
         
         if(fieldCondition.action === 'equal' && (inputData && (brave_form_cond_vals[depKey][fieldKey].includes(inputData))) ){ brave_form_conditions[depKey][fieldKey] = true; }
         if(fieldCondition.action === 'equal' && (!inputData || (!brave_form_cond_vals[depKey][fieldKey].includes(inputData))) ){ brave_form_conditions[depKey][fieldKey] = false; }

         if(fieldCondition.action === 'notequal' && (inputData && (!brave_form_cond_vals[depKey][fieldKey].includes(inputData))) && !brave_form_conditions[depKey][fieldKey]){ brave_form_conditions[depKey][fieldKey] = true; }
         if(fieldCondition.action === 'notequal' && (!inputData || (brave_form_cond_vals[depKey][fieldKey].includes(inputData))) && brave_form_conditions[depKey][fieldKey]){ brave_form_conditions[depKey][fieldKey] = false;  }
      }
   });
   //console.log(brave_popup_formData[formID].conditionsMatch);


   Object.keys(brave_form_conditions).forEach(function(fieldKey){
      var matchArray = Object.keys(brave_form_conditions[fieldKey]);
      var matchedConditionsArray = matchArray.filter(function(depKey){ if(brave_form_conditions[fieldKey][depKey]){ return true; }else{ return false; } })
      var allMatched = matchedConditionsArray.length === matchArray.length;
      // console.log(matchedConditionsArray, matchArray);
      var dependantField = document.getElementById('brave_form_field'+fieldKey);
      if(dependantField){
         if(allMatched){
            dependantField.classList.remove('brave_form_field--hasCondition');
         }else{
            dependantField.classList.add('brave_form_field--hasCondition');
         }
         var formFieldsWrapper  = document.querySelector('#brave_form_'+formID+' .brave_form_fields');
         var currentFormStep = formFieldsWrapper.dataset.step || undefined;
         if(currentFormStep !== undefined && document.querySelector('#brave_form_'+formID+' .brave_form_fields .brave_form_fields_step'+currentFormStep)){
            formFieldsWrapper.style.height = document.querySelector('#brave_form_'+formID+' .brave_form_fields .brave_form_fields_step'+currentFormStep).offsetHeight+'px';
         }
      }
   });
}

function brave_select_imageField(fieldID, optionIndex, multi=false){
   var optionField = document.getElementById('brave_form_field'+fieldID+'_opt-'+optionIndex);
   if(optionField){
      if(!multi){
         var allOptsFields = document.getElementById('brave_form_field'+fieldID).querySelectorAll('input');
         for (var i = 0; i < allOptsFields.length; i++) { allOptsFields[i].checked = false;  document.getElementById('brave_form_field'+fieldID+'_opt-'+i).classList.remove('formfield__inner__image--selected');  }
      }
      var optionFieldInput = optionField.querySelector('input');
      if(optionFieldInput && (optionFieldInput.checked === false)){   optionFieldInput.checked = true;  optionField.classList.add('formfield__inner__image--selected'); 
      }else if(optionFieldInput && (optionFieldInput.checked === true)){ optionFieldInput.checked = false; optionField.classList.remove('formfield__inner__image--selected'); }
   }
}
function brave_select_form_ButtonGroup(fieldID, optionIndex, nextStep=false, formID='', totalSteps=0, goto=undefined){
   brave_select_imageField(fieldID, optionIndex, false);
   //console.warn(nextStep, formID, totalSteps, goto);
   if(nextStep && formID && totalSteps && goto !== undefined){
      brave_form_gotoStep(formID, totalSteps, goto);
   }
}
function brave_form_rating_unhover(fieldID){
   var allRatingFields = document.getElementById('brave_form_field'+fieldID).querySelectorAll('.formfield__inner__ratings_star, .formfield__inner__ratings_number');
   for (var i = 0; i < allRatingFields.length; i++) { 
      allRatingFields[i].classList.remove('formfield__inner__ratings--hovered'); 
   }
}
function brave_form_rating_hover(fieldID, optionIndex){
   var allRatingFields = document.getElementById('brave_form_field'+fieldID).querySelectorAll('.formfield__inner__ratings_star, .formfield__inner__ratings_number');
   for (var i = 0; i < allRatingFields.length; i++) { 
      allRatingFields[i].classList.remove('formfield__inner__ratings--hovered');  
      if(i <= (optionIndex-1)){
         allRatingFields[i].classList.add('formfield__inner__ratings--hovered'); 
      }
   }
}

function brave_form_rate(fieldID, optionIndex, smiley=false){
   document.getElementById('brave_form_field'+fieldID).dataset.rated = optionIndex;
   var allRatingFields = document.getElementById('brave_form_field'+fieldID).querySelectorAll('.formfield__inner__ratings_star, .formfield__inner__ratings_number, .formfield__inner__ratings_smiley');
   for (var i = 0; i < allRatingFields.length; i++) { 
      allRatingFields[i].classList.remove('formfield__inner__ratings--selected');
      if(!smiley && (i <= (optionIndex-1))){    allRatingFields[i].querySelector('input').checked = true; allRatingFields[i].classList.add('formfield__inner__ratings--selected');   }
      
      if(smiley && (i === (optionIndex-1))){    
         allRatingFields[i].querySelector('input').checked = true; 
         allRatingFields[i].classList.add('formfield__inner__ratings--selected');   
      }
   }
}

function brave_form_progress(formID, goto=0, totalSteps=2){
   if(document.querySelector('#'+formID+'__form_progress')){
      var progressPercent =  Math.round(((goto+1)/ totalSteps) * 100) ;
      var progStyle = document.querySelector('#'+formID+'__form_progress').dataset.style || 'style1';
      if(progStyle === 'style2'){ progressPercent = Math.round(((goto)/ (totalSteps-1)) * 100) ;}
      if(document.querySelector('#'+formID+'__form_progress .bravepopupform_theProgressbar_steps')){
         document.querySelector('#'+formID+'__form_progress .bravepopupform_theProgressbar_steps').innerHTML = (goto+1)+'/'+(totalSteps);
         document.querySelector('#'+formID+'__form_progress .bravepopupform_theProgressbar_progress').innerHTML = progressPercent+'%' ;
      }

      document.querySelector('#'+formID+'__form_progress .bravepopupform_theProgressbar__bar').style.width = progressPercent+'%';
   }
}

function brave_form_goBack(formID, totalSteps){
   var currentStep = document.querySelector('#brave_form_'+formID+' .brave_form_fields').dataset.step; currentStep = parseInt(currentStep, 10)
   var formBackButton = document.querySelector('#brave_form_'+formID+' .brave_form_stepBack');
   var goto = currentStep - 1;
   document.querySelector('#brave_form_'+formID+' .brave_form_fields').dataset.step = goto;
   brave_popup_formData[formID].currentStep = goto;

   //Change The Popup and Form Height if specified
   brave_form_changeHeight(formID, goto);
   //Update ProgressBar
   brave_form_progress(formID, goto, totalSteps);

   if(formBackButton){
      if(goto === 0 ){   
         formBackButton.classList.add('brave_form_stepBack--hide');
      }else{
         formBackButton.classList.remove('brave_form_stepBack--hide');
      }
   }

   // //Go to Prev Step
   var allformSteps = document.querySelectorAll('#brave_form_'+formID+' .brave_form_fields_step');
   for (var i = 0; i < allformSteps.length; i++) {   
      if(i ===goto){
         allformSteps[i].classList.add('brave_form_fields_step--show'); 

      }else{
         allformSteps[i].classList.remove('brave_form_fields_step--show');   
      }
      //if already filled the fields in next steps, reset them to preserve conditional logic
      if(currentStep === i){
         var nextFieldsWithVals =  allformSteps[i].querySelectorAll('.brave_form_field input, .brave_form_field select');
         for (var k = 0; k < nextFieldsWithVals.length; k++) {
            var fieldType = nextFieldsWithVals[k].getAttribute('type');
            var fieldKey = nextFieldsWithVals[k].name.replace('[]');
            if(['radio', 'checkbox'].includes(fieldType) && nextFieldsWithVals[k].checked){
               nextFieldsWithVals[k].checked = false;
            }      
            if(fieldType === 'select' && nextFieldsWithVals[k].value){
               nextFieldsWithVals[k].value = 'false';
            }
            brave_check_field_condition({target:{value:''}}, fieldKey, formID, fieldType)
         }
      }
   }

   if(goto === (totalSteps - 1) ){
      document.querySelector('#brave_form_'+formID+' .brave_form_button').classList.remove('brave_form_button--hide');   
   }else{
      document.querySelector('#brave_form_'+formID+' .brave_form_button').classList.add('brave_form_button--hide'); 
   }
}

function brave_form_gotoStep(formID, totalSteps, goto){
   //console.log('gotoStep', goto,totalSteps, goto === (totalSteps - 1));
   
   //Get The form Fields Values
   var braveForm = document.getElementById('brave_form_'+formID);
   var fieldsData = brave_get_field_vals(braveForm, JSON.parse(brave_popup_formData[formID].fields))


   var stepFieldIDs = [];
   var currentStep = document.querySelector('#brave_form_'+formID+' .brave_form_fields_step'+(goto-1));
   if(currentStep){
      var allStepFields = currentStep.querySelectorAll('.brave_form_field');
      for (var i = 0; i < allStepFields.length; i++) { 
         if( !allStepFields[i].classList.contains('brave_form_field--step') ){
            stepFieldIDs.push(allStepFields[i].getAttribute('id').replace('brave_form_field', ''));
         }
      }
   }
   var filteredFieldData = {}; var emailFields = [];
   stepFieldIDs.forEach(function(fieldID){
      if(fieldsData[fieldID]){ 
         filteredFieldData[fieldID] = fieldsData[fieldID];
         if(filteredFieldData[fieldID] && fieldsData[fieldID].required && fieldsData[fieldID].validation === 'email' && (!bravepop_emailSuggestions[fieldID])) { 
            emailFields.push({ID: fieldID, value: fieldsData[fieldID].value});
            //bravepop_emailSuggestions[fieldID] = true;
         }
      }
   })
   //console.log( fieldsData, filteredFieldData);

   // //Check if Form Has Errors
   var formErrors = [];
   Object.keys(filteredFieldData).forEach(function(fieldID){
      var fieldError = brave_validate_fields(fieldID, filteredFieldData[fieldID]);
      if(fieldError){ formErrors.push(fieldError); }
      document.querySelector('#brave_form_field'+fieldID).classList.remove('brave_form_field--hasError', 'brave_form_field--hasSuggestion', 'brave_form_field--hasError-firstname', 'brave_form_field--hasError-lastname');
   });
   brave_display_form_error(formErrors);

   if(formErrors.length > 0){ return }

   var braveFormNextStepActions = function(){
      //Change The Popup and Form Height if specified
      brave_form_changeHeight(formID, goto);

      // //Go to NextStep
      var allformSteps = document.querySelectorAll('#brave_form_'+formID+' .brave_form_fields_step');
      for (var i = 0; i < allformSteps.length; i++) {   
         if(i ===goto){
            document.querySelector('#brave_form_'+formID+' .brave_form_fields').dataset.step = goto;
            allformSteps[i].classList.add('brave_form_fields_step--show');   
            document.querySelector('#brave_form_'+formID+' .brave_form_fields').style.height = allformSteps[i].offsetHeight+'px';
            var formBackButton = document.querySelector('#brave_form_'+formID+' .brave_form_stepBack');
            if(formBackButton){ formBackButton.classList.remove('brave_form_stepBack--hide'); }
            brave_popup_formData[formID].currentStep = goto;
         }else{
            allformSteps[i].classList.remove('brave_form_fields_step--show');   
         }
      }
      brave_form_progress(formID, goto, totalSteps, false);
      if(goto === (totalSteps - 1) ){
         setTimeout(function() {
            document.querySelector('#brave_form_'+formID+' .brave_form_button').classList.remove('brave_form_button--hide');  
         }, 250);
      }

   }

   if(bravepop_emailValidation && emailFields.length > 0){
      var ajaxurl = bravepop_global.ajaxURL;
      var security = document.getElementById('brave_form_security'+formID).value;
      var emailData = { formData: JSON.stringify(emailFields), freemailAllow: JSON.stringify(brave_popup_formData[formID].freemailAllow || false), security: security, action: 'bravepopup_validate_email'};
      brave_ajax_send(ajaxurl, emailData, function(status, sentData){
         var validatedData = JSON.parse(sentData);
         console.log('Validation response:', validatedData); var emailValidationErrors = [];
         if(Array.isArray(validatedData)){
            validatedData.forEach(function(field){
               if(field.validation && field.validation.status === 'invalid' && field.validation.errorMsg){
                  emailValidationErrors.push({id:field.ID ,message: field.validation.errorMsg, type: "required"});
               }
               if(field.validation && field.suggestionMsg){
                  emailValidationErrors.push({id:field.ID ,message: field.suggestionMsg, type: "info"});
               }
            });
         }
         if(emailValidationErrors.length > 0){
            brave_display_form_error(emailValidationErrors);
            if(brave_form){ brave_form.classList.remove('brave_form_form--loading');  }
            if(brave_login_loader){  brave_login_loader.classList.remove('brave_form_loading--show'); }
         }else{
            braveFormNextStepActions();
         }
      });
   }else{
      braveFormNextStepActions();
   }



}

function brave_form_changeHeight(formID, goto){
   var changesFormHeight = brave_popup_formData[formID].changesFormHeight || false;
   if(changesFormHeight){
      var braveForm = document.getElementById('brave_element-'+formID);
      var parentPopupStep = braveForm.closest('.brave_popup__step');
      var initialHeight = brave_popup_formData[formID].heightData[0];
      var diffHeight = brave_popup_formData[formID].heightData[goto] - initialHeight;
      var newFormHeight = brave_popup_formData[formID].heightData[goto] || initialHeight;
      braveForm.style.height = (parseInt(newFormHeight, 10))+'px';

      if(parentPopupStep){
         var parentPopupHeight = parentPopupStep.dataset.height;
         //console.log(goto, parentPopupHeight, diffHeight, initialHeight, brave_popup_formData[formID].heightData[goto], brave_popup_formData[formID].heightData);
         parentPopupStep.querySelector('.brave_popup__step__inner').style.height =  (parseInt(parentPopupHeight, 10) + diffHeight)+'px';
      }
   }
   //console.log( changesFormHeight, newFormHeight, initialHeight, diffHeight);
}

function brave_get_field_vals(braveForm, fieldOpts, quiz){
   var fieldsData = fieldOpts; var firstname_val ='';
   for( var i=0; i<braveForm.elements.length; i++ ){
      var fieldName = braveForm.elements[i].name.replace('[]', '');
      var fieldOpts = fieldsData[fieldName] || {};
      var fieldValue = braveForm.elements[i].value;
      // console.log(fieldName, fieldOpts, fieldValue);
      if(fieldOpts && fieldOpts.type && (fieldOpts.type ==='checkbox' || (fieldOpts.type ==='select' && fieldOpts.multi) || (fieldOpts.type ==='image' && fieldOpts.multi)) ){
         var checkedVal = document.querySelectorAll('#brave_form_field'+fieldName+' input:checked, #brave_form_field'+fieldName+' option:checked');
         fieldValue = Array.from(checkedVal).map(function(el){ return el.value});
         if(fieldOpts.required && (fieldValue.length === 0 || fieldValue.includes('none')) ){ fieldValue =''; }
      }
      if(fieldOpts && fieldOpts.type && (fieldOpts.type ==='rating' || fieldOpts.type ==='radio' || fieldOpts.type ==='buttons') ){
         var checkedVal = document.querySelectorAll('#brave_form_field'+fieldName+' input:checked, #brave_form_field'+fieldName+' option:checked');
         var selectedfieldValue = Array.from(checkedVal).map(function(el){ return el.value});
         fieldValue = Array.isArray(selectedfieldValue) && selectedfieldValue[0] ? selectedfieldValue[0] : '';
         //console.log(fieldOpts.type, checkedVal );
      }
      if(fieldOpts && fieldOpts.type && fieldOpts.type ==='input' && fieldOpts.validation ==='name'){
         if(braveForm.elements[i].classList.contains('brave_form_field_input-firstname')){   firstname_val = fieldValue; }
         if(braveForm.elements[i].classList.contains('brave_form_field_input-lastname')){   
            fieldsData[fieldName].value = [firstname_val,fieldValue];
          }
      }else if(fieldsData[fieldName]){
         fieldsData[fieldName].value = fieldValue;
      }

      //If the Field is only set to show conditionally, and the condition does not need to match, set the required to false
      if(fieldsData[fieldName] && fieldsData[fieldName].required && document.getElementById('brave_form_field'+fieldName).classList.contains('brave_form_field--hasCondition')){
         fieldsData[fieldName].required = false;
      }

      if(fieldOpts && fieldOpts.type && fieldOpts.type ==='date' && fieldOpts.validation ==='multi'){
         var dateVal = document.getElementById('brave_form_field'+fieldName+'-date') ? document.getElementById('brave_form_field'+fieldName+'-date').value : '';
         var monthVal = document.getElementById('brave_form_field'+fieldName+'-month') ? document.getElementById('brave_form_field'+fieldName+'-month').value : '';
         var yearVal = document.getElementById('brave_form_field'+fieldName+'-year') ? document.getElementById('brave_form_field'+fieldName+'-year').value : new Date().getFullYear();
         if(dateVal || monthVal){
            fieldsData[fieldName].value = dateVal+'/'+monthVal+'/'+yearVal;
         }
      }

      if(quiz && fieldOpts.options){
         var selectedOpt = false;
         fieldOpts.options.forEach(function(opt) {
            if( (fieldOpts.type ==='checkbox' || (fieldOpts.type ==='select' && fieldOpts.multi) || (fieldOpts.type ==='image' && fieldOpts.multi)) && fieldValue.includes(opt.value)){ selectedOpt = opt; }
            if( (fieldOpts.type ==='radio' || fieldOpts.type ==='buttons')  && opt.value === fieldValue){ selectedOpt = opt; }
         });
         if(selectedOpt){
            fieldsData[fieldName].score = selectedOpt.score ? selectedOpt.score : 0 ;
            fieldsData[fieldName].correct = selectedOpt.correct ? selectedOpt.correct : false ;
         }
      }
   }
   return fieldsData;
}

function brave_submit_form(event, settings, supressErrors=false){
   if(event){  event.preventDefault();  }
   var braveForm = document.getElementById('brave_form_'+settings.formID);
   var originalFields = JSON.parse(settings.fields);
   var fieldsData = brave_get_field_vals(braveForm, originalFields, settings.quiz)
   var ajaxurl = bravepop_global.ajaxURL;
   var security = document.getElementById('brave_form_security'+settings.formID).value;
   //console.log(settings.fields);
   //Check if has Errrors
   var formErrors = [];
   Object.keys(fieldsData).forEach(function(fieldID){
      var fieldError = brave_validate_fields(fieldID, fieldsData[fieldID]);
      if(fieldError){ formErrors.push(fieldError); }
      if(document.querySelector('#brave_form_field'+fieldID)){
         document.querySelector('#brave_form_field'+fieldID).classList.remove('brave_form_field--hasError','brave_form_field--hasSuggestion', 'brave_form_field--hasError-firstname', 'brave_form_field--hasError-lastname');
      }
   });
   brave_display_form_error(formErrors);

   var finalFieldsData = {}; var quizData = {}; var emailFields = []; var quizScore = 0; var availableScore = 0; var quizCorrect = 0;
   Object.keys(fieldsData).forEach(function(fieldID){  
      if(fieldsData[fieldID]) {  finalFieldsData[fieldID] = fieldsData[fieldID].value; }
      if(fieldsData[fieldID] && fieldsData[fieldID].validation === 'name' && Array.isArray(fieldsData[fieldID].value)) { 
         finalFieldsData[fieldID] = fieldsData[fieldID].value.join(' ').trim();
      }
      if(fieldsData[fieldID] && fieldsData[fieldID].required && fieldsData[fieldID].validation === 'email' && (!bravepop_emailSuggestions[fieldID])) { 
         emailFields.push({ID: fieldID, value: fieldsData[fieldID].value});
         //bravepop_emailSuggestions[fieldID] = true;
      }
      if(settings.quiz && fieldsData[fieldID] && fieldsData[fieldID].score){ quizScore = quizScore + fieldsData[fieldID].score;}
      if(settings.quiz && fieldsData[fieldID] && fieldsData[fieldID].correct){ quizCorrect = quizCorrect + 1;}
      if(settings.quiz && fieldsData[fieldID] && fieldsData[fieldID].topScore){ availableScore = availableScore + fieldsData[fieldID].topScore; }
   });

   if(settings.quiz){
      brave_popup_formData[settings.formID].totalScore = quizScore;
      brave_popup_formData[settings.formID].totalCorrect = quizCorrect;
      brave_popup_formData[settings.formID].availableScore = availableScore;
      quizData = {availableScore: availableScore, userScore: quizScore, userCorrect: quizCorrect, totalQuestions: settings.totalQuestions, scoring: settings.quizScoring}
   }

   console.log('finalFieldsData: ', quizScore, quizCorrect, fieldsData);
   console.log(formErrors);
   //console.log('FORM ERRORS: ', formErrors);

   if(supressErrors){ formErrors = []; }

   if(formErrors.length === 0){
      //console.log('NO ERRORS Sending to Backend!');
      //SEND Data
      if(!security || !ajaxurl) { return console.log('Security Failed or Ajax URL Missing!!!!', security, ajaxurl); }
      var finalData = { formData: JSON.stringify(finalFieldsData), popupID: settings.popupID, formID: settings.formID, stepID: settings.stepID, quizData: JSON.stringify(quizData), cookieConditions:"", device: settings.device, userDevice: brave_currentDevice, pageURL: window.location, security: security, action: 'bravepop_form_submission' };
      var brave_form = document.getElementById('brave_form_'+settings.formID);
      var brave_login_loader = document.getElementById('brave_form_loading_'+settings.formID);
      if(brave_login_loader){  brave_login_loader.classList.add('brave_form_loading--show'); }
      if(brave_form){ brave_form.classList.add('brave_form_form--loading');  }

      var cookiesToCheck = brave_form.dataset.cookies ? brave_form.dataset.cookies.split(',') : [];
      if(cookiesToCheck.length > 0){
         var cookiesData = {};
         cookiesToCheck.forEach(function(cookieName){   cookiesData[cookieName] = localStorage.getItem(cookieName) ? true : false;  })
         finalData.cookieConditions = JSON.stringify(cookiesData);
      }
      
      if(window.location.href.includes('brave_popup') && window.location.href.includes('brave_preview') && location.search.split('brave_preview=')[1]){
         finalData.brave_previewID = location.search.split('brave_preview=')[1];
      }

      //console.log(finalData);
      var braveSubmitForm = function(){
         return brave_ajax_send(ajaxurl, finalData, function(status, sentData){
            //Remove The sending Status
            if(brave_login_loader){  brave_login_loader.classList.remove('brave_form_loading--show'); }
            if(brave_form){ brave_form.classList.remove('brave_form_form--loading');  }
            if(document.querySelector('#bravepopupform_socialOptin-'+settings.formID)){
               document.querySelector('#bravepopupform_socialOptin-'+settings.formID).classList.remove('bravepopupform_socialOptin--loading');
               document.querySelector('#bravepopupform_socialOptin-'+settings.formID).classList.add('bravepopupform_socialOptin--hide');
               document.querySelector('#brave_element-'+settings.formID+' .brave_element__form_inner').classList.remove('brave_element__form_inner--hide');
            }
   
            var response = JSON.parse(sentData);
            console.log(status, response);

            if(response.error &&  typeof response.error === 'string'){
               return alert(response.error);
            }

            localStorage.setItem('brave_popup_'+settings.popupID+'_formsubmitted', true);
   
            var braveFormSubmitEvent = new CustomEvent('brave_form_submitted', { detail: {popupId: parseInt(settings.popupID, 10), formId: settings.formID, formData: JSON.stringify(finalFieldsData)} });
            document.dispatchEvent(braveFormSubmitEvent);
   
            // if(document.querySelector('#'+settings.formID+'__form_progress')){
            //    document.querySelector('#'+settings.formID+'__form_progress').style.display = 'none';
            // }

            //Reset the Form
            if(brave_form){  brave_form.reset();   }

            //If Multistep Form, go to the First Step
            if(brave_popup_formData[settings.formID] && brave_popup_formData[settings.formID].totalSteps > 0){
               for (var findx = 1; findx < brave_popup_formData[settings.formID].totalSteps; findx++) {
                  brave_form_goBack(settings.formID, brave_popup_formData[settings.formID].totalStep)
               }
            }

            if(settings.track){
               var formTrackingSettings = JSON.parse(settings.track);
               //Send Ga Event
               if( formTrackingSettings && formTrackingSettings.enable && formTrackingSettings.eventCategory && formTrackingSettings.eventAction){
                  brave_send_ga_event(formTrackingSettings.eventCategory, formTrackingSettings.eventAction, formTrackingSettings.eventLabel || '');
               }
               if( formTrackingSettings && formTrackingSettings.enable && formTrackingSettings.fbq_event_type){
                  brave_send_fbq_event(formTrackingSettings.fbq_event_type, formTrackingSettings.fbq_content_name, formTrackingSettings.fbq_content_category, formTrackingSettings.fbq_value, formTrackingSettings.fbq_currency);
               }
            }
   
            //Perform Goal
            if(brave_popup_formData[settings.formID] && brave_popup_formData[settings.formID].goal){
               brave_complete_goal(settings.popupID, 'form');
            }
            
            //Save Field Value as Cookie
            Object.keys(fieldsData).forEach(function(fieldID){  
               var fieldVal = Array.isArray(fieldsData[fieldID].value) ? fieldsData[fieldID].value.join(',') : fieldsData[fieldID].value;
               if(fieldVal && fieldsData[fieldID] && fieldsData[fieldID].save_cookie){ localStorage.setItem(fieldsData[fieldID].save_cookie, fieldVal)}
            });
   
            //Show Content
            if(response.sent && response.primaryAction === 'content' && response.contentMessage ){
               if(brave_form){ brave_form.classList.add('brave_form_form--hide');  }
               document.getElementById('brave_form_custom_content'+settings.formID).innerHTML = response.contentMessage;
               //Auto Close Popup
               if(response.autoclose && response.autoclosetime){
                  setTimeout(function() {
                     var thePopID = parseInt(settings.popupID, 10);
                     brave_close_popup(thePopID, brave_popup_data[thePopID].currentStep||0);
                  }, parseInt(response.autoclosetime, 10) * 1000);
               }
               //Also Download File
               if(response.download && response.downloadURL){
                  var downloadURI = response.downloadURL; var filename =  response.downloadURL.substring( response.downloadURL.lastIndexOf('/')+1);
                  var link = document.createElement('a');
                  if (typeof link.download === 'string') {
                        document.body.appendChild(link); // Firefox requires the link to be in the body
                        link.download = filename;
                        link.href = downloadURI;
                        link.target = '_blank';
                        link.click();
                        document.body.removeChild(link); // remove the link when done
                  } else {
                        location.replace(downloadURI);
                  }
               }
            }

            //Run Users own functions after submit
            brave_popup_formData[settings.formID] && brave_popup_formData[settings.formID].onSubmit(fieldsData, response);
   
            //Redirect User
            if(response.sent && response.primaryAction === 'redirect' && response.redirectURL ){
               if(brave_form){ brave_form.classList.add('brave_form_form--hide');  }
               document.getElementById('brave_form_custom_content'+settings.formID).innerHTML = response.redirectMessage;
               var redirectTime = response.redirectAfter ? (response.redirectAfter *1000) : 6000;
               setTimeout(function() {
                  window.location.href = response.redirectURL;
               }, redirectTime);
            }
   
            //Go to Step
            if(response.sent && response.primaryAction === 'step' && response.step && settings.popupID ){
               var formStep = parseInt(response.step, 10);
               formStep = formStep === 0 ? 0 : formStep - 1;
               var selectedPopupStep = document.querySelector('#brave_popup_'+settings.popupID+'__step__'+(brave_popup_data[settings.popupID].currentStep||0)+' .brave_popup__step__'+brave_currentDevice)
               if(selectedPopupStep){ selectedPopupStep.dataset.popopen = 'false'; }
               brave_open_popup(settings.popupID, formStep);
            }
            //If has quiz Shortcode in the popup, insert the quiz scores inside the shortcodes
            if(settings.quiz){
               var quizShortcodes = document.querySelectorAll('.bravepop_quizScore-'+settings.formID);
               if(quizShortcodes){
                  for (var i = 0; i < quizShortcodes.length; i++) { 
                     var showTotal = quizShortcodes[i].dataset.total && quizShortcodes[i].dataset.total === 'false' ? false : true;
                     if(settings.quizScoring ==='points' ){
                        var availableScoreVal = showTotal && brave_popup_formData[settings.formID].availableScore && brave_popup_formData[settings.formID].availableScore >= brave_popup_formData[settings.formID].totalScore ? '/'+brave_popup_formData[settings.formID].availableScore : '';
                        quizShortcodes[i].innerHTML = '<span>'+brave_popup_formData[settings.formID].totalScore+'</span>'+availableScoreVal;
                     }
                     if(settings.quizScoring ==='answer'){
                        var availableQuesVal = showTotal && brave_popup_formData[settings.formID].totalQuestions ?  '/'+brave_popup_formData[settings.formID].totalQuestions : '';
                        quizShortcodes[i].innerHTML = '<span>'+brave_popup_formData[settings.formID].totalCorrect+'</span>'+availableQuesVal;
                     }
                  }
               }
            }

         });
      }

      var braveSubmitWithEmailValidation = function(){
         var emailData = { formData: JSON.stringify(emailFields), freemailAllow: JSON.stringify(brave_popup_formData[settings.formID].freemailAllow || false), security: security, action: 'bravepopup_validate_email' };
         brave_ajax_send(ajaxurl, emailData, function(status, sentData){
            var validatedData = JSON.parse(sentData);
            console.log('Validation response:', validatedData); var emailValidationErrors = [];
            if(Array.isArray(validatedData)){
               validatedData.forEach(function(field){
                  if(field.validation && field.validation.status === 'invalid' && field.validation.errorMsg){
                     emailValidationErrors.push({id:field.ID ,message: field.validation.errorMsg, type: "required"});
                  }
                  if(field.validation && field.validation.suggestionMsg && field.validation.suggestion){
                     emailValidationErrors.push({id:field.ID , message: field.validation.suggestionMsg, type: "suggestion", suggestion: field.validation.suggestion});
                  }
               });
            }
            if(emailValidationErrors.length > 0){
               brave_display_form_error(emailValidationErrors);
               if(brave_form){ brave_form.classList.remove('brave_form_form--loading');  }
               if(brave_login_loader){  brave_login_loader.classList.remove('brave_form_loading--show'); }
            }else{
               braveSubmitForm();
            }
         });
      }

      if(settings.recaptcha && grecaptcha){
         grecaptcha.execute(settings.recaptcha, {action: 'submit'}).then(function(token) {
            if(token){
               var recaptchaData = { token: token, security: security, action: 'bravepopup_validate_recaptcha' };
               brave_ajax_send(ajaxurl, recaptchaData, function(status, valid){
                  console.log('Google Recaptcha Verified!');
                  if(valid === 'true'){
                     if(bravepop_emailValidation && emailFields.length > 0){
                        braveSubmitWithEmailValidation();
                     }else{
                        braveSubmitForm();
                     }
                  }else{
                     console.error('Google Recaptcha Failed! Spammer Detected!');
                  }
               });
            }else{ console.error('Google Recaptcha Failed! Could not Fetch Token!'); }
        });
      }else{
         if(bravepop_emailValidation && emailFields.length > 0){
            braveSubmitWithEmailValidation();
         }else{
            braveSubmitForm();
         }
      }

   }

}

function brave_apply_email_suggestion(fieldID, suggestion){ document.querySelector('#brave_form_field'+fieldID+' input').value = suggestion; brave_dismiss_email_suggestion(fieldID); }
function brave_dismiss_email_suggestion(fieldID){ document.querySelector('#brave_form_field'+fieldID).classList.remove('brave_form_field--hasError', 'brave_form_field--hasSuggestion'); }

function brave_display_form_error(formErrors){
      //console.log(formErrors);
      
      //Display The Errors
      if(formErrors.length > 0){
         formErrors.forEach(function(error){
            if(error.fieldType && error.fieldType ==='name'){
               if(error.firstname){
                  document.querySelector('#brave_form_field'+error.id).classList.add('brave_form_field--hasError-firstname');
                  document.querySelector('#brave_form_field'+error.id+' .brave_form_field_error--firstname').innerHTML = error.message;
               }
               if(error.lastname){
                  document.querySelector('#brave_form_field'+error.id).classList.add('brave_form_field--hasError-lastname');
                  document.querySelector('#brave_form_field'+error.id+' .brave_form_field_error--lastname').innerHTML = error.message;
               }
   
            }else{
               document.querySelector('#brave_form_field'+error.id).classList.add('brave_form_field--hasError');
               document.querySelector('#brave_form_field'+error.id+' .brave_form_field_error').innerHTML = error.message;
               if(error.type === 'suggestion' && error.suggestion ){
                  document.querySelector('#brave_form_field'+error.id).classList.add('brave_form_field--hasSuggestion');
                  var suggestionApplyBtn = '<span onclick="brave_apply_email_suggestion(\''+error.id+'\', \''+error.suggestion+'\')">'+bravepop_global.yes+'</span>';
                  var suggestionDismissBtn = '<span onclick="brave_dismiss_email_suggestion(\''+error.id+'\', \''+error.suggestion+'\')">'+bravepop_global.no+'</span>';
                  var suggestionActions = '<div id="brave_form_field_suggestion_actions-'+error.id+'" class="brave_form_field_suggestion_actions">'+suggestionApplyBtn+suggestionDismissBtn+'</div>'
                  document.querySelector('#brave_form_field'+error.id+' .brave_form_field_error').setAttribute('id', 'brave_form_field_error--suggestion'+error.id)
                  document.querySelector('#brave_form_field'+error.id+' .brave_form_field_error').classList.add('brave_form_field_error--suggestion');
                  document.querySelector('#brave_form_field'+error.id+' .brave_form_field_error').innerHTML = (error.message)+suggestionActions;
               }
            }
         });
      }
}


function brave_validate_fields(fieldID, field){
   if(!field){ return }

   if(field.type==='input' && field.validation === 'name' && field.required) { 
      if(!field.value[0] || !field.value[1]){
         return {id: fieldID, type: 'required', fieldType: 'name', message: bravepop_global.field_required, firstname: !field.value[0] ? true : false, lastname:  !field.value[1] ? true : false}
      }
   }

   if(!field.value && field.required) { 
      return {id: fieldID, type: 'required', message: bravepop_global.field_required}
   }
   if(field.required && (field.type==='input' || field.type==='textarea' || field.type==='date' ) ) { 
      if(field.validation !== 'name'  && !field.value.trim()){
         return {id: fieldID, type: 'required', message: bravepop_global.field_required}
      }
   }

   if(field.value && field.required && field.type==='select' && field.value === 'none') { 
      return {id: fieldID, type: 'required', message: bravepop_global.field_required}
   }

   //Check HTML or Empty Value
   if(field.value && ((field.type==='input' && field.validation === 'text') || field.type==='textarea' || field.type==='date'  ) ) { 

      if(brave_hasHTML(field.value)){
         return {id: fieldID, type: 'ho_html', message: bravepop_global.no_html_allowed};
      }
   }

   //Validate number
   if(field.value && field.type==='input' && field.validation === 'number' ) { 
      if(brave_isNumber(field.value) === false){
         return {id: fieldID, type: 'invalid', message: bravepop_global.invalid_number};
      }
   }
   //Validate url
   if(field.value && field.type==='input' && field.validation === 'url' ) { 
      if(brave_isURL(field.value) === false){
         return {id: fieldID, type: 'invalid', message: bravepop_global.invalid_url};
      }
   }

   //Validate Date
   if(field.value && field.type==='date' ) { 
      if(brave_isDate(field.value) === false){
         return {id: fieldID, type: 'invalid', message: bravepop_global.invalid_date};
      }
   }

   //Validate email
   if(field.value && field.type==='input' && field.validation === 'email' ) { 
      if(brave_isEmail(field.value) === false){
         return {id: fieldID, type: 'invalid', message: bravepop_global.invalid_email};
      }
   }

}

function brave_hasHTML(value) {
   var doc = new DOMParser().parseFromString(value, "text/html");
   return Array.from(doc.body.childNodes).some(function(node){ return node.nodeType === 1});
}

function brave_isNumber(value) {
   var numberFormat = RegExp(/^[a-zA-Z]+$/);
   return numberFormat.test(value) ? false : true;
}

function brave_isEmail(value) {
   var mailformat = RegExp(/^\w+([\.+-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,8})+$/);
   return mailformat.test(value) ? true : false;
}

function brave_isURL(value) {
   var urlFormat = RegExp(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g);
   return urlFormat.test(value) ? true : false;
}

function brave_isDate(value) {
   var dateFormat = RegExp(/\d{1,4}[-/]\d{1,2}[-/]\d{1,4}/);
   var dateFormatTwo = RegExp(/\d{1,4}[-/]\d{1,2}/);
   var matchedDate = dateFormat.test(value);
   if(!matchedDate){ matchedDate = dateFormatTwo.test(value);  }
   //console.log(value, matchedDate);
   return matchedDate ? true : false;
}


function brave_ajax_send(ajaxurl, ajaxData, callbackFunction){
   //console.log(ajaxData);
   var array = [];
   Object.keys(ajaxData).forEach(function(element) {
      array.push(  encodeURIComponent(element) + "=" + encodeURIComponent(ajaxData[element]) ) 
   });
   var dataToSend = array.join("&");
   
   var request = new XMLHttpRequest();
   request.open('POST', ajaxurl, true);
   request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded' );
   request.onload = function () {  
      if (this.status >= 200 && this.status < 400) { 
         callbackFunction('success', this.response);
      } else {   
         callbackFunction('error', this.response);
      }  
   };
   //request.onerror = function(error) {  console.log(error);   };
   request.send(dataToSend);
}


function brave_action_step(popupID, currentStep, stepIndex){
   //console.log(popupID, stepIndex);
   var selectedPopupStep = document.querySelector('#brave_popup_'+popupID+'__step__'+stepIndex+' .brave_popup__step__'+brave_currentDevice)
   var noMobileContent = selectedPopupStep && selectedPopupStep.dataset.nomobilecontent === 'true' ? true : false;
   var currentDevice = noMobileContent ? 'desktop' : brave_currentDevice;
   var selectedPopupStep = document.querySelector('#brave_popup_'+popupID+'__step__'+currentStep+' .brave_popup__step__'+currentDevice)
   selectedPopupStep.dataset.popopen = 'false';
   brave_open_popup(popupID, stepIndex);
   
}


function brave_init_popup(popupID, popupData ){
   //console.log(popupID, popupData);
   if(!popupData){ return; }
   brave_responsiveness(null, popupID, popupData);

   //If is in Preview Mode force load the popup skinpping all the conditions
   if(popupData.forceLoad){
      return brave_load_popup(popupID, popupData);  
   }

   var triggerType = popupData.settings && popupData.settings.trigger && popupData.settings.trigger.triggerType ? popupData.settings.trigger.triggerType.split(',') : ['load'];

   //Do not load if campaign has ad block detection enabled and user has Ad blocker Installed.
   if(popupData.settings && popupData.settings.placement && popupData.settings.placement.adblock_check && window.brave_canRunAds){
      return console.log('Visitor Doesnt have Ad Blocked Installed! Aborting..');
   }
   //Do not Load if embedded popups if the shortcode is not present in current page
   if((popupData.hasDesktopEmbed || (popupData.hasMobileEmbed && brave_currentDevice === 'mobile')) && !document.getElementById('bravepopup_embedded_'+popupID)){
      return console.log('Popup shortcode is not found in current page');
   }

   //Do not load if Popup is not in Scheduled Dates
   if(popupData.schedule && popupData.schedule.active && popupData.schedule.type){
      if(popupData.schedule.type === 'days' && popupData.schedule.days.length > 0){
         var currentDay = new Date().getDay();
         if(!popupData.schedule.days.includes(currentDay)){
            return console.log('Popup Not Scheduled for Today!');
         }
      }
      if(popupData.schedule.type === 'dates' && popupData.schedule.dates.length > 0){
         var thecurrentDate = new Date().getTime();
         var dateInRange = false;
         popupData.schedule.dates.forEach(function(date){
            var theTimes = []; var timeTypes = ['start', 'end'];
            timeTypes.forEach(function(val){
               var theDate = date[val].date.split('/');
               var theHour = date[val].time && date[val].time.hour ? date[val].time.hour : '00';
               var theMinutes = date[val].time && date[val].time.minutes ? date[val].time.minutes : '00';
               theTimes.push(new Date(theDate[2]+'/'+theDate[1]+'/'+theDate[0]+' '+theHour+':'+theMinutes+':00').getTime());
            })
            if(theTimes[0] < thecurrentDate && theTimes[1] > thecurrentDate){
               dateInRange = true;
            }
         });
         if(!dateInRange){
            return console.log('Popup Not Scheduled these dates!');
         }
      }
   }

   //Do not Load if Device Settings doesnt Match
   if(popupData.settings && popupData.settings.audience && popupData.settings.audience.devices && popupData.settings.audience.devices === "desktop" && brave_currentDevice === 'mobile'){   return console.log('Device Settings doesnt Match');  }
   if(popupData.settings && popupData.settings.audience && popupData.settings.audience.devices && popupData.settings.audience.devices === "mobile" && brave_currentDevice === 'desktop'){   return console.log('Device Settings doesnt Match');  }


   var popVariants = brave_popup_data[popupID] && brave_popup_data[popupID].variants ? brave_popup_data[popupID].variants : false;
   var popupRepeatCountType = popupData.settings && popupData.settings.frequency && popupData.settings.frequency.repeatCountType || 'lifetime';
   var popupRepeatDelay = popupData.settings && popupData.settings.frequency && popupData.settings.frequency.repeatDelay || false;
   var popupRepeatDelayTime = popupData.settings && popupData.settings.frequency && popupData.settings.frequency.repeatDelayTime ? parseInt(popupData.settings.frequency.repeatDelayTime, 10) : false;
   var repeatCount = popupData.settings && popupData.settings.frequency && popupData.settings.frequency.repeatCount ? parseInt(popupData.settings.frequency.repeatCount, 10) : 3;

   //Do not load if Opened n Times already
   var filterViewFreq = function(popID){
      var popupOpenCount = localStorage.getItem('brave_popup_'+popID+'_viewed'); var popupOpenSessionCount = sessionStorage.getItem('brave_popup_'+popID+'_viewed');

      if(popupData.settings && popupData.settings.frequency && popupData.settings.frequency.repeat && brave_popup_adminUser === false ){  
         if(popupRepeatCountType === 'lifetime' && parseInt(popupOpenCount, 10) >= repeatCount){
            return 'Already Viewed '+repeatCount+' Times. Hiding..';  
         }
         var popupViewStat = localStorage.getItem('brave_popup_'+popID+'_view_stat'); popupViewStat = popupViewStat ? JSON.parse(popupViewStat) : [];
         var thecurrentDate = new Date(); var thecurrentDay = thecurrentDate.getDate();  var thecurrentHour = thecurrentDate.getHours(); var thecurrentMonth = thecurrentDate.getMonth(); var thecurrentYear = thecurrentDate.getFullYear();
         var popupViewDayCount = 0; var popupViewMonthCount = 0; var popupView12hCount = 0; var popupView6hCount = 0; var popupView1hCount = 0; var matchedStats = [];
         if(popupViewStat){
            popupViewStat.forEach(function(stat){
               var statDate = new Date(stat); var statDay = statDate.getDate(); var statHour = statDate.getHours();  var statMonth = statDate.getMonth(); var statYear = statDate.getFullYear();
               if((statDay === thecurrentDay) && (statMonth === thecurrentMonth) && (statYear === thecurrentYear)){  popupViewDayCount = popupViewDayCount + 1;   }
               if((statMonth === thecurrentMonth) && (statYear === thecurrentYear)){  popupViewMonthCount = popupViewMonthCount + 1;   }
               if((thecurrentHour - statHour) < 12 && (statDay === thecurrentDay) && (statMonth === thecurrentMonth) && (statYear === thecurrentYear)){  popupView12hCount = popupView12hCount + 1;   }
               if((thecurrentHour - statHour) < 6 && (statDay === thecurrentDay) && (statMonth === thecurrentMonth) && (statYear === thecurrentYear)){   popupView6hCount = popupView6hCount + 1;   }
               if((thecurrentHour - statHour) < 1 && (statDay === thecurrentDay) && (statMonth === thecurrentMonth) && (statYear === thecurrentYear)){   popupView1hCount = popupView1hCount + 1;   }
               if(!matchedStats.includes(stat)){   matchedStats.push(stat);   }
            })
         }
         matchedStats = matchedStats.sort();
         var lastViewedTime = matchedStats.length > 0 ? matchedStats[matchedStats.length - 1] : 0;
         var viewDelayMatched = popupRepeatDelay && popupRepeatDelayTime ? (thecurrentDate.getTime() - lastViewedTime) > popupRepeatDelayTime : true;
         var viewCountError = 'Already Viewed '+repeatCount+' Times in last '+popupRepeatCountType+' Hiding..'
         console.log(lastViewedTime, thecurrentDate.getTime(), popupRepeatDelayTime, (thecurrentDate.getTime() - lastViewedTime) > popupRepeatDelayTime);
         if(viewDelayMatched){
            console.log(lastViewedTime, thecurrentDate.getTime(), popupRepeatDelayTime, (thecurrentDate.getTime() - lastViewedTime) > popupRepeatDelayTime);
            if(popupRepeatCountType === '12h' && (parseInt(popupView12hCount, 10) >= repeatCount)){   return viewCountError;    }
            if(popupRepeatCountType === '6h' && (parseInt(popupView6hCount, 10) >= repeatCount)){   return viewCountError;    }
            if(popupRepeatCountType === '1h' && (parseInt(popupView1hCount, 10) >= repeatCount)){   return viewCountError;    }
            if(popupRepeatCountType === 'session' && (parseInt(popupOpenSessionCount, 10) >= repeatCount) ){   return viewCountError;    }
            if(popupRepeatCountType === 'day' && (parseInt(popupViewDayCount, 10) >= repeatCount)){   return viewCountError;    }
            if(popupRepeatCountType === 'month' && (parseInt(popupViewMonthCount, 10) >= repeatCount) ){   return viewCountError;    }
         }else{
            return 'Repeat Delay Doesnt Match! Hiding....';
         }
      }

      return false;
   }

   //Do not load if Closed n Times already
   var filterCloseFreq = function(popID){
      var popupCloseStat = localStorage.getItem('brave_popup_'+popID+'_closed') ? JSON.parse(localStorage.getItem('brave_popup_'+popID+'_closed')) : null;
      if(popupData.settings && popupData.settings.frequency && popupData.settings.frequency.close && popupCloseStat && popupCloseStat.closed){   
         var popupCloseCount = popupData.settings.frequency.closeCount ? popupData.settings.frequency.closeCount : 2; 
         var popupCloseFor = popupData.settings.frequency.closeFor ? parseInt(popupData.settings.frequency.closeFor, 10) * 86400 * 1000 : 86400 * 1000 * 30;
         var userCloseCount = parseInt(popupCloseStat.closed, 10);
         var userCloseDate = parseInt(popupCloseStat.closeTime, 10);
         var currentDate = new Date().getTime();
         var closeTimeDiff = currentDate - userCloseDate;
         var closeDelayMatched = popupRepeatDelay && popupRepeatDelayTime ? (currentDate - userCloseDate) > popupRepeatDelayTime : true;
         console.log('##### CLOSING STATS',userCloseCount, popupCloseFor,closeTimeDiff, closeTimeDiff >= popupCloseFor, userCloseCount >= popupCloseCount, closeDelayMatched, currentDate - userCloseDate);
         if((closeTimeDiff <= popupCloseFor && (userCloseCount >= popupCloseCount )) || !closeDelayMatched){
            return 'Close Time Settings Do not Match';
         }
      }
   }


   var viewFreqMatch = false; var closeFreqMatch = false; var formFreqMatch = false; goalFreqmatch = false;
   var hasViewFreqSetting = popupData.settings && popupData.settings.frequency && popupData.settings.frequency.repeat && brave_popup_adminUser === false;
   var hasCloseFreqSetting = popupData.settings && popupData.settings.frequency && popupData.settings.frequency.close && brave_popup_adminUser === false;
   var formFreqSetting = popupData.settings && popupData.settings.frequency && popupData.settings.frequency.formSubmit && brave_popup_adminUser === false;
   var goalFreqSetting = popupData.settings && popupData.settings.frequency && popupData.settings.frequency.goalComplete && brave_popup_adminUser === false;

   if(popVariants.length > 0){
      popVariants.forEach(function(popID){
         if(!viewFreqMatch && hasViewFreqSetting){
            var popViewFreqMatch = filterViewFreq(popID);
            if(popViewFreqMatch){
               viewFreqMatch = popViewFreqMatch;
            }
         }
         if(!closeFreqMatch && hasCloseFreqSetting){
            var popCloseFreqMatch = filterCloseFreq(popID);
            if(popCloseFreqMatch){
               closeFreqMatch = popCloseFreqMatch;
            }
         }
         if(!formFreqMatch && formFreqSetting && localStorage.getItem('brave_popup_'+popID+'_formsubmitted')){
            formFreqMatch = true;
         }
         if(!goalFreqmatch && goalFreqSetting && localStorage.getItem('brave_popup_'+popID+'_goal_complete')){
            goalFreqmatch = true;
         }
      })
   }else{
      if(hasViewFreqSetting){
         viewFreqMatch = filterViewFreq(popupID);
      }
      if(hasCloseFreqSetting){
         closeFreqMatch = filterCloseFreq(popupID);
      }
      if(!formFreqMatch && formFreqSetting && localStorage.getItem('brave_popup_'+popupID+'_formsubmitted')){
         formFreqMatch = true;
      }
      if(!goalFreqmatch && goalFreqSetting && localStorage.getItem('brave_popup_'+popupID+'_goal_complete')){
         goalFreqmatch = true;
      }
   }

   //Do not Load if the popup is already viewed x times.
   if(viewFreqMatch && hasViewFreqSetting){
      return console.log('[Viewed Frequency]',  popupID, viewFreqMatch);;
   }
   //Do not Load if the popup is already closed x times.
   if(closeFreqMatch && hasCloseFreqSetting){
      return console.log('[Closed Frequency]',  popupID, closeFreqMatch);;
   }
   //Do not Load if a Form Submitted in this Popup
   if(formFreqMatch && formFreqSetting){
      return console.log('[Form Frequency]',  popupID, 'Form in this Popup already Submitted! Hiding..');
   }
   //Do not Load if a Campaign Goal is completed already
   if(goalFreqmatch && goalFreqSetting){
      return console.log('[Goal Frequency]',  popupID, 'Popup Goal Already Completed..');
   }

   //Do not Load if Popups Countdown Timer has ended
   if(popupData.settings && popupData.settings.frequency && popupData.settings.frequency.timerEnded && popupData.timers.length > 0){
      const timerData = popupData.timers.find(item => item.device === brave_currentDevice);
      let hasEnded = false;
      if(timerData && timerData.endTime && new Date() > new Date(timerData.endTime) && timerData.auto === false) {
         hasEnded = true;
      }
      if(timerData && timerData.auto){
         const autoEndTime = localStorage.getItem(timerData.auto);
         if(new Date() > new Date(autoEndTime)){
            hasEnded = true;
         }
      }
      if(hasEnded){
         return console.log('Popup Countdown Timer Ended! Hiding..');
      }
   }

   //Do not Load if a certain popup was not viewed/goaled/closed before
   if(popupData.settings && popupData.settings.filters && popupData.settings.filters.popups_before && popupData.settings.filters.popups.length > 0){
      var popupFilterFulfilled = false;
      popupData.settings.filters.popups.forEach(function (popup) {
         if(!popupFilterFulfilled && popup.id && popup.action){
            var popupActionNegative = popup.action.includes('not_')
            var popActionKey = popupActionNegative ? popup.action.replace('not_', '') : popup.action
            var popupActionMatched = localStorage.getItem('brave_popup_'+popup.id+'_'+popActionKey);
            if(!popupActionNegative && popupActionMatched){  popupFilterFulfilled = true;  }
            if(popupActionNegative && !popupActionMatched){  popupFilterFulfilled = true;  }
         }
      })
      if(!popupFilterFulfilled){
         return console.log('A selected Popup was not viewed before! Hiding..');
      }
   }

   //Custom Cookie Conditions 
   if(popupData.settings && popupData.settings.filters && popupData.settings.filters.cookieFilter && popupData.settings.filters.cookies && popupData.settings.filters.cookies.length > 0 ){
      var cookieMatched = false;
      popupData.settings.filters.cookies.forEach(function(cookie){
         if(!cookieMatched && cookie.action === 'has' && cookie.key  && localStorage.getItem(cookie.key) ){   cookieMatched = true; }
         if(!cookieMatched && cookie.action === 'nothas' && cookie.key  && !localStorage.getItem(cookie.key) ){   cookieMatched = true; }
         if(!cookieMatched && cookie.action === 'equal' && cookie.key && cookie.value && localStorage.getItem(cookie.key) === cookie.value ){   cookieMatched = true; }
         if(!cookieMatched && cookie.action === 'notequal' && cookie.key && cookie.value && localStorage.getItem(cookie.key) !== cookie.value ){   cookieMatched = true; }
      })
      if(cookieMatched === false){   return console.log('Visitor Cookie did not match! Hiding..'); }
   }

   //Do not Load if page view count did not match
   if(popupData.settings && popupData.settings.filters && ((popupData.settings.filters.pages_count_filter && popupData.settings.filters.pagecount) || (popupData.settings.filters.pages_before && popupData.settings.filters.pages)) ){
      var viewCountLimit = parseInt(popupData.settings.filters.pagecount, 10);
      var current_page_view_data = localStorage.getItem('brave_page_visited'); 
      current_page_view_data = current_page_view_data ? JSON.parse(current_page_view_data) : [];
      if(popupData.settings.filters.pages_count_filter && popupData.settings.filters.pagecount && current_page_view_data && (current_page_view_data.length < viewCountLimit)){
         return console.log('Visitor did not view enough pages to show the popup! Hiding..');
      }
      //Do not Load if Visitor Did Not View selected pages before
      var pageConditionMatch = []; var viewConditionMatched = true;
      if(popupData.settings.filters.pages_before && popupData.settings.filters.pages && popupData.settings.filters.pages.length > 0){
         var pagesmatched = [];
         var braveMatchPageViewTime = function(timeLimit, viewTime){
            if(!timeLimit){ return true; }
            if(viewTime > timeLimit){ return true; }else{ return false; }
         }
         var braveCheckIfViewedPageMatch = function(pageCond, checkExistence=true, timeLimit) {
            var pageCondID = pageCond.id; var pageCondType = pageCond.type; var conditionMatched = false;
            current_page_view_data.forEach(function(pgView){
               var timeMatch = braveMatchPageViewTime(timeLimit, pgView.time);
               var pageMatch = pgView.PID === pageCondID && pgView.type === pageCondType && timeMatch;
               if(pageMatch && checkExistence){ conditionMatched = true;  }  
            })
            return conditionMatched;
         }
         var braveCheckIfNotViewedPageMatch = function(pageCond, checkExistence=false, timeLimit) {
            var pageCondID = pageCond.id; var pageCondType = pageCond.type; var conditionMatched = true;
            current_page_view_data.forEach(function(pgView){
               var timeMatch = braveMatchPageViewTime(timeLimit, pgView.time);
               var pageMatch = pgView.PID === pageCondID && pgView.type === pageCondType && timeMatch;
               if(pageMatch && !checkExistence){ conditionMatched = false;  }
            })
            return conditionMatched;
         }
         popupData.settings.filters.pages.forEach(function(pageCond){
            var currentTime = new Date().getTime(); var timeLimit  =  currentTime - 10800000;  if(pageCond.duration === 'lifetime' ){ timeLimit = false;}
            if(pageCond.duration === '1day' ){ timeLimit = currentTime - 86400000;} if(pageCond.duration === '7days' ){ timeLimit = currentTime - 604800000;} if(pageCond.duration === '30days' ){ timeLimit = currentTime - 2592000000;}
            var condMatch = pageCond.action.includes('not_viewed') ? braveCheckIfNotViewedPageMatch(pageCond, false, timeLimit) : braveCheckIfViewedPageMatch(pageCond, true, timeLimit);
            pagesmatched.push({matched: condMatch, forced: pageCond.condition === 'and' ? true : false });
         })

         pagesmatched.forEach(function(matchItm) {
            if(matchItm.forced === true &&  matchItm.matched === true){ pageConditionMatch.push(true); }
            if(matchItm.forced === true &&  matchItm.matched === false){ pageConditionMatch.push(false); }
            if(matchItm.forced === false &&  (matchItm.matched === true || matchItm.matched === false )){ pageConditionMatch.push(true); }
         })
         pageConditionMatch.forEach(function(bool){ if(bool===false){ viewConditionMatched = false; } })

         if(!viewConditionMatched){
            return console.log('Visitor did not view the selected pages before to show the popup! Hiding..');
         }

      }
   }

   if(triggerType.includes('load')){   brave_load_popup(popupID, popupData, 'load');   }

   if(triggerType.includes('exit')){
      if(brave_currentDevice === 'mobile'){
         if(!popupData.settings.trigger.exitMobileFallback || (popupData.settings.trigger.exitMobileFallback && popupData.settings.trigger.exitMobileFallback.type && popupData.settings.trigger.exitMobileFallback.type === 'load')){
            brave_load_popup(popupID, popupData,'load');
         }
         if(popupData.settings.trigger.exitMobileFallback && popupData.settings.trigger.exitMobileFallback.type && popupData.settings.trigger.exitMobileFallback.type === 'time'){
            var exitMobileDelay = popupData.settings.trigger.exitMobileFallback.time || 2000;
            setTimeout(function() {    brave_load_popup(popupID, popupData , 'time');  }, (exitMobileDelay * 1000));
         }
      }else{
         document.addEventListener("mouseout", function(evt){
            if((evt.toElement === null || evt.toElement === undefined) && (evt.relatedTarget === null)) {
                brave_load_popup(popupID, popupData, 'exit');
            }
         });
      }
   }

   if(triggerType.includes('scroll') || (brave_isMobile && popupData.settings.trigger.exitMobileFallback && popupData.settings.trigger.exitMobileFallback.type  && popupData.settings.trigger.exitMobileFallback.type ==='scroll')){
      var currentPopup = document.getElementById('brave_popup_'+popupID+'__step__0'); 
      var noMobileContent = currentPopup && currentPopup.classList.contains('brave_popup__step--mobile-noContent') === true ? true : false;
      var currentDevice = noMobileContent ? 'desktop' : brave_currentDevice;
      var scrollHide = popupData.settings && popupData.settings.trigger && popupData.settings.trigger.scrollHide ? true : false;
      var scrollTriggerPopup = function(scrollPercent, percentLimit=20, currentPopStep, currentPopStepVisible, between=false){
         var scrollPercentVal = between && percentLimit.split('-');
         var startScrollPercent = scrollPercentVal && scrollPercentVal[0] && parseInt(scrollPercentVal[0], 10);
         var endScrollPercent = scrollPercentVal && scrollPercentVal[1] && parseInt(scrollPercentVal[1], 10);

         if(brave_popup_data[popupID].userClosed){ return; }
         if((!between && (scrollPercent >= percentLimit)) || ((between && !isNaN(startScrollPercent) && !isNaN(endScrollPercent)) && ((scrollPercent >= startScrollPercent) && (scrollPercent <= endScrollPercent)) )){  
            if(!brave_popup_data[popupID].loaded){   brave_load_popup(popupID, popupData, 'scroll'); }else{  if(!currentPopStepVisible){brave_open_animation(popupID, 0, currentDevice);   currentPopStep.dataset.popopen = true; } } 
         }else{ 
            if((between || scrollHide) && currentPopStepVisible){  brave_close_animation(popupID, 0, currentDevice); currentPopStep.dataset.popopen = false; brave_popup_data[popupID].isOpen = false;  } 
         }
      }
      document.addEventListener("scroll", function(evt){
         var h = document.documentElement,  b = document.body, st = 'scrollTop', sh = 'scrollHeight';
         var scrollPercent = (h[st]||b[st]) / ((h[sh]||b[sh]) - h.clientHeight) * 100;
         var currentPopStep = document.querySelector('#brave_popup_'+popupID+'__step__0 .brave_popup__step__'+currentDevice); 
         var currentPopStepVisible = currentPopStep && currentPopStep.dataset.popopen === 'true' ? true : false;

         if( brave_isMobile && (popupData.settings.trigger.exitMobileFallback && popupData.settings.trigger.exitMobileFallback.type && popupData.settings.trigger.exitMobileFallback.type ==='scroll')){
            var exitMobielScroll = popupData.settings.trigger.exitMobileFallback.scroll || 15;
            if(scrollPercent >= exitMobielScroll){   brave_load_popup(popupID, popupData, 'scroll');   }
         }

         if(popupData.settings && popupData.settings.trigger.scrolltype && popupData.settings.trigger.scrolltype !== 'between'){
            var srollTargetAmnt = 0;
            if(popupData.settings.trigger.scrolltype === 'ten') {  srollTargetAmnt = 10}
            if(popupData.settings.trigger.scrolltype === 'twenty') {  srollTargetAmnt = 20}
            if(popupData.settings.trigger.scrolltype === 'thirty') {  srollTargetAmnt = 30}
            if(popupData.settings.trigger.scrolltype === 'forty') {  srollTargetAmnt = 40}
            if(popupData.settings.trigger.scrolltype === 'half') {  srollTargetAmnt = 50}
            if(popupData.settings.trigger.scrolltype === 'sixty') {  srollTargetAmnt = 60}
            if(popupData.settings.trigger.scrolltype === 'seventy') {  srollTargetAmnt = 70}
            if(popupData.settings.trigger.scrolltype === 'eighty') {  srollTargetAmnt = 80}
            if(popupData.settings.trigger.scrolltype === 'end') {  srollTargetAmnt = 95}
            scrollTriggerPopup(scrollPercent, srollTargetAmnt, currentPopStep, currentPopStepVisible);
         }
         if(popupData.settings && popupData.settings.trigger && popupData.settings.trigger.scrolltype === 'between' && popupData.settings.trigger.scrollBetween && popupData.settings.trigger.scrollBetween.includes('-')){
            scrollTriggerPopup(scrollPercent, popupData.settings.trigger.scrollBetween, currentPopStep, currentPopStepVisible, true);
         }

         if(popupData.settings && popupData.settings.trigger.scrolltype && popupData.settings.trigger.scrollto && popupData.settings.trigger.scrolltype === 'custom' ){
            var scrollToIDs = popupData.settings.trigger.scrollto || ''; 
            var scrolltoElms = scrollToIDs && document.querySelectorAll(scrollToIDs);
            //console.log(scrolltoElms);
            if(scrollToIDs && scrolltoElms){
               for (var i = 0; i < scrolltoElms.length; i++) { 
                  var scrolltoElm = scrolltoElms[i]; 
                  if(scrolltoElm && brave_is_in_view(scrolltoElm)){
                     brave_load_popup(popupID, popupData, 'scroll');  
                  }
               }
            }
         }
      }, {passive: true});
   }

   if(popupData.settings && popupData.settings.content && popupData.settings.content.highlight){
      document.addEventListener("scroll", function(evt){
         var contentElm = document.querySelector('.bravepopup_embedded--highlight_'+popupID);
         var contenthlElm = document.getElementById('bravepopup_embedded__bg_'+popupID);
         var contentAlreadyHighlighted = false; 
         if(popupData.settings.content.highlight === 'once'){ contentAlreadyHighlighted = contentElm.classList.contains('bravepopup_embedded__highlight--done') ? true : false;}
         if(contentElm && (popupData.settings.content.highlight === 'always' || (!contentAlreadyHighlighted && popupData.settings.content.highlight === 'once'))){
            var embeddedContentRect = contentElm.getBoundingClientRect();
            var embeddedViewRatio = (embeddedContentRect.top / document.documentElement.clientHeight)*100;
            if((embeddedViewRatio < 50 && embeddedViewRatio > -20 ) && !contenthlElm.classList.contains('bravepopup_embedded__bg--active')){
               contenthlElm.classList.add('bravepopup_embedded__bg--active'); contentElm.classList.add('bravepopup_embedded__highlight--active');
            }
            if((embeddedViewRatio < -20 || embeddedViewRatio > 50) && contenthlElm.classList.contains('bravepopup_embedded__bg--active')){
               contenthlElm.classList.remove('bravepopup_embedded__bg--active'); contentElm.classList.remove('bravepopup_embedded__highlight--active');
               if(popupData.settings.content.highlight){   contentElm.classList.add('bravepopup_embedded__highlight--done');  }
            }
         }
      }, {passive: true});
   }


   if(triggerType.includes('click') && popupData.settings && popupData.settings.trigger.clickElements){
      var clickElms = document.querySelectorAll(popupData.settings.trigger.clickElements);
      //console.log(clickElms);
      
      if(clickElms){
         for (var i = 0; i < clickElms.length; i++) { 
            var clickElm = clickElms[i]; 
            clickElm.addEventListener("click", function(evt){
               evt.preventDefault();
               document.getElementById('brave_popup_'+popupID).style.zIndex = 9999999999;
               brave_load_popup(popupID, popupData, 'click');
            });
         }
      }
   }

   if(triggerType.includes('time') && popupData.settings && popupData.settings.trigger.time && (popupData.settings.trigger.time.hours || popupData.settings.trigger.time.minutes || popupData.settings.trigger.time.seconds)){
      var triggerHours = popupData.settings.trigger.time.hours ? parseInt(popupData.settings.trigger.time.hours, 10) : 0;
      var triggerMinutes = popupData.settings.trigger.time.minutes ? parseInt(popupData.settings.trigger.time.minutes, 10) : 0;
      var triggerSeconds = popupData.settings.trigger.time.seconds ? parseInt(popupData.settings.trigger.time.seconds, 10) : 0;

      var totalTriggerDelay = (triggerHours * 3600) + (triggerMinutes * 60) + (triggerSeconds);
      totalTriggerDelay = totalTriggerDelay * 1000;
      //console.log(triggerHours, triggerMinutes, triggerSeconds, totalTriggerDelay);
      
      setTimeout(function() {
          brave_load_popup(popupID, popupData , 'time');
      }, totalTriggerDelay);

   }


   
}

function brave_load_popup(popupID, popupData, triggerType='load'){
   if(brave_popup_data[popupID] && brave_popup_data[popupID].ajaxLoad && !brave_popup_data[popupID].ajaxLoaded){
      if(brave_popup_data[popupID].ajaxLoading !== true){
         var loadData = { popupID: popupID, type: brave_popup_data[popupID].type, security: bravepop_global.security, current_url: location.href ,action: 'bravepop_ajax_load_popup_content' };
         brave_popup_data[popupID].ajaxLoading = true;
         brave_ajax_send(bravepop_global.ajaxURL, loadData, function(status, sentData){
            // console.warn('Ajax Load Popup!!', popupID, brave_popup_data[popupID].ajaxLoading);
            brave_popup_data[popupID].ajaxLoaded = true;
            brave_popup_data[popupID].ajaxLoading = false;
            var selectedPopup = document.getElementById('brave_popup_'+popupID);
            if(selectedPopup){
               selectedPopup.innerHTML = sentData;
               brave_process_open_popup(popupID, popupData, triggerType);
            }
         });
      }
   }else{
      brave_process_open_popup(popupID, popupData, triggerType);
   }
}


function brave_process_open_popup(popupID, popupData, triggerType='load'){
   var selectedPopup = document.getElementById('brave_popup_'+popupID);
   var popupLoadStatus = selectedPopup ? selectedPopup.dataset.loaded : 'false';
   if(popupLoadStatus === 'false'){
      //LOAD ASSETS
      //--------------------
         //Load Video Scripts
         if(popupData.hasYoutube){
            var YTtag = document.createElement('script'); YTtag.src = "https://www.youtube.com/iframe_api";
            var PageFirstScript_YT = document.getElementsByTagName('script')[0]; PageFirstScript_YT.parentNode.insertBefore(YTtag, PageFirstScript_YT);
         }
         if(popupData.hasVimeo){
            var VimTag = document.createElement('script'); VimTag.src = "https://player.vimeo.com/api/player.js";
            var PageFirstScript_Vim = document.getElementsByTagName('script')[0]; PageFirstScript_Vim.parentNode.insertBefore(VimTag, PageFirstScript_Vim);
         }
         
         //Load the DatePicker
         var dateFields = document.querySelectorAll('.brave_form_field--date');
         if(window.brave_initPikaday && dateFields && dateFields.length > 0 ){
            for (var i = 0, len = dateFields.length; i < len; i++) {
               var dateField = dateFields[i];
               var startDate = dateField.dataset.startdate ? dateField.dataset.startdate : '';
               var endDate = dateField.dataset.enddate ? dateField.dataset.enddate : '';
               var dateInput = dateField.querySelector('input');
               brave_initPikaday(dateInput, startDate, endDate);
            }
         }

         selectedPopup.dataset.loaded = true;
   }

   //Open Popup
   //---------------------
   let step = popupData.forceLoad && popupData.forceStep? parseInt(popupData.forceStep, 10) - 1 : 0;
   if(brave_popup_data[popupID].settings && brave_popup_data[popupID].settings.frequency && brave_popup_data[popupID].settings.frequency.rememberLastStep){
      let foundLastStep = localStorage.getItem('brave_popup_'+popupID+'_last_viewed_step');
      if(foundLastStep !==undefined && foundLastStep !==null){
         step = foundLastStep;
      }
   }
   if(triggerType === 'exit' || triggerType === 'scroll' || triggerType === 'time'){
      var triggerFulFilled = document.getElementById('brave_popup_'+popupID).dataset.triggerfulfilled;
      console.log('triggerFulFilled: ', triggerFulFilled);
      if(!triggerFulFilled){
         document.getElementById('brave_popup_'+popupID).dataset.triggerfulfilled = true;
         brave_open_popup(popupID, step);
      }
   }else{
      brave_open_popup(popupID, step);
   }

   if(brave_popup_data[popupID] && !brave_popup_data[popupID].loaded){  brave_popup_data[popupID].loaded = true; }
}


function brave_open_popup(popupID, step=0){
   var popupData = brave_popup_data[popupID];
   var selectedPopupStep = document.querySelector('#brave_popup_'+popupID+'__step__'+step+' .brave_popup__step__'+brave_currentDevice)
   var noMobileContent = selectedPopupStep && selectedPopupStep.dataset.nomobilecontent === 'true' ? true : false;
   var currentDevice = noMobileContent ? 'desktop' : brave_currentDevice;
   var selectedPopupStep = document.querySelector('#brave_popup_'+popupID+'__step__'+step+' .brave_popup__step__'+currentDevice);
   var popupStepOpen = selectedPopupStep ? selectedPopupStep.dataset.popopen : 'false';
   var hasLockScroll = selectedPopupStep.dataset.scrollock ?  true : false;
   var stickyBar = selectedPopupStep && selectedPopupStep.dataset.layout === 'float' && selectedPopupStep.dataset.position === 'top_center' ? true : false;
   
   if(popupStepOpen !== 'false'){ return }
   if(!popupData){ return }
   if(popupData && popupData.forceHide){ return console.log(popupID, 'Force Hidden'); }

   console.log('Opening ', popupID, step, popupStepOpen, stickyBar, noMobileContent, currentDevice);
   //Set Current Step
   brave_popup_data[popupID].currentStep = step;

   //Send Ajax View Stat Data
   if(window.location.href.includes('brave_popup') === false && window.location.href.includes('braveshot') === false && !brave_popup_data[popupID].viewStatSent){ 
      var viewDate = new Date(); var viewYear = viewDate.getFullYear(); var viewMonth = brave_number_padding(viewDate.getMonth() + 1);  var viewDate = brave_number_padding(viewDate.getDate()); var goalIsFirstView = false;
      if(brave_popup_data[popupID] && brave_popup_data[popupID].settings && brave_popup_data[popupID].settings.goalAction && 
      brave_popup_data[popupID].settings.goalAction.type === 'step' && brave_popup_data[popupID].settings.goalAction.step === 0 ){  goalIsFirstView = true;  } 
      var viewData = { popupID: popupID, date: viewYear+'-'+viewMonth+'-'+viewDate, goalIsFirstView: goalIsFirstView, pageURL: window.location, goalUTCTime: new Date().toUTCString(), security: bravepop_global.security, action: 'bravepop_ajax_popup_viewed' };
      brave_ajax_send(bravepop_global.ajaxURL, viewData, function(status, sentData){
         // console.log('Popup View Updated: ', status, JSON.parse(sentData));
         brave_popup_data[popupID].viewStatSent = true;
      });
   }
   //Set Popup Viewed Cookie
   var currentPopupStat = localStorage.getItem('brave_popup_'+popupID+'_viewed');
   localStorage.setItem('brave_popup_'+popupID+'_viewed', currentPopupStat ? parseInt(currentPopupStat, 10) + 1 : 1);
   var popupRepeat =popupData.settings && popupData.settings.frequency && popupData.settings.frequency.repeat
   var popupRepeatCountType = popupData.settings && popupData.settings.frequency && popupData.settings.frequency.repeatCountType || 'lifetime';
   if(popupRepeat && popupRepeatCountType !== 'lifetime'){
      var theViewStat = localStorage.getItem('brave_popup_'+popupID+'_view_stat'); theViewStat = theViewStat ? JSON.parse(theViewStat) : [];
      theViewStat.push(new Date().getTime());      
      localStorage.setItem('brave_popup_'+popupID+'_view_stat', JSON.stringify(theViewStat));
   }

   if(popupRepeat && popupRepeatCountType === 'session'){
      var currentSessionPopupViews = sessionStorage.getItem('brave_popup_'+popupID+'_viewed') || 0;
      sessionStorage.setItem('brave_popup_'+popupID+'_viewed', parseInt(currentSessionPopupViews, 10)+1);
   }

   //Scroll Lock
   if(hasLockScroll){
      document.body.classList.add('brave_scroll_lock')
   }

   //Stickybar - Add html top margin
   if(stickyBar){
      //console.log('Apply Sticky Margin');
      var popupHeight = selectedPopupStep.dataset.height;
      document.documentElement.style.setProperty('margin-top', popupHeight+'px', 'important');
   } 

   //LazyLoad Images
   if(popupData.type !== 'content'){
      var allImages = selectedPopupStep.querySelectorAll('img');
      for (var i = 0; i < allImages.length; i++) { 
         if(allImages[i].dataset.lazy){
            allImages[i].src = allImages[i].dataset.lazy;
         }
      }
   }

   //Stop currently Playing Videos First
   if(brave_popup_videos && brave_popup_videos[popupID] && Object.keys(brave_popup_videos).length > 0){
      Object.keys(brave_popup_videos[popupID]).forEach(function(playerID){
         if(playerID.includes('youtube') && brave_popup_videos[popupID][playerID] && brave_popup_videos[popupID][playerID].stopVideo){   brave_popup_videos[popupID][playerID].stopVideo();  }
         if(playerID.includes('vimeo') && brave_popup_videos[popupID][playerID] && brave_popup_videos[popupID][playerID].pause){   brave_popup_videos[popupID][playerID].pause();  }
         if(playerID.includes('custom') && brave_popup_videos[popupID][playerID] && brave_popup_videos[popupID][playerID].pause){   brave_popup_videos[popupID][playerID].pause();  }
      });
   }
   if(popupData.videoData && popupData.videoData[step] && popupData.videoData[step][currentDevice]){
      //console.log('HAS VIDEO!!!!!!!!!!!', popupData.videoData[step][currentDevice]);
      var videoObj = popupData.videoData[step][currentDevice];
      var videoType = videoObj.videoType ? videoObj.videoType : 'youtube';
      var videoURL = videoObj.videoUrl ? videoObj.videoUrl : 'youtube';
      var videoTracking = videoObj.action && videoObj.action.track && videoObj.action.trackData ? videoObj.action.trackData : null;
      var videoSettings = {id: videoObj.id, autoplay: videoObj.autoplay || false, controls: videoObj.controls || false, mute: videoObj.mute || false, tracking: videoTracking }
      brave_load_video(videoType, videoURL, videoSettings, popupID);
   }

   //Replace Text element Shortcodes
   var elmsWithCookie = selectedPopupStep && selectedPopupStep.querySelectorAll('.brave_element--text_hasCookie .brave_element__text_inner');
   if(elmsWithCookie && elmsWithCookie.length > 0){
      brave_replace_dynamic_text_cookie(elmsWithCookie);
   }

   var hasAnimation = popupData.hasAnimation ?  popupData.hasAnimation : false;
   var advancedAnimation = popupData.advancedAnimation ?  popupData.advancedAnimation : false;
   var hasContAnim = popupData.hasContAnim ?  popupData.hasContAnim : false;
   var animationData = popupData.animationData ?  popupData.animationData : {};
   var selectedStep = document.getElementById('brave_popup_'+popupID+'__step__'+step);
   if(!selectedStep){ return;}

   var focusableElm = selectedStep.querySelector('.brave_popup--popup .brave_popupMargin__wrap');
   var closableFocus = selectedStep.querySelector('.brave_popup--popup .brave_popup__close');  
   if(closableFocus){   closableFocus.tabIndex = 0;  }
   if(focusableElm){ focusableElm.tabIndex = 0; setTimeout(() => {   focusableElm.focus();   }, 200);  }
   
   if(selectedStep){
      var allSteps = document.querySelectorAll('#brave_popup_'+popupID+' .brave_popup__step_wrap');
      if(allSteps){
         for (var i = 0; i < allSteps.length; i++) { 
            allSteps[i].classList.remove('brave_popup__step_wrap--show');
         }
      }
      var braveOpenEvent = new CustomEvent('brave_popup_open', { detail: {popupId: parseInt(popupID, 10), step: step} });
      document.dispatchEvent(braveOpenEvent);

      //Open Animation
      if(!advancedAnimation){
         brave_open_animation(popupID, step, currentDevice);
      }

      //Advanced Animation
      if(advancedAnimation && hasAnimation && animationData && brave_animate_popup){
         selectedStep.classList.add('brave_popup__step_wrap--show');
         brave_animate_popup(animationData, popupID, step, 'load');
      }
      //Continious Animation
      if(hasContAnim && animationData && animationData[step][currentDevice] && animationData[step][currentDevice].elements){
         var initialDelay = (animationData[step][currentDevice].totalDuration || 0) + 1200;
         animationData[step][currentDevice].elements.forEach(function(element){
            //console.log(element);
            
            if(element && element.animation && element.animation.continious){
               //console.log(element.id,element.animation.continious);
               var theElement = element.id === 'popup' ? document.querySelector('#brave_popup_'+popupID+'__step__'+step+' .brave_popup__step__inner .brave_popupSections__wrap') : document.getElementById('brave_element-'+element.id);
               var elementID = element.id === 'popup' ? popupID : element.id;
               var contAnimType = element.animation.continious.preset || 'none';
               var contAnimDuration = element.animation.continious.duration || 500;
               var contAnimDelay = element.animation.continious.delay || 0;
               if(theElement){
                  setTimeout(function() {
                     theElement.classList.add('brave_element-'+elementID+'_contAnim'); 
                     if(contAnimDelay > 0){
                        setInterval(function() {
                           theElement.classList.add('brave_element-'+elementID+'_contAnim'); 
                           setTimeout(function() {
                              theElement.classList.remove('brave_element-'+elementID+'_contAnim');
                           }, contAnimDuration);
                        }, ((contAnimDelay > contAnimDuration) ? contAnimDelay : contAnimDuration + contAnimDelay))
                     }
                  }, initialDelay);
               }
            }

         })

      }

   }
   //Show Vertical Scrollbar if Necessary
   setTimeout(function() {
      var currentPopupDimension = selectedPopupStep.querySelector('.brave_popup__step__inner').getBoundingClientRect();
      if((window.innerHeight < currentPopupDimension.height) && selectedPopupStep.classList.contains('brave_popup__step--boxed') && selectedPopupStep.classList.contains('position_center')){
         selectedPopupStep.classList.add('brave_popup_exceeds_windowHeight');
         selectedPopupStep.classList.add('brave_popup_show_scrollbar');
         //document.body.classList.add('brave_HideBodyScroll-'+popupID);
      }
   }, 100);


   //Popup AutoClose
   //console.warn(popupData);
   if(popupData.close[0] && popupData.close[0][currentDevice] && popupData.close[0][currentDevice].autoClose && popupData.close[0][currentDevice].autoCloseDuration){
      setTimeout(function() {
         if(!brave_popup_data[popupID].autoClosed){
            brave_close_popup(popupID, step);
            if(popupData.close[0][currentDevice].closeStep !== 'undefined' && Number.isInteger(popupData.close[0][currentDevice].closeStep)){
               brave_open_popup(popupID, popupData.close[0][currentDevice].closeStep);
            }
         }
         brave_popup_data[popupID].autoClosed = true;
      }, parseInt(popupData.close[0][currentDevice].autoCloseDuration) * 1000);
   }

   //Change the Popup Step open status
   selectedPopupStep.dataset.popopen = true;
   brave_popup_data[popupID].opened = new Date().getTime();
   brave_popup_data[popupID].isOpen = true;

   //Complete Step Open Goal
   if((popupData.settings && !popupData.settings.goalAction) || (popupData.settings && popupData.settings.goalAction && popupData.settings.goalAction.type  && popupData.settings.goalAction.type==='step' && popupData.settings.goalAction.step !== undefined )){
      var goalStep = popupData.settings.goalAction && popupData.settings.goalAction.step ? popupData.settings.goalAction.step.toString().split(',') :['0'];
      if(goalStep.includes(step.toString()) && step !== 0){
         brave_complete_goal(popupID, 'view');
      }
   }

   //If Code element is set to goal, attach submit event listener to complete goal
   var allCodeElmGoals = document.querySelectorAll('.brave_element__code--goaled form');
   if(allCodeElmGoals.length > 0){
      for (var i = 0; i < allCodeElmGoals.length; ++i) {
         allCodeElmGoals[i].addEventListener('submit', function () {    brave_complete_goal(popupID, 'form');    })
       }
   }
   if(brave_popup_data[popupID].settings && brave_popup_data[popupID].settings.frequency && brave_popup_data[popupID].settings.frequency.rememberLastStep){
      localStorage.setItem('brave_popup_'+popupID+'_last_viewed_step', step);
   }

   // Close Button Delayed Appearance
   const closeProgressBar = selectedStep.querySelector('.brave_popup__close__progress-circle'); 
   if(closeProgressBar){
      const closeDuration = parseInt(closeProgressBar.dataset.duration, 10);
      const closeCircle = selectedStep.querySelector('.brave_popup__close__progress-ring-circle');   
      const closeRadius = closeCircle.r.baseVal.value;
      const circumference = closeRadius * 2 * Math.PI;
      let currentSecond = 0;
   
      window.braveCloseProgrss = setInterval(function() {
       if (currentSecond >= closeDuration / 1000) {
         clearInterval(window.braveCloseProgrss);
         closeProgressBar.style.display = 'none';
         selectedStep.querySelector('.brave_popup__close__button').style.display = 'block';
       }else{
         currentSecond++;
         const percent = currentSecond / (closeDuration / 1000) * 100;
         closeCircle.style.strokeDashoffset = circumference - (percent / 100 * circumference);
         selectedStep.querySelector('.brave_popup__close__progress-text').textContent = Math.round(closeDuration / 1000 - currentSecond)+'s';
       }
      }, 1000);
   }

}

function brave_open_animation(popupID, step, currentDevice){
   var totalDuration = brave_popup_data[popupID].animationData[step][currentDevice].totalDuration;
   var openAnimData = brave_popup_data[popupID].animationData[step][currentDevice].elements ? brave_popup_data[popupID].animationData[step][currentDevice].elements : [];
   var hasAnimation = brave_popup_data[popupID].hasAnimation;
   var selectedStep = document.querySelector('#brave_popup_'+popupID+'__step__'+step);

   if(window.location.href.includes('braveshot') === true && window.location.href.includes('brave_id') === true){
      return selectedStep.classList.add('brave_popup__step_wrap--show');
   }

   //console.log(popupID, step, currentDevice, openAnimData, selectedStep);

   var brave_animateElement = function(elementID, selectedStep, elementDom, step, animType) {
      selectedStep.classList.add('brave_popup__step_wrap--show');
      if(animType === 'text'){
         var selected_text_element =  document.querySelector('#brave_element-'+elementID+' .brave_element__text_inner');
         var selected_text_element_HTML = selected_text_element.innerHTML;
         var selected_text_element_content = selected_text_element.textContent;
         var newHTLArray = selected_text_element_content.toString().split('');
         var selected_text_element_clone = selected_text_element; 
         
         selected_text_element_clone.innerHTML ='';
         for (var i=0;i<=(newHTLArray.length - 1);i++) {
            (function(ind) {
               setTimeout(function(){
                  selected_text_element_clone.innerHTML = selected_text_element_clone.innerHTML+newHTLArray[ind];
               }, 1000 + (50 * ind));
            })(i);
         }
         setTimeout(function(){
            selected_text_element_clone.innerHTML = selected_text_element_HTML;
         }, 1000 + (50 * (newHTLArray.length -1)));
      }else{
         //console.log(elementID, selectedStep, elementDom, step, animType);
         if(!elementDom.classList.contains('brave_element-'+elementID+'_'+step+'_openAnim')){ elementDom.classList.add('brave_element-'+elementID+'_'+step+'_openAnim'); }
      }
   }

   if(hasAnimation && openAnimData.length > 0){
      selectedStep.classList.add('brave_popup__step_wrap--show');
      openAnimData.forEach( function(element){
         if(element.animation && element.animation.load && element.animation.load.preset){
            var animType = element.animation.load.preset;
            var animDuration = element.animation.load.duration;
            var animDelay = element.animation.load.delay || 0;
            var elementID = element.id === 'popup' ? popupID : element.id;
            var elementDom = element.id === 'popup' ? selectedStep.querySelector('.brave_popup__step__'+currentDevice+' .brave_popupSections__wrap') : selectedStep.querySelector('.brave_popup__step__'+currentDevice+' #brave_element-'+element.id);
            elementDom.style.opacity = 0;
            selectedStep.classList.remove('brave_popup__step_wrap--show');
            if(animDelay){
               // setTimeout(function(){    brave_animateElement(elementID, selectedStep, elementDom, step, animType);  }, animDelay);
               brave_animateElement(elementID, selectedStep, elementDom, step, animType);
            }else{
               brave_animateElement(elementID, selectedStep, elementDom, step, animType);
            }
            if(element.id === 'popup'){  animDelay = 0; } //Disable Popup Animation Delay
            setTimeout(function() { elementDom.style.opacity = ''; }, animDelay+ animDuration - 100);
            setTimeout(function(){
               elementDom.classList.remove('brave_element-'+elementID+'_'+step+'_openAnim');
            }, animDelay+ animDuration + 500);
         }
      })

   }else{
      selectedStep.classList.add('brave_popup__step_wrap--show');
   }

}

function brave_close_animation(popupID, step, currentDevice){
   //Close Animation
   var selectedStep = document.getElementById('brave_popup_'+popupID+'__step__'+step);
   var selectedStepDevice = selectedStep.querySelector('.brave_popup__step__'+currentDevice);
   var exitAnimation = selectedStepDevice.dataset.exitanimtype || '';
   var exitAnimationDuration = selectedStepDevice.dataset.exitanimlength ? parseFloat(selectedStepDevice.dataset.exitanimlength, 10) : 0.5;
   var hasAnimation = brave_popup_data[popupID].hasAnimation ?  brave_popup_data[popupID].hasAnimation : false;
   var advancedAnimation = brave_popup_data[popupID].advancedAnimation ?  brave_popup_data[popupID].advancedAnimation : false;
   var animationData = brave_popup_data[popupID].animationData ?  brave_popup_data[popupID].animationData : {};
   var hasCustomExitAnimation = animationData[step][currentDevice].totalDuration['exit'];
   //console.warn(currentDevice, exitAnimation, exitAnimationDuration, hasAnimation, selectedStepDevice);

   if(selectedStep){
      if(exitAnimation){
         selectedStepDevice.querySelector('.brave_popupSections__wrap').classList.add('brave_element-'+popupID+'_'+step+'_exitAnim');
         setTimeout(function() { selectedStep.classList.remove('brave_popup__step_wrap--show');}, (exitAnimationDuration * 1000));
         setTimeout(function() { selectedStepDevice.querySelector('.brave_popupSections__wrap').classList.remove('brave_element-'+popupID+'_'+step+'_exitAnim'); }, (exitAnimationDuration * 1000)+500);
         if(selectedStep.querySelector('.brave_popup__step__'+currentDevice+' .brave_popup__step__overlay')){
            setTimeout(function() { 
               selectedStepDevice.querySelector('.brave_popup__step__overlay').classList.add('brave_popup__step__overlay--hide');
            }, (exitAnimationDuration > 0.3 ? ((exitAnimationDuration * 1000) - 200) : 200));
            setTimeout(function() { selectedStepDevice.querySelector('.brave_popup__step__overlay').classList.remove('brave_popup__step__overlay--hide'); }, (exitAnimationDuration * 1000)+500);
         }
      }else if(advancedAnimation && hasCustomExitAnimation){
         if(advancedAnimation && hasAnimation && animationData && brave_animate_popup){
            brave_animate_popup(animationData, popupID, step, 'exit');
         }
      }else{
         selectedStep.classList.add('brave_popup__step_wrap--hide');
         setTimeout(function() { selectedStep.classList.remove('brave_popup__step_wrap--show');}, 500);
         setTimeout(function() { selectedStep.classList.remove('brave_popup__step_wrap--hide');}, 800);
      }
   }
         
}

function brave_close_popup(popupID, step=0, gotoStep=false, updateStat=true){
   var selectedStep = document.getElementById('brave_popup_'+popupID+'__step__'+step);
   var selectedPopupStep = selectedStep.querySelector('.brave_popup__step__'+brave_currentDevice);
   var noMobileContent = selectedPopupStep.dataset.nomobilecontent === 'true' ? true : false;
   var currentDevice = noMobileContent ? 'desktop' : brave_currentDevice;
   var hasLockScroll = selectedStep.querySelector('.brave_popup__step__'+currentDevice) && selectedStep.querySelector('.brave_popup__step__'+currentDevice).dataset.scrollock ?  true : false;
   var exitAnimation = selectedStep.querySelector('.brave_popup__step__'+currentDevice).dataset.exitanimtype || '';
   var exitAnimationDuration = selectedStep.dataset.exitanimlength ? parseFloat(selectedStep.dataset.exitanimlength, 10) : 0.5;

   //Scroll Lock
   if(hasLockScroll){
      document.body.classList.remove('brave_scroll_lock')
   }

   if(selectedStep){

      if(updateStat){
         var currentPopupCloseStat = localStorage.getItem('brave_popup_'+popupID+'_closed') ? JSON.parse(localStorage.getItem('brave_popup_'+popupID+'_closed')) : {};
         var newCloseStat = {closed: currentPopupCloseStat.closed ? currentPopupCloseStat.closed + 1 : 1, closeTime: new Date().getTime()}
         localStorage.setItem('brave_popup_'+popupID+'_closed', JSON.stringify(newCloseStat));
      }
      
      //Set the Popup Open status to False 
      var selectedPopupStep = selectedStep.querySelector('.brave_popup__step__'+currentDevice);
      selectedPopupStep.dataset.popopen = 'false';

      //Stop All Videos
      if(brave_popup_videos && Object.keys(brave_popup_videos).length > 0){
         Object.keys(brave_popup_videos).forEach(function(popupID){
            Object.keys(brave_popup_videos[popupID]).forEach(function(playerID){
               if(brave_popup_videos[popupID][playerID]){
                  if(playerID.includes('youtube') && brave_popup_videos[popupID][playerID] && brave_popup_videos[popupID][playerID].stopVideo){   brave_popup_videos[popupID][playerID].stopVideo();  }
                  if(playerID.includes('vimeo') && brave_popup_videos[popupID][playerID] && brave_popup_videos[popupID][playerID].pause){   brave_popup_videos[popupID][playerID].pause();  }
                  if(playerID.includes('custom') && brave_popup_videos[popupID][playerID] && brave_popup_videos[popupID][playerID].pause){   brave_popup_videos[popupID][playerID].pause();  }
               }
            });
         });
      }

      brave_popup_data[popupID].userClosed = true;
      brave_popup_data[popupID].isOpen = false;
      var braveCloseEvent = new CustomEvent('brave_popup_close', { detail: {popupId: parseInt(popupID, 10), step: step} });
      document.dispatchEvent(braveCloseEvent);

      //Play Exit Animation
      brave_close_animation(popupID, step, currentDevice);

      //If has gotoStep Command, go there
      setTimeout(function() {
         if(gotoStep !== false && gotoStep >=0){
            //First set the Taget Step's Open status to false
            var targetStep = document.getElementById('brave_popup_'+popupID+'__step__'+gotoStep);
            targetStep.querySelector('.brave_popup__step__desktop').dataset.popopen = false;
            targetStep.querySelector('.brave_popup__step__mobile').dataset.popopen = false;
            
            //Then Open the Popup
            brave_open_popup(popupID, gotoStep);
         }
         var stickyBar = selectedPopupStep.dataset.layout === 'float' && selectedPopupStep.dataset.position === 'top_center' ? true : false;
         if(stickyBar){
            var newHeight = document.querySelector('body.admin-bar') ? '32px' : '0px';
            document.documentElement.style.setProperty('margin-top', newHeight, 'important');
         }

         //Reset the zIndex that was set for click triggered Popups
         document.getElementById('brave_popup_'+popupID).style.zIndex = 9999999999;
         
      }, ( exitAnimation && exitAnimationDuration ? (exitAnimationDuration * 1000) : 10));
   }

}

function brave_send_ga_event(eventCategory, eventAction, eventLabel){
   //console.log('ga Event: ', eventCategory, eventAction, eventLabel);
   if ("gtag" in window && eventCategory && eventAction) {
      gtag("event", eventAction, {
         "event_category": eventCategory,
         "event_label": eventLabel
     });
   }
}
function brave_send_fbq_event(eventType, fbq_content_name, fbq_content_category, fbq_value, fbq_currency){
   //console.log('fbq Event: ', eventType, fbq_content_name, fbq_content_category, fbq_value, fbq_currency);
   if (window.fbq && eventType) {
      var fbqData = {};
      if(fbq_content_name){ fbqData.content_name = fbq_content_name }
      if(fbq_content_category){ fbqData.content_category = fbq_content_category }
      if(fbq_value){ fbqData.value = parseFloat(fbq_value, 10) }
      if(fbq_currency){ fbqData.currency = fbq_currency }

      if(eventType === 'Contact'){
         fbq('track', 'Contact');
      }
      if(eventType === 'Lead'){
         fbq('track', 'Lead', fbqData);
         console.log(fbqData);
      }
  }
}



function brave_load_video(videoType, videoURL, videoSettings, popupID){
   //console.log(videoType, videoURL, videoSettings, videoSettings.tracking);

   var elmID = videoSettings.id.replace(/[^a-zA-Z0-9]+/g, '');
   if(!brave_popup_videos[popupID]){
      brave_popup_videos[popupID] = {}
   }
   if(videoType ==='custom'){
      brave_popup_videos[popupID]['video_'+videoType+elmID] = document.getElementById('brave_video_custom_'+videoSettings.id);
      if(videoSettings.autoplay){
         brave_play_video(popupID, videoSettings.id, 'custom', videoSettings.tracking);
      }
   }

   if(videoType ==='youtube'){
      var youtube_regex = new RegExp(/^.*(youtu\.be\/|vi?\/|u\/\w\/|embed\/|\?vi?=|\\&vi?=)([^#\\&\\?]*).*/);
      var parsed = videoURL.match(youtube_regex);
      var videoID = parsed && parsed[2] ? parsed[2]:null;
   
      if(!brave_popup_videos[popupID]['video_'+videoType+elmID]){
         setTimeout(function() {
            brave_popup_videos[popupID]['video_'+videoType+elmID] = new YT.Player('brave_video_iframe'+elmID, {
               videoId: videoID,
               playerVars: { 'autoplay': false, 'controls': videoSettings.controls ? 1 : 0, mute: videoSettings.mute ? videoSettings.mute : false  },
               events: {
                  'onReady': function(){
                     return videoSettings.autoplay && brave_play_video(popupID, videoSettings.id, 'youtube', videoSettings.tracking)
                  }
               }
            });
         }, 1000);
      }else{
         if(videoSettings.autoplay) { brave_play_video(popupID, videoSettings.id, 'youtube', videoSettings.tracking); }
      } 
   }

   if(videoType ==='vimeo'){
      var vimeo_regex = new RegExp(/https?:\/\/(?:www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/);
      var parsed = videoURL.match(vimeo_regex);
      var videoID = parsed && parsed[3] ? parsed[3] : null;
      
      if(!brave_popup_videos[popupID]['video_'+videoType+elmID]){
         setTimeout(function() {
            brave_popup_videos[popupID]['video_'+videoType+elmID] = new Vimeo.Player('brave_video_iframe'+elmID, {  id: videoID, background: videoSettings.controls ===false ? true : false });
            setTimeout(function() {
               if(videoSettings.mute){
                  brave_popup_videos[popupID]['video_'+videoType+elmID].setVolume(0);
               }
               if(videoSettings.autoplay){
                  brave_play_video(popupID, videoSettings.id, 'vimeo', videoSettings.tracking);
               }
            }, 2000);
         }, 1000);
      }else{
         if(videoSettings.autoplay) { brave_play_video(popupID, videoSettings.id, 'vimeo', videoSettings.tracking); }
      }
      
   }

}

function brave_play_video(popupID, elmentID, videoType, track=null, inline=false){
   console.log('brave_play_video', elmentID, videoType, track );
   var elmID = elmentID.replace(/[^a-zA-Z0-9]+/g, '');
   if(!brave_popup_videos[popupID]['video_'+videoType+elmID]){ return; }

   if(document.getElementById("brave_element-"+elmentID)){
      document.getElementById("brave_element-"+elmentID).classList.add('brave_element--video-show');
   }

   //Custom Video Playback
   if(videoType === 'custom' ){
      brave_popup_videos[popupID]['video_'+videoType+elmID].muted = true; //Video doesnt autoplay if not muted
      brave_popup_videos[popupID]['video_'+videoType+elmID].play();
      var videoMuted = brave_popup_videos[popupID]['video_'+videoType+elmID].classList.contains('brave_video_muted');
      if(!videoMuted){
         setTimeout(function() {
            brave_popup_videos[popupID]['video_'+videoType+elmID].muted=false;  brave_popup_videos[popupID]['video_'+videoType+elmID].volume=1; 
            brave_popup_videos[popupID]['video_'+videoType+elmID].play(); 
         }, 100);
      }
   }
   //Youtube and Vimeo Playback
   if(videoType === 'youtube' || videoType === 'vimeo'){
      if(videoType === 'youtube' && brave_popup_videos[popupID]['video_'+videoType+elmID]){   brave_popup_videos[popupID]['video_'+videoType+elmID].playVideo();   }
      if(videoType === 'vimeo' && brave_popup_videos[popupID]['video_'+videoType+elmID]){     brave_popup_videos[popupID]['video_'+videoType+elmID].play().then(); }
   }

   //Track Video Playback
   if(inline && !track){
      var playButton = document.getElementById('brave_play_video-'+elmentID)
      if(playButton){
         var eventCategory = playButton.dataset.trackcategory || '';
         var eventAction = playButton.dataset.trackcategory || '';
         var eventLabel = playButton.dataset.tracklabel || '';
         track = {eventCategory: eventCategory, eventAction:eventAction, eventLabel: eventLabel}
      }
   }
   
   if(track && track.eventCategory && track.eventAction){
      brave_send_ga_event(track.eventCategory, track.eventAction, track.eventLabel || '');
   }
   
}

function brave_complete_goal(popupID, goalType='view', auto=false){
   if(window.location.href.includes('brave_popup') === false && !brave_popup_data[popupID].goaled){ 
      var goalDate = new Date(); var goalYear = goalDate.getFullYear(); var goalMonth = brave_number_padding(goalDate.getMonth() + 1);  var goalDay = brave_number_padding(goalDate.getDate()); 
      var goalData = { 
         popupID: popupID, 
         pageURL: window.location, 
         security: bravepop_global.security, 
         goalType: goalType, 
         //viewTime: brave_popup_data[popupID].opened, 
         views: localStorage.getItem('brave_popup_'+popupID+'_viewed') || 1, 
         goalTime: new Date().getTime(), 
         goalDate: goalYear+'-'+goalMonth+'-'+goalDay,
         goalUTCTime: new Date().toUTCString(), 
         device: brave_currentDevice, 
         auto: auto,
         action: 'bravepop_ajax_popup_complete_goal' 
      };

      brave_ajax_send(bravepop_global.ajaxURL, goalData, function(status, sentData){   brave_popup_data[popupID].goaled = true; console.log('Goal Complete!!!!!!', sentData);  });
      localStorage.setItem('brave_popup_'+popupID+'_goal_complete', goalData.goalTime);
      var braveGoalCompletEvent = new CustomEvent('brave_goal_complete', { detail: {popupId: parseInt(popupID, 10), goalType: goalType} });
      document.dispatchEvent(braveGoalCompletEvent);
      if(brave_popup_data[popupID].settings && brave_popup_data[popupID].settings.notification && brave_popup_data[popupID].settings.notification.analyticsGoal){
         setTimeout(function() {
            //console.log('##### Send Goal Event to GA!');
            brave_send_ga_event('popup', 'goal', brave_popup_data[popupID].title+' ('+popupID+')' || popupID);
         }, 2000);
      }
   }
}

function brave_load_fonts(fontArray){
   var googleFonts  = []; var customFonts = [];
   fontArray.forEach(function(font){ 
      const inCustomFontList = bravepop_global && bravepop_global.customFonts.find((fnt)=> fnt.name === font);
      if(inCustomFontList){ customFonts.push(font); }else{ googleFonts.push(font); }
   })
    if(googleFonts.length > 0 && bravepop_global && bravepop_global.disableGoogleFonts === 'false'){
      WebFontConfig = {
         google: { families: googleFonts }
       };
       (function() {
          if(!document.getElementById('bravePopu_webfontLoader')){
               var wf = document.createElement('script');
               wf.setAttribute("id", "bravePopu_webfontLoader");
               wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +'://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js';
               wf.type = 'text/javascript';
               wf.async = 'true';
               var s = document.getElementsByTagName('script')[0];
               s.parentNode.insertBefore(wf, s);
          }
       })();
    }
   //  console.log(googleFonts, customFonts);
   //Load Custom Fonts
   if(bravepop_global.customFonts.length > 0 && customFonts.length > 0){
      customFonts.map((font)=> {
         var foundFont = bravepop_global.customFonts.find((fnt)=> font === fnt.name  );
         if(foundFont && foundFont.url && foundFont.url !=='UAF' && foundFont.name  && foundFont.name.includes('brave_custom-')){
            var custom_font = new FontFace(foundFont.name, 'url('+foundFont.url+')');
            custom_font.load().then(function(loaded_face) {
               document.fonts.add(loaded_face)
            }).catch(function(error) {
               console.error(error);
            });
         }
      })
   }
	//return fontArray;
};

function brave_is_in_view(elem) {
   var rect = elem.getBoundingClientRect();
   return (rect.bottom >= 0 && rect.right >= 0 && rect.top <= (window.innerHeight || document.documentElement.clientHeight) && rect.left <= (window.innerWidth || document.documentElement.clientWidth));
};


function brave_save_visitor_pageviews(){
   //Save Page View Data
   if(brave_popup_pageInfo.type === 'front' || brave_popup_pageInfo.pageID){
      var brave_page_visited = localStorage.getItem('brave_page_visited'); var currentPageType = 'page';
      if(brave_popup_pageInfo.type === 'front'){ currentPageType = 'front'; } 
      var brave_page_visited_new = {type: brave_popup_pageInfo.type === 'front'? 'front' :(brave_popup_pageInfo.singleType || brave_popup_pageInfo.type), PID: brave_popup_pageInfo.pageID, time: new Date().getTime() };
      if(brave_page_visited){
         var brave_page_visited_newData = JSON.parse(brave_page_visited);
         brave_page_visited_newData.push(brave_page_visited_new);
         localStorage.setItem('brave_page_visited', JSON.stringify(brave_page_visited_newData));
      }else{
         localStorage.setItem('brave_page_visited', JSON.stringify([brave_page_visited_new]));
      }
   }
}

function brave_add_to_cart(elementID){
   brave_tooltip_open(elementID, 'Adding to Cart...', 'top');
   setTimeout(function() {   brave_tooltip_close();  }, 2000);
}
function brave_close_on_add_to_cart(popupID){
   setTimeout(function() { popupID &&  brave_close_popup(popupID);  }, 2000);
}
function brave_update_checkout_on_add_to_cart(){
   setTimeout(function() { if (typeof jQuery == 'function') { jQuery( document.body ).trigger( "update_checkout" ); }  }, 2000);
}
function brave_apply_woo_coupon(coupon, popupID, elementID, onCouponApply){
   if(elementID){ document.querySelector('#brave_button_loading_'+elementID).classList.add('brave_button_loading--show') }
   brave_ajax_send(location.href+'/?wc-ajax=apply_coupon', { coupon_code: coupon, security: bravepop_global.couponSecurity}, function(status, response){
      if(onCouponApply==='reload'){ location.reload(); }
      if(onCouponApply==='close' && popupID){ brave_close_popup(popupID); } 
      if(onCouponApply==='cart' && bravepop_global.cartURL){ location.href = bravepop_global.cartURL}
      if(onCouponApply==='checkout' && bravepop_global.checkoutURL){ location.href = bravepop_global.checkoutURL}
      if(elementID){ document.querySelector('#brave_button_loading_'+elementID).classList.remove('brave_button_loading--show') }
   })
}
function brave_copy_to_clipboard(elementID, tooltipData, position){
   var copyText = document.getElementById('bravepopup_text_copy-'+elementID);
   copyText.select(); copyText.setSelectionRange(0, 99999); document.execCommand("copy");
   brave_tooltip_open(elementID, '✓ '+tooltipData, position);
   setTimeout(function() {   brave_tooltip_close();  }, 2000);
}
function brave_tooltip_open(elementID, tooltipData, position){
   var tooltipDiv = document.getElementById('bravepop_element_tooltip'); var tooltipElm = document.getElementById('brave_element-'+elementID); var elmPos = tooltipElm.getBoundingClientRect();
   if(tooltipDiv){
      tooltipDiv.innerHTML = tooltipData; tooltipDiv.className = 'bravepop_element_tooltip-show bravepop_element_tooltip-'+position; var tooltipWidth = tooltipDiv.offsetWidth;
      tooltipDiv.style.top = (elmPos.top + (elmPos.height/2) - 10)+'px'; tooltipDiv.style.left = (elmPos.left - (tooltipWidth) - 8)+'px';
      if(position === 'right'){ tooltipDiv.style.left = (elmPos.left + elmPos.width + 8)+'px';  }
      if(position === 'top'){ tooltipDiv.style.top = (elmPos.top - (tooltipDiv.offsetHeight) - 4 )+'px'; tooltipDiv.style.left = (elmPos.left - (tooltipWidth/2) + (elmPos.width/2) - 4)+'px';  }
      if(position === 'bottom'){ tooltipDiv.style.top = (elmPos.top + elmPos.height + 8)+'px'; tooltipDiv.style.left = (elmPos.left - (tooltipWidth/2) + (elmPos.width/2) + 8)+'px';  }
   }
}
function brave_tooltip_close(){
   var tooltipDiv = document.getElementById('bravepop_element_tooltip');
   if(tooltipDiv){   tooltipDiv.innerHTML = ''; tooltipDiv.className = '';  tooltipDiv.style.left = ''; tooltipDiv.style.top = ''; }
}

function brave_lightbox_open(elementID, contentType, content){
   var bravelightbox = document.getElementById('bravepop_element_lightbox');  var bravelightboxContent = document.getElementById('bravepop_element_lightbox_content'); 
   if(contentType === 'image' && bravelightboxContent){ bravelightboxContent.innerHTML = '<img src="'+content+'" />';bravelightbox.classList.add('bravepop_element_lightbox--open');}
}
function brave_lightbox_close(){
   var bravelightbox = document.getElementById('bravepop_element_lightbox');  var bravelightboxContent = document.getElementById('bravepop_element_lightbox_content'); 
   if(bravelightbox && bravelightboxContent){ bravelightbox.classList.remove('bravepop_element_lightbox--open'); bravelightboxContent.innerHTML = ''; }
}

function brave_responsiveness(event, popupID, popupData){
   if(window.location.href.includes('braveshot') === true && window.location.href.includes('brave_id') === true){ return; }
   //If a step has no Mobile Content is set, make the Desktop Content mobile friendly and display that instead
   var stepsWithNoMobile = document.querySelectorAll('.brave_popup__step--mobile-noContent');
   if(brave_isTab || (brave_currentDevice === 'mobile' && document.body.clientWidth < 350 )){ stepsWithNoMobile = document.querySelectorAll('.brave_popup__step_wrap'); }
   for (var i = 0; i < stepsWithNoMobile.length; i++) {
      var desktopStep =  stepsWithNoMobile[i].querySelector('.brave_popup__step__desktop');
      var mobileStep =  stepsWithNoMobile[i].querySelector('.brave_popup__step__mobile');
      var popupLayout = desktopStep.dataset.layout;
      var popupPosition = popupLayout === 'landing' ? 'top_center' :desktopStep.dataset.position;
      var popupWidth = parseInt(desktopStep.dataset.width, 10);
      var popupHeight = parseInt(desktopStep.dataset.height, 10);
      var windowWidth = document.body.clientWidth || window.innerWidth;  //window.innerWidth
      var windowHeight = window.innerHeight;
      
      if(brave_currentDevice === 'mobile' || brave_isTab){
         if(((windowWidth < popupWidth) && popupLayout == 'boxed')){
            var scale =  desktopStep ? windowWidth/ desktopStep.dataset.width : 0;
            var tansformOrigin = popupPosition.includes('top') ? 'top' : 'center';
            desktopStep.querySelector('.brave_popup__step__inner').style.transform = 'scale('+((scale*95)/100)+')';
            desktopStep.querySelector('.brave_popup__step__inner').style.transformOrigin = ((scale*95)/2)+'px '+tansformOrigin;
            if(windowWidth < popupWidth && windowHeight < popupHeight){
               desktopStep.querySelector('.brave_popup__step__inner').style.transformOrigin = 'left top';
               desktopStep.querySelector('.brave_popup__step__inner').style.marginTop = 0;
               desktopStep.querySelector('.brave_popup__step__inner').style.top = 0;
            }
            if(popupPosition.includes('center') ){
               var widthRemainder = windowWidth - (popupWidth * (scale*95)/100) ;
               var heightRemainder = windowHeight - (popupHeight * (scale*95)/100) ;
               desktopStep.querySelector('.brave_popup__step__inner').style.left = widthRemainder > 0 ? (widthRemainder/2)+'px' : '0';
               //if(popupPosition === 'center'){ desktopStep.querySelector('.brave_popup__step__inner').style.top = heightRemainder > 0 ? (heightRemainder/2)+'px' : '0'; }
            }
            // if(windowHeight < (popupHeight * (scale*95)/100) ){
            //    console.log('Window Height vs Popup Height', windowHeight < (popupHeight * (scale*95)/100), windowHeight , (popupHeight * (scale*95)/100) );
            // }
         }

         if(brave_currentDevice === 'mobile'&& windowWidth < 321 && ((290 < popupWidth) || windowHeight < popupHeight) && (popupLayout == 'boxed' ) && (popupPosition === 'bottom_right') ){
            desktopStep.querySelector('.brave_popup__step__inner').style.transform = 'scale(0.8)';
            desktopStep.querySelector('.brave_popup__step__inner').style.transformOrigin = '100% bottom';
         }
         //Top/bottom bar on mobile phones, If no Mobile layout exists
         if(brave_currentDevice === 'mobile' && (popupLayout == 'float' ) && windowWidth < 750 ){
            desktopStep.querySelector('.brave_popup__step__elements').style.transform = 'scale('+(((windowWidth / 1024)*100)/100)+')';
            desktopStep.querySelector('.brave_popup__step__elements').style.transformOrigin = (((windowWidth / 1024)*100)/2)+'px center';
         }
         //Top/bottom bar ipad/iphone5 fix
         if(popupLayout == 'float' && ((brave_isTab && windowWidth < 1024 && windowWidth > 760) || (brave_currentDevice === 'mobile' && windowWidth < 360 && mobileStep))){
            if(brave_isTab && windowWidth < 1024 && windowWidth > 760){ popupWidth = 1024; }   var scale =  desktopStep ? windowWidth/ popupWidth : 0;
            if(!brave_isTab && windowWidth < 360 && mobileStep){ popupWidth = 360; scale =  windowWidth / popupWidth; desktopStep = mobileStep; } 
            desktopStep.querySelector('.brave_popup__step__elements').style.transform = 'scale('+((scale*100)/100)+')';
            desktopStep.querySelector('.brave_popup__step__elements').style.transformOrigin = ((scale*100)/2)+'px center';
         }

      }
   }
   var allPopups = document.querySelectorAll('.brave_popup__step');
   for (var i = 0; i < allPopups.length; i++) {
      var aPopupHeight = allPopups[i].dataset.height ? parseInt(allPopups[i].dataset.height, 10) : 400;
      var aPopupPosition = allPopups[i].dataset.position; var aPopupLayout = allPopups[i].dataset.layout;
      if((window.innerHeight < aPopupHeight) && aPopupPosition.includes('center') && aPopupLayout === 'boxed' && !allPopups[i].classList.contains('brave_popup_show_scrollbar')){
         allPopups[i].classList.add('brave_popup_show_scrollbar', 'brave_popup_exceeds_windowHeight');
      }
   }

   //Embedded Popup Resonsiveness
   var allEmbeddedPopups = document.querySelectorAll('.bravepopup_embedded');
   for (var x = 0; x < allEmbeddedPopups.length; x++) {
      var popupID = allEmbeddedPopups[x].dataset.popupid; var parentElm = allEmbeddedPopups[x].parentNode;  var popup_parent_width = parentElm.clientWidth;
      //Resize the embedded popup
      var allEmbeddedSteps = allEmbeddedPopups[x].querySelectorAll('.brave_popup__step_wrap');
      for (var i = 0; i < allEmbeddedSteps.length; i++) {
         var currentDevice = allEmbeddedSteps[i].classList.contains('brave_popup__step--mobile-noContent') ? 'desktop' : brave_currentDevice;
         var selectedStep =  allEmbeddedSteps[i].querySelector('.brave_popup__step__'+currentDevice+'.brave_popup__step--embedded');
         if(selectedStep){
            var stepWidth = parseInt(selectedStep.dataset.width, 10); var stepHeight = parseInt(selectedStep.dataset.height, 10);
            if(popup_parent_width && (popup_parent_width < stepWidth)){
               var scale =  Math.min( popup_parent_width / stepWidth);
               selectedStep.querySelector('.brave_popup__step__inner').style.transform = 'scale('+((scale*98)/100)+')';
               selectedStep.querySelector('.brave_popup__step__inner').style.transformOrigin = 'left top';
               selectedStep.querySelector('.brave_popup__step__inner').parentNode.classList.add('brave_popup__step__inner--scaled');
               selectedStep.style.height = ((stepHeight * (scale*98)/100 ))+'px';
            }
         }
      }
   }

}


//Click Open Popup
function brave_click_open_popups(){
   var braveFoundOpenElems = document.querySelectorAll('a[href*="#brave_open_popup_"]');
   if(braveFoundOpenElems.length > 0){
      for (var i = 0; i < braveFoundOpenElems.length; i++) { 
         var rawOpenElmHref = braveFoundOpenElems[i].href; 
         var braveOpenPopupID = rawOpenElmHref && rawOpenElmHref.split('#brave_open_popup_')[1] ? parseInt(rawOpenElmHref.split('#brave_open_popup_')[1], 10) : false;
         //console.log(braveOpenPopupID);
         if(braveOpenPopupID && brave_popup_data[braveOpenPopupID] && document.getElementById('brave_popup_'+braveOpenPopupID)){
            braveFoundOpenElems[i].addEventListener( 'click', function(event){ event.preventDefault(); if(document.getElementById('brave_popup_'+braveOpenPopupID)) document.getElementById('brave_popup_'+braveOpenPopupID).style.zIndex = 9999999999;  });
            braveFoundOpenElems[i].setAttribute('onclick','brave_load_popup('+braveOpenPopupID+', brave_popup_data['+parseInt(braveOpenPopupID, 10)+'])');
         }
      }
   }
}

//Replace Dynamic Text Cookie value
function brave_replace_dynamic_text_cookie(elmsWithCookie){
   if(elmsWithCookie.length > 0){
      for (var i = 0; i < elmsWithCookie.length; i++) { 
         var finalContent = elmsWithCookie[i].innerHTML;
         var allShortCodes =  finalContent.match(/({{cookie-)+([a-zA-Z0-9_]).+?}}/gi);
         if(allShortCodes && allShortCodes.length > 0){
            allShortCodes.forEach(function(shortcode){
               var theShortcode = shortcode.replace('{{cookie-', '').replace('}}', '');
               var cookieVal = localStorage.getItem(theShortcode);
               finalContent = finalContent.replace(shortcode, cookieVal ? cookieVal : '');
            })
            elmsWithCookie[i].innerHTML = finalContent;
         }
      }
   }
}

function brave_lazyLoad_content_images(emebeddedCampain){
   if(emebeddedCampain && !emebeddedCampain.classList.contains('bravepopup_embedded--lazyload_done') && brave_is_in_view(emebeddedCampain)){
      var allImages = emebeddedCampain.querySelectorAll('img');
      for (var i = 0; i < allImages.length; i++) { 
         if(allImages[i].dataset.lazy){
            allImages[i].src = allImages[i].dataset.lazy;
         }
      }
      emebeddedCampain.classList.add('bravepopup_embedded--lazyload_done');
   }
}

function brave_after_page_load(){
   var allBraveFonts = []
   if(!brave_popup_data){ return; }
   Object.keys(brave_popup_data).forEach(function(popID) {
      brave_popup_data[popID].fonts.forEach(function(fontName) {
         if(!allBraveFonts.includes(fontName)){
               allBraveFonts.push(fontName);
         }
      })
   });
   brave_save_visitor_pageviews();
   brave_load_fonts(allBraveFonts);
   brave_click_open_popups();
   //brave_replace_dynamic_text_cookie();
   var emebddedContent = document.querySelectorAll('.bravepopup_embedded');
   if(emebddedContent && emebddedContent.length > 0){
      for (var i = 0; i < emebddedContent.length; i++) { 
         var emebeddedCampain = emebddedContent[i];
         if(emebeddedCampain){
            brave_lazyLoad_content_images(emebeddedCampain); //Load the images if they are in view on page load.
            document.addEventListener("scroll", function(){ brave_lazyLoad_content_images(emebeddedCampain) }, {passive: true});
         }
      }
   }
}

window.addEventListener( 'DOMContentLoaded', brave_after_page_load );
window.addEventListener( 'resize', brave_responsiveness );
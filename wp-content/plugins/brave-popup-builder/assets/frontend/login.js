function brave_switch_loginForm(elementID, formType){
   console.log(elementID, formType);
   
   var loginFrom = document.getElementById('brave_wpLogin__login_'+elementID);
   var registerFrom = document.getElementById('brave_wpLogin__regsiter_'+elementID);
   var resetFrom = document.getElementById('brave_wpLogin__reset_'+elementID);

   var allLoginForms = document.querySelectorAll('#brave_element-'+elementID+' .brave_wpLogin__formWrap');
   for (var i = 0; i < allLoginForms.length; i++) { allLoginForms[i].classList.remove('brave_wpLogin__formWrap--show');  }

   if(loginFrom && formType ==='login'){ loginFrom.classList.add('brave_wpLogin__formWrap--show'); }
   if(registerFrom && formType ==='register'){ registerFrom.classList.add('brave_wpLogin__formWrap--show'); }
   if(resetFrom && formType ==='resetpass'){ resetFrom.classList.add('brave_wpLogin__formWrap--show'); }

   if(window.brave_apply_google_buttons){
      brave_apply_google_buttons(elementID);
   }
}

function brave_close_loginError(elementID){
   var ErrorDIV = document.getElementById('brave_wpLogin_error_'+elementID);
   var ErrorinnerDIV = document.getElementById('brave_wpLogin_error_content_'+elementID);
   if(ErrorDIV){ ErrorDIV.classList.remove('brave_wpLogin_error--show'); }
   if(ErrorinnerDIV){ ErrorinnerDIV.innerHTML = ''; }
}

function brave_show_loginError(elementID, loginError, success=false){
   console.log(elementID, loginError, success);
   
   if(loginError){
      var ErrorDIV = document.getElementById('brave_wpLogin_error_'+elementID);
      var ErrorinnerDIV = document.getElementById('brave_wpLogin_error_content_'+elementID);
      if(success){  ErrorDIV.classList.add('brave_wpLogin_error--success');  }else{  ErrorDIV.classList.remove('brave_wpLogin_error--success');  }
      if(ErrorDIV){ ErrorDIV.classList.add('brave_wpLogin_error--show'); }
      if(ErrorinnerDIV){ ErrorinnerDIV.innerHTML = loginError; }
   }
}

function brave_loginUser(event, elementID, popupID){
   event.preventDefault();
   var userEmail = document.getElementById('brave_login_email_'+elementID).value;
   var userPassword = document.getElementById('brave_login_pass_'+elementID).value;
   var ajaxurl = document.getElementById('brave_login_ajaxURL_'+elementID).value;
   var security = document.getElementById('brave_login_security'+elementID).value;
   var redirectURL = document.getElementById('brave_login_redirect_'+elementID).value ? document.getElementById('brave_login_redirect_'+elementID).value :  window.location.href;
   var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
   var isLogginWithEmail = document.getElementById('brave_login_email_'+elementID) && document.getElementById('brave_login_email_'+elementID).getAttribute('type') === 'email';
   
   if(!userEmail){ return brave_show_loginError(elementID, bravepop_global.email_required);  }
   if(userEmail && isLogginWithEmail && !userEmail.match(mailformat)){ return brave_show_loginError(elementID, bravepop_global.email_invalid);   }
   if(!userPassword){ return brave_show_loginError(elementID, bravepop_global.pass_required);  }
   if(userPassword && userPassword.length < 5){ return brave_show_loginError(elementID, bravepop_global.pass_short);   }
   if(!ajaxurl || !security){ return brave_show_loginError(elementID, bravepop_global.login_error);  }

   //SEND Data
   var logindata = { email: userEmail, password: userPassword, security: security, redirect: redirectURL, action: 'bravepop_ajax_login' };
   var brave_login_loader = document.getElementById('brave_login_loading_'+elementID);
   if(brave_login_loader){  brave_login_loader.classList.add('brave_login_loading--show'); }
   brave_ajax_send(ajaxurl, logindata, function(status, sentData){
      console.log(status, JSON.parse(sentData));
      var sentData = JSON.parse(sentData);
      if(brave_login_loader){  brave_login_loader.classList.remove('brave_login_loading--show'); }
      if(sentData.loggedin === false){
         return brave_show_loginError(elementID, sentData.message); 
      }else{
         //REDIRECT USER
         window.location.href = sentData.redirect;
      }
   });
}


function brave_signupUser(event, elementID, popupID, stepIndex){
   event.preventDefault();
   var registerFrom = document.getElementById('brave_wpLogin__regsiter_'+elementID);
   var userFirstname = document.getElementById('brave_signup_fname_'+elementID).value;
   var userLastname = document.getElementById('brave_signup_lname_'+elementID).value;
   var username = document.getElementById('brave_signup_username_'+elementID) && document.getElementById('brave_signup_username_'+elementID).value;
   var userEmail = document.getElementById('brave_signup_email_'+elementID).value;
   var userPassword = document.getElementById('brave_signup_pass_'+elementID) ? document.getElementById('brave_signup_pass_'+elementID).value : false;
   var passwordDisabled = document.getElementById('brave_signup_pass_type_'+elementID) && document.getElementById('brave_signup_pass_type_'+elementID).value === 'auto' ? true : false;
   var ajaxurl = document.getElementById('brave_signup_ajaxURL_'+elementID).value;
   var security = document.getElementById('brave_signup_security'+elementID).value;
   var redirectURL = document.getElementById('brave_signup_redirect_'+elementID).value;
   var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
   //Verification
   if(!userFirstname){ return brave_show_loginError(elementID, bravepop_global.fname_required);  }
   if(!userLastname){ return brave_show_loginError(elementID, bravepop_global.lname_required);   }
   if(document.getElementById('brave_signup_username_'+elementID) && !username){ return brave_show_loginError(elementID, bravepop_global.username_required);   }
   if(!userEmail){ return brave_show_loginError(elementID, bravepop_global.email_required);  }
   if(userEmail && !userEmail.match(mailformat)){ return brave_show_loginError(elementID, bravepop_global.email_invalid);   }
   if(!passwordDisabled && !userPassword){ return brave_show_loginError(elementID, bravepop_global.pass_required);   }
   if(!passwordDisabled && userPassword && userPassword.length < 5){ return brave_show_loginError(elementID, bravepop_global.pass_short);   }
   if(!ajaxurl || !security){ return brave_show_loginError(elementID, bravepop_global.login_error);  }


   var newUserData = { username: username || false, email: userEmail, signupsecurity: security, firstname: userFirstname, lastname: userLastname, elementID: elementID, popupID: popupID, stepIndex: stepIndex, redirect: redirectURL, action: 'bravepop_ajax_signup' };
   if(!passwordDisabled && userPassword){
      newUserData.password = userPassword;
   }
   if(passwordDisabled){
      newUserData.autoPass = true;
   }

   var isSignupGoal = brave_popup_data[popupID] && brave_popup_data[popupID].settings.goalAction && brave_popup_data[popupID].settings.goalAction.elementIDs && brave_popup_data[popupID].settings.goalAction.elementIDs[brave_currentDevice] && brave_popup_data[popupID].settings.goalAction.elementIDs[brave_currentDevice].includes(elementID);
   //Goal Data
   if(isSignupGoal){
      newUserData.goalData = JSON.stringify({ 
         popupID: popupID, 
         pageURL: window.location, 
         goalSecurity: bravepop_global.goalSecurity, 
         goalType: 'form', 
         viewTime: brave_popup_data[popupID].opened, 
         goalTime: new Date().getTime(), 
         goalUTCTime: new Date().toUTCString(), 
         device: brave_currentDevice, 
         action: 'bravepop_ajax_popup_complete_goal' 
      });
   }
   
   var brave_login_loader = document.getElementById('brave_signup_loading_'+elementID);
   if(brave_login_loader){  brave_login_loader.classList.add('brave_login_loading--show'); }
   brave_ajax_send(ajaxurl, newUserData, function(status, sentData){
      console.log(status, sentData, JSON.parse(sentData), brave_popup_data[popupID].settings);
      var sentData = JSON.parse(sentData);
      if(brave_login_loader){  brave_login_loader.classList.remove('brave_login_loading--show'); }
      if(sentData.created === false){
         return brave_show_loginError(elementID, sentData.message); 
      }else{
         //Complete Goal
         if(isSignupGoal){
            if(window.location.href.includes('brave_popup') === false ){ 
               localStorage.setItem('brave_popup_'+popupID+'_goal_complete', true);
               if(brave_popup_data[popupID].settings && brave_popup_data[popupID].settings.notification && brave_popup_data[popupID].settings.notification.analyticsGoal){
                  setTimeout(() => {
                     console.log('##### Send Goal Event to GA!');
                     brave_send_ga_event('popup', 'goal', brave_popup_data[popupID].title+' ('+popupID+')' || popupID);
                  }, 2000);
               }
            }
         }
         //REDIRECT USER
         if(passwordDisabled){
            registerFrom.querySelector('form').style.visibility = 'hidden';
            registerFrom.querySelector('.brave_wpLogin__registerForm__successMessage').classList.add('brave_wpLogin__registerForm__successMessage--active');
         }else{
            window.location.href = sentData.redirect;
         }

      }
   });
}


function brave_resetPass(event, elementID){
   event.preventDefault();
   var userEmail = document.getElementById('brave_resetpass_email_'+elementID).value;
   var ajaxurl = document.getElementById('brave_resetpass_ajaxURL_'+elementID).value;
   var security = document.getElementById('brave_resetpass_security'+elementID).value;
   var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
   //Verification
   if(!userEmail){ return brave_show_loginError(elementID, bravepop_global.email_required);  }
   if(userEmail && !userEmail.match(mailformat)){ return brave_show_loginError(elementID, bravepop_global.email_invalid);   }
   if(!ajaxurl || !security){ return brave_show_loginError(elementID, bravepop_global.login_error);  }

   var resetpassData = { email: userEmail, security: security, action: 'bravepop_ajax_resetpass' };

   //Send Data
   var brave_login_loader = document.getElementById('brave_resetpass_loading_'+elementID);
   if(brave_login_loader){  brave_login_loader.classList.add('brave_login_loading--show'); }
   brave_ajax_send(ajaxurl, resetpassData, function(status, sentData){
      console.log(status, JSON.parse(sentData));
      var sentData = JSON.parse(sentData);
      if(brave_login_loader){  brave_login_loader.classList.remove('brave_login_loading--show'); }
      if(sentData.sent){
         return brave_show_loginError(elementID, bravepop_global.pass_reset_success, true); 
      }else{
         return brave_show_loginError(elementID, sentData.message); 
      }
   });
}

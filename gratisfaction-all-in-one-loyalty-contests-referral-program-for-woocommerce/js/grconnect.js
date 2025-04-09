function validateEmail($email) {
    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
    return emailReg.test( $email );
}

function callRegister(){

    // Validate the form
    jQuery.validity.start();
    jQuery('#grconnect_reg_email_user').require('Please add email');
    jQuery('#grconnect_reg_email_user').match('email','Please add valid email');
    jQuery('#grconnect_reg_firstname').require('Please add first name');
    jQuery('#grconnect_reg_lastname').require('Please add last name');
    var result = jQuery.validity.end();

    if(result.valid)
    {
        jQuery('#createButton').val('Saving Settings..');
        jQuery('#createButton').attr('disabled','disabled');

        var $modal = jQuery('.js-loading-bar');
        $modal.show();

        jQuery.post(
            ajaxurl,
            jQuery('#registerForm').serialize()+'&raffd='+jQuery('#raffd').val(),
            function(response){

                if(response.gr_reg == 0)
                {
                    setTimeout(function(){
                        jQuery('#settingBlock').show();
                        jQuery('#registerBlock, .grBlkNonFrame').hide();
                        $modal.hide();
                    }, 1500);
                }
                else if(response.gr_reg == 2)
                {
                    jQuery('.error_msg').html(response.message);
                    jQuery('#registerBlock, #loaderBlock').hide();
                    jQuery('.alertBox').show();
                    jQuery('#loginBlock').show();
                    $modal.hide();
                }
                else
                {
                    if(typeof response.message !== "undefined") {
                        jQuery('.error_msg').html(response.message);
                        jQuery('.alertBox').show();
                    }

                    jQuery('#createButton').removeAttr('disabled');
                    jQuery('#createButton').val('Next');
                    $modal.hide();
                }
            },'json'
        );
    }
    else
    {
        //alert('Please clear errors while input.');
        return false;
    }
}

function callVerify(){

    // Validate the form
    jQuery.validity.start();
    jQuery('#admin_email').require('Please add email');
    jQuery('#admin_email').match('email','Please add proper format email');
    var result = jQuery.validity.end();

    if(result.valid)
    {
        jQuery('#verifyButton').val('Updating Settings..');
        jQuery('#verifyButton').attr('disabled','disabled');

        var $modal = jQuery('.js-loading-bar3');
        $modal.show();
        jQuery('.modal-backdrop').appendTo('#loaderBlock');

        jQuery.post(
            ajaxurl,
            jQuery('#verifyForm').serialize()+'&raffd='+jQuery('#raffd').val(),
            function(response){

                if(response.gr_reg == 1)
                {
                    jQuery('.error_msg').html(response.msg);
                    jQuery('.alertBox').show();
                    jQuery('#verifyForm').show();
                    jQuery('#verifyButton').val('Update');
                    jQuery('#verifyButton').removeAttr('disabled');
                    $modal.hide();
                }
                else if(response.gr_reg == 0)
                {
                    setTimeout(function(){
                        jQuery('#settingBlock').show();
                        jQuery('#loaderBlock, .grBlkNonFrame').hide();
                        $modal.hide();
                    },500);
                }
                else
                {
                    jQuery('#registerBlock').show();
                    jQuery('#loaderBlock').hide();
                    $modal.hide();
                }
            },'json'
        );
    }
}

function callLoader(){

    var $modal = jQuery('.js-loading-bar3');
    $modal.show();

    jQuery.post(
        ajaxurl,
        {action:'check_settings',raffd: jQuery('#raffd').val(), security: jQuery('#security').val()},
        function(response){

            if(response.gr_reg == 0){
                setTimeout(function(){
                    jQuery('#settingBlock').show();
                    jQuery('#loaderBlock, .grBlkNonFrame').hide();
                    $modal.hide();
                },500);
            }
            else if(response.gr_reg == 2 || response.gr_reg == 3)
            {
                if(typeof response.message !== "undefined") {
                    jQuery('.error_msg').html(response.message);
                    jQuery('.alertBox').show();
                }
                jQuery('#loginBlock').show();
                jQuery('#loaderBlock').hide();
                $modal.hide();
            }
            else
            {
                if(typeof response.message !== "undefined") {
                    jQuery('.error_msg').html(response.message);
                    jQuery('.alertBox').show();
                }
                jQuery('#registerBlock').show();
                jQuery('#loaderBlock').hide();
                $modal.hide();
            }

        },'json'
    );
}

function callLogin() {

    // Validate login form
    jQuery.validity.start();
    jQuery('#grconnect_login_email').require('Please add email');
    jQuery('#grconnect_login_email').match('email','Please add valid email');
    jQuery('#grconnect_login_pwd').require('Please add password');
    var result = jQuery.validity.end();

    if(result.valid)
    {
        jQuery('#loginButton').val('Checking Login..');
        jQuery('#loginButton').attr('disabled','disabled');

        var $modal = jQuery('.js-loading-bar');
        $modal.show();
        jQuery('.modal-backdrop').appendTo('#loginBlock');

        jQuery.post(
            ajaxurl,
            jQuery('#loginForm').serialize()+'&raffd='+jQuery('#raffd').val(),
            function(response){
                if(response.error == 0)
                {
                    setTimeout(function(){
                        jQuery('#settingBlock').show();
                        jQuery('#loginBlock, .grBlkNonFrame').hide();
                        $modal.hide();
                    },1500);
                }
                else
                {
                    if(typeof response.message !== "undefined") {
                        jQuery('.error_msg').html(response.message);
                        jQuery('.alertBox').show();
                    }

                    jQuery('#loginButton').removeAttr('disabled');
                    jQuery('#loginButton').val('Login');
                    $modal.hide();
                }
            },'json'
        );

    }else{
        //alert('Please clear errors while input.');
        return false;
    }
}

function isSafari() {
    return /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
}

/**
 * Handles the auto-login process to Gratisfaction admin panel
 *
 * Makes an AJAX request to the server, processes the response,
 * and either redirects to Gratisfaction or displays any errors.
 * Special handling is included for Safari browsers.
 *
 * @return {void}
 */
function callAutoLogin() {
    // Cache jQuery selectors for better performance
    const $launchButton = jQuery('#gr_launch_button');
    const $launchLink = jQuery('#gr_launch_link');
    const $errorDisplay = jQuery('#autologinErrorDisplay');

    // Hide any previous error messages
    $errorDisplay.hide();

    // Update UI to show loading state
    $launchButton.html('Loading...');
    $launchLink.prop('disabled', true);

    // Variable to hold Safari specific window
    let newWindow = null;
    try {
        // Special handling for Safari browsers
        if (isSafari()) {
            newWindow = window.open('about:blank', '_blank');
            if (!newWindow) {
                throw new Error('Popup blocker detected. Please allow popups for this site.');
            }
        }

        // Make AJAX request to server
        jQuery.post(
            ajaxurl,
            jQuery('#autoLoginForm').serialize(),
            function (response) {
                if (response.error == 0 && response.frame_url != "") {
                    $launchLink.removeAttr('onclick')
                        .attr('href', response.frame_url)
                        .attr('target', '_blank');

                    // Handle browser-specific redirection
                    if (isSafari() && newWindow) {
                        newWindow.location.href = response.frame_url;
                    } else {
                        // Trigger click programmatically
                        $launchLink[0].click();
                    }
                } else {
                    const errorMessage = response.message || 'Unknown error occurred';
                    jQuery('.error_msg').html(errorMessage);
                    $errorDisplay.show();
                }
                $launchButton.html('Go to Gratisfaction admin');
                $launchLink.prop('disabled', false);
                setTimeout(function () {
                    $launchLink.attr('onclick', 'callAutoLogin()');
                    $launchLink.removeAttr('href').removeAttr('target');
                }, 2000); // 2 seconds delay
            }, 'json'
        );
    } catch (error) {
        // Handle any runtime errors
        jQuery('.error_msg').html(error.message || 'Unknown error occurred');
        $errorDisplay.show();

        // Reset UI state
        $launchButton.html('Go to Gratisfaction admin');
        $launchLink.prop('disabled', false);
        setTimeout(function () {
            $launchLink.attr('onclick', 'callAutoLogin()');
            $launchLink.removeAttr('href').removeAttr('target');
        }, 2000); // 2 seconds delay
    }
}

jQuery(document).ready(function(){

    if(jQuery('#grRegisterAr').val()	== 2){
        callLoader();
    }

    // Added for success tick
    jQuery('.inputBox input').on('input', function(){
        var re = /\S+@\S+\.\S+/;
        if(jQuery(this).data('type') == 'email') {
            if( jQuery(this).val() != '' && re.test( jQuery(this).val() ) ) {
                jQuery(this).parent().addClass('success').removeClass('errorBox');
                if(jQuery(this).parent().find('.error').length > 0) {
                    jQuery(this).parent().find('.error').remove();
                }
            }
            else {
                jQuery(this).parent().removeClass('success').addClass('errorBox');
            }
        }
        else if( jQuery(this).val() != '') {
            jQuery(this).parent().addClass('success').removeClass('errorBox');
            if(jQuery(this).parent().find('.error').length > 0) {
                jQuery(this).parent().find('.error').remove();
            }
        }
        else {
            jQuery(this).parent().removeClass('success').addClass('errorBox');
        }
    });
});

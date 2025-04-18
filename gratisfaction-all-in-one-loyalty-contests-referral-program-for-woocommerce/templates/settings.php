<?php
if (!current_user_can('manage_options'))
    wp_die(__('You do not have sufficient permissions to access this page.'));

    $grRegisterAr          = get_option('grconnect_register', 0);
    $displayRegisterBlock  = ($grRegisterAr == 0) ? "block" : "none";
    $displaySettingBlock   = ($grRegisterAr == 1) ? "block" : "none";
    $displayLoaderBlock    = ($grRegisterAr == 2) ? "block" : "none";
    $displayLoginBlock     = ($grRegisterAr == 3) ? "block" : "none";
    $gr_nonce = wp_create_nonce('gr_nonce');
?>
<div class="wrap">
<h1></h1> <!-- Don't delete. This is for WP message push. -->
<input type="hidden" id="grRegisterAr" value="<?php echo $grRegisterAr?>" />

    <div class="grWrap">
        <div class="grContent">

            <div class="grHead">
                <h1><b>Gratis</b>faction</h1>
            </div>

            <div class="ConnectBlock grBlkNonFrame" id="loaderBlock" style="min-height:220px;display:<?php echo $displayLoaderBlock?>;">
                <form class="formGr form-horizontal" method="post" action="#" id="verifyForm" style="display:none">
                    <input type="hidden" name="action" value="check_settings" />
                    <input type="hidden" id="security" name="security" value="<?php echo $gr_nonce; ?>" />
                    <p class="subtitle">Verify your Email</p>
                    <div class="inputBox">
                        <input type="text" data-type="email" name="admin_email" id="admin_email" placeholder="Email" value="<?php echo get_option('grconnect_admin_email'); ?>"
                            maxlength="250" title="Email" class="form-control" />
                        <u></u><i></i>
                    </div>
                    <div class="form-group">
                        <input type="button" id="verifyButton" value="Update" onclick="callVerify();" class="btn btn-success btn-lg" />
                    </div>
                    <div class="alertBox" style="display:none;"><div><i></i> <span class="error_msg"></span></div></div>
                </form>

                <div class="modal js-loading-bar3" style="background:rgba(51, 51, 51, 0.7);display:none">
                    <div class="modal-dialog" style="position: absolute; top: 0; left: 0;right: 0;bottom:0;" >
                        <div class="modal-content">
                            <div class="modal-body" style="text-align:center; padding: 20px;">
                                <img src="<?php echo plugin_dir_url( __FILE__ )?>../img/loader.gif" style='margin: 20px 0 10px;'>
                                <!--<div style=" margin: 20px;">Saving...</div>-->
                                <p style="margin: 10px 0; font-size: 16px;">Verifying Your Account. This may take a few seconds. Please do not refresh or close the browser. Thanks......</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form for registeration Starts here -->
            <div class="ConnectBlock grBlkNonFrame" id="registerBlock" style="display:<?php echo $displayRegisterBlock?>;">
                <form class="formGr form-horizontal" method="post" action="#" id="registerForm">
                    <input type="hidden" name="action" value="create_account" />
                    <input type="hidden" name="security" value="<?php echo $gr_nonce; ?>" />
                    <?php $current_user = wp_get_current_user();?>

                    <p class="subtitle">Get started with <mark><b>free full-featured</b> plan</mark> No Obligations. No Commitment.</p>

                    <div class="inputBox">
                        <input type="text" name="grconnect_reg_firstname" title="First Name" placeholder="First Name" maxlength="100" id="grconnect_reg_firstname"
                            value="<?php echo $current_user->user_firstname; ?>" class="form-control" />
                        <u></u><i></i>
                    </div>
                    <div class="inputBox">
                        <input type="text" name="grconnect_reg_lastname" title="Last Name" placeholder="Last Name" maxlength="100" id="grconnect_reg_lastname"
                            value="<?php echo $current_user->user_lastname; ?>" class="form-control" />
                        <u></u><i></i>
                    </div>
                    <div class="inputBox">
                        <input type="text" data-type="email" name="grconnect_reg_email_user" title="Email" placeholder="Email" id="grconnect_reg_email_user"
                            value="<?php echo get_option('grconnect_admin_email'); ?>" maxlength="250" class="form-control /">
                        <u></u><i></i>
                    </div>

                    <div class="checkBox"><span>By clicking 'NEXT' you agree to our <a href='https://appsmav.com/terms.php' target="_blank">Terms &amp; Conditions</a> and <a href='https://appsmav.com/privacy.php' target="_blank">Privacy Policy</a></span></div>
                    <div class="form-group">
                        <input type="button" id="createButton" value="Next" onclick="callRegister();" class="btn btn-success btn-lg" />
                        <input type="hidden" name="grconnect_reg_email" id="grconnect_reg_email" value="<?php echo get_option('grconnect_admin_email'); ?>" maxlength="250" class="form-control /">
                    </div>

                    <div class="alertBox" style="display:none;"><div><i></i> <span class="error_msg"></span></div></div>
                </form>

                <div class="modal js-loading-bar" style="background:rgba(51, 51, 51, 0.7); display:none;">
                    <div class="modal-dialog" style="position: absolute; bottom: 0; top: 0; left: 0;right: 0;" >
                        <div class="modal-content">
                            <div class="modal-body" style="text-align:center; padding: 50px;">
                                <img src="<?php echo plugin_dir_url( __FILE__ )?>../img/loader.gif" style='margin: 20px 0 10px;'>
                                <!--<div style=" margin: 20px;">Saving...</div>-->
                                <p style="margin: 10px 0; font-size: 16px;">Creating your account. This may take a few seconds. Please do not refresh or close the browser. Thanks......</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Form for login Starts here -->
            <div class="ConnectBlock grBlkNonFrame" id="loginBlock" style="display:<?php echo $displayLoginBlock?>;">
                <form class="formGr form-horizontal" method="post" action="#" id="loginForm">
                    <input type="hidden" name="action" value="check_login" />
                    <input type="hidden" name="security" value="<?php echo $gr_nonce; ?>" />
                    <p class="subtitle">Login with your Email and Password</p>
                    <div class="inputBox">
                        <input type="text" data-type="email" title="Email" name="grconnect_login_email" id="grconnect_login_email" value="" placeholder="Email"
                            maxlength="250" class="form-control" />
                        <u></u><i></i>
                    </div>
                    <div class="inputBox">
                        <input type="password" title="Password" name="grconnect_login_pwd" id="grconnect_login_pwd" value="" placeholder="Password"
                            maxlength="250" class="form-control" />
                        <u></u><i></i>
                    </div>
                    <div class="form-group">
                        <input type="button" id="loginButton" value="Login" onclick="callLogin();" class="btn btn-success btn-lg" />
                    </div>
                    <div class="checkBox">
                        <a href="https://appsmav.com/customer/pwreset.php" target="_blank"> Forgot password?</a>
                    </div>
                    <div class="alertBox" style="display:none;"><div><i></i> <span class="error_msg"></span></div></div>
                </form>
            </div>
            <!-- Form for login ends here -->


            <section class="gr_400_section grBlkNonFrame" style="display:none">
                <img src="<?php echo plugin_dir_url( __FILE__ )?>../img/img-400-error.jpg" alt="Error 400">
                <h2><b>Error!</b><br/> Invalid Shop!</h2>
            </section>

            <!-- After Login Block -->
            <div class="ConnectBlock text-center" id="settingBlock" style="display:<?php echo $displaySettingBlock?>;">
                <figure>
                    <img src="<?php echo plugin_dir_url( __FILE__ )?>../img/gratisfaction.jpg" alt="Gratisfaction" />
                </figure>
                <form class="formGr form-horizontal" method="post" action="#" id="autoLoginForm">
                    <input type="hidden" name="action" value="check_autologin" />
                    <a onclick="callAutoLogin();" class="btn btn-success btn-lg" id="gr_launch_link">
                        <span id="gr_launch_button">Go to Gratisfaction admin</span>
                        <img src="<?php echo plugin_dir_url(__FILE__) ?>../img/icon-link.png" height="16" alt="Gratisfaction" />
                    </a>
                    <div id="autologinErrorDisplay" style="display:none;"><br>
                        <div class="alertBox">
                            <div><i></i> <span class="error_msg"></span></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <p class="helpText">If you face any problem installing this app, simply email to <a href="mailto:sales@appsmav.com">sales@appsmav.com</a>.<br>
         Our team will work with you to correctly install &amp; configure Gratisfaction.</p>
    </div>
    <input type="hidden" name="raffd" id="raffd" value="" />
    <script src="//cdn.appsmav.com/am/lib/js/chat.js"></script>
</div>

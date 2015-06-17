<style>
.login_box_tab{height: 30px; overflow: hidden; position: relative; z-index:10;}
.login_box_tab li{line-height: 30px; border: 1px #ddd solid;  height: 28px; background-color: #F6F6F6; padding:0 15px; margin-left: 10px; cursor: pointer;}
.login_box_tab a,.fb_login_1:hover{text-decoration: none}
.login_box_tab li.hover{height: 30px;color:#E99C00; background-color: #fff}
.login_box_content{border-top: 1px #ddd solid; position: relative; margin-top: -1px; z-index:9;}
.input_lable{line-height: 25px; color: #999}
.reg_form .input_lable{line-height: 20px;}
.input_text{line-height: 26px\9; height: 26px; width: 238px; padding-left: 4px;}
.rand_code{width: 100px;}
.overflow{overflow: hidden;}
.reg_form .randcode_img{width: 100px; height: 26px; margin: 0 5px 0 10px; border: #ddd solid 1px;}
.reg_form .randcode_reload{height: 26px; cursor: pointer; background-position: -120px -197px;}
.login_form,.reg_form{display: block; margin: 0 auto; width: 246px; }
.login_box_content .submit_btn{width: 246px; text-align: center; line-height: 26px; padding-bottom: 3px;}
.checkbox_text{padding-left:18px;}
.checkbox{position: absolute;left:0;top:10px; *left:-4px; *top:6px;}
.login_box_loading{margin:-26px 0 0 -35px; display: block}
.login_box_bottom{border-top: 1px #ddd solid; text-align: left; padding-top: 15px;}
.login_box_facebook{margin: 0 auto; display: block; width: 360px; padding-left: 20px}
.login_error_msg,.reg_error_msg{
	height: 40px;padding-top: 5px;
    color: #FF0000; padding-left: 17px;
}
.icon_error_msg{background: url("../images/img/validation_advice_bg.gif") no-repeat left 7px;}
.login_ru .login_box_tab li{padding:0 5px;}

</style>

<script type="text/html" id="loginBoxTpl">
	<div class="login_<?php echo $language_code?>">
		<div class="login_box_tab">
			<ul class="clearfix">
				<li class="hover fl" onclick="ec.login.tab(this, 'login_form')"><a href="javascript:;"><?php echo lang('popbox_registered') ?></a></li>
				<li class="fl" onclick="ec.login.tab(this, 'reg_form')"><a href="javascript:;"><?php echo lang('popbox_new_customers') ?></a></li>
			</ul>
		</div>

		<div class="login_box_content">
			<div id="login_form" class="login_form">
				<form id="popup_login_frm" name="formLogin" action="#" method="post" onsubmit="return ec.login.boxLogin(this);">
					<div class="pt10">
						<p class="input_lable"><?php echo lang('popbox_email_or_nickname') ?></p>
						<input type="text" name="user_name" class="input_text" />
					</div>
					<div class="pt10">
						<p class="input_lable"><?php echo lang('popbox_password') ?></p>
						<input type="password" name="password" class="input_text" />
					</div>

					<div class="pt10 relative">
						<span class="checkbox"><input type="checkbox" name="remember" /></span>
						<p class="checkbox_text">
							<span><?php echo lang('popbox_auto_login') ?></span>
							<span><a href="<?php echo eb_gen_url('forgot_password') ?>" class="blue"><?php echo lang('popbox_forgot_password') ?></a></span>
						</p>
					</div>

					<div class="pt15">
						<button id="loginSubmitBtn" class="btn btn_30 btn_og shadow submit_btn" value="" type="submit"><?php echo lang('popbox_sign_in') ?></button>
						<span id="popLoginLoading" class="login_box_loading" style="display: none"><img alt="" src="/images/common/other/loading/25_25.gif"/></span>
					</div>
				</form>
				<div id="loginSubmitError" class="login_error_msg"></div>
			</div>

			<div id="reg_form" class="reg_form" style="display: none;">
				<form id="popup_register_frm" name="formLogin" action="#" method="post" onsubmit="return ec.login.boxReg(this);">

					<div class="pt10">
						<p class="input_lable"><?php echo lang('l_register_nickname') ?></p>
						<input type="text" id="regNickname" class="input_text" name="user_name" />
					</div>

					<div class="pt10">
						<p class="input_lable"><?php echo lang('l_register_email') ?></p>
						<input type="text" id="regEmail" class="input_text" name="email" />
					</div>
					<div class="pt10">
						<p class="input_lable"><?php echo lang('l_register_psw') ?></p>
						<input type="Password" id="regPassword" class="input_text" name="password" />
					</div>
					<div class="pt10">
						<p class="input_lable"><?php echo lang('l_register_confim') ?></p>
						<input type="Password" id="regPasswordConfirm" class="input_text" name="confirm_password" />
					</div>
					
					<?php if($showCaptchaFlag){?>
					<?php //if(true){?>
					<div class="pt10 overflow" id="randCode">
						<p class="input_lable"><?php echo lang('l_verification_code') ?>:</p>
						<input type="text" class="input_text rand_code fl" name="rand_code" maxlength="4"/>
						<img class="randcode_img fl" src="/captcha/index">
						<span class="randcode_reload fl"></span>
					</div>
					<?php } ?>
					
					<div>
						<div class="pt10 relative">
							<span class="checkbox"><input id="registerAgree" type="checkbox" checked="checked" onclick="return false;" name="agreement" /></span>
							<p class="checkbox_text">
								<span><?php echo lang('l_register_agree') ?></span><br />
								<a class="blue" target="_blank" href="<?php echo eb_gen_url('terms_and_conditions.html')?>"><?php echo lang('popbox_terms_and_conditions') ?></a>.
							</p>
						</div>
						<div class="pt10 relative">
							<span class="checkbox"><input id="subscribe" name="subscribe" type="checkbox" value="1" checked="checked"></span>
							<p class="checkbox_text"><?php echo lang('l_register_agree_sub') ?></p>
						</div>
					</div>
					<div class="pt15">
						<button class="btn btn_30 btn_og shadow submit_btn" id="regSubmitBtn" onclick="dataLayer.push({'buttonaction': 'register_popup','event': 'buttonclick'});" value="" type="submit"><?php echo lang('sign_up') ?></button>
						<span id="popRegLoading" class="login_box_loading" style="display: none"><img alt="" src="/images/common/other/loading/25_25.gif"/></span>
					</div>
					<input type="hidden" name="reg_from" value="cart" />
				</form>
				<div id="regSubmitError" class="reg_error_msg"></div>
			</div>
		</div>

		<div class="login_box_bottom">
			<div class="login_box_facebook">
				<a class="fb_login_1" href="javascript:;" onclick="ec.fbLogin(ec.ajaxLoginCallback);">
					<i class="icon_fb"></i>
					<span><?php echo lang('popbox_login')?></span>
				</a>
				<span class="pl10 facebook_text"><?php echo lang('facebook_login_text') ?></span>
			</div>
		</div>
	</div>
</script>
<script>
	ec.login.boxTitle = "<?php echo lang('popbox_login_or_create_account') ?>";
	ec.login.tab = function(ele, tagId) {
		if($(ele).hasClass('hover')) return;
		var $box = $('#olBox');
		var $boxContent = $('.box_content', $box);
		var hasCaptcha = $('#randCode').length > 0;
		var h = (tagId == 'login_form') ? 330 : hasCaptcha ? 550 : 490;
		if( hasCaptcha ){
			var top = (tagId == 'login_form') ? "+=110px" : "-=110px";	// 550-330
		}else{
			var top = (tagId == 'login_form') ? "+=80px" : "-=80px";	// 490-330
		}
		


		$(ele).addClass('hover').siblings().removeClass('hover');
		$('#'+ tagId).show().siblings().hide();
		$boxContent.css({height : h});
		$box.stop().animate({
			top : top
		}, 200);
	};
	ec.login.boxLogin = function (form) {
		//限时3秒响应一次，防止重复点击
        if(!ec.ui.eventTimeLimits('#loginSubmitBtn')) return false;

		var $thisForm = $(form);
		var userName = $thisForm.find('input[name=user_name]');
		var pwd = $thisForm.find('input[name=password]');
		var isReload = ec.login.afterReload_box;
		if(!userName.val().trim() && !pwd.val().trim()) return false;

		var $loading = $("#popLoginLoading").show();
		var $errorMSg = $('#loginSubmitError').html('').removeClass('icon_error_msg');
		var $loginBtn = $("#loginSubmitBtn").attr('disabled', true);
		new ec.ajax().post({
			url : "/login/authenticateApi",
			form : form,
			timeoutFunction : function () {
	        	log('ajaxLogin timeout');
	        },
			successFunction : function (json) {
				if(json.errorCode !== 0) {
					$errorMSg.html(json.msg).addClass('icon_error_msg');
                    $loginBtn.attr('disabled', false);
                    $loading.hide();
					return;
				}

				if(isReload) {
					location.reload();
				} else {
					ec.login.setLoginInfoAndShow(json.data);
					ec.login.box.close();
					if(ec.login.callback && typeof(ec.login.callback) == 'function') ec.login.callback(json.data);
				}
			}
		});
		return false;
	};
	ec.login.boxReg = function (form) {
		//限时3秒响应一次，防止重复点击
        if(!ec.ui.eventTimeLimits('#regSubmitBtn')) return false;

		var $thisForm = $(form);
		var userName = $thisForm.find('input[name=user_name]');
		var email = $thisForm.find('input[name=email]');
		var pwd = $thisForm.find('input[name=password]');
		var pwd2 = $thisForm.find('input[name=confirm_password]');
		if(!userName.val().trim() && !email.val().trim() && !pwd.val().trim() && !pwd2.val().trim()) return false;

		var $loading = $("#popRegLoading").show();
		var $errorMSg = $('#regSubmitError').html('').removeClass('icon_error_msg');
		var $regBtn = $("#regSubmitBtn").attr('disabled', true);
		var isReload = ec.login.afterReload_box;
		new ec.ajax().post({
			url : "/login/registerApi",
			form : form,
			timeoutFunction : function () {
	        	log('ajaxLogin timeout');
	        },
			successFunction : function (json) {
				if (json.errorCode != 0) {
					$errorMSg.html(json.msg).addClass('icon_error_msg');
					$('#randCode .randcode_img').attr('src','/captcha/index?v='+ parseInt(Math.random()*99999));
                    $('#randCode .input_text').val('');
                    $regBtn.attr('disabled', false);
                    $loading.hide();
                    return;
                }
                if(typeof ec.cart != 'undefined' && ec.cart.placeUrl) {
                	location.href = ec.cart.placeUrl;
				}else {
					location.reload();
				}
			}
		});
		return false;
	};

	$('body').delegate('#randCode .randcode_img,#randCode .randcode_reload','click',function(){
		var ranNum = parseInt (Math.random() *10000);
		$('#randCode .randcode_img').attr('src','/captcha/index?v=' + ranNum);
	})
	
</script>
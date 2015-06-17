
	<!--social s-->
	<div class="wrap social clearfix">
		<div class="media blog">
			<a href="http://blog.<?php echo COMMON_DOMAIN;?>" target="_blank" rel="nofollow" onclick="dataLayer.push({'linkname': 'eachbuyer_blog','event': 'outboundlinks'})"><span>&nbsp;</span><?php echo lang('footer_blog') ?></a>
		</div>
		<div class="media facebook">
			<a href="" target="_blank" rel="nofollow" onclick="dataLayer.push({'linkname': 'facebook','event': 'outboundlinks'})"><span>&nbsp;</span><?php echo lang('footer_facebook') ?></a>
		</div>
		<div class="media twitter">
			<a href="" target="_blank" rel="nofollow" onclick="dataLayer.push({'linkname': 'twitter','event': 'outboundlinks'})"><span>&nbsp;</span><?php echo lang('footer_twitter') ?></a>
		</div>
		<div class="media pin">
			<a href="" target="_blank" rel="nofollow" onclick="dataLayer.push({'linkname': 'pinterest','event': 'outboundlinks'})"><span>&nbsp;</span><?php echo lang('footer_pinterest') ?></a>
		</div>
		<div class="media youtobe">
			<a href="" target="_blank" rel="nofollow" onclick="dataLayer.push({'linkname': 'youtube','event': 'outboundlinks'})"><span>&nbsp;</span><?php echo lang('footer_youtube') ?></a>
		</div>
		<div class="media vk">
			<a href="" target="_blank" rel="nofollow" onclick="dataLayer.push({'linkname': 'vk','event': 'outboundlinks'})"><span>&nbsp;</span><?php echo lang('footer_vk') ?></a>
		</div>
	</div>
	<!--social end-->
	<div class="footer-bg"></div>
	<div class="wrap footer">
		<div class="footer-block clearfix">
			<div class="f-nav first">
				<div class="f-nav-title"><h4><?php echo lang('footer_article_companyinfo') ?></h4></div>
				<ul class="clearfix">
					<li><a href="<?php echo eb_gen_url('about_us.html') ?>" title="<?php echo lang('footer_article_aboutus') ?>" target="_blank" rel="nofollow"><?php echo lang('footer_article_aboutus') ?></a></li>
					<li><a href="<?php echo eb_gen_url('terms_and_conditions.html') ?>" title="<?php echo lang('footer_article_termsandconditions') ?>" target="_blank" rel="nofollow"><?php echo lang('footer_article_termsandconditions') ?></a></li>
					<li><a href="<?php echo eb_gen_url('privacy_policy.html') ?>" title="<?php echo lang('footer_article_privacypolicy') ?>" target="_blank" rel="nofollow"><?php echo lang('footer_article_privacypolicy') ?></a></li>
				</ul>
			</div>
			<div class="f-nav">
				<div class="f-nav-title"><h4><?php echo lang('footer_article_customerservice') ?></h4></div>
				<ul class="clearfix">
					<li><a href="<?php echo eb_gen_url('contact_us.html') ?>" title="<?php echo lang('footer_article_contactus') ?>" target="_blank" rel="nofollow"><?php echo lang('footer_article_contactus') ?></a></li>
					<li><a href="<?php echo eb_gen_url('faq.html') ?>" title="<?php echo lang('footer_article_faq') ?>" target="_blank" rel="nofollow"><?php echo lang('footer_article_faq') ?></a></li>
					<li><a href="<?php echo eb_gen_url('payment_method.html') ?>" title="<?php echo lang('footer_article_paymentmethod') ?>" target="_blank" rel="nofollow"><?php echo lang('footer_article_paymentmethod') ?></a></li>
					<li><a href="<?php echo eb_gen_url('shipping_method_guide.html') ?>" title="<?php echo lang('footer_article_shippingmethodguide') ?>" target="_blank" rel="nofollow"><?php echo lang('footer_article_shippingmethodguide') ?></a></li>
					<li><a href="<?php echo eb_gen_url('return_policy.html') ?>" title="<?php echo lang('footer_article_returnpolicy') ?>" target="_blank" rel="nofollow"><?php echo lang('footer_article_returnpolicy') ?></a></li>
					<?php if(in_array($language_code,array('de'))){ ?>
					<ul class="mar_t1"><li><a href="<?php echo eb_gen_url('impressum.html') ?>" title="<?php echo lang('footer_article_impressum') ?>" target="_blank" rel="nofollow"><?php echo lang('footer_article_impressum') ?></a></li></ul>
					<?php } ?>
				</ul>
			</div>
			<div class="f-nav">
				<div class="f-nav-title"><?php echo lang('footer_article_shippingandreturns') ?></h4></div>
				<ul class="clearfix">
					<li><a href="<?php echo eb_gen_url('affiliate_program.html') ?>" title="<?php echo lang('footer_article_affiliateprogram') ?>" target="_blank" rel="nofollow"><?php echo lang('footer_article_affiliateprogram') ?></a></li>
					<li><a href="<?php echo eb_gen_url('wholesale.html') ?>" title="<?php echo lang('footer_article_wholesale') ?>" target="_blank" rel="nofollow"><?php echo lang('footer_article_wholesale') ?></a></li>
					<li><a href="<?php echo eb_gen_url('bbs_user') ?>" title="<?php echo lang('bbs_user') ?>" target="_blank" rel="nofollow"><?php echo lang('bbs_user') ?></a></li>
				</ul>
			</div>
			<?php if(isset($footer['atoz_list'])){ ?>
				<div class="f-keyword">
					<div class="f-nav-title"><h4><?php echo lang('footer_popular_pages') ?></h4></div>
					<div class="clearfix font-verdana">
						<?php foreach($footer['atoz_list'] as $record){ ?>
						<a href="<?php echo eb_gen_url($record.'.html') ?>" target="_top"><?php echo $record ?></a>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
			<?php if(isset($footer['tag_list'])){ ?>
				<div class="f-hot-tag">
					<div class="f-nav-title"><h4><?php echo lang('footer_hot_searches') ?></h4></div>
					<div class="clearfix font-verdana">
						<?php foreach($footer['tag_list'] as $key => $record){ ?>
						<?php if($key < 20){ ?>
						<a href="<?php echo $record['url'] ?>"><?php echo $record['tag_word'] ?></a>
						<?php } ?>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
			<div class="f-subEmail">
				<div class="f-nav-title"><h4><?php echo lang('select_category') ?>:</h4></div>
				<div class="f-inputEmail">
					<form method="post" action="<?php echo eb_gen_url('common/subscribe') ?>" id="footerSubmit">
						<div class="f-enter-input">
							<input type="text" data-value="<?php echo lang('enter_email') ?>" id="footEnterInputText" class="f-subscribe" name="email">
						</div>
						<div class="f-error red" id="footerSubmitMsg">
							<p class="hide"><?php echo addslashes(lang('subscribe_email_tip')) ?></p>
							<span class="hide"><?php echo addslashes(lang('enter_email')) ?></span>
						</div>
						<div class="f-btn">
							<button class="btn btn-30 btn-bk shadow" type="submit"><?php echo lang('subscribe_footer_tip') ?></button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div class="bottom-links wrap">
			<a href="javascript:;" rel="nofollow"><img src="<?php echo  HelpUrl::getImage('common/bottom-ico-1.png') ?>" alt="credit card payment"/></a>
			<a href="javascript:;" rel="nofollow"><img src="<?php echo  HelpUrl::getImage('common/bottom-ico-2.png') ?>" alt="paypal payment"/></a>
			<a href="javascript:;" rel="nofollow"><img src="<?php echo  HelpUrl::getImage('common/bottom-ico-3.png') ?>" alt="paypal verified"/></a>
			<a href="javascript:;" rel="nofollow"><img src="<?php echo  HelpUrl::getImage('common/bottom-ico-4.png') ?>" alt=""/></a>
			<a href="javascript:;" rel="nofollow"><img src="<?php echo  HelpUrl::getImage('common/bottom-ico-5.png') ?>" alt="ems &amp; dhl shipment"/></a>
			<a href="javascript:;" rel="nofollow"><img src="<?php echo  HelpUrl::getImage('common/bottom-ico-8.png') ?>" alt=""/></a>

	</div>
	<div class="bottom-copyright">
		<address>2012-2015 <?php echo ucfirst( COMMON_DOMAIN ); ?>. <?php echo lang('allRightReserved')?></address>
	</div>

	<div id="toolBox" class="tool-box">
		<a href="javascript:;" class="to-top list" id="gotop">top</a>
	</div>

	<!-- facebook login -->
	<div id="fb-root" class="hide"></div>
	<!-- facebook login end -->

	<!-- category banner s -->
	<?php if(isset($categoryBanner) && is_array($categoryBanner) && count($categoryBanner) > 0 && !FRONT_DEBUG) {?>
	<link href='http://fonts.googleapis.com/css?family=Roboto+Condensed:700' rel='stylesheet' type='text/css'>
	<?php }?>
	<!-- category banner end -->

	<!-- loading img -->
	<img src="<?php echo  HelpUrl::getImage('common/loading/60_60.gif') ?>" width="1" height="1" class="hide" />

<script src="<?php echo  HelpUrl::getJs('lang/us.js') ?>"></script>
<script src="<?php echo  HelpUrl::getJs('common/ec.base.js') ?>"></script>


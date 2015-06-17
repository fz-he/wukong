<!doctype html>
<!--[if lt IE 7]> <html class="ie6 oldIE"> <![endif]-->
<!--[if IE 7]>    <html class="ie7 oldIE"> <![endif]-->
<!--[if IE 8]>    <html class="ie8 oldIE"> <![endif]-->
<!--[if gt IE 8]><!-->
<html>
<!--<![endif]-->
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
<?php echo id2name('canonical',$head) ?>
<?php if(isset($head['html_google_rel'])){?>
    <?php foreach($head['html_google_rel'] as $lang => $record){?>
    <link rel="alternate" hreflang="<?php echo $lang ?>" href="<?php echo $record ?><?php
    	if( $this->router->class == 'category' ){
			$rsegmentsTmp = $this->uri->rsegments;
			if( !( isset( $rsegmentsTmp[4] ) && (int)$rsegmentsTmp[4] > 1 ) ){ echo '/' ;}
		}?>" />
    <?php } ?>
<?php } ?>
<?php if(isset($head['html_google_rel_seo'])){ ?>
	<?php foreach($head['html_google_rel_seo'] as $lang => $record){ ?>
    <link rel="alternate" href="<?php echo $record ?><?php if($this->router->class == 'category'){
    	$rsegmentsTmp = $this->uri->rsegments;
    	if( !( isset( $rsegmentsTmp[4] ) && (int)$rsegmentsTmp[4] > 1 ) ){ echo '/' ;}
    }?>" />
	<?php } ?>
<?php } ?>
<link rel="shortcut icon" href="<?php echo HelpUrl::getImage('common/favicon.ico') ?>" />
<link rel="icon" href="<?php echo HelpUrl::getImage('common/animated_favicon.gif') ?>" type="image/gif" />
<title><?php echo id2name('title',$head) ?></title>
<meta name="keywords" content="<?php echo htmlspecialchars(id2name('keywords',$head).id2name('keywords_desc_domain',$head)) ?>" />
<!-- narrow search mate is use -->
<meta name="description" content="<?php echo htmlspecialchars(id2name('description',$head)) ?>" />

<link rel="stylesheet" media="all" href="<?php echo HelpUrl::getCss('common/common.css') ?>"/>
<!--[if lt IE 9]>
<script src="<?php echo HelpUrl::getJs('libs/jquery-1.11.1.js') ?>"></script>
<![endif]-->

<script src="<?php echo  HelpUrl::getJs('common/html5shiv.js') ?>"></script>
<!--[if gte IE 9]><!-->
<script src="<?php echo  HelpUrl::getJs('libs/jquery-2.1.1.js') ?>"></script>
<!--<![endif]-->
<script src="<?php echo HelpUrl::getJs('libs/ec.lib.js') ?>" namespace="ec"></script>

<!--[if IE 6]><script>ol.isIE6=true;</script><![endif]-->
<!--[if IE 7]><script>ol.isIE7=true;</script><![endif]-->
<!--[if IE 8]><script>ol.isIE8=true;</script><![endif]-->
<script>
<?php echo (FRONT_DEBUG) ? 'ol.debug = true;' : ''?>
ol.version = '<?php echo STATIC_FILE_VERSION ?>';
ol.lang = {globle : "<?php echo $language_code ?>"};
<?php
#登录信息
if ($user != false) {
	echo 'ol.loginInfo = {';
	echo '"user_name":"'. id2name('user_name',$user).'"';
	echo '};';
}
?>
</script>
</head>

<body class="lan-us">
<!--topbar start -->
<div id="topbar" class="topbar">
    <div class="wrap">
    <!-- 语言设置 -->
    	<div class="lan-module">
			<div class="lan-switch <?php echo isset($isOnlyHeader)?'hide':'' ?>">
            	<div class="select-block">
                	<div class="selected">
                    	<a href="javascript:;" title="Language"><?php echo id2name('current_language_title',$header) ?></a>
                        <i class="icon-select-arrow"></i>
                    </div>
		            <?php if(isset($header['language_list'])){ ?>
                    <div class="drop-box">
                    	<ul class="drop-list drop-content">
							<?php foreach($header['language_list'] as $record){ ?>
								<?php if($language_code != $record['code']){ ?>
									<?php if($this->router->class == 'buy' || $this->router->class == 'atoz'){ ?>
										<li> <a href="<?php echo $record['url'] ?>"><span><?php echo $record['title']?></span></a> </li>
									<?php }else{ ?>
										<li> <a href="<?php echo $record['url'].  HelpUrl::removeXSS( $_SERVER['REQUEST_URI'] ); ?>"><span><?php echo $record['title']?></span></a> </li>
									<?php } ?>
								<?php } ?>
							 <?php } ?>
                         </ul>
                    </div>
					<?php } ?>
                 </div>
            </div>
            <div class="currency-switch fl">
            	<div class="select-block <?php echo isset($isOnlyHeader)?'hide':'' ?>">
                	<div class="selected">
                        <a rel="nofollow" href="javascript:;" title="Currency"><?php echo lang('currency') ?>: <em class="currency-name"><?php echo $currency ?></em></a>
                    	<i class="icon-select-arrow"></i>
                    </div>
					<?php if(isset($header['currency_list'])){ ?>
                    <div class="drop-box">
                            <div class="drop-content">
                                <div class="space">
                                    <input class="currency-keywords" type="text" id="currencyKeywords" autocomplete="off"/>
                                    <input id="allCurrency" type="hidden" value = "<?php echo implode(',',$header['currency_list']) ?>"/>
                                </div>
                                <div id="countryList" class="drop-list">
									<?php foreach($header['currency_list'] as $record){ ?>
                                    <a href="javascript:;" onclick="ec.currency('<?php echo $record ?>');" rel="nofollow" class="tab_<?php echo strtoupper($record) ?>"><i class="icon_guoqi tab_<?php echo $record ?>"></i><span><?php echo $record ?></span></a>
                                    <?php } ?>
                                 </div>
                            </div>
                        </div>
					<?php } ?>
                </div>
            </div>
		</div>
    <!-- 语言设置 end -->
        <div class="bar-content">
            <!-- 迷你购物车 -->
            <div class="minicart <?php echo isset($isOnlyHeader)?'hide':'' ?>">
                <div id="miniCart" class="select-block">
                    <div class="selected">
                        <a rel="nofollow" href="<?php echo eb_gen_url('cart') ?>" title="<?php echo lang('cart_name') ?>"><i class="icon-cart"></i><?php echo lang('cart_name') ?><span class="total">(<i class="red" id="cartTotal">0</i>)</span></a>
                        <i class="icon-select-arrow"></i>
                    </div>
                    <div class="drop-box">
                        <div class="drop-content">
                            <p data-tip="<?php echo lang('no_items') ?>" id="minCartTips" class="minicart-tips">loading...</p>
                            <div id="miniCartContent"></div>
                            <div class="miniCart-btn"><a class="btn btn-24 btn-bk shadow" href="<?php echo eb_gen_url('cart') ?>"><?php echo lang('view_cart') ?></a></div>
                            <!-- 购物车模板 -->
							<script type="text/html" id="miniCartTpl">
								{{if (list && list.length > 0)}}
								    <table class="minicart-list">
										{{each list}}
										<tr>
											<td class="cart-img"><a href="{{$value.url}}"><img alt="{{$value.goodsName}}" src="{{$value.image45}}"></a></td>
											<td class="cart-name"><a href="{{$value.url}}">{{$value.goodsName}}</a></td>
											<td class="cart-price">{{$value.finalPriceFormat}}</td>
											<td class="cart-num">{{$value.qty}}</td>
										</tr>
										{{/each}}
									</table>
								{{/if}}
							</script>
							<!-- 购物车模板 end -->
                        </div>
                    </div>
                </div>
               </div>
				<!-- 迷你购物车 end -->
				<!-- 个人中心快速入口 -->
				<?php if(!isset($flg_header_account_disable) || $flg_header_account_disable !== true){ ?>
				<div class="my-account">
					<div class="select-block">
							<div class="selected lan_<?php echo $language_code?>">
								<a rel="nofollow" href="<?php echo eb_gen_url('account') ?>" title="<?php echo lang('my_account') ?>"><?php echo lang('my_account') ?></a>
								<i class="icon-select-arrow"></i>
							</div>
							<div class="drop-box">
								<ul class="drop-content drop-list">
									<li><a href="<?php echo eb_gen_url('account') ?>"><span><?php echo lang('account_dash') ?></span></a></li>
									<li><a href="<?php echo eb_gen_url('wishlist') ?>"><span><?php echo lang('my_wishlist') ?></span></a></li>
									<li><a href="<?php echo eb_gen_url('order_list') ?>"><span><?php echo lang('my_order') ?></span></a></li>
									<li><a href="<?php echo eb_gen_url('review_list') ?>"><span><?php echo lang('my_review') ?></span></a></li>
									<li><a href="<?php echo eb_gen_url('bbs_user') ?>"><span><?php echo lang('bbs_user') ?></span></a></li>
								</ul>
							</div>
					</div>
				</div>
				<!-- 个人中心快速入口 end -->
				<?php } ?>
				<!-- 快速登录 -->
				<div id="ajaxLogin" class="login">
				<!-- ajax登录模板 -->
				<script type="text/html" id="isLoginTpl">
				<div class="login-success">
					<span id="welcomeMsg" class="welcome_msg"><?php echo lang('welcome')?>, {{user_name}} !</span>
					<span id="headerLogout" class="logout">&nbsp;<a href="<?php echo eb_gen_url('common/logout') ?>" rel="nofollow"><?php echo lang('logout') ?></a></span>
				</div>
				</script>
				<script type="text/html" id="unLoginTpl">
					<div class="select-block">
						<div class="selected">
							<a href="<?php echo eb_gen_url('login') ?>" rel="nofollow"><?php echo lang('label_login') ?></a>
							<em><?php echo lang('or') ?></em>
							<a href="<?php echo eb_gen_url('login') ?>" rel="nofollow"><?php echo lang('label_regist') ?></a>
							<i class="icon-select-arrow"></i>
						</div>

						<div class="drop-box">
							<div class="drop-content login_<?php echo $language_code?>">
								<table>
									<tr>
										<td width="45%">
											<form autocomplete="off" id="loginForm" name="formLogin" action="<?php echo eb_gen_url('login/authenticateApi') ?>" method="post" class="mini-login-form" onsubmit="return ec.login.ajaxLogin(this);">
												<h5 class="red"><span><?php echo lang('label_login') ?></span></h5>
												<div class="login-content">
													<label for="mini-login"><?php echo lang('l_login_username') ?>:</label>
													<input type="text" name="user_name" id="userName">
													<label for="mini-password"><?php echo lang('l_login_psw') ?>:</label>
													<input type="password" name="password" id="passWord">
													<div class="mini-form-btn">
														<button id="loginSubmitTop" class="btn btn-30 btn-bk shadow" type="submit"><?php echo lang('label_login') ?></button>
													</div>
													<div class="top-login-error">
														<span class="hide" id="topLoginError"></span>
													</div>
												</div>
											</form>
										</td>
										<td class="customers">
											<div class="login-text">
												<h5 class="red"><?php echo lang('newCustomers') ?></h5>
												<p><?php echo lang('member_desc') ?></p>
											</div>
											<p><a href="<?php echo eb_gen_url('login') ?>" class="btn btn-30 btn-bk shadow"><?php echo lang('label_CreateAnAccount') ?></a></p>
										</td>
									</tr>
									<tr>
										<td colspan="2" class="login-ft">
											<a href="javascript:;" class="fb-login" onclick="ec.login.fbLogin();">
												<i class="icon_fb"></i>
												<span><?php echo lang('popbox_login')?></span>
											</a>
											<span class="facebook-text"><?php echo lang('facebook_login_text') ?></span>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</script>
				<!-- ajax登录模板 end -->
			</div>
            <!-- 快速登录 end -->
        </div>
    </div>	
</div>
<!--topbar end-->
<!-- sale banner -->
<?php if($headBannerDisabled===false) { ?>
	<?php if(isset($fullSiteBanner) && is_array($fullSiteBanner) && count($fullSiteBanner) > 0) {?>
	<div class="sale-banner">
		<img src="<?php echo HelpUrl::getImage('common/banner2.jpg')?>"/>
		<!--<img src="<?php echo $fullSiteBanner['img']?>" />-->
		<div class="countdown">
		<span class="countdown-text" id="saleCountdownText">Ends in:</span><span id="saleCountdown" data-endtime="<?php echo $fullSiteBanner['excessTime'];?>"></span>
		</div>
		<?php if( !empty( $fullSiteBanner['url'] ) ){ ?>
			<a href="<?php echo HelpUrl::absolutePath( $fullSiteBanner['url'] , array() , FALSE ) ?>" class="imglink"  onclick="dataLayer.push({'event': 'promotionClick','ecommerce': {'promoClick': {'promotions': [{'id': '<?php echo $fullSiteBanner['id'];?>','name': '<?php echo addslashes(  $fullSiteBanner['alt'] ) ?>'}]}},'eventCallback': function() { /*document.location = ''*/;}})" >promote</a>
		<?php } ?>
	</div>	
	<script>
		$(function() {
			var langsEnds = 'Ends in';
			$('#saleCountdownText').html(langsEnds+':&nbsp;');
			ec.load('ec.ui.countdown', {
				onload : function () {
					ec.ui.countdown('#saleCountdown', {
						"html" : "<em class='day'>{#day}</em>&nbsp;<span class='day-text'>{#dayText}</span> <em>{#hours}</em><i>:</i><em>{#minutes}</em><i>:</i><em>{#seconds}</em>",
						"zeroDayHide" : true,
						"callback" : function (json) {
							//计时结束时要执行的方法,比如置灰
							//$(this).parent().addClass('timeend');
						}
					});
				}
			});
		});							
	</script>
	<?php }?>
<?php } ?>
<!-- sale banner end-->
<!--logo-->
<div class="header" id="pageHeader">
	<div class="wrap header_<?php echo $language_code;?>">
		<div class="logo"><a href="/" title="<?php echo ucfirst( COMMON_DOMAIN ); ?>"><img src="<?php echo HelpUrl::getImage('common/logo.png') ?>" alt="<?php echo ucfirst( COMMON_DOMAIN ); ?>" title="<?php echo ucfirst( COMMON_DOMAIN ); ?>" /></a></div>
		<div class="logo-banner">
			<?php if(isset($header['image_ad'][14])){ ?>
			<?php foreach($header['image_ad'][14] as $record){ ?>
			<img src="<?php echo $record['image_path'] ?>" alt="<?php echo $record['image_alt'] ?>" />
			<?php } ?>
			<?php } ?>
		</div>
		<ul class="logo-nav">
			<?php $wholesaleUrl = eb_gen_url('wholesale.html'); ?>
			<?php if( time() >= strtotime("2014-07-21 15:00:00") ){ ?>
				<li class="long"><a class="red vam" href="<?php echo eb_gen_url('flashsale') ?>" title="<?php echo lang('flashsale') ?>" onclick="dataLayer.push({'linkname': 'Flash Sale','event': 'navilinks'})"><?php echo lang('flashsale') ?></a><i class="icon_arr_right vam"></i></li>
			<?php } else { ?>
				<li class="long"><a href="<?php echo eb_gen_url('limited-time-offer.html') ?>" title="<?php echo lang('limit_time_offer') ?>" class="vam" onclick="dataLayer.push({'linkname': 'Limited Time Offer','event': 'navilinks'})"><?php echo lang('limit_time_offer') ?></a><i class="icon_arr_right vam"></i></li>
			<?php } ?>
			<li class="short"><a href="<?php echo eb_gen_url('wholesale') ?>" title="<?php echo lang('wholesale') ?>" class="vam" onclick="dataLayer.push({'linkname': 'wholesale','event': 'navilinks'})"><?php echo lang('wholesale') ?></a><i class="icon_arr_right vam"></i></li>
		</ul>

	</div>
</div>
<!--logo end-->

<!-- nav start-->
<div id="pageNav" class="nav">
	<div class="wrap">
		<!-- categray start -->
		<!--<div class="nav-categray nav-categray-show">-->
		<div class="nav-categray" id="navCategray">
			<div class="cate-title"><a href="javascript:;"><?php echo lang('all_category') ?></a><i class="icon-allCate-arrow"></i></div>
			<div class="cate-list" id="categrayAll">
				<ul class="big-list clearfix" id="categrayAll">
					<?php if(count($allCategoryMap) > 0) {?>
						<?php
							$allCateLen = 0;
							foreach ($allCategoryMap as $k1 => $v1) {
								$v1id = isset($v1['id'])? $v1['id'] : 0;
								$v1url = isset($v1['url'])? $v1['url'] : 0;
								$v1name = isset($v1['name'])? $v1['name'] : 0;
								$v1product_active_num = isset($v1['product_active_num'])? $v1['product_active_num'] : 0;

								if(!empty($v1id) && !empty($v1url) && !empty($v1name) && !empty($v1product_active_num)) {
									$allCateLen++;
								}
							}
						?>

						<?php foreach ($allCategoryMap as $k1 => $v1) { ?>
							<?php $v1id = isset($v1['id'])? $v1['id'] : 0?>
							<?php $v1url = isset($v1['url'])? $v1['url'] : 0?>
							<?php $v1name = isset($v1['name'])? $v1['name'] : 0?>
							<?php $v1product_active_num = isset($v1['product_active_num'])? $v1['product_active_num'] : 0?>
							<?php $cat_css_img = isset($v1['nav_image_bg']) ? HelpUrl::imgSite( $v1['nav_image_bg']) : '';?>
							<?php if(!empty($v1id) && !empty($v1url) && !empty($v1name) && !empty($v1product_active_num)) {?>
								<li class="list" id="menu_<?php echo $v1id?>" data-id="<?php echo $v1id?>">
									<div class="li">
										<a href="<?php echo eb_gen_url($v1url, TRUE)?>" class="vam"><?php echo $v1name?></a>
										<span class="vam">(<?php echo $v1product_active_num?>)</span>
										<i class="icon-arr-right"></i>
									</div>

									<div class="sub-list clearfix cate_sub_<?php echo $v1id?>" style="background:#fff url(<?php echo $cat_css_img;?>) right bottom no-repeat;">
										<div class="sub-border"><div class="sub-border-top"></div></div>
										<div class="popup-logo">
											<?php if(isset($v1['nav_image']) && !empty($v1['nav_image'])) { ?>
												<?php $navUrl = isset($v1['nav_url'])? $v1['nav_url']:'' ?>
												<a href="<?php echo $navUrl?>" target="_blank">
													<img src="<?php echo HelpUrl::imgSite($v1['nav_image'])?>">
												</a>
											<?php  } ?>
										</div>
										<div class="p10_20 sub-padding">
											<?php if(isset($v1['subCategory']) && count($v1['subCategory']) > 0) { ?>
												<?php
													//$v2Count = $v1['subCount'];
													//if($v2Count == 0) {
														//$v2Count = 60;
													//}
													//$lineCount = ($v2Count > 20) ? 20 : $v2Count;
													$len = floor(($allCateLen * 31 - 20) / 19);
													$lineCount = $len;
													//if($language_code == 'ru') $lineCount -= 3;
													$i = 0;
													$j = 1;
												?>
												<ul class="column">
													<?php foreach ($v1['subCategory'] as $k2 => $v2) { ?>
														<?php if(isset($v2['product_active_num']) && $v2['product_active_num'] > 0) {?>
															<?php
																if($j >= 4) break;
																$subCount = 0;
																if(isset($v2['subCategory']) && count($v2['subCategory']) > 0){
																	$subCount = count($v2['subCategory']);
																}

																if($subCount > 0) {
																	$b = (($lineCount - $i) < 5 && $subCount + $i >= $lineCount) ? true : false;
																} else {
																	$b = ($i >= $lineCount) ? true : false;
																}
																if($i > 0 && $b) {
																	echo '</ul><ul class="column">';
																	$i = 0;
																	$j += 1;
																}

															?>
															<?php if(isset($v2['id']) && isset($v2['url']) && isset($v2['name'])){ ?>
																<li class="itemMenuName level1 item1" data-id="<?php echo $v2['id']?>">
																	<a href="<?php echo eb_gen_url($v2['url'], TRUE)?>">
																		<?php echo $v2['name']?>
																	</a>
																</li>
																<?php $i++; ?>
															<?php }?>
															<?php if(isset($v2['subCategory']) && count($v2['subCategory']) >0) {?>
																<?php foreach ($v2['subCategory'] as $k3 => $v3) { ?>
																	<?php
																		if($i > 0 && $i >= $lineCount) {
																			echo '</ul><ul class="column">';
																			$i=0;
																			$j+=1;
																		}
																	?>
																	<?php if(isset($v3['id']) && isset($v3['url']) && isset($v3['name']) && count($v2['subCategory']) > 0) {?>
																		<li class="itemMenuName level2 item1" data-id="<?php echo $v3['id']?>">
																			<a href="<?php echo eb_gen_url($v3['url'], TRUE)?>">
																				<?php echo $v3['name']?>
																			</a>
																		</li>
																		<?php $i = (mb_strlen($v3['name'], 'UTF8') > (($language_code == 'us') ? 38 : 37)) ? ($i+2) : ($i+1);?>
																	<?php }?>
																<?php } ?>
															<?php } ?>
														<?php } ?>
													<?php } ?>
												</ul>
											<?php } ?>
										</div>
									</div>
								</li>
							<?php }?>
						<?php }?>
					<?php }?>
				</ul>
			</div>
		</div>
        <!-- categray end -->

		<!-- search form start -->
		<div class="nav-search">
			<div class="search-module">
				<form id="searchForm" name="searchForm" method="get" action="<?php echo eb_gen_url('search') ?>">
					<input type="hidden" name="category" id="selectCategory" value="<?php echo id2name('current_search_category_id',$header,0) ?>">
					<div id="searchTypeSelect" class="search-type">
						<div class="select-block">
							<div class="selected">
								<a id="searchTypeValue" href="javascript:;" class="searchvalue"><?php echo id2name('current_search_category',$header,'ALL') ?></a>
								<i class="icon-select-arrow"></i>
							</div>
							<div class="drop-box">
								<!--<ul id="searchType" class="drop-content drop-list"></ul>-->
								<ul class="drop-content drop-list" id="searchType">
									<li data-id="0" class="category_level_0"><a href="javascript:;">ALL</a></li>
								</ul>
							</div>
						</div>
					</div>
					<div class="search-keyword">
						<?php
							$recommend_keyword_index = rand(0,count($header['keyword_recommend_list'])-1);
							if(!isset($header['search_keywords'])){
								$header['search_keywords'] = $header['keyword_recommend_list'][$recommend_keyword_index]['keyword'];
							}
						?>
						<input type="text" data-value="<?php echo $header['search_keywords'] ?>" autocomplete="off" name="keywords" id="keywords" class="keywords">
					</div>
					<button type="submit" class="search-btn"><?php echo lang('search') ?></button>
				</form>
			</div>
			<?php if(isset($header['keyword_recommend_list'])){ ?>
			<ul class="hot">
				<?php foreach($header['keyword_recommend_list'] as $key => $record){ ?>
				<li><a href="<?php echo trim( $record['url'] ) ;?>"<?php echo $recommend_keyword_index==$key?' class="orange"':'' ?>><?php echo $record['keyword'] ?></a></li>
				<?php } ?>
			</ul>
			<?php } ?>
		</div><!-- search form end -->

	</div>
</div>


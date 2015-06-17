<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\components\helpers\ArrayHelper;

/* @var $this \yii\web\View */
/* @var $content string */
$head = isset($this->params['head']) ? $this->params['head'] : [] ;

AppAsset::register($this);
?>
<?= $this->render('//pc/common/topbar.php', ['header' => $header, 'language_code'=>$language_code] ) ?>
<!-- sale -->
<?= $this->render('//pc/widget/top_sale_banner.php' , [] ) ?>
<!-- sale end -->
<!--logo-->
<div class="header" id="pageHeader">
	<div class="wrap header_<?php echo $language_code;?>">
		<div class="fl logo"><a href="/" title="<?php echo ucfirst( COMMON_DOMAIN ); ?>"><img src="/images/common/logo.png" alt="<?php echo ucfirst( COMMON_DOMAIN ); ?>" title="<?php echo ucfirst( COMMON_DOMAIN ); ?>" /></a></div>

		<div class="fl logo_banner">
			<?php if(isset($header['image_ad'][14])){ ?>
			<?php foreach($header['image_ad'][14] as $record){ ?>
			<img src="<?php echo $record['image_path'] ?>" alt="<?php echo $record['image_alt'] ?>" />
			<?php } ?>
			<?php } ?>
		</div>

		<div class="fr logo_nav pt15">
			<ul class="clearfix">
				<?php $wholesaleUrl = eb_gen_url('wholesale.html'); ?>
				<?php if( time() >= strtotime("2014-07-21 15:00:00") ){ ?>
					<li class="toolong fl"><a class="red vam" href="<?php echo eb_gen_url('flashsale') ?>" title="<?php echo lang('flashsale') ?>" onclick="dataLayer.push({'linkname': 'Flash Sale','event': 'navilinks'})"><?php echo lang('flashsale') ?></a><i class="icon_arr_right vam"></i></li>
				<?php } else { ?>
					<li class="toolong fl"><a href="<?php echo eb_gen_url('limited-time-offer.html') ?>" title="<?php echo lang('limit_time_offer') ?>" class="vam" onclick="dataLayer.push({'linkname': 'Limited Time Offer','event': 'navilinks'})"><?php echo lang('limit_time_offer') ?></a><i class="icon_arr_right vam"></i></li>
				<?php } ?>
				<li class="fr"><a href="/wholesale" title="<?php echo lang('wholesale') ?>" class="vam" onclick="dataLayer.push({'linkname': 'wholesale','event': 'navilinks'})"><?php echo lang('wholesale') ?></a><i class="icon_arr_right vam"></i></li>
			</ul>
		</div>
	</div>
</div>
<!--logo end-->

<!-- nav start-->
<div class="nav" id="pageNav">
	<div class="wrap nav_container">
		<!-- categray start -->
		<!--<div class="nav_categray nav_categray_show">-->
		<div class="nav_categray" id="navCategray">
			<div class="cate_title"><a href="javascript:;"><?php echo lang('all_category') ?></a><i class="icon_allCate_arrow"></i></div>
			<div class="cate_all_list" id="categrayAll">
				<ul class="cate_big_list clearfix">
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
								<li class="vam list" id="menu_<?php echo $v1id?>" data-id="<?php echo $v1id?>">
									<div class="li">
										<a href="<?php echo eb_gen_url($v1url, TRUE)?>" class="vam"><?php echo $v1name?></a>
										<span class="vam">(<?php echo $v1product_active_num?>)</span>
										<i class="icon_arr_right"></i>
									</div>

									<div class="cate_sub_list clearfix cate_sub_<?php echo $v1id?>" style="background:#fff url(<?php echo $cat_css_img;?>) right bottom no-repeat;">
										<div class="cate_sub_border"><div class="cate_sub_border_top"></div></div>
										<div class="popup_logo">
											<?php if(isset($v1['nav_image']) && !empty($v1['nav_image'])) { ?>
												<?php $navUrl = isset($v1['nav_url'])? $v1['nav_url']:'' ?>
												<a href="<?php echo $navUrl?>" target="_blank">
													<img src="<?php echo HelpUrl::imgSite($v1['nav_image'])?>">
												</a>
											<?php  } ?>
										</div>
										<div class="p10_20 cate_sub_padding">
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
		</div><!-- categray end -->

		<!-- search form start -->
		<div class="nav_search">
			<div class="search_main fl">
				<form action="/search" method="get" name="searchForm" id="searchForm">
					<input type="hidden" value="<?php echo id2name('current_search_category_id',$header,0) ?>" id="selectCategory" name="category">
					<div class="search_type" id="searchTypeSelect">
						<div class="select_block vat">
							<div class="selected vat">
								<a class="searchvalue" href="javascript:;" id="searchTypeValue"><?php echo id2name('current_search_category',$header,'ALL') ?></a>
								<i class="icon_select_arrow"></i>
							</div>
							<div class="drop_box">
								<ul class="drop_content drop_list" id="searchType">
									<li data-id="0" class="category_level_0"><a href="javascript:;">ALL</a></li>
								</ul>
							</div>
						</div>
					</div>
					<div class="search_keyword vat fl">
						<?php
							$recommend_keyword_index = rand(0,count($header['keyword_recommend_list'])-1);
							if(!isset($header['search_keywords'])){
								$header['search_keywords'] = $header['keyword_recommend_list'][$recommend_keyword_index]['keyword'];
							}
						?>
						<input type="text" data-value="<?php echo $header['search_keywords'] ?>" autocomplete="off" name="keywords" id="keywords" class="keywords vat">
					</div>
					<button class="search_btn fl" type="submit"><?php echo lang('search') ?></button>
				</form>
			</div>

			<?php if(isset($header['keyword_recommend_list'])){ ?>
			<div class="nav_hot fl nva_hot_<?php echo $language_code ?>">
				<ul>
					<?php foreach($header['keyword_recommend_list'] as $key => $record){ ?>
					<li><a href="<?php echo trim( $record['url'] ) ;?>"<?php echo $recommend_keyword_index==$key?' class="orange"':'' ?>><?php echo $record['keyword'] ?></a></li>
					<?php } ?>
				</ul>
			</div>
			<?php } ?>

		</div><!-- search form end -->

	</div>
</div>
<script src="<?php echo HelpUrl::js('ec.base.js' , $jsPath.'/common') ?>"></script>
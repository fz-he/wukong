<?php // include app\components\helpers\OtherHelper::eb_view_path_new('echannel/common/header.php'); ?>
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\LoginForm */

$this->params['breadcrumbs'][] = $this->title;
$this->params['head'] = $header;
 ?>
<h1><?= Html::encode($this->title) ?></h1>
<script>
	//隐藏首页分类上的小箭头,写在这里为了加快渲染速度
	gid('navCategray').className = 'nav_categray nav_categray_show';
</script>
<div class="main wrap clearfix">
	<div class="content fr">
		<!-- 焦点图片 -->
		<div class="focus_imgs mt10 clearfix">
			<div class="focus_l fl">
				<div class="focus_l_content">
					<div class="ec_slider focus_l_t" id="focus">
						<div class="ec_slider_main">
							<ul class="ec_slider_list">
								<?php if(isset($image_ad[1])){ ?>
								<?php foreach($image_ad[1] as $key => $record){ ?>
								<li><a onclick="dataLayer.push({'event': 'promotionClick','ecommerce': {'promoClick': {'promotions': [{'id': '<?php echo addslashes( $record['image_ad_name'] ).$key ?>','name': '<?php echo addslashes(  $record['image_alt'] ) ?>'}]}}})" href="<?php echo $record['image_link'] ?>" ><img src="<?php echo $record['image_path'] ?>" alt="<?php echo $record['image_alt'] ?>" /></a></li>
								<?php } ?>
								<?php } ?>
							</ul>
						</div>
					</div>

					<div class="focus_l_b">
						<ul class="clearfix">
							<?php if(isset($image_ad[3])){ ?>
							<?php foreach($image_ad[3] as $key => $record){ ?>
							<li <?php echo $key==0?' class="first"':'' ?>>
								<a onclick="dataLayer.push({'event': 'promotionClick','ecommerce': {'promoClick': {'promotions': [{'id': '<?php echo addslashes( $record['image_ad_name']).$key ?>','name': '<?php echo addslashes( $record['image_alt']) ?>'}]}}})" href="<?php echo $record['image_link'] ?>" title="<?php echo $record['image_alt'] ?>" rel="nofollow">
									<img src="<?php echo $record['image_path'] ?>" alt="<?php echo $record['image_alt'] ?>"/>
								</a>
							</li>
							<?php } ?>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
			<div class="focus_r fr">
				<?php if(isset($image_ad[2]) && isset($image_ad[2][0])){ ?>
				<?php $record = $image_ad[2][0]; ?>
					<a href="<?php echo $record['image_link'] ?>" title="<?php echo $record['image_alt'] ?>" onclick="dataLayer.push({'event': 'promotionClick','ecommerce': {'promoClick': {'promotions': [{'id': '<?php echo addslashes( $record['image_ad_name']).$key ?>','name': '<?php echo addslashes( $record['image_alt'] )?>'}]}}})">
					<img src="<?php echo $record['image_path'] ?>" alt="<?php echo $record['image_alt'] ?>" />
					</a>
				<?php } ?>
			</div>
		</div><!-- 焦点图片 end -->

		<!-- 分类索引 -->
		<div class="cate_index mt10 clearfix" id="cateIndex">
			<?php foreach($category_keyword_recommend_list as $mid => $row_list){ ?>
			<div class="li <?php echo $mid%2==0?'fr':'fl' ?>">
				<?php foreach($row_list as $row_id => $keyword_list){ ?>
					<?php if($row_id == 0){ ?>
						<div class="title"><h2><?php echo $keyword_list[0]['keyword'] ?></h2><a class="more" href="<?php echo $keyword_list[0]['keyword_url'] ?>"><?php echo lang( 'see_more' );?>>></a></div>
					<?php }else{ ?>
						<div class="index_sub_list">
							<dl class="clear">
								<dt class="fl pr10"><?php echo $keyword_list[0]['keyword'] ?>:</dt>
								<dd class="fl">
								<?php foreach($keyword_list as $kid => $keyword){ ?>
									<?php if($kid > 0){ ?>
										<a onclick="dataLayer.push({'section':'text_links','homelinkname':'<?php echo "$mid-$row_id-$kid"?>','event':'homelinks'})" href="<?php echo $keyword['keyword_url'] ?>" <?php echo $keyword['checked']==1?'class="orange"':'' ?>><?php echo $keyword['keyword'] ?></a>
									<?php } ?>
								<?php } ?>
								</dd>
							</dl>
						</div>
					<?php }?>
				<?php }?>
			</div>
			<?php }?>
		</div>
		<!-- 分类索引 end -->

		<div id="productList">
			<!-- 商品列表1 -->
			<div class="pro_tab_list mt10 clearfix">
				<div class="title">
					<h4><?php echo id2name('part_title',$special_goods_recommend_tab) ?><a id="special_deals" name="special_deals"></a></h4>
					<p class="tab_t">
						<?php for($i=0; $i<5; $i+=1){ ?>
						<?php $title = id2name('model_title_'.($i+1),$special_goods_recommend_tab) ?>
						<a onclick="ec.index.tab(this,'pro_tab_b_0_<?php echo ($i+1);?>')" href="javascript:;" class="<?php if($i < 1){echo 'current';}?>" data-index="<?php echo $i;?>"><?php echo $title?></a><?php if($i < 4){echo '<span>|</span>';}?>
						<?php } ?>
					</p>
				</div>
				<div class="pro_list index_pro_list">
					<?php foreach($special_goods_recommend_list as $model_id => $sku_list){ ?>
					<?php $title = id2name('model_title_'.($model_id),$special_goods_recommend_tab) ?>
					<?php $id = str_pad($model_id,2,'0',STR_PAD_LEFT) ?>
					<?php $current = ($model_id < 2) ? 'current' : ''; ?>
					<?php $dataLazysrc = ($model_id>1) ? 'data-lazysrc-tab' : 'data-lazysrc';?>
					<ul class="clearfix fl <?php echo $current?>" id="pro_tab_b_0_<?php echo $model_id ?>">
						<?php foreach($sku_list as $key => $sku){ ?>
						<li class="fl">
							<div class="pro_list_block">
								<div class="p_pic">
									<a onclick="dataLayer.push({'event': 'productClick','ecommerce': {'click': {'actionField': {'list': 'Home Page'},'products': [{'id': '<?php echo $sku['id']; ?>','price': '<?php echo $sku['final_price']; ?>'}]}}})" href="<?php echo eb_gen_url($sku['url']) ?>" title="<?php echo $sku['name']; ?>">
										<img src="<?php echo $mediaPath;?>/common/other/default.png" <?php echo $dataLazysrc;?>="<?php echo $sku['goods_img'] ?>" alt="<?php echo $sku['name']; ?>" />
										<?php if($sku['promote_type'] == 32 || $sku['promote_type'] == 33) {?>
											<em class="seckill seckill_<?php echo strtolower($language_code)?>"></em>
										<?php }?>
										<?php if( $sku['flg_promote'] == true && $sku['discount'] != 0){ ?>
											<p class="icon_off"><i><?php echo $sku['discount'] ?></i></p>
										<?php } ?>

									</a>
									<?php if( $sku['flg_promote'] == true && $sku['is_foreshow'] != true ) { ?>
										<div class="p_time_text">
										<i class="icon_time vam" title="<?php echo $sku['name']; ?>" ></i>
										<span class="p_countdown vam" data-endtime="<?php echo $sku['countdown_time'] ?>"></span>
										</div>
									<?php } ?>
								</div>
								<div class="p_name"><a onclick="dataLayer.push({'event': 'productClick','ecommerce': {'click': {'actionField': {'list': 'Home Page'},'products': [{'id': '<?php echo $sku['id']; ?>','price': '<?php echo $sku['final_price']; ?>'}]}}})" href="<?php echo eb_gen_url($sku['url']) ?>"><?php echo $sku['name'] ?></a></div>
								<div class="p_price">
									<p class="p_price_o pr10"><?php echo $sku['formatPrice'];?></p>
									<p class="p_price_n red"><?php echo $sku['formatShopPrice'];?></p>
								</div>
								<div class="p_fs fs_<?php echo $language_code ?>"><em><?php echo lang('freeShippingCa') ?></em></div>
							</div>
						</li>
						<?php }?>
					</ul>
					<?php }?>
				</div>
			</div><!-- 商品列表1 end -->

			<div class="mt30 mb10">
				<?php if(isset($image_ad[6])){ ?>
				<?php foreach($image_ad[6] as $key => $record){ ?>
				<a onclick="dataLayer.push({'event': 'promotionClick','ecommerce': {'promoClick': {'promotions': [{'id': '<?php echo addslashes( $record['image_ad_name']).$key ?>','name': '<?php echo addslashes( $record['image_alt']) ?>'}]}}})" class="index_center_chuxiao" href="<?php echo $record['image_link'] ?>" title="<?php echo $record['image_alt'] ?>"><img src="/images/common/other/default.png" data-lazysrc="<?php echo $record['image_path'] ?>" alt="<?php echo $record['image_alt'] ?>" /></a>
				<?php } ?>
				<?php } ?>
			</div>

			<!-- 商品列表2 -->
			<div class="pro_tab_list mt10 clearfix">
				<div class="title">
					<h4><?php echo id2name('part_title',$new_goods_recommend_tab) ?><a id="new_arrivals" name="new_arrivals"></a></h4>
					<p class="tab_t">
						<?php for($i=0; $i<5; $i+=1){ ?>
						<?php $title = id2name('model_title_'.($i+1),$new_goods_recommend_tab) ?>
						<a onclick="ec.index.tab(this,'pro_tab_b_1_<?php echo ($i+1);?>')" href="javascript:;" class="<?php if($i < 1){echo 'current';}?>" data-index="<?php echo $i;?>"><?php echo $title?></a><?php if($i < 4){echo '<span>|</span>';}?>
						<?php } ?>
					</p>
				</div>
				<div class="pro_list index_pro_list">
					<?php foreach($new_goods_recommend_list as $model_id => $sku_list){ ?>
					<?php $title = id2name('model_title_'.($model_id),$new_goods_recommend_tab) ?>
					<?php $id = str_pad($model_id,2,'0',STR_PAD_LEFT) ?>
					<?php $current = ($model_id < 2) ? 'current' : ''; ?>
					<?php $dataLazysrc = ($model_id>1) ? 'data-lazysrc-tab' : 'data-lazysrc';?>
					<ul class="clearfix fl <?php echo $current?>" id="pro_tab_b_1_<?php echo $model_id ?>">
						<?php foreach($sku_list as $key => $sku){ ?>
						<li class="fl">
							<div class="pro_list_block">
								<div class="p_pic">
									<a onclick="dataLayer.push({'event': 'productClick','ecommerce': {'click': {'actionField': {'list': 'Home Page'},'products': [{'id': '<?php echo $sku['id']; ?>','price': '<?php echo $sku['final_price']; ?>'}]}}})" href="<?php echo eb_gen_url($sku['url']) ?>" title="<?php echo $sku['name'] ?>">
										<img src="<?php echo $mediaPath;?>/common/other/default.png" <?php echo $dataLazysrc;?>="<?php echo $sku['goods_img'] ?>" alt="<?php echo $sku['name'] ?>" />
										<?php if($sku['promote_type'] == 32 || $sku['promote_type'] == 33) {?>
											<em class="seckill seckill_<?php echo strtolower($language_code)?>"></em>
										<?php }?>
										<?php if( $sku['flg_promote'] == true && $sku['discount'] != 0 ){ ?>
											<p class="icon_off"><i><?php echo $sku['discount'] ?></i></p>
										<?php } ?>

									</a>
									<?php if( $sku['flg_promote'] == true && $sku['is_foreshow'] != true ) { ?>
										<div class="p_time_text">
										<i class="icon_time vam" title="<?php echo $sku['name']; ?>" ></i>
										<span class="p_countdown vam" data-endtime="<?php echo $sku['countdown_time'] ?>"></span>
										</div>
									<?php } ?>
								</div>
								<div class="p_name"><a onclick="dataLayer.push({'event': 'productClick','ecommerce': {'click': {'actionField': {'list': 'Home Page'},'products': [{'id': '<?php echo $sku['id']; ?>','price': '<?php echo $sku['final_price']; ?>'}]}}})" href="<?php echo eb_gen_url($sku['url']) ?>"><?php echo $sku['name'] ?></a></div>
								<div class="p_price">
									<p class="p_price_o pr10"><?php echo $sku['formatPrice'];?></p>
									<p class="p_price_n red"><?php echo $sku['formatShopPrice'];?></p>
								</div>
								<div class="p_fs fs_<?php echo $language_code ?>"><em><?php echo lang('freeShippingCa') ?></em></div>
							</div>
						</li>
						<?php }?>
					</ul>
					<?php }?>
				</div>
			</div><!-- 商品列表2 end -->
		</div>
	</div>

	<?php include app\components\helpers\OtherHelper::eb_view_path_new('echannel/index_sidebar.php'); ?>

</div>
<script>
//刷新时 不能加引号
var dataLayerProducts = <?php echo empty($dataLayerProducts)? '[]': $dataLayerProducts ?>; 
//首页banner
var dataLayerBanners =  <?php echo empty($dataLayerBanners)? '[]': $dataLayerBanners ?>;
</script>
<script src="<?php echo HelpUrl::js('index.js' , $jsPath .'/index') ;?>"></script>

<?php if(isset($recently_review_list) && !empty($recently_review_list) ){ ?>
<div class="side_recent_review mod mt10">
	<div class="title"><h4><?php echo lang('recent_review_items') ?></h4></div>
	<div class="review_list ec_slider" id="reviewList">
		<div class="ec_slider_main">
			<ul class="ec_slider_list">
				<?php foreach($recently_review_list as $record){ ?>
					<li>
					<div class="li clearfix">
						<div class="review_pic"><a href="<?php echo eb_gen_url($record['url']) ?>#procuct_reviews" rel="nofollow"><img src="<?php echo $record['goods_img'] ?>" title="<?php echo htmlspecialchars($record['name']) ?>" /></a></div>
						<div class="review_desc">
							<h5><a href="<?php echo eb_gen_url($record['url']) ?>#procuct_reviews" rel="nofollow" title="<?php echo htmlspecialchars($record['name']) ?>"><?php echo $record['goods_name_show'] ?></a></h5>
							<p class="review_title"><a rel="nofollow" href="<?php echo eb_gen_url($record['url']) ?>#procuct_reviews"><?php echo $record['title'] ?></a></p>
							<p class="txtr review_by"><span>-</span><?php echo $record['user_name'] ?></p>
						</div>
					</div>
					</li>
				<?php }?>
			</ul>
		</div>
	</div>
</div>
<script>
(function ($) {
	//调用滑动插件
	var $reviewList = $("#reviewList");
	if($('li', $reviewList).length > 3) {
		ec.load("ec.ui.slider", {
			loadType : "lazy",
			onload : function() {
				$reviewList.slider({
					width: 218, //必须
					height: 330, //必须
					style : 0, //1显示分页，2只显示左右箭头,3两者都显示, 0都不显示
					pause : 5000, //间隔时间
					speed : 250, //速度
					auto : true, //是否自动开始
					sliderType : 'up' //up:向上，left:向左，filter：渐变
				});
			}
		});
	}
})(jQuery);
</script>
<?php }?>
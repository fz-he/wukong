<!-- sale -->
<?php if($headBannerDisabled===false) { ?>
	<?php if(isset($fullSiteBanner) && is_array($fullSiteBanner) && count($fullSiteBanner) > 0) {?>
			<div class="sale_banner sale_<?php echo $language_code?>">
			<div class="sale_img">
				<img src="<?php echo $fullSiteBanner['img']?>" />
			</div>
			<div class="sale_banner_countdown">
				<span class="sale_banner_countdown_text" id="sale_countDown_text">Ends in:</span><span id="sale_countdown" data-endtime="<?php echo $fullSiteBanner['excessTime'];?>"></span>
			</div>
			<?php if( !empty( $fullSiteBanner['url'] ) ){ ?>
				<a href="<?php echo HelpUrl::absolutePath( $fullSiteBanner['url'] , array() , FALSE ) ?>" class="sale_href"  onclick="dataLayer.push({'event': 'promotionClick','ecommerce': {'promoClick': {'promotions': [{'id': '<?php echo $fullSiteBanner['id'];?>','name': '<?php echo addslashes(  $fullSiteBanner['alt'] ) ?>'}]}}})" >promote</a>
			 <?php } ?>
			</div>

			<script>
			$(function() {
				var langsEnds = '<?php echo lang('sale_end_time'); ?>';
				$('#sale_countDown_text').html(langsEnds+':&nbsp;');
				ec.load('ec.ui.countdown', {
					onload : function () {
						ec.ui.countdown('#sale_countdown', {
							"html" : "<em class='day'>{#day}</em>&nbsp;<span class='day_text'>{#dayText}</span> <em>{#hours}</em><i>:</i><em>{#minutes}</em><i>:</i><em>{#seconds}</em>",
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
<!-- sale end -->
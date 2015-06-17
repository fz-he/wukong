/**
* @authors zhuzhengwei
* @date    2014-11-1
*/
ec.pkg('ec.index.tab');


(function () {
	var _imgCachList = {};
	ec.index.tab = function (obj, tagId) {
		var $thix = $(obj);
		var $tagObj = $('#'+ tagId);
		$thix.addClass('current').siblings().removeClass('current');
		$tagObj.siblings().fadeOut(500);
		$tagObj.fadeIn(500, function () {
			if($thix.index() > 0 && !_imgCachList[tagId]){
				$tagObj.find('img').each(function (i, obj){
					var lazysrc = $(obj).data('lazysrc-tab');
					obj.src = lazysrc;
					$(obj).removeAttr('data-lazysrc-tab');
				});
				_imgCachList[tagId] = true;
			}
		});
	};
        //GA统计
        if ( window['dataLayerProducts'].length > 0 ){
            dataLayer.push({
              'ecommerce': {
                'currencyCode': 'USD',                       
                'impressions': window['dataLayerProducts']
              }
            });
        }
        if ( window['dataLayerBanners'].length > 0 ){
            dataLayer.push({
              'ecommerce': {
                    'promoView': {
                    	'promotions': window['dataLayerBanners']  
           	 }
              }
            });
        }
        
})();

// 加载倒计时插件
ec.index.countDown = function (){
	ec.load('ec.ui.countdown', {
		loadType:"lazy",
		onload : function () {
			ec.ui.countdown('#productList .p_countdown',{
				"html" : "<span class='day'>{#day}</span><i class='day'>days</i> <span>{#hours}</span><i>:</i><span>{#minutes}</span><i>:</i><span>{#seconds}</span>",
				"zeroDayHide" : true,
				"callback" : function (json) {
					//计时结束时要执行的方法,比如按钮置灰
					$(this).parent().addClass('timeend');
				}
			});
		}
	});
};

//内容动画效果
ec.load("ec.ui.slider", {
	loadType : "lazy",
	onload : function() {
		$("#focus").slider({
			width: 750, //必须
			height: 265, //必须
			style : 1, //1显示分页，2只显示左右箭头,3两者都显示, 0都不显示
			pause : 3000, //间隔时间
			auto : true, //是否自动开始
			sliderType : 'filter' //up:向上，left:向左，filter：渐变
		});
	}
});
ec.ready(function () {
	ec.index.countDown();
});

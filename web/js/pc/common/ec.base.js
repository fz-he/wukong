/** 
 * 公共方法，改动或追加比较多
 * @class 	allPage
 * @author  zhuzhengwei
 * @date	2014/05/08
 * @lastModified  2014/11/1
 * @dependon jquery-1.4.1.min.js or later、 ec.lib.js
 */

ol.debug = (ol.debug || false);//调试模式
log('debug:' + ol.debug);

ol.load.define("jquery.form" , [{mark:"jquery.form",uri: "../plugs/jquery.form/form-3.50.js?20140530",type: "js"}]);
ol.load.define("ajax" , [
	"jquery.form",
	{mark:"ajax",uri: "../common/ajax.js?20140530",type: "js",charset: "utf-8",depend:true}
]);
ol.load.define("ajaxcdr" , [
	"jquery.form",
	{mark:"ajaxcdr",uri: "../common/ajaxcdr.js?20140530",type: "js",charset: "utf-8",depend:true}
]);

/*ol.load.define("swfobject" , [
	{mark:"swfobject", uri: "../common/swfobject.js?20140530",type: "js"}
]);
ol.load.define("jquery.uploadify" , [
	"swfobject",
	{mark:"jquery.uploadify", uri: "../plugs/jquery.uploadify/uploadify.js?20140530",type:"js",depend:true},
	{uri: "../plugs/jquery.uploadify/uploadify.css?20140530",type:"css"}
]);*/
ol.load.define("ec.ui.pager" , [
	"ajax",
	{mark:"ec.ui.pager", uri: "../plugs/ec.pager/pager.js?20140530",type:"js"}
]);

ol.load.define("ec.ui.box" , [
	{mark:"ec.ui.box", uri: "../plugs/ec.box/box.js?20140530",type: "js", depend:true}
]);

ol.load.define("ec.ui.tip" , [
	{mark:"ec.ui.tip", uri: "../plugs/ec.tip/tip.js?20140530",type: "js", depend:true}
]);

ol.load.define("ec.ui.zoom" , [
	"ec.ui.box",
	{mark:"ec.ui.zoom", uri : "../plugs/ec.imgZoom/imgZoom.js?20140909" , type :"js"}
]);

ol.load.define("ec.ui.slider" , [
	{mark:"ec.ui.slider", uri : "../plugs/ec.slider/slider.js?20140530" , type :"js"}
]);

ol.load.define("ec.ui.autocomplete" , [
	"ajax",
	{mark:"ec.ui.autocomplete", uri: "../plugs/ec.autocomplete/autocomplete.js?20140530",type: "js"}
]);

ol.load.define("ec.ui.countdown" , [
	{mark:"ec.ui.countdown", uri: "../plugs/ec.countdown/countdown.js?20140710",type: "js"}
]);

/* 公共语言包 */
var lang = {
	"common" : {
		"sys_error" : "System Error!"
	},
	"us" : {
		"ajaxLogin_error" : "Invalid login or password!",
		"addToWish_error" : "This item was already in Wish List."
	},
	"br" : {
		"ajaxLogin_error" : "login ou senha são inválidos!",
		"addToWish_error" : "Este item já se encontra nos seus produtos guardados."
	},
	"de" : {
		"ajaxLogin_error" : "Ungültiger Benutzername oder Passwort!",
		"addToWish_error" : "Dieses Produkt wurde bereits in Wunschliste."
	},
	"es" : {
		"ajaxLogin_error" : "usuario o contraseña inválido!",
		"addToWish_error" : "Este artículo ya estaba en la lista de deseos."
	},
	"fr" : {
		"ajaxLogin_error" : "Invalide connexion ou mot de passe!",
		"addToWish_error" : "Cet article est déjà dans la liste de voeux- liste de voeux."
	},
	"it" : {
		"ajaxLogin_error" : "Invalid login or password!",
		"addToWish_error" : "Questo prodotto è già nella lista desideri."
	},
	"ru" : {
		"ajaxLogin_error" : "Invalid login or password!",
		"addToWish_error" : "Этот пункт был уже в списке пожеланий."
	}
};

ec.load('ajax');
ec.load('ec.ui.box');

/* 系统级错误提示 */
ec.checkApiStauts = function(json, obj) {
	var errorCode = json.errorCode;
	var tips = lang['common'];
	var showMsg = function() {
		var msg = json.detail || json.msg || tips[msg];
		if(obj){
			obj.html(msg).show();
		} else {
			ec.ui.box('<span class="red">'+ msg + '</span>',{width:250, height:40, showCancel: false}).open();
		}
	};

	if(errorCode !== 0) {
		if(gid('ol_load')) $('#ol_load').hide();
		logger.warn('errorCode:' + errorCode);

		showMsg();
		return false;
	}
	return true;
};
/*
//baidu分享
ec.ui.addShare = function(options){
	options = $.extend({
		//type : "tools",
		//lazy : true,
		jsUrl : "http://bdimg.share.baidu.com/static/api/js/share.js?v=86835285.js?cdnversion="+~(-new Date()/36e5)
	} , options);
	//document.write('<script type="text/javascript" id="bdshare_js" data="type='+options.type+'&amp;uid=4505950" ></s' + 'cript>');
	window._bd_share_config = options;
	ec.ready(function(){
		with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src=options.jsUrl];
	});

};
//相关统计
/*
ec.ui.addAnalytics = function(options){
	options = options || {
		google : true,
		doubleclick : true
	};
	//Google Tag Manager
	if(options.google){
		ec.load({url:"http://www.googletagmanager.com/gtm.js?id=GTM-PTWPVK",type:"js",loadType:"lazy"});
	}
	if(options.doubleclick) {
		_gaq.push(['_setAccount', 'UA-32553607-1']);
		_gaq.push(['_setDomainName', 'eachbuyer.com']);
		_gaq.push(['_trackPageview']);
		ec.load({url:"//stats.g.doubleclick.net/dc.js",type:"js",loadType:"lazy"});
	}
};
//在线客服 & 右侧工具条
ec.ui.addService = function(options){
	ec.load("jquery.float" , {
		loadType : "lazy",
		callback : function(){

			if(options.showService){
				var url = window.location.href;
				//$("#tools-nav-service-robotim").attr("href" , "http://www.eachbuyer.com?enterurl="+encodeURIComponent(url)).css('display','block');
				//$("#tools-nav-service-qq").css('display','block');
			}
			if(options.showTools){
				$("#tools-nav-survery").css('display','block');
			}
			if(options.showService || options.showTools) {
				$('#tools-nav')["float"]("mr").show();
			}
		}
	});
};
*/
//icon 提示
ec.ui.tip = function (selector, opt) {
	ec.load('ec.ui.tip',{
		loadType:"lazy",
		onload : function () {
			if(!selector) return;
			var $ele = $(selector);
			$ele.each(function (i, n) {
				$(n).hover(function () {
					$(n).tip(opt);
				}, function (){
					$('#ecTips').hide();
				});
			});
		}
	});
};

/**
 * 防止重复操作，设置操作间隔时间
 * @param  {Object} 	thix 	当前操作对象
 * @param  {int} 		s 		间隔时间，毫秒
 * @return {[bool]}     true || false
 */
ec.ui.eventTimeLimits = function (thix, s) {
	var $thix = $(thix);
	var ms = s || 3000; //间隔时间 毫秒
	var isBtn = $thix.hasClass('btn');
	if($thix.data('isDisabled')) return false;
	$thix.data('isDisabled', 'true');
	if(isBtn) $thix.addClass('time_out');
	setTimeout(function () {
		$thix.removeData('isDisabled');
		if(isBtn) $thix.removeClass('time_out');
	}, ms);
	return true;
};

//数字输入验证
ec.ui.number = function (selector, options){
	var defaultOpt = {
		max : null,
		min : null,
		showButton : true,
		isDisabled : true,
		minusBtn : '<a class="icon_minus" href="javascript:;"><span>-</span></a>',
		plusBtn : '<a class="icon_plus" href="javascript:;"><span>+</span></a>'
	};
	var thix = $(selector);
	var options = $.extend(defaultOpt, options);
	var _checkNumber = function(e) { //非法字符过滤
		var currentKey = e.which;
		var val = parseInt(this.value, 10);
		var thisVal = (val < 1) ? 1 : val;
		var limit = _getLimit(e);
		if((currentKey < 37 || currentKey > 40) && currentKey != 8 && currentKey != 46) {
			if(thisVal > limit.max || thisVal < limit.min) {
				e.preventDefault();
				return false;
			} else {
				if((currentKey<48 || currentKey>57) && (currentKey <96 || currentKey>105) && currentKey!=9) {
					e.preventDefault();
					return false;
				}
			}
		}

	};
	var _changClass = function (ele) {
		if(!options.isDisabled) return;
		var $thix = $(ele);
		var inputVal = parseInt($thix.val().trim(), 10);
		var $minBtn = $thix.prev('.icon_minus');
		var $maxBtn = $thix.next('.icon_plus');
		var limit = _getLimit($thix);
		$minBtn.toggleClass('disabled', (inputVal <= limit.min));
		$maxBtn.toggleClass('disabled', (inputVal >= limit.max));
	};
	var _getLimit = function (ele) {
		var $thix = $(ele);
		var max = $thix.data("max");
		var min = $thix.data("min");

		max = (max ? parseInt(max , 10) : options.max);
		min = (min ? parseInt(min , 10) : options.min);
		return {"max" : max, "min" : min};
	};


	thix.each(function () {
		if($(this).data('isuinumber')) return true;
		$(this).data('isuinumber', 'true');

		var opt = $.extend({}, options);
		var inputObj = $(this).css('ime-mode','disabled');

		if(opt.showButton) {
			//减少
			var minusBtn = $(opt.minusBtn).click(function(){
				var val= inputObj.val() || 0;
				var thisVal = parseInt(val , 10) -1;
				var limit = _getLimit(inputObj);

				if(typeof(limit.min) == "number" && thisVal < limit.min) {
					_changClass(inputObj);
					return;
				}
				inputObj.val(thisVal).trigger("blur");
			}),
			//增加
			plusBtn = $(opt.plusBtn).click(function(){
				var val= inputObj.val() || 0;
				var thisVal = parseInt(val , 10) +1;
				var limit = _getLimit(inputObj);

				if(typeof(limit.max) == "number" && thisVal > limit.max) {
					_changClass(inputObj);
					return;
				}
				inputObj.val(thisVal).trigger("blur");
			});
			inputObj.after(plusBtn).before(minusBtn);
			_changClass(inputObj);
		}
		inputObj.data("ovalue" , inputObj.val() || 0)
			.keydown(_checkNumber)
			.keyup(function () {
				var $thix = $(this);
				var thisVal = parseInt(this.value || 0);
				var limit = _getLimit(this);
				if(typeof(limit.min) == "number" && thisVal < limit.min) {
					this.value  = limit.min ;
					$thix.select();
				}else if(typeof(limit.max) == "number" && thisVal > limit.max) {
					this.value  = limit.max ;
					$thix.select();
				}
				if(opt.onkeyup && typeof opt.onkeyup === 'function'){
					opt.onkeyup.call(this);
				}
				_changClass(this);
			})
			.blur(function () {
				if(typeof opt.onchange === "function") {
					var oldVal = inputObj.data("ovalue"),
						newVal = this.value || 0,
						diff = parseInt(newVal , 10) -  parseInt(oldVal , 10);
					if(diff == 0)return;
					opt.onchange.call(this , newVal , diff);
					inputObj.data("ovalue" , newVal);
				}
				_changClass(this);
			});

	});

};



/**
 * 图片延时加载
 * @param  {String | jQuery Object} selector jQuery选择器
 * @return {[type]}          无
 */
ec.ui.lazyLoad = function(selector){
	var _window = $(window),
		_doc =  ol.isIE ? document.body : document.documentElement,
		_clientHeight, //可见区域高度
		_scrollTopSrart = 0, //网页卷上去的高度
		_scrollTopEnd = 0, //网页卷上去的高度+网页可见区域高度
		_imgList = [],
		_timer,

		_renderImg = function (img) {
			var top = img.offset().top;
			var pos = top + img.height();
			if((top >= _scrollTopSrart && top <= _scrollTopEnd) || (pos >= _scrollTopSrart && pos <= _scrollTopEnd))
			{
				img.attr("src" , img.attr("data-lazysrc"));
				img.removeAttr("data-lazysrc");
				return true;
			}
			return false;
		},

		_bindEvent = function () {
			var scrollEvent = function(){
				clearTimeout(_timer);
				_timer = setTimeout(function(){

					_scrollTopSrart = _window.scrollTop();
					_scrollTopEnd = _scrollTopSrart + _clientHeight;

					var img;

					for(var i = 0 ; i < _imgList.length ; i ++)	{
						img = _imgList[i];
						if(_renderImg(img)) {
							_imgList.splice(i , 1);
							i--;
						}
					}

					if(!_imgList || _imgList.length == 0)
					{
						window.onscroll = null;
						window.onresize = null;
					}

				} , 100);

			},

			resizeEvent = function(event) {
				_clientHeight = _doc.clientHeight;
			};

			window.onscroll = function (){ scrollEvent(); };
			window.onresize = function (){ resizeEvent(); };

			_clientHeight = _doc.clientHeight;
			_scrollTopSrart = _window.scrollTop();
			_scrollTopEnd = _scrollTopSrart + _clientHeight;

		};

	_bindEvent();//绑定事件

	$(selector).each(function(){
		var thix = $(this);
		if(thix.attr("data-lazysrc")) {

			if(!_renderImg(thix)) {
				if(!thix.attr("src")) {
					thix.attr("src" , "/images/common/other/default.png");
				}
				_imgList.push(thix);
			}
		}

	});
};

/**
 * ajax登录
 * @param  {String | jQuery Object} selector jQuery选择器
 * @return {[type]}          无
 */
(function () {
	ec.login = {
		afterReload_top : false, //控制顶部登录是否刷新页面
		afterReload_box : false, //控制弹框登录后是否刷新页面
		//获取模板
		init : function () {
			var me = ec.login;
			me.isLoginTplRender = ec.ui.template.compile(gid('isLoginTpl').innerHTML.trim());
			if(gid('loginBoxTpl')){
				me.loginBoxTpl = gid('loginBoxTpl').innerHTML.trim();
			}
		},
		//检测是否登录
		checkLogin : function (fn) {
			var isLogin = (ec.loginInfo && ec.loginInfo.user_name);
			if(isLogin && fn && ec.util.isFunction(fn)) {
				fn();
			} else {
				return isLogin;
			}
		},
		loginBox : function (reload) {
			if(!this.loginBoxTpl) return;
			this.callback = (typeof(reload) == 'function') ? reload : false;
			this.afterReload_box = (reload === true) ? true : false;
			var html = this.loginBoxTpl;
			ec.load('ec.ui.box',{
				onload : function () {
					//ec.util.cache.get('loginBox', function () {
						ec.login.box = ec.ui.box(html, {
							title : (ec.login.boxTitle || ""),
							width: 400,
							height: 320,
							showButton : false
						}).open();
					//}).open();
				}
			});
		},
		//保存用户登录信息并显示登录名
		setLoginInfoAndShow : function (userInfo) {
			var html = '';
			if(!userInfo) return;

			ec.loginInfo = (ec.loginInfo || {
				"user_name" : userInfo.user_name
			});

			html = ec.login.isLoginTplRender(ec.loginInfo);
			$('#ajaxLogin').html(html);
		},
		//ajax登录请求
		ajaxLogin : function (form) {
			/* ajax 登录 */
			var me = this;
			var tips = lang[ec.lang.globle];
			var $this = $(form);
			var $topLoginError = $('#topLoginError');
			var name = $('#userName').val().trim();
			var pwd = $('#passWord').val();
			var _showError = function (msg) {
				var msg = (tips[msg]) ? tips[msg] : msg;
				$topLoginError.html(msg).show();
			};
			$topLoginError.hide();
			if(!name || pwd.trim().length < 6) {
				_showError('ajaxLogin_error');
				return false;
			}
			new ec.ajax().post({
				url : '/login/authenticateApi',
				form : $this,
				loading: true,
				timeoutFunction : function () {
		        	log('ajaxLogin timeout');
		        },
				successFunction : function (json) {
					if(!ec.checkApiStauts(json, $topLoginError)) return;
					//是否刷新页面
					if(ec.login.afterReload_top) {
						location.reload();
					} else {
						if(!gid('cartPage')) ec.miniCart.init();
						ec.login.setLoginInfoAndShow(json.data);
						if(me.callback) me.callback();
					}
				},
				errorFunction : function (json) {
					ec.checkApiStauts(json, $topLoginError);
				}
			});
			return false;

		},
		//专用于顶部facebook登录
		fbLogin : function () {
			this.afterReload_box = this.afterReload_top;
			ec.fbLogin(ec.ajaxLoginCallback);
		}
	};
	//初始化登录实例
	$(document).ready(ec.login.init);
})();

/**
 * facebook登录组件
 * @param  {Function}  功能回调事件
 * @return {[type]}    无
 */
$(function(){

    // This is called with the results from from FB.getLoginStatus().
    function statusChangeCallback(response) {
        //log('statusChangeCallback');
        //console.log(response);
        // The response object is returned with a status field that lets the
        // app know the current login status of the person.
        // Full docs on the response object can be found in the documentation
        // for FB.getLoginStatus().
        if (response.status === 'connected') {
            // Logged into your app and Facebook.
            fbLoginCallback(response);
        } else if (response.status === 'not_authorized') {
            // The person is logged into Facebook, but not your app.
            log('Please log into this app.');
            //fbLoginCallback(response);
            //FB.login(fbLoginCallback,{scope:"email"});
        } else {
            // The person is not logged into Facebook, so we're not sure if
            // they are logged into this app or not.
            log('Please log into Facebook.');
            //FB.login(fbLoginCallback,{scope:"email"});
        }
    }


    window.fbAsyncInit = function() {
        FB.init({
            appId      : '1390176774575549',
            cookie     : true,  // enable cookies to allow the server to access 
                                // the session
            xfbml      : true,  // parse social plugins on this page
            version    : 'v2.0' // use version 2.0
        });

        // Now that we've initialized the JavaScript SDK, we call 
        // FB.getLoginStatus().  This function gets the state of the
        // person visiting this page and can return one of three states to
        // the callback you provide.  They can be:
        //
        // 1. Logged into your app ('connected')
        // 2. Logged into Facebook, but not your app ('not_authorized')
        // 3. Not logged into Facebook and can't tell if they are logged into
        //    your app or not.
        //
        // These three cases are handled in the callback function.
        /*
        FB.getLoginStatus(function(response) {
            statusChangeCallback(response);
        });
        */
    };

    var fbLoginCallback = function (response) {
        // Logged into your app and Facebook.
        log('Welcome!  Fetching your information.... ');
        FB.api('/me', function(response) {
            var name = response.name;
            var email = response.email;
            var id = response.id;
            var username = response.username || '';
            var cur_url = encodeURIComponent(location.href);
            var $ajaxLoginObj = $('#ajaxLogin');

            $.ajax({
                type : "POST",
                dataType: 'json',
                url: "/fb_login?act=login",
                data: {
                    facebook_email:email,
                    facebook_id:id,
                    facebook_username:username,
                    back_url:cur_url
                },
                success: function(json){

                    if(json.errorCode !== 0) {
                    	if(json.errorCode == 1){
                    		location.href = '/fb_login?act=binding';
                    		return;
                    	}
                    	alert('Login error!');
                        return;
                    }
                    ec.login.setLoginInfoAndShow(json.data);
                    if(FB.back_fn && ec.util.isFunction(FB.back_fn)) {
                        FB.back_fn(json.data);
                    }
                },
                error : function () {
                	alert('Login error!');
                    /*$ajaxLoginObj.html('<div class="login_loading">Login error!</div>');
                    setTimeout(function () {
                        $ajaxLoginObj.html(ec.ui.template('unLoginTpl'));
                    }, 1500);*/
                }
            });

        });
    };
    // Here we run a very simple test of the Graph API after login is
    // successful.  See statusChangeCallback() for when this call is made.
    ec.fbLogin = function (back_fn) {
        //var $ajaxLoginObj = $('#ajaxLogin');
        //$ajaxLoginObj.html('<div class="login_loading">Welcome, &nbsp;<img class="vam" src="/images/common/other/loading/16_16.gif" /></div>');
        FB.back_fn = back_fn;
        FB.login(statusChangeCallback,{scope:"email"});
    };
    ec.ajaxLoginCallback = function (json) {
		var isReload = ec.login.afterReload_box;
		if(isReload){
			location.reload();
		} else {
			if(!gid('cartPage')) ec.miniCart.init();
			$('#olMask, #olBox').hide();
			if(gid('cartContent') && ec.cart && $.isFunction(ec.cart.login_callback)){
				ec.cart.login_callback(json);
			}
		}
	};

    //checkLoginStatus
    if(ec.login.checkLogin()){
        ec.login.setLoginInfoAndShow(ec.loginInfo);
        return;
    };
    $('#ajaxLogin').html(ec.ui.template('unLoginTpl'));

    // Load the SDK asynchronously
    $(document).ready(function () {
    	var lang = {
    		"us" : "en_US",
    		"de" : "de_DE",
    		"es" : "es_ES",
    		"it" : "it_IT",
    		"fr" : "fr_CA",
    		"br" : "pt_BR",
    		"ru" : "ru_RU"
    	};
        if(!ec.debug) ec.loadApi("//connect.facebook.net/"+ lang[ec.lang.globle] +"/sdk.js#xfbml=1&version=v2.0", 'facebook-jssdk');
    });

});


/**
 * 获取迷你购物车数据的方法
 * @param  {}	无
 * @return {[type]} 	无
 */
(function () {
	var miniCart = function() {
		var self = this;
		this.tips = lang[ec.lang.globle];
		this.miniCartContent = null;
		this.tipsContent = null;
		//数字输入插件初始化
		/* this.checkNumber = function(obj) {
			var $obj = obj || '#miniCartContent .mini_cart_num';
			ec.ui.number($obj, {
				max : 9999,
				min : 1,
				onchange : function (newVal, diff) {
					self.updateQty.call(this, newVal, diff);
				}
			});
		};*/
	};
	miniCart.prototype = {
		countList : 0,
		cartTotal : 0,
		init : function () {
			var self = this;
			self.miniCartContent = $('#miniCartContent');
			self.tipsContent = $('#minCartTips');
			self.tplRender = ec.ui.template.compile($('#miniCartTpl').html().trim());
			self.getCartInfo();
			self.noItemTipsText = self.tipsContent.data('tip');
			/*
			$('#miniCart').hover(function(){
				if(self.setTimeout) clearTimeout(self.setTimeout);
			}, function () {
				$(this).removeClass('select_block_hover');
			});*/
		},
		reload : function() {
			var self = this;
			self.getCartInfo();
			/*
			var $miniCart = $('#miniCart');
			$miniCart.addClass('select_block_hover');
			self.setTimeout = setTimeout(function(){
				self.miniCartContent.slideUp("fast", function (){
					self.miniCartContent.show();
					$miniCart.removeClass('select_block_hover');
				});
			}, 1200);
			*/
		},
		showMsg : function (msg) {
			this.tipsContent.html(msg).show();
			this.miniCartContent.hide();
		},
		showList : function (html) {
			this.tipsContent.hide();
			this.miniCartContent.html(html).show();
		},
		getCartInfo : function () {
			//获取购物车数据
			var self = this;
			$.get('/cart/getTopCart?_'+ (new Date()).getTime(), function (json) {
				if(json.errorCode != 0) {
					logger.warn('ajax load error : \/common\/cartAjax');
					self.showMsg(json.msg || self.noItemTipsText);
					return;
				}
				var len = (json.data.list) ? json.data.list.length : 0;
				var html = '';
				if(len < 1) {
					self.showMsg(self.noItemTipsText);
					return;
				}
				html = self.tplRender(json.data);
				self.showList(html);
				self.updateCount();
			}, 'json');
		},
		updateCount : function () {
			var count = 0;
			var $numInput = $('#miniCartContent').find('.cart_num');
			$numInput.each(function (i, n) {
				count = count + parseInt($(n).html().trim());
			});
			this.cartTotal = count;
			$('#cartTotal').html(count);
		}
		/*,updateQty : function (newVal, diff) {
			var $obj = $(this);
			var pid = $obj.data('pid');
			new ec.ajax().get({
				url: '/common/cartAjax?action=updateCartQty',
				data : {
					"goods_qty" : newVal,
					"pid" : pid
				},
				cache : false,
				successFunction: function (json) {
					if(!ec.checkApiStauts(json)) return;

					var number = parseInt(json.data.goods_number);
					if(number != newVal) {
						$obj.val(number);
						setTimeout(function () {
							$obj.data('ovalue', number);
						}, 300);
						return;
					}
					$('#miniCartPrice_'+rec_id).html(json.data.subtotal);
					ec.miniCart.updateCount();
				}
			});
		},
		//删除操作
		del : function (id) {
			var self = this;
			if(self.countList < 1) return;
			new ec.ajax().get({
				url: '/common/cartAjax?action=removeItem',
				data : { rec_id : id },
				cache : false,
				successFunction: function (json) {
					if(!ec.checkApiStauts(json)) return;

					if(self.countList <= 1){
						self.showMsg(self.tips['minCart_noItem']);
					}
					$('#miniCartList_'+ id).remove();
					self.countList--;
					self.updateCount();
				}
			});
		}*/
	};
	ec.miniCart = new miniCart();
})();


/**
 * [货币转换]
 * @return {[type]} [description]
 */
ec.ui.searchCountry = function () {
	var currencyRates = $.parseJSON(ec.cookie.get('currencyRates'));
	var currencyRates;
	var url = window.location.href;
	var $guoqiList = $('#guoqiList');
	var $list = $guoqiList.html();
	var guoqiList = $('#all_currency').val().trim().split(',');

	if(!currencyRates) {
		$.ajax({
			type:"post",       //http请求方法,默认:"post"
			url:"/static_js/currency.php",   //发送请求的地址
			dataType:"json",   //预期服务器返回的数据类型
			success: function (res) {
				currencyRates = res.rates;
				ec.cookie.set('currencyRates', ec.util.stringify(currencyRates));
			}
		});
	}

	ec.currency = function (currencyType) {
		// cookie 保留货币种类
		//ec.cookie.set('currencyTypeNew', currencyType.toUpperCase());
		$.ajax({
			url: '/ajax/changeCurrency',
			type: 'post',
			data: {currency: currencyType,curUrl: window.location.href},
			dataType: 'json',
			success: function(res){
				if( res.errorCode == 0 ){
					window.location.href = res.data.url;
				}else{
					window.location.reload();
				}
			}
		})
	};

	/* 自动查找货币列表 */
	$('#searchCurrency').on('keyup', function () {
		var val = $(this).val().trim();
		var html = '';
		var text = '';
		if(!val) {
			$guoqiList.html($list);
			return;
		}
		for(var i = 0; i < guoqiList.length; i += 1) {
			text = guoqiList[i];
			if(text.indexOf(val.toUpperCase()) > -1) {
				html = '<a href="javascript:;" onclick="ec.currency(\''+ text +'\');" rel="nofollow" class="tab_'+ text +'">';
				html += '<i class="icon_guoqi tab_'+ text +'"></i><span>'+ text +'</span>';
				html += '</a>';
			}
		}
		$guoqiList.html(html);
	});
};


//声明product对象
ec.pkg('ec.product');
/**
 * [添加到愿望清单]
 * @param {object} 		ele       		当前触发事件的操作对象
 * @param {int} 		id           	productId
 * @param {[booler]} 	showLoginBox 	未登录状态是否自动弹出登录框，要事先载入登录框模块
 * @param {[string]} 	leftOrRight 	定位，左对齐还是右对齐
 */
ec.product.addToWishList = function(ele, pid, opt) {
	var $thix = $(ele);
	var okClass = 'addToWishlistOK';
	var _default = {
		width : 200,
		showLoginBox : true,
		"msg" : lang[ec.lang.globle].addToWish_error
	};
	/*var _callback = function (tip) {
		var $this = tip;
		$this.find('.ec_tips_msg').html(json.msg);
		setTimeout(function () {$this.hide();}, 1200);
	};
	//opt.callback = _callback;*/
	opt = $.extend(_default, opt);

	//防止恶意点击
	if(!pid || !ec.ui.eventTimeLimits($thix)) return;
	if($thix.hasClass(okClass)){
		$thix.tip(opt);
		return;
	}

	new ec.ajax().post({
		url : '/common/addToWishlist',
		data : {"pid" : pid},
		loading: true,
		timeoutFunction : function () {
        	log('ajaxLogin timeout');
        },
		successFunction : function (json) {
			if(json.errorCode !== 0 && json.data && json.data.noLogin === true && opt.showLoginBox === true) {
				ec.login.loginBox(function () {
					$thix.addClass(okClass);
				});
				return;
			}
			$thix.addClass(okClass);
		}
	});
};


//关键字搜索自动完成插件
ec.ui.keywordAutocomplete = function () {
	ec.load('ec.ui.autocomplete', {
		loadType:"lazy",
		onload : function () {
			ec.ui.autocomplete($('#keywords'), {
				//form : $('#searchForm'),
				url : '/includes/modules/search/suggest.php',
				className : 'ol_autocomplete',
				callback : function () {
					$('#searchForm').submit();
				}
			});
		}
	});
};


//获取搜索关键字时左边的分类列表
ec.ui.getSearchCatgray = function () {
	var html = '';
	var level_name = '';
	var level_id = 0;
	var $selectCategory = $('#selectCategory');
	var $searchTypeValue = $('#searchTypeValue');
	$('#categrayAll .list').each(function (i, n) {
		var $this = $(n);
		var liName = null;
		var $thisSubList = $this.find('.cate_sub_list li.level1');
		var subListLen = $thisSubList.length;
		liName = $this.find('.li a');
		level_name = liName.html();
		level_id = $this.data('id');
		html += '<li data-id="'+ level_id +'" class="category_level_0"><a href="javascript:;">'+ level_name +'</a></li>';
		if(subListLen > 0) {
			for(var i = 0; i < subListLen; i += 1) {
				level_id = $thisSubList.eq(i).data('id');
				level_name = $thisSubList.eq(i).children('a').html();
				html += '<li data-id="'+ level_id +'" class="category_level_1"><a href="javascript:;">'+ level_name +'</a></li>';
			}
		}
	});
	//输出列表
	$('#searchType').append(html);
	//绑定事件
	$('#searchType li').on('click', function () {
		var id = $(this).data('id');
		$selectCategory.val(id);
		$searchTypeValue.html($(this).text().trim());
		$(this).closest('.select_block').removeClass('select_block_hover');
		$(this).closest('.drop_box').hide();
	});

	//选择搜索分类
	$('#searchTypeSelect .select_block').hover(function(){
		$(this).addClass('select_block_hover').find('.drop_box').show();
		$('#keywords').blur();
	}, function () {
		$(this).removeClass('select_block_hover').find('.drop_box').hide();;
	});
};



//topbar部份
ec.topbarInit = function () {

	if(!gid('topbar')) return;

	//货币转换
	ec.ui.searchCountry();

	//初始化购物车
	if(!gid('cartPage')) ec.miniCart.init();

	//顶部下拉动画效果
	$('#topbar .select_block').on('mouseout', function () {
		$(this).find('input').blur();
	});
};

//topbar部份
ec.headerInit = function () {

    if(!gid('pageNav')) return;
    var setTimeoutHover = null;
    //分类展示效果
    var widths = [0, 275, 510, 760, 980]; //宽度设置
    $('#categrayAll .list').hover(function() {
        if(setTimeoutHover !== null) clearTimeout(setTimeoutHover);
        var $thix = $(this);
        setTimeoutHover = setTimeout(function () {
            var $thisSubList = $thix.children('.cate_sub_list');
            var $col = $thisSubList.find('.column');
            var colLen = $col.size();
            var w = widths[colLen];

            if(colLen < 1) return;
            $thisSubList.width(w).find('.cate_sub_padding').width(w - 40);
            $thix.addClass('li_hover');
        }, 200);
    }, function() {
    	if(setTimeoutHover !== null) clearTimeout(setTimeoutHover);
        var $thix = $(this);
        var $thisSubList = $(this).children('.cate_sub_list');
        setTimeout(function (){
            $thix.removeClass('li_hover');
            $thisSubList.width(0);
        }, 50);
    });

	//搜索关键字检测
	var $keywords = $('#keywords');
	var keywordsVal = $keywords.data('value');
	$('#searchForm').submit(function(){
		keywordsVal = $.trim($keywords.val()) || $.trim(keywordsVal);
		if(!keywordsVal) {
			return false;
		}
		$keywords.val( keywordsVal );
	});
	ec.form.tips.label($keywords);

	//关键字搜索左边的分类列表
	ec.ui.getSearchCatgray();
	//关键字搜索自动完成插件
	ec.ui.keywordAutocomplete();

};
/* 返回顶部按钮  */
ec.ui.tools = function () {

	var $toTop = $('#gotop');

	if(!$toTop[0]) return;

	$(window).scroll(function(){
		t = $(document).scrollTop();
		if(t > 50){
			$toTop.fadeIn('slow');
		}else{
			$toTop.fadeOut('slow');
		}
	})
	$toTop.click(function(){
		ec.ui.scrollTo(0);
	})
};


/* footer 相关功能 */
ec.footerInit = function () {

	if(!gid('footerSubmit')) return;

	ec.form.tips.label('#footEnterInputText');
	$('#footerSubmit').submit(function(){
		var val = $('#footEnterInputText').val().trim();
		var $errorObj = $('#footerSubmitMsg');
		var $errorMsg = $errorObj.children('p');
		var $errorEmpty = $errorObj.children('span');
		var emailReg = ec.form.regex.email;

		$errorMsg.hide();
		$errorEmpty.hide();
		if(!val){
			$errorEmpty.show()
			$errorMsg.hide();
			return false;
		}
		if(!val.match(emailReg)) {
			$errorEmpty.hide()
			$errorMsg.show();
			return false;
		}
		dataLayer.push({'buttonaction': 'Sub_NL_footer','event': 'buttonclick'});
		return true;
	});
};

//加载头部相关功能
$(function (){
	ec.topbarInit();
	ec.headerInit();
});

$(document).ready(function(){
	//图片延时加载
	ec.ui.lazyLoad($('body .main').find("img"));

	ec.footerInit();
	//初始化右侧工具条
	ec.ui.tools();


	ec.ui.tip('.icon_help', {
		position : 'right',
		autoHide : false
	});
});



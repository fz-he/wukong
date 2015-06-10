<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| Application Config
| -------------------------------------------------------------------
| This file stay same in different environment.
| version control by svn,same as other code.
|
*/
//public
date_default_timezone_set('Asia/Shanghai');
define('SQL_EXECUTE_RETAIN_CONDITION', false);
define('ENABLE_CHECKREFER', True );
define('NOW', date( 'Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) );
define('TODAY', date( 'Y-m-d', $_SERVER['REQUEST_TIME']) );

define('COMMON_META_KEYWORDS',', Eachbuyer, ' . ucfirst( COMMON_DOMAIN ) .' ');

define('PATH_APPLICATION',ROOT_PATH.'application/');
define('PATH_CONTROLLER',PATH_APPLICATION.'controllers/');
define('PATH_VIEW',PATH_APPLICATION.'views/');

//memcache
//common
define('CACHE_TIME_COMMON_HOUR',3600 );//1 hour
define('CACHE_TIME_COMMON_MINUTE',60 ); //1minute
////home
define('CACHE_TIME_SHOP_CONFIG',72000);
define('CACHE_TIME_IMAGE_AD',36000);
define('CACHE_TIME_ALL_CATEGORY',3600);
// define('CACHE_TIME_ALL_CATEGORY',1);
define('CACHE_TIME_FOOTER_TAG',3600);
define('CACHE_TIME_HOME_RECENTLY_REVIEW',1800);
define('CACHE_TIME_HOME_SPECIALNEW_GOODS_RECOMMEND_TAB_TITLE',36000);
define('CACHE_TIME_HOME_SPECIALNEW_GOODS_RECOMMEND_LIST',3600);
define('CACHE_TIME_HOME_KEYWORD_RECOMMEND_LIST',36000);
define('CACHE_TIME_HOME_CATEGORY_KEYWORD_RECOMMEND_LIST',36000);
////goods
define('CACHE_TIME_GOODS_REVIEW_LIST',1800);
define('CACHE_TIME_CATEGORY_KEYWORDS',3600);
define('CACHE_TIME_CATEGORY_BEST_SALE_ACTIVE_GOODS',3600);
define('CACHE_TIME_ACTIVE_GOODS_BY_CATEGORY',3600);
define('CACHE_TIME_CATEGORY_TEMPLATE',36000);
define('CACHE_TIME_CATEGORY_INFO',36000);
define('CACHE_TIME_GOODS_GALLERY_LIST',1800);
define('CACHE_TIME_PROS_INFO',1800);
////category
define('CACHE_TIME_CATEGORY_BANNER_AD',7200);
define('CACHE_TIME_CHILD_CATEGORY_IDS',1800);
define('CACHE_TIME_CATEGORY_LIST_BY_CATEGORY',7200);
define('CACHE_TIME_CATEGORY_GOODS_PRICE_BOUNDARY',1800);
//account
define('CACHE_TIME_ALL_COUNTRY_REGION',36000);
//cart
define('CACHE_TIME_ATTRIBUTE_AND_VALUE_BY_GOODS',3600);

//page count
define('PAGE_COUNT_CATEGORY', 40);
define('PAGE_COUNT_CATEGORY_PROMOTE', 36);
define('COUNT_CATEGORY_TEMPLATE_MASTER_SHOW', 5);

//mobile category template
define('MOBILE_PAGE_COUNT_CATEGORY', 12 );
define('CATEGORY_TEMPLATE_MASTER',1);
define('CATEGORY_TEMPLATE_SUBCATEGORY',2);
define('CATEGORY_TEMPLATE_PRODUCT',3);

define('OOS_WAIT',                  0); // 等待货物备齐后再发
define('OOS_CANCEL',                1); // 取消订单
define('OOS_CONSULT',               2); // 与店主协商

/* 用户中心留言类型 */
define('M_MESSAGE',                 0); // 留言
define('M_COMPLAINT',               1); // 投诉
define('M_ENQUIRY',                 2); // 询问
define('M_CUSTOME',                 3); // 售后
define('M_BUY',                     4); // 求购
define('M_BUSINESS',                5); // 商家
define('M_COMMENT',                 6); // 评论

//产品上下架
define('GOODS_IS_ON_SALE', 1); //产品上架
define('GOODS_NOT_ON_SALE', 0); //产品下架

define('CART_GENERAL_GOODS',        0); // 普通商品
define('CART_AUCTION_GOODS',        1); // 竞拍商品
define('CART_VOUCHER_GOODS',        2); // 代金券商品
define('CART_LIMIT_BUY_GOODS',      3); // 限时抢购商品
define('CART_GROUP_BUY_GOODS',      4); // 团购商品
define('CART_GIFT_GOODS',           5); // 促销商品（赠品）

/* 订单状态 */
define('OS_UNCONFIRMED',            0); // 未确认
define('OS_CONFIRMED',              1); // 已确认
define('OS_CANCELED',               2); // 已取消
define('OS_INVALID',                3); // 无效
define('OS_RETURNED',               4); // 退货
define('OS_SPLITED',                5); // 已分单
define('OS_SPLITING_PART',          6); // 部分分单

/* 配送状态 */
define('SS_UNSHIPPED',              0); // 未发货
define('SS_SHIPPED',                1); // 已发货
define('SS_RECEIVED',               2); // 已收货
define('SS_PREPARING',              3); // 备货中
define('SS_SHIPPED_PART',           4); // 已发货(部分商品)
define('SS_SHIPPED_ING',            5); // 发货中(处理分单)
define('OS_SHIPPED_PART',           6); // 已发货(部分商品)

/* 支付状态 */
define('PS_UNPAID',                0); // 未付款
define('PS_PAYING',                 1); // 付款中
define('PS_PAID',                  2); // 已付款
define('PS_REFUND',                  3); // 退款

//newsletter subscribe source
define('NEWSLETTER_SUBSCRIBE_SOURCE_OTHER',0);
define('NEWSLETTER_SUBSCRIBE_SOURCE_EBAY',1);
define('NEWSLETTER_SUBSCRIBE_SOURCE_SIDEBAR',2);
define('NEWSLETTER_SUBSCRIBE_SOURCE_ACCOUNT',3);
define('NEWSLETTER_SUBSCRIBE_SOURCE_REGISTER',4);
define('NEWSLETTER_SUBSCRIBE_SOURCE_AUCTION',5);
define('NEWSLETTER_SUBSCRIBE_SOURCE_FOOTER',6);
define('NEWSLETTER_SUBSCRIBE_SOURCE_EBAY_ORDER',7);
define('NEWSLETTER_SUBSCRIBE_SOURCE_POPUP',8);
define('NEWSLETTER_SUBSCRIBE_SOURCE_ORDER_SUCCESS',9);
define('NEWSLETTER_SUBSCRIBE_SOURCE_ORDER_SUCCESS_AUTO',11);

//The coupon code display in detail page.
define('DETAIL_PAGE_COUPON_CODE', 'EBCSE');

//special topic page limit
define('SPECIAL_PAGE_LIMIT', 15);

// $display_coupon_banner_sku_Arr = array('CD92','HH41','XX002444','HF11','XX002446','HH19','C181','R571','B246','ZH09','IG87','IA29','I416','I672','ID44','HH21','I794','XX001409','C975','HS32','CQ91','CA62','BX97','HV76','BM39');
$display_coupon_banner_sku_Arr = array();

$warehouse_local = array('GZ','HK');
$cn_mail_citys = array('guangzhou','shenzhen');

//source and time webgains
$c_webgains_sourcetotime = array(
	'47947'	=> 86400,
);

$private_css = array(
	'mobile' => array(
	),
);

/*New view template config.*/
define('NEW_TPL', 'newTpl');//Set the new view tpl path.

$newTplPage = array('home', 'category','search', 'buy', 'page_not_found', 'goods', 'only_header', 'only_footer');//Set the page which used the new tpl.
$activityPage = array('flashsale','flashsale_detail', 'promote', 'promote_detail', 'promote_preview');//Activity pages
$testPage = array(
	'cart', 'placeorder',
	'login', 'forgot_password',
	'about_us', 'terms_and_conditions','privacy_policy',
	'contact_us', 'faq', 'payment_method', 'shipping_method_guide', 'return_policy',
	'affiliate_program', 'wholesale',
	'atoz', 'topbrands',
	'point_rules',
	'repay',
	'promotion',
	'newsletter_public', 'newsletter_result',
	'impressum',
	'bbs_user','bbs_user_account','bbs_user_information','bbs_user_spread','bbs_user_success','fb_login'
);
$pagesUsedNewTpl = array_merge($newTplPage, $activityPage, $testPage);

//Dragon Boat Festival 端午活动详情页
$goods_dragon_boat_festival_coupon = array(
	'sku_coupon' => array (
		'W478' => 'LIW4FR' ,
		'N478' => 'JENNFR' ,
		'ME66' => 'WAMEFR' ,
		'CA55' => 'SOCAFR' ,
		'IS92' => 'HGSIFR' ,
		'CL19' => 'CNCLFR' ,
		'R474' => 'WAR4FR' ,
		'IS82' => 'HGISFR' ,
		'BH271' => 'TOBHFR' ,
		'HC96' => 'HOHCFR' ,
		'WG51' => 'SOWGFR' ,
		'L642091' => 'JEL6FR' ,
		'RU15' => 'TORUFR' ,
		'C795' => 'SOC7FR' ,
	),
	'time' => array(
		'start' => strtotime( '2014-06-02 00:00:00' ),
		'end' => strtotime( '2014-06-06 23:59:59' ),
	),
);


/**
 * 如果定义简单类型的常量，用const，  数组用static $XXX
 */
class AppConfig{
	//海外仓库
	static $warehouse_oversea = array('US','AU','DE','ES','UK');
	//海外仓库
	static $language2warehouse = array(
		1 => array('GZ','HK','US','AU','UK'),
		2 => array('GZ','HK','DE'),
		3 => array('GZ','HK','ES'),
		4 => array('GZ','HK'),
		5 => array('GZ','HK'),
		6 => array('GZ','HK'),
		7 => array('GZ','HK'),
	);
	//用户的级别
	static $user_rank = array(
		1 => array('min_points'=>0,'max_points'=>99,'discount'=>0),
		2 => array('min_points'=>100,'max_points'=>299,'discount'=>2),
		3 => array('min_points'=>300,'max_points'=>999,'discount'=>5),
		4 => array('min_points'=>1000,'max_points'=>2999,'discount'=>7),
		5 => array('min_points'=>3000,'max_points'=>10000,'discount'=>8),
	);
	//促销类型
	static $proTypeTextArr = array(
		0 => '普通',
		1 => '折扣',
		4 => '捆绑',
		5 => '捆绑+折扣',
		32 => '秒杀',
		33 => '秒杀预告+折扣',
		36 => '秒杀预告+捆绑',
		37 => '秒杀预告+捆绑+折扣',
		4001 => '被捆绑商品',
		55555 => 'Coupon赠品',
	);
	//广告位置 描述以及某一个位置对于的个数  'position' 很重要 有校验滴
	static $adLocationList = array(
		1 => array('name' => '首页轮播','position' => 4,'width' => 750,'height' => 265),
		2 => array('name' => '首页轮播下侧','position' => 4,'width' => 183,'height' => 78),
		3 => array('name' => '首页轮播右侧','position' => 1,'width' => 212,'height' => 348),
		4 => array('name' => '首页横断','position' => 1,'width' => 970,'height' => 0),
		5 => array('name' => '首页左侧1','position' => 1,'width' => 220,'height' => 0),
		6 => array('name' => '首页左侧2','position' => 10,'width' => 220,'height' => 0),
		7 => array('name' => '全站顶通','position' => 1,'width' => 648,'height' => 58),
		8 => array('name' => '个人中心首页','position' => 1,'width' => 965,'height' => 0),
		9 => array('name' => '个人中心订单列表','position' => 1,'width' => 965,'height' => 0),
		10 => array('name' => '个人中心订单详情','position' => 1,'width' => 965,'height' => 0),
		11 => array('name' => '登录/注册页','position' => 1,'width' => 516,'height' => 155),
		12 => array('name' => '下单成功页','position' => 1,'width' => 220,'height' => 0),
		13 => array('name' => 'FlashSale页','position' => 4,'width' => 1000,'height' => 0),
		14 => array('name' => '移动端首页轮播','position' => 4,'width' => 750,'height' => 265),
		15 => array('name' => '移动端首页轮播下侧','position' => 4,'width' => 0,'height' => 0),
	);
}

/* End of file config_application.php */
/* Location: ./application/config/config_application.php */
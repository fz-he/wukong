<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

// common
$route['default_controller'] = 'home';
$route['404_override'] = 'page_not_found';
$route['reviews/(.*)-p([0-9]+).html'] = "reviews/index/$2";
$route['zt/(:num)'] = "zt/index/$1";
$route['buy-(.*).html'] = "buy/index/$1" ;
$route['(.*)-p([0-9]+).html'] = "goods/index/$2";
$route['FreeOrder'] = 'free_order'; //金良 - 2014/03/22
$route['freeorder'] = 'free_order'; //金良 - 2014/03/22 - 不知道怎么兼容大小写
$route['feeddata/([a-z]+)?/(.*)?'] = 'feed_data/$1/$2';//金良 - 2014/09/24
$route['freeorder-id([0-9]+)'] = 'free_order/reward/$1';
$route['crontabs/([a-z]+)?/(.*)?'] = 'crontabs/$1/$2';  //定时任务的WEB入口 [//(192.168.1.120=>)  /usr/local/php/bin/php  /home/jinliang/new_eachbuyer/index.php crontabs hello "jinliang"]
//ns 分类处理
$route['ns/(.*)-c([0-9]+)'] = "category/index/$2";
$route['ns/(.*)-c([0-9]+)\/(.*)\/([0-9]+).html'] = "category/index/$2/$4";
$route['ns/(.*)-c([0-9]+)\/(.*)'] = "category/index/$2";

$route['(.*)-c([0-9]+)'] = "category/index/$2";
$route['(.*)_c([0-9]+)'] = "category/index/$2";
$route['(.*)-c([0-9]+).html'] = "category/index/$2";
$route['(.*)_c([0-9]+).html'] = "category/index/$2";
$route['(.*)-c([0-9]+)\/([0-9]+).html'] = "category/index/$2/$3";
$route['(.*)_c([0-9]+)\/([0-9]+).html'] = "category/index/$2/$3";

$route['order_list/(:num)'] = "order_list/index/$1";
$route['order_detail/(:num)'] = "order_detail/index/$1";
$route['review_list/(:num)'] = "review_list/index/$1";
$route['point_list/(:num)'] = "point_list/index/$1";
$route['repay/(:num)'] = "repay/index/$1";
$route['([A-Z]).html'] = "atoz/index/$1";
$route['0_9.html'] = "atoz/index/0-9";
$route['0-9.html'] = "atoz/index/0-9";
$route['([A-Z])_([0-9]+).html'] = "atoz/index/$1/$2";
$route['0_9_([0-9]+).html'] = "atoz/index/0-9/$1";
$route['0-9_([0-9]+).html'] = "atoz/index/0-9/$1";
$route['about_us.html'] = 'about_us';
$route['fb_login'] = "fb_login/index";
$route['contact_us.html'] = 'contact_us';
$route['faq.html'] = 'faq';
$route['affiliate_program.html'] = 'affiliate_program';
$route['terms_and_conditions.html'] = 'terms_and_conditions';
$route['privacy_policy.html'] = 'privacy_policy';
$route['shipping_method_guide.html'] = 'shipping_method_guide';
$route['return_policy.html'] = 'return_policy';
$route['wholesale.html'] = 'wholesale';
$route['payment_method.html'] = 'payment_method';
$route['topbrands.html'] = 'topbrands';
$route['promotion.html'] = 'promotion';
$route['impressum.html'] = 'impressum';

//移动促销模板
$route['(.*)promote_flashsale-m([0-9]+).html'] = "promote_flashsale/index/$2";
//促销模板
$route['(.*)-m([0-9]+).html'] = "promote/index/$2";
$route['(.*)-m([0-9]+)-mc([0-9]+).html'] = "promote_detail/index/$2/$3";
$route['(.*)-m([0-9]+)-mc([0-9]+)-([0-9]+).html'] = "promote_detail/index/$2/$3/$4";


// only default
if(SITE_CODE == 'default') {
}
/* End of file routes.php */
/* Location: ./application/config/routes.php */

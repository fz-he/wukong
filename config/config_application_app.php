<?php
namespace app\config;

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
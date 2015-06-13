<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * Model for advertisement.
 * @author BRYAN . NING
 */
class AdModel extends CI_Model {

	const STATUS_ENABLED = 1; //状态:启用
	const MEM_KEY_AD_INFO = 'ad_info_%s';//ad_info_{$location}  广告信息的memcache缓存key

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 获取实例化
	 * @return AdModel
	 */
	public static function & getInstanceObj( ){
		return parent::_getBaseInstanceObj( __CLASS__ );
	}

	/**
	 * 根据广告位的ID 获取广告的详细信息
	 * @param int $location //广告的位置
	 *		当$location为1 => array('name' => '首页轮播','position' => 4,'width' => 750,'height' => 265),
	 *		当$location为2 => array('name' => '首页轮播下侧','position' => 4,'width' => 183,'height' => 78),
	 *		当$location为3 => array('name' => '首页轮播右侧','position' => 1,'width' => 212,'height' => 348),
	 *		当$location为4 => array('name' => '首页横断','position' => 1,'width' => 970,'height' => 0),
	 *		当$location为5 => array('name' => '首页左侧1','position' => 1,'width' => 220,'height' => 0),
	 *		当$location为6 => array('name' => '首页左侧2','position' => 10,'width' => 220,'height' => 0),
	 *		当$location为7 => array('name' => '全站顶通','position' => 1,'width' => 648,'height' => 58),
	 *		当$location为8 => array('name' => '个人中心首页','position' => 1,'width' => 965,'height' => 0),
	 *		当$location为9 => array('name' => '个人中心订单列表','position' => 1,'width' => 965,'height' => 0),
	 *		当$location为10 => array('name' => '个人中心订单详情','position' => 1,'width' => 965,'height' => 0),
	 *		当$location为11 => array('name' => '登录/注册页','position' => 1,'width' => 516,'height' => 155),
	 *		当$location为12 => array('name' => '下单成功页','position' => 1,'width' => 220,'height' => 0),
	 *		当$location为13 => array('name' => 'FlashSale页','position' => 4,'width' => 1000,'height' => 0),
	 *		当$location为14 => array('name' => '移动端首页轮播','position' => 4,'width' => 750,'height' => 265),
	 *		当$location为15 => array('name' => '移动端首页轮播下侧','position' => 4,'width' => 0,'height' => 0),
	 * @param int $langId //当前的语言 默认英语
	 *
	 * @return array array()
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getAdInfoById( $locationId , $langId=1 ){
		//最后返回数据变量
		$result = array();
		//格式化参数
		$locationId = (int)$locationId;
		//获取配置项中的位置信息
		$locationConfig = AppConfig::$adLocationList;
		if( isset( $locationConfig[ $locationId ]['position'] ) && is_array( $locationConfig[ $locationId ] ) ){
			//此广告位置下的广告个数
			$locationConfigCount = (int)$locationConfig[ $locationId ]['position'] ;
			//获取广告的数据
			$adInfos = $this->_getAdInfoDbById( $locationId );
			//判断 此广告的信息 是否不为空
			if( !empty( $adInfos ) && is_array( $adInfos )){
				//获取当前时间戳
				$dateTime = date( 'Y-m-d H:i:s' , HelpOther::requestTime() ) ;
				$i = 0 ;
				foreach ( $adInfos as $info ){
					if( ( $dateTime >= $info['start_time'] ) && ( $dateTime < $info['end_time'] ) ){
						//.com 替换为.net 跨站处理
						if( defined( 'EBPLATEFORM' ) && ( EBPLATEFORM === 2 ) ){
							$info['content'] = str_replace( 'eachbuyer.com' , 'eachbuyer.net' , $info['content'] );
						}
						//把json 转化为数组
						$contentTmp = json_decode( $info['content'] , TRUE );
						$contentTmp = isset( $contentTmp[ $langId ] ) ? $contentTmp[ $langId ] : array();
						if( !isset( $result[ (int)$info['position'] ] ) && ( $i < $locationConfigCount ) ){
							//格式化数据
							$result[ (int)$info['position'] ] = array(
								'id' => (int)$info['id']  ,
								'position' => (int)$info['position'] ,
								'img' => isset( $contentTmp['img'] ) ? HelpUrl::imgSite( $contentTmp['img'] ) : '' ,
								'url' => isset( $contentTmp['url'] ) ? trim( $contentTmp['url'] ) : '' ,
								'alt' => isset( $contentTmp['alt'] ) ? htmlspecialchars( trim( $contentTmp['alt'] ) , ENT_QUOTES ) : '' ,
								'start_time' => trim( $info['start_time'] ),
								'end_time' => trim( $info['end_time'] ),
							);
							$i++;
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * 根据广告的位置从数据库mysql中获取数据信息
	 *  @param int $location //广告的位置
	 *
	 * @return array array()
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	private function _getAdInfoDbById( $locationId ){
		//最后返回数据变量
		$result = array();
		//MC key
		$cacheKey = self::MEM_KEY_AD_INFO ;
		//MCkey 中的变量
		$cacheParams = array( $locationId );
		//获取数据
		$result = $this->memcache->get( $cacheKey , $cacheParams );
		//mc没有数据 从数据库抓取
		if( $result === false ) {
			$this->db_ebmaster_read->select('`id` , `position` , `content` , `start_time` , `end_time`');
			$this->db_ebmaster_read->from('ad');
			$this->db_ebmaster_read->where( 'location' , $locationId );
			$this->db_ebmaster_read->where( 'status' , self::STATUS_ENABLED );
			$this->db_ebmaster_read->where( 'end_time >' , date( 'Y-m-d H:i:s' , HelpOther::requestTime() ) );
			$this->db_ebmaster_read->order_by( 'position', 'asc' );
			$result = $this->db_ebmaster_read->get()->result_array();
			$this->memcache->set( $cacheKey , $result , $cacheParams );
		}
		return $result;
	}
}


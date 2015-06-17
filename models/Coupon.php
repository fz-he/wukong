<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Model for Coupon.
 * @author BRYAN - NYD  <ningyandong@hofan.cn>
 */
class CouponModel extends CI_Model {


	const SUBSCRIBE_CODE_END_DAY = '2015-09-30 23:59:59';

	protected $_couponMessage = false;
	protected $_couponCode = '';
	/**
	 * 获取实例化
	 * @return CouponModel
	 */
	public static function & getInstanceObj( ){
		return parent::_getBaseInstanceObj( __CLASS__ );
	}

	public function __construct(){
		parent::__construct();
	}

	public function getCoupon(){
		if( !empty( $this->_couponCode ) ){
			$memcacheKeyStrCode = array($this->_couponCode);
			$cacheKey = "idx_couponmodel_getcoupon_%s";
			$coupon = $this->memcache->get($cacheKey, $memcacheKeyStrCode);
			if( $coupon == FALSE  || !is_array( $coupon ) ){
				$this->db_ebmaster_read->from('coupon');
				$this->db_ebmaster_read->where('code', $this->_couponCode);
				$this->db_ebmaster_read->where('status',3);
				$this->db_ebmaster_read->limit(1);
				$query = $this->db_ebmaster_read->get();
				$coupon = $query->row_array();

				if(!empty($coupon)){
					$this->db_ebmaster_read->from('coupon_effect');
					$this->db_ebmaster_read->where('coupon_id',$coupon['id']);
					$this->db_ebmaster_read->where('status',1);
					$query = $this->db_ebmaster_read->get();
					//type为3 4时(满额优惠) 一个coupon对应多条coupon_effect 以实现阶梯优惠效果
					$effect = $query->result_array();//row_array读到的数据只有一条
					if(!empty($effect)){
						$coupon['effect'] = $effect;
						//只有全取出来了 才添加到缓存
						$this->memcache->set($cacheKey, $coupon, $memcacheKeyStrCode);
					}else{
						$coupon['effect'] = array();
					}

				}
			}
		}else{
			$coupon = array();
		}

		return $coupon;
	}

	/**
	 * 检测正常的coupon是否合法
	 * @param type $coupon
	 * @return boolean
	 */
	public function checkNormalCouponAvailable($coupon, $languageId = 1 ){
		//判断coupon 是否存在
		if(empty($coupon)){
			$this->_couponMessage = lang('coupon_code_not_exist');
			return false;
		}
		// `start_time` timestamp 类型
		// `end_time` timestamp
		$now = date('Y-m-d H:i:s' , HelpOther::requestTime() );

		$numUsedAllCounts = $this->getUsedCouponCountByCouponId( (int)$coupon['id'] );
		if($coupon['start_time'] > $now){ //判断开始时间是否大于现在时间
			$this->_couponMessage = lang('coupon_code_not_start');
			return false;
		}elseif($coupon['end_time'] !='0000-00-00 00:00:00' && $coupon['end_time'] < $now){ //有结束时间 结束时间小于现在的时间
			$this->_couponMessage = lang('coupon_code_expired');
			return false;
		}elseif($coupon['status'] != 3){//coupon 状态为开启状态
			$this->_couponMessage = lang('coupon_code_not_stuitable');
			return false;
		}elseif($coupon['total'] > 0 &&  $coupon['total'] <= $numUsedAllCounts ){//判断总数是否大于现在已使用数量
			$this->_couponMessage = lang('coupon_code_times_limit');
			return false;
		}elseif(strpos($coupon['language'], strval($languageId)) === false){ //$languageId 是int 结果总是false
			$this->_couponMessage = lang('coupon_code_not_stuitable');
			return false;
		}

		return true;
	}
	/**
	 *  检测订阅的coupon是否合法
	 * @param type $status	订阅的coupon 状态
	 * @param type $totalPrice  购物车总价
	 * @return boolean
	 */
	public function checkSubscribeCouponAvailable($status, $totalPrice = 0){
		$now = time();

		if($now > strtotime(self::SUBSCRIBE_CODE_END_DAY)) {
			$this->_couponMessage = lang('coupon_code_expired');
			return false;
		}elseif( $totalPrice < 30){
			$this->_couponMessage = lang('coupon_sub_cart_total');
			return false;
		}elseif($status == 1){
			$this->_couponMessage = lang('coupon_code_used');
			return false;
		}

		return true;
	}
	/**
	 * 二期做
	 * 根据catr信息判断coupon是否可以使用,只在正常折扣，非优惠的商品才可以使用coupon
	 */
	public function checkCouponAvailable(){

		return true;
	}


	public function checkCustomerUsedCoupon($email,$coupon_id){
		$memcacheKeyStrCode = array($email, $coupon_id);
		$cacheKey = "idx_couponmodel_checkcustomerusedcoupon_%s_%s";
		$count = $this->memcache->get($cacheKey, $memcacheKeyStrCode);

		if(!$count){
			$this->db_ebmaster_read->from('coupon_code_customer');
			$this->db_ebmaster_read->where('coupon_id',$coupon_id);
			$this->db_ebmaster_read->where('email',$email);
			$count = $this->db_ebmaster_read->count_all_results();

			$this->memcache->set($cacheKey, $count, $memcacheKeyStrCode);
		}

		return ($count > 0);
	}

	/**
	 * 根据copon id 获取使用coupon的总数
	 * @param int $coupon_id
	 * @return int $result
	 *
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getUsedCouponCountByCouponId( $coupon_id ){
		if( $coupon_id > 0 ){
			$memcacheKeyStrCode = array( $coupon_id );
			$cacheKey = "idx_couponmodel_count_coupon_%s";
			$count = $this->memcache->get( $cacheKey, array( $coupon_id ));
			if( $count === FALSE ){
				//$this->db_ebmaster_read->select_sum('counts');
				$this->db_ebmaster_read->from('coupon_code_customer');
				$this->db_ebmaster_read->where('coupon_id',$coupon_id);
				$count = $this->db_ebmaster_read->count_all_results();
				$this->memcache->set($cacheKey, $count, $memcacheKeyStrCode);
			}
		}else{
			$count = 0 ;
		}

		return (int)$count;
	}

	/**
	 * @todo 此方法废弃 去掉单个人数限制
	 * @by ningyandong
	 */
	public function getCouponUsedTimeByEmail($coupon_id, $email){
		$memcacheKeyStrCode = array($coupon_id,  $email);
		$cacheKey = "idx_couponmodel_getcouponusedtimebyemail_%s_%s";
		$res = $this->memcache->get($cacheKey, $memcacheKeyStrCode);

		if(!$res){
			$this->db_ebmaster_read->select('counts');
			$this->db_ebmaster_read->from('coupon_code_customer');
			$this->db_ebmaster_read->where('coupon_id', $coupon_id);
			$this->db_ebmaster_read->where('email', $email);
			$this->db_ebmaster_read->limit(1);
			$query = $this->db_ebmaster_read->get();
			$res = $query->row_array();
			if(!empty($res)){
				$this->memcache->set($cacheKey, $res, $memcacheKeyStrCode);
			}
		}

		return id2name('counts' , $res,0);
	}

	public function getCouponGiftInfoBak($act_id, $language_id  = 1 ){
		$this->db_read->from('favorable_gift');
		$this->db_read->where('act_id',$act_id);
		$this->db_read->limit(1);
		$query = $this->db_read->get();
		$res = $query->row_array();

		if(!empty($res)){
			//获取商品的sku 根据pid
			$productObj = ProductModel::getInstanceObj() ;
			$pid = $res['sku']; //上一版是$res['sku']存的是Pid ,现在又改回sku
			$skuArray = $productObj->getProInfoById(array($pid));
			// 如果是符合商品的话，默认取出第一个sku,如果不是符合商品，默认的skuinfo里面只有一个商品，所以也是这个取出方式
			$skuInfo = isset($skuArray[$pid]['skuInfo']) && count($skuArray[$pid]['skuInfo']) > 0 ?current($skuArray[$pid]['skuInfo']):array();
			if(empty($skuInfo)) { return false; }
			$sku = $skuInfo['sku'];
			if( $pid > 0 ){
				//获取商品的信息
				$prodcutSku[] = array(
						'sku' => $sku,
						'pid' => $pid,
						'promoteType' => 1 ,
						'promoteId' => 1 ,
						'bindingPid' => 0,
						'qty'=> 1,
				);
				$productInfo = CartModel::getInstanceObj()->checkStockAndStatus( $prodcutSku , $language_id );
				$productInfo = isset($productInfo[0]) && !empty($productInfo[0]) ? $productInfo[0] : array();
				if(empty($productInfo)) {
					return false;
				}
				if( isset( $productInfo['sku'] ) && $productInfo['status'] == 1 ) {
					//礼品价格为0
					$productInfo[ 'finalPrice' ] = $res['price'] ;
					//type
					$productInfo[ 'finalPromoteType' ] = CartModel::CART_GOODS_TYPE_GIFT ;
					$productInfo[ 'finalPromoteId' ] = $couponEffect['id'] ;
					$productInfo[ 'gift_coupon_effect_id'] = $couponEffect['id'] ;//下标和购物车里的下标风格一致
					$res = array_merge($res, $productInfo );
					$res['finalPrice'] = $res['price'] ? $res['price'] : 0;
				}
			}
		}

		//如果礼物下架等情况 直接下架
		if( !isset( $productInfo[ 'finalPromoteType' ] ) ){
			$res = FALSE;
		}
		return $res;
	}

	/**
	 * coupon 赠品
	 * @param type $res  json格式  $coupon_effect['value'] = {   "price": 2.99,   "sku": "BA02",   "limit": 2 }
	 * @return boolean
	 */
	public function getCouponGiftInfo($couponEffect, $language_id  = 1 ){
		//@todo json数据
		$res = json_decode(stripslashes($couponEffect['value']), true);
		$sku = $res['sku'];
		if(!empty($res)){
			$productObj = ProductModel::getInstanceObj() ;
			$pid = $productObj->getPidBySku($sku);
			if( $pid > 0 ){
				//获取商品的信息
				$prodcutSku[] = array(
						'sku' => $sku,
						'pid' => $pid,
						'promoteType' => 1 ,//为什么是1(折扣倒计时)
						'promoteId' => 1 , //??
						'bindingPid' => 0,
						'qty'=> 1,
				);
				$productInfo = CartModel::getInstanceObj()->checkStockAndStatus( $prodcutSku , $language_id , 4 );

				// $productInfo[$sku] 没有$sku 对应的下标 而是以索引数组返回的
				//$productInfo = isset($productInfo[$sku]) && !empty($productInfo[$sku]) ? $productInfo[$sku] : array();
				$productInfo = isset($productInfo[0]) && !empty($productInfo[0]) ? $productInfo[0] : array();
				if(empty($productInfo)) {
					HelpOther::returnJson(array(), lang('coupon_code_not_invalid'), 70004); exit;
				}
				if( isset( $productInfo['sku'] ) && $productInfo['status'] == 1 ) {
					//礼品价格为0
					$productInfo[ 'finalPrice' ] = $res['price'] ;
					//type
					$productInfo[ 'finalPromoteType' ] = CartModel::CART_GOODS_TYPE_GIFT ;
					//@todo 修改为coupon ID
					$productInfo[ 'finalPromoteId' ] = $couponEffect['coupon_id'] ;
					//下标和购物车里的下标风格一致 名字暂时没改
					$productInfo[ 'favorable_gift_id'] = $couponEffect['id'] ;
					$res = array_merge($res, $productInfo );
					//因为赠品 折扣的金额是
					$res['finalPrice'] = isset( $res['price'] ) ? $res['price'] : $res['finalPrice'] ;
				}
			}
		}
		//如果礼物下架等情况 直接下架
		if( !isset( $productInfo[ 'finalPromoteType' ] ) ){
			$res = FALSE;
		}
		return $res;
	}

	public function addUpCustomerCouponTimes($email, $coupon_id){
		$this->db_ebmaster_write->set('counts', 'counts+1',false);
		$this->db_ebmaster_write->where('coupon_id', $coupon_id);
		$this->db_ebmaster_write->where('email', $email);
		$this->db_ebmaster_write->update('coupon_code_customer');
	}

	public function createCustomerCoupon($info){
		$this->db_ebmaster_write->insert('coupon_code_customer', $info);
		return $this->db_ebmaster_write->insert_id();
	}

	public function getCouponMessage(){
		return $this->_couponMessage ;
	}

	public function setCouponMessage($message = false){
		$this->_couponMessage = trim($message);
	}

	public function getCouponCode() {
		return $this->_couponCode;
	}

	public function setCouponCode($couponCode = '') {
		$this->_couponCode = trim($couponCode);
	}


}
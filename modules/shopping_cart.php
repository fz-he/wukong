<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shopping_cart extends CI_Module {

	const CART_GOODS_TYPE_NORMAL = 0;
	const CART_GOODS_TYPE_GIFT = 5;

	const PROMOTE_ACT_TYPE_PRICE_REDUCE = 1;
	const PROMOTE_ACT_TYPE_PRICE_DISCOUNT = 2;
	const PROMOTE_ACT_TYPE_PRICE_GIFT = 3;
	const PROMOTE_ACT_TYPE_PRICE_COND = 4;

	const INSURANCE_FEE = 1.99;
	const SUBSCRIBE_CODE_END_DAY = '2015-09-30 23:59:59';

	//cart
	protected $_goods_list = array();
	protected $_subtotal_price = 0;
	protected $_total_price = 0;
	protected $_total_integral = 0;
	protected $_flg_can_checkout = true;
	protected $_cart_message = false;

	//coupon
	protected $_coupon_code = '';
	protected $_coupon_message = false;

	//integral
	protected $_use_integral = 0;
	protected $_integral_message = false;

	//discount
	protected $_discount_list = array();
	protected $_discount_desc = '';
	protected $_discount_amount = 0;
	protected $_gift = false;
	protected $_exclusiveCodeDiscount_num = 0;
	protected $_exclusiveCode = '';
	protected $_exclusiveInfo = array();

	//Environment
	protected $_user = array();
	protected $_session_id = '';
	protected $_language_id = 1;
	protected $_language_code = DEFAULT_LANGUAGE;

	//shipping
	protected $_address_id = 0;
	protected $_shipping_country = false;
	protected $_shipping_city = false;
	protected $_flg_empty_province = false;
	protected $_shipping_list = array();
	protected $_selected_shipping_id = 0;
	protected $_shipping_message = false;
	protected $_is_cn_mail = false;
	protected $_shipping_price = 0;
	protected $_flg_insurance = false;
	protected $_flg_separate_package = false;
	protected $_flg_separate_package_disabled = false;

	//payment
	protected $_payment_country = false;
	protected $_payment_list = array();
	protected $_payment_id = 0;

	protected $_flg_ignore_login_check = false;
	protected $_flg_ignore_payment_check = false;
	protected $_flg_can_placeorder = true;

	//TODO
	protected $_flg_enter_loop = false;

	public function __construct(){
		//load models
		$this->load->model('Appmodel','m_app');
		$this->UserModel = new UserModel();
		$this->load->model('Cartmodel','m_cart');
		$this->GoodsModel = new GoodsModel();
		$this->load->model('Couponmodel','m_coupon');
		$this->load->model('Addressmodel','m_address');

		//init envi info : user language etc.
		$this->_initEnvironmentInfo();

		//load Integral & Coupon info this session
		$this->_loadSessionIntegralInfo();
		$this->_loadSessionCouponInfo();

		//load addresss
		$this->_loadAddressInfo();

		//shipping
		$this->load->module('shipping');
		$this->_loadSessionShippingInfo();
		$this->_loadSessionInsuranceInfo();
		$this->_loadSessionSeparatePackageInfo();
		$this->_loadSessionPaymentInfo();

		//payment
		$this->load->module('payment');
	}

	public function loadCart(){
		$this->_resetCartParams();
		//load cart from db
		if(empty($this->_user)){
			$this->_goods_list = $this->m_cart->getCartBySession($this->_session_id);
		}else{
			$this->_goods_list = $this->m_cart->getCartByUser($this->_user['user_id']);
		}
		//fetch goods info
		$goods_ids = extractColumn($this->_goods_list,'goods_id');
		$goods_list = $this->GoodsModel->getUncachedGoodsById($goods_ids,$this->_language_id);
		$goods_list = reindexArray($goods_list,'goods_id');

		//fetch attribute info
		$attribute_list_arr = $this->GoodsModel->getAttributeAndValueByGoods(extractColumn($goods_list,'goods_id'),$this->_language_id);

		//fetch gift info
		$favorable_gift_ids = extractColumn($this->_goods_list,'favorable_gift_id');
		$favorable_gift_ids = array_diff($favorable_gift_ids,array(0));
		$gift_list = $this->m_cart->getGiftById($favorable_gift_ids);
		$gift_list = reindexArray($gift_list,'favorable_gift_id');

		//fetch shipping info
		$goods_skus = extractColumn($goods_list,'goods_sn');
		$goods_spec_list = $this->GoodsModel->getGoodsSpecBySku($goods_skus);
		$goods_spec_list = reindexArray($goods_spec_list,'code');


		$goods_sensitive_list = $this->GoodsModel->getGoodsSensitiveInfoBySku($goods_skus);
		$goods_sensitive_list = reindexArray($goods_sensitive_list,'goods_sku');

		//walk though goods list, format goods info, add up total price
		$applied_gift = array();
		$goods_weight_total = 0;
		$goods_weight_max = 0;
		$volume_weight_total = 0;
		$max_sumlwh = 0;
		$max_length = 0;
		$flg_battery = false;
		$flg_sensitive = false;
		$warehouse_arr = array();
		$total_number = 0;
		foreach ($this->_goods_list as $key => $record) {
			//exist check
			if($record['goods_number'] <= 0){
				unset($this->_goods_list[$key]);
				continue;
			}
			if(!isset($goods_list[$record['goods_id']])){
				unset($this->_goods_list[$key]);
				continue;
			}
			if($record['pro_type'] == self::CART_GOODS_TYPE_GIFT && !isset($gift_list[$record['favorable_gift_id']])){
				unset($this->_goods_list[$key]);
				continue;
			}

			//get goods & gift info & attr info
			$goods_info = $goods_list[$record['goods_id']];
			$gift_info = id2name($record['favorable_gift_id'],$gift_list,false);
			$attr_info = id2name($record['goods_id'],$attribute_list_arr,array());

			//basic info
			$this->_addGoodsBasicInfo($record,$goods_info);
			$this->_addGoodsAttributeInfo($record,$attr_info);

			//stock
			if($record['pro_type'] == self::CART_GOODS_TYPE_GIFT){
				$limit = id2name('gift_num_limit',$gift_info,0);
				$this->_addGoodsStockInfo($record,$limit);
				$applied_gift = $record;
			}else{
				$this->_addGoodsStockInfo($record,$goods_info['goods_number']);
			}

			//on sale
			if($goods_info['is_on_sale'] == GOODS_NOT_ON_SALE){
				$record['message'] = lang('goods_not_on_sale');
				$this->_flg_can_checkout = false;
				$this->_cart_message = lang('goods_not_on_sale').':'.$goods_info['goods_name'];
			}

			//price
			if($record['pro_type'] == self::CART_GOODS_TYPE_GIFT){
				$this->_calculateGiftPrice($record,$goods_info,$gift_info);
			}else{
				$this->_calculateGoodsPrice($record,$goods_info);
			}

			$this->_goods_list[$key] = $record;

			$this->_subtotal_price += $record['price_subtotal_number'];
			$total_number += $record['goods_number'];
			if(!$record['flg_show_order_to']){
				$goods_weight_max = max($goods_weight_max,$goods_info['goods_weight']);
				$goods_weight_total += $goods_info['goods_weight'] * $record['goods_number'];

				if(isset($goods_spec_list[$record['goods_sn']])){
					$length = $goods_spec_list[$record['goods_sn']]['length'] * 100;
					$width = $goods_spec_list[$record['goods_sn']]['width'] * 100;
					$height = $goods_spec_list[$record['goods_sn']]['height'] * 100;

					$max_sumlwh = max($max_sumlwh,($length + $width + $height));
					$max_length = max($max_length,$length);
					$max_length = max($max_length,$width);
					$max_length = max($max_length,$height);
					$volume_weight_total += ($length * $width * $height) / 5000 * $record['goods_number'];
				}
				if(isset($goods_sensitive_list[$record['goods_sn']])){
					if($goods_sensitive_list[$record['goods_sn']]['contraband_type_id'] == 1 || $goods_sensitive_list[$record['goods_sn']]['contraband_type_id'] == 5){
						$flg_battery = true;
					}elseif($goods_sensitive_list[$record['goods_sn']]['contraband_type_id'] > 0){
						$flg_sensitive = true;
					}
				}
			}
			$warehouse_arr[] = $goods_info['order_to'];
		}

		$this->_total_price = $this->_subtotal_price;

		//make discount list
		$this->_addGroupDiscountIntoDiscountList();
		$this->_addLevelDiscountIntoDiscountList();
		$this->_addCouponDiscountIntoDiscountList();
		$this->_addExclusiveCodeDiscountList();

		//apply all discount in discount list
		$this->_applyAllDiscount();

		//gift
		if($this->_flg_enter_loop === false){
			if($this->_gift !== false && id2name('favorable_gift_id',$applied_gift,0) != $this->_gift['favorable_gift_id']){
				if(!empty($applied_gift)){
					$this->m_cart->deleteCartById($applied_gift['rec_id']);
				}
				$this->_addToCart($this->_gift,1,self::CART_GOODS_TYPE_GIFT);
				$this->_flg_enter_loop = true;
				$this->loadCart();
				return;
			}elseif($this->_gift === false && !empty($applied_gift)){
				$this->m_cart->deleteCartById($applied_gift['rec_id']);
				$this->_flg_enter_loop = true;
				$this->loadCart();
				return;
			}
		}

		$this->_applyIntegral();

		$this->_total_integral = round($this->_total_price);

		if($this->_total_price < 0.01 || empty($this->_goods_list)){
			$this->_total_price = 0;
			$this->_flg_can_checkout = false;
			$this->_cart_message = lang('account_cart_empty');
		}

		//shipping method

		if($this->_shipping_country !== false){
			$this->shipping->setShippingCountry($this->_shipping_country);
			$this->shipping->setShippingCity($this->_shipping_city);
			$this->shipping->setOrderGoodsWeight($goods_weight_total);
			$this->shipping->setMaxGoodsWeight($goods_weight_max);
			$this->shipping->setOrderVolumeWeight($volume_weight_total);
			$this->shipping->setOrderPrice($this->_total_price);
			$this->shipping->setWarehouse($warehouse_arr);
			$this->shipping->setMaxSumLwh($max_sumlwh);
			$this->shipping->setMaxLength($max_length);
			if($flg_sensitive) $this->shipping->setContainSensitive();
			if($flg_battery) $this->shipping->setContainBattery();
			$this->_shipping_list = $this->shipping->getShippingMethodList();
			if(isset($this->_shipping_list['CN-Mail'])){
				$this->_is_cn_mail = TRUE;
			}
			if($this->shipping->checkShippingAvailable()){
				$this->_selectShipping($this->_selected_shipping_id);
				if($this->_flg_insurance){
					$this->_total_price += self::INSURANCE_FEE;
				}
			}else{
				$this->_selected_shipping_id = 0;
				$this->_flg_insurance = false;
				$this->_flg_separate_package = false;
				$this->_flg_separate_package_disabled = true;
				$this->_shipping_message = lang('shipping_notice');
			}
		}

		if($this->_flg_separate_package === null){
			if($this->_selected_shipping_id == 3){
				$this->_flg_separate_package = false;
			}else{
				$this->_flg_separate_package = true;
			}
		}

		if($this->_selected_shipping_id == 1){
			$this->_flg_separate_package = true;
			$this->_flg_separate_package_disabled = true;
		}

		if($total_number <= 1){
			$this->_flg_separate_package = false;
			$this->_flg_separate_package_disabled = true;
		}


		//payment
		if($this->_payment_country === false) $this->_payment_country = $this->_shipping_country;
		$this->payment->setShippingCountry($this->_shipping_country);
		$this->payment->setPaymentCountry($this->_payment_country);
		$this->payment->loadPaymentList();
		$this->_payment_list = $this->payment->getPaymentList();

		//check can place order
		if($this->_shipping_country === false){
			$this->_flg_can_placeorder = false;
		}
		if($this->_flg_empty_province === true){
			$this->_flg_can_placeorder = false;
		}
		if(!$this->_flg_can_checkout){
			$this->_flg_can_placeorder = false;
		}
		if(!$this->_flg_ignore_login_check){
			if(empty($this->_user) || $this->_address_id == 0){
				$this->_flg_can_placeorder = false;
			}
		}
		if(!$this->_flg_ignore_payment_check){
			if(!$this->payment->checkPlaceOrderPaymentAvailable($this->_payment_id)){
				$default_payment_id = $this->payment->getPlaceOrderDefaultAvailablePayment();
				if($default_payment_id === false){
					$this->_flg_can_placeorder = false;
				}else{
					$this->_payment_id = $default_payment_id;
				}
			}
		}
		if(!$this->shipping->checkSelectedShippingAvailable($this->_selected_shipping_id)){
			$this->_flg_can_placeorder = false;
		}

		/*Exclusive discount*/
		if ($this->_exclusiveInfo) {
			$this->_exclusiveCodeDiscount_num = ceil($this->_total_price * $this->_exclusiveInfo['discountRate']) / 100;
			$this->_total_price -= $this->_exclusiveCodeDiscount_num;
			$this->_discount_amount += $this->_exclusiveCodeDiscount_num;
		}
	}

	public function getCart(){
		$cart = array(
			'goods_list' => array_values($this->_goods_list),
			'subtotal' => $this->_subtotal_price,
			'subtotal_price' => formatPrice($this->_subtotal_price),
			'total_price_number' => $this->_total_price,
			'total_price' => formatPrice($this->_total_price),
			'total_integral' => $this->_total_integral,
			'flg_can_checkout' => $this->_flg_can_checkout,
			'cart_message' => $this->_cart_message,

			'coupon_code' => $this->_coupon_code,
			'coupon_message' => $this->_coupon_message,

			'use_integral' => $this->_use_integral,
			'use_integral_price_number' => $this->_calculatePointPrice($this->_use_integral),
			'use_integral_price' => formatPrice($this->_calculatePointPrice($this->_use_integral)),
			'integral_message' => $this->_integral_message,

			'discount_desc' => $this->_discount_desc,
			'discount_amount' => $this->_discount_amount,
			'discount_amount_price' => formatPrice($this->_discount_amount),
			'exclusiveCode' => $this->_exclusiveCode,
			'exclusiveCodeDiscountNum' => $this->_exclusiveCodeDiscount_num,
			'exclusiveCodeDiscount' => formatPrice($this->_exclusiveCodeDiscount_num),

			'address_id' => $this->_address_id,
			'shipping_country' => $this->_shipping_country,
			'flg_empty_province' => $this->_flg_empty_province,
			'shipping_list' => $this->_shipping_list,
			'shipping_id' => $this->_selected_shipping_id,
			'shipping_message' => $this->_shipping_message,
			'is_cn_mail' => $this->_is_cn_mail,
			'shipping_price_number' => $this->_shipping_price,
			'shipping_price' => formatPrice($this->_shipping_price),
			'flg_insurance' => $this->_flg_insurance,
			'flg_separate_package' => $this->_flg_separate_package,
			'flg_separate_package_disabled' => $this->_flg_separate_package_disabled,

			'payment_country' => $this->_payment_country,
			'payment_list' => $this->_payment_list,
			'payment_id' => $this->_payment_id,

			'flg_can_placeorder' => $this->_flg_can_placeorder,
		);
		return $cart;
	}

	public function useIntegral($point){
		$point = intval($point);
		$point = max(0,$point);
		$this->_use_integral = $point;
	}

	public function useCoupon($coupon){
		$this->_coupon_code = trim(strval($coupon));
		$this->session->delete('user_cart');
	}

	public function addExclusiveCodeDiscount($code, $discountRate,$countryCode,$payTypeId,$is_mobile_device) {
		$this->session->set('exclusiveCodeInfo', array(
			'code' => $code,
			'discountRate' => $discountRate,
			'countryCode' => $countryCode,
			'payTypeId' => $payTypeId,
			'is_mobile_device' => $is_mobile_device,
		));
	}

	public function cancelExclusiveCodeDiscount(){
		$this->session->delete('exclusiveCodeInfo');
	}

	protected function _addExclusiveCodeDiscountList(){

		$exclusiveCodeInfo = $this->session->get('exclusiveCodeInfo');
		if ($exclusiveCodeInfo) {
			if ($this->router->class == 'place_order' ||$this->router->class == 'common') {
				$this->_exclusiveInfo = $exclusiveCodeInfo;
				$this->_exclusiveCode = $exclusiveCodeInfo['code'];
				$this->load->language('place_order', $this->m_app->currentLanguageCode());
				$this->_discount_list[] = array(
					'name' => lang('exclu_code_name'),
					'amount' => 0,
				);
			} else {
				$this->cancelExclusiveCodeDiscount();
			}
		}
	}

	public function updateQty($rec_id,$qty = 1){
		$rec_id = intval($rec_id);
		$qty = intval($qty);

		$row = $this->m_cart->getCartRow($rec_id);
		if(empty($row)) return false;

		$goods = $this->GoodsModel->getUncachedGoodsById($row['goods_id']);
		if(empty($goods)) return false;
		$goods = current($goods);
		if(empty($goods) || $goods['is_on_sale'] == GOODS_NOT_ON_SALE) return false;
		$qty = min($qty,$goods['goods_number']);

		if($row['pro_type'] == self::CART_GOODS_TYPE_GIFT){
			$gift = $this->m_cart->getGiftById($row['favorable_gift_id']);
			if(!empty($gift)) $gift = current($gift);
			$qty = min($qty,id2name('gift_num_limit',$gift,0));
		}

		if(empty($this->_user)){
			if($row['session_id'] == $this->_session_id){
				if($qty > 0){
					$this->m_cart->updateCart($rec_id,array('goods_number'=>$qty));
				}else{
					$this->m_cart->deleteCartById($rec_id);
				}
			}
		}else{
			if($row['user_id'] == $this->_user['user_id']){
				if($qty > 0){
					$this->m_cart->updateCart($rec_id,array('goods_number'=>$qty));
				}else{
					$this->m_cart->deleteCartById($rec_id);
				}
			}
		}

		$this->session->delete('user_cart');
		return true;
	}

	public function removeRecord($rec_id){
		$rec_id = intval($rec_id);

		$row = $this->m_cart->getCartRow($rec_id);
		if(empty($row)) return false;

		if(empty($this->_user)){
			if($row['session_id'] == $this->_session_id){
				$this->m_cart->deleteCartById($rec_id);
			}
		}else{
			if($row['user_id'] == $this->_user['user_id']){
				$this->m_cart->deleteCartById($rec_id);
			}
		}
		if($row['pro_type'] == self::CART_GOODS_TYPE_GIFT){
			$this->_removeSessionCouponInfo();
			$this->_coupon_code = '';
		}

		$this->session->delete('user_cart_all');
		return true;
	}

	public function addToCart($goods,$qty = 1){
		$list = array();
		if(empty($this->_user)){
			$list = $this->m_cart->getCartBySession($this->_session_id);
		}else{
			$list = $this->m_cart->getCartByUser($this->_user['user_id']);
		}

		$record_exist = array();
		foreach($list as $record){
			if($record['goods_id'] == $goods['goods_id'] && $record['pro_type'] == self::CART_GOODS_TYPE_NORMAL){
				$record_exist = $record;
				break;
			}
		}
		if(!empty($record_exist)){
			$qty += $record_exist['goods_number'];
			$qty = min($qty,$goods['goods_number']);
			$this->m_cart->updateCart($record_exist['rec_id'],array(
				'goods_number' => $qty,
				'update_time' => time(),
			));
		}else{
			$this->_addToCart($goods,$qty);
		}
		$this->session->delete('user_cart_all');
	}

	public function mergeCart(){
		if(empty($this->_user)) return false;
		$now = $_SERVER['REQUEST_TIME'];
		$user_cart = $this->m_cart->getCartByUser($this->_user['user_id']);
		$session_cart = $this->m_cart->getCartBySession($this->_session_id);

		$user_cart = spreadArray($user_cart,'goods_id');
		foreach($user_cart as $key => $record){
			$user_cart[$key] = reindexArray($record,'pro_type');
		}
		$update_arr = array();
		$delete_arr = array();
		$need_calculate_price_arr = array();
		foreach($session_cart as $record){
			if(isset($user_cart[$record['goods_id']][$record['pro_type']])){
				if ($record['pro_type'] == self::CART_GOODS_TYPE_NORMAL) {
					$user_cart_record = $user_cart[$record['goods_id']][$record['pro_type']];

					$goods_number = $record['goods_number'] + $user_cart_record['goods_number'];
					$goods_number = max(1,$goods_number);

					$need_calculate_price_arr[] = array(
						'rec_id' => $user_cart_record['rec_id'],
						'goods_id' => $record['goods_id'],
						'goods_number' => $goods_number,
					);
				}

				$delete_arr[] = $record['rec_id'];
			}else{
				$update_arr[] = array(
					'rec_id' => $record['rec_id'],
					'user_id' => $this->_user['user_id'],
					'update_time' => $now,
				);
			}
		}

		$goods_ids = extractColumn($need_calculate_price_arr,'goods_id');
		$goods_list = $this->GoodsModel->getGoodsById($goods_ids, 1, GoodsModel::GOODS_UNDELETED);
		$goods_list = reindexArray($goods_list,'goods_id');
		foreach($need_calculate_price_arr as $record){
			if(!isset($goods_list[$record['goods_id']])) continue;
			$goods = $goods_list[$record['goods_id']];

			$this->GoodsModel->calculatePrice($goods);

			$update_arr[] = array(
				'rec_id' => $record['rec_id'],
				'goods_price' => $goods['goods_price'],
				'goods_number' => $record['goods_number'],
				'update_time' => $now,
			);
		}

		if(!empty($update_arr)) $this->m_cart->updateBatchCart($update_arr);
		if(!empty($delete_arr)) $this->m_cart->deleteCartById($delete_arr);
		$this->session->delete('user_cart');
	}

	public function selectShipping($shipping_id){
		$shipping_id = intval($shipping_id);
		if($this->_selected_shipping_id != $shipping_id){
			$this->_flg_separate_package = null;
		}
		$this->_selected_shipping_id = intval($shipping_id);
	}

	public function addInsurance(){
		$this->_flg_insurance = true;
		$this->_saveSessionInsuranceInfo();
	}

	public function removeInsurance(){
		$this->_flg_insurance = false;
		$this->_saveSessionInsuranceInfo();
	}

	public function allowSeparatePackage(){
		$this->_flg_separate_package = true;
		$this->_saveSessionSeparatePackageInfo();
	}

	public function denySeparatePackage(){
		$this->_flg_separate_package = false;
		$this->_saveSessionSeparatePackageInfo();
	}

	public function resetSeparatePackage(){
		$this->_flg_separate_package = null;
	}

	public function clearCart(){
		$this->m_cart->deleteSessionCart($this->_session_id);
		if(!empty($this->_user)) $this->m_cart->deleteUserCart($this->_user['user_id']);
		$this->session->delete('user_cart_all');
		$this->session->delete('cart_integral');
		$this->session->delete('cart_coupon_code');
		$this->session->delete('exclusiveCodeInfo');
		$this->session->delete('cart_shipping_id');
		$this->session->delete('cart_flg_insurance');
		$this->session->delete('cart_flg_separate_package');
		$this->session->delete('cart_payment_id');
	}

	public function setShippingCountry($country_code){
		$this->_shipping_country = strtoupper($country_code);
	}

	public function setShippingCity($city){
		$this->_shipping_city = $city;
	}

	public function setPaymentCountry($country_code){
		$this->_payment_country = strtoupper($country_code);
		$this->_saveSessionPaymentInfo();
	}

	public function setIgnoreShipping(){
		$this->_shipping_country = false;
	}

	public function setIgnoreLoginCheck(){
		$this->_flg_ignore_login_check = true;
	}

	public function setIgnorePaymentCheck(){
		$this->_flg_ignore_payment_check = true;
	}
/*
| -------------------------------------------------------------------
|  Goods Private Functions
| -------------------------------------------------------------------
*/
	protected function _addGoodsBasicInfo(&$cart_record,$goods){
		$cart_record['goods_id'] = $goods['goods_id'];
		$cart_record['cat_id'] = $goods['cat_id'];
		$cart_record['goods_name'] = $goods['goods_name'];
		$cart_record['goods_sn'] = $goods['goods_sn'];
		$cart_record['goods_img'] = genImageUrl($goods['goods_img']);
		$cart_record['goods_img_45'] = str_replace('350-350', '45-45', $cart_record['goods_img']);
		$cart_record['goods_url'] = eb_gen_url($goods['url_name']);
		$cart_record['order_to'] = $goods['order_to']?$goods['order_to']:'HK';
		$cart_record['flg_show_order_to'] = in_array($goods['order_to'],AppConfig::$warehouse_oversea);
		$cart_record['message'] = false;
	}

	protected function _addGoodsAttributeInfo(&$cart_record,$attribute_list){
		foreach($attribute_list as $sub_key => $sub_record){
			$attribute_list[$sub_key] = $sub_record['attribute_title'].':'.$sub_record['attribute_value_title'];
		}
		$cart_record['attribute_list'] = $attribute_list;
	}

	protected function _addGoodsStockInfo(&$cart_record,$qty_limit = 0){
		$cart_record['flg_maxqty'] = false;
		$cart_record['qty_limit'] = $qty_limit;

		if($cart_record['goods_number'] > $qty_limit){
			$this->_flg_can_checkout = false;
			$this->_cart_message = lang('goods_unstock').':'.$cart_record['goods_name'];
		}


		if($cart_record['goods_number'] >= $qty_limit){
			$cart_record['flg_maxqty'] = true;
			$cart_record['message'] = sprintf(lang('only_buy'),$qty_limit);
		}elseif($cart_record['pro_type'] == self::CART_GOODS_TYPE_NORMAL && $qty_limit <= 10){
			// $cart_record['message'] = sprintf(lang('left'),$qty_limit - $cart_record['goods_number']);
			$cart_record['message'] = sprintf(lang('left'),$qty_limit);
		}
	}

	protected function _calculateGoodsPrice(&$cart_record,$goods){
		//calculate price
		$this->GoodsModel->calculatePrice($goods);

		//current price
		$cart_record['goods_price_number'] = $goods['goods_price'];
		$cart_record['goods_price'] = formatPrice($goods['goods_price']);
		$cart_record['subtotal'] = formatPrice($goods['goods_price']*$cart_record['goods_number']);
		$cart_record['price_subtotal_number'] = $goods['goods_price']*$cart_record['goods_number'];

		//original price
		$cart_record['original_price'] = false;
		$cart_record['original_subtotal'] = false;

		//price icon
		$cart_record['flg_free'] = false;
		$cart_record['flg_promote_active'] = $goods['flg_promote_active'];
	}

	protected function _calculateGiftPrice(&$cart_record,$goods,$gift = false){
		//calculate price
		$this->GoodsModel->calculatePrice($goods);

		//current price
		$goods_price = id2name('gift_price',$gift,$goods['goods_price']);
		$cart_record['goods_price_number'] = $goods_price;
		$cart_record['goods_price'] = formatPrice($goods_price);
		$cart_record['subtotal'] = formatPrice($goods_price*$cart_record['goods_number']);
		$cart_record['price_subtotal_number'] = $goods_price*$cart_record['goods_number'];

		//original price
		$cart_record['original_price'] = formatPrice($goods['goods_price']);
		$cart_record['original_subtotal'] = formatPrice($goods['goods_price']*$cart_record['goods_number']);

		//price icon
		$cart_record['flg_promote_active'] = true;
		$cart_record['flg_free'] = $goods_price>0?false:true;
	}

	protected function _addToCart($item,$qty = 1,$pro_type = self::CART_GOODS_TYPE_NORMAL){
		$goods_sn = id2name('goods_sn',$item);
		$goods_price = id2name('goods_price',$item);
		$is_gift = 0;
		if($pro_type == self::CART_GOODS_TYPE_GIFT){
			$goods_sn = id2name('gift_sku',$item);
			$is_gift = 1;
			$goods_price = id2name('gift_price',$item);
		}

		return $this->m_cart->k(array(
			'user_id' => empty($this->_user)?0:$this->_user['user_id'],
			'session_id' => $this->_session_id,
			'goods_id' => id2name('goods_id',$item,0),
			'goods_sn' => $goods_sn,
			'goods_name' => addslashes(id2name('goods_name',$item)),
			'goods_price' => $goods_price,
			'goods_number' => $qty,
			'is_gift' => $is_gift,
			'pro_type' => $pro_type,
			'promotion_ticket' => '',
			'auc_id' => 0,
			'order_to' => id2name('order_to',$item),
			'favorable_gift_id' => id2name('favorable_gift_id',$item,0),
			'update_time' => time(),
		));
	}
/*
| -------------------------------------------------------------------
|  Integral Private Functions
| -------------------------------------------------------------------
*/
	protected function _loadSessionIntegralInfo(){
		$integral = $this->session->get('cart_integral');
		if($integral !== false){
			$this->_use_integral = intval($integral);
		}
	}

	protected function _applyIntegral(){
		if(!empty($this->_user) && $this->_user['user_group_id'] == 3){
			$this->_use_integral = 0;
		}

		if($this->_use_integral > 0){
			if(empty($this->_user) || $this->_use_integral > $this->_user['point_active']){
				$this->_use_integral = $this->_user['point_active'];
				$this->_integral_message = lang('integral_not_enough');
			}
			$order_limit_integral = $this->_calculateMaxIntegral();
			if($this->_use_integral > $order_limit_integral){
				$this->_use_integral = $order_limit_integral;
				$this->_integral_message = lang('max_point');
			}
			$this->_total_price -= $this->_calculatePointPrice($this->_use_integral);
		}
		$this->_saveSessionIntegralInfo();
	}

	protected function _calculatePointPrice($point){
		$price = round($point/100, 2);
		return $price;
	}

	protected function _calculateMaxIntegral(){
		$point = floor($this->_subtotal_price * 20);
		return $point;
	}

	protected function _saveSessionIntegralInfo(){
		$this->session->set('cart_integral',$this->_use_integral);
	}
/*
| -------------------------------------------------------------------
|  User Private Functions
| -------------------------------------------------------------------
*/
	protected function _initEnvironmentInfo(){
		$this->_session_id = $this->session->getSessionId();
		$this->_language_id = $this->m_app->currentLanguageId();
		$this->_language_code = $this->m_app->currentLanguageCode();

		if($this->m_app->checkUserLogin()){
			$this->_user = $this->UserModel->getUserInfoLatest($this->m_app->getCurrentUserId());
		}

		if(!empty($this->_user)){
			$point = $this->UserModel->getUserPoint($this->_user['user_id']);
			$this->_user['point_active'] = intval(id2name('active',$point,0));
			$point_total = intval(id2name('total',$point,0));

			$this->_user['level'] = array();
			global $user_rank;
			$point_total = min($point_total,9999);
			if( is_array( $user_rank ) &&  count($user_rank ) > 0 ){
				foreach($user_rank as $level => $level_info){
					if($level_info['min_points'] <= $point_total && $level_info['max_points'] >= $point_total){
						$level_info['level'] = $level;
						$this->_user['level'] = $level_info;
						break;
					}
				}
			}
		}
	}

	protected function _resetCartParams(){
		$this->_goods_list = array();
		$this->_subtotal_price = 0;
		$this->_total_price = 0;
		$this->_total_integral = 0;
		$this->_flg_can_checkout = true;
		$this->_cart_message = false;

		$this->_coupon_message = false;
		$this->_integral_message = false;

		$this->_discount_list = array();
		$this->_discount_desc = '';
		$this->_discount_amount = 0;
		$this->_gift = false;
	}

	protected function _loadAddressInfo(){
		if(!empty($this->_user)){
			$this->_address_id = id2name('address_id',$this->_user,0);
		}else{
			$this->_address_id = 0;
		}

		$address = array();
		if($this->_address_id > 0){
			$address = $this->m_address->getAddress($this->_address_id);
		}
		if(!empty($address) && $address['user_id'] == $this->_user['user_id']){
			$this->_shipping_country = strtoupper($address['country']);
			if(in_array($this->_shipping_country,array('US','AU')) && $address['province'] == ''){
				$this->_flg_empty_province = true;
			}
			$this->_shipping_city = $address['city'];
		}else{
			$this->_address_id = 0;
			$this->_shipping_country = false;
		}
	}
/*
| -------------------------------------------------------------------
|  Discount Private Functions
| -------------------------------------------------------------------
*/
	protected function _addGroupDiscountIntoDiscountList(){
		if(!empty($this->_user) && $this->_user['user_group_id'] == 3){
			$price_discount_amount = sprintf("%.2f", $this->_total_price * 0.1);
			$price_discount_amount = floatval($price_discount_amount);
			$price_discount_amount = max($price_discount_amount,0);
			if($price_discount_amount > 0){
				$this->_discount_list[] = array(
					'name' => 'Business Customer Discount',
					'amount' => $price_discount_amount,
				);
			}
		}
	}

	protected function _addLevelDiscountIntoDiscountList(){
		if(!empty($this->_user) && !empty($this->_user['level']) && $this->_user['level']['discount'] > 0 && $this->_user['user_group_id'] != 3){
			$price_discount_amount = sprintf("%.2f", $this->_total_price * ($this->_user['level']['discount'] / 100));
			$price_discount_amount = floatval($price_discount_amount);
			$price_discount_amount = max($price_discount_amount,0);
			if($price_discount_amount > 0){
				$this->_discount_list[] = array(
					'name' => lang('level_'.$this->_user['level']['level'].'_title').' '.$this->_user['level']['discount'].'% '.lang('discount'),
					'amount' => $price_discount_amount,
				);
			}
		}
	}

	protected function _addCouponDiscountIntoDiscountList(){
		if($this->_coupon_code == '' || (!empty($this->_user) && $this->_user['user_group_id'] == 3)){
			$this->_removeSessionCouponInfo();
			return false;
		}

		$subscribeInfo = $this->UserModel->getSubscribeInfoByHash($this->_coupon_code);
		if(!empty($subscribeInfo)){
			//subscribe coupon
			if($this->_checkSubscribeCouponAvailable($subscribeInfo)){
				$this->_discount_list[] = array(
					'name' => lang('coupon_subscription_coupon_code'),
					'amount' => sprintf("%.2f", 3),
				);
				$this->_saveSessionCouponInfo();
			}
		}else{
			//normal coupon
			$coupon = $this->m_coupon->getCoupon($this->_coupon_code);
			if($this->_checkNormalCouponAvailable($coupon)){
				$coupon['act_name_language'] = $coupon['act_name_language']==''?array():unserialize($coupon['act_name_language']);
				$coupon['act_name_language'] = id2name($this->_language_id,$coupon['act_name_language']);
				if($coupon['act_name_language'] == '') $coupon['act_name_language'] = $coupon['act_name'];
				if($coupon['range_type'] == 1){
					$this->_applyCouponSku($coupon);
				}elseif($coupon['range_type'] == 2){
					$this->_applyCouponCategory($coupon);
				}elseif($coupon['range_type'] == 3){
					$this->_applyCouponPrice($coupon);
				}elseif($coupon['range_type'] == 4){
					$this->_applyCouponAll($coupon);
				}
				$this->_saveSessionCouponInfo();
			}
		}
	}

	protected function _applyAllDiscount(){
		if(!empty($this->_discount_list)){
			$discount_name = array();
			foreach($this->_discount_list as $record){
				if($record['amount'] >= $this->_total_price) {continue;}
				$discount_name[] = $record['name'];
				$this->_total_price -= $record['amount'];
				$this->_discount_amount += $record['amount'];
			}
			$this->_discount_desc = implode(',',$discount_name);
		}
	}
/*
| -------------------------------------------------------------------
|  Coupon Private Functions
| -------------------------------------------------------------------
*/
	protected function _loadSessionCouponInfo(){
		$coupon = $this->session->get('cart_coupon_code');
		if($coupon !== false){
			$this->_coupon_code = strval($coupon);
		}
	}

	protected function _checkSubscribeCouponAvailable($subscribeInfo){
		$now = time();

		if($now > strtotime(self::SUBSCRIBE_CODE_END_DAY)) {//todo
			$this->_coupon_message = lang('coupon_code_expired');
			return false;
		}elseif($this->_total_price < 30){
			$this->_coupon_message = lang('coupon_sub_cart_total');
			return false;
		}elseif($subscribeInfo['code_status'] == 1){
			$this->_coupon_message = lang('coupon_code_used');
			return false;
		}

		return true;
	}

	protected function _checkNormalCouponAvailable($coupon){
		$now = time();

		if(empty($coupon)){
			$this->_coupon_message = lang('coupon_code_not_exist');
			return false;
		}elseif($coupon['start_time'] > $now){
			$this->_coupon_message = lang('coupon_code_not_start');
			return false;
		}elseif($coupon['end_time'] !=0 && $coupon['end_time'] < $now){
			$this->_coupon_message = lang('coupon_code_expired');
			return false;
		}elseif($coupon['act_status'] != 1){
			$this->_coupon_message = lang('coupon_code_expired');
			return false;
		}elseif($coupon['max_use_times'] > 0 && $coupon['max_use_times'] <= $coupon['cur_use_times']){
			$this->_coupon_message = lang('coupon_code_times_limit');
			return false;
		}elseif($coupon['max_use_times_customer'] > 0 && !empty($this->_user) && $coupon['max_use_times_customer'] <= $this->m_coupon->getCouponUsedTimeByEmail($coupon['coupon_code_id'],$this->_user['email'])){
			$this->_coupon_message = lang('coupon_code_times_limit');
			return false;
		}elseif(strpos($coupon['website_code'],$this->_language_code) === false){
			$this->_coupon_message = lang('coupon_code_not_stuitable');
			return false;
		}

		return true;
	}

	protected function _applyCouponSku($coupon){
		$skus = explode(',',$coupon['range_value']);

		$fit_count = 0;
		$price_fit_range = 0;
		foreach($this->_goods_list as $goods){
			if(!in_array($goods['goods_sn'],$skus)) continue;
			$fit_count++;
			$price_fit_range += $goods['price_subtotal_number'];
		}

		$gift = array();
		$price_discount_amount = 0;
		if($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_REDUCE){
			if($coupon['range_operator'] == 1){
				if($fit_count > 0) $price_discount_amount = sprintf("%.2f",$coupon['act_type_ext']);
			}elseif($coupon['range_operator'] == 2){
				$price_discount_amount = sprintf("%.2f",$coupon['act_type_ext']*$fit_count);
			}
			$price_discount_amount = floatval($price_discount_amount);
			$price_discount_amount = max($price_discount_amount,0);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_DISCOUNT){
			if($coupon['range_operator'] == 1){
				if($fit_count > 0) $price_discount_amount = sprintf("%.2f", $this->_total_price * (1 - $coupon['act_type_ext'] / 100));
			}elseif($coupon['range_operator'] == 2){
				$price_discount_amount = sprintf("%.2f", $price_fit_range * (1 - $coupon['act_type_ext'] / 100));
			}
			$price_discount_amount = floatval($price_discount_amount);
			$price_discount_amount = max($price_discount_amount,0);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_GIFT){
			$gift = $this->m_coupon->getCouponGiftInfo($coupon['act_id'],$this->_language_id);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_COND && $coupon['quota_level']){
			//find qualified level
			$quota_level = json_decode($coupon['quota_level'],true);
			$fit_level = false;
			foreach($quota_level as $record){
				if($price_fit_range < $record['quota_amount']) break;
				$fit_level = $record;
			}

			//calculate discount amount
			if($fit_level !== false){
				if($fit_level['quota_type'] == 1){
					$price_discount_amount = sprintf("%.2f", $fit_level['quota_rate']);
				}elseif($fit_level['quota_type'] == 2){
					$quota_rate = floatval($fit_level['quota_rate'] / 100);
					$quota_rate = max($quota_rate,0);
					$quota_rate = min($quota_rate,1);
					$price_discount_amount = sprintf("%.2f",$price_fit_range*$quota_rate);
				}

			}
			$price_discount_amount = floatval($price_discount_amount);
			$price_discount_amount = max($price_discount_amount,0);
		}

		if($price_discount_amount > 0){
			$this->_discount_list[] = array(
				'name' => $coupon['act_name_language'],
				'amount' => $price_discount_amount,
			);
		}
		if(!empty($gift)){
			$this->_gift = $gift;
		}
	}

	protected function _applyCouponCategory($coupon){
		$cat_ids = explode(',',$coupon['range_value']);

		$fit_count = 0;
		$price_fit_range = 0;
		foreach($this->_goods_list as $goods){
			if(!in_array($goods['cat_id'],$cat_ids)) continue;
			$fit_count++;
			$price_fit_range += $goods['price_subtotal_number'];
		}

		$gift = array();
		$price_discount_amount = 0;
		if($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_REDUCE){
			if($coupon['range_operator'] == 1){
				if($fit_count > 0) $price_discount_amount = sprintf("%.2f",$coupon['act_type_ext']);
			}elseif($coupon['range_operator'] == 2){
				$price_discount_amount = sprintf("%.2f",$coupon['act_type_ext']*$fit_count);
			}
			$price_discount_amount = floatval($price_discount_amount);
			$price_discount_amount = max($price_discount_amount,0);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_DISCOUNT){
			if($coupon['range_operator'] == 1){
				if($fit_count > 0) $price_discount_amount = sprintf("%.2f", $this->_total_price * (1 - $coupon['act_type_ext'] / 100));
			}elseif($coupon['range_operator'] == 2){
				$price_discount_amount = sprintf("%.2f", $price_fit_range * (1 - $coupon['act_type_ext'] / 100));
			}
			$price_discount_amount = floatval($price_discount_amount);
			$price_discount_amount = max($price_discount_amount,0);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_GIFT){
			$gift = $this->m_coupon->getCouponGiftInfo($coupon['act_id'],$this->_language_id);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_COND && $coupon['quota_level']){
			//find qualified level
			$quota_level = json_decode($coupon['quota_level'],true);
			$fit_level = false;
			foreach($quota_level as $record){
				if($price_fit_range < $record['quota_amount']) break;
				$fit_level = $record;
			}

			//calculate discount amount
			if($fit_level !== false){
				if($fit_level['quota_type'] == 1){
					$price_discount_amount = sprintf("%.2f", $fit_level['quota_rate']);
				}elseif($fit_level['quota_type'] == 2){
					$quota_rate = floatval($fit_level['quota_rate'] / 100);
					$quota_rate = max($quota_rate,0);
					$quota_rate = min($quota_rate,1);
					$price_discount_amount = sprintf("%.2f",$price_fit_range*$quota_rate);
				}

			}
			$price_discount_amount = floatval($price_discount_amount);
			$price_discount_amount = max($price_discount_amount,0);
		}

		if($price_discount_amount > 0){
			$this->_discount_list[] = array(
				'name' => $coupon['act_name_language'],
				'amount' => $price_discount_amount,
			);
		}
		if(!empty($gift)){
			$this->_gift = $gift;
		}
	}

	protected function _applyCouponPrice($coupon){
		if ($this->_total_price < $coupon['checkout_value_min'] || ($coupon['checkout_value_max'] > 0 && $this->_total_price > $coupon['checkout_value_max'])) {
			return false;
		}

		$gift = array();
		$price_discount_amount = 0;
		if($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_REDUCE){
			$price_discount_amount = sprintf("%.2f",$coupon['act_type_ext']);
			$price_discount_amount = floatval($price_discount_amount);
			$price_discount_amount = max($price_discount_amount,0);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_DISCOUNT){
			$price_discount_amount = sprintf("%.2f", $this->_total_price * (1 - $coupon['act_type_ext'] / 100));
			$price_discount_amount = floatval($price_discount_amount);
			$price_discount_amount = max($price_discount_amount,0);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_GIFT){
			$gift = $this->m_coupon->getCouponGiftInfo($coupon['act_id'],$this->_language_id);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_COND && $coupon['quota_level']){
		}

		if($price_discount_amount > 0){
			$this->_discount_list[] = array(
				'name' => $coupon['act_name_language'],
				'amount' => $price_discount_amount,
			);
		}
		if(!empty($gift)){
			$this->_gift = $gift;
		}
	}

	protected function _applyCouponAll($coupon){
		$gift = array();
		$price_discount_amount = 0;
		if($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_REDUCE){
			$price_discount_amount = sprintf("%.2f",$coupon['act_type_ext']);
			$price_discount_amount = floatval($price_discount_amount);
			$price_discount_amount = max($price_discount_amount,0);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_DISCOUNT){
			$price_discount_amount = sprintf("%.2f", $this->_total_price * (1 - $coupon['act_type_ext'] / 100));
			$price_discount_amount = floatval($price_discount_amount);
			$price_discount_amount = max($price_discount_amount,0);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_GIFT && $this->_total_price >= $coupon['act_type_ext']){
			$gift = $this->m_coupon->getCouponGiftInfo($coupon['act_id'],$this->_language_id);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_COND && $coupon['quota_level']){
			//find qualified level
			$quota_level = json_decode($coupon['quota_level'],true);
			$fit_level = false;
			foreach($quota_level as $record){
				if($this->_total_price < $record['quota_amount']) break;
				$fit_level = $record;
			}

			//calculate discount amount
			if($fit_level !== false){
				if($fit_level['quota_type'] == 1){
					$price_discount_amount = sprintf("%.2f", $fit_level['quota_rate']);
				}elseif($fit_level['quota_type'] == 2){
					$quota_rate = floatval($fit_level['quota_rate'] / 100);
					$quota_rate = max($quota_rate,0);
					$quota_rate = min($quota_rate,1);
					$price_discount_amount = sprintf("%.2f",$this->_total_price*$quota_rate);
				}
			}
			$price_discount_amount = floatval($price_discount_amount);
			$price_discount_amount = max($price_discount_amount,0);
		}

		if($price_discount_amount > 0){
			$this->_discount_list[] = array(
				'name' => $coupon['act_name_language'],
				'amount' => $price_discount_amount,
			);
		}
		if(!empty($gift)){
			$this->_gift = $gift;
		}
	}

	protected function _saveSessionCouponInfo(){
		$this->session->set('cart_coupon_code',$this->_coupon_code);
	}

	protected function _removeSessionCouponInfo(){
		$this->session->delete('cart_coupon_code');
	}
/*
| -------------------------------------------------------------------
|  Shipping Private Functions
| -------------------------------------------------------------------
*/
	protected function _loadSessionShippingInfo(){
		$shipping_id = $this->session->get('cart_shipping_id');
		if($shipping_id !== false){
			$this->_selected_shipping_id = intval($shipping_id);
		}
	}

	protected function _saveSessionShippingInfo(){
		if($this->_selected_shipping_id == 0){
			$this->session->delete('cart_shipping_id');
		}else{
			$this->session->set('cart_shipping_id',$this->_selected_shipping_id);
		}
	}

	protected function _selectShipping($shipping_id){
		$this->_selected_shipping_id = 0;
		$default_shipping_id = 0;
		$default_shipping_price = 0;
		foreach($this->_shipping_list as $record){
			if($record['flg_active'] === true && $record['id'] == $shipping_id){
				$this->_selected_shipping_id = $record['id'];
				$this->_shipping_price = $record['price'];
				break;
			}
			if(!empty($record['register']) && $record['register']['id'] == $shipping_id){
				$this->_selected_shipping_id = $record['register']['id'];
				$this->_shipping_price = $record['price'] + $record['register']['price'];
				break;
			}
			if($record['flg_active'] === true && $default_shipping_id == 0){
				$default_shipping_id = $record['id'];
				$default_shipping_price = $record['price'];
			}
			if(!empty($record['register']) && $default_shipping_id == 0){
				$default_shipping_id = $record['register']['id'];
				$default_shipping_price = $record['price'] + $record['register']['price'];
			}
		}
		if($this->_selected_shipping_id == 0){
			$this->_selected_shipping_id = $default_shipping_id;
			$this->_shipping_price = $default_shipping_price;
		}

		$this->_total_price += $this->_shipping_price;

		$this->_saveSessionShippingInfo();
	}

	protected function _loadSessionInsuranceInfo(){
		$flg_insurance = $this->session->get('cart_flg_insurance');
		if($flg_insurance !== false){
			$this->_flg_insurance = true;
		}
	}

	protected function _saveSessionInsuranceInfo(){
		if($this->_flg_insurance){
			$this->session->set('cart_flg_insurance',1);
		}else{
			$this->session->delete('cart_flg_insurance');
		}
	}

	protected function _loadSessionSeparatePackageInfo(){
		$flg_separate_package = $this->session->get('cart_flg_separate_package');
		if($flg_separate_package == 1){
			$this->_flg_separate_package = true;
		}else{
			$this->_flg_separate_package = false;
		}
	}

	protected function _saveSessionSeparatePackageInfo(){
		if($this->_flg_separate_package){
			$this->session->set('cart_flg_separate_package',1);
		}else{
			$this->session->set('cart_flg_separate_package',0);
		}
	}
/*
| -------------------------------------------------------------------
|  Payment Private Functions
| -------------------------------------------------------------------
*/
	public function selectPayment($id){
		$this->_payment_id = $id;
		$this->_saveSessionPaymentInfo();
	}

	protected function _loadSessionPaymentInfo(){
		$payment_id = $this->session->get('cart_payment_id');
		if($payment_id !== false){
			$this->_payment_id = $payment_id;
		}
		$payment_country = $this->session->get('cart_payment_country');
		if($payment_country !== false){
			$this->_payment_country = $payment_country;
		}
	}

	protected function _saveSessionPaymentInfo(){
		if($this->_payment_id > 0){
			$this->session->set('cart_payment_id',$this->_payment_id);
		}else{
			$this->session->delete('cart_payment_id');
		}
		if($this->_payment_country !== false){
			$this->session->set('cart_payment_country',$this->_payment_country);
		}else{
			$this->session->delete('cart_payment_country');
		}
	}
}

/* End of file shopping_cart.php */
/* Location: ./application/modules/shopping_cart.php */

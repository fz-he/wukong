<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shipping extends CI_Module {

	const SHIPPING_METHOD_AIRMAIL = 'airmail';
	const SHIPPING_METHOD_STANDARD = 'standard';
	const SHIPPING_METHOD_EXPRESS = 'express';
	const SHIPPING_METHOD_REGISTER_AIRMAIL = 'register_airmail';
	const SHIPPING_METHOD_REGISTER_STANDARD = 'register_standard';
	const SHIPPING_METHOD_CN_MAIL = 'CN-Mail';

	const RATE_GOODS_WEIGHT_TO_FEE = 12;
	const STANDARD_FIRST_WEIGHT_PRICE = 10;
	const STANDARD_STEP_PRICE = 120;

	const EURO_COUNTRY_LIST = 'BD,BN,ID,IN,KH,LA,LK,MM,PH,TH,VN,AT,BE,BG,CY,CZ,DE,DK,EE,ES,FI,FR,GB,GE,GR,HU,IE,IT,LT,LU,MT,MY,NL,PL,PT,RO,SE,SG,SK,ZA';

	protected $_shipping_country = false;
	protected $_shipping_city = false;
	protected $_goods_weight = 0;
	protected $_goods_weight_max = 0;
	protected $_volume_weight = 0;
	protected $_order_price = 0;
	protected $_warehouse_arr = array();
	protected $_max_sumlwh = 0;
	protected $_max_length = 0;
	protected $_flg_sensitive = false;
	protected $_flg_battery = false;

	protected $_shipping_rules = array();

	protected $_shipping_method_list = array();
	protected $_flg_shipping_available = true;

	public function __construct(){
		//load models
		$this->load->model('Appmodel','m_app');
		$this->load->model('Shippingmodel','m_shipping');
	}
/*
| -------------------------------------------------------------------
|  Public Functions
| -------------------------------------------------------------------
*/
	public function setShippingCountry($country_code){
		$country_code = strtoupper($country_code);
		$this->_shipping_country = $country_code;
	}
	
	public function setShippingCity($city){
		$this->_shipping_city = $city;
	}

	public function setOrderGoodsWeight($goods_weight){
		$this->_goods_weight = $goods_weight;
	}

	public function setMaxGoodsWeight($goods_weight){
		$this->_goods_weight_max = $goods_weight;
	}

	public function setOrderVolumeWeight($weight){
		$this->_volume_weight = $weight;
	}

	public function setOrderPrice($price){
		$this->_order_price = $price;
	}

	public function setWarehouse($warehouse_arr){
		$warehouse_arr = array_unique($warehouse_arr);
		$this->_warehouse_arr = $warehouse_arr;
	}

	public function setMaxSumLwh($sum){
		$this->_max_sumlwh = max($sum,0);
	}

	public function setMaxLength($max_length){
		$max_length = max($max_length,0);
		$this->_max_length = $max_length;
	}

	public function setContainSensitive(){
		$this->_flg_sensitive = true;
	}

	public function setContainBattery(){
		$this->_flg_battery = true;
	}

//@todo
	public function getShippingMethodList(){
		$this->_initShippingList();
		$this->_calculateShippingFee();
		$this->_checkShippingAvailable(); //5个变3个
		$this->_mergeShippingMethod();
		$this->_formatShippingMethod();
		return $this->_shipping_method_list;
	}

	public function checkShippingAvailable(){
		return $this->_flg_shipping_available;
	}

	public function checkSelectedShippingAvailable($shipping_id){
		foreach($this->_shipping_method_list as $record){
			if($record['id'] == $shipping_id){
				return $record['flg_active'];
			}
			if(!empty($record['register']) && $record['register']['id'] == $shipping_id){
				return true;
			}
		}

		return false;
	}
/*
| -------------------------------------------------------------------
|  Private Functions
| -------------------------------------------------------------------
*/
	protected function _initShippingList(){
		$shipping_method_code_list = array(
			self::SHIPPING_METHOD_AIRMAIL,
			self::SHIPPING_METHOD_STANDARD,
			self::SHIPPING_METHOD_EXPRESS,
			self::SHIPPING_METHOD_REGISTER_AIRMAIL,
			self::SHIPPING_METHOD_REGISTER_STANDARD,
            self::SHIPPING_METHOD_CN_MAIL
		);

		$this->_shipping_method_list = array();

		global $shipping_method_list;
		foreach($shipping_method_list as $id => $code){
			if(!in_array($code,$shipping_method_code_list)) continue;
			$this->_shipping_method_list[$code] = array(
				'id' => $id,
				'code' => $code,
				'title' => lang('shipping_method_title_'.$code),
				'desc' => lang('shipping_method_desc_'.$code),
			);
		}

		if($this->_onlyOverSeaWarehouse()){
			$this->_shipping_method_list = array();
			$this->_shipping_method_list[self::SHIPPING_METHOD_AIRMAIL] = array(
				'id' => 1,
				'code' => self::SHIPPING_METHOD_AIRMAIL,
				'title' => lang('free_expedite_shipping_title'),
				'desc' => lang('free_expedite_shipping_desc'),
				'flg_overseas' => true,
			);
		}
	}

	protected function _mergeShippingMethod(){
		$register_airmail = array();
		if(isset($this->_shipping_method_list[self::SHIPPING_METHOD_REGISTER_AIRMAIL])){
			if($this->_shipping_method_list[self::SHIPPING_METHOD_REGISTER_AIRMAIL]['flg_active'] === true){
				$register_airmail['id'] = $this->_shipping_method_list[self::SHIPPING_METHOD_REGISTER_AIRMAIL]['id'];
				$register_airmail['code'] = $this->_shipping_method_list[self::SHIPPING_METHOD_REGISTER_AIRMAIL]['code'];
				$register_airmail['price'] = $this->_shipping_method_list[self::SHIPPING_METHOD_REGISTER_AIRMAIL]['price'];
			}

			unset($this->_shipping_method_list[self::SHIPPING_METHOD_REGISTER_AIRMAIL]);
		}

		if(isset($this->_shipping_method_list[self::SHIPPING_METHOD_AIRMAIL])){
			if(!empty($register_airmail)) $register_airmail['price'] -= $this->_shipping_method_list[self::SHIPPING_METHOD_AIRMAIL]['price'];
			$this->_shipping_method_list[self::SHIPPING_METHOD_AIRMAIL]['register'] = $register_airmail;
		}

		$register_standard = array();
		if(isset($this->_shipping_method_list[self::SHIPPING_METHOD_REGISTER_STANDARD])){
			if($this->_shipping_method_list[self::SHIPPING_METHOD_REGISTER_STANDARD]['flg_active'] === true){
				$register_standard['id'] = $this->_shipping_method_list[self::SHIPPING_METHOD_REGISTER_STANDARD]['id'];
				$register_standard['code'] = $this->_shipping_method_list[self::SHIPPING_METHOD_REGISTER_STANDARD]['code'];
				$register_standard['price'] = $this->_shipping_method_list[self::SHIPPING_METHOD_REGISTER_STANDARD]['price'];
			}

			unset($this->_shipping_method_list[self::SHIPPING_METHOD_REGISTER_STANDARD]);
		}

		if(isset($this->_shipping_method_list[self::SHIPPING_METHOD_STANDARD])){
			if(!empty($register_standard)) $register_standard['price'] -= $this->_shipping_method_list[self::SHIPPING_METHOD_STANDARD]['price'];
			$this->_shipping_method_list[self::SHIPPING_METHOD_STANDARD]['register'] = $register_standard;
		}

		if(isset($this->_shipping_method_list[self::SHIPPING_METHOD_EXPRESS])){
			$this->_shipping_method_list[self::SHIPPING_METHOD_EXPRESS]['register'] = array();
		}

		if(isset($this->_shipping_method_list[self::SHIPPING_METHOD_CN_MAIL])){
			$this->_shipping_method_list[self::SHIPPING_METHOD_CN_MAIL]['register'] = array();
		}

		foreach ($this->_shipping_method_list as $code => $params) {
			//如果track_number为空
			if(empty($params['register']) && ( $code != self::SHIPPING_METHOD_EXPRESS || $code != self::SHIPPING_METHOD_CN_MAIL) ) {
				$params['message'] = lang('shipping_not_available');
			}

			$this->_shipping_method_list[$code] = $params;
		}
	}

	protected function _formatShippingMethod(){
		foreach($this->_shipping_method_list as $code => $params){
			$this->_shipping_method_list[$code]['formatted_price'] = formatPrice($params['price']);
			if(!empty($params['register'])){
				$this->_shipping_method_list[$code]['register']['formatted_price'] = formatPrice($params['register']['price']);
			}
		}
	}
/*
| -------------------------------------------------------------------
|  Calculate Shipping Fee Private Functions
| -------------------------------------------------------------------
*/
	protected function _calculateShippingFee(){
		foreach($this->_shipping_method_list as $code => $params){
			$price = false;
			switch($code){
				case self::SHIPPING_METHOD_AIRMAIL : $price = $this->_calculateShippingFeeAirmail();break;
				case self::SHIPPING_METHOD_STANDARD : $price = $this->_calculateShippingFeeStandard();break;
				case self::SHIPPING_METHOD_EXPRESS : $price = $this->_calculateShippingFeeExpress();break;
				case self::SHIPPING_METHOD_REGISTER_AIRMAIL : $price = $this->_calculateShippingFeeRegisterAirmail();break;
				case self::SHIPPING_METHOD_REGISTER_STANDARD : $price = $this->_calculateShippingFeeRegisterStandard();break;
                case self::SHIPPING_METHOD_CN_MAIL : $price = $this->_calculateShippingFeeCnmail();break;
			}
			if($price === false){
				unset($this->_shipping_method_list[$code]);
			}
			$this->_shipping_method_list[$code]['price'] = $price;
		}
	}

	protected function _calculateShippingFeeAirmail(){
		return 0;
	}
        
        protected function _calculateShippingFeeCnmail(){
		return 0;
	}

	protected function _calculateShippingFeeStandard(){
		$airmail_price = $this->_goods_weight * self::RATE_GOODS_WEIGHT_TO_FEE;
		$airmail_price = sprintf("%.2f",$airmail_price);

		$price = self::STANDARD_FIRST_WEIGHT_PRICE + $this->_goods_weight * self::STANDARD_STEP_PRICE;
		$price = exchangePriceToDefaultCurrency($price,'CNY');
		$price -= $airmail_price;
		$price = max($price,0);

		return $price;
	}

	protected function _calculateShippingFeeExpress(){
		$airmail_price = $this->_goods_weight * self::RATE_GOODS_WEIGHT_TO_FEE;
		$airmail_price = sprintf("%.2f",$airmail_price);
		$weight = max($this->_volume_weight,$this->_goods_weight);

		$step_price = $this->m_shipping->getDHLStepPrice($this->_shipping_country);
		if(empty($step_price)) return false;

		$price = 0;
		if($weight < 20.5){

			$weight = max($weight,0.5);
			$price = $step_price['first_weight'] + ceil( ( $weight - 0.5 ) / 0.5 ) * $step_price['step_weight'];

		}elseif($weight >= 20.5 && $weight <= 30){

			$price = ceil($weight) * $step_price['21_30'];

		}elseif($weight > 30 && $weight <= 50){

			$price = ceil($weight) * $step_price['31_50'];

		}elseif($weight > 50 && $weight <= 70){

			$price = ceil($weight) * $step_price['51_70'];

		}elseif($weight > 70 && $weight <= 100){

			$price = ceil($weight) * $step_price['71_100'];

		}elseif($weight > 100 && $weight <= 200){

			$price = ceil($weight) * $step_price['101_200'];

		}elseif($weight > 200 && $weight <= 299){

			$price = ceil($weight) * $step_price['201_299'];

		}elseif($weight > 299 && $weight <= 300){

			$price = ceil($weight) * $step_price['300'];

		}elseif($weight > 300){

			$price = ceil($weight) * $step_price['301'];

		}else{

			return false;

		}

		$price = exchangePriceToDefaultCurrency($price,'CNY');
		$price -= $airmail_price;
		$price = max($price,0);

		return $price;
	}

	protected function _calculateShippingFeeRegisterAirmail(){
		$price = $this->_calculateShippingFeeAirmail();
		$price += 1.69;

		return $price;
	}

	protected function _calculateShippingFeeRegisterStandard(){
		$price = $this->_calculateShippingFeeStandard();
		if(strtolower($this->_shipping_country) == 'de'){
            $price += 8.69;
		}else{
			$price += 2.19;
		}

		return $price;
	}
/*
| -------------------------------------------------------------------
|  Check Shipping Available Private Functions
| -------------------------------------------------------------------
*/
	protected function _checkShippingAvailable(){
		
		global $cn_mail_citys;
		$rules = $this->m_shipping->getCountryShippingRule($this->_shipping_country);

		$this->_shipping_rules = reindexArray($rules,'shipping_code');
		$flg_only_oversea_warehouse = $this->_onlyOverSeaWarehouse();
		$flg_only_local_warehouse = $this->_onlyLocalWarehouse();

		foreach ($this->_shipping_method_list as $code => $params) {
			$params['flg_active'] = true;
			$params['message'] = '';

			// 判断商品是不是都是海外仓
			if(isset($params['flg_overseas']) && $params['flg_overseas'] === true) { 
				$params['flg_overseas'] = true;
			} else {
				$params['flg_overseas'] = false;
			}

			if (isset($this->_shipping_rules[$code])) {
				$rule = $this->_shipping_rules[$code];
				$this->_checkShippingRule($params, $rule);
			}

			//如果track_number为空
			if(empty($params['register']) && ( $code != self::SHIPPING_METHOD_EXPRESS || $code != self::SHIPPING_METHOD_CN_MAIL) ) {
				$params['message'] = lang('shipping_not_available');
			}

			//order contains only oversea warehouse goods,just show airmail
			if ($flg_only_oversea_warehouse && $code != self::SHIPPING_METHOD_AIRMAIL) {
				$params['flg_active'] = false;
			}

			if (empty($this->_shipping_rules)) {
				$params['flg_active'] = false;
			}

			$this->_shipping_method_list[$code] = $params;
		}
		if ($this->_multipleOverSeaWarehouse() || ($this->_hasOverSeaWarehouse() && $this->_orderOverSeaWarehouse() != $this->_shipping_country)) {
			$this->_flg_shipping_available = false;
			foreach ($this->_shipping_method_list as $code => $params) {
				$this->_shipping_method_list[$code]['message'] = '';
				$this->_shipping_method_list[$code]['flg_active'] = false;
			}
		}

		$flg_enabled_shipping_exists = false;
		foreach($this->_shipping_method_list as $code => $params){
			if($params['flg_active'] === true){
				$flg_enabled_shipping_exists = true;
				break;
			}
		}
		if($flg_enabled_shipping_exists === false){
			$this->_flg_shipping_available = false;
		}
	}

	protected function _checkShippingRule(&$params,$rule){
		$price_limit = exchangePriceToDefaultCurrency(88,'EUR');
		if($rule['status_active'] == 1){
			$params['flg_active'] = true;
		}elseif($rule['status_disable'] == 1){
			$params['flg_active'] = false;
			$params['message'] = lang('shipping_not_available');
		}elseif($rule['weight_limit'] > 0 && $this->_goods_weight_max >= $rule['weight_limit']){
			$params['flg_active'] = false;
			$params['message'] = lang('shipping_not_available_for_product');
		}elseif($rule['volume_limit'] > 0 && $this->_max_sumlwh >= $rule['volume_limit']){
			$params['flg_active'] = false;
			$params['message'] = lang('shipping_not_available_for_product');
		}elseif($rule['length_limit'] > 0 && $this->_max_length >= $rule['length_limit']){
			$params['flg_active'] = false;
			$params['message'] = lang('shipping_not_available_for_product');
		}elseif($rule['status_sensitive_disable'] == 1 && $this->_flg_sensitive){
			$params['flg_active'] = false;
			$params['message'] = lang('shipping_not_available_for_product');
		}elseif($rule['status_battery_disable'] == 1 && $this->_flg_battery){
			$params['flg_active'] = false;
			$params['message'] = lang('shipping_not_available_for_product');
		}elseif(strpos(self::EURO_COUNTRY_LIST,$this->_shipping_country) !== false && $rule['shipping_code'] != self::SHIPPING_METHOD_EXPRESS && $this->_order_price > $price_limit){
			$params['flg_active'] = false;
			$params['message'] = sprintf(lang('shipping_not_available_for_price'),formatPrice($price_limit));
		}

		return true;
	}
/*
| -------------------------------------------------------------------
|  Warehouse Private Functions
| -------------------------------------------------------------------
*/
	protected function _orderOverSeaWarehouse(){
		$order_oversea_warehouse = array_intersect(AppConfig::$warehouse_oversea,$this->_warehouse_arr);
		$order_oversea_warehouse = current($order_oversea_warehouse);
		if('UK' == $order_oversea_warehouse){
			$order_oversea_warehouse = 'GB';
		}
		return $order_oversea_warehouse;
	}

	protected function _onlyOverSeaWarehouse(){
		$order_local_warehouse = array_diff($this->_warehouse_arr,AppConfig::$warehouse_oversea);
		$order_oversea_warehouse = array_intersect(AppConfig::$warehouse_oversea,$this->_warehouse_arr);

		$flag = false;
		if(empty($order_local_warehouse) && !empty($order_oversea_warehouse)){
			$flag = true;
		}

		return $flag;
	}
        
    protected function _onlyLocalWarehouse() {
		global $warehouse_local;
		$order_oversea_warehouse = array_diff($this->_warehouse_arr, $warehouse_local);
		$order_local_warehouse = array_intersect($warehouse_local, $this->_warehouse_arr);

		$flag = false;
		if (empty($order_oversea_warehouse) && !empty($order_local_warehouse)) {
			$flag = true;
		}

		return $flag;
	}

	protected function _hasOverSeaWarehouse(){
		$count = $this->_overSeaWarehouseCount();
		return ($count > 0);
	}

	protected function _multipleOverSeaWarehouse(){
		$count = $this->_overSeaWarehouseCount();
		return ($count > 1);
	}

	protected function _overSeaWarehouseCount(){
		$order_oversea_warehouse = array_intersect(AppConfig::$warehouse_oversea,$this->_warehouse_arr);
		return count($order_oversea_warehouse);
	}
}

/* End of file shipping.php */
/* Location: ./application/modules/shipping.php */
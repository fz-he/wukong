<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payment extends CI_Module {

	const PAYMENT_METHOD_PAYPAL_EC = 'paypal_ec';
	const PAYMENT_METHOD_PAYPAL = 'paypal';
	const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';
	const PAYMENT_METHOD_SOFORT = 'sofort';
	const PAYMENT_METHOD_SAFETYPAY = 'safetypay';
	const PAYMENT_METHOD_BANK = 'bank';
	const PAYMENT_METHOD_ADYEN = 'adyen';

	protected $_user_id = 0;
	protected $_shipping_country = false;
	protected $_payment_country = false;
	protected $_current_currency = false;

	protected $_payment_list = array();

	public function __construct(){
		//load models
		$this->load->model('Appmodel','m_app');
		$this->GoodsModel = new GoodsModel();
		$this->OrderModel = new OrderModel();
		$this->load->model('Addressmodel','m_address');
		$this->load->model('Paymentmodel','m_payment');
		$this->CategoryModel = new CategoryModel();
		//$this->load->module('payment/paypal');
		
		$this->load->module('payment/checkout');
		//$this->load->module('payment/sofort');
		//$this->load->module('payment/safetypay');
		$this->load->module('payment/adyen');

		$this->_user_id = $this->m_app->getCurrentUserId();
		$this->_current_currency = $this->m_app->currentCurrency();
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

	public function setPaymentCountry($country_code){
		$country_code = strtoupper($country_code);
		$this->_payment_country = $country_code;
	}

	public function loadPaymentList($country = '',   $payAmount = 0){
		$this->_initPaymentList($country, $payAmount);
		foreach($this->_payment_list as $code => $params){
			$params = $this->_checkPaymentAvailable($params);
			if( $params['flg_active']){//检测不通过的 直接不显示？？？@todo 待确定
				$this->_payment_list[$code] = $params;
			}
		}
	}
	
	public function loadPaypalECPayment(){
		global $payment_list;
		global $paymentMethodsIcon;
	
		$this->_payment_list = array();	
		if(!empty( $payment_list[1])){			
			$code = $payment_list[1];//1: paypalEC
			if(!empty($paymentMethodsIcon[$code] )){
				$this->_payment_list[$code] = array(
					'id' => 1,
					'code' => $code,
					'icon' => $paymentMethodsIcon[$code]['icon'],
					'title' => $paymentMethodsIcon[$code]['name'],
					'flg_active' => true,
					'message' => '',
				);
			}
		}
	}

	public function getPaymentList(){
		return $this->_payment_list;
	}

	public function checkPlaceOrderPaymentAvailable($payment_id){
		$flg_available = false;
		foreach($this->_payment_list as $record){
			if($record['id'] != $payment_id) continue;
			if($record['flg_active'] !== true || $record['code'] == self::PAYMENT_METHOD_PAYPAL_EC) continue;
			$flg_available = true;
			break;
		}

		return $flg_available;
	}

	public function getPlaceOrderDefaultAvailablePayment(){
		foreach($this->_payment_list as $record){
			if($record['flg_active'] !== true || $record['code'] == self::PAYMENT_METHOD_PAYPAL_EC) continue;
			return $record['id'];
		}

		return false;
	}

	public function checkPaymentEnabled($code){
		global $payment_list;

		if(in_array($code,$payment_list)){
			$params = array();
			$params['flg_active'] = true;
			$params['code'] = $code;
			$res = $this->_checkPaymentAvailable($params);
			if($res['flg_active']){
				return true;
			}
		}

		return false;
	}

	public function markOrderPaid($order,$txn_id,$payer_email){
		//$cid = $this->input->get('cid');
		//if($cid === false) $cid = generateGACid();
		$cid = $order['ga_cid'];
		if($order['pay_status'] != PS_PAID){
			$this->OrderModel->updateOrder($order['order_id'],array(
				'order_status' => OS_CONFIRMED,
				'confirm_time' => time(),
				'pay_status' => PS_PAID,
				'pay_time' => time(),
				'money_paid' => $order['order_amount'],
				'send_nopay_email' => 1,
				'pay_note' => $txn_id
			));
			$this->OrderModel->createOrderAction(array(
				'order_id' => $order['order_id'],
				'action_user' => 'Buyer',
				'order_status' => OS_CONFIRMED,
				'shipping_status' => SS_UNSHIPPED,
				'pay_status' => PS_PAID,
				'action_place' => 0,
				'action_note' => $txn_id,
				'log_time' => time(),
			));
			$this->OrderModel->createOrderPayHistory(array(
				'order_sn' => $order['order_sn'],
				'payment_type' => $order['pay_name'],
				'payment_user' => $payer_email,
				'order_amount' => $order['base_money_paid'],
				'created_at' => date('Y-m-d H:i:s'),
			));
			$this->OrderModel->updatePayLog($order['order_id'],array('is_paid' => 1));
			if(!$this->OrderModel->checkOrderSentGAInfo($order['order_sn'])){
				//todo GA统计 @jingliang
				 $this->_processGAInfo($order,$cid);
			}
			$price_format = $this->m_app->getConfig(strtolower($order['currency_code']).'_price_format','$%s');
			$goods_list = $this->OrderModel->getOrderGoodsListLatest($order['order_id']);
			foreach($goods_list as $key => $record){
				$goods_list[$key]['final_price'] = sprintf($price_format,exchangePrice($record['final_price']*$record['goods_number'],$order['currency_code']));
			}
			$order['goods_list'] = $goods_list;
			$order['add_time'] = date('F j, Y h:i:s A e', $order['add_time']);
			$order['country'] = $this->m_address->getCountryName($order['country']);
			$order['shipping_name'] = ucwords(str_replace('_',' ',$order['shipping_name']));
			$order['goods_amount'] = sprintf($price_format,$order['goods_amount']);
			$order['shipping_fee'] = sprintf($price_format,$order['shipping_fee']);
			$order['insure_fee'] = sprintf($price_format,$order['insure_fee']);
			$order['integral_money'] = sprintf($price_format,$order['integral_money']);
			$order['discount'] = sprintf($price_format,$order['discount']-$order['integral_money']);
			$order['order_amount'] = sprintf($price_format,$order['order_amount']);

			//兼容老版重支付
			switch($order['pay_id']){
				case 1: $paymentName = 'Paypal EC';break;
				case 2: $paymentName = 'PayPal'; break;
				case 3: $paymentName = 'Wire transfer';break;
				case 7: $paymentName = 'Credit Card / Bank / Others';break;
				case ($order['pay_id'] >  19 && $order['pay_id'] < 45) :
					global $payment_list;
					global $availablePaymentMethodsIcon;
					$paymentCode = $payment_list[$order['pay_id']];
					$paymentName = $availablePaymentMethodsIcon[$paymentCode]['name'];
					break;
				default: $paymentName = 'Unknown what payment method'; break;
			}
			$order['pay_name']  = $paymentName;
			//old sendMail
			//processMail('order_pay',$payer_email,sprintf(lang('mail_subject_order_pay'),$order['order_sn']),array('order'=>$order));
			//付款邮件发送
			$this->_newSendMail( $order );
		}
	}

	public function markOrderRefund($order,$txn_id){
		$this->OrderModel->updateOrder($order['order_id'],array(
			'pay_status' => PS_REFUND,
		));
		$this->OrderModel->createOrderAction(array(
			'order_id' => $order['order_id'],
			'action_user' => 'Buyer',
			'order_status' => OS_CONFIRMED,
			'shipping_status' => SS_UNSHIPPED,
			'pay_status' => PS_REFUND,
			'action_place' => 0,
			'action_note' => $txn_id,
			'log_time' => time(),
		));
	}

	public function markOrderPaying($order,$txn_id){
		$this->OrderModel->updateOrder($order['order_id'],array(
			'pay_status' => PS_PAYING,
		));
		$this->OrderModel->createOrderAction(array(
			'order_id' => $order['order_id'],
			'action_user' => 'Buyer',
			'order_status' => $order['order_status'],
			'shipping_status' => $order['shipping_status'],
			'pay_status' => PS_PAYING,
			'action_place' => 0,
			'action_note' => $txn_id,
			'log_time' => time(),
		));
	}
/*
| -------------------------------------------------------------------
|  Private Functions
| -------------------------------------------------------------------
*/
	protected function _initPaymentList($countryCode = '', $payAmount = 0){
		global $payment_list;
		global $paymentMethodsIcon;

		$this->_payment_list = array();
		//非adyen里的支付方式 2:PPSK(PPHK)
		if( !empty( $payment_list[2] ) ){
			$code = $payment_list[2];
			if(!empty($paymentMethodsIcon[$code] )){
				$this->_payment_list[$code] = array(
					'id' => 2,
					'code' => $code,
					'icon' => $paymentMethodsIcon[$code]['icon'],
					'title' => $paymentMethodsIcon[$code]['name'],
					'flg_active' => true,
					'message' => '',
				);
			}
		}
		
		$availableMethods = array();
		$paymentMethods = $this->getAvailablePayMethods($countryCode, $payAmount);

		global $allowableMinPayAmount;
		$currencyCode = $this->getCurCurrency();
		if(!empty($paymentMethods)){
			$currencyInfo = $this->m_app->getConfigCurrency($currencyCode, 1);
			$usdPayAmount = $currencyInfo['rate'] * 1;//1美元对应的金额
			foreach ($paymentMethods as $index => $method){
				//最小支付金额条件过滤 先粗略判断
				if($payAmount < $usdPayAmount){
					if(!empty($allowableMinPayAmount[$method['brandCode']]) &&
							isset($allowableMinPayAmount[$method['brandCode']][$currencyCode] )
							){
						if($payAmount >= $allowableMinPayAmount[$method['brandCode']][$currencyCode]){
							$availableMethods[ $method['brandCode']] = $method['brandCode'];
						}
					}
				}else{
					$availableMethods[ $method['brandCode']] = $method['brandCode'];
				}
			}
		}

		$this->_payment_list = array();
		//非adyen里的支付方式
		foreach($payment_list as $payId => $code){
			if($payId >7 ){
				break;
			}
			if(in_array($payId, array(1,2))){//1,ppec,2 ppsk
				if(!empty($paymentMethodsIcon[$code] )){
					$this->_payment_list[$code] = array(
						'id' => $payId,
						'code' => $code,
						'icon' => $paymentMethodsIcon[$code]['icon'],
						'title' => $paymentMethodsIcon[$code]['name'],
						'flg_active' => true,
						'message' => '',
					);
				}
			}
		}
		//顺序以请求的到数据为准
		$payment_key_list = array_combine ($payment_list , array_keys($payment_list) );
		foreach($availableMethods as $brandCode => $code){
			if(!empty($payment_key_list[$brandCode])){
				if( !empty( $paymentMethods[ $brandCode ] ) ){
					$this->_payment_list[$brandCode] = array(
						'id' => $payment_key_list[$brandCode],
						'code' => $code,
						'icon' => $paymentMethods[$brandCode]['icon'],
						'title' => $paymentMethods[$brandCode]['name'],
						'flg_active' => true,
						'message' => '',
					);
				}
			}
		}
		//PPSK 和PPUK 不同时出现
		if(!empty($this->_payment_list['paypal']) &&  !empty($this->_payment_list['paypalsk'])){
			unset($this->_payment_list['paypalsk']);
		}
		//bank adyen里的电汇不存在时，显示原先电汇支付方式
		if(  empty( $this->_payment_list['bankTransfer_BE'] ) && empty( $this->_payment_list['bankTransfer_CH'] )
				&& empty( $this->_payment_list['bankTransfer_DE'] ) && empty( $this->_payment_list['bankTransfer_GB'] ) 
				&& empty( $this->_payment_list['bankTransfer_NL'] ) && empty( $this->_payment_list['bankTransfer_IBAN']) ){			
			if(!empty($payment_list[3])){
				$code = $payment_list[3];
				if(!empty($paymentMethodsIcon[$code])){
					$this->_payment_list[$code] = array(
						'id' =>  3,
						'code' => $code,
						'icon' => $paymentMethodsIcon[$code]['icon'],
						'title' => $paymentMethodsIcon[$code]['name'],
						'flg_active' => true,
						'message' => '',
					);
				}
			}
		}
	}

	//获取可用的支付方式，由country和currency决定
	public function getAvailablePayMethods( $countryCode , $payAmount ){
		if(empty($countryCode)){
			$countryCode = 'US';
		}
		if( empty($payAmount) || ! is_numeric($payAmount)){
			$payAmount = 0;
		}
		$currencyCode = $this->getCurCurrency();
		if(empty($currencyCode)){
			$currencyCode = 'EUR';
		}

		$response = $this->adyen->callDirectoryLookupRequest(strtoupper($countryCode), strtoupper($currencyCode), $payAmount);

		$response = json_decode($response, true);
		$response = $response['paymentMethods'];
		if(!empty( $response)){
			global $availablePaymentMethodsIcon;
			//先规定图标后缀格式都是 .jpg 不然会有问题
			foreach($response as $index => $method){
				if( !empty( $availablePaymentMethodsIcon[ $method['brandCode'] ] ) ){
					$response[ $method['brandCode'] ] = $method;
					$response[ $method['brandCode'] ]['icon']= $availablePaymentMethodsIcon[$method['brandCode']]['icon'];
					unset($response[ $index ] );
				}
			}			
		}
		if( ( !empty($response['bankTransfer_BE']) || !empty($response['bankTransfer_CH'])
					|| !empty($response['bankTransfer_DE']) || !empty($response['bankTransfer_GB']) 
					|| !empty($response['bankTransfer_NL'] ) )
					&& !empty($response['bankTransfer_IBAN']) ){
			unset( $response['bankTransfer_IBAN'] );
		}
		
		return $response;
	}

	public function getCurCurrency(){
		$currency = $this->m_app->currentCurrency();
		if(in_array($currency,array('BRL' , 'INR'))){
			return DEFAULT_CURRENCY;
		}else{
			return $currency;
		}
	}

	/**
	 * 已不再使用
	 */
	protected function _checkPaymentAvailableBat($params){
		if($params['code'] == self::PAYMENT_METHOD_CREDIT_CARD){
			$params['check_list'] = array(
				0 => array('flg_selected'=>false,'message'=>lang('blacklist_not_credit_msg2')),
				1 => array('flg_selected'=>false,'message'=>lang('blacklist_not_credit_msg3')),
				2 => array('flg_selected'=>false,'message'=>lang('blacklist_not_credit_msg4')),
			);

			$params['flg_hide'] = true;
			if($this->adyen->checkPaymentAvailable($this->_payment_country)){
				$params['flg_active'] = false;
			}else{
				$params['flg_hide'] = false;
			}

			if(!$this->checkout->checkCountryAvailable($this->_shipping_country)){
				$params['flg_active'] = false;
				$params['check_list'][0]['flg_selected'] = true;
				$params['flg_hide'] = false;
			}

			$config = $this->m_payment->getCreditCardLimitConfig();
			if(!isset($config['pay_amount'])){
				unset($params['check_list'][1]);
			}else{
				$params['check_list'][1]['message'] = str_replace('{$day}',$config['pay_amount']['day'],$params['check_list'][1]['message']);
				$params['check_list'][1]['message'] = str_replace('{$price}',formatPrice($config['pay_amount']['amount']),$params['check_list'][1]['message']);

				if(!$this->checkout->checkOrderAmountLimit($config['pay_amount'])){
					$params['flg_active'] = false;
					$params['check_list'][1]['flg_selected'] = true;
					$params['flg_hide'] = false;
				}
			}

			if(!isset($config['pay_num'])){
				unset($params['check_list'][2]);
			}else{
				$params['check_list'][2]['message'] = str_replace('{$day}',$config['pay_num']['day'],$params['check_list'][2]['message']);
				$params['check_list'][2]['message'] = str_replace('{$times}',$config['pay_num']['number'],$params['check_list'][2]['message']);

				if(!$this->checkout->checkOrderCountLimit($config['pay_num'])){
					$params['flg_active'] = false;
					$params['check_list'][2]['flg_selected'] = true;
					$params['flg_hide'] = false;
				}
			}
		}
		elseif($params['code'] == self::PAYMENT_METHOD_PAYPAL){
			if(!$this->paypal->checkPaymentAvailable()){
				$params['flg_active'] = false;
			}
		}


		//if($params['code'] == self::PAYMENT_METHOD_ADYEN){
		if($params['code'] != 'paypal_ec'){
			$config = $this->m_payment->getCreditCardLimitConfig();
			if(!$this->adyen->checkPaymentAvailable($this->_payment_country)){
				$params['flg_active'] = false;
			}elseif(!$this->checkout->checkCountryAvailable($this->_shipping_country)){
				$params['flg_active'] = false;
			}elseif(isset($config['pay_amount']) && !$this->checkout->checkOrderAmountLimit($config['pay_amount'])){
				$params['flg_active'] = false;
			}elseif(isset($config['pay_num']) && !$this->checkout->checkOrderCountLimit($config['pay_num'])){
				$params['flg_active'] = false;
			}
		}
		return $params;
	}

	protected function _checkPaymentAvailable($params){
		//if($params['code'] == self::PAYMENT_METHOD_ADYEN){
		if($params['code'] != 'paypal_ec'){
			//信用卡额度检测已去掉
			if(!$this->adyen->checkPaymentAvailable($this->_payment_country)){
				$params['flg_active'] = false;
			}elseif(!$this->checkout->checkCountryAvailable($this->_shipping_country)){
				$params['flg_active'] = false;
			}
		}
		return $params;
	}
/*
| -------------------------------------------------------------------
|  GA Functions
| -------------------------------------------------------------------
*/
	protected function _processGAInfo($order,$cid){
		global $payment_list;
		global $shipping_method_list;
		$ga_order = array(
			'v' => 1,
			'tid' => 'UA-44016380-1',
			'cid' => $cid,
			't' => 'transaction',
			'dh' => 'eachbuyer.com',
			'ds' => 'web',
			'ti' => $order['order_sn'],
			'tr' => $order['base_order_amount'],
			'tt' => $order['base_discount'],
			'ts' => $order['base_shipping_fee'] + $order['base_insure_fee'],
			'tcc' => $order['coupon_code'],
			'pa' => 'purchase',
		);

		$goods_list = $this->OrderModel->getOrderGoodsListLatest($order['order_id']);
		foreach ($goods_list as $index => $record) {
				$index += 1;
				$ga_order['pr' . $index . 'id'] =  $record['product_id'];
				$ga_order['pr' . $index . 'pr'] =  $record['final_price'];
				$ga_order['pr' . $index . 'qt' ] =  $record['goods_number'];
		}
		
		if($this->_sendInfoToGA($ga_order)){
			$this->OrderModel->createGAOrderHistory(array(
				'order_id' => $order['order_id'],
				'order_sn' => $order['order_sn'],
				'created_at' => date('Y-m-d H:i:s'),
			));
		}
	}

	protected function _sendInfoToGA($info){
		$params = '';
		foreach($info as $key => $value){
			$params .= $key .'='.urlencode($value).'&';
		}

		$flag = false;
		for($i=1;$i<=5;$i++){
			$request = curl_init();
			curl_setopt($request, CURLOPT_URL,'http://www.google-analytics.com/collect');
			curl_setopt($request, CURLOPT_VERBOSE, 1);
			curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($request, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($request, CURLOPT_POST, 1);
			curl_setopt($request, CURLOPT_POSTFIELDS, $params);
			curl_setopt($request, CURLOPT_HEADER, true);
			curl_exec($request);
			$response = curl_getinfo($request, CURLINFO_HTTP_CODE);
			curl_close($request);
			if( substr($response,0,1) == 2 ){
				$flag = true;
				break;
			}
		}

		return $flag;
	}

	/**
	 * new当单付款邮件发送
	 * @param  array $order 订单信息
	 */
	protected function _newSendMail( $order ){
		try
		{
			$type = 2; //订单支付成功
			$currentLanguageId = $this->m_app->getLanguageCodeByCode( $order['language_code'] );
			//获得邮件模板信息
			$EmailtemplateModel = new EmailtemplateModel();
			$templateInfo = $EmailtemplateModel->getSystemEmailTemplateInfo( $type, $currentLanguageId );

			//模板启用
			if( isset( $templateInfo['status'] ) && isset( $templateInfo['eid'] ) && $templateInfo['status'] == 1 && !empty( $templateInfo['eid'] ) ){
				//订单商品
				$orderGoodsList = $this->OrderModel->getOrderGoodsList( $order['order_id'] );
				//格式订单价格等信息
				foreach($orderGoodsList as $key => $record) {
					$orderGoodsList[$key]['goods_price'] = formatPrice( $record['final_price'], $order['currency_code'] , $order['store_to_order_rate'] );
				}
				$productIds = extractColumn( $orderGoodsList, 'product_id' );
				$order = eb_htmlspecialchars( $order );
				//订单商品信息dom
				$order['base_discount'] = $order['base_discount']-$order['base_integral_money'];
				$order['discount'] = formatPrice( $order['base_discount' ], $order['currency_code'] , $order['store_to_order_rate']  );
				$orderInfoDomArray = $EmailtemplateModel->getEmailOrderInfoDom( $order, $orderGoodsList, 'payment' );
				//推荐商品
				$ProductModel = new ProductModel();
				$recommendProList = $ProductModel->getEmailRecommendProduct( $currentLanguageId, $productIds, FALSE, array(), '', $order['currency_code'], $order['language_code'], '', 'payment' );
				$recommendProDom = $EmailtemplateModel->getEmailRecommendProductDom( $recommendProList );

				global $lang_basic_url;
				//邮件模版参数
				$contentParam = array(
					'SITE_DOMAIN' => rtrim( $lang_basic_url[ $order['language_code'] ], '/' ), //域名链接
					'SITE_DOMAIN1' => COMMON_DOMAIN, //域名
					'CS_EMAIL' => 'cs@'.COMMON_DOMAIN,
					'USER_NAME' => $order['consignee'],
					'ORDER_NUM' => $order['order_sn'],
					'ORDER_TIME' => $order['add_time'],
					'ORDER_INFO' => $orderInfoDomArray['order_info'],
					'SHIP_ADDRESS' => $orderInfoDomArray['address'],
					'SHIP_WAY' => $order['shipping_name'],
					'PAY_WAY' => $order['pay_name'],
					'ITEM_REO' => $recommendProDom,
				);

				//发送 $order['email'] luowenyong@hofan.cn
				$result = HelpOther::sendSystemEmail( $order['email'], $templateInfo['eid'], $contentParam );
				//发送失败重试一次
				if( trim( $result ) !== 'OK' ){
					$result = HelpOther::sendSystemEmail( $order['email'], $templateInfo['eid'], $contentParam );
				}
				if( trim( $result ) !== 'OK' ){
					$logInfo = '[payment] ORDERID:#'.$order['order_sn'].' - EMAIL:'.$order['email'].' - EID:'.$templateInfo['eid'].' - ERROR:'.$result;
					$this->log->write( Log::LOG_TYPE_SYSTEM_EMAIL , $logInfo, true );
				}
			}
		}
		catch( Exception $e )
		{
			return;
		}
	}
}
/* End of file payment.php */
/* Location: ./application/modules/payment.php */
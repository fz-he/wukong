<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * Model for order.
 * @author lucas
 */
class OrderModel extends CI_Model {

	public function __construct() {
		parent::__construct();
	}

	/*
	| -------------------------------------------------------------------
	|  DB Read Functions
	| -------------------------------------------------------------------
	*/

	/**
	 * 获得订单详细信息
	 * @param inc $userId
	 * @param inc $page
	 * @param inc $limit
	 * @return array 返回订单详细信息数组
	 * @author lucas
	 */
	public function getOrderList( $userId, $page = 1, $limit = 10 ){
		$start = ($page-1)*$limit;

		$this->db_ebmaster_write->from('order_info');
		$this->db_ebmaster_write->where('user_id', $userId);
		$count = $this->db_ebmaster_write->count_all_results('', SQL_EXECUTE_RETAIN_CONDITION);

		$this->db_ebmaster_write->order_by('add_time', 'desc');
		$this->db_ebmaster_write->limit($limit,$start);
		$query = $this->db_ebmaster_write->get();
		$list = $query->result_array();

		return array($list,$count);
	}

	/**
	 * 格式订单信息
	 * @param array $order 订单信息
	 * @param array
	 */
	public function calculateOrder(&$order){
		$complete_payment_limit_time = 1209600;
		$order['flg_complete_payment'] = false;
		if($_SERVER['REQUEST_TIME'] - $order['add_time'] <= $complete_payment_limit_time && $order['pay_status'] == PS_UNPAID && $order['shipping_status'] == SS_UNSHIPPED){
			$order['flg_complete_payment'] = true;
		}
		if(in_array($order['pay_id'],array(1,3))){
			$order['flg_complete_payment'] = false;
		}
		$order['add_time'] = date('m/d/Y',$order['add_time']);
		$order['total'] = $order['order_amount'] <= 0?$order['money_paid']:$order['order_amount'];

		$order['order_status_desc'] = id2name($order['order_status'],array(
			OS_UNCONFIRMED => lang('order_status_unconfirmed'),
			OS_CONFIRMED => lang('order_status_confirmed'),
			OS_CANCELED => lang('order_status_canceled'),
			OS_INVALID => lang('order_status_invalid'),
			OS_RETURNED => lang('order_status_returned'),
			OS_SPLITED => lang('order_status_splited'),
			OS_SPLITING_PART => lang('order_status_spliting_part'),
			10 => lang('order_status_canceled'),
			11 => lang('order_status_unconfirmed'),
			12 => lang('order_status_unconfirmed'),
			13 => lang('order_status_confirmed'),
			14 => lang('order_status_confirmed'),
			15 => lang('order_status_confirmed'),
			16 => lang('order_status_confirmed'),
			17 => lang('order_status_confirmed'),
			18 => lang('order_status_confirmed'),
			19 => lang('order_status_confirmed'),
		));
		$order['pay_status_desc'] = id2name($order['pay_status'],array(
			PS_UNPAID => lang('payment_status_unpaid'),
			PS_PAYING => lang('payment_status_paying'),
			PS_PAID => lang('payment_status_paid'),
		));
		$order['shipping_status_desc'] = id2name($order['shipping_status'],array(
			SS_UNSHIPPED => lang('shipping_status_unshipped'),
			SS_SHIPPED => lang('shipping_status_shipped'),
			SS_RECEIVED => lang('shipping_status_received'),
			SS_PREPARING => lang('shipping_status_preparing'),
			SS_SHIPPED_PART => lang('shipping_status_shipped_part'),
			SS_SHIPPED_ING => lang('shipping_status_shipped_ing'),
			OS_SHIPPED_PART => lang('shipping_status_shipped_part'),
		));
	}


	/**
	 * 获得订单商品信息
	 * @param inc $orderId 订单ID
	 * @return array 返回订单商品信息
	 */
	public function getOrderGoodsList( $orderId ){
		$this->db_ebmaster_write->from('order_goods');
		$this->db_ebmaster_write->where('order_id',$orderId);
		$query = $this->db_ebmaster_write->get();
		$list = $query->result_array();

		return $list;
	}

	/**
	 * 批量获得订单商品信息
	 * @param array $orderId 订单ID
	 * @return array 返回订单商品信息
	 * @author lucas
	 */
	public function getOrderGoodsBatchList( $orderIds ){
		$this->db_ebmaster_write->from('order_goods');
		$this->db_ebmaster_write->where_in( 'order_id', $orderIds );
		$this->db_ebmaster_write->order_by('add_time', 'desc');
		$query = $this->db_ebmaster_write->get();
		$list = $query->result_array();

		return $list;
	}

	/**
	 * 创建订单
	 * @param  array $info 订单信息
	 * @return integer 订单id
	 */
	public function createOrder($info) {
		$this->db_ebmaster_write->insert('order_info',$info);
		return $this->db_ebmaster_write->insert_id();
	}

	/**
	 * 更新订单信息
	 * @param  integer $orderId 订单id
	 * @param  array $info 订单信息
	 */
	public function updateOrder($orderId,$info) {
		$this->db_ebmaster_write->where('order_id', $orderId);
		$this->db_ebmaster_write->update('order_info', $info);
	}

	/**
	 * 创建订单动作
	 * @param  array $info 订单信息
	 */
	public function createOrderAction($info) {
		$this->db_write->insert('order_action', $info);
		return $this->db_write->insert_id();
	}

	/**
	 * 创建订单的生成历史
	 * @param  array $info 订单信息
	 */
	public function createOrderPayHistory($info) {
		$this->db_write->insert('order_pay_history', $info);
		return $this->db_write->insert_id();
	}

	/**
	 * 创建支付日志
	 * @param  array $info 订单信息
	 */
	public function createPayLog($info) {
		$this->db_write->insert('pay_log', $info);
		return $this->db_write->insert_id();
	}


	/**
	 * 记录支付错误的日志ID
	 *
	 * * @param string $pay	支付name
	 * @param int $type		支付Type
	 * 						类型
	 * 							1 :pay/result
	 * 							2. pay/notifcation_master success UN_PAYING
	 * 							3. pay/notifcation_master success 订单不存在
	 * 							4. pay/notifcation_master fail 不成功或者参数不合法
	 *
	 * @param array $info	返回数组
	 * @param int $notice	错误级别
	 *
	 *
	 * @return int $result ID
	 */
	public function createPayErrorLog( $pay = 'other' , $type = 1 , $info = array() , $notice = 255 ){
		$result = array(
			'notice' => (int)$notice ,
			'pay' => trim( $pay ),
			'type' => (int)$type,
			'payment_method' => ( isset( $info['paymentMethod'] ) && !empty( $info['paymentMethod'] ) ) ? trim( $info['paymentMethod'] ) : 'other' ,
			'order_sn' =>  ( isset( $info['merchantReference'] ) &&  !empty( $info['merchantReference'] ) ) ? (int)$info['merchantReference'] : 0,
			'info' => addslashes( json_encode( $info ) ),
			'add_time' => HelpOther::requestTime()
		);
		$this->db_ebmaster_write->insert('order_error_log', $result );
		return $this->db_ebmaster_write->insert_id();
	}

	/**
	 * 更新支付信息
	 * @param  integer $orderId 订单id
	 * @param  array $info 订单信息
	 */
	public function updatePayLog($orderId, $info) {
		$this->db_write->where('order_id', $orderId);
		$this->db_write->update('pay_log', $info);
	}

	/**
	 * 检查订单的统计信息
	 * @param  string $orderSn 订单号
	 */
	public function checkOrderSentGAInfo($orderSn) {
		$this->db_read->from('ga_order_history');
		$this->db_read->where('order_sn', $orderSn);
		$count = $this->db_read->count_all_results();
		return ($count > 0);
	}

	/**
	 * 创建订单统计信息
	 * @param  array $info 订单信息
	 */
	public function createGAOrderHistory($info) {
		$this->db_write->insert('ga_order_history', $info);
		return $this->db_write->insert_id();
	}

	/**
	 * 获取订单最后的商品列表
	 * @param  integer $orderId 订单id
	 */
	public function getOrderGoodsListLatest($orderId) {
		$this->db_ebmaster_write->from('order_goods');
		$this->db_ebmaster_write->where('order_id', $orderId);
		$query = $this->db_ebmaster_write->get();
		$list = $query->result_array();

		return $list;
	}

	/**
	 * 判断用户是否下过订单
	 * @param  integer  $userId 用户id
	 * @param  boolean $timeBefore 判断开始时间
	 */
	public function getUserHasOrder($userId, $timeBefore = false) {
		$this->db_ebmaster_write->from('order_info');
		$this->db_ebmaster_write->where('user_id',$userId);
		$this->db_ebmaster_write->where('pay_time <',$timeBefore);
		$this->db_ebmaster_write->where('pay_status',2);
		$count = $this->db_ebmaster_write->count_all_results();

		return ($count > 0);
	}

	/**
	 * 获取用户支付订单的总价
	 * @param  integer $userId 获取用
	 * @param  integer $payId 支付id
	 * @param  string $startTime 开始时间
	 */
	public function getUserPaymentOrderAmount($userId, $payId, $startTime) {
		$this->db_ebmaster_write->select('sum(base_money_paid) as base_money_paid');
		$this->db_ebmaster_write->from('order_info');
		$this->db_ebmaster_write->where('user_id',$userId);
		$this->db_ebmaster_write->where('pay_id',$payId);
		$this->db_ebmaster_write->where('pay_status',2);
		$this->db_ebmaster_write->where('add_time >=',$startTime);
		$this->db_ebmaster_write->limit(1);
		$query = $this->db_ebmaster_write->get();
		$res = $query->row_array();

		return id2name('base_money_paid',$res,0);
	}

	/**
	 * 获取用户支付订单的总数
	 * @param  integer $userId 获取用
	 * @param  integer $payId 支付id
	 * @param  string $startTime 开始时间
	 */
	public function getUserPaymentOrderCount($userId,$payId,$startTime) {
		$this->db_ebmaster_write->from('order_info');
		$this->db_ebmaster_write->where('user_id',$userId);
		$this->db_ebmaster_write->where('pay_id',$payId);
		$this->db_ebmaster_write->where('pay_status',2);
		$this->db_ebmaster_write->where('add_time >=',$startTime);
		$count = $this->db_ebmaster_write->count_all_results();

		return $count;
	}

	/**
	 * 获得订单信息
	 * @param inc $orderId
	 * @return array 返回订单信息
	 */
	public function getOrder($orderId){
		$this->db_ebmaster_write->from('order_info');
		$this->db_ebmaster_write->where('order_id',$orderId);
		$this->db_ebmaster_write->limit(1);
		$query = $this->db_ebmaster_write->get();
		$res = $query->row_array();

		return $res;
	}

	/**
	 * 获得订单信息
	 * @param string $orderSn
	 * @return array 返回订单信息
	 */
	public function getOrderBySn($orderSn){
		$this->db_ebmaster_write->from('order_info');
		$this->db_ebmaster_write->where('order_sn',$orderSn);
		$this->db_ebmaster_write->limit(1);
		$query = $this->db_ebmaster_write->get();
		$res = $query->row_array();
		return $res;
	}

	/**
	 * 添加商品信息
	 * @param  array $info 商品信息数组
	 */
	public function createOrderGoodsbatch($info) {
		if(!empty($info)){
			$this->db_ebmaster_write->insert_batch('order_goods', $info);
		}
	}

	public function createAffiliateLogBatch($info){
		if(!empty($info)){
			$this->db_write->insert_batch('affiliate_medium',$info);
		}
	}

	/**
	 * 获取订单商品的一级分类id
	 * @param  array  $data 订单商品的id
	 */
	public function getOrderCategoryIdLevel1($data = array()) {
		if(empty($data)) {
			return array();
		}

		$this->db_ebmaster_write->select('id,category_id');
		$this->db_ebmaster_write->from('product');
		$this->db_ebmaster_write->where_in('id',$data);
		$query = $this->db_ebmaster_write->get();
		$res = $query->result_array();
		$result = reindexArray( $res, 'id');

		// 循环取出分类id
		$categoryIds = array();
		if(!empty($result)) {
			foreach ($result as $key => $value) {
				$categoryIds[] = $value['category_id'];
			}
		}

		// 通过分类id去取出一级分类id
		$Categoryv2Model = new Categoryv2Model();
		$categoryInfo = $Categoryv2Model->getCateInfoById($categoryIds);
		$categroyParentsIds = array();
		if(!empty($categoryInfo)) {
			foreach ($categoryInfo as $key => $value) {
				$categroyParentsIds[$value['id']] = 0;
				if(!empty($value['path'])) {
					$pathArray = explode("/", $value['path']);
					$categroyParentsIds[$value['id']] = $pathArray[0];
				}
			}
		}

		if(!empty($result)) {
			foreach ($result as $key => $value) {
				$result[$key] = isset($categroyParentsIds[$value['category_id']]) && !empty($categroyParentsIds[$value['category_id']]) ? (int)$categroyParentsIds[$value['category_id']]: 0;
			}
		}
		return $result;
	}

	/**
	 * 获取昨天的订单统计数据。
	 * @return array
	 * @author Terry
	 */
	public function getOrderReportData(){

		$endTime = strtotime( date( "Y-m-d" , HelpOther::requestTime() ) );
		$startTime = $endTime-86400;
		$proTypeAllowed = implode(',',array_keys(AppConfig::$proTypeTextArr));
		$res = $this->db_ebmaster_read->query("SELECT a.pro_type, sum(a.goods_number) count,sum(a.final_price*a.goods_number) amount,
sum(IF( b.pay_status=2,a.final_price*a.goods_number,0)) payedAmount
FROM order_goods a left join order_info b on a.order_id=b.order_id WHERE a.add_time>=$startTime AND a.add_time<$endTime AND a.pro_type in ($proTypeAllowed) GROUP BY a.pro_type")->result_array();
		return $res;
	}

	/**
	 * 获取订单的错误日志
	 * @return array
	 * @author ningyandong
	 */
	public function getOrderErrorData(){
		$endTime = strtotime( date( "Y-m-d" , HelpOther::requestTime() ) );
		$startTime = $endTime-86400;
		//获取日志中异常的订单号
		$this->db_ebmaster_read->select('id, pay, type, payment_method, order_sn, info, add_time');
		$this->db_ebmaster_read->from( 'order_error_log' );
		$this->db_ebmaster_read->where_in('type', array( 2,3 ) );
		$this->db_ebmaster_read->where('add_time <=', $endTime );
		$this->db_ebmaster_read->where('add_time >', $startTime );
		$query = $this->db_ebmaster_read->get();
		$result = $query->result_array();
		$result = reindexArray( $result , 'order_sn' );
		
		//获取订单详情
		$this->db_ebmaster_read->select('pay_status, order_sn');
		$this->db_ebmaster_read->from( 'order_info' );
		$this->db_ebmaster_read->where_in('order_sn', array_keys( $result ) );
		$this->db_ebmaster_read->where('pay_status', PS_PAID );
		$query = $this->db_ebmaster_read->get();
		$orders = $query->result_array();

		foreach ( $orders as $v ){
			unset( $result[ $v['order_sn'] ] );
		}
		return $result;
	}

	/**
	 * 获取订单在X小时内未进行支付并且为发送催款邮件的数据
	 * @param int $hour 24|48 获得X小时内未支付的订单信息
	 * @return array
	 * @author lucas
	 */
	public function getOrderPromptPayingData( $hour = 24 ){

		$time = HelpOther::requestTime();
		//过期时间设置
		$h24  = $time - 24 * 3600; //24小时
		$h48  = $time - 48 * 3600; //48小时
		$d14  = $time - 14 * 24 * 3600; //14天

		$this->db_ebmaster_read->select('order_id, language_code, order_sn, user_id, email, consignee, goods_amount, shipping_fee, insure_fee, integral_money, discount, order_amount, base_shipping_fee, base_insure_fee, base_integral_money, base_discount, address, address2, city, province, zipcode, country, mobile, currency_code,store_to_order_rate,add_time, shipping_name, pay_name, pay_status, shipping_status, pay_id, order_status, order_from');
		$this->db_ebmaster_read->from( 'order_info' );
		$this->db_ebmaster_read->where('pay_status !=', 2);
		$this->db_ebmaster_read->where('shipping_status =', 0);
		if( $hour === 24 ){
			$this->db_ebmaster_read->where('repay48', 0);
			$this->db_ebmaster_read->where('add_time <=', $h24 );
			$this->db_ebmaster_read->where('add_time >', $h48 );
		}
		if( $hour === 48 ){
			$this->db_ebmaster_read->where('repay150', 0);
			$this->db_ebmaster_read->where('add_time <=', $h48 );
			$this->db_ebmaster_read->where('add_time >', $d14 );
		}

		$query = $this->db_ebmaster_read->get();
		$result = $query->result_array();

		return $result;
	}

	/**
	 * 系统邮件判断是否可继续支付的逻辑
	 * @param array $order 订单信息
	 * @param array
	 */
	public function checkContinuePayment( $order, $orderProductList, $languageId ){
		//订单为空的时候
		if( empty( $order ) ) {
			return false;
		}
		//检查汇率是否有变动
		$currencyInfo = $this->m_app->getConfigCurrency($order['currency_code'], 1);
		if( $currencyInfo['rate'] != $order['store_to_order_rate'] ){
			return false;
		}
		$continuePayment = true;
		if( $order['pay_status'] != PS_UNPAID || $order['shipping_status'] != SS_UNSHIPPED ){
			$continuePayment = false;
		}else{
			$pids = extractColumn( $orderProductList, 'product_id' );
			$productModel = new ProductModel();
			$productList = $productModel->getProInfoById( $pids, $languageId );
			foreach ( $orderProductList as $record ) {
				if( $productList[ $record['product_id'] ]['status'] != 1 || !isset( $productList[ $record['product_id'] ]['skuInfo'][ $record['sku'] ]['final_price'] ) || $record['final_price'] != $productList[ $record['product_id'] ]['skuInfo'][ $record['sku'] ]['final_price'] ){
					$continuePayment = false;
					break;
				}
			}
		}

		return $continuePayment;
	}
}
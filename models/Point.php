<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * Model for point.
 * @author lucas
 */
class PointModel extends CI_Model {

	/**
	 * 获取实例化
	 * @return PointModel
	 */
	public static function & getInstanceObj( ){
		return parent::_getBaseInstanceObj( __CLASS__ );
	}

	/**
	 * 初始化
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * 获得积分
	 * @param float $totalPrice 订单总金额
	 * @param int $languageId 语言ID
	 * @param string $currency 货币
	 * @param string $shipping 物流方式
	 * @param string $payment 支付方式
	 * @param string $country_shipping 物流国家
	 * @param string $country_payment 支付国家
	 * @return int $point
	 * @author lucas
	 */
	public function getPoint( $totalPrice, $languageId, $currency, $shipping = '', $payment = '', $country_shipping = '', $country_payment = '' ){
		$totalPrice = round( $totalPrice );
		$pointList = $this->getPointList();

		//默认倍数
		$amount = 1;
		//匹配积分倍数条件
		foreach ( $pointList as $key => $record ) {
			if( !in_array( $languageId, $record['language'] ) ){
				continue;
			}

			if( !in_array( $currency, $record['currency'] ) ){
				continue;
			}

			if( !empty( $shipping ) && !in_array( $shipping, $record['shipping'] ) ){
				continue;
			}

			if( !empty( $payment ) && !in_array( $payment, $record['payment'] ) ){
				continue;
			}

			//物流国家 空为全部国家
			if( !empty( $country_shipping ) && !empty( $record['country_shipping'] ) && !in_array( $country_shipping, $record['country_shipping'] ) ){
				continue;
			}

			//支付国家 空为全部国家
			if( !empty( $country_payment ) && !empty( $record['country_payment'] ) && !in_array( $country_payment, $record['country_payment'] ) ){
				continue;
			}

			$amount = $record['amount'];
			break;
		}

		$point = $totalPrice * $amount;
		return ceil( $point );
	}

	/**
	 * 获得购物车积分
	 * @param float $totalPrice 订单总金额
	 * @param int $languageId 语言ID
	 * @param string $currency 货币
	 * @return int $point
	 * @author lucas
	 */
	public function getCartPoint( $totalPrice, $languageId, $currency ){
		$totalPrice = round( $totalPrice );
		$pointList = $this->getPointList();

		//默认倍数
		$amount = 1;
		//匹配积分倍数条件
		foreach ( $pointList as $key => $record ) {
			if( !in_array( $languageId, $record['language'] ) ){
				continue;
			}

			if( !in_array( $currency, $record['currency'] ) ){
				continue;
			}

			if( count( $record['shipping'] ) < 6 ){
				continue;
			}

			if( count( $record['payment'] ) < 22 ){
				continue;
			}

			//物流国家 空为全部国家
			if( !empty( $record['country_shipping'] ) ){
				continue;
			}

			//支付国家 空为全部国家
			if( !empty( $record['country_payment'] ) ){
				continue;
			}

			$amount = $record['amount'];
			break;
		}

		$point = $totalPrice * $amount;
		return ceil( $point );
	}

	/**
	 * 获得多倍积分信息列表
	 * @return array $pointList
	 * @author lucas
	 */
	private function getPointList(){
		$cacheKey = "get_point_list";
		$pointList = $this->memcache->get( $cacheKey );

		if ( $pointList === false ) {
			$result = $this->db_ebmaster_read
			->select('name, amount, language, currency, shipping, payment, country_shipping, country_payment, start_time, end_time')
			->from('eb_promote_point')
			->where('status' , 1 )
			->where('start_time <=' , date( 'Y-m-d H:i:s', HelpOther::requestTime()+36000 ) )
			->where('end_time >' , date( 'Y-m-d H:i:s', HelpOther::requestTime() ) )
			->order_by('amount', 'desc')
			->get()->result_array();

			//处理数据 转换为数组
			$pointList = array();
			foreach( $result as $key => $record ){
				$record['language'] = explode('/', trim( $record['language'], '/' ) );
				$record['currency'] = explode(',', trim( $record['currency'], ',' ) );
				$record['shipping'] = explode('/', trim( $record['shipping'], '/' ) );
				$record['payment'] = explode('/', trim( $record['payment'], '/' ) );
				$record['country_shipping'] = empty( $record['country_shipping'] ) ? array() : explode(',', trim( $record['country_shipping'], ',' ) );
				$record['country_payment'] = empty( $record['country_payment'] ) ? array() : explode(',', trim( $record['country_payment'], ',' ) );
				$record['start_time'] = $record['start_time'];
				$record['end_time'] = $record['end_time'];

				$pointList[ $key ] = $record;
			}

			$this->memcache->set( $cacheKey, $pointList );
		}

		$result = array();
		foreach( $pointList as $record ){
			if( $record['start_time'] <= date( 'Y-m-d H:i:s', HelpOther::requestTime() ) && $record['end_time'] > date( 'Y-m-d H:i:s', HelpOther::requestTime() ) ){
				$result[] = $record;
			}
		}

		return $result;
	}
}
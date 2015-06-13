<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * 系统邮件触发类
 * @author lucas
 */
class TriggersystememailModel extends CI_Model{

	/**
	 * 触发类型
	 */
	const TRIGGER_TYPE_3 = 3; //催款(一)
	const TRIGGER_TYPE_4 = 4; //催款(二)
	const TRIGGER_TYPE_9 = 9; //重下单
	const TIME24TO48 = 24; //24小时-48小时
	const TIME48TO14DAY = 48; //48小时-14天

	/**
	 * 初始化
	 */
	public function __construct() {
		set_time_limit( 0 );
		$this->orderObj = new OrderModel();
		$this->emailtemplateObj = new EmailtemplateModel();
		$this->load->library(array('log'));

		parent::__construct();
	}

	/**
	 * 催款(一)
	 * 订单在24-48小时内未进行支付时
	 */
	public function promptPaying24(){
		//获得24-48小时内的订单
		$promptPayingInfo = $this->orderObj->getOrderPromptPayingData( self::TIME24TO48 );

		if( count( $promptPayingInfo ) > 0 ){
			//获得邮件模板信息
			$templateInfo = $this->emailtemplateObj->getSystemEmailTemplateInfo( self::TRIGGER_TYPE_3 );
			//商品类实例
			$ProductModel = new ProductModel();
			//获得订单商品
			$orderIds = extractColumn( $promptPayingInfo, 'order_id' );
			$orderGoodsList = $this->orderObj->getOrderGoodsBatchList( $orderIds );
			$orderGoodsList = spreadArray( $orderGoodsList, 'order_id' );

			//获得订单用户购物车商品
			$userIds = extractColumn( $promptPayingInfo, 'user_id' );
			$usersCartProductIds = $this->emailtemplateObj->getCartByUser( $userIds );

			//发送催款邮件
			foreach ( $promptPayingInfo as $order ) {
				$addTime = date('F j, Y h:i:s A e', $order['add_time']);
				$order = eb_htmlspecialchars( $order );
				$languageId = $this->m_app->getLanguageCodeByCode( $order['language_code'] );
				//模板启用
				if( isset( $templateInfo[ $languageId ]['status'] ) && $templateInfo[ $languageId ]['status'] == 1 && isset( $templateInfo[ $languageId ]['eid'] ) && !empty( $templateInfo[ $languageId ]['eid'] ) ){
					//检查是否继续支付
					$checkRepay = $this->orderObj->checkContinuePayment( $order, $orderGoodsList[ $order['order_id'] ], $languageId );
					$source = empty( $checkRepay ) ? 'paying24reorder' : 'paying24checkout';
					//订单商品信息dom
					$orderInfoDom = $this->emailtemplateObj->getEmailReminderOrderInfoDom( $orderGoodsList[ $order['order_id'] ], $languageId, $order['language_code'], $order['order_from'], $source );
					//购物车商品
					$usersCartProId = isset( $usersCartProductIds[ $order['user_id'] ] ) ? $usersCartProductIds[ $order['user_id'] ] : array();
					//订单商品pid
					$productIds = extractColumn( $orderGoodsList[ $order['order_id'] ], 'product_id' );
					try{
						// $recommendProList = $ProductModel->getEmailRecommendProduct( $languageId, $productIds, TRUE, $usersCartProId, $order['user_id'], $order['currency_code'], $order['language_code'], $order['order_from'], $source );
						$recommendProList = $ProductModel->getEmailRecommendProductNew( $languageId, $order['currency_code'], $order['language_code'], $order['order_from'], $source );
						$recommendProDom = $this->emailtemplateObj->getEmailRecommendProductDom( $recommendProList );
					}catch(Exception $e) {}

					//邮件模版参数
					$www = ( $order['language_code'] == 'us' ) ? 'www' : $order['language_code'];
					$commonDomain = ( $order['order_from'] == '2' ) ? 'eachbuyer.net' : COMMON_DOMAIN;
					$contentParam = array(
						'SITE_DOMAIN' => 'http://'.$www.'.'.$commonDomain, //域名链接
						'SITE_DOMAIN1' => $commonDomain, //域名
						'CS_EMAIL' => 'cs@'.$commonDomain,
						'USER_NAME' => $order['consignee'],
						'ORDER_NUM' => $order['order_sn'],
						'ORDER_TIME' => $addTime,
						'ORDER_INFO2' => $orderInfoDom,
						'ITEM_REO' => $recommendProDom,
						'ORDER_ID' => $order['order_id'],
					);

					//判断是否可支付
					if( $checkRepay ){
						//发送 $order['email'] luowenyong@hofan.cn
						$result = HelpOther::sendSystemEmail( $order['email'], $templateInfo[ $languageId ]['eid'], $contentParam );
						//发送失败重试一次
						if( trim( $result ) !== 'OK' ){
							$result = HelpOther::sendSystemEmail( $order['email'], $templateInfo[ $languageId ]['eid'], $contentParam );
						}
						if( trim( $result ) !== 'OK' ){
							$logInfo = '[prompt Paying 24] ORDERID:#'.$order['order_sn'].' - EMAIL:'.$order['email'].' - EID:'.$templateInfo[ $languageId ]['eid'].' - ERROR:'.$result;
							$this->log->write( Log::LOG_TYPE_SYSTEM_EMAIL , $logInfo, true );
						}
						//发送成功修改状态
						if( trim( $result ) === 'OK' ){
							$info = array( 'repay48' => 1 );
							$this->orderObj->updateOrder( $order['order_id'], $info );
						}
					}else{
						//获得邮件模板信息
						$templateInfo = $this->emailtemplateObj->getSystemEmailTemplateInfo( self::TRIGGER_TYPE_9 );
						//模板启用
						if( isset( $templateInfo[ $languageId ]['status'] ) && $templateInfo[ $languageId ]['status'] == 1 && isset( $templateInfo[ $languageId ]['eid'] ) && !empty( $templateInfo[ $languageId ]['eid'] ) ){
							//发送 $order['email'] luowenyong@hofan.cn
							$result = HelpOther::sendSystemEmail( $order['email'], $templateInfo[ $languageId ]['eid'], $contentParam );
							//发送失败重试一次
							if( trim( $result ) !== 'OK' ){
								$result = HelpOther::sendSystemEmail( $order['email'], $templateInfo[ $languageId ]['eid'], $contentParam );
							}
							if( trim( $result ) !== 'OK' ){
								$logInfo = '[prompt Paying 24] ORDERID:#'.$order['order_sn'].' - EMAIL:'.$order['email'].' - EID:'.$templateInfo[ $languageId ]['eid'].' - ERROR:'.$result;
								$this->log->write( Log::LOG_TYPE_SYSTEM_EMAIL , $logInfo, true );
							}
							//发送成功修改状态
							if( trim( $result ) === 'OK' ){
								$info = array( 'repay48' => 1 );
								$this->orderObj->updateOrder( $order['order_id'], $info );
							}
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * 催款（二）
	 * 订单在48小时-14天内未进行支付时
	 */
	public function promptPaying48(){
		//获得48-14天内的订单
		$promptPayingInfo = $this->orderObj->getOrderPromptPayingData( self::TIME48TO14DAY );
		if( count( $promptPayingInfo ) > 0 ){
			//获得邮件模板信息
			$templateInfo = $this->emailtemplateObj->getSystemEmailTemplateInfo( self::TRIGGER_TYPE_4 );
			//商品类实例
			$ProductModel = new ProductModel();
			//获得订单商品
			$orderIds = extractColumn( $promptPayingInfo, 'order_id' );
			$orderGoodsList = $this->orderObj->getOrderGoodsBatchList( $orderIds );
			$orderGoodsList = spreadArray( $orderGoodsList, 'order_id' );

			//获得订单用户购物车商品
			$userIds = extractColumn( $promptPayingInfo, 'user_id' );
			$usersCartProductIds = $this->emailtemplateObj->getCartByUser( $userIds );

			//发送催款邮件
			foreach ( $promptPayingInfo as $order ) {
				$addTime = date('F j, Y h:i:s A e', $order['add_time']);
				$order = eb_htmlspecialchars( $order );
				$languageId = $this->m_app->getLanguageCodeByCode( $order['language_code'] );
				//检查是否继续支付
				$checkRepay = $this->orderObj->checkContinuePayment( $order, $orderGoodsList[ $order['order_id'] ], $languageId );
				//判断是否可支付
				if( $checkRepay ){
					//模板启用
					if( isset( $templateInfo[ $languageId ]['status'] ) && $templateInfo[ $languageId ]['status'] == 1 && isset( $templateInfo[ $languageId ]['eid'] ) && !empty( $templateInfo[ $languageId ]['eid'] ) ){
						//订单商品信息dom
						$orderInfoDom = $this->emailtemplateObj->getEmailReminderOrderInfoDom( $orderGoodsList[ $order['order_id'] ], $languageId, $order['language_code'], $order['order_from'], 'paying48' );
						//购物车商品
						$usersCartProId = isset( $usersCartProductIds[ $order['user_id'] ] ) ? $usersCartProductIds[ $order['user_id'] ] : array();
						//订单商品pid
						$productIds = extractColumn( $orderGoodsList[ $order['order_id'] ], 'product_id' );

 						try{
 							// $recommendProList = $ProductModel->getEmailRecommendProduct( $languageId, $productIds, TRUE, $usersCartProId, $order['user_id'], $order['currency_code'], $order['language_code'], $order['order_from'], 'paying48' );
 							$recommendProList = $ProductModel->getEmailRecommendProductNew( $languageId, $order['currency_code'], $order['language_code'], $order['order_from'], 'paying48' );
 							$recommendProDom = $this->emailtemplateObj->getEmailRecommendProductDom( $recommendProList );
 						}catch(Exception $e) {}

						//邮件模版参数
						$www = ( $order['language_code'] == 'us' ) ? 'www' : $order['language_code'];
						$commonDomain = ( $order['order_from'] == '2' ) ? 'eachbuyer.net' : COMMON_DOMAIN;
						$contentParam = array(
							'SITE_DOMAIN' => 'http://'.$www.'.'.$commonDomain, //域名链接
							'SITE_DOMAIN1' => $commonDomain, //域名
							'CS_EMAIL' => 'cs@'.$commonDomain,
							'USER_NAME' => $order['consignee'],
							'ORDER_NUM' => $order['order_sn'],
							'ORDER_TIME' => $addTime,
							'ORDER_INFO2' => $orderInfoDom,
							'ITEM_REO' => $recommendProDom,
							'ORDER_ID' => $order['order_id'],
						);

						//发送 $order['email'] luowenyong@hofan.cn
						$result = HelpOther::sendSystemEmail( $order['email'], $templateInfo[ $languageId ]['eid'], $contentParam );
						//发送失败重试一次
						if( trim( $result ) !== 'OK' ){
							$result = HelpOther::sendSystemEmail( $order['email'], $templateInfo[ $languageId ]['eid'], $contentParam );
						}
						if( trim( $result ) !== 'OK' ){
							$logInfo = '[prompt Paying 48] ORDERID:#'.$order['order_sn'].' - EMAIL:'.$order['email'].' - EID:'.$templateInfo[ $languageId ]['eid'].' - ERROR:'.$result;
							$this->log->write( Log::LOG_TYPE_SYSTEM_EMAIL , $logInfo, true );
						}
						//发送成功修改状态
						if( trim( $result ) === 'OK' ){
							$info = array( 'repay150' => 1 );
							$this->orderObj->updateOrder( $order['order_id'], $info );
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * 脚本运行结束给内部人员发通知邮件
	 */
	public function sendEmailToOurs() {
		$date = date( 'Y-m-d', $_SERVER['REQUEST_TIME'] );
		$fire = dirname(__FILE__)."/../../log/system_email/system_email-{$date}.log";
		$fileContents = '';
		if( file_exists( $fire ) ){
			$fileContents = @file_get_contents( $fire, NULL, NULL, 80 );
			$fileContents = preg_replace("/\n/", "<br>", $fileContents );
		}

		send_mail('luowenyong@hofan.cn', 'repay email send', "repay email send success!<br /> send Failed log: {$fileContents}", 1, false, true );
		send_mail('ningyandong@hofan.cn', 'repay email send', "repay email send success!<br /> send Failed log: {$fileContents}", 1, false, true );
		send_mail('chentaolian@hofan.cn', 'repay email send', "repay email send success!<br /> send Failed log: {$fileContents}", 1, false, true );
		send_mail('qinkun@hofan.cn', 'repay email send', "repay email send success!<br /> send Failed log: {$fileContents}", 1, false, true );
		send_mail('luojie@hofan.cn', 'repay email send', "repay email send success!<br /> send Failed log: {$fileContents}", 1, false, true );
		send_mail('lihuojian@hofan.cn', 'repay email send', "repay email send success!<br /> send Failed log: {$fileContents}", 1, false, true );


		return true;
	}

	/**
	 * 析构方法
	 */
	public function __destruct(){

	}

}
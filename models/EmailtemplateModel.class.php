<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * 系统邮件类
 * @author lucas
 */
class EmailtemplateModel extends CI_Model {

	//表名 eb_pc_site:system_email
	private $_tableName_email_template = 'email_template';

	/*
	 * 初始化
	 */
	public function __construct(){
		parent::__construct();
	}

	/**
	 * 获得系统邮件EID和状态信息
	 * @param integet $type 类别ID
	 * @param integet $languageId 语言ID
	 * @return Array 返回 分类广告位数据
	 * @author lucas
	 */
	public function getSystemEmailTemplateInfo( $type, $languageId = '' ){
		$result = array();
		if( $type > 0 ){
			$cacheKey = 'get_email_template_%s';
			$cacheParams = array( $type );
			$result = $this->memcache->get( $cacheKey, $cacheParams );

			if( $result === FALSE ){
				//get DB
				$this->db_ebmaster_read->select('language_id, eid, status');
				$this->db_ebmaster_read->from( $this->_tableName_email_template );
				$this->db_ebmaster_read->where('type', $type );
				$query = $this->db_ebmaster_read->get();
				$result = $query->result_array();
				$result = reindexArray( $result, 'language_id' );

				//set mc
				$this->memcache->set( $cacheKey, $result, $cacheParams );
			}
		}

		if( $languageId && isset( $result[ $languageId ] ) ){
			$result = $result[ $languageId ];
		}
		return $result;
	}

	/**
	 * 获得系统邮件模板订单商品信息dom
	 * @param array $order 订单信息
	 * @param array $orderGoodsList 订单商品列表
	 * @return array
	 * @author lucas
	 */
	public function getEmailOrderInfoDom( $order, $orderGoodsList, $source = '' ){
		$emailTemplate = array();
		//订单信息
		$order_info = '';
		$ProductModel = new ProductModel();
		foreach ( $orderGoodsList as $key => $record ) {
			//获得商品链接
			$languageId = $this->m_app->getLanguageCodeByCode( $order['language_code'] );
			$productInfo = $ProductModel->getProInfoById( $record['product_id'], $languageId );
			$sourceUrl = '';
			if( $source === 'order' ){
				$sourceUrl = '?utm_source=System_Own&utm_medium=Email&utm_campaign=order_new&utm_nooverride=1';
			}elseif( $source === 'payment' ){
				$sourceUrl = '?utm_source=System_Own&utm_medium=Email&utm_campaign=got-payment&utm_nooverride=1';
			}
			$productUrl = eb_gen_url($productInfo[ $record['product_id'] ]['url']).$sourceUrl;

			//判断催款邮件不显示价格
			$goodsPrice = isset( $record['goods_price'] ) ? $record['goods_price'] : 0;

			$order_info .= sprintf( '<tr>
				<td bgcolor="#F4F4F4" colspan="2">
				<table width="700" cellspacing="1" cellpadding="2" border="0" align="center">
					<tr>
						<td width="90" height="30" align="center" bgcolor="#FFFFFF"  style="font-family: Arial, sans-serif; font-size: 12px; color: #000000;">%u</td>
						<td width="90" align="center" bgcolor="#FFFFFF"><span style="font-family: Arial, sans-serif; font-size: 12px; color: #000000;">%s</span></td>
						<td width="354" bgcolor="#FFFFFF" style="font-family: Arial, sans-serif; font-size: 12px; color: #000000;"><a href="%s" style="font-family: Arial, sans-serif; font-size: 12px; color: #2d72cc;">%s</a></td>
						<td width="40" align="center" bgcolor="#FFFFFF"  style="font-family: Arial, sans-serif; font-size: 12px; color: #000000;">%u</td>
						<td width="100" align="center" bgcolor="#FFFFFF"  style="font-family: Arial, sans-serif; font-size: 12px; color: #000000;">%s</td>
					</tr>
				</table>
				</td>
			</tr>', $record['product_id'], $record['sku'], $productUrl, $record['goods_name'], $record['goods_number'], $goodsPrice );
		}
		$this->load->language('order_detail', $order['language_code']);
		$this->load->language('cart', $order['language_code']);
		$order_info .= '<tr>
							<td valign="top" bgcolor="#F4F4F4" align="center" colspan="2">
								<table width="700" cellspacing="0" cellpadding="0" border="0">
									<tr>
										<td bgcolor="#FFFFFF" align="right">
											<table cellspacing="6" cellpadding="0" border="0">
												<tr>
													<td align="right" style="font-family: Arial, sans-serif; font-size: 14px; color: #000000;"><span style="font-family: Arial, sans-serif; font-size: 15px; color: #000000;">'. lang('order_detail_subtotal') . ':' . $order['goods_amount'] .'</span></td>
												</tr>';
												if( $order['base_shipping_fee'] > 0 ){
		$order_info .= 							'<tr>
													<td align="right" style="font-family: Arial, sans-serif; font-size: 14px; color: #000000;"><span style="font-family: Arial, sans-serif; font-size: 15px; color: #000000;">'. lang('order_detail_shipping_handling') . ':' . $order['shipping_fee'] .'</span></td>
												</tr>';
												}
												if( $order['base_insure_fee'] > 0 ){
		$order_info .= 							'<tr>
													<td align="right" style="font-family: Arial, sans-serif; font-size: 14px; color: #000000;"><span style="font-family: Arial, sans-serif; font-size: 15px; color: #000000;">'. lang('order_detail_shipping_insurance') . ':' . $order['insure_fee'] .'</span></td>
												</tr>';
												}
												if($order['base_integral_money'] > 0){
		$order_info .= 							'<tr>
													<td align="right" style="font-family: Arial, sans-serif; font-size: 14px; color: #000000;"><span style="font-family: Arial, sans-serif; font-size: 15px; color: #000000;">'. lang('shopping_points') .': -'. $order['integral_money'] .'</span></td>
												</tr>';
												}
												if($order['base_discount'] > 0){
		$order_info .= 							'<tr>
													<td align="right" style="font-family: Arial, sans-serif; font-size: 14px; color: #000000;"><span style="font-family: Arial, sans-serif; font-size: 15px; color: #000000;">'. lang('order_detail_discount') .': -'. $order['discount'] .'</span></td>
												</tr>';
												}
		$order_info .= 							'<tr>
													<td height="1" align="right" bgcolor="#000000"></td>
												</tr><tr>
													<td align="right" style="font-family: Arial, sans-serif; font-size: 18px; color: #000000;">'. lang('order_detail_total') .' :<span style="font-family: Arial, sans-serif; font-size: 24px; color: #FF0000;">'. $order['order_amount'] .'</span></td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>';
		$emailTemplate['order_info'] = $order_info;

		$address = $order['address'].'<br />';
		if($order['address2']){
			$address .= $order['address2'].'<br />';
		}
		$address .= $order['city'].', '.$order['province'].', '.$order['zipcode'].'<br />';
		$address .= $order['country'].'<br />';
		$address .= 'T: '. $order['mobile'] .'<br />';
		$emailTemplate['address'] = $address;

		return $emailTemplate;
	}

	/**
	 * 获得系统邮件模板催款订单商品信息dom
	 * @param array $orderGoodsList 订单商品列表
	 * @param int $languageID 订单语言ID
	 * @param string $languageCode 订单语言code
	 * @param string $orderFrom 订单来源
	 * @return array
	 * @author lucas
	 */
	public function getEmailReminderOrderInfoDom( $orderGoodsList, $languageId, $languageCode, $orderFrom, $source = '' ){
		//订单信息
		$order_info = '';
		$ProductModel = new ProductModel();
		foreach ( $orderGoodsList as $key => $record ) {
			//获得商品链接
			$productInfo = $ProductModel->getProInfoById( $record['product_id'], $languageId );
			$productUrl = eb_gen_url($productInfo[ $record['product_id'] ]['url'], false, array(), '?', $languageCode );
			if( $orderFrom == 2 ){
				$productUrl = str_replace( ".com", ".net", $productUrl );
			}

			$sourceUrl = '';
			if( $source === 'paying24checkout' ){
				$sourceUrl = '?utm_source=System_Own&utm_medium=Email&utm_campaign=2-day-reminder-checkout&utm_nooverride=1';
			}elseif( $source === 'paying24reorder' ){
				$sourceUrl = '?utm_source=System_Own&utm_medium=Email&utm_campaign=2-day-reminder-reorder&utm_nooverride=1';
			}elseif( $source === 'paying48' ){
				$sourceUrl = '?utm_source=System_Own&utm_medium=Email&utm_campaign=7-day-reminder&utm_nooverride=1';
			}
			$productUrl = $productUrl.$sourceUrl;

			$order_info .= sprintf('<tr>
				<td colspan="2" bgcolor="#F4F4F4"><table width="700" border="0" align="center" cellpadding="2" cellspacing="1">
				<tr>
					<td width="90" height="30" align="center" bgcolor="#FFFFFF"  style="font-family: Arial, sans-serif; font-size: 12px; color: #000000;">%u</td>
					<td width="90" align="center" bgcolor="#FFFFFF"><span style="font-family: Arial, sans-serif; font-size: 12px; color: #000000;">%s</span></td>
					<td width="458" bgcolor="#FFFFFF" style="font-family: Arial, sans-serif; font-size: 12px; color: #000000;"><a href="%s" style="font-family: Arial, sans-serif; font-size: 12px; color: #2d72cc;">%s</a></td>
					<td width="40" align="center" bgcolor="#FFFFFF"  style="font-family: Arial, sans-serif; font-size: 12px; color: #000000;">%u</td>
				</tr>
				</table></td>
			</tr>', $record['product_id'], $record['sku'], $productUrl, $record['goods_name'], $record['goods_number']);
		}

		return $order_info;
	}

	/**
	 * 获得发送系统邮件推荐商品DOM
	 * @param string $languageId 语言
	 * @return string recommendProductDOM
	 * @author lucas
	 */
	public function getEmailRecommendProductDom( $recommendProList ){
		$recommendProDom = '<table width="720" cellspacing="2" cellpadding="0" border="0"><tr>';
		if( count( $recommendProList ) > 0 ){
			foreach ( $recommendProList as $key => $proRecord ) {
				$recommendProDom .= '<td valign="top" bgcolor="#FFFFFF"><table width="178" border="0" cellpadding="0" cellspacing="0">
										<tr>
											<td><a href="'. $proRecord['url'] .'"><img src="'. $proRecord['goods_img'] .'" alt="'. $proRecord['name'] .'" width="176" height="176" /></a></td>
										</tr>
										<tr>
											<td><table width="178" border="0">
											<tr>
												<td><span style="font-family: Arial, sans-serif; font-size: 12px; color: #000000;display: block;height: 42px;line-height: 14px;overflow: hidden;">'. $proRecord['name'] .'</span></td>
											</tr>
											<tr>
												<td style="font-family: Arial, sans-serif; font-size: 14px; color: #000000;"><span style="font-family: Arial, sans-serif; font-size: 12px; color: #000000; text-decoration:line-through">'. $proRecord['formatPrice'] .'</span></td>
											</tr>
											<tr>
												<td><span style="font-family: Arial, sans-serif; font-size: 14px; color: #ff0000;">'. $proRecord['formatShopPrice'] .'</span></td>
											</tr>';
											if( $proRecord['discount'] > 0 ){
				$recommendProDom .= 		'<tr>
												<td><table width="100" border="0" cellpadding="5" cellspacing="0">
												<tr>
													<td height="20" align="center" bgcolor="#f99501"><span style="font-family: Arial, sans-serif; font-size: 16px; color: #FFFFFF;">'. $proRecord['discount'] .'% OFF</span></td>
												</tr>
												</table></td>
											</tr>';
											}
				$recommendProDom .=		'</table></td>
										</tr>
									</table></td>';
			}
		}
		$recommendProDom .= '</tr></table>';

		return $recommendProDom;
	}

	/**
	 * 根据用户id批量获取购物车商品ID
	 * @param  string $userIds 用户id数组
	 * @author lucas
	 * @return array
	 */
	public function getCartByUser( $userIds ) {
		if( is_array( $userIds ) ){
			$userIds = implode(',', $userIds);
		}

		$proIds = array();
		if( count( $userIds ) > 0 ) {
			//由于主从库有延迟 因此这里 读 主库
			$this->db_ebmaster_read->select('user_id, product_id');
			$this->db_ebmaster_read->from('cart');
			$this->db_ebmaster_read->where_in('user_id', $userIds);
			$this->db_ebmaster_read->order_by('create_time','desc');
			$result = $this->db_ebmaster_read->get()->result_array();

			foreach ( $result as $record ) {
				$proIds[ $record['user_id'] ][] = $record['product_id'];
			}
		}

		return $proIds;
	}

}
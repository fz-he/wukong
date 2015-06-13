<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * 数据迁移脚本类
 * @author lucas
 */
class CrontabModel extends CI_Model{

	const DB_NAME = 'eachbuyer'; //迁移库
	const DB_NAME_NEW = 'eb_pc_site'; //迁移目标库

	/**
	 * 初始化
	 */
	public function __construct() {
		set_time_limit( 0 );
	}

	/**
	 * 迁移收藏商品数据
	 * 用户商品收藏表 collect_products
	 */
	public function collectProducts(){
		$page = 0;
		$offset = 100;
		do {
			$rows = $page*$offset;
			$sql = "SELECT * FROM `collect_goods` LIMIT ". $rows .", ". $offset;
			$this->db_read->save_queries = FALSE;
			$query = $this->db_read->query( $sql );
			$data = $query->result_array();

			//插入数据
			foreach( $data as $record ){
				$sql_add = "INSERT INTO `collect_products` ( `id`, `user_id` ,`product_id`, `description`, `add_time` ) VALUES ( '". $record['rec_id'] ."','". $record['user_id'] ."', '". $record['goods_id'] ."', '". addslashes( $record['description'] ) ."', '". $record['add_time'] ."' )";
				$this->db_ebmaster_write->save_queries = FALSE;
				$query = $this->db_ebmaster_write->query( $sql_add );
				if( empty( $query )){
					echo "fail_rec_id=".$record['rec_id']."\n";
					continue;
				}
			}

			$count = count( $data );
			unset( $data );
			$page++;

		}while( $count == $offset );

		return TRUE;
	}

	/**
	 * 迁移商品评论数据
	 * 商品评论表 comment
	 */
	public function comment( $commentId ){
		$language = array(
			'us' => 1,
			'de' => 2,
			'es' => 3,
			'it' => 4,
			'fr' => 5,
			'br' => 6,
			'ru' => 7,
		);
		$page = 0;
		$offset = 100;
		$where = empty( $commentId ) ? '' :'WHERE `comment_id` > '.$commentId;
		do {
			$rows = $page*$offset;
			$sql = "SELECT * FROM `comment` ". $where ." LIMIT ". $rows .", ". $offset;
			$this->db_read->save_queries = FALSE;
			$query = $this->db_read->query( $sql );
			$data = $query->result_array();

			//插入数据
			foreach( $data as $record ){
				$languageId = isset( $language[ $record['language'] ] ) ? isset( $language[ $record['language'] ] ) : 1;
				$sql_add = " INSERT INTO `comment` (
							`id` ,
							`product_id` ,
							`user_id` ,
							`user_name` ,
							`language_id` ,
							`title` ,
							`content` ,
							`sort_order` ,
							`support_count` ,
							`unsupport_count` ,
							`add_time` ,
							`ip_address` ,
							`status`
							)
							VALUES (
							'". $record['comment_id'] ."',
							'". $record['id_value'] ."',
							'". $record['user_id'] ."',
							'". addslashes( $record['user_name'] ) ."',
							'". $languageId ."',
							'". addslashes( $record['title'] ) ."',
							'". addslashes( $record['content'] ) ."',
							'". $record['sort_order'] ."',
							'". $record['support_count'] ."',
							'". $record['unsupport_count'] ."',
							'". $record['add_time'] ."',
							'". $record['ip_address'] ."',
							'". $record['status'] ."'
							)";
				$this->db_ebmaster_write->save_queries = FALSE;
				$query = $this->db_ebmaster_write->query( $sql_add );
				$commentId = $record['comment_id'];

				if( empty( $query )){
					echo "fail_comment_id=".$commentId."\n";
					continue;
				}
			}

			$count = count( $data );
			unset( $data );
			$page++;

		}while( $count == $offset );
		echo "last_comment_id=".$commentId."\n";

		return TRUE;
	}

	/**
	 * 迁移订单数据
	 * order_info "订单详情表"
	 */
	public function order( $orderId, $type ){
		$page = 0;
		$offset = 100;

		//迁移order_info
		$where = empty( $orderId ) ? '' :'WHERE `order_id` > '.$orderId;
		do {
			$rows = $page*$offset;
			$sql = "SELECT * FROM `order_info` " .$where ." LIMIT ". $rows .", ". $offset;
			$this->db_read->save_queries = FALSE;
			if( $type == 1 ){
				$delSql = "DELETE FROM `order_info` WHERE `order_id` = ".$orderId;
				$this->db_ebmaster_write->save_queries = FALSE;
				$query = $this->db_ebmaster_write->query( $delSql );
				$sql = "SELECT * FROM `order_info` WHERE `order_id`= ".$orderId;
			}
			$query = $this->db_read->query( $sql );
			$data = $query->result_array();

			//插入数据
			foreach( $data as $record ){
				$sql_add = " INSERT INTO `order_info` (
							`order_id`,
							`language_code`,
							`order_sn`,
							`user_id`,
							`order_status`,
							`shipping_status`,
							`pay_status`,
							`address_id`,
							`consignee`,
							`country`,
							`province`,
							`city`,
							`company`,
							`address`,
							`address2`,
							`zipcode`,
							`tel`,
							`mobile`,
							`cpf_cnpj`,
							`email`,
							`shipping_id`,
							`shipping_name`,
							`pay_id`,
							`pay_name`,
							`pay_country`,
							`how_oos`,
							`goods_amount`,
							`shipping_fee`,
							`insure_fee`,
							`money_paid`,
							`integral`,
							`integral_money`,
							`order_amount`,
							`from_ad`,
							`referer`,
							`add_time`,
							`confirm_time`,
							`pay_time`,
							`shipping_time`,
							`discount`,
							`discount_rule_ids`,
							`coupon_code`,
							`base_goods_amount`,
							`base_shipping_fee`,
							`base_insure_fee`,
							`base_money_paid`,
							`base_order_amount`,
							`base_discount`,
							`base_integral_money`,
							`base_currency_code`,
							`currency_code`,
							`store_to_order_rate`,
							`remark`,
							`send_nopay_email`,
							`send_review_email`,
							`user_type`,
							`ga_cid`,
							`repay48`,
							`repay150`,
							`separate_package`,
							`order_from`,
							`pay_note`
							)
							VALUES (
							'". $record['order_id'] ."',
							'". $record['language_code'] ."',
							'". $record['order_sn'] ."',
							'". $record['user_id'] ."',
							'". $record['order_status'] ."',
							'". $record['shipping_status'] ."',
							'". $record['pay_status'] ."',
							'". $record['address_id'] ."',
							'". addslashes( $record['consignee'] ) ."',
							'". addslashes( $record['country'] ) ."',
							'". addslashes( $record['province'] ) ."',
							'". addslashes( $record['city'] ) ."',
							'". addslashes( $record['company'] ) ."',
							'". addslashes( $record['address'] ) ."',
							'". addslashes( $record['address2'] ) ."',
							'". addslashes( $record['zipcode'] ) ."',
							'". addslashes( $record['tel'] ) ."',
							'". addslashes( $record['mobile'] ) ."',
							'". addslashes( $record['cpf_cnpj'] ) ."',
							'". addslashes( $record['email'] ) ."',
							'". $record['shipping_id'] ."',
							'". addslashes( $record['shipping_name'] ) ."',
							'". $record['pay_id'] ."',
							'". addslashes( $record['pay_name'] ) ."',
							'". addslashes( $record['pay_country'] ) ."',
							'". addslashes( $record['how_oos'] ) ."',
							'". $record['goods_amount'] ."',
							'". $record['shipping_fee'] ."',
							'". $record['insure_fee'] ."',
							'". $record['money_paid'] ."',
							'". $record['integral'] ."',
							'". $record['integral_money'] ."',
							'". $record['order_amount'] ."',
							'". $record['from_ad'] ."',
							'". $record['referer'] ."',
							'". $record['add_time'] ."',
							'". $record['confirm_time'] ."',
							'". $record['pay_time'] ."',
							'". $record['shipping_time'] ."',
							'". $record['discount'] ."',
							'". $record['discount_rule_ids'] ."',
							'". $record['coupon_code'] ."',
							'". $record['base_goods_amount'] ."',
							'". $record['base_shipping_fee'] ."',
							'". $record['base_insure_fee'] ."',
							'". $record['base_money_paid'] ."',
							'". $record['base_order_amount'] ."',
							'". $record['base_discount'] ."',
							'". $record['base_integral_money'] ."',
							'". $record['base_currency_code'] ."',
							'". $record['currency_code'] ."',
							'". $record['store_to_order_rate'] ."',
							'". $record['remark'] ."',
							'". $record['send_nopay_email'] ."',
							'". $record['send_review_email'] ."',
							'". addslashes( $record['user_type'] ) ."',
							'". $record['ga_cid'] ."',
							'". $record['repay48'] ."',
							'". $record['repay150'] ."',
							'". $record['separate_package'] ."',
							'". $record['order_from'] ."',
							'". addslashes( $record['pay_note'] ) ."'
							)";

				$this->db_ebmaster_write->save_queries = FALSE;
				$query = $this->db_ebmaster_write->query( $sql_add );
				$orderId = $record['order_id'];

				unset( $record );
				if( empty( $query ) ){
					echo "fail_order_id=".$orderId."\n";
					continue;
				}
			}

			$count = count( $data );
			unset( $data );
			$page++;
		}while( $count == $offset );
		echo "last_order_id=".$orderId."\n";

		return TRUE;
	}

	/**
	 * 迁移订单数据
	 * order_goods "订单商品表"
	 */
	public function orderGoods( $id, $type ){
		$page = 0;
		$offset = 100;

		$where = empty( $id ) ? '' :'WHERE `rec_id` > '.$id;
		//迁移order_goods
		do {
			$rows = $page*$offset;
			$sql = "SELECT * FROM `order_goods` ". $where ." LIMIT ". $rows .", ". $offset;
			$this->db_read->save_queries = FALSE;
			if( $type == 1 ){
				$delSql = "DELETE FROM `order_goods` WHERE `id` = ".$id;
				$this->db_ebmaster_write->save_queries = FALSE;
				$query = $this->db_ebmaster_write->query( $delSql );
				$sql = "SELECT * FROM `order_goods` WHERE `rec_id`= ".$id;
			}
			$query = $this->db_read->query( $sql );
			$data = $query->result_array();

			//插入数据
			foreach( $data as $record ){
				$sql_add = " INSERT INTO `order_goods` (
							`id`,
							`order_id`,
							`product_id`,
							`goods_name`,
							`sku`,
							`goods_number`,
							`market_price`,
							`final_price`,
							`is_real`,
							`is_gift`,
							`pro_type`,
							`add_time`
							)
							VALUES (
							'". $record['rec_id'] ."',
							'". $record['order_id'] ."',
							'". $record['goods_id'] ."',
							'". addslashes( $record['goods_name'] ) ."',
							'". $record['goods_sn'] ."',
							'". $record['goods_number'] ."',
							'". $record['market_price'] ."',
							'". $record['goods_price'] ."',
							'". $record['is_real'] ."',
							'". $record['is_gift'] ."',
							'". $record['pro_type'] ."',
							'". $record['add_time'] ."'
							)";
				$this->db_ebmaster_write->save_queries = FALSE;
				$query = $this->db_ebmaster_write->query( $sql_add );
				$id = $record['rec_id'];

				unset( $record );
				if( empty( $query )){
					echo "fail_rec_id=".$id."\n";
					continue;
				}
			}

			$count = count( $data );
			unset( $data );
			$page++;

		}while( $count == $offset );
		echo "last_rec_id=".$id."\n";

		return TRUE;
	}

	/**
	 * 迁移迁移点赞踩数据
	 * comment_support表
	 */
	public function commentSupport(){
		$page = 0;
		$offset = 100;
		do {
			$rows = $page*$offset;
			$sql = "SELECT * FROM `comment_support` LIMIT ". $rows .", ". $offset;
			$this->db_read->save_queries = FALSE;
			$query = $this->db_read->query( $sql );
			$data = $query->result_array();

			//插入数据
			foreach( $data as $record ){
				$sql_add = "INSERT INTO `comment_support` ( `comment_support_id`, `user_id` ,`comment_id`, `ip`, `support_type`, `create_time` ) VALUES ( '". $record['comment_support_id'] ."','". $record['user_id'] ."','". $record['comment_id'] ."', '". $record['ip'] ."', '". $record['support_type'] ."', '". $record['create_time'] ."' )";
				$this->db_ebmaster_write->save_queries = FALSE;
				$query = $this->db_ebmaster_write->query( $sql_add );
				if( empty( $query )){
					echo "fail_comment_support_id=".$record['comment_support_id']."\n";
					continue;
				}
			}

			$count = count( $data );
			unset( $data );
			$page++;

		}while( $count == $offset );

		return TRUE;
	}

	/**
	 * 迁移购物车数据
	 * cart表
	 */
	public function cart(){
		$page = 0;
		$offset = 100;
		do {
			$rows = $page*$offset;
			$sql = "SELECT * FROM `cart` LIMIT ". $rows .", ". $offset;
			$this->db_read->save_queries = FALSE;
			$query = $this->db_read->query( $sql );
			$data = $query->result_array();

			//插入数据
			foreach( $data as $record ){
				$sql_add = "INSERT INTO `cart` (
							`id`,
							`user_id`,
							`session_id`,
							`product_id`,
							`sku`,
							`goods_name`,
							`market_price`,
							`final_price`,
							`goods_number`,
							`is_real`,
							`order_to`,
							`favorable_gift_id`
							)
							VALUES (
							'". $record['rec_id'] ."',
							'". $record['user_id'] ."',
							'". $record['session_id'] ."',
							'". $record['goods_id'] ."',
							'". $record['goods_sn'] ."',
							'". addslashes( $record['goods_name'] ) ."',
							'". $record['market_price'] ."',
							'". $record['goods_price'] ."',
							'". $record['goods_number'] ."',
							'". $record['is_real'] ."',
							'". $record['order_to'] ."',
							'". $record['favorable_gift_id'] ."'
							)";
				$this->db_ebmaster_write->save_queries = FALSE;
				$query = $this->db_ebmaster_write->query( $sql_add );
				if( empty( $query )){
					echo "fail_rec_id=".$record['rec_id']."\n";
					continue;
				}
			}

			$count = count( $data );
			unset( $data );
			$page++;

		}while( $count == $offset );

		return TRUE;
	}

	/**
	 * 析构方法
	 */
	public function __destruct(){

	}

}

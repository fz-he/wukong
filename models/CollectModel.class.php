<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * Collect model.
 * @author Terry
 */
class CollectModel extends CI_Model {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get user colleted product ids by user id.
	 * @param type $userId
	 * @return array #The product ids collected by user.
	 * @author Terry
	 */
	public function getUserCollectedProIds($userId = 0) {
		
		if ($userId == 0) {
			return array();
		}
		return extractColumn($this->db_ebmaster_read->select('product_id')->from('collect_products')->where('user_id', $userId)->get()->result_array(), 'product_id');
	}

	/**
	 * 检查用户是否已经收藏了这个商品
	 * @param  integer $userId 用户id
	 * @param  integer $productId 产品id
	 * @author qcn
	 * @return booler
	 */
	public function checkUserProductCollected($userId = 0, $productId = 0) {
		if(empty($userId) || empty($productId)) {
			return false;
		}
		$this->db_ebmaster_read->from('collect_products');
		$this->db_ebmaster_read->where('user_id',$userId);
		$this->db_ebmaster_read->where('product_id',$productId);
		$count = $this->db_ebmaster_read->count_all_results();
		return ($count > 0);
	}

	/**
	 * 收藏商品的操作
	 * @param  integer $userId 用户id
	 * @param  integer $productId 产品id
	 * @author qcn
	 */
	public function collectProducts($userId = 0, $productId = 0) {
		if(empty($userId) || empty($productId)) {
			return false;
		}
		if($this->db_ebmaster_write->insert('collect_products',array(
			'user_id' => $userId,
			'product_id' => $productId,
			'add_time' => HelpOther::requestTime(),
		) ) ) {
			return true;
		}
	}

	/**
	 * 获得用户收藏商品列表
	 * @param integer $userId 
	 * @return Array 返回收藏商品列表
	 * @author lucas
	 */
	public function getUserCollecteList( $userId ){
		$this->db_ebmaster_read->from('collect_products');
		$this->db_ebmaster_read->where('user_id', $userId);
		$this->db_ebmaster_read->order_by('id','desc');
		$query = $this->db_ebmaster_read->get();
		$list = $query->result_array();

		return $list;
	}

	/**
	 * 修改收藏商品备注
	 * @param Array $info
	 * @return null
	 * @author lucas
	 */
	public function updateCollectBatch( $info ){
		if(!empty($info)){
			$this->db_ebmaster_write->update_batch( 'collect_products', $info, 'id' );
		}
	}

	/**
	 * 删除收藏商品
	 * @param integer $id
	 * @param integer $userId
	 * @return null
	 * @author lucas
	 */
	public function deleteCollect( $id, $userId ){
		$this->db_ebmaster_write->where('id', $id);
		$this->db_ebmaster_write->where('user_id',$userId);
		$this->db_ebmaster_write->delete('collect_products');
	}

	/**
	 * 检查用户是否收藏此商品
	 * @param integer $userId
	 * @param integer $goodsId
	 * @return boolean
	 * @author lucas
	 */
	public function checkUserGoodsCollected( $userId, $goodsId ){
		$this->db_ebmaster_read->from('collect_products');
		$this->db_ebmaster_read->where('user_id', $userId);
		$this->db_ebmaster_read->where('product_id', $goodsId);
		$count = $this->db_ebmaster_read->count_all_results();

		return ($count > 0);
	}

	/**
	 * 添加用户收藏商品
	 * @param integer $userId
	 * @param integer $goodsId
	 * @return boolean
	 * @author lucas
	 */
	public function collectGoods( $userId, $goodsId ){
		$this->db_ebmaster_write->insert('collect_products',array(
			'user_id' => $userId,
			'product_id' => $goodsId,
			'add_time' => $_SERVER['REQUEST_TIME'],
		));
	}


}

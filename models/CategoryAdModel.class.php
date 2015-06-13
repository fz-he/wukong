<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * 分类广告位管理Model
 * @author lucas
 */
class CategoryAdModel extends CI_Model {

	//表名 eb_pc_site:category_ad_batch
	private $_tableName_ad_batch = 'category_ad_batch';

	//表名 eb_pc_site:category_ad 
	private $_tableName_ad = 'category_ad';

	/*
	 * 初始化
	 */
	public function __construct(){
		parent::__construct();
	}

	/**
	 * 获得分类广告位数据
	 * @param integet $categoryId 类别ID
	 * @return Array 返回 分类广告位数据
	 * @author lucas
	 */
	public function getCategoryAdBatch( $categoryId ){
		$result = array();
		if( $categoryId > 0 ){
			$cacheKey = 'get_cat_ad_batch_%s';
			$cacheParams = array( $categoryId );
			$result = $this->memcache->get( $cacheKey, $cacheParams );
			if( $result === FALSE ){
				//get DB
				$this->db_ebmaster_read->select('id, type, start_time, end_time');
				$this->db_ebmaster_read->from( $this->_tableName_ad_batch );
				$this->db_ebmaster_read->where('category_id', $categoryId);
				$this->db_ebmaster_read->where('status', 1);
				$this->db_ebmaster_read->where('start_time <=', date( 'Y-m-d H:i:s', $_SERVER['REQUEST_TIME']+1800 ) );
				$this->db_ebmaster_read->where('end_time >=', date( 'Y-m-d H:i:s', $_SERVER['REQUEST_TIME']  ) );
				$query = $this->db_ebmaster_read->get();
				$result = $query->result_array();

				//set mc
				$this->memcache->set( $cacheKey, $result, $cacheParams );
			}
		}

		return $result;
	}

	/**
	 * 获得分类广告位图片数据
	 * @param integet $categoryId 类别ID
	 * @return Array 返回 分类广告位图片数据
	 * @author lucas
	 */
	public function getCategoryAd( $categoryId ){
		$result = array();
		if( $categoryId > 0 ){
			$cacheKey = 'get_cat_ad_%s';
			$cacheParams = array( $categoryId );
			$result = $this->memcache->get( $cacheKey, $cacheParams );
			if( $result === FALSE ){
				//get DB
				$this->db_ebmaster_read->select('category_id, category_ad_batch_id, type, content');
				$this->db_ebmaster_read->from( $this->_tableName_ad );
				$this->db_ebmaster_read->where('category_id', $categoryId);
				$this->db_ebmaster_read->where('status', 1);
				$this->db_ebmaster_read->order_by('sort', 'DESC');
				$query = $this->db_ebmaster_read->get();
				$result = $query->result_array();

				//set mc
				$this->memcache->set( $cacheKey, $result, $cacheParams );
			}
		}

		return $result;
	}

	/**
	 * 获得全部左侧广告信息
	 * @param null
	 * @return Array 返回 全部左侧广告信息
	 * @author lucas
	 */
	public function getCategoryAllLeftAdBatch(){
		$cacheKey = 'get_cat_left_ad_batch';
		$result = $this->memcache->get( $cacheKey );
		if( $result === FALSE ){
			//get DB
			$this->db_ebmaster_read->select('id, category_id, start_time, end_time');
			$this->db_ebmaster_read->from( $this->_tableName_ad_batch );
			$this->db_ebmaster_read->where('type', 1);
			$this->db_ebmaster_read->where('status', 1);
			$this->db_ebmaster_read->where('start_time <=', date( 'Y-m-d H:i:s', $_SERVER['REQUEST_TIME']+1800 ) );
			$this->db_ebmaster_read->where('end_time >=', date( 'Y-m-d H:i:s', $_SERVER['REQUEST_TIME']  ) );
			$query = $this->db_ebmaster_read->get();
			$result = $query->result_array();

			//set mc
			$this->memcache->set( $cacheKey, $result );
		}

		return $result;
	}

	/**
	 * 获得全部左侧广告图片
	 * @param null
	 * @return Array 返回 全部左侧广告图片
	 * @author lucas
	 */
	public function getCategoryAllLeftAd(){
		$cacheKey = 'get_cat_left_ad';
		$result = $this->memcache->get( $cacheKey );
		if( $result === FALSE ){
			//get DB
			$this->db_ebmaster_read->select('category_ad_batch_id, type, category_id, content');
			$this->db_ebmaster_read->from( $this->_tableName_ad );
			$this->db_ebmaster_read->where('status', 1);
			$this->db_ebmaster_read->order_by('sort', 'DESC');
			$query = $this->db_ebmaster_read->get();
			$result = $query->result_array();

			//set mc
			$this->memcache->set( $cacheKey, $result );
		}

		return $result;
	}

}
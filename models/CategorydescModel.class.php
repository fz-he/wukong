<?php
if( !defined( 'BASEPATH' ) )
	exit( 'No direct script access allowed' );

/**
 * 分类管理model
 * 版本 v2
 * @auther qcn
 * @date 2014-8-18 
 */
class CategorydescModel extends CI_Model {
	//定义操作表名 eb_pc_site:category
	private $_tableName = 'category_desc';

	public function __construct(){
		parent::__construct();
	}

	/**
	 * 获取分类到的信息
	 * @param  array  $fieldArray 字段
	 * @param  array  $whereArray 查询where条件
	 * @param  array  $orderBy    排序条件
	 * @param  string $groupBy    分组条件
	 * @param  array  $limit      limit条件
	 * @return array 返回分类的信息
	 */
	public function getCategoryDescInfo( $fieldArray = array(), $whereArray = array(), $orderBy = array(), $groupBy = '', $limit = array() ) {
		//缓存处理
		$memcacheKeyStrCode = array(md5(serialize($fieldArray) . serialize($whereArray) . serialize($orderBy) . $groupBy . serialize($limit)));
		$cacheKey = "idx_categorydescmodel_getcategorydescinfo_%s";
		$result = $this->memcache->get($cacheKey,$memcacheKeyStrCode);

		//字段处理
		if($result === false) {
			if(is_array($fieldArray) && count($fieldArray) > 0) {
				$fieldContion = implode(',', $fieldArray);
				$this->db_ebmaster_read->select($fieldContion);
			}

			$this->db_ebmaster_read->from($this->_tableName);

			//where条件处理
			if(is_array($whereArray) && count( $whereArray ) > 0 ) {
				foreach ($whereArray as $key => $value) {
					$this->db_ebmaster_read->where( $key, $value);
				}
			}

			//order by条件处理
			if(is_array($orderBy) &&  count( $orderBy ) > 0 ) {
				foreach ($orderBy as $key => $value) {
					$this->db_ebmaster_read->order_by( $key, $value);
				}
			}

			//group by条件处理
			if(!empty($groupBy)) {
				$this->db_ebmaster_read->group_by($groupBy); 
			}

			//limit 条件的处理
			if( count( $limit ) > 0 ) {
				$rows = 0;
				$offset = 0;
				if( isset( $limit['rows'] ) ) {
					$rows = $limit['rows'];
				}
				if( count( $limit ) == 1 ) {
					$rows = current($limit);
				}
				if( isset( $limit['offset'] ) ) {
					$offset = $limit['offset'];
				}
				$this->db_ebmaster_read->limit( $rows, $offset);
			}
			$query = $this->db_ebmaster_read->get();
			$result = $query->result_array();
			$this->memcache->set($cacheKey,$result,$memcacheKeyStrCode);
		}
		return $result;
	}
}
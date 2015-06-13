<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * Model for user.
 * @author hezaofeng
 */
class CounterRecordModel extends CI_Model {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 获取实例化
	 * @return UserModel
	 */
	public static function & getInstanceObj( ){
		return parent::_getBaseInstanceObj( __CLASS__ );
	}

	public function	getCounterRecord($uid, $type){
		$this->db_ebmaster_read->select('status');
		$this->db_ebmaster_read->from('counter_record');
		$this->db_ebmaster_read->where('uid',$uid);
		$this->db_ebmaster_read->where('type',$type);
		$this->db_ebmaster_read->limit(1);
		$query = $this->db_ebmaster_read->get();
		$res = $query->row_array();

		return id2name('status', $res, 1);
	}
	
	/**
	 * 
	 * @param type $uid 
	 * @param type $type 区分位置 1为个人中心首页物流通告 
	 * @param type $status 1为展示 0为收起
	 */
	public function setCounerRecord($uid, $type, $status = 0){
		
		$query = $this->db_ebmaster_write->query("INSERT INTO  `counter_record` (`uid` ,`type`, `status`, `last_time`)VALUES ($uid, $type, $status, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE `status` = $status, `last_time` = CURRENT_TIMESTAMP; ");
		//echo $query;
		return $query;
	}
	
}
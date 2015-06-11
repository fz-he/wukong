<?php

namespace app\models;

use app\models\common\EbARModel;

/**
 * Model for user.
 * @author Terry Lu
 */
class Users extends EbARModel {

	private static $_instance =	NULL;
	private  $table = 'users';
	
	public function __construct() {
		parent::__construct();
	}

	/**
	 * 获取实例化
	 * @return UserModel
	 */
	public static function getInstanceObj( ){
		if ( self::$_instance === NULL ){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * 根据用户id获取用户信息。
	 * @param int||array $uids #用户id
	 * @param boolean $cache
	 * @return array
	 * @author Terry
	 */
	public function getUserInfoByIds($uids,$cache=TRUE) {
		if ($cache) {
			$return = $this->memcache->ebMcFetchData($uids,self::MEM_KEY_USER_INFO,array($this,'getUserInfoByIds'));
		}else{
			$resArr = $this->db_write->select('user_id,email,user_name,password')->from('users')->where_in('user_id', $uids)->get()->result_array();
			$return = reindexArray($resArr, 'user_id');

		}
		return $return;
	}

	/**
	 * 根据指定的一个用户id获取用户信息
	 * @param  integer $userId 指定的用户id
	 * @author qcn
	 * @return array 用户信息
	 */
	public function getUserInfo($userId) {
		$userId = (int)$userId;
		$this->db_write->from('users');
		$this->db_write->where('user_id',$userId);
		$this->db_write->limit(1);
		$query = $this->db_write->get();
		$user = $query->row_array();
		return $user;
	}

	/**
	 * 根据指定的一个用户邮箱获取用户信息
	 * @param  string $email 指定的用户email
	 * @author qcn
	 * @return array 用户信息
	 */
	public function getUserByEmail($email) {
		if(is_array($email)) {
			return false;
		}
		$this->db_write->from('users');
		$this->db_write->where('email', $email);
		$this->db_write->limit(1);
		$query = $this->db_write->get();
		$record = $query->row_array();
		
		return $record;
	}

	/**
	 * 根据指定的一个用户名称获取用户信息
	 * @param  string $userName 指定的用户email
	 * @author lucas
	 * @return array 用户信息
	 */
	public function getUserByName( $userName ){
		if(is_array($userName)) {
			return false;
		}
		$customers = Users::find()
		->where(['user_name' => $userName])->asArray()->one();	
	var_dump($customers);die;
		$sql = 'SELECT * FROM ' . $this->table . ' WHERE user_name=' . '\'' . $userName . '\'';
		$command =  $this->db_write->createCommand( $sql );
		$record = $command->queryOne();

		return $record;
	}

	/**
	 * 获取时时的用户信息
	 * @param  integer $userId 指定的用户id
	 * @author qcn
	 */
	public function getUserByIdImmediately($userId) {
		$this->db_write->from('users');
		$this->db_write->where('user_id',$userId);
		$this->db_write->limit(1);
		$query = $this->db_write->get();
		$user = $query->row_array();
		return $user;
	}

	/**
	 * 获取用户积分
	 * @param integer $userId 用户的id
	 * @author qcn
	 * @return array
	 */
	public function getUserPoint($userId) {
		$this->db_write->from('points');
		$this->db_write->where('customer_id', $userId);
		$this->db_write->limit(1);
		$query = $this->db_write->get();
		$res = $query->row_array();
		return $res;
	}

	/**
	 * 获取ebay积分通过email
	 * @param  string $email email
	 */
	public function getEBayPointByEmail($email) {
		if($email == 'savin_gheorghita@yahoo.com') { return 0; }
		$this->db_write->select('total_point');
		$this->db_write->from('ebay_members');
		$this->db_write->where('ebay_email', $email);
		$this->db_write->limit(1);
		$query = $this->db_write->get();
		$res = $query->row_array();
		$res = id2name('total_point', $res, 0);
		return $res;
	}

	/**
	 * 获取指定邮件的订阅信息
	 * @param  string $email 订阅的邮件
	 */
	public function getEmailSubscribeInfo($email) {
		$this->db_write->from('email_list');
		$this->db_write->where('email',$email);
		$this->db_write->limit(1);
		$query = $this->db_write->get();
		$res = $query->row_array();
		return $res;
	}

	/**
	 * 获取是不是不锁定用户信息
	 * @param  string  $input 用户邮箱或者用户名称
	 * @param  integer $type 1的时候是用户邮箱
	 * @author qcn
	 * @return integer　有值则是有
	 */
	public function checkBannedByBlackList($input,$type = 1) {
		$this->db_write->from('black_list');
		if($type == 1) {
			$this->db_write->where('email', $input);
		} else {
			$this->db_write->where('name', $input);
		}
		$this->db_write->where('type',$type);
		$count = $this->db_write->count_all_results();
		return ($count > 0);
	}

	/**
	 * 检查用户是否购买过某商品（product）
	 * @param int $uid
	 * @param int $pid
	 * @return boolean
	 * @author Terry
	 */
	public function checkProductPurchased($uid,$pid) {
		$res = FALSE;
		$query = $this->db_ebmaster_read->select('order_id')->from('order_info')->where('user_id', $uid)->get();
		if($query){

			/*获取用户购买过的订单id*/
			$orderIdsArr = $query->result_array();
			$orderIds = extractColumn($orderIdsArr, 'order_id');

			/*根据订单和sku到order_goods表里面进行匹配，匹配到则说明用户买过此商品（product）。*/
			$resOrderGoods = $this->db_ebmaster_read->select('order_id')->from('order_goods')->where_in('order_id', $orderIds)->where('product_id',$pid)->get();
			if($resOrderGoods && $resOrderGoods->row()){
				$res = TRUE;
			}
		}
		return $res;
	}

	/**
	 * 判断  conpon code 获取 订阅信息
	 * @param string $hash
	 * @return array $result
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getSubscribeInfoByHash($hash) {
		$this->db_write->from('email_list');
		$this->db_write->where('hash', $hash);
		$this->db_write->limit(1);
		$query = $this->db_write->get();
		$res = $query->row_array();

		return $res;
	}

	/**
	 * 更新用户登录信息
	 * @param integer $userId 用户id
	 * @author qcn
	 */
	public function updateUserLoginInfo($userId) {
		$this->db_write->set('visit_count', 'visit_count+1', false);
		$this->db_write->set('last_ip', $this->input->ip_address());
		$this->db_write->set('last_login', $_SERVER['REQUEST_TIME']);
		$this->db_write->where('user_id', $userId);
		$this->db_write->update('users');
	}

	/**
	 * 更新订阅邮件
	 * @author qcn
	 */
	public function updateEmailSubscribe($email, $info) {
		$email = strval($email);
		$this->db_write->where('email', $email);
		$this->db_write->update('email_list', $info);
	}

	/**
	 * 添加积分
	 * @param integer $userId 用户id
	 * @author qcn
	 */
	public function addPoint($userId, $info) {
		foreach($info as $key => $value){
			$this->db_write->set($key, $value, false);
		}
		$this->db_write->where('customer_id', $userId);
		$this->db_write->update('points');
	}

	/**
	 * 创建用户积分
	 * @param  array $info 积分信息
	 * @author qcn
	 */
	public function createPoint($info) {
		$this->db_write->insert('points', $info);
		return $this->db_write->insert_id();
	}

	/**
	 * 创建积分log
	 * @param  array $info 积分信息
	 * @author qcn
	 */
	public function createPointLog($info) {
		$this->db_write->insert('point_log', $info);
		return $this->db_write->insert_id();
	}

	/**
	 * 创建新用户
	 * @param array $info 用户信息数组
	 * @author qcn
	 */
	public function createUser($info) {
		$this->db_write->insert('users', $info);
		return $this->db_write->insert_id();
	}

	/**
	 * 创建paypal用户注册信息
	 * @param array $info 用户信息数组
	 * @author qcn
	 */
	public function createPaypalRegister($info) {
		$this->db_write->insert('paypal_register', $info);
		return $this->db_write->insert_id();
	}

	/**
	 * 创建密码
	 * @param  array $password 密码的关键词
	 * @author qcn
	 */
	public function hashPassword($password){
		$salt = '';
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$lc = strlen($chars)-1;
		mt_srand(10000000*(double)microtime());
		for($i=0;$i<2;$i++) {
			$salt .= $chars[mt_rand(0,$lc)];
		}
		return md5($salt.$password).':'.$salt;
	}

	/**
	 * 创建订阅邮件
	 * @param  array $info 订阅邮件的信息
	 */
	public function createEmailSubscribe($info) {
		$this->db_write->insert('email_list', $info);
		$id = $this->db_write->insert_id();
		return $id;
	}

	/**
	 * 便捷用户信息
	 * @param  integer $userId 用户id
	 * @param  array $info 用户信息
	 */
	public function editUser($userId, $info) {
		$this->db_write->where('user_id', $userId);
		$this->db_write->update('users', $info);
	}

	/**
	 * 验证用户密码
	 * @param string $dbPassword 数据密码
	 * @param string $inputPassword 登录密码
	 * @return boolean
	 * @author lucas
	 */
	public function validatePassword($dbPassword,$inputPassword){
		$hashArr = explode(':',$dbPassword);
		if(count($hashArr) == 1){
			if(md5($inputPassword) !== $dbPassword) return false;
		}elseif(count($hashArr) == 2){
			if(md5($hashArr['1'].$inputPassword) !== $hashArr['0']) return false;
		}

		return true;
	}

	/**
	 * 检测用户名是否存在
	 * @param string $userName 用户名称
	 * @param inc $userId 用户ID
	 * @return boolean
	 * @author lucas
	 */
	public function checkUserNameUsed( $userName, $userId = false ){
		$this->db_write->from('admin_user');
		$this->db_write->where('user_name',$userName);
		$count = $this->db_write->count_all_results();
		if($count > 0) return true;

		$this->db_write->from('users');
		$this->db_write->where('user_name',$userName);
		if($userId !== false) $this->db_write->where('user_id !=',$userId);
		$count = $this->db_write->count_all_results();
		if($count > 0) return true;

		return false;
	}

	/**
	 * 检测邮箱是否存在
	 * @param string $email 用户邮箱地址
	 * @param inc $userId 用户ID
	 * @return boolean
	 * @author lucas
	 */
	public function checkEmailUsed( $email, $userId = false ){
		$this->db_write->from('users');
		$this->db_write->where('email',$email);
		if($userId !== false){
			$this->db_write->where('user_id !=',$userId);
		}
		$count = $this->db_write->count_all_results();
		return ($count > 0);
	}

	/**
	 * 修改订阅邮件添加日志
	 * @param array $info
	 */
	public function logUpdateEmailSubscribe( $info ) {
		$this->db_write->insert( 'email_list_editlog', $info );
	}

	/**
	 * 获得Point列表
	 * @param int $userId
	 * @param int $page
	 * @return Array 返回数据列表
	 */
	public function getPointLogList( $userId, $page = 1){
		$limit = 10;
		$start = ($page-1)*$limit;

		$this->db_write->from('point_log');
		$this->db_write->where('customer_id',$userId);
		$count = $this->db_write->count_all_results('',SQL_EXECUTE_RETAIN_CONDITION);

		$this->db_write->order_by('log_id','desc');
		$this->db_write->limit($limit,$start);
		$query = $this->db_write->get();
		$list = $query->result_array();

		return array($list,$count);
	}

	/**
	 * facebook 账号
	 */

	public function fbInfoByfbId( $fbId ){
		$result = array();
		if( $fbId > 0 ){
			$this->db_write->select('user_id,user_name,email,facebook_id');
			$this->db_write->from('users');
			$this->db_write->where('facebook_id',$fbId);
			$this->db_write->limit(1);
			$query = $this->db_write->get();
			$result = $query->row_array();
		}
		return $result;
	}

	const MEM_KEY_USER_INFO = 'userInfo%s';//userInfo{$userId} user infomation.

}

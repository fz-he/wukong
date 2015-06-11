<?php  

namespace app\libraries;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\helpers\ArrayHelper;

class Session {

	const INIT_DATA = 'a:0:{}';

	protected $_CI = NULL;
	protected $_ip = '';
	protected $_md5 = '';
	protected $_expiry = 0;
	protected $_data = array();
	protected $_session_id = '';
	protected $_memcache = NULL;
	protected static $_instance = NULL;

	public function __construct(){
		$this->_ip = $this->_getIP();
		$params = Yii::$app->params;
		$memcacheConfig = $params['eb_db_config']['memcache_config'];
		
		$host_config = $memcacheConfig['memcache_session']['host'] ;
		$port_config = $memcacheConfig['memcache_session']['port'] ; 
		$this->_memcache = new \Memcache;
		$this->_memcache->addServer( $host_config , $port_config );

		$cookies = Yii::$app->request->cookies;
		$cookie_session_key = $cookies->getValue(SESSION_NAME);  
		if($cookie_session_key === false || !$this->_checkCookieSessionKey($cookie_session_key)){
			$this->_session_id = $this->_genSessionId();
			$this->_initWithEmptySession($this->_session_id);
			
			$cookies = Yii::$app->response->cookies;
			$cookies->add(new \yii\web\Cookie([
				'name' => SESSION_NAME,
				'value' => $this->_session_id.$this->_genSessionKey($this->_session_id),
				'expire' => 0,
				'domain' => COMMON_DOMAIN,
				'path' => ArrayHelper::id2name('cookie_path',$GLOBALS,'/'),
				'secure' => ArrayHelper::id2name('cookie_secure',$GLOBALS,false),
			]));
		}else{
			$this->_session_id = substr($cookie_session_key,0,32);
			$this->_loadSession();
		}
	}
	
	public static function  getInstance( ){
		if (self::$_instance === NULL) {
            self::$_instance = new self();
        }
        return self::$_instance;
	}

	public function getSessionId(){
		return $this->_session_id;
	}

	public function get($key){
		if(isset($this->_data[$key])){
			return $this->_data[$key];
		}else{
			return false;
		}
	}

	public function set($key,$value){
			$this->_data[$key] = $value;
			$this->_memcache->set($this->_session_id,array(
				'expiry' => $_SERVER['REQUEST_TIME'],
				'ip' => $this->_ip,
				'data' => addslashes(serialize($this->_data)),
			), FALSE ,SESSION_LIFE_TIME);		
		$this->_loadSession();
	}

	public function delete($key){
		if(isset($this->_data[$key])){
			unset($this->_data[$key]);
			$this->_memcache->set($this->_session_id,array(
				'expiry' => $_SERVER['REQUEST_TIME'],
				'ip' => $this->_ip,
				'data' => addslashes(serialize($this->_data)),
			),false ,SESSION_LIFE_TIME);
			$this->_loadSession();
		}
	}

	public function destroy(){
		$this->_memcache->delete($this->_session_id);
		$this->_CI->input->set_cookie(array(
			'name' => SESSION_NAME,
			'value' => '',
			'expire' => '',
			'domain' => COMMON_DOMAIN,
			'path' => ArrayHelper::id2name('cookie_path',$GLOBALS,'/'),
			'secure' => ArrayHelper::id2name('cookie_secure',$GLOBALS,false),
		));
		$this->_CI->input->set_cookie(array(
			'name' => "ECS[username]",
			'value' => '',
			'expire' => $_SERVER['REQUEST_TIME']-3600,
			'path' => ArrayHelper::id2name('cookie_path',$GLOBALS,'/'),
			'secure' => ArrayHelper::id2name('cookie_secure',$GLOBALS,false),
		));
		$this->_CI->input->set_cookie(array(
			'name' => "ECS[user_id]",
			'value' => '',
			'expire' => $_SERVER['REQUEST_TIME']-3600,
			'path' => ArrayHelper::id2name('cookie_path',$GLOBALS,'/'),
			'secure' => ArrayHelper::id2name('cookie_secure',$GLOBALS,false),
		));
		$this->_CI->input->set_cookie(array(
			'name' => "ECS[password]",
			'value' => '',
			'expire' => $_SERVER['REQUEST_TIME']-3600,
			'path' => ArrayHelper::id2name('cookie_path',$GLOBALS,'/'),
			'secure' => ArrayHelper::id2name('cookie_secure',$GLOBALS,false),
		));
		$this->_md5 = md5(self::INIT_DATA);
		$this->_expiry = 0;
		$this->_data = array();
	}

	public function dumpSession(){	
		
		return $this->_data;
	}

	protected function _getIP(){
		$cookies = Yii::$app->request->cookies;
		$ip = $cookies->getValue('real_ipd');  
		
		if ($ip === false) {
			$ip = Yii::$app->getRequest()->getUserIP();
			
			$cookies = Yii::$app->response->cookies;
			// add a new cookie to the response to be sent
			$cookies->add(new \yii\web\Cookie([
				'name' => 'real_ipd',
				'value' => $ip,
				'expire' => $_SERVER['REQUEST_TIME'] + 36000,
				'domain' => COMMON_DOMAIN,
				'path' => ArrayHelper::id2name('cookie_path',$GLOBALS,'/'),
				'secure' => ArrayHelper::id2name('cookie_secure',$GLOBALS,false),
			]));
		}

		return $ip;
	}

	protected function _genSessionId(){
		$session_id = md5(uniqid(mt_rand(),true).mt_rand(0,1000000));

		return $session_id;
	}

	protected function _genSessionKey($session_id){
		$ip = substr($this->_ip,0,strrpos($this->_ip,'.'));
		return sprintf('%08x', crc32(ROOT_PATH . $ip . $session_id));
	}

	protected function _checkCookieSessionKey($cookie_session_key){
		$key = $this->_genSessionKey(substr($cookie_session_key,0,32));

		return ($key == substr($cookie_session_key,32));
	}

	protected function _initWithEmptySession($session_id){
		$this->_memcache->set($session_id,array(
			'expiry' => $_SERVER['REQUEST_TIME'],
			'ip' => $this->_ip,
			'data' => self::INIT_DATA,
		),FALSE ,SESSION_LIFE_TIME);
	}

	protected function _loadSession(){
		$session = $this->_memcache->get($this->_session_id);

		if($session === false || empty($session)){
			//Empty session
			$this->_initWithEmptySession($this->_session_id);
			$this->_md5 = md5(self::INIT_DATA);
			$this->_expiry = 0;
			$this->_data = array();
		}elseif(empty($session['data']) || $_SERVER['REQUEST_TIME'] - $session['expiry'] > SESSION_LIFE_TIME){
			//Expired session
			$this->_md5 = md5(self::INIT_DATA);
			$this->_expiry = 0;
			$this->_data = array();
		}else{
			//Normal session
			$this->_md5 = md5($session['data']);
			$this->_expiry = $session['expiry'];
			$this->_data = unserialize(stripslashes($session['data']));
		}
	}
}

/* End of file Session.php */
/* Location: ./application/libraries/Session.php */

<?php
namespace app\models;

use app\models\common\EbARModel;
use app\components\helpers\ArrayHelper;

class Appmodel extends EbARModel {
	//追踪代码用,见工单15787
	public static $gaEcommProdid = array(
		'us' => 'usd',
		'de' => 'eur',
		'es' => 'esp',
		'fr' => 'fra',
		'it' => 'it',
		'br' => 'br',
		'ru' => 'ru',
	);

	protected $_config = null;
	private static $_instance = NULL;

	public function __construct(){
		parent::__construct();
	}
	public static function getInstanceObj( ){
		if ( self::$_instance === NULL ){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public static function getDb(){  
        return Yii::$app->eachbuyer_eb_slave;
    }
/*
| -------------------------------------------------------------------
|  Config Functions
| -------------------------------------------------------------------
*/
	public function getAllConfig(){
		if($this->_config === null) $this->_loadShopConfig();
		return $this->_config;
	}

	public function getConfig($key,$default = ''){
		if($this->_config === null) $this->_loadShopConfig();
		return ArrayHelper::id2name($key,$this->_config,$default);
	}

	/**
	 * 获取货币的配置（汇率和货币格式）
	 * @param  string $key     货币标示
	 * @param  string $default 默认返回
	 */
	public function getConfigCurrency($key = 'USD',$default = '') {
		$cache_key = 'idx_all_config_currency';
		$cache_params = array($key, $default);
		$result = $this->memcache->get( $cache_key , $cache_params );
		if( $result === false ) {
//			$this->db_ebmaster_read->select('code, rate, format');
//			$this->db_ebmaster_read->from('currency');
//			$query = $this->db_ebmaster_read->get();
//			$result = $query->result_array();			

			$sql = 'SELECT code, rate, format FROM currency';
			$command =  $this->db_ebmaster_read->createCommand( $sql );
			$result = $command->queryAll();

			$resultMc = array();
			if( !empty( $result ) && is_array( $result ) ){
				foreach ( $result as $v ){
					$resultMc[ trim( $v['code'] ) ] = $v ;
				}
				$result = $resultMc ;
			}
			$this->memcache->set( $cache_key , $result , $cache_params );
		}
		$return = array( 'code' => $key , 'rate' => $default, 'format' => '$%s' );
		if( isset( $result[ trim( $key ) ] ) ){
			$return = $result[ trim( $key ) ] ;
		}
		return $return;
	}

	protected function _loadShopConfig(){
		$cache_key = 'idx_load_shop_config';
		$config = $this->memcache->get($cache_key);
		if($config === false){			
//			$this->db_read->select('code,value');
//			$this->db_read->from('shop_config');
//			$this->db_read->where('parent_id >',0);
//			$query = $this->db_read->get();
//			$list = $query->result_array();

			$sql = 'SELECT code, value FROM shop_config where parent_id > :parent_id';
			$command =  $this->db_read->createCommand( $sql );
			$command->bindValue(':parent_id', 0);
			$list = $command->queryAll();
			
			$config = array();
			foreach ($list as $record){
				$config[$record['code']] = $record['value'];
			}

			/* 对数值型设置处理 */
			$config['watermark_alpha']      = intval($config['watermark_alpha']);
			$config['market_price_rate']    = floatval($config['market_price_rate']);
			$config['integral_scale']       = floatval($config['integral_scale']);
			$config['cache_time']           = intval($config['cache_time']);
			$config['thumb_width']          = intval($config['thumb_width']);
			$config['thumb_height']         = intval($config['thumb_height']);
			$config['image_width']          = intval($config['image_width']);
			$config['image_height']         = intval($config['image_height']);
			$config['best_number']          = !empty($config['best_number']) && intval($config['best_number']) > 0 ? intval($config['best_number'])     : 3;
			$config['new_number']           = !empty($config['new_number']) && intval($config['new_number']) > 0 ? intval($config['new_number'])      : 3;
			$config['hot_number']           = !empty($config['hot_number']) && intval($config['hot_number']) > 0 ? intval($config['hot_number'])      : 3;
			$config['promote_number']       = !empty($config['promote_number']) && intval($config['promote_number']) > 0 ? intval($config['promote_number'])  : 3;
			$config['top_number']           = intval($config['top_number'])      > 0 ? intval($config['top_number'])      : 10;
			$config['history_number']       = intval($config['history_number'])  > 0 ? intval($config['history_number'])  : 5;
			$config['comments_number']      = intval($config['comments_number']) > 0 ? intval($config['comments_number']) : 5;
			$config['article_number']       = intval($config['article_number'])  > 0 ? intval($config['article_number'])  : 5;
			$config['page_size']            = intval($config['page_size'])       > 0 ? intval($config['page_size'])       : 10;
			$config['bought_goods']         = intval($config['bought_goods']);
			$config['goods_name_length']    = intval($config['goods_name_length']);
			$config['top10_time']           = intval($config['top10_time']);
			$config['goods_gallery_number'] = intval($config['goods_gallery_number']) ? intval($config['goods_gallery_number']) : 5;
			$config['no_picture']           = !empty($config['no_picture']) ? str_replace('../', './', $config['no_picture']) : 'images/no_picture.gif'; // 修改默认商品图片的路径
			$config['qq']                   = !empty($config['qq']) ? $config['qq'] : '';
			$config['ww']                   = !empty($config['ww']) ? $config['ww'] : '';
			$config['default_storage']      = isset($config['default_storage']) ? intval($config['default_storage']) : 1;
			$config['min_goods_amount']     = isset($config['min_goods_amount']) ? floatval($config['min_goods_amount']) : 0;
			$config['one_step_buy']         = empty($config['one_step_buy']) ? 0 : 1;
			$config['invoice_type']         = empty($config['invoice_type']) ? array('type' => array(), 'rate' => array()) : unserialize($config['invoice_type']);
			$config['show_order_type']      = isset($config['show_order_type']) ? $config['show_order_type'] : 0;    // 显示方式默认为列表方式
			$config['help_open']            = isset($config['help_open']) ? $config['help_open'] : 1;    // 显示方式默认为列表方式

			$lang_array = array('zh_cn', 'zh_tw', 'en_us');
			if (empty($config['lang']) || !in_array($config['lang'], $lang_array)) $config['lang'] = 'zh_cn';
			if (empty($config['integrate_code'])) $config['integrate_code'] = 'ecshop';
			$this->memcache->set($cache_key,$config);
		}

		$this->_config = $config;
	}
/*
| -------------------------------------------------------------------
|  Price Functions
| -------------------------------------------------------------------
*/
	public function genPriceArr($price){
		$length = strlen($price);
		$signPos = 0;
		for ($i = 0;$i<$length;$i++) {
			if (is_numeric(substr($price,$i,1))) {
				$signPos = $i - 1;
				break;
			}
		}
		$pointPos = strrpos($price, '.');

		$priceArr = array(
			'origin' => $price,
			'sign' => trim(substr($price, 0, $signPos + 1)),
			'integer' => trim(substr($price, $signPos + 1, $pointPos - $signPos - 1)),
			'fractional' => trim(substr($price, $pointPos + 1)),
		);

		return $priceArr;
	}

	public function localTime($timestamp){
		$timezone = $this->getConfig('timezone');
		$timestamp += ($timezone * 3600);

		return $timestamp;
	}
/*
| -------------------------------------------------------------------
|  User Session Functions
| -------------------------------------------------------------------
*/
	public function checkUserLogin(){
		$user_id = $this->session->get('user_id');
		if($user_id === false || $user_id <= 0){
			return false;
		}else{
			return true;
		}
	}

	public function getCurrentUserId(){
		$user_id = 0;
		if($this->checkUserLogin()){
			$user_id = $this->session->get('user_id');
			$user_id = intval($user_id);
		}

		return $user_id;
	}

	public function getCurrentUserName(){
		$user_name = '';
		if($this->checkUserLogin()){
			$user_name = $this->session->get('user_name');
			$user_name = strval($user_name);
		}

		return $user_name;
	}

	public function getCurrentUserEmail(){
		$email = '';
		if($this->checkUserLogin()){
			$email = $this->session->get('email');
			$email = strval($email);
		}

		return $email;
	}
/*
| -------------------------------------------------------------------
|  public Functions
| -------------------------------------------------------------------
*/
	public function currentLanguageId(){
		$language_id = $this->session->get('language_id');
		if($language_id === false) {$language_id = 1;}

		return $language_id;
	}

	public function currentLanguageCode(){
		$language_code = $this->session->get('language_code');
		if($language_code === false) {$language_code = DEFAULT_LANGUAGE;}

		return $language_code;
	}

	/**
	 * 根据语言ID获得语言code
	 * @param inc $languageId 语言ID
	 * @return string 语言code
	 */
	public function getLanguageCodeById($languageId) {
		$this->db_ebmaster_read->select('language_code');
		$this->db_ebmaster_read->from('languages');
		$this->db_ebmaster_read->where('language_id', $languageId);
		return $this->db_ebmaster_read->get()->row()->language_code;
	}

	/**
	 * 根据语言code获得语言ID
	 * @param string $languageId 语言code
	 * @return inc 语言 ID
	 */
	public function getLanguageCodeByCode( $languageCode ) {
		$this->db_ebmaster_read->select('language_id');
		$this->db_ebmaster_read->from('languages');
		$this->db_ebmaster_read->where('language_code', $languageCode);
		return $this->db_ebmaster_read->get()->row()->language_id;
	}

	public function currentCurrency(){
		$currency = $this->session->get('currency');
		if($currency === false) $currency = DEFAULT_CURRENCY;

		return $currency;
	}

}
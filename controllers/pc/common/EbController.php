<?php 

namespace app\controllers\pc\common;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\libraries\Session;
use app\libraries\Memcache;
use app\libraries\Log;
use app\models\Appmodel;
use app\models\Users;
use app\models\Banner;
use app\models\Category;
use app\models\Categorydesc;
use app\components\helpers\ArrayHelper;
use app\components\helpers\HelpOther;
use app\components\helpers\HelpUrl;
use app\components\helpers\OtherHelper;

class EbController extends Controller {

	protected $mem_key_user_register_time_info = 'user_register_times_%s';
	/*
	 * databases master&slave
	 */

	public $session = NULL;
	public $memcache = NULL;
	public $log = NULL;
	public $m_app = NULL;

	/*
	 * data used by view
	 * extract() when $this->load->view($this->router->class,$this->_view_data);
	 */
	protected $_view_data = array();

	/*
	 * constructer.
	 * run before every request.
	 * init db,language&currency etc.
	 */
	public function  init(){
		/*
		 * basic settings
		 */
		@ini_set('memory_limit', '64M');
		$current_page = strtolower(get_class($this));
		$this->_view_data['current_page'] = $current_page;

		$this->log =  Log::getInstance();
		$this->memcache =  Memcache::getInstance();
		$this->session =  Session::getInstance();
		$this->m_app = new Appmodel;

		$this->_view_data['language_code'] = $this->_resolveCurrentLanguage();
		$this->_view_data['currency'] = $this->_resolveCurrentCurrency(); 

		/*
		 * head banner display
		 */
		$this->_view_data['headBannerDisabled'] = false;

		/*
		 * check user login & save user info into session
		 */
		$this->_addUserInfo();
		
		/*
		 * webgains
		 */
		$this->_setWebgainsCookie();

		/**
		 * 全站通栏banner
		 */
		$this->_fullSiteBanner();

		//取出并创建分类树
		$allCategoryMap = $this->_getMapCategory(); 
		$this->_view_data['allCategoryMap'] = $allCategoryMap; 
	}

	public function index( $templet = '' ){
		/*
		 * get page head,header&footer data & save to $this->_view_data
		 */
		$this->_addHeadData();
		$this->_addHeaderData();
		$this->_addFooterData();

		/*
		 * close databases master&slave
		 */
		$this->db_read->close();
		$this->db_write->close();
		//抛到页面sql 使用情况
		$this->_view_data['open_analysis_log_status'] = FALSE;
		// if( defined('OPEN_LOG_ANALYSIS') && OPEN_LOG_ANALYSIS === TRUE ){
		// 	$this->_view_data['open_analysis_log_status'] = TRUE ;
		// 	$this->_view_data['analysis_log'] = $GLOBALS['analysis_log'];
		// }

		$this->_view_data['captchaInvalid'] = lang('p_captcha_invalid');
		/*
		 * render view
		 */
		if( empty( $templet ) ){
			$this->load->view( strtolower(get_class($this)) , $this->_view_data );
		}else {
			$this->load->view( strtolower( $templet ) ,$this->_view_data);
		}
	}

	/*
	 * resolve language code&id from url
	 * save into session
	 */
	protected function _resolveCurrentLanguage(){
		$params = Yii::$app->params;
		$language_list = $params['language_list'];

		$domain = $_SERVER['HTTP_HOST'];
		$domain = explode('.', $domain);
		$domain = $domain[0];
		$domain = substr($domain,0,2);

		$language_id = 1;
		$language_code = DEFAULT_LANGUAGE;
		foreach($language_list as $key => $lang_record){
			if($domain == $lang_record['code']){
				$language_id = $key;
				$language_code = $lang_record['code'];
				break;
			}
		}
		Yii::$app->language = $language_code;
		$this->session->set('language_code',$language_code);
		$this->session->set('language_id',$language_id);

		return $language_code;
	}

	/**
	 * 全站通栏banner
	 * @return array
	 * @author qcn+bryan
	 */
	protected function _fullSiteBanner() {
		$this->_view_data['fullSiteBanner'] = Banner::getInstanceObj()->getAllSiteBanner( $this->m_app->currentLanguageId() );
	}

	/*
	 * resolve currency
	 * save into session
	 */
	protected function _resolveCurrentCurrency(){
		$params = Yii::$app->params;
		$language_list = $params['language_list'];
		
		$language_id = $this->m_app->currentLanguageId();
		$currencyFromUrl = Yii::$app->request->get('currency');
		$getCurrency = OtherHelper::get_cookie('currencyTypeNew');
		$currency = '';
		//参数获取货币
		if($currencyFromUrl !== false){
			$currency = strtoupper( trim( $currencyFromUrl ) );
			//参数获取cookie后  种cookie
			if( in_array( $currency , $GLOBALS['currency_list'] ) ){
				OtherHelper::set_cookie('currencyTypeNew', $currency , 864000 );
			}
		}elseif($getCurrency !== false){
			//此参数从cookie 获取  不需要大写转化 用户修改cookie
			$currency = $getCurrency;
		}
		//校验货币是否在已有的货币类型中
		if( !in_array( $currency , $GLOBALS['currency_list'] ) ){
			if(isset($language_list[$language_id]) && isset($language_list[$language_id]['currency'])){
				$currency = $language_list[$language_id]['currency'];
			}else{
				$currency = DEFAULT_CURRENCY;
			}
		}

		$this->session->set('currency', strtoupper( trim( $currency ) ) );
		
		return $currency;
	}

	/*
	 * get page head data & save to $this->_view_data
	 */
	protected function _addHeadData(){
		global $language_list;
		$language_id = $this->m_app->currentLanguageId();
		$language_code = $this->m_app->currentLanguageCode();

		//header meta canonical
		if(!isset($this->_view_data['head']['canonical'])){
			$canonical = '';
			if($_SERVER['REQUEST_URI'] && strpos($_SERVER['REQUEST_URI'],'?')!==false){
				$canonical = $_SERVER['REQUEST_URI'];
				$canonical = substr($canonical,1,strpos($_SERVER['REQUEST_URI'],'?')-1);
				$canonical = eb_gen_url($canonical);
				$canonical = "<link rel='canonical' href='{$canonical}' />";
			}
			$this->_view_data['head']['canonical'] = $canonical;
		}

		//header meta content-language
		if(!isset($this->_view_data['head']['html_meta_lang'])){
			if(isset($language_list[$language_id])){
				$meta_lang = $language_list[$language_id]['common_code'];
			}else{
				$meta_lang = $language_list[1]['common_code'];
			}
			$this->_view_data['head']['html_meta_lang'] = '<meta http-equiv="content-language" content="' . $meta_lang .'" />';
		}

		//header meta alternate
		if(!isset($this->_view_data['head']['html_google_rel']) && $this->router->class != 'atoz' && $this->router->class != 'buy'){
			global $language_list;
			global $lang_basic_url;
			$html_google_rel_list = array();
			foreach($language_list as $record){
				$html_google_rel_list[$record['common_code']] = ArrayHelper::id2name($record['code'],$lang_basic_url,BASIC_URL).uri_string();
			}
			$this->_view_data['head']['html_google_rel'] = $html_google_rel_list;
		}

		//SEO优化
		if(!isset($this->_view_data['head']['html_google_rel_seo']) && $this->router->class != 'atoz' && $this->router->class != 'buy'){
			$lang_basic_seo_url = array(
									'us' => 'http://m.eachbuyer.com/',
									'de' => 'http://m.eachbuyer.com/de/',
									'es' => 'http://m.eachbuyer.com/es/',
									'fr' => 'http://m.eachbuyer.com/fr/',
									'it' => 'http://m.eachbuyer.com/it/',
									'br' => 'http://m.eachbuyer.com/br/',
									'ru' => 'http://m.eachbuyer.com/ru/',
									);
			$html_google_rel_seo_list = array();
			$languageCode = $this->m_app->currentLanguageCode();
			foreach($language_list as $record){
				if($record['code'] == $languageCode){
					$html_google_rel_seo_list[$record['common_code']] = ArrayHelper::id2name($languageCode,$lang_basic_seo_url,BASIC_URL).uri_string();
				}
			}
			$this->_view_data['head']['html_google_rel_seo'] = $html_google_rel_seo_list;
		}

		//header meta keywords
		if(!isset($this->_view_data['head']['keywords_desc_domain'])){
			global $lang_basic_url;
			$url = ArrayHelper::id2name($language_code,$lang_basic_url,BASIC_URL);
			$url = str_replace('http://','',$url);
			$url = trim($url,'/');
			$this->_view_data['head']['keywords_desc_domain'] = COMMON_META_KEYWORDS.$url;
		}
		//page keywords/desc/title/ga
		$this->_addPageHeadData();
	}

	/*
	 * get page head data & save to $this->_view_data
	 * vary when different page loaded
	 */
	protected function _addPageHeadData(){
		$current_page = strtolower(get_class($this));
		$language_id = $this->m_app->currentLanguageId();

		if($current_page == 'home'){
			$this->_view_data['head']['ga_ecomm_pagetype'] = "'home'";
		}elseif($current_page == 'page_not_found'){
			$this->_view_data['head']['keywords'] = 'Error Page Not Found';
			$this->_view_data['head']['description'] = 'The page you requested was not found or its name had changed, this page is unavailable temporarily.';
			$this->_view_data['head']['title'] = 'Error Page Not Found.';
			$this->_view_data['head']['ga_ecomm_pagetype'] = "'other'";
		}

		//default
		$config = false;
		if(!isset($this->_view_data['head']['keywords'])){
			if($config === false) $config = $this->m_app->getAllConfig();
			if(!empty($config['shop_keywords_'.$language_id])){
				$this->_view_data['head']['keywords'] = htmlspecialchars($config['shop_keywords_'.$language_id]);
			}elseif(!empty($config['shop_keywords'])){
				$this->_view_data['head']['keywords'] = htmlspecialchars($config['shop_keywords']);
			}else{
				$this->_view_data['head']['keywords'] = '';
			}
		}
		if(!isset($this->_view_data['head']['description'])){
			if($config === false) $config = $this->m_app->getAllConfig();
			if(!empty($config['shop_desc_'.$language_id])){
				$this->_view_data['head']['description'] = htmlspecialchars($config['shop_desc_'.$language_id]);
			}elseif(!empty($config['shop_desc'])){
				$this->_view_data['head']['description'] = htmlspecialchars($config['shop_desc']);
			}else{
				$this->_view_data['head']['description'] = '';
			}
		}
		if(!isset($this->_view_data['head']['title'])){
			if($config === false) $config = $this->m_app->getAllConfig();
			if(!empty($config['shop_title_'.$language_id])){
				$this->_view_data['head']['title'] = htmlspecialchars($config['shop_title_'.$language_id]);
			}elseif(!empty($config['shop_title'])){
				$this->_view_data['head']['title'] = htmlspecialchars($config['shop_title']);
			}else{
				$this->_view_data['head']['title'] = '';
			}
		}

		if(!isset($this->_view_data['head']['ga_ecomm_pagetype'])){
			$this->_view_data['head']['ga_ecomm_pagetype'] = "'other'";
		}
	}

	/*
	 * get page header data & save to $this->_view_data
	 */
	protected function _addHeaderData(){
		$language_id = $this->m_app->currentLanguageId();

		//language list
		global $language_list;
		global $lang_basic_url;
		foreach($language_list as $key => $record){
			$language_list[$key]['url'] = rtrim(ArrayHelper::id2name($record['code'],$lang_basic_url,BASIC_URL),'/');
		}
		$this->_view_data['header']['language_list'] = $language_list;
		$this->_view_data['header']['current_language_title'] = $language_list[$language_id]['title'];

		//currency_list
		$this->_view_data['header']['currency_list'] = $GLOBALS['currency_list'];

		//image ad list
		$this->load->model('Imageadmodel','m_imagead');
		$this->_view_data['header']['image_ad'] = $this->m_imagead->getImageAdList(14,$language_id);

		//category tree
		$this->CategoryModel = new CategoryModel();
		$this->Categoryv2Model = new Categoryv2Model();;
		$list = $this->Categoryv2Model->getShownCategory($language_id);
		$list = spreadArray($list,'p_id');
		$list = $this->_buildList($list);
		$this->_view_data['header']['category_list'] = $list;

		//keyword recommend
		$this->KeywordrecommendModel = new KeywordrecommendModel();
		$length_limit = 72;
		$keyword_recommend_list = $this->KeywordrecommendModel->getKeywordRecommendList($language_id);
		foreach($keyword_recommend_list as $key => $record){
			$keyword = trim($record['keyword']);
			$keyword_recommend_list[$key]['url'] = eb_gen_url('search').'?keywords='.urlencode($keyword);
			$keyword_recommend_list[$key]['length'] = strlen($keyword);
			$keyword_recommend_list[$key]['keyword'] = $keyword;
		}
		sortArray($keyword_recommend_list,'length');
		$total_lenth = 0;
		$keyword_recommend_list_buffer = array();
		foreach($keyword_recommend_list as $key => $record){
			$total_lenth += $record['length']+1;
			if($total_lenth <= $length_limit){
				$keyword_recommend_list_buffer[$record['keyword']] = $record;
			}else{
				break;
			}
		}
		$this->_view_data['header']['keyword_recommend_list'] = array_values($keyword_recommend_list_buffer);

		//cart
		$this->_addCartInfo();
	}

	/*
	 * get page footer data & save to $this->_view_data
	 */
	protected function _addFooterData(){
		$language_id = $this->m_app->currentLanguageId();
		$language_code = $this->m_app->currentLanguageCode();

		//a to z
		$this->KeywordrecommendModel = new KeywordrecommendModel();
		$this->_view_data['footer']['atoz_list'] = $this->KeywordrecommendModel->getAtozList($language_code);

		//tag
		$this->load->model('Tagmodel','m_tag');
		$tag_list = $this->m_tag->getTagList();
		$tag_buffer = array();
		foreach($tag_list as $record){
			$word = explode(',',$record['tag_words']);
			$word = ArrayHelper::id2name($language_id-1,$word,'');
			if($word != ''){
				if($record['tag_url'] == ''){
					$record['tag_url'] = eb_gen_url('search-keywords-'.eb_substr($word,20,false).'.html');
				}
				$tag_buffer[] = array(
					'tag_word' => eb_substr($word,30,false),
					'url' => $record['tag_url'],
					'goods_id' => $record['goods_id'],
				);
				if(count($tag_buffer)>30) break;
			}
		}
		$this->_view_data['footer']['tag_list'] = $tag_buffer;

		//common language code
		$this->_view_data['footer']['common_language_code'] = $GLOBALS['language_list'][$language_id]['common_code'];
	}

	protected function _buildCategoryTreeIndex($category_tree){
		$cate_level_1_height = 2;
		$cate_level_2_height = 2;
		$min_row_height = floor(count($category_tree)*$cate_level_1_height)-6;
		$show_list_level_1 = array();
		foreach($category_tree as $cate_level_1){
			//get row height
			$row_height = 0;
			foreach($cate_level_1['children'] as $cate_level_2){
				$row_height += $cate_level_2_height + count($cate_level_2['children']);
			}
			$row_height = floor(sqrt($row_height));
			$row_height = max($row_height,$min_row_height);

			//get show list
			$show_list_level_2 = array();
			$show_list_level_3 = array();
			$current_height = 0;
			foreach($cate_level_1['children'] as $cate_level_2){
				$total_height = $current_height + $cate_level_2_height + count($cate_level_2['children']);
				if($total_height < $row_height){
					//left enough height for all level3 cate
					$current_height += $cate_level_2_height + count($cate_level_2['children']);
					$show_list_level_3[] = $cate_level_2['cat_id'];
					$show_list_level_3 = array_merge($show_list_level_3,extractColumn($cate_level_2['children'],'cat_id'));
				}else{
					$total_height = $current_height + $cate_level_2_height + floor(count($cate_level_2['children'])/2);
					if($total_height < $row_height){
						//left enough height for half level3 cate
						$current_height += $cate_level_2_height;
						$show_list_level_3[] = $cate_level_2['cat_id'];
						foreach($cate_level_2['children'] as $cate_level_3){
							if($current_height + 1 > $row_height){
								//current column filled,save into $show_list_level_2 & start new column
								$show_list_level_2[] = $show_list_level_3;
								$show_list_level_3 = array();
								$current_height = 0;
							}
							$current_height++;
							$show_list_level_3[] = $cate_level_3['cat_id'];
						}
					}else{
						//whole cate for a new column
						//save last column
						$show_list_level_2[] = $show_list_level_3;
						$show_list_level_3 = array();
						$current_height = 0;

						$total_height = $current_height + $cate_level_2_height + count($cate_level_2['children']);
						if($total_height < $row_height){
							//left enough height for all level3 cate
							$current_height += $cate_level_2_height + count($cate_level_2['children']);
							$show_list_level_3[] = $cate_level_2['cat_id'];
							$show_list_level_3 = array_merge($show_list_level_3,extractColumn($cate_level_2['children'],'cat_id'));
						}else{
							$current_height += $cate_level_2_height;
							$show_list_level_3[] = $cate_level_2['cat_id'];
							foreach($cate_level_2['children'] as $cate_level_3){
								if($current_height + 1 > $row_height){
									//current column filled,save into $show_list_level_2 & start new column
									$show_list_level_2[] = $show_list_level_3;
									$show_list_level_3 = array();
									$current_height = 0;
								}
								$current_height++;
								$show_list_level_3[] = $cate_level_3['cat_id'];
							}
						}
					}
				}
			}
			$show_list_level_2[] = $show_list_level_3;
			$show_list_level_1[$cate_level_1['cat_id']] = $show_list_level_2;
		}

		return $show_list_level_1;
	}

	/*
	 * check user login & save user info into session
	 */
	protected function _addUserInfo(){
		$user = false;
		if(!$this->m_app->checkUserLogin()){
			//auto login
			$user_id = OtherHelper::get_cookie('ECS[user_id]');
			$user_name = OtherHelper::get_cookie('ECS[user_name]');
			$password = OtherHelper::get_cookie('ECS[password]');
			$user_name = 'grace';
			if($user_id !== false && $user_name !== false && $password !== false){
				$userModelObj = Users::getInstanceObj();
				if( OtherHelper::is_email($user_name)){
					$user = $userModelObj->getUserByEmail($user_name);
				}else{
					$user = $userModelObj->getUserByName($user_name);
				}

				if(!empty($user) && $user['user_id'] == $user_id && $userModelObj->validatePassword($user['password'],$password)){
					$this->session->set('user_id',$user['user_id']);
					$this->session->set('user_name',$user['user_name']);
					$this->session->set('email',$user['email']);
					$this->UserModel->updateUserLoginInfo($user['user_id']);
				}else{
					OtherHelper::unset_cookie('ECS[user_id]');
					OtherHelper::unset_cookie('ECS[user_name]');
					OtherHelper::unset_cookie('ECS[password]');
				}
			}
		}

		if($this->m_app->checkUserLogin()){
			$user = array(
				'user_id' => $this->m_app->getCurrentUserId(),
				'user_name' => $this->m_app->getCurrentUserName(),
				'email' => $this->m_app->getCurrentUserEmail(),
			);
		}
		$this->_view_data['user'] = $user;
	}

	protected function _addMessageInfo($key){
		$this->_view_data['message_flg'] = $this->session->get($key.'_flag');
		$this->_view_data['message'] = $this->session->get($key);
		$this->session->delete($key.'_flag');
		$this->session->delete($key);
	}

	protected function _addCartInfo(){
		if(isset($this->_view_data['flg_header_cart_disable']) && $this->_view_data['flg_header_cart_disable'] === true){
			return;
		}

		if(is_spider()){
			return;
		}
		$this->_view_data['header_cart'] = $this->_getUserCartInfo();
	}

	protected function _setWebgainsCookie() {

		$utm_medium =  Yii::$app->request->get('utm_medium'); //平台
		$utm_source =  Yii::$app->request->get('utm_source'); //所属项目
		$source =  Yii::$app->request->get('source'); //获取佣金平台
		$utm_campaign =  Yii::$app->request->get('utm_campaign'); //网盟唯一标示id

		if ( $utm_medium === false || $utm_source === false ) {
			return false;
		}

		$utm_campaign = intval($utm_campaign);

		//通过供应商决定cookie失效时间
		global $c_webgains_sourcetotime;
		$expiresTime = ArrayHelper::id2name($utm_campaign, $c_webgains_sourcetotime, 45*86400);

		//设置网盟cookie
		OtherHelper::set_affiliate_cookie($source, $utm_medium, $utm_source , $utm_campaign , $expiresTime);
	}

	//检测移动版
	protected function _is_mobile_device( $is_access_pc = FALSE ) {
		$is_mobile_device = FALSE ;
		if( defined( 'CHECK_MOBILE_DEVICE' ) && CHECK_MOBILE_DEVICE === TRUE ){
			if( $is_access_pc ){
				set_cookie('mobile_is_access_pc', $is_access_pc, 864000 );
			}else{
				$is_mobile_device_get = $this->input->cookie('mobile_is_access_pc' );
				if( ! $is_mobile_device_get ){
					$http_user_agent = empty($_SERVER['HTTP_USER_AGENT']) ? '' : strtolower($_SERVER['HTTP_USER_AGENT']) ;
					$is_mobile_device = ( strpos ( $http_user_agent , 'pad') !== FALSE ) ? FALSE : strpos ( $http_user_agent , 'obile');
				}
			}
		}
		return  $is_mobile_device ? TRUE : FALSE ;
	}

	/**
	 * Get the cart info of user.
	 * @return array
	 * @author Albie
	 */
	protected function _getUserCartInfo() {
		$languageCode = $this->m_app->currentLanguageCode();
		$currency = $this->m_app->currentCurrency();
		$user_cart_all = $this->session->get('user_cart_all');

		if ($user_cart_all && isset($user_cart_all[$languageCode . '_' . $currency])) {
			$cart = $user_cart_all[$languageCode . '_' . $currency];
		} else {
			$cartObj = CartModel::getInstanceObj();
			$cartObj->loadCart();
			$cart = $cartObj->getCart();//加载购物车信息
			$user_cart_all[$languageCode . '_' . $currency] = $cart;
			$this->session->set('user_cart_all', $user_cart_all);
		}
		return $cart;
	}

	/**
	 * 分类树
	 * @return [type] [description]
	 */
	protected function _getMapCategory() {
		$categoryModelObj =  Category::getInstanceObj();
		$categorydescModelObj = Categorydesc::getInstanceObj();
		$categoryLevel1 = array();
		//获取所有的分类
		$fieldArray = array('id', 'p_id','name', 'image', 'path', 'url', 'nav_image', 'nav_image_bg', 'nav_url', 'status', 'product_active_num');
		$whereArray = array( //注意这里的写法和以往不同了
			'status' => '=1',
			'product_active_num' => '>0',
			'url' => '!=\'\'',
			'id'=> ' >15000',
		);
		$orderBy = ['sort' =>  SORT_DESC];//注意值是写成常量了
		$categoryMapArray = $categoryModelObj->getCategoryInfo( $fieldArray, $whereArray, $orderBy, $groupBy = '', array() );

		//取出分类的多语言
		$fieldArray = array('category_id', 'name');
		$whereArray = array(
			'language_id' => '='. $this->m_app->currentLanguageId() ,
		);
		$categoryNameArray = $categorydescModelObj->getCategoryDescInfo($fieldArray, $whereArray, array(), $groupBy = '', array());
		$categoryNameArray = ArrayHelper::reindexArray( $categoryNameArray , 'category_id' );

		//将分类的多语言加入分类信息数组中
		if(count($categoryMapArray) > 0) {
			foreach ($categoryMapArray as $key => $value) {
				if(isset($categoryNameArray[$value['id']]['name']) && !empty($categoryNameArray[$value['id']]['name'])) {
					$categoryMapArray[$key]['name'] = htmlspecialchars($categoryNameArray[$value['id']]['name'],ENT_QUOTES);
				}
			}
		}

		//循环处理分类的树级
		$categoryMapArray = ArrayHelper::reindexArray( $categoryMapArray , 'id' );

		if(count($categoryMapArray) > 0) {
			//处理一级分类
			foreach ($categoryMapArray as $key => $value) {
				if(empty($value['product_active_num']) || $value['status'] != 1 || empty($value['name']) || empty($value['url']) || empty($value['path']) ) {
					unset($categoryMapArray[$key]);continue;
				}

				$idPathArray = explode('/', $value['path'] );
				$countTmp = count( $idPathArray ) ;
				if( !isset( $categoryLevel1[$idPathArray[0]]['subCount'] ) ){
					$categoryLevel1[$idPathArray[0]]['subCount'] = 0 ;
				}else{
					//只是取出二级分类和三级分类的总数
					if( $countTmp !== 1 && $countTmp != 4){
						$categoryLevel1[$idPathArray[0]]['subCount'] ++ ;
					}
				}

				if( $countTmp === 1){//一级分类
					if( isset($idPathArray[0]) && isset($categoryMapArray[$idPathArray[0]] ) && !isset($categoryLevel1[$idPathArray[0]]['id'] ) ) {
						if( isset( $categoryLevel1[$idPathArray[0]]['subCategory'] ) ){
							$categoryMapArray[$idPathArray[0]]['subCategory'] = $categoryLevel1[$idPathArray[0]]['subCategory'] ;
						}
						//修改一级分类下面子分类的个数
						if( isset( $categoryLevel1[$idPathArray[0]]['subCount'] ) ){
							$categoryMapArray[$idPathArray[0]]['subCount'] =  $categoryLevel1[$idPathArray[0]]['subCount'] ;
						}

						if( isset( $categoryLevel1[$idPathArray[0]] ) ){
							//unset 掉 否则影响排序
							unset( $categoryLevel1[$idPathArray[0]]);
						}

						$categoryLevel1[$idPathArray[0]] = $categoryMapArray[$idPathArray[0]];
					}
				} elseif ( $countTmp === 2 ){//二级分类
					if(isset($idPathArray[1]) && isset($categoryMapArray[$idPathArray[1]] ) && !isset( $categoryLevel1[$idPathArray[0]]['subCategory'][$idPathArray[1]]['id'] ) ) {
						if( isset( $categoryLevel1[$idPathArray[0]]['subCategory'][$idPathArray[1]]['subCategory'] ) ){
							$categoryMapArray[$idPathArray[1]]['subCategory'] = $categoryLevel1[$idPathArray[0]]['subCategory'][$idPathArray[1]]['subCategory'] ;
							unset( $categoryLevel1[$idPathArray[0]]['subCategory'][$idPathArray[1]] );
						}
						$categoryLevel1[$idPathArray[0]]['subCategory'][$idPathArray[1]] = $categoryMapArray[ $idPathArray[1] ] ;
					}
				}elseif ( $countTmp === 3 ){//三级分类
					if( isset($idPathArray[2]) && isset($categoryMapArray[$idPathArray[2]]) && !isset( $categoryLevel1[$idPathArray[0]]['subCategory'][$idPathArray[1]]['subCategory'][$idPathArray[2]]['id'] ) ) {
						if( isset( $categoryLevel1[$idPathArray[0]]['subCategory'][$idPathArray[1]]['subCategory'][$idPathArray[2]]['subCategory'] ) ){
							$categoryMapArray[$idPathArray[2]]['subCategory'] = $categoryLevel1[$idPathArray[0]]['subCategory'][$idPathArray[1]]['subCategory'][$idPathArray[2]]['subCategory'] ;
							unset( $categoryLevel1[$idPathArray[0]]['subCategory'][$idPathArray[1]]['subCategory'][$idPathArray[2]] );
						}
						$categoryLevel1[$idPathArray[0]]['subCategory'][$idPathArray[1]]['subCategory'][$idPathArray[2]] = $categoryMapArray[$idPathArray[2]];
					}
				}elseif( $countTmp === 4 ){//四级分类
					if( isset( $idPathArray[3]) && isset($categoryMapArray[$idPathArray[3]]) && !isset( $categoryLevel1[$idPathArray[0]]['subCategory'][$idPathArray[1]]['subCategory'][$idPathArray[2]]['subCategory'][$idPathArray[3]]['id'] )) {
						if( isset( $categoryLevel1[$idPathArray[0]]['subCategory'][$idPathArray[1]]['subCategory'][$idPathArray[2]]['subCategory'][$idPathArray[3]]['subCategory'] ) ){
							$categoryMapArray[$idPathArray[3]]['subCategory'] = $categoryLevel1[$idPathArray[0]]['subCategory'][$idPathArray[1]]['subCategory'][$idPathArray[2]]['subCategory'][$idPathArray[3]]['subCategory'] ;
							unset( $categoryLevel1[$idPathArray[0]]['subCategory'][$idPathArray[1]]['subCategory'][$idPathArray[2]]['subCategory'][$idPathArray[3]] );
						}
						$categoryLevel1[$idPathArray[0]]['subCategory'][$idPathArray[1]]['subCategory'][$idPathArray[2]]['subCategory'][$idPathArray[3]] = $categoryMapArray[$idPathArray[3]];
					}
				}
			}
		}

		return $categoryLevel1;
	}

	/**
	 * 循环分类的数组
	 * @param  array $data 分类信息数组
	 */
	protected function _buildList($data) {
		if(!isset($data) || empty($data)) {
			return array();
		}
		$i = 0;
		$res = $data[0];
		while($i < count($res)){
			if(isset($data[$res[$i]['id']])){
				array_splice($res,$i+1,0,$data[$res[$i]['id']]);
			}
			$i++;
		}

		return $res;
	}

	/**
	 * ref 自动校验
	 */
	protected function _loginCheckrefer(){
		$referResult = HelpOther::checkrefer();
		if( $referResult === FALSE ){
			redirect( eb_gen_url(), 'location', 301 );
		}
	}

	//hzf
	public function checkRegisterTimesInOneHour(){

		$ip = $this->input->ip_address();
		$ip = str_replace('.', '_', $ip);

		$duration = 0;
		$showCaptchaFlag = false;
		$registerTimes = array();
		if(!empty($ip)){
			$registerTimes = $this->memcache->get( $this->mem_key_user_register_time_info , $ip );
		}
		if(!empty($registerTimes)){
			$duration = $registerTimes['secondRegisterTime'] - $registerTimes['firstRegisterTime'];

			if( 0 < $duration  && $duration <=3600){
				$showCaptchaFlag = true;
			}
		}

		return $showCaptchaFlag;
	}

	//针对同一IP，在1小时之内成功注册2次，再次进入注册页面时，需要填写验证码才可提交注册内容。
	public function setRegisterTimes(){
		$ip = $this->input->ip_address();
		$ip = str_replace('.', '_', $ip);
		$expire = 3600;
		$prevDuration = 0;
		$registerTimes = array('secondRegisterTime' =>  $_SERVER['REQUEST_TIME']);
		$times = $this->memcache->get( $this->mem_key_user_register_time_info, $ip );
		//10.20   10.50   11.15  11.21  11.59
		//first  second
		//       first    second   ...
		if(!empty($times) && isset($times['secondRegisterTime'])){
			$prevDuration = $times['secondRegisterTime'] -  $times['firstRegisterTime'];
			$registerTimes['firstRegisterTime'] = $times['secondRegisterTime'];
			$expire = $expire - ($registerTimes['secondRegisterTime']   - $registerTimes['firstRegisterTime'] );
		}else{
			$registerTimes['firstRegisterTime'] = $_SERVER['REQUEST_TIME'];
		}

		if( 0 == $prevDuration ){
			$this->memcache->set( $this->mem_key_user_register_time_info, $registerTimes , $ip , $expire );
		}
	}

	/**
	 * ga统计  购物流程
	 * @param type $cart
	 * @return string
	 */
	protected function dataLayerPushShopping( $cart = array() ){
		if ( empty ( $cart ) ){
			$this->_view_data['dataLayerProducts'] = '';
			return ;
		}

		$products = array();

		if( ! empty( $cart['goodsListCommon'] ) ) {
			foreach($cart['goodsListCommon'] as $record){ 
				$products[] = array(
					'id' => $record['pid'],
					'sku' =>  $record['sku'],
					'price' => $record['finalPrice'],
					'quantity'=> $record['qty'], //@todo 这数字可能会变的，最后怎么得到用户选择的个数
				);	
			}
		}else if( ! empty( $cart['goodsListBind'] ) ){ 
			foreach($cart['goodsListBind'] as $goodsListbindBlock){
				foreach($goodsListbindBlock as $record){ 
					$products[] = array(
						'id' => $record['pid'],
						'sku' =>  $record['sku'],
						'price' => $record['finalPrice'],
						'quantity'=> $record['qty'], //@todo 这数字可能会变的，最后怎么得到用户选择的个数
					);	
				}
			}
		}

		$this->_view_data['dataLayerProducts'] = json_encode( $products ); 
	}

	/**
	 * 数据结构可能不同，则同类本身实现
	 * 相应页面刷新时，GA数据统计
	 */
	protected function dataLayerPushImpressions(  $productList = array() , $list = ''  ){
		if ( empty ( $productList  ) ){
		   $this->_view_data['dataLayerProducts'] = '';
		   return ;
		}
		$position = 1;
		$products = array();
		foreach( $productList as $sku){
		   $products[] = array(
			   'id' => $sku['id'],
			   'price'=> $sku['final_price'],  //美元价格
			   'list' => $list,
			   'position' => $position++ 
		   );				
	   }

	   $this->_view_data['dataLayerProducts'] = json_encode( $products ); 
	}
}

/* End of file EB_controller.php */
/* Location: ./application/controllers/default/common/EB_controller.php */

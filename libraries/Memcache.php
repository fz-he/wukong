<?php

namespace app\libraries;

use Yii;

class Memcache {

	protected static $_instance = NULL;
	protected $_memcache ;
	protected $_banned_key_pattern = array();
	static $allKeyTree = array(
		// Appmodel :: getConfigCurrency($key = 'USD',$default = '')
		'idx_all_config_currency'=>array('type'=>'memcache_page','expire'=> 7200 ),
		'idx_pro_%s_%s'=>array('type'=>'memcache_web','expire'=>1800),//idx_pro_{$proId}_{$languageId}
		'idx_category_info_%s_%s'=>array('type'=>'memcache_page','expire'=>36000),//idx_category_info_{$category_id}_{$language_id}
		'idx_get_special_goods_recommend_tab_title_%s'=>array('type'=>'memcache_page','expire'=>36000),//idx_get_special_goods_recommend_tab_title_{$languageId}
		'idx_get_recommend_goods_special_list_%s'=>array('type'=>'memcache_page','expire'=>3600),//idx_get_recommend_goods_special_list_{$languageId}
		'idx_get_new_goods_recommend_tab_title_%s'=>array('type'=>'memcache_page','expire'=>36000),//idx_get_new_goods_recommend_tab_title_{$languageId}
		'idx_get_recommend_goods_new_list_%s'=>array('type'=>'memcache_page','expire'=>3600),//idx_get_recommend_goods_new_list_{$languageId}
		'idx_goods_%s_%s'=>array('type'=>'memcache_web','expire'=>3600),//idx_goods_{$goods_id}_{$language_id}
		'idx_get_goods_gallery_list_%s'=>array('type'=>'memcache_page','expire'=>1800),//idx_get_goods_gallery_list_{$proId}
		'idx_get_attribute_and_value_by_goods_%s_%s'=>array('type'=>'memcache_page','expire'=>3600),//idx_get_attribute_and_value_by_goods_{$proId}_{$languageId}
		'idx_get_child_goods_and_attribute_%s_%s'=>array('type'=>'memcache_page','expire'=>3600),//idx_get_child_goods_and_attribute_{$proId}_{$languageId}
		'idx_get_active_goods_by_category_%s_%s_%s_%s'=>array('type'=>'memcache_page','expire'=>3600),//idx_get_active_goods_by_category_{$category_id}_{$exception_category_id}_{$languageId}_{$limit}
		'idx_get_category_best_sale_active_goods_%s_%s_%s'=>array('type'=>'memcache_page','expire'=>3600),//idx_get_category_best_sale_active_goods_{$category_id}_{$languageId}_{$limit}

		// GoodsModel :: getCategoryGoodsIds($category_id,$languageId,$param = array(), $start, $pagesize)
		'idx_get_category_pids_%s_%s_%s_%s_%s_%s_%s'=>array('type'=>'memcache_page','expire'=>7200),
		//Categoryv2Model :: getNarrowSearchByCategoryId( $categoryId , $langId=1 )
		'idx_category_ns_catid_%s_%s' => array( 'type'=>'memcache_page', 'expire'=>7200 ),
		//Categoryv2Model :: getCategoryNarrowSearchCountDisplayAttr($categoryId)
		'category_getCategoryNarrowSearchCountDisplayAttr_%s' => array( 'type'=>'memcache_page', 'expire'=>7200 ),
		//Categoryv2Model::_getNarrowSearchPidDbByAttrValueIds
		'narrowSearchPidDbByAttrValueIds_%s' => array( 'type'=>'memcache_page', 'expire'=>7200 ),

		'idx_get_category_goods_price_boundary_%s_%s'=>array('type'=>'memcache_page','expire'=>1800),//idx_get_category_goods_price_boundary_{$category_id}_{$languageId}
		'idx_good_attribute_info_%s_%s'=>array('type'=>'memcache_page','expire'=>14400),//idx_good_attribute_info_{$skus_mckey}_{$lang_id}
		'idx_goods_sku_%s_%s'=>array('type'=>'memcache_page','expire'=>3600),//idx_goods_sku_{$sku}_{$languageId}
		'idx_good_recent_promotion_goods_%s'=>array('type'=>'memcache_web','expire'=>36000),//idx_good_recent_promotion_goods_{$count}
		'idx_get_act_promote_goods_list'=>array('type'=>'memcache_web','expire'=>3600),//idx_get_act_promote_goods_list
		'idx_get_all_country'=>array('type'=>'memcache_page','expire'=>36000),//idx_get_all_country
		'idx_get_all_region'=>array('type'=>'memcache_page','expire'=>36000),//idx_get_all_region
		'idx_load_shop_config'=>array('type'=>'memcache_page','expire'=>72000),//idx_load_shop_config
		'category_redirect_301'=>array('type'=>'memcache_page','expire'=>0),//category_redirect_301
		'category_redirect_302'=>array('type'=>'memcache_page','expire'=>0),//category_redirect_302
		'idx_get_ciku_list_%s_%s_%s_%s'=>array('type'=>'memcache_page','expire'=>36000),//'idx_get_ciku_list_'.$key.'_'.$language_id.'_'.$start.'_'.$limit
		'idx_get_shown_category_%s'=>array('type'=>'memcache_page','expire'=>3600),//idx_get_shown_category_{$language_id}_" . (int)$is_recommend
		'idx_category_template_%s_%s'=>array('type'=>'memcache_page','expire'=>36000),//idx_category_template_{$category_id}_{$language_id}
		'idx_get_top_category_banner_%s'=>array('type'=>'memcache_page','expire'=>7200),//idx_get_top_category_banner_{$category_id}
		'idx_get_left_category_ad'=>array('type'=>'memcache_page','expire'=>7200),//idx_get_left_category_ad
		'get_rediect_Product_id_array_%s'=>array('type'=>'memcache_page','expire'=>7200),//goods _redirectProduct($productId)
		'idx_get_category_list_by_category_%s_%s'=>array('type'=>'memcache_page','expire'=>7200),//idx_get_category_list_by_category_{$category_id}_{$language_id}
		'idx_buy_get_ciku_desc_%s_%s_%s'=>array('type'=>'memcache_page','expire'=>72000),//idx_buy_get_ciku_desc_{$cat_id}_{$keywords}_{$currentLanguageId}
		'idx_get_image_ad_list_%s_%s'=>array('type'=>'memcache_page','expire'=>36000),//idx_get_image_ad_list_{$image_ad_id}_{$language_id}
		'idx_get_keyword_recommend_list_%s'=>array('type'=>'memcache_page','expire'=>36000),//idx_get_keyword_recommend_list_{$language_id}
		'idx_get_category_keyword_recommend_list_%s'=>array('type'=>'memcache_page','expire'=>36000),//idx_get_category_keyword_recommend_list_{$language_id}
		'idx_get_category_keywords_%s_%s_%s'=>array('type'=>'memcache_page','expire'=>3600),//idx_get_category_keywords_{$category_id_path}_{$language_id}_{$limit}
		'idx_get_goods_review_list_%s_%s_%s'=>array('type'=>'memcache_page','expire'=>1800),//idx_get_goods_review_list_{$goods_id}_{$language_code}_{$page}
		'idx_get_tag_list_%s_%s'=>array('type'=>'memcache_page','expire'=>3600),//idx_get_tag_list_{$goods_id}_{$user_id}
		//ProductModel :: getPidBySku($sku)
		'product_getpidbysku_%s'=>array('type'=>'memcache_page','expire'=>3600),

		// Categoryv2Model :: getCategoryInfo( $fieldArray = array(), $whereArray = array(), $orderBy = array(), $groupBy = '', $limit = array() )
		'idx_categorymodel_getcategoryinfo_%s'=>array('type'=>'memcache_page','expire'=>36000),
		// Categoryv2Model :: getSubCategoryIdsById($categoryId)
		'idx_categorymodel_getsubcategoryidsbyid_%s'=>array('type'=>'memcache_page','expire'=>36000),
		// CategorydescModel :: getCategoryDescInfo( $fieldArray = array(), $whereArray = array(), $orderBy = array(), $groupBy = '', $limit = array() )
		'idx_categorydescmodel_getcategorydescinfo_%s'=>array('type'=>'memcache_page','expire'=>36000),
		// CategorydescModel :: getParentCategoryInfo( $categoryId, $language_id )
		'idx_categorymodel_getparentcategoryinfo_%s'=>array('type'=>'memcache_page','expire'=>36000),
		// CategorydescModel :: getParentCategoryInfo( $categoryId, $language_id )
		'idx_categorymodel_getparentcategoryinfo_%s_%s'=>array('type'=>'memcache_page','expire'=>36000),
		// CategorydescModel :: getParentCategoryInfo( $categoryId, $language_id )
		'idx_categorymodel_getparentallcategoryinfo_%s'=>array('type'=>'memcache_page','expire'=>36000),
		// CategorydescModel :: getParentCategoryInfo( $categoryId, $language_id )
		'idx_categorymodel_getparentallcategoryinfo_%s_%s'=>array('type'=>'memcache_page','expire'=>36000),
		//Categoryv2Model::getCategoryDisplayByCategoryId( $categoryId )
		'idx_category_display_type_%s'=>array('type'=>'memcache_page','expire'=>36000),
		//CategorydescModel:: getCategoryRecommendGoodsIds( $categoryId, $is_sonCate )
		'idx_categorymodel_getcategoryrecommendgoodsids_%s'=>array('type'=>'memcache_web','expire'=>7200 ),
		'pro_%s_%s'=>array('type'=>'memcache_web','expire'=>3600),//pro_{$productId}_{$languageId}
		//Categoryv2Model:: getLeftCategoryAd()
		'idx_category_getleftcategoryad' => array( 'type'=>'memcache_page', 'expire'=>7200 ),
		//Categoryv2Model :: getTopCategoryBanner ( $categoryLd )
		'idx_category_gettopcategorybanner_%s' => array( 'type'=>'memcache_page', 'expire'=>7200 ),
		'proGalleryList_%s' => array( 'type'=>'memcache_web', 'expire'=>7200 ),//proGalleryList_{$productId}

		//Categoryv2Model :: getCategoryListByCategory($categoryId, $languageId)
		'idx_catgegory_getcategorylistbycategory_%s_%s' => array( 'type'=>'memcache_page', 'expire'=>7200 ),

		//CategoryredirectModel :: getRedirectInfoByCategoryId($categoryId = 0)
		'get_redirect_category_info_by_category_id_%s' => array( 'type'=>'memcache_page', 'expire'=>3600 ),
		//ProductModel:: getRecommendGoodsSpecialList($languageId)
		'proDiscountInfo%s' => array( 'type'=>'memcache_web', 'expire'=>1800 ),//proDiscountInfo{$product_id} 产品的打折信息缓存key
		'discountInfo%s' => array( 'type'=>'memcache_web', 'expire'=>1800 ),//discountInfo{$discount_id} 根据打折id缓存的打折信息key
		'parentCatIdByPid_%s' => array( 'type'=>'memcache_page', 'expire'=>86400 ),//getParentCategoryIdByPid{$pids} 根据商品ID获得父类别ID
		'proSecKillInfo%s' => array( 'type'=>'memcache_web', 'expire'=>1800 ),//proSecKillInfo{$product_id} 产品的秒杀信息缓存key
		'proGpInfo%s' => array( 'type'=>'memcache_page', 'expire'=>1800 ),//proGpInfo{$product_id} 产品的团购信息缓存key
		'proInfo%s%s' => array( 'type'=>'memcache_web', 'expire'=>1800 ),//proInfo{$product_id}{$$languageId} 产品信息的memcache缓存key
		'proCatInfo%s%s' => array( 'type'=>'memcache_page', 'expire'=>1800 ),//proCatInfo{$category_id}{$$languageId} 产品分类信息的memcache缓存key
		'proSkuInfo%s' => array( 'type'=>'memcache_page', 'expire'=>1800 ),//产品的子sku信息的memcache缓存key
		'proSkuAttrInfo%s%s' => array( 'type'=>'memcache_page', 'expire'=>1800 ),//proSkuAttrInfo{$product_id}{$languageId} 产品的子sku属性信息的memcache缓存key
		'proAttrTitle%s%s' => array( 'type'=>'memcache_page', 'expire'=>1800 ),//proAttrTitle{$attr_id}{$languageId} 产品属性多语言title的mc缓存key
		'proAttrInfo%s%s' => array( 'type'=>'memcache_page', 'expire'=>1800 ),//proAttrInfo{$product_id}{$languageId} 产品narrow search属性的mc缓存key
		'proReview%s%s%s' => array( 'type'=>'memcache_page', 'expire'=>1800 ),//proReview{$product_id}{$languageId}{$page} 产品评论信息（多语言、分页）
		'proBtToge%s%s' => array( 'type'=>'memcache_page', 'expire'=>1800 ),//proBtToge{$baseCatId}{$finalCatId} #bought together mc key.
		'proReco%s%s' => array( 'type'=>'memcache_page', 'expire'=>1800 ),//proReco{$cid}{$pid}#Related recommond products mc key.
		'proPics%s' => array( 'type'=>'memcache_page', 'expire'=>1800 ),//proPics{$pid} #Product pictrues mc key.
		'skuStock%s' => array( 'type'=>'memcache_web', 'expire'=>1800 ),//skuStock{$sku} #Sku stock mc key.
		'proActive%s' => array( 'type'=>'memcache_web', 'expire'=>1800 ),//proActive{$pid} #Product active status mc key.
		//PromoteModel::getAllPromoteBundleByType( $promoteBundleType =3 )
		'pro_all_bundleInfo_%s' => array( 'type'=>'memcache_web', 'expire'=>1800 ),
		'catTemp%s%s'=>array( 'type'=>'memcache_page', 'expire'=>1800 ),//catTemp{$catId}{$languageId}分类模板的memcache缓存key
		//PromoteModel::getAllFullReduction()
		'pro_all_full_reduction' => array( 'type'=>'memcache_web', 'expire'=>1800 ),
		//PromoteModel::getAllSecKillPro();
		'pro_all_seckill_pro' =>  array( 'type'=>'memcache_web', 'expire'=>1800 ),
		//ProductModel::getRecommendProBycatId();
		'get_recommend_pro_by_catid_%s' => array( 'type'=>'memcache_web', 'expire'=>3600 ),
		//ProductModel::getAllSeckillProByCatid()
		'get_special_pro_by_catid_%s' => array( 'type'=>'memcache_web', 'expire'=>1800 ),
		//ReviewModel::getRecentlyReviewList()
		'get_recently_review_list_%s_%s'=>array( 'type'=>'memcache_page','expire'=>1800 ),
		//Categoryv2Model::getSubCatIdbyCatId()
		'get_sub_catid_by_pcatid_%s'=>array('type'=>'memcache_page','expire'=>3600),
		//ProductModel::getActiveStockByPid()
		'get_product_stock_%s'=>array('type'=>'memcache_web','expire'=>1800),
		//ProductModel::getProductDescription()
		'get_prodesc_by_pid_%s'=>array('type'=>'memcache_web','expire'=>3600),
		//CategorymoduleModel::getCategoryModuleInfoByCategoryId()
		'get_cat_module_info_%s' => array('type'=>'memcache_page','expire'=>1800),
		//CategorymoduleModel::getCategoryModuleContentByCategoryId()
		'get_cat_module_content_%s' => array('type'=>'memcache_page','expire'=>1800),
		//CategorymoduleModel::getCategoryModuleidContent()
		'get_cat_moduleid_content_%s' => array('type'=>'memcache_page','expire'=>1800),
		//CategoryAdModel::getCategoryAdBatch()
		'get_cat_ad_batch_%s' => array('type'=>'memcache_page','expire'=>1800),
		//CategoryAdModel::getRootCategoryGoodsCount()
		'get_root_category_goods_count' => array('type'=>'memcache_page','expire'=>1800),
		//CategoryAdModel::getCategoryAd()
		'get_cat_ad_%s' => array('type'=>'memcache_page','expire'=>1800),
		//BannerModel::getAllSiteBanner(); 全站banner
		'banner_all_site' => array('type'=>'memcache_page','expire'=>3600 ),
		//BannerModel::getCategoryBannerById(); 分类通栏banner
		'banner_category_%s' => array('type'=>'memcache_page','expire'=>36000 ),
		//CategoryAdModel::getCategoryAllLeftAdBatch();
		'get_cat_left_ad_batch' => array('type'=>'memcache_page','expire'=>1800 ),
		//CategoryAdModel::getCategoryAllLeftAd();
		'get_cat_left_ad' => array('type'=>'memcache_page','expire'=>1800 ),
		//PromotionModel::getCurrentSpecialTopicList($date, $language = 1)
		'get_special_topic_list_%s_%s_%s_%s' => array('type'=>'memcache_page','expire'=>36000 ),
		//PromotionModel::getHistorySpecialTopicList($date, $language = 1, $start = 0)
		'get_history_special_topic_list_%s_%s_%s' => array('type'=>'memcache_page','expire'=>36000 ),
		//PromotionModel::getHistorySpecialTopicCount($date, $language = 1)
		'get_history_special_topic_count_%s_%s' => array('type'=>'memcache_page','expire'=>36000 ),
		//获取购物车缓存KEY 存贮到session 中  CartModel::_clearMemcacheByCart();
		'cart_uid_%s' => array('type'=>'memcache_session','expire'=>3600 ) ,
		'cart_session_%s' => array('type'=>'memcache_session','expire'=>3600 ) ,
		'proSizeChart%s' => array('type'=>'memcache_web','expire'=>1800 ),//产品尺码数据缓存
		//ProductModel::getProductCategorySub  此数据 实时性要求高 放到web中
		'productCategorySubByCatId_%s' => array('type'=>'memcache_web','expire'=>1800) ,
		//ProductModel::getSaleCategoryProduct  此数据 实时性要求高    放到web中
		'saleCategoryPidByCatId_%s' => array('type'=>'memcache_web','expire'=>1800) ,
		//ReviewModel::getProReviewCount
		'product_review_count_%s_%s' => array('type'=>'memcache_page','expire'=>7200) ,
		'proTwentyfourSeckillInfo%s' => array('type' => 'memcache_web', 'expire' => 1800 ) ,

		//EmailtemplateModel::getSystemEmailTemplateInfo
		'get_email_template_%s' => array('type' => 'memcache_web', 'expire' => 36000 ) ,

		//PromotionModel::getCurrentSpecialTopicList
		'get_current_special_topic_list_%s_%s' => array('type'=>'memcache_page','expire'=>36000 ) ,
		//PromotionModel::getHistorySpecialTopicList
		'get_history_special_topic_list_%s_%s_%s' => array('type'=>'memcache_page','expire'=>36000 ) ,
		//Categoryv2Model::getTwoCategoryIds
		'get_category_two_ids' => array( 'type'=>'memcache_page','expire'=>36000 ) ,

		'user_register_times_%s'=> array('type'=>'memcache_page','expire'=>3600 ) ,
		'captcha_code_session_%s' => array('type'=>'memcache_session','expire'=>3600 ) ,
		//CouponModel
		'idx_couponmodel_getcoupon_%s' => array('type' => 'memcache_page', 'expire' => 36000 ) ,
		'idx_couponmodel_checkcustomerusedcoupon_%s_%s' => array('type' => 'memcache_page', 'expire' => 36000 ) ,
		'idx_couponmodel_getcouponusedtimebyemail_%s_%s' => array('type' => 'memcache_page', 'expire' => 36000 ) ,
		'idx_couponmodel_count_coupon_%s' => array('type' => 'memcache_page', 'expire' => 36000 ) ,
		"idx_cartmodel_getgiftbyid_%s" => array('type' => 'memcache_web', 'expire' => 3600 ) ,

		'flasasale_%s' => array('type'=>'memcache_page','expire'=>36000 ) ,
		//AdModel::_getAdInfoDbById
		'ad_info_%s' => array( 'type'=>'memcache_page' , 'expire'=>36000 ) ,
		'get_point_list' => array( 'type' => 'memcache_page', 'expire'=>36000 ) ,
		//ProductModel::getEmailRecommendProductNew()
		'get_email_recommend_product_ids' => array( 'type'=>'memcache_page' , 'expire' => 86400 ) ,
		//BuykeywordModel::getBuyInfoByMd5() buy 页面关键词 缓存记录
		'buy_keyword_md5_%s_%s' => array( 'type'=>'memcache_page' , 'expire' => 864000 ),
		//PromotetemplatesModel
		'get_promotelist_%s' => array( 'type'=>'memcache_page' , 'expire' => 36000 ) ,
		'get_promote_banner_%s' => array( 'type'=>'memcache_page' , 'expire' => 36000 ) ,
		'get_promote_banner_image_%s_%s' => array( 'type'=>'memcache_page' , 'expire' => 36000 ) ,
		'get_promote_description_%s' => array( 'type'=>'memcache_page' , 'expire' => 36000 ) ,
		'get_promote_modules_group_%s' => array( 'type'=>'memcache_page' , 'expire' => 36000 ) ,
		'get_promote_modules_%s' => array( 'type'=>'memcache_page' , 'expire' => 36000 ) ,
		'get_promote_modules_images_%s' => array( 'type'=>'memcache_page' , 'expire' => 36000 ) ,
		'get_promote_sidebar_%s' => array( 'type'=>'memcache_page' , 'expire' => 36000 ) ,
		'get_promote_module_pids_%s' => array('type'=>'memcache_page', 'expire'=>36000 ) ,

	);

	public function __construct(){
		$this->_memcache = new \stdClass();
		/*Init the memcache obj.*/
		$params = Yii::$app->params;
		$memcacheConfig = $params['system_config']['memcache_config'];
		
		foreach ((array) $memcacheConfig as $memKey => $memItem) {
			$this->_memcache->$memKey = new \Memcache;
			$this->_memcache->$memKey->addServer($memItem['host'], $memItem['port']);
		}
	}
	public static function getInstance(){
		if (self::$_instance === NULL) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

	/**
	 * Get the memobj name by key.
	 * @param type $key
	 * @return str
	 * @author Terry
	 */
	protected function _getMemObjNameByKey($key){
		$memcache_session = 'memcache_session';
		if(isset(self::$allKeyTree[$key]) && !empty(self::$allKeyTree[$key])) {
			$memcache_session = self::$allKeyTree[$key]['type'];
		}
		return $memcache_session;
	}

	/**
	 * Get the memobj name by key.
	 * @param type $key
	 * @return str
	 * @author Terry
	 */
	protected function _getMemExpireByKey($key){
		$expire = 7200;
		if(isset(self::$allKeyTree[$key]) && !empty(self::$allKeyTree[$key])) {
			$expire = self::$allKeyTree[$key]['expire'];
		}
		return $expire;
	}

	/**
	 * Replace the params into the key.
	 * @param type $key
	 * @param type $params
	 * @return type
	 * @author Terry
	 */
	protected function _getRealKey($key,$params){
		if ( !empty($params) ){
			if( is_array( $params ) ){
				$params =  implode('\',\'', $params);
			}
			eval("\$key = sprintf('$key','$params');");
		}
		return $key;
	}

	protected function _checkKeyBanned($key){
		$flg = false;
		foreach($this->_banned_key_pattern as $record){
			if(strpos($key,$record) !== false) {$flg = true;}
		}

		return $flg;
	}

	public function get($key,$params=array()){

		/*Save log.*/
		if( defined("OPEN_LOG_ANALYSIS") && OPEN_LOG_ANALYSIS === TRUE ){
			$GLOBALS['analysis_log']['new_mc']['get'][] = $key ;
		}

		if(defined("DISABLE_CACHE") && DISABLE_CACHE === true) {return false;}
		if($this->_checkKeyBanned($key)){
			return false;
		}

		$memObjName = $this->_getMemObjNameByKey($key);
		$realKey = $this->_getRealKey($key, $params);

		return $this->_memcache->$memObjName->get($realKey);
	}

	/**
	* 批量获取缓存
	* @param string $key  例如 get_special_topic_list_%s_%s_%s_%s 之前定义好的key
	* @param array $params    【 %s 可变的参数  】
	*                    当 $key 中的%s  只有一个（即可变变量为1个  ）   例如 banner_category_%s
	*                        那么这个参数  有两种方式
	*                            第一种方式为一维数组   array( $catId1(这里的键 用作返回数组的键)=> $cartId1 , $catId2(这里的键 用作返回数组的键) => $cartId2 , );
	*                            第一种方式为二维数组   array( $catId1(这里的键 用作返回数组的键) => array( $cartId1 ) , $catId2(这里的键 用作返回数组的键) =>array( $cartId2 ) );
	*                    当 $key 中的%s  只有两个以上   例如 banner_category_%s_%s
	*                          此参数必须传 二维数组   array( $catId1(这里的键 用作返回数组的键)=> array( $cartId1 , $lang,... 【key 中的%s 有几个%s 这里需要几个元素】 ) , $catId2 =>array( $cartId2 , $lang,... 【key 中的%s 有几个%s 这里需要几个元素】) );
	* @return array  $result
	*        array(
	*            $catId1 => mix , // key 为传过来的数组的第一个维度的KEY mix 当缓存存在 则返回缓存的信息 ，否则返回FALSE
	*            $catId2 => mix , // key 为传过来的数组的第一个维度的KEY mix 当缓存存在 则返回缓存的信息 ，否则返回FALSE
	*        );
	*
	* @author BRYAN - NYD  <ningyandong@hofan.cn>
	*/
public function batchGetMc($key,$params=array()){
		if(defined("DISABLE_CACHE") && DISABLE_CACHE === true) {return false;}
		$return = array() ;
		if( !empty( $params ) && is_array( $params ) ){
			$memObjName = $this->_getMemObjNameByKey( $key );
			$keys = array();
			//批量获取key
			foreach ( $params as $k => $v ){
				$keyTmp = $this->_getRealKey( $key , $v );
				$keys[ $k ] = $keyTmp ;
			}
			//获取mc
			$result = $this->_memcache->$memObjName->get( $keys );
			//批量格式化数据
			foreach ( $keys as $k => $v ){
				$return[ $k ] = isset( $result[ $v ] ) ?  $result[ $v ] : FALSE ;
			}
		}
		return $return ;
	}

	public function set($key,$value,$params=array(),$expire=-1){
		if(defined("DISABLE_CACHE") && DISABLE_CACHE === true) {return true;}
		if($this->_checkKeyBanned($key)){
			return true;
		}

		$memObjName = $this->_getMemObjNameByKey($key);
		$realKey = $this->_getRealKey($key, $params);
		$expire = $expire>=0?$expire:$this->_getMemExpireByKey($key);
		$this->_memcache->$memObjName->set($realKey,$value,false,$expire);
	}

	public function delete($key,$params=array()){

		/*Save log.*/
		if( defined("OPEN_LOG_ANALYSIS") && OPEN_LOG_ANALYSIS === TRUE ){
			$GLOBALS['analysis_log']['new_mc']['del'][] = $key ;
		}
		if(defined("DISABLE_CACHE") && DISABLE_CACHE === true) {return true;}

		$memObjName = $this->_getMemObjNameByKey($key);
		$realKey = $this->_getRealKey($key, $params);
		return $this->_memcache->$memObjName->delete($realKey);
	}

	/**
	 * 清理某个sever下的所有memcache.
	 * @param type $memServerName
	 * @return boolean
	 * @author Terry
	 */
	public function flush($memServerName){
		return $this->_memcache->$memServerName->flush();
	}

	/**
	 * 一个通用的mc批量获取数据模式。
	 * @param int||array $sourceKeys
	 * @param vachar $mcKey
	 * @param array $noCacheDataHander
	 * @param array $sourceKeyParams
	 * @return array
	 * @author Terry
	 */
	public function ebMcFetchData($sourceKeys,$mcKey,$noCacheDataHander,$sourceKeyParams=array()){
		if (!is_array($sourceKeys)) {
			$sourceKeys = array($sourceKeys);
		}

		$cachedData = array();
		$noCacheKeys = array();
		foreach($sourceKeys as $sourceKey){
			$cacheData = $this->get($mcKey,array_merge(array($sourceKey),$sourceKeyParams));
			if($cacheData===false){
				$noCacheKeys[] = $sourceKey;
			}else{
				$cachedData[$sourceKey] = $cacheData;
			}
		}

		if (!empty($noCacheKeys)) {
			if (!empty($sourceKeyParams)) {
				$noCacheData = $noCacheDataHander[0]->$noCacheDataHander[1]($noCacheKeys, $sourceKeyParams, false); //通过外部指定方法获取到未缓存key对应的数据
			} else {
				$noCacheData = $noCacheDataHander[0]->$noCacheDataHander[1]($noCacheKeys, false); //通过外部指定方法获取到未缓存key对应的数据
			}

			foreach ($noCacheKeys as $noCacheKey) {//给未缓存数据key写入相应的缓存数据。
				if (!isset($noCacheData[$noCacheKey])) {
					continue;
				}
				$this->set($mcKey, $noCacheData[$noCacheKey], array_merge(array($noCacheKey), $sourceKeyParams));
			}
			$cachedData+=$noCacheData;
		}

		return $cachedData;
	}
}

/* End of file Memcache.php */
/* Location: ./application/libraries/Memcache.php */

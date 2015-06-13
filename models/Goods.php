<?php

namespace app\models;

use Yii;
use app\models\common\EbARModel as baseModel;
use app\components\helpers\HelpOther;
use app\components\helpers\HelpUrl;
use app\components\helpers\ArrayHelper;

class Goods extends baseModel {
	const GOODS_DELETED = 1;//已删除
	const GOODS_UNDELETED = 0;//未删除
	
	private static $_tableName = 'goods';
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
        return Yii::$app->eachbuyer_slave;
    }
	public static function tableName() {
		return self::$_tableName;
	}

	/*
	| -------------------------------------------------------------------
	|  DB Functions
	| -------------------------------------------------------------------
	*/
	public function getSpecialGoodsRecommendTabTitle($languageId){
		$cache_key = "idx_get_special_goods_recommend_tab_title_%s";
		$cache_params = array($languageId);
		$record = $this->memcache->get($cache_key,$cache_params);
		if($record === false){die;
			$query = static::find();
			$query->select('special_goods_recommend_desc');
			$query->where(  ['language_id' => $languageId] );
			$record = $query->asArray()->limit(1)->one();

			$this->memcache->set($cache_key,$record,$cache_params);
		}

		return $record;
	}

	public function getRecommendGoodsSpecialList($languageId){
		$cache_key = "idx_get_recommend_goods_special_list_%s";
		$cache_params = array($languageId);
		$model_sku_list = $this->memcache->get($cache_key,$cache_params);
		if($model_sku_list === false){
			$this->db_read->from('special_goods_recommend_info');
			$query = $this->db_read->get();
			$list = $query->result_array();

			$model_sku_list = array();
			foreach($list as $record){
				$skus = array();
				for($i=1;$i<=10;$i++){
					if($record['sku_'.$i] != '') $skus[] = $record['sku_'.$i];
				}
				$skus = array_unique($skus);

				$proInfoList = $this->_getGoodsBySku($skus,$languageId);
				foreach($proInfoList as $key => $proInfo){
					if($proInfo['goods_number'] == 0 || $proInfo['is_on_sale'] == 0 || $proInfo['goods_complex'] == 2){
						unset($proInfoList[$key]);
						continue;
					}
				}
				$skus = extractColumn($proInfoList,'goods_sn');

				$lack_sku_count = 10 - count($proInfoList);
				if($lack_sku_count > 0){
					$orderby = array('column'=>'last_update','dir'=>'desc');
					if($record['complement'] == 1){
						$orderby = array('column'=>'promote_start_date','dir'=>'desc');
					}elseif($record['complement'] == 2){
						$orderby = array('column'=>'click_count','dir'=>'desc');
					}elseif($record['complement'] == 3){
						$orderby = array('column'=>'add_time','dir'=>'desc');
					}elseif($record['complement'] == 4){
						$orderby = array('column'=>'shop_price','dir'=>'asc');
					}

					$fillup_goods = $this->_getGoodsListWithException($record['category_id'],$languageId,$skus,$lack_sku_count,$orderby);
					$proInfoList = array_merge($proInfoList,$fillup_goods);
				}
				$proInfoList = array_slice($proInfoList,0,10);
				$model_sku_list[$record['model_id']] = $proInfoList;
			}

			$this->memcache->set($cache_key,$model_sku_list,$cache_params);
		}

		return $model_sku_list;
	}

	public function getNewGoodsRecommendTabTitle($languageId){
		$cache_key = "idx_get_new_goods_recommend_tab_title_%s";
		$cache_params = array($languageId);
		$record = $this->memcache->get($cache_key,$cache_params);
		if($record === false){
			$this->db_read->from('new_goods_recommend_desc');
			$this->db_read->where('language_id',$languageId);
			$this->db_read->limit(1);
			$query = $this->db_read->get();
			$record = $query->row_array();

			$this->memcache->set($cache_key,$record,$cache_params);
		}

		return $record;
	}

	public function getRecommendGoodsNewList($languageId){
		$cache_key = "idx_get_recommend_goods_new_list_%s";
		$cache_params = array($languageId);
		$model_sku_list = $this->memcache->get($cache_key,$cache_params);
		if($model_sku_list === false){
			$this->db_read->from('new_goods_recommend_info');
			$query = $this->db_read->get();
			$list = $query->result_array();

			$model_sku_list = array();
			foreach($list as $record){
				$skus = array();
				for($i=1;$i<=10;$i++){
					if($record['sku_'.$i] != '') $skus[] = $record['sku_'.$i];
				}
				$skus = array_unique($skus);

				$proInfoList = $this->_getGoodsBySku($skus,$languageId);
				foreach($proInfoList as $key => $proInfo){
					if($proInfo['goods_number'] == 0 || $proInfo['is_on_sale'] == 0 || $proInfo['goods_complex'] == 2){
						unset($proInfoList[$key]);
						continue;
					}
				}
				$skus = extractColumn($proInfoList,'goods_sn');

				$lack_sku_count = 10 - count($proInfoList);
				if($lack_sku_count > 0){
					$orderby = array('column'=>'last_update','dir'=>'desc');
					if($record['complement'] == 1){
						$orderby = array('column'=>'promote_start_date','dir'=>'desc');
					}elseif($record['complement'] == 2){
						$orderby = array('column'=>'click_count','dir'=>'desc');
					}elseif($record['complement'] == 3){
						$orderby = array('column'=>'add_time','dir'=>'desc');
					}elseif($record['complement'] == 4){
						$orderby = array('column'=>'shop_price','dir'=>'asc');
					}

					$fillup_goods = $this->_getGoodsListWithException($record['category_id'],$languageId,$skus,$lack_sku_count,$orderby);
					$proInfoList = array_merge($proInfoList,$fillup_goods);
				}
				$proInfoList = array_slice($proInfoList,0,10);
				$model_sku_list[$record['model_id']] = $proInfoList;
			}

			$this->memcache->set($cache_key,$model_sku_list,$cache_params);
		}

		return $model_sku_list;
	}
	
	/**
	 * 通过ID获取商品信息
	 * @param int|array $goods_ids
	 * @param int $language_id
	 * @param int $is_delete  0未删除, 1已删除, false 不加此条件
	 * @return multitype:
	 */
	public function getGoodsById($goods_ids,$language_id = 1){
		//判断商品ids
		if(!is_array($goods_ids)) {
			$goods_ids = array($goods_ids);
		}
		if(empty($goods_ids)) {
			return array();
		}

		//缓存处理 更具商品的id和语言的id逐个缓存
		$fetch_db_goods_ids = array();
		$goods_buffer = array();
		$cache_key = "idx_goods_%s_%s";
		foreach($goods_ids as $goods_id){
			$cache_params = array($goods_id,$language_id);
			$goods = $this->memcache->get($cache_key,$cache_params);
			if( $goods === false){
				$fetch_db_goods_ids[] = $goods_id;
			}else{
				$goods_buffer[$goods_id] = $goods;
			}
		}

		//取出没有在缓存中的数据
		if(!empty($fetch_db_goods_ids)){
			//取出商品的基本信息
			$this->db_ebmaster_read->from('product');
			$this->db_ebmaster_read->where_in('id',$fetch_db_goods_ids);
			//根据商品id的数组排序取出
			$this->db_ebmaster_read->order_by('id','asc');
			$query = $this->db_ebmaster_read->get();
			$goods_list = $query->result_array();

			//取出商品的多语言描述信息
			$this->db_ebmaster_read->from('product_description_'.$language_id);
			$this->db_ebmaster_read->where_in('product_id',$fetch_db_goods_ids);
			$query = $this->db_ebmaster_read->get();
			$desc_list = $query->result_array();
			$desc_list = reindexArray($desc_list,'product_id');

			//取出商品的海外仓库 sku warehouse
			global $language2warehouse;
			$warehouse = id2name($language_id,$language2warehouse,array('GZ','HK'));
			$this->db_ebmaster_read->select('product_id, sku, warehouse');
			$this->db_ebmaster_read->from('product_sku');
			$this->db_ebmaster_read->where_in('product_id',$fetch_db_goods_ids);
			if(!empty($warehouse)){
				$this->db_ebmaster_read->where_in('warehouse',$warehouse);
			}
			$query = $this->db_ebmaster_read->get();
			$goods_ext_list = $query->result_array();
			$goods_ext_list = reindexArray($goods_ext_list,'product_id');

			//商品的评论数
			$this->db_ebmaster_read->select('count(*) as count,product_id');
			$this->db_ebmaster_read->from('comment');
			$this->db_ebmaster_read->where_in('product_id',$fetch_db_goods_ids);
			$this->db_ebmaster_read->group_by('product_id');
			$query = $this->db_ebmaster_read->get();
			$review_list = $query->result_array();
			$review_list = reindexArray($review_list,'product_id');

			//循环组合商品信息数组
			if(count($goods_list) > 0) {
				foreach($goods_list as $record){
					//商品的描述
					if(isset($desc_list[$record['id']])){
						$record = array_merge($record,$desc_list[$record['id']]);
					}

					//商品的sku 海外仓库
					if(isset($goods_ext_list[$record['id']])){
						$record = array_merge($record,$goods_ext_list[$record['id']]);
					}

					//商品的评论
					$record['review_count'] = 0;
					if(isset($review_list[$record['id']])){
						$record['review_count'] = $review_list[$record['id']]['count'];
					}

					//存入缓存
					$cache_params = array($record['id'],$language_id);
					$this->memcache->set($cache_key,$record,$cache_params);
					$goods_buffer[$record['id']] = $record;
				}
			} else {
				return array();
			}
		}
		$list = array_values($goods_buffer);

		return $list;
	}

	/**
	 * 通过ID获取商品信息
	 * @param int|array $proIds
	 * @param int $languageId
	 * @param int $isDelete  0未删除, 1已删除, false 不加此条件
	 * @return multitype:
	 */
	public function getProsById($proIds,$languageId = 1){
		
		if(empty($proIds)) {return array();}
		if(!is_array($proIds)) {$proIds = array($proIds);}
		
		$fetchDbProIds = array();
		$prosBuffer = array();
		$cache_key = "idx_pro_%s_%s";
		foreach($proIds as $proId){
			$cache_params = array($proId,$languageId);
			$proInfo = $this->memcache->get($cache_key,$cache_params);
			if( $proInfo === false){
				$fetchDbProIds[] = $proId;
			}else{
				$prosBuffer[$proId] = $proInfo;
			}
		}
		
		if(!empty($fetchDbProIds)){
			
			/*Get product info in array.*/
			$this->db_ebmaster_read->from('product');
			$this->db_ebmaster_read->where_in('id',$fetchDbProIds);
			$this->db_ebmaster_read->order_by('id','asc');
			$proInfoList = $this->db_ebmaster_read->get()->result_array();

			/*Get product description in array.*/
			$languageCode = $this->m_app->getLanguageCodeById($languageId);
			$this->db_ebmaster_read->from('product_description_'.$languageCode);
			$this->db_ebmaster_read->where_in('product_id',$fetchDbProIds);
			$descResults = $this->db_ebmaster_read->get()->result_array();
			$proDescList = reindexArray($descResults,'product_id');

			/*Get product comment in array.*/
			$this->db_ebmaster_read->select('count(*) as count,product_id');
			$this->db_ebmaster_read->from('comment');
			$this->db_ebmaster_read->where_in('product_id',$fetchDbProIds);
			$this->db_ebmaster_read->where('language_id',$languageId);
			$this->db_ebmaster_read->group_by('product_id');
			$reviewResults = $this->db_ebmaster_read->get()->result_array();
			$reviewList = reindexArray($reviewResults,'product_id');

			foreach($proInfoList as $productInfo){
				if(isset($proDescList[$productInfo['id']])){
					$productInfo = array_merge($productInfo,$proDescList[$productInfo['id']]);
				}
				$productInfo['review_count'] = 0;
				if(isset($reviewList[$productInfo['id']])){
					$productInfo['review_count'] = $reviewList[$productInfo['id']]['count'];
				}
				$cache_params = array($productInfo['id'],$languageId);
				$this->memcache->set($cache_key,$productInfo,$cache_params);
				$prosBuffer[$productInfo['id']] = $productInfo;
			}
		}
		$list = array_values($prosBuffer);
		
		return $list;
	}

	public function getUncachedGoodsById($proIds,$languageId = 1){
		if(!is_array($proIds)) $proIds = array($proIds);
		if(empty($proIds)) return array();

		$this->db_read->from('goods');
		$this->db_read->where_in('goods_id',$proIds);
		$this->db_read->where('is_delete',0);
		$this->db_read->order_by('goods_id','asc');
		$query = $this->db_read->get();
		$list = $query->result_array();

		$this->db_read->select('goods_id,goods_name');
		$this->db_read->from('goods_description_'.$languageId);
		$this->db_read->where_in('goods_id',$proIds);
		$query = $this->db_read->get();
		$proDescList = $query->result_array();
		$proDescList = reindexArray($proDescList,'goods_id');

		foreach($list as $key => $record){
			if(isset($proDescList[$record['goods_id']])){
				$list[$key] = array_merge($record,$proDescList[$record['goods_id']]);
			}
		}

		return $list;
	}

	public function getGoodsGalleryList($proIds){
		if(!is_array($proIds)) $proIds = array($proIds);
		if(empty($proIds)) return array();

		$fetchDbProIds = array();
		$prosBuffer = array();
		foreach($proIds as $proId){
			$cache_key = "idx_get_goods_gallery_list_%s";
			$cache_params = array($proId);
			$proInfo = $this->memcache->get($cache_key,$cache_params);
			if($proInfo === false){
				$fetchDbProIds[] = $proId;
			}else{
				$prosBuffer[$proId] = $proInfo;
			}
		}

		if(!empty($fetchDbProIds)){
			$this->db_read->from('goods_gallery');
			$this->db_read->where_in('goods_id',$fetchDbProIds);
			$query = $this->db_read->get();
			$list = $query->result_array();
			$list = spreadArray($list,'goods_id');

			foreach($list as $proId => $record){
				$cache_key = "idx_get_goods_gallery_list_{$proId}";
				$this->memcache->set($cache_key,$record,$cache_params);
				$prosBuffer[$proId] = $record;
			}
		}

		return $prosBuffer;
	}

	public function getAttributeAndValueByGoods($proIds,$languageId){
		if(!is_array($proIds)) $proIds = array($proIds);
		if(empty($proIds)) return array();

		$fetchDbProIds = array();
		$prosBuffer = array();
		foreach($proIds as $proId){
			$cache_key = "idx_get_attribute_and_value_by_goods_%s_%s";
			$cache_params = array($proId,$languageId);
			$proInfo = $this->memcache->get($cache_key,$cache_params);
			if($proInfo === false){
				$fetchDbProIds[] = $proId;
			}else{
				$prosBuffer[$proId] = $proInfo;
			}
		}

		if(!empty($fetchDbProIds)){
			//goods to attribute_value
			$this->db_read->from('attribute_value_goods_link');
			$this->db_read->where_in('goods_id',$fetchDbProIds);
			$query = $this->db_read->get();
			$attribute_value_goods_link = $query->result_array();

			//attribute_value to attribute
			$attribute_value_link = array();
			if(!empty($attribute_value_goods_link)){
				$attribute_value_ids = extractColumn($attribute_value_goods_link,'attribute_value_id');
				$this->db_read->from('attribute_value_link');
				$this->db_read->where_in('attribute_value_id',$attribute_value_ids);
				$query = $this->db_read->get();
				$attribute_value_link = $query->result_array();
			}

			//attribute_value to attribute
			$attribute_value_link = array();
			if(!empty($attribute_value_goods_link)){
				$attribute_value_ids = extractColumn($attribute_value_goods_link,'attribute_value_id');
				$this->db_read->from('attribute_value_link');
				$this->db_read->where_in('attribute_value_id',$attribute_value_ids);
				$query = $this->db_read->get();
				$attribute_value_link = $query->result_array();
			}

			//attribute desc
			$attribute_desc_list = array();
			if(!empty($attribute_value_link)){
				$attribute_ids = extractColumn($attribute_value_link,'attribute_id');
				$this->db_read->from('attribute_desc');
				$this->db_read->where_in('attribute_id',$attribute_ids);
				$this->db_read->where('lan_id',$languageId);
				$query = $this->db_read->get();
				$attribute_desc_list = $query->result_array();
				$attribute_desc_list = reindexArray($attribute_desc_list,'attribute_id');
			}

			//attribute value desc
			$attribute_value_desc_list = array();
			if(!empty($attribute_value_ids)){
				$this->db_read->from('attribute_value_desc');
				$this->db_read->where_in('attribute_value_id',$attribute_value_ids);
				$this->db_read->where('lan_id',$languageId);
				$query = $this->db_read->get();
				$attribute_value_desc_list = $query->result_array();
				$attribute_value_desc_list = reindexArray($attribute_value_desc_list,'attribute_value_id');
			}

			$attribute_value_list = array();
			foreach($attribute_value_link as $record){
				if(!isset($attribute_desc_list[$record['attribute_id']])){
					continue;
				}
				if(!isset($attribute_value_desc_list[$record['attribute_value_id']])){
					continue;
				}
				$attribute_value_list[$record['attribute_value_id']] = array(
					'attribute_id' => $record['attribute_id'],
					'attribute_value_id' => $record['attribute_value_id'],
					'attribute_title' => $attribute_desc_list[$record['attribute_id']]['title'],
					'attribute_value_title' => $attribute_value_desc_list[$record['attribute_value_id']]['title'],
				);
			}

			$list = array();
			foreach($attribute_value_goods_link as $record){
				if(!isset($attribute_value_list[$record['attribute_value_id']])){
					continue;
				}
				$list[$record['goods_id']][] = $attribute_value_list[$record['attribute_value_id']];
			}

			foreach($list as $proId => $record){
				$cache_key = "idx_get_attribute_and_value_by_goods_{$proId}_{$languageId}";
				$this->memcache->set($cache_key,$record,$cache_params);
				$prosBuffer[$proId] = $record;
			}
		}

		return $prosBuffer;
	}

	public function getChildGoodsAndAttribute($proId,$languageId){
		$cache_key = "idx_get_child_goods_and_attribute_%s_%s";
		$cache_params = array($proId,$languageId);
		$proInfo_and_attribute = $this->memcache->get($cache_key,$cache_params);
		if($proInfo_and_attribute === false){
			$this->db_read->from('primary_child_goods_link');
			$this->db_read->where('primary_goods_id',$proId);
			$query = $this->db_read->get();
			$child_goods_ids = $query->result_array();
			$child_goods_ids = extractColumn($child_goods_ids,'child_goods_id');

			$child_goods_list = $this->getGoodsById($child_goods_ids,$languageId, self::GOODS_UNDELETED);
			foreach($child_goods_list as $key => $record){
				if($child_goods_list[$key]['goods_number'] <= 0){
					unset($child_goods_list[$key]);
				}elseif($child_goods_list[$key]['goods_complex'] == 1){
					unset($child_goods_list[$key]);
				}elseif($child_goods_list[$key]['is_on_sale'] != GOODS_IS_ON_SALE){
					unset($child_goods_list[$key]);
				}
			}
			$child_goods_list = array_values($child_goods_list);
			$child_goods_ids = extractColumn($child_goods_list,'goods_id');

			//goods to attribute_value
			$attribute_value_goods_link = array();
			if(!empty($child_goods_ids)){
				$this->db_read->from('attribute_value_goods_link');
				$this->db_read->where_in('goods_id',$child_goods_ids);
				$query = $this->db_read->get();
				$attribute_value_goods_link = $query->result_array();
			}

			//attribute_value to attribute
			$attribute_value_link = array();
			if(!empty($attribute_value_goods_link)){
				$attribute_value_ids = extractColumn($attribute_value_goods_link,'attribute_value_id');
				$this->db_read->from('attribute_value_link');
				$this->db_read->where_in('attribute_value_id',$attribute_value_ids);
				$query = $this->db_read->get();
				$attribute_value_link = $query->result_array();
			}

			//attribute desc
			$attribute_desc_list = array();
			if(!empty($attribute_value_link)){
				$attribute_ids = extractColumn($attribute_value_link,'attribute_id');
				$this->db_read->from('attribute_desc');
				$this->db_read->where_in('attribute_id',$attribute_ids);
				$this->db_read->where('lan_id',$languageId);
				$query = $this->db_read->get();
				$attribute_desc_list = $query->result_array();
				$attribute_desc_list = reindexArray($attribute_desc_list,'attribute_id');
			}

			//attribute value desc
			$attribute_value_desc_list = array();
			if(!empty($attribute_value_ids)){
				$this->db_read->from('attribute_value_desc');
				$this->db_read->where_in('attribute_value_id',$attribute_value_ids);
				$this->db_read->where('lan_id',$languageId);
				$query = $this->db_read->get();
				$attribute_value_desc_list = $query->result_array();
				$attribute_value_desc_list = reindexArray($attribute_value_desc_list,'attribute_value_id');
			}

			$attribute_value_link = spreadArray($attribute_value_link,'attribute_id');
			$attribute_value_goods_link = spreadArray($attribute_value_goods_link,'attribute_value_id');

			foreach($attribute_value_link as $attribute_id => $attribute_value_list){
				if(!isset($attribute_desc_list[$attribute_id])){
					unset($attribute_value_link[$attribute_id]);
					continue;
				}

				foreach($attribute_value_list as $key => $attribute_value){
					if(!isset($attribute_value_desc_list[$attribute_value['attribute_value_id']])){
						unset($attribute_value_list[$key]);
						continue;
					}
					if(!isset($attribute_value_goods_link[$attribute_value['attribute_value_id']])){
						unset($attribute_value_list[$key]);
						continue;
					}

					$proIds = extractColumn($attribute_value_goods_link[$attribute_value['attribute_value_id']],'goods_id');
					sort($proIds);
					$attribute_value_list[$key] = array(
						'attribute_value_id' => $attribute_value['attribute_value_id'],
						'goods_id' => $proIds,
						'default_goods_id' => current($proIds),
						'attribute_value_title' => $attribute_value_desc_list[$attribute_value['attribute_value_id']]['title'],
					);
				}
				$attribute_value_list = array_values($attribute_value_list);
				sortArray($attribute_value_list,'default_goods_id');

				$attribute_value_link[$attribute_id] = array(
					'attribute_id' => $attribute_id,
					'attribute_title' => $attribute_desc_list[$attribute_id]['title'],
					'attribute_value_list' => $attribute_value_list,
				);
			}
			$attribute_value_link = array_values($attribute_value_link);

			$proInfo_and_attribute = array($child_goods_list,$attribute_value_link);
			$this->memcache->set($cache_key,$proInfo_and_attribute,$cache_params);
		}

		return $proInfo_and_attribute;
	}

	public function getUserCollectedGoodsId($user_id = false){
		if($user_id === false || $user_id == 0) return array();

		$this->db_read->select('goods_id');
		$this->db_read->from('collect_goods');
		$this->db_read->where('user_id',$user_id);
		$query = $this->db_read->get();
		$list = $query->result_array();

		$list = extractColumn($list,'goods_id');
		return $list;
	}

	public function checkBoughtGoods($proId,$user_id){
		$this->db_read->from('primary_child_goods_link');
		$this->db_read->where('primary_goods_id',$proId);
		$query = $this->db_read->get();
		$proIds = $query->result_array();
		$proIds = extractColumn($proIds,'child_goods_id');
		$proIds[] = $proId;

		$this->db_read->select('order_id');
		$this->db_read->from('order_info');
		$this->db_read->where('user_id',$user_id);
		$query = $this->db_read->get();
		$list = $query->result_array();
		$order_ids = extractColumn($list,'order_id');
		$order_ids[] = -1;

		$this->db_read->from('order_goods');
		$this->db_read->where_in('order_id',$order_ids);
		$this->db_read->where_in('goods_id',$proIds);
		$count = $this->db_read->count_all_results();

		return ($count > 0);
	}

	public function getActiveGoodsByCategory($category_id,$exception_category_id,$languageId,$limit){
		$cache_key = "idx_get_active_goods_by_category_%s_%s_%s_%s";
		$cache_params = array($category_id,$exception_category_id,$languageId,$limit);
		$list = $this->memcache->get($cache_key,$cache_params);
		if($list === false){
			$this->db_read->select('cat_id');
			$this->db_read->from('category');
			$this->db_read->like('id_path',$category_id);
			$query = $this->db_read->get();
			$list = $query->result_array();
			$category_ids = extractColumn($list,'cat_id');
			$category_ids = array_diff($category_ids,array($exception_category_id));

			$proInfoList = array();
			if(!empty($category_ids)){
				$this->db_read->select('goods_id,goods_name,url_name,goods_img,cost_price,market_price,display_market_price,shop_price,promote_price,promote_start_date,promote_end_date');
				$this->db_read->from('goods');
				$this->db_read->where_in('cat_id',$category_ids);
				$this->db_read->where('is_delete',0);
				$this->db_read->where('goods_complex !=',1);
				$this->db_read->where('is_on_sale',GOODS_IS_ON_SALE);
				$this->db_read->limit($limit*5);
				$query = $this->db_read->get();
				$proInfoList = $query->result_array();
			}

			$proIds = extractColumn($proInfoList,'goods_id');
			$proDescList = array();
			if(!empty($proIds)){
				$this->db_read->select('goods_id,goods_name');
				$this->db_read->from('goods_description_'.$languageId);
				$this->db_read->where_in('goods_id',$proIds);
				$query = $this->db_read->get();
				$proDescList = $query->result_array();
			}
			$proDescList = reindexArray($proDescList,'goods_id');

			$list = array();
			$alternative_buffer = array();
			foreach($proInfoList as $record){
				if(!isset($proDescList[$record['goods_id']])){
					$alternative_buffer[] = $record;
				}else{
					$list[] = array_merge($record,$proDescList[$record['goods_id']]);
					if(count($list) >= $limit) break;
				}
			}
			$list_length = count($list);
			if($list_length < $limit){
				$alternative_buffer = array_slice($alternative_buffer,0,$limit - $list_length);
				$list = array_merge($list,$alternative_buffer);
			}

			$this->memcache->set($cache_key,$list,$cache_params);
		}

		return $list;
	}

	public function getCategoryBestSaleActiveGoods($category_id,$languageId,$limit){
		$cache_key = "idx_get_category_best_sale_active_goods_%s_%s_%s";
		$cache_params = array($category_id,$languageId,$limit);
		$list = $this->memcache->get($cache_key,$cache_params);
		if($list === false){
			$this->db_read->select('goods_id,goods_name,url_name,goods_img,cost_price,market_price,display_market_price,shop_price,promote_price,promote_start_date,promote_end_date');
			$this->db_read->from('goods');
			$this->db_read->where('cat_id',$category_id);
			$this->db_read->where('is_delete',0);
			$this->db_read->where('goods_complex !=',1);
			$this->db_read->where('is_on_sale',GOODS_IS_ON_SALE);
			$query = $this->db_read->get();
			$proInfoList = $query->result_array();

			$proIds = extractColumn($proInfoList,'goods_id');
			$order_list = array();
			if(!empty($proIds)){
				$this->db_read->select('goods_id,count(*) as sale_quantity');
				$this->db_read->from('order_goods');
				$this->db_read->where_in('goods_id',$proIds);
				$this->db_read->group_by('goods_id');
				$this->db_read->order_by('sale_quantity','desc');
				$this->db_read->order_by('goods_id','desc');
				$this->db_read->limit($limit*5);
				$query = $this->db_read->get();
				$order_list = $query->result_array();
			}

			$proIds = extractColumn($order_list,'goods_id');
			$proDescList = array();
			if(!empty($proIds)){
				$this->db_read->select('goods_id,goods_name');
				$this->db_read->from('goods_description_'.$languageId);
				$this->db_read->where_in('goods_id',$proIds);
				$query = $this->db_read->get();
				$proDescList = $query->result_array();
			}
			$proInfoList = reindexArray($proInfoList,'goods_id');
			$proDescList = reindexArray($proDescList,'goods_id');

			$list = array();
			$alternative_buffer = array();
			foreach($order_list as $record){
				if(!isset($proInfoList[$record['goods_id']])) continue;

				if(!isset($proDescList[$record['goods_id']])){
					$alternative_buffer[] = array_merge($record,$proInfoList[$record['goods_id']]);
				}else{
					$list[] = array_merge($record,$proInfoList[$record['goods_id']],$proDescList[$record['goods_id']]);
					if(count($list) >= $limit) break;
				}
			}
			$list_length = count($list);
			if($list_length < $limit){
				$alternative_buffer = array_slice($alternative_buffer,0,$limit - $list_length);
				$list = array_merge($list,$alternative_buffer);
			}

			$this->memcache->set($cache_key,$list,$cache_params);
		}

		return $list;
	}

	/**
	 * 取出指定的分类的商品的id
	 * @param  integer $category_id 指定的获取的分类的id
	 * @param  integer $languageId 指定获取的语言的id
	 * @param  array  $param 指定商品的排序的方式
	 * @param  integer $start 商品取出偏移量
	 * @param  integer $pagesize 每页产品的展示数量
	 * @return array 返回商品的ids数组
	 */
	public function getCategoryGoodsIds($category_id,$languageId,$param = array(), $start, $pagesize){
		//参数处理
		$param_cache_key = array();
		if(isset($param['sort'])) $param_cache_key[] = 's'.$param['sort'];
		if(isset($param['price_max'])) $param_cache_key[] = 'pa'.$param['price_max'];
		if(isset($param['price_min'])) $param_cache_key[] = 'pi'.$param['price_min'];

		//缓存处理
		$param_cache_key = implode('_',$param_cache_key);
		$cache_key = "idx_get_category_goods_ids_%s_%s_%s_%s_%s";
		$cache_params = array($category_id,$languageId,$param_cache_key,$start,$pagesize);
		$list = $this->memcache->get($cache_key,$cache_params);
		if( $list === false){
			//取出指定分类的子分类
			$this->db_ebmaster_read->select('id');
			$this->db_ebmaster_read->from('category');
			$this->db_ebmaster_read->like('path',$category_id);
			$query = $this->db_ebmaster_read->get();
			$list = $query->result_array();

			//取出销售分类的商品id
			$category_ids = extractColumn($list,'id');//组合为id数组
			$this->db_ebmaster_read->select('pid');
			$this->db_ebmaster_read->from('category_product');
			$this->db_ebmaster_read->where_in('category_id',$category_ids);
			$query = $this->db_ebmaster_read->get();
			$proIds_from_cat = $query->result_array();
			$proIds_from_cat = extractColumn($proIds_from_cat,'pid');

			//取出商品的id
			$this->db_ebmaster_read->select('id');
			$this->db_ebmaster_read->from('product');
			//商品id取出条件的处理
			if( !empty($proIds_from_cat) ){
				$where_tmp = '( `category_id` IN ( ' . implode(',' , $category_ids ) . ') OR `id`  IN ( ' . implode( ',' , $proIds_from_cat ) . ' ) )';
				$this->db_ebmaster_read->where( $where_tmp );
			} else {
				$this->db_ebmaster_read->where_in('category_id',$category_ids);
			}
			//价格的排序是按照商品默认的价格排序
			if(isset($param['price_max'])){
				$this->db_ebmaster_read->where('price <=',$param['price_max']);
			}
			if(isset($param['price_min'])){
				$this->db_ebmaster_read->where('price >=',$param['price_min']);
			}
			$this->db_ebmaster_read->where('status',1); //商品的状态 1是上架 0是下架
			if(isset($param['sort'])){
				if($param['sort']=='add') {
					$this->db_ebmaster_read->order_by('add_time','desc');
				} elseif($param['sort']=='price_asc') {
					$this->db_ebmaster_read->order_by('price','asc');
				} elseif($param['sort']=='price_desc') {
					$this->db_ebmaster_read->order_by('price','desc');
				}
			}
			//默认排序是按照销量排序
			$this->db_ebmaster_read->order_by('sale_count','desc');

			//分页取出
			$this->db_ebmaster_read->limit($pagesize, $start);//第一个参数是取出多少条数据，第二个参数是从什么地方开始取出
			$query = $this->db_ebmaster_read->get();
			$list = $query->result_array();
			$list = extractColumn($list,'id');

			//取出指定分类下商品总数
			$this->db_ebmaster_read->from('product');
			//商品id取出条件的处理
			if( !empty($proIds_from_cat) ){
				$where_tmp = '( `category_id` IN ( ' . implode(',' , $category_ids ) . ') OR `id`  IN ( ' . implode( ',' , $proIds_from_cat ) . ' ) )';
				$this->db_ebmaster_read->where( $where_tmp );
			} else {
				$this->db_ebmaster_read->where_in('category_id',$category_ids);
			}
			//价格的排序是按照商品默认的价格排序
			if(isset($param['price_max'])){
				$this->db_ebmaster_read->where('price <=',$param['price_max']);
			}
			if(isset($param['price_min'])){
				$this->db_ebmaster_read->where('price >=',$param['price_min']);
			}
			$this->db_ebmaster_read->where('status',1); //商品的状态 1是上架 0是下架
			$resultCount = $this->db_ebmaster_read->count_all_results();

			//加入缓存
			$list = array('goodsCount' => $resultCount, 'goodsList' => $list);
			$this->memcache->set($cache_key,$list,$cache_params);
		}

		return $list;
	}

	/**
	 * 获取分类的商品列表
	 * @param  integer  $category_id 指定的获取的分类的id
	 * @param  integer  $languageId  指定获取的语言的id
	 * @param  array $param 指定商品的排序的方式
	 * @param  integer $page 指定获取的分页数
	 * @param  integer $pagesize 指定每页显示的商品的数量
	 * @return [type] 返回取出的商品的列表
	 */
	public function getCategoryGoodsList($category_id,$languageId,$param,$page = 1, $pagesize = PAGE_COUNT_CATEGORY){
		//方法的参数处理
		$category_id = intval($category_id);
		$languageId = intval($languageId);
		$page = intval($page);

		//商品取出偏移量
		$start = ($page - 1) * $pagesize;

		//通过分类标示，语言标示，排序参数取出指定排序后的商品的id
		$goodsArray = $this->getCategoryGoodsIds($category_id,$languageId,$param, $start, $pagesize);
		if(isset($goodsArray['goodsList']) && count($goodsArray['goodsList']) > 0) {
			$goodsIds = $goodsArray['goodsList'];
		}

		//根据语言取出指定的海外仓
		// global $language2warehouse;
		// $warehouse = id2name($languageId,$language2warehouse,array('GZ','HK'));

		//获取商品的总数
		$count = 0;
		if(isset($goodsArray['goodsCount']) && count($goodsArray['goodsCount']) > 0) {
			$count = $goodsArray['goodsCount'];
		}

		//组合商品信息数组
		$list = $this->getGoodsById($goodsIds,$languageId);
		$list = reindexArray($list,'id');

		//恢复数组的键值
		$res = array();
		foreach($goodsIds as $proId){
			if(isset($list[$proId])){
				$res[] = $list[$proId];
			}
		}

		//返回商品数组和数量
		return array($res,$count);
	}

	public function getCategoryGoodsPriceBoundary($category_id,$languageId) {
		$cache_key = "idx_get_category_goods_price_boundary_%s_%s";
		$cache_params = array($category_id,$languageId);
		$result = $this->memcache->get($cache_key,$cache_params);
		if ( $result === false) {
			$proIds = $this->getCategoryGoodsIds($category_id,$languageId);

			$result = array();
			if(!empty($proIds)){
				$this->db_read->select_min( 'shop_price', 'price_min' );
				$this->db_read->select_max( 'shop_price', 'price_max' );
				$this->db_read->from( 'goods' );
				$this->db_read->where_in( 'goods_id', $proIds );
				$this->db_read->limit( 1 );
				$query = $this->db_read->get();
				$result = $query->row_array();
			}else{
				$result['price_min'] = 0;
				$result['price_max'] = 0;
			}

			if($result['price_min'] === null) $result['price_min'] = 0;
			if($result['price_max'] === null) $result['price_max'] = 0;
			$result = array($result['price_min'],$result['price_max']);

			$this->memcache->set( $cache_key, $result, $cache_params );
		}

		return $result;
	}

	public function getGoodsSpecBySku($proInfo_skus){
		if(!is_array($proInfo_skus)) $proInfo_skus = array($proInfo_skus);
		if(empty($proInfo_skus)) return array();

		$this->db_read->from('real_time_pro_weight_info');
		$this->db_read->where_in('code',$proInfo_skus);
		$this->db_read->where('length >',0);
		$this->db_read->where('width >',0);
		$this->db_read->where('height >',0);
		$query = $this->db_read->get();
		$list = $query->result_array();

		return $list;
	}

	public function getGoodsSensitiveInfoBySku($proInfo_skus){
		if(!is_array($proInfo_skus)) $proInfo_skus = array($proInfo_skus);
		if(empty($proInfo_skus)) return array();

		$this->db_read->select('goods_sku,contraband_type_id');
		$this->db_read->from('product_sensitive_info');
		$this->db_read->where_in('goods_sku',$proInfo_skus);
		$query = $this->db_read->get();
		$list = $query->result_array();

		return $list;
	}
	/*
	| -------------------------------------------------------------------
	|  DB Write Functions
	| -------------------------------------------------------------------
	*/
	public function updateGoodsSaleBatch($info){
		if(!empty($info)){
			$this->db_write->update_batch('goods',$info,'goods_id',false);
		}
	}
	/*
	| -------------------------------------------------------------------
	|  noDB Functions
	| -------------------------------------------------------------------
	*/
	public function calculatePrice(&$proInfo,$count = 1){
		$proInfo['flg_promote_active'] = $this->isPromoteActive($proInfo);
		$proInfo['active_promote_price'] = $this->calculateActivePromotePrice($proInfo);
		$proInfo['gross_margin'] = $this->calculateGrossMargin($proInfo);
		$proInfo['shop_price'] = $this->calculateShopPrice($proInfo,$count);
		$proInfo['goods_price'] = $this->calculateFinalPrice($proInfo);
		$proInfo['discount'] = $this->calculateDiscount($proInfo);
	}

	public function isPromoteActive($proInfo){
		$now = $_SERVER['REQUEST_TIME'];
		$flg = false;
		if($proInfo['promote_price'] > 0 && $proInfo['promote_start_date'] <= $now && ($proInfo['promote_end_date'] >= $now || !$proInfo['promote_end_date'])){
			$flg = true;
		}

		return $flg;
	}

	public function calculateActivePromotePrice($proInfo){
		$now = $_SERVER['REQUEST_TIME'];
		$price = 0;
		if($proInfo['flg_promote_active'] === true){
			$price = $proInfo['promote_price'];
		}
		$price = sprintf('%.2f',$price);

		return $price;
	}

	public function calculateGrossMargin($proInfo){
		$now = $_SERVER['REQUEST_TIME'];
		$price = $proInfo['shop_price'];
		if($proInfo['flg_promote_active'] === true){
			$price = $proInfo['promote_price'];
		}
		$grossMargin = ($price - $proInfo['cost_price']) / $price;

		return $grossMargin;
	}

	public function calculateShopPrice($proInfo,$count = 1){
		$shop_price = $proInfo['shop_price'];
		if($proInfo['flg_promote_active'] !== true){
			if (isset($proInfo['gross_margin']) && $proInfo['gross_margin'] > 0.15) {
				if ($count >= 50) {
					$shop_price = $shop_price * 0.88;
				} elseif ($count >= 10) {
					$shop_price = $shop_price * 0.9;
				} elseif ($count >= 3) {
					$shop_price = $shop_price * 0.94;
				}
			}
		}
		$shop_price = sprintf('%.2f',$shop_price);

		return $shop_price;
	}

	public function calculateFinalPrice($proInfo){
		$final_price = 0;
		if($proInfo['flg_promote_active'] === true){
			$final_price = $proInfo['active_promote_price'];
		}else{
			$final_price = $proInfo['shop_price'];
		}

		return $final_price;
	}

	public function calculateDiscount($proInfo){
		$discountFormat = 0;
		if($proInfo['flg_promote_active'] === true && isset($proInfo['active_promote_price']) && $proInfo['active_promote_price'] > 0){
			if ($proInfo['display_market_price'] == 1 && $proInfo['market_price'] > 0) {
				$discountReferPri = $proInfo['market_price'];
			}else{
				$discountReferPri = $proInfo['shop_price'];
			}
			$discount = round(($discountReferPri-$proInfo['active_promote_price'])/$discountReferPri*100);
			$endNum = substr($discount,-1,1);
			if($endNum>=5){
				$endNumFormat = 5;
			}else{
				$endNumFormat = 0;
			}
			$discountFormat = substr($discount,0,-1).$endNumFormat;
		}
		return $discountFormat;
	}

	public function calculateLimitBuyTime($proInfo){
		$limit_time_days = 7;
		$now = $_SERVER['REQUEST_TIME'];
		$display_time = 3600 * 24 * $limit_time_days;

		$seconds = 0;
		if($proInfo['flg_promote_active'] && isset($proInfo['active_promote_price']) && $proInfo['active_promote_price'] > 0 && $proInfo['promote_end_date'] > 0 && $now > $proInfo['promote_end_date'] - $display_time){
			$seconds = $proInfo['promote_end_date'] - $now;
		}

		return $seconds;
	}

	public function formatLimitBuyTime($proInfo){
		$display = '';
		if(isset($proInfo['limit_buy_time']) && $proInfo['limit_buy_time'] > 0){
			$day = floor($proInfo['limit_buy_time']/3600/24);
			$day = str_pad($day,2,'0',STR_PAD_LEFT);

			$dayLeft = $proInfo['limit_buy_time']%(24*3600);
			$hour = floor($dayLeft/3600);
			$hour = str_pad($hour,2,'0',STR_PAD_LEFT);

			$hourLeft = $dayLeft%3600;
			$min = floor($hourLeft/60);
			$min = str_pad($min,2,'0',STR_PAD_LEFT);

			$second = $hourLeft%60;
			$second = str_pad($second,2,'0',STR_PAD_LEFT);

			$display = $day.':'.$hour.':'.$min.':'.$second;
		}

		return $display;
	}
	/*
	| -------------------------------------------------------------------
	|  Private Functions
	| -------------------------------------------------------------------
	*/
	protected function _getGoodsBySku($skus,$languageId){
		if(!is_array($skus)) $skus = array($skus);
		if(empty($skus)) return array();

		$res = array();
		if(!empty($skus)){
			$this->db_ebmaster_read->select('id,name,url,image,price,market_price,sale_type');
			$this->db_ebmaster_read->from('product');
			$this->db_ebmaster_read->where_in('id',$skus);
			//$this->db_ebmaster_read->where('status', 1);
			$query = $this->db_ebmaster_read->get();
			$list = $query->result_array();
			$proIds = extractColumn($list,'id');

			$this->db_read->from('goods');
			$this->db_read->where_in('goods_sn',$skus);
			$this->db_read->where('is_delete !=',1);
			$query = $this->db_read->get();
			$list = $query->result_array();
			$proIds = extractColumn($list,'goods_id');

			$proInfo_name_list = array();
			if(!empty($proIds)){
				$this->db_read->from('goods_description_'.$languageId);
				$this->db_read->where_in('goods_id',$proIds);
				$this->db_read->where('goods_name !=','');
				$query = $this->db_read->get();
				$proInfo_name_list = $query->result_array();
				$proInfo_name_list = reindexArray($proInfo_name_list,'goods_id');
			}

			foreach($list as $key => $record){
				if(! empty( $proInfo_name_list[$record['goods_id']]) ){
					$record = array_merge($record,$proInfo_name_list[$record['goods_id']]);
				}
				$res[] = $record;
			}
		}

		$list = array();
		$res = reindexArray($res,'goods_sn');
		foreach ($skus as $sku) {
			if(isset($res[$sku])) $list[] = $res[$sku];
		}

		return $list;
	}

	protected function _getGoodsListWithException($category_id,$languageId,$exception_skus,$limit = 20,$orderby = array()){
		$this->db_read->distinct('cat_id');
		$this->db_read->from('category');
		$this->db_read->like('id_path',strval($category_id));
		$this->db_read->where('is_show',1);
		$query = $this->db_read->get();
		$category_ids = $query->result_array();
		$category_ids = extractColumn($category_ids,'cat_id');

		$list = array();
		if(!empty($category_ids)){
			$this->db_read->from('goods');
			$this->db_read->where_in('cat_id',$category_ids);
			if(!empty($exception_skus)) $this->db_read->where_not_in('goods_sn',$exception_skus);
			$this->db_read->where('is_on_sale >',0);
			$this->db_read->where('goods_complex !=',2);
			$this->db_read->where('is_delete !=',1);
			$this->db_read->where('goods_number >',0);
			if(isset($orderby['column'])) $this->db_read->order_by($orderby['column'],id2name('dir',$orderby,'asc'));
			$this->db_read->limit($limit*10);
			$query = $this->db_read->get();
			$proInfoList = $query->result_array();

			$proIds = extractColumn($proInfoList,'goods_id');

			$proDescList = array();
			if(!empty($proIds)){
				$this->db_read->from('goods_description_'.$languageId);
				$this->db_read->where_in('goods_id',$proIds);
				$query = $this->db_read->get();
				$proDescList = $query->result_array();
				$proDescList = reindexArray($proDescList,'goods_id');
			}

			$alternative_buffer = array();
			foreach($proInfoList as $record){
				if(!isset($proDescList[$record['goods_id']])){
					$alternative_buffer[] = $record;
				}else{
					$list[] = array_merge($record,$proDescList[$record['goods_id']]);
					if(count($list) >= $limit) break;
				}
			}
			$list_length = count($list);
			if($list_length < $limit){
				$alternative_buffer = array_slice($alternative_buffer,0,$limit - $list_length);
				$list = array_merge($list,$alternative_buffer);
			}
		}

		return $list;
	}

	/**
	 * get good attribute by $sku &&$lang_id mc
	 *  BY mysql
	 * @param string $sku  good sku
	 * @param int $lang_id lang id
	 * @param int $proInfo_child_status 是否有子产品
	 * @param boolean $is_cache is mc TRUE/FALSE
	 *
	 * @return array();
	 * @author ningyandong
	 */
	public function getGoodsAttrBySkuPage( $skus , $lang_id ) {
		sort($skus);
		$skus_mckey = implode('_', $skus ) ;
		$cache_key = "idx_good_attribute_info_%s_%s";
		$cache_params = array($skus_mckey,$lang_id);
		$result = $this->memcache->get( $cache_key,$cache_params );
		if( $result === FALSE ){
			$result	= $this->_getGoodsAttrBySkuData($skus , $lang_id );
			if(is_array( $result ) ){
				$this->memcache->set( $cache_key, $result, $cache_params );
			}
		}

		return $result ;
	}

	/**
	 * get good attribute by $sku &&$lang_id mysql
	 *  BY mysql
	 * @param array $sku  good sku
	 * @param int $lang_id lang id
	 * @param int $proInfo_child_status is child good
	 * @return array();
	 * @author ningyandong
	 */
	protected function _getGoodsAttrBySkuData( $skus , $lang_id ){
		//get attribute_id  by  product_sku
		$product_attribute_list = array();
		$attribute_info = array();
		$attribute_result_list = array();
		$attribute_name = array();
		$attribute_value_list = array();
		if(!empty($skus)){
			$this->db_read->select( 'attribute_id,value' );
			$this->db_read->from( 'www_attr_product_attribute' );
			$this->db_read->where_in( 'product_sku' , $skus );
			$this->db_read->where( 'deleted' , 0 ) ;
			$query = $this->db_read->get();
			$product_attribute_list = $query->result_array() ;
			$product_attribute_list = reindexArray( $product_attribute_list , 'attribute_id' );
		}
		//get attribute info by attribute_id
		if( !empty( $product_attribute_list ) ) {
			$this->db_read->select( '`id`,`type`,`unit`,`code`' );
			$this->db_read->from( 'www_attr_attribute' );
			$this->db_read->where_in( 'id' , array_keys( $product_attribute_list ) );
			$this->db_read->where( 'deleted' , 0 ) ;
			$this->db_read->order_by('time','desc');
			$query = $this->db_read->get();
			$attribute_info = $query->result_array() ;
		}
		//get attribute_name info by attribute_id
		if( !empty( $product_attribute_list ) ) {
			$this->db_read->select( '`attribute_id`,`name`' );
			$this->db_read->from( 'www_attr_attribute_lang' );
			$this->db_read->where_in( 'attribute_id' , array_keys( $product_attribute_list ) );
			$this->db_read->where('lang_eb_id', $lang_id );
			$this->db_read->where( 'deleted' , 0 ) ;
			$this->db_read->order_by('time','desc');
			$query = $this->db_read->get();
			$attribute_name = reindexArray( $query->result_array() , 'attribute_id' ) ;
		}
		//获取 attribute_value_id  根据 product_sku
		$attribute_value_id_list = array();
		if(!empty($skus)){
			$this->db_read->select( 'attribute_value_id' );
			$this->db_read->from( 'www_attr_product_attribute_value' );
			$this->db_read->where_in( 'product_sku' , $skus );
			$this->db_read->where( 'deleted' , 0 ) ;
			$query = $this->db_read->get();
			$attribute_value_id_list = $query->result_array() ;
			$attribute_value_id_list = extractColumn( $attribute_value_id_list , 'attribute_value_id' )  ;
		}
		//判断是否是空数组 是空数组直接返回
		$attribute_id_list = array();
		$attribute_value_info = array();
		if( !empty( $attribute_value_id_list ) ) {
			//获取 属性值 attribute_value 根据  attribute_value_id
			$this->db_read->select( 'attribute_value_id,value' );
			$this->db_read->from( 'www_attr_attribute_value_lang' );
			$this->db_read->where_in( 'attribute_value_id' , $attribute_value_id_list );
			$this->db_read->where('lang_eb_id', $lang_id );
			$this->db_read->where( 'deleted' , 0 ) ;
			$query = $this->db_read->get();
			$attribute_value_info = $query->result_array();

			//获取 attribute_id  根据  attribute_value 的ID
			$this->db_read->select( 'id,attribute_id' );
			$this->db_read->from( 'www_attr_attribute_value' );
			$this->db_read->where_in( 'id' , $attribute_value_id_list );
			$this->db_read->where( 'deleted' , 0 ) ;
			$this->db_read->order_by('sort_order','desc');
			$query = $this->db_read->get();
			$attribute_id_list = $query->result_array() ;
		}
		if( !empty( $attribute_value_info ) && !empty( $attribute_id_list ) ){
			$attribute_value_info = reindexArray($attribute_value_info,'attribute_value_id' );
			foreach ( $attribute_id_list as $v ){
				if( empty($attribute_value_info[ $v['id'] ]['value']) ) {
					continue;
				}
				//多个值
				if(  empty( $attribute_value_list[ $v['attribute_id'] ] ) ){
					$attribute_value_list[ $v['attribute_id'] ] = trim( $attribute_value_info[ $v['id'] ]['value']) ;
				}else{
					$attribute_value_list[ $v['attribute_id'] ] .= ',' .trim( $attribute_value_info[ $v['id'] ]['value']) ;
				}
			}
		}

		//格式化数据
		if( !empty( $attribute_info ) ){
			foreach ( $attribute_info as $info ){
				// type: 单选、多选 和 输入项   输入项 是 录入型    value
				if( trim( $info['type'] ) === '输入项' ){
					$value_tmp = empty( $product_attribute_list[ $info['id'] ]['value'] ) ? '' : trim( $product_attribute_list[ $info['id'] ]['value'] );
				}else{// 单选、多选  是 非录入型
					$value_tmp = empty( $attribute_value_list[ $info['id'] ] ) ? '' : trim( $attribute_value_list[ $info['id'] ] );
				}
				if( empty( $value_tmp ) || empty( $attribute_name[ $info['id'] ]['name'] ) ){
					continue;
				}

				$attribute_result_list[ $info['code'] ] = array(
					'name' => empty( $attribute_name[ $info['id'] ]['name'] ) ? '' : trim( $attribute_name[ $info['id'] ]['name'] ) ,
					'unit' => empty( $info['unit'] ) ? '' : trim( $info['unit'] ) ,
					'value' => $value_tmp ,
				);

				ksort( $attribute_result_list );
			}
		}

		return $attribute_result_list ;
	}

	public function getGoodsBySku($skus,$languageId = 1){
		if(!is_array($skus)) $skus = array($skus);
		if(empty($skus)) return array();

		$fetch_db_goods_skus = array();
		$prosBuffer = array();
		$cache_key = "idx_goods_sku_%s_%s";
		foreach($skus as $sku){
			$cache_params = array($sku,$languageId);
			$proInfo = $this->memcache->get($cache_key,$cache_params);
			if( $proInfo === false){
				$prosBuffer[$sku]  = array();
				$fetch_db_goods_skus[] = $sku;
			}else{
				$prosBuffer[$sku] = $proInfo;
			}
		}

		if(!empty($fetch_db_goods_skus)){
			$result_sku = $this->_getGoodsBySku( $fetch_db_goods_skus , $languageId );
			$result_sku = reindexArray($result_sku,'goods_sn');
			foreach($result_sku as $record){
				$cache_params = array($record['goods_sn'],$languageId);
				$this->memcache->set($cache_key , $record , $cache_params);
				$prosBuffer[$record['goods_sn']] = $record;
			}
		}

		return $prosBuffer ;
	}

	public function auto_completion_desc_by_narrow_attr($page){
		$limit = 100;
		$languageIds = array();
		$this->db_read->select( 'language_id, language_code' );
		$this->db_read->from( 'languages' );
		$query = $this->db_read->get();
		$record = $query->result_array();
		foreach( $record as $info ){
			$languageIds[] = $info['language_id'];
		}
		//select g.goods_id,goods_sn from goods_description_1 gd inner join goods g on g.goods_id = gd.goods_id and goods_complex != 1 and goods_description like '%desc_tmp%'
		foreach($languageIds as $languageId){
			$star = $limit * ($page - 1);
			$desc_table = "goods_description_{$languageId}";
			$this->db_read->select( 'g.goods_id, goods_sn,goods_description' );
			$this->db_read->from( 'goods g' );
			$this->db_read->join( "{$desc_table} as gd", "g.goods_id = gd.goods_id and goods_complex != 1 and goods_description like '%desc_tmp%'");
			$this->db_read->limit( $limit, $star );
			$query = $this->db_read->get();
			$record = $query->result_array();
			foreach( $record as $info ){
				$proId = $info['goods_id'];
				$proInfo_sn = $info['goods_sn'];
				$proInfo_description = str_replace("<br><input type='hidden' value = '' name = 'desc_tmp'><br>",'',$info['goods_description']);
				$proInfo_attribute_list = $this->getGoodsAttrBySkuPage( array($proInfo_sn) , $languageId );
				$proInfo_attribute_desc_list = array();
				$proInfo_attribute_desc = '';
				foreach($proInfo_attribute_list as $proInfo_attribute_info){
					$name = $proInfo_attribute_info['name'];
					$value = $proInfo_attribute_info['value'];
					$unit = $proInfo_attribute_info['unit'];
					if(!empty($name) && !empty($value)){
						$unit_str = empty($unit)?'':"({$unit})";
						$str = "
							<li>
								<div class='attr_key'>
									{$name}&nbsp;{$unit_str}
								</div>
								<div class='attr_value'>
									{$value}
								</div>
							</li>";
						$proInfo_attribute_desc_list[] = $str;
					}
				}
				$proInfo_attribute_desc = '';
				if(count($proInfo_attribute_desc_list) > 0){
					$proInfo_attribute_desc = "
						<div class='attr_table'>
							<div class='partition_line'></div>
								<ul>
								".implode('',$proInfo_attribute_desc_list)."
								</ul>
							</div>
						</div>
					";
				}
				if(!empty($proInfo_attribute_desc)){
					$proInfo_attribute_desc .= $proInfo_description;
					$arr['goods_description'] = $proInfo_attribute_desc;
					$this->db_write->where('goods_id',$proId);
					$this->db_write->update($desc_table,$arr);
					$log_msg = "[$proId][$proInfo_sn][$languageId][1]\n";
					file_put_contents("/home/www/log_desc.txt",$log_msg,FILE_APPEND);
				} else {
					$log_msg = "[$proId][$proInfo_sn][$languageId][0]\n";
					file_put_contents("/home/www/log_desc.txt",$log_msg,FILE_APPEND);
				}
			}
		}
	}


	/*
	 * count 最大不超过50
	 */
	public function getRecentPromotionGoods( $count = 25  ) {
		if( $count > 50 ){
			$count = 50 ;
		}
		$cache_key = 'idx_good_recent_promotion_goods_%s';
		$cache_params = array($count);
		$result = $this->memcache->get( $cache_key,$cache_params );
		if( $result === FALSE || count( $result ) <= $count ){
			$this->db_read->select( 'goods_id,goods_sn,promote_start_date,promote_end_date' );
			$this->db_read->from( 'goods' );
			$this->db_read->where( 'is_delete !=' , 1 );
			$this->db_read->where( 'is_on_sale' , 1 );
			$this->db_read->where( 'goods_number >' , 0 );
			$this->db_read->where( 'promote_price !=' , '0' );
			$this->db_read->where( 'promote_start_date <=' , $_SERVER['REQUEST_TIME'] );
			$this->db_read->where( 'promote_end_date >' , $_SERVER['REQUEST_TIME'] );
			$this->db_read->where( 'promote_end_date <=' , ( $_SERVER['REQUEST_TIME'] + 86400 ) );
			$this->db_read->limit( $count*5 );
			$query = $this->db_read->get();
			$result = $query->result_array() ;
			if( count( $result ) <= $count ){
				$this->db_read->select( 'goods_id,goods_sn,promote_start_date,promote_end_date' );
				$this->db_read->from( 'goods' );
				$this->db_read->where( 'is_delete !=' , 1 );
				$this->db_read->where( 'is_on_sale' , 1 );
				$this->db_read->where( 'goods_number >' , 0 );
				$this->db_read->where( 'promote_price !=' , '0' );
				$this->db_read->order_by('last_update','desc');
				$this->db_read->limit( $count*5 );
				$query = $this->db_read->get();
				$result = $query->result_array() ;
			}

			$this->memcache->set( $cache_key, $result, $cache_params );
		}
		if( !empty( $result ) && is_array( $result ) ){
			shuffle($result);
			$result = array_splice( $result , 0 ,  $count );
		}else{
			$result = array();
		}
		return $result ;
	}
}

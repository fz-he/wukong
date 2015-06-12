<?php

namespace app\models;

use Yii;
use app\models\common\EbARModel as baseModel;
use app\components\helpers\HelpOther;
use app\components\helpers\HelpUrl;

/**
 * 分类管理model
 * 版本 v2
 * @auther qcn
 * @date 2014-8-18
 */
class Category extends baseModel {

	const MEM_KEY_PRO_CAT_INFO = 'proCatInfo%s%s';//proCatInfo{$product_id}{$$languageId} 产品分类信息的memcache缓存key
	const MEM_KEY_CAT_TEMP = 'catTemp%s%s';//catTemp{$catId}{$languageId}分类模板的memcache缓存key


	//定义操作表名 eb_pc_site:category
	private $_tableName = 'category';
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
	/**
	 * 获取分类到的信息
	 * @param  array  $fieldArray 字段
	 * @param  array  $whereArray 查询where条件
	 * @param  array  $orderBy    排序条件
	 * @param  string $groupBy    分组条件
	 * @param  array  $limit      limit条件 row + offset
	 * @return array 返回分类的信息
	 */
	public function getCategoryInfo( $fieldArray = array(), $whereArray = array(), $orderBy = '', $groupBy = '', $limit = array() ) {
		//缓存处理
		$memcacheKeyStrCode = array(md5(serialize($fieldArray) . serialize($whereArray) . serialize($orderBy) . $groupBy . serialize($limit)));
		$cacheKey = "idx_categorymodel_getcategoryinfo_%s";
		$result = $this->memcache->get($cacheKey,$memcacheKeyStrCode);
			
		//字段处理
		if($result === false) {
			$query = static::find();
			if( !empty($fieldArray) && is_array( $fieldArray ) && count($fieldArray) > 0 ){
				$query->select($fieldArray);
			}

			if ( !empty($whereArray) && is_array($whereArray) && count($whereArray) > 0){
				$where = '';
				foreach ($whereArray as $key => $value) {
					$where = $where  . ' and '   . $key . $value;
				}
				$where = substr( $where,  strpos( $where , 'and') + 3);	
				$query->where( $where );
			}
			
			if ( !empty($orderBy)  ){
				$query = $query->orderBy( $orderBy ); 
			}
			
			if(!empty($groupBy)) {
				$query->groupBy($groupBy);
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
				$query->offset( $offset )->limit( $rows );
			}
			$result = $query->asArray()->all();
			
			if ( !empty($result) ){
				$this->memcache->set($cacheKey,$result,$memcacheKeyStrCode);
			}		
		}

		return $result;
	}

	/**
	 * 获取子分类通过 分类id
	 * @return array
	 */
	public function getSubCategoryIdsById($categoryId) {
		$categoryId = intval($categoryId);
		$result = array();
		if( $categoryId > 0 ){
			//缓存处理
			$cacheKey = "idx_categorymodel_getcategoryinfo_%s";
			$memcacheKeyStrCode = array($categoryId);
			$result = $this->memcache->get($cacheKey,$memcacheKeyStrCode);

			//数据取出
			if($result === false) {
				$this->db_ebmaster_read->select('id');
				$this->db_ebmaster_read->from('category');
				$this->db_ebmaster_read->like('path',$categoryId);
				$query = $this->db_ebmaster_read->get();
				$list = $query->result_array();
				$result = extractColumn($list,'id');
				$this->memcache->set($cacheKey,$result,$memcacheKeyStrCode);
			}
		}
		return $result;
	}

	/**
	 * 创建分类树
	 * @param  [array]  $dataArray 分类信息数组
	 * @param  integer $parentId 父类id
	 * @return [array]
	 */
	public function buildTree($dataArray,$parentId = 0){
		$res = array();

		if(isset($dataArray[$parentId])){
			$res = $dataArray[$parentId];
		}

		foreach($res as $key => $record){
			$res[$key]['children'] = $this->buildTree($dataArray,$record['id']);
		}
		return $res;
	}

	/**
	 * 获取类别的父分类信息
	 * @param inc $categoryId 类别ID
	 * @param inc $languageId 多语言ID
	 * @param boolean $isParent 是否获得全部父分类
	 * @return array 返回父级分类信息
	 * @author lucas
	 */
	public function getParentCategoryInfo( $categoryId ,$languageId = '', $isParent = false ){
		if( empty($categoryId) ){
			return array();
		}

		//缓存处理
		if( empty($languageId) ){
			$cacheKey = empty($isParent) ? "idx_categorymodel_getparentcategoryinfo_%s" : "idx_categorymodel_getparentallcategoryinfo_%s";
			$cacheParams = array((int)$categoryId);
		}else{
			$cacheKey = empty($isParent) ? "idx_categorymodel_getparentcategoryinfo_%s_%s" : "idx_categorymodel_getparentallcategoryinfo_%s_%s";
			$cacheParams = array((int)$categoryId, $languageId);
		}
		$parentCategoryResult = $this->memcache->get($cacheKey,$cacheParams);

		//数据取出
		if($parentCategoryResult === false) {
			$this->db_ebmaster_read->select('p_id,path');
			$this->db_ebmaster_read->from('category');
			$this->db_ebmaster_read->where('id',(int)$categoryId);
			$this->db_ebmaster_read->where('status', 1);
			$this->db_ebmaster_read->limit( 1 );
			$query = $this->db_ebmaster_read->get();
			$categoryPidArr = $query->result_array();

			$parentCategoryResult = array();
			if( !empty( $categoryPidArr ) && is_array( $categoryPidArr ) ) {
				$categoryPidArr = current($categoryPidArr);
				if( $isParent === true ){
					$pathArr = explode('/', $categoryPidArr['path']);
					$parent_ids = array();
					foreach ($pathArr as $parent_id) {
						if($parent_id == $categoryId){
							break;
						}
						$parent_ids[] = $parent_id;
					}
				}else{
					$parent_ids = array( $categoryPidArr['p_id'] );
				}

				$this->db_ebmaster_read->select('id, name, url, type, image, price_grade, nav_image, nav_image_bg, nav_url, product_active_num');
				$this->db_ebmaster_read->from('category');
				$this->db_ebmaster_read->where_in('id',$parent_ids);
				$this->db_ebmaster_read->where('status', 1);
				$query = $this->db_ebmaster_read->get();
				$result = $query->result_array();
				$parentCategoryResult = reindexArray( $result, 'id');

				if( !empty( $languageId ) ){
					$parentCategoryIds = array();
					foreach ($result as $key => $value) {
						$parentCategoryIds[] = $value['id'];
					}
					$this->db_ebmaster_read->select('category_id,name');
					$this->db_ebmaster_read->from('category_desc');
					$this->db_ebmaster_read->where_in('category_id',$parentCategoryIds);
					$this->db_ebmaster_read->where('language_id',$languageId);
					$query = $this->db_ebmaster_read->get();
					$categoryDescResult = $query->result_array();
					$parentCategoryDesc = array();
					foreach ($categoryDescResult as $key => $value) {
						$parentCategoryDesc[$value['category_id']] = $value['name'];
					}

					foreach ($parentCategoryResult as $key => $value) {
						if( !empty($parentCategoryDesc[$key]) ){
							$parentCategoryResult[$key]['name'] = $parentCategoryDesc[$key];
						}
					}
				}

				$this->memcache->set($cacheKey,$parentCategoryResult,$cacheParams);
			}
		}

		return $parentCategoryResult;
	}

	/**
	 * 获取此分类推荐的商品PID
	 * @param inc $categoryId 类别ID
	 * @param boolean $is_sonCate false 是否查询子分类
	 * @return array 返回此分类推荐的商品PID
	 * @author lucas
	 */
	public function getCategoryRecommendGoodsIds( $categoryId, $is_sonCate = false ){
		if( empty($categoryId) ){
			return array();
		}

		//缓存处理
		$cache_key = "idx_categorymodel_getcategoryrecommendgoodsids_%s";
		$cache_params = array($categoryId);
		$product_ids = $this->memcache->get($cache_key,$cache_params);
		//数据取出
		if($product_ids === false) {
			if( $is_sonCate === true ){
				$categoryId = $this->getSubCategoryIdsById($categoryId);
			}else{
				$categoryId = array( $categoryId );
			}

			$product_ids = array();
			$this->db_ebmaster_read->select('product_id');
			$this->db_ebmaster_read->from('product_recommend');
			$this->db_ebmaster_read->where_in('category_id',$categoryId);
			$this->db_ebmaster_read->where('status',1);
			$this->db_ebmaster_read->order_by( 'sort' , 'desc' );
			$query = $this->db_ebmaster_read->get();
			$list = $query->result_array();
			$product_ids = extractColumn($list,'product_id');

			$this->memcache->set($cache_key,$product_ids,$cache_params);
		}

		return $product_ids;
	}

	/**
	 * 根据分类id获取其id_path 对应的所有名称(数组的形式)。
	 * @param type $cids
	 * @param type $languageId
	 * @return array
	 * @author Terry
	 */
	public function getParentCateInfoListByIds($cids,$languageId=1){

		$return = array();
		$cidsArr = is_array($cids)?$cids:array($cids);
		$cateInfoList = $this->getCateInfoById($cidsArr, $languageId);
		foreach($cateInfoList as $cateInfo){
			$allParentCateInfo = $this->getParentsCateInfoList($cateInfo['path'], $languageId);
			$return[$cateInfo['id']] = extractColumn($allParentCateInfo, 'name');
		}

		return $return;
	}

	/**
	 * Get the category infomation of category id.(support batch)
	 * @param int|array $categoryIds #The category id or a list of category ids in array.
	 * @param int $languageId
	 * @return array $return #The category infomation of the category id.
	 * @author Terry
	 */
	public function getCateInfoById($categoryIds, $languageId = 1) {
		$return = array();
		$categoryIds = is_array($categoryIds)?$categoryIds:array($categoryIds);//如果是单个category id，这里同样处理成数组，这样方便后面处理。
		$noCacheCatIds = array();//用以收集没有缓存的category id。
		foreach ($categoryIds as $categoryId) {//从memcache获取缓存了的分类信息，并收集未缓存的分类id.
			$categoryInfo = $this->memcache->get(self::MEM_KEY_PRO_CAT_INFO, array($categoryId, $languageId));
			if ($categoryInfo === false) {
				$noCacheCatIds[] = $categoryId;
			} else {
				$return[$categoryId] = $categoryInfo;
			}
		}
		if (!empty($noCacheCatIds)) {//从数据库查询未缓存分类id对应的分类信息。
			$categorysBasicInfo = reindexArray($this->db_ebmaster_read->from('category')->where_in('id', $noCacheCatIds)->get()->result_array(), 'id');
			$categorysDesc = reindexArray($this->db_ebmaster_read->from('category_desc')->where_in('category_id', $noCacheCatIds)->where('language_id', $languageId)->get()->result_array(), 'category_id');

			foreach ($categorysBasicInfo as $categoryBasicInfo) {
				$cid = $categoryBasicInfo['id'];
				$mixCatInfo = $categoryBasicInfo;
				$mixCatInfo +=isset($categorysDesc[$cid])?$categorysDesc[$cid]:array();
				$mixCatInfo['name'] = isset($categorysDesc[$cid])?$categorysDesc[$cid]['name']:'';
				$this->memcache->set(self::MEM_KEY_PRO_CAT_INFO, $mixCatInfo, array($cid,$languageId));
				$return[$cid] = $mixCatInfo;
			}
		}
		return $return;
	}

	/**
	 * Get all parent categorys infomation of one category by id path.
	 * @param type $categoryIdPath #The id path of category.
	 * @param type $languageId
	 * @return array $list #The parent category infomation array.
	 * @author Terry
	 */
	public function getParentsCateInfoList($categoryIdPath, $languageId) {
		$categoryIds = explode('/', $categoryIdPath);
		$categoryList = $this->getCateInfoById($categoryIds, $languageId);

		$list = array();
		foreach ($categoryIds as $categoryId) {
			if (!isset($categoryList[$categoryId])) {
				continue;
			}
			$list[] = $categoryList[$categoryId];
		}
		return $list;
	}

	//-------------Narrow search  相关方法！ ------start--------------
	/**
	 * 根据分类ID获取分类的显示类型
	 * @param $categoryId
	 *
	 * @return int $rs //展示类型(1:默认 2:NS 3:分类模板)
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getCategoryDisplayByCategoryId( $categoryId ){
		//初始化默认展示类型
		$displayType =1 ;
		if( $categoryId > 0 ){
			$cacheKey = 'idx_category_display_type_%s';
			$displayType = $this->memcache->get( $cacheKey , $categoryId );
			if($displayType === FALSE  ){
				// get DB
				$this->db_ebmaster_read->select('type_display');
				$this->db_ebmaster_read->from('category');
				$this->db_ebmaster_read->where('id',$categoryId);
				$this->db_ebmaster_read->where('status', 1);
				$this->db_ebmaster_read->limit( 1 );
				$query = $this->db_ebmaster_read->get();
				$result = $query->result_array();
				//格式化为int类型
				$displayType = (int)$result[0]['type_display'] ;
				if( !in_array( $displayType , array( 1,2,3 ) ) ){
					$displayType = 1 ;
				}
				//设置mc
				$this->memcache->set( $cacheKey , $displayType , $categoryId );
			}
		}

		return $displayType ;
	}

	/**
	 * @todo
	 * 获取某一个分类对应的 Narrow Search  初始化列表
	 * @param int $categoryId 分类ID
	 * @param int $langId 默认1（US）
	 *
	 * @return array $result 属性ID, 属性值ID ,属性组ID,对应关系
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getNarrowSearchByCategoryId( $categoryId , $langId=1 ){
		$result = array('info' => array() , 'display_count' => 0 , 'attr_group_info' => array() );
		if( ( $categoryId = (int) $categoryId ) > 0 ){
			$cacheKey = 'idx_category_ns_catid_%s_%s' ;
			$resultMc = $this->memcache->get( $cacheKey , array($categoryId, $langId) );
			if( $resultMc === FALSE ){
				$resultTmp = $this->_getProductattrValueCategoryByCategoryId( $categoryId , $langId );
				$result['info'] = $resultTmp['attr_relation_info'] ;
				$result['attr_value_ids'] = $resultTmp['attr_value_ids'] ;
				$result['display_count'] = $resultTmp['display_count'] ;
				$result['attr_group_info'] = $resultTmp['attr_group_info'];
				//设置mc
				$this->memcache->set( $cacheKey , $result , array($categoryId, $langId) );
			}else{
				$result = $resultMc;
			}

			//获取每一个Narrow Search 属性 对应的商品个数 计算
			if( is_array( $result['info'] ) && is_array( $result['attr_value_ids'] ) && count( $result['info'] ) > 0 && count( $result['attr_value_ids'] ) > 0 ){
				$result['info'] = $this->getNarrowSearchPidCountByAttrValueIds ( $categoryId , $result['attr_value_ids'] ,  $result['info'] );

			}
		}
		return $result;
	}

	/**
	 * 获取 Narrow Search 每一个属性值下面对应的商品个数
	 * @param int $categoryId  分类ID
	 * @param array $attrValueIds	展示的有效属性值ID
	 * @param array $narrowSearchInfo narrowSearch 展示的整体结构
	 * @param array $hasAttr 属性
	 *
	 * @return array $result
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getNarrowSearchPidCountByAttrValueIds( $categoryId , $attrValueIds =array() , $narrowSearchInfo = array() ,$hasAttr = array() ){
		$result = array() ;
		if( ( $categoryId > 0 ) && count( $attrValueIds )>0 && count( $narrowSearchInfo ) > 0 ){
			//获取分类下 每一个属性值对应的pid 信息
			$narrowSearchPidDbByAttrValueIds = $this->_getNarrowSearchPidDbByAttrValueIds( $categoryId , $attrValueIds );
			//循环过来每一个属性值对应的 pid 个数
			foreach ( $narrowSearchInfo as $attrId => &$groupInfo ){
				$attrHavePidsOFF =  FALSE ;
				//判断显示属性值
				$displayCountGroup = $havetoHideCount =  0 ;
				foreach ( $groupInfo['group_info'] as $groupId => &$info ){
					$pidsArr = array();
					foreach ( $info['content'] as $valueId ){
						if( isset( $narrowSearchPidDbByAttrValueIds[ $valueId ] ) && count( $narrowSearchPidDbByAttrValueIds[ $valueId ] ) > 0 ){
							if( count( $pidsArr ) > 0 ){
								foreach ( $narrowSearchPidDbByAttrValueIds[ $valueId ] as $pidKey => $marketPriceValue ){
									if( !isset ( $pidsArr [ $pidKey ] ) ){
										$pidsArr [ $pidKey ] = $marketPriceValue ;
									}
								}
							}else{
								$pidsArr = $narrowSearchPidDbByAttrValueIds[ $valueId ] ;
							}
						}
					}

					if( count( $pidsArr ) > 0 ){
						$info['pids_count'] = count( $pidsArr );
						$info['pids'] = $pidsArr ;
						$attrHavePidsOFF = TRUE ;
						( (int)$info['display'] === 0 ) ? (++$havetoHideCount ): ( ++$displayCountGroup ) ;
					}else{
						unset( $groupInfo['group_info'][ $groupId ] );
					}
				}

				if( $attrHavePidsOFF === FALSE ){
					unset( $narrowSearchInfo[ $attrId ] );
					continue;
				}

				//判断显示不存在 或者总数小于5全部展开
				if( isset( $groupInfo['group_info'] ) && !empty( $groupInfo['group_info'] ) && ( ( $displayCountGroup === 0 ) || ( count(  $groupInfo['group_info'] ) < 5 ) ) ){
					$narrowSearchInfo[ $attrId ]['havetoHideOpen'] = $narrowSearchInfo[ $attrId ]['havetoHide'] = FALSE ;
					foreach ( $groupInfo['group_info'] as  &$vaule ){
						if( (int)$vaule['display'] === 0 ){
							$vaule['display'] = 1;
						}
					}
				}elseif( ( $havetoHideCount === 0 ) && ( $narrowSearchInfo[ $attrId ]['havetoHide'] === TRUE ) ){
					$narrowSearchInfo[ $attrId ]['havetoHide'] = FALSE;
				}
			}

			$result = $narrowSearchInfo ;
		}
		return $result ;
	}

	/**
	 * 获取某一个分类下面 NarrowSearch  有效的属性值对应的pid 信息。
	 * @param int $categoryId  分类ID
	 * @param array $attrValueIds	展示的有效属性值ID
	 *
	 * @return array $result
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	private function _getNarrowSearchPidDbByAttrValueIds( $categoryId , $attrValueIds =array() ){
		$dbData = array();
		//判断传的分类ID 或者有效的属性值ID 不为空
		if( ( $categoryId > 0 ) && ( count( $attrValueIds ) > 0 ) ){
			//由于一个分类下的有效属性值是不变的 所以 属性值 不用写入mc的 key 中
			$cacheKey = 'narrowSearchPidDbByAttrValueIds_%s' ;
			$dbData = $this->memcache->get( $cacheKey , $categoryId );
			if( $dbData === FALSE || !is_array( $dbData ) ){
				$dbData = array();
				//获取pid信息 根据属性值ID;
				$this->db_ebmaster_read->select('product_id,attribute_value_id'); //设置取出的商品的字段
				$this->db_ebmaster_read->from('attribute_product');
				$this->db_ebmaster_read->where_in('attribute_value_id', $attrValueIds );
				$this->db_ebmaster_read->where('status',1);
				$query = $this->db_ebmaster_read->get();
				$narrowSearchProductIdList = $query->result_array();
				if( !empty( $narrowSearchProductIdList ) && count( $narrowSearchProductIdList ) > 0 ){
					$narrowSearchProductIds = extractColumn( $narrowSearchProductIdList , 'product_id' );
					//获取此分类的所有分类 取出指定分类的子分类
					$productObj = ProductModel::getInstanceObj();
					$categoryIds = $productObj->getProductCategorySub( $categoryId );
					//取出销售分类的商品Pid
					$proIdsFromCat = $productObj->getSaleCategoryProduct( $categoryIds );

					$this->db_ebmaster_read->select('id,market_price');
					$this->db_ebmaster_read->from('product');
					$this->db_ebmaster_read->where_in('id', $narrowSearchProductIds ); //有属性值的商品PID
					if( !empty($proIdsFromCat) ){
						$where_tmp = '( `category_id` IN ( ' . implode( ',' , $categoryIds ) . ') OR `id`  IN ( ' . implode( ',' , $proIdsFromCat ) . ' ) )';
						$this->db_ebmaster_read->where( $where_tmp );
					} else {
						$this->db_ebmaster_read->where_in('category_id',$categoryIds);
					}
					$this->db_ebmaster_read->where('status',1); //商品的状态 1是上架 0是下架
					$this->db_ebmaster_read->where('price !=','0.00');
					$this->db_ebmaster_read->where('market_price !=','0.00');
					$validQuery = $this->db_ebmaster_read->get()->result_array();
					if( !empty( $validQuery ) && count( $validQuery )>0 ){
						$validQuery = reindexArray( $validQuery , 'id' );
						foreach ( $narrowSearchProductIdList as $v ){
							$pidTmp = (int)$v['product_id'];
							if( isset( $validQuery[ $pidTmp ] ) ) {
								$valueIdByAttr = (int)$v['attribute_value_id'];
								$dbData[ $valueIdByAttr ][ $pidTmp ] = $validQuery[ $pidTmp ]['market_price'];
							}
						}
					}
				}
				$this->memcache->set( $cacheKey , $dbData , $categoryId );
			}
		}
		return $dbData ;
	}

	/**
	 * 获取分类ID对应的属性ID，属性值ID ,属性组ID,对应关系
	 * @param int $categoryId
	 * @param int $langId 默认1（US）
	 *
	 * @return array $result
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	private function _getProductattrValueCategoryByCategoryId( $categoryId , $langId=1  ){
		//初始化变量
		$result = array(
			'attr_category'=> array(),//属性详细信息
			'attr_info'=> array(),//属性详细信息
			'attr_value_ids'=> array(),//此分类下有多少 属性值id被展示
			'attr_group_info'=> array(),//属性组具体信息
			'attr_relation_info'=> array(),//对应关系
			'display_count' => 0 ,//分类下  属性显示的数量
		);
		$attributeIds = array();
		//判断分类合法否

		if( ( $categoryId = (int)$categoryId ) > 0 ){
			// get DB 获取分类下面的属性ID
			$this->db_ebmaster_read->select('`attribute_id`,`type`,`sort`,`display` ');
			$this->db_ebmaster_read->from('attribute_category');
			$this->db_ebmaster_read->where('category_id', $categoryId );
			$this->db_ebmaster_read->where('status', 1);
			$this->db_ebmaster_read->order_by('display', 'desc');
			$this->db_ebmaster_read->order_by('sort', 'desc');
			$query = $this->db_ebmaster_read->get();
			$resultSql = $query->result_array();

			if( !empty( $resultSql ) && is_array( $resultSql ) ){
				foreach ( $resultSql as $v ){
					//过滤脏数据
					$attributeIdTmp = (int) $v['attribute_id'] ;
					if( $attributeIdTmp <= 0 ){
						continue;
					}
					$attributeIds[ $attributeIdTmp ] = $attributeIdTmp ;
					$result['attr_category'][ $attributeIdTmp ] = $v ;
				}

				//判断属性ID 是否存在
				if( count( $attributeIds ) > 0 ){
					//获取属性ID
					$result['attr_info'] = $this->getAttributeInfo( $attributeIds , $langId );
					if( !empty( $result['attr_info'] )  && is_array( $result['attr_info'] ) ){
						//获取属性组信息
						$getAttributeGroupInfoResult = $this->getAttributeGroupInfo( $categoryId , array_keys( $result['attr_info'] ) , $langId );
						$result['attr_group_info'] = $getAttributeGroupInfoResult['attrGroupArr'];
						$result['attr_value_ids'] = $getAttributeGroupInfoResult['attributeValueIds'];
						foreach ( $result['attr_info'] as $k => $v ){
							if( isset( $result['attr_group_info'][ $k ] ) && is_array( $result['attr_group_info'][ $k ] ) && ( count( $result['attr_group_info'][ $k ] ) > 0 ) ){
								//排序
								$v[ 'categorySort' ] = $result['attr_category'][ $k ][ 'sort' ] ;
								//显示状态(1默认展开 0默认隐藏)
								$v[ 'display' ] = (int)$result['attr_category'][ $k ][ 'display' ] ;
								//类型(1单选 2多选)
								$v[ 'type' ] = (int)$result['attr_category'][ $k ][ 'type' ] ;
								//是否有隐藏的属性值
								$v['havetoHide'] = FALSE ;
								//判断属性下面是否用隐藏的属性值
								if( isset( $result['attr_group_info'][ $k ] ) && !empty( $result['attr_group_info'][ $k ] ) && is_array( $result['attr_group_info'][ $k ] )){
									foreach ( $result['attr_group_info'][ $k ] as $groupTmp ){
										if( (int)$groupTmp['display'] === 0 ){
											$v['havetoHide']  = TRUE ;break;
										}
									}
								}
								//是否吧隐藏的属性值 展开 当havetoHide 为TRUE  havetoHideOpen TRUE 则打开。
								$v['havetoHideOpen'] = FALSE ;
								$v[ 'group_info' ] = $result['attr_group_info'][ $k ] ;
								$result['attr_relation_info'][ $k ] = $v ;
								if( $v[ 'display' ] === 1 ){
									$result[ 'display_count' ]++;
								}
							}
						}
					}
				}
			}
		}
		return $result ;
	}

	/**
	 * 批量获取属性名称key为attrId
	 * @param array $attrIds 属性ID
	 * @param inc $languageId 语言ID
	 * @return array 返回属性名称数组
	 * @author lucas / bryan
	 */
	protected function _getAttributeTitle( $attrIds , $languageId = 1 ){
		if( empty($attrIds) ){
			return array();
		}
		$this->db_ebmaster_read->select( 'attribute_id,title' );
		$this->db_ebmaster_read->from( 'attribute_lang' );
		$this->db_ebmaster_read->where_in( 'attribute_id', $attrIds );
		$this->db_ebmaster_read->where( 'status', 1 );
		$this->db_ebmaster_read->where( 'language_id', $languageId );
		$query = $this->db_ebmaster_read->get();
		$attrTitleResult = $query->result_array();
		$attrTitleLanguage = array();
		if( count( $attrTitleResult ) > 0 ){
			foreach ($attrTitleResult as  $value) {
				$attrTitleLanguage[ (int)$value['attribute_id'] ] = $value['title'];
			}
		}
		return $attrTitleLanguage;
	}

	/**
	 *
	 * 批量获取属性 以及单位 多语言 根据属性ID
	 * @param array $attrIds	//属性ID
	 * @param int $languageId	//语言ID
	 *
	 * @return array $result	//结果数据
	 *
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getAttributeInfo( $attrIds , $languageId = 1 ){
		$attributeInfos = array();
		if( !empty( $attrIds ) && is_array( $attrIds ) ){
			//获取属性的多语言
			$attributeTitle = $this->_getAttributeTitle( $attrIds , $languageId );
			//获取属性的单位和排序
			$this->db_ebmaster_read->select( 'id,name,unit,sort' );
			$this->db_ebmaster_read->from( 'attribute' );
			$this->db_ebmaster_read->where_in( 'id', $attrIds );
			$this->db_ebmaster_read->where( 'status', 1 );
			$this->db_ebmaster_read->order_by('sort', 'desc');
			$query = $this->db_ebmaster_read->get();
			$attrResult = $query->result_array();
			if( count(  $attrResult ) > 0 ){
				$attributeInfo = array();
				foreach ( $attrResult as $value ) {
					$id = (int)$value['id'];
					$attributeInfo [ $id ] = $value;
					$attributeInfo [ $id ]['lang'] = isset( $attributeTitle[ $id ] ) ? eb_htmlspecialchars(trim( $attributeTitle[ $id ] )) : eb_htmlspecialchars(trim( $value['name'] ));
				}

				//调整顺序
				foreach ( $attrIds as $attrId ){
					$attrId = (int)$attrId ;
					if( isset( $attributeInfo[ $attrId ] ) ){
						$attributeInfos[ $attrId ] = $attributeInfo[ $attrId ];
					}
				}
			}
		}

		return $attributeInfos;
	}

	/**
	 * 批量获取商品属性组信息
	 * @param array $attrGroupIds
	 * @param inc $languageId 语言ID
	 * @return array 返回属性名称数组
	 * @author lucas
	 */
	public function getAttributeGroupInfo( $categoryId , $attributeIds, $languageId = 1 ){
		if( ( $categoryId <=0 ) || ( !is_array( $attributeIds ) ) || ( count($attributeIds) <= 0 ) ){
			return array();
		}
		$attrGroupArr = $attributeValueIds = array();
		$this->db_ebmaster_read->select( 'id,attribute_id,category_id,name,lang,content,sort,display' );
		$this->db_ebmaster_read->from( 'attribute_value_group' );
		$this->db_ebmaster_read->where_in( 'category_id', $categoryId );
		$this->db_ebmaster_read->where( 'status', 1 );
		$this->db_ebmaster_read->where_in( 'attribute_id', $attributeIds );
		$this->db_ebmaster_read->order_by('display', 'desc');
		$this->db_ebmaster_read->order_by('sort', 'desc');
		$query = $this->db_ebmaster_read->get();
		$groupResult = $query->result_array();

		if( !empty( $groupResult ) && is_array( $groupResult ) ){
			//判断属性值组的状态是否合法
			foreach ( $groupResult as $v ){
				$attributeValueIds = array_merge( $attributeValueIds , explode( ',' , $v['content'] ) );
			}
			//去重复
			$attributeValueIds = array_unique( $attributeValueIds );
			$this->db_ebmaster_read->select( 'id' );
			$this->db_ebmaster_read->from( 'attribute_value' );
			$this->db_ebmaster_read->where( 'status', 1 );
			$this->db_ebmaster_read->where_in( 'id', $attributeValueIds );
			$query = $this->db_ebmaster_read->get();
			$valueIdsResult = $query->result_array();
			$attributeValueIds = extractColumn( $valueIdsResult , 'id' );
			foreach ( $groupResult as $v ){
				//求交集
				$valueIdsTmp = array_intersect( explode( ',' , $v['content'] ) , $attributeValueIds );
				//判断交集为空则不显示
				if( !empty( $valueIdsTmp ) ){
					$attributeId = (int)$v[ 'attribute_id' ];
					$langTmp = json_decode( $v[ 'lang' ] , TRUE);
					$attrGroupArr[ $attributeId ][ (int)$v['id'] ] =array(
						'id' =>(int)$v['id'] ,
						'attribute_id' => $attributeId ,
						'category_id' =>(int)$v['category_id'] ,
						'name' =>trim( $v['name'] ) ,
						'lang' => isset( $langTmp[ $languageId ] ) ? eb_htmlspecialchars(trim( $langTmp[ $languageId ] )) : eb_htmlspecialchars(trim( $v['name'] )) ,
						'content' => $valueIdsTmp ,
						'sort' => (int)$v['sort'] ,
						'display' => (int)$v['display'] ,
						'selected' => 0 ,	////是否被选择 此属性 默认为0
						'pids_count' => 0 ,
						'pids' => array(),
					);
				}
			}
		}
		return array( 'attrGroupArr' => $attrGroupArr , 'attributeValueIds' => $attributeValueIds  );
	}


	//-------------Narrow search  相关方法！ ------end--------------

	/**
	 * Get category template of product by it's idpath.
	 * @param type $categoryIdPath
	 * @param type $languageId
	 * @return str #The category template of product.
	 * @author Terry
	 */
	public function getCategoryTemplateOfProduct($categoryIdPath,$languageId){

		$return = '';
		$parentCatIds = explode('/',$categoryIdPath);
		$parentCatIdsReverse = array_reverse($parentCatIds);
		foreach($parentCatIdsReverse as $catId){
			$catTemplate = current($this->getCategoryTemplate($catId, array($languageId)));
			if($catTemplate){
				$return = $catTemplate;
				break;
			}
		}
		return $return;
	}

	public function getCategoryTemplate($cids,$params,$cache = TRUE){

		if ($cache) {
			$return = $this->memcache->ebMcFetchData($cids,self::MEM_KEY_CAT_TEMP,array($this,'getCategoryTemplate'),$params);
		}else{
			$return = array();
			$catTemplistRes = $this->db_ebmaster_read->select('content,category_id')->from('category_template')->where_in('category_id',$cids)->where('language_id',$params[0])->where('status',1)->where('content !=','')->get();
			if ($catTemplistRes) {
				$catTemplist = $catTemplistRes->result_array();
				$catTempListReindex = reindexArray($catTemplist, 'category_id');
				foreach ($cids as $cid) {
					$return[$cid] = isset($catTempListReindex[$cid]) ? $catTempListReindex[$cid]['content'] : '';
				}
			}
		}
		return $return;
	}

	/**
	 * 获取分类也顶部ad
	 * @param integer $categoryLd 分类id
	 * @author qcn qianchangnian@hofan.cn
	 * @return array
	 */
	public function getTopCategoryBanner ( $categoryLd ) {
		$cacheKey = "idx_category_gettopcategorybanner_%s";
		$result = $this->memcache->get($cacheKey,array($categoryLd));
		if ( $result === false) {
			$this->db_read->from('operation_topic');
			$this->db_read->where('parent_cat_id',$categoryLd);
			$this->db_read->order_by('sort','asc');
			$this->db_read->limit(5);
			$query = $this->db_read->get();
			$result = $query->result_array();
			$this->memcache->set($cacheKey,$result,array($categoryLd));
		}
		return $result;
	}

	/**
	 * 分类页面左侧导航
	 * @param integer $category 当前分类信息
	 * @param integer $languageId 语言id
	 * @return array
	 */
	public function getCatRelatedDataByCat($category, $languageId) {
		$relatedTree = array();
		$type = '';

		//判断出当前分类级数
		$idPathArr = explode('/', $category['path']);
		$levelCount = count($idPathArr);
		switch ($levelCount) {
			case 1:// Level 1 category
				$type = 1;
				$categoryId = current($idPathArr);//取出一级分类id
				$childCatList = $this->getCategoryListByCategory($categoryId, $languageId);
				foreach ($childCatList as $childCat) {
					if($childCat['product_active_num']<=0){
						continue;
					}
					$relatedTree[] = array(
						'cat_name' => eb_htmlspecialchars($childCat['name']),
						'product_active_num' => $childCat['product_active_num'],
						'url_path' => $childCat['url'],
						'child' => $this->_getFormatRelatedChildCatTree($childCat['id'], $languageId)
					);
				}
				break;
			case 2:// Level 2 category.
				$type = 2;
				if($category['product_active_num']<=0){
					$relatedTree = array();
					break;
				}
				$relatedTree[] = array(
					'cat_name' => $category['name'],
					'product_active_num' => $category['product_active_num'],
					'url_path' => $category['url'],
					'child' => $this->_getFormatRelatedChildCatTree($category['id'], $languageId),
					'current' => TRUE
				);
				$parentCatId = $idPathArr[0];
				$otherCatList = $this->getCategoryListByCategory($parentCatId, $languageId);
				foreach ($otherCatList as $cat) {
					if ($cat['id'] != $category['id'] && $cat['product_active_num']>0) {
						$relatedTree[] = array(
							'cat_name' => eb_htmlspecialchars($cat['name']),
							'product_active_num' => $cat['product_active_num'],
							'url_path' => $cat['url']
						);
					}
				}
				break;
			default:
				$type = 3;
				$parentCatId = $idPathArr[$levelCount-2];
				if ($parentCatId) {
					$parentCategory = current($this->getCateInfoById($parentCatId, $languageId));
					if($parentCategory['product_active_num']<=0){
						$relatedTree = array();
						break;
					}
					$catList = $this->getCategoryListByCategory($parentCatId, $languageId);
					$childCatListFormat = array();
					foreach ($catList as $cat) {
						if($cat['product_active_num']<=0){
							continue;
						}
						$childCatListFormat[] = array(
							'cat_name' => eb_htmlspecialchars($cat['name']),
							'product_active_num' => $cat['product_active_num'],
							'url_path' => $cat['url'],
							'current' => ($cat['id'] == $category['id'])?TRUE:FALSE
						);
					}
					$relatedTree[] = array(
						'cat_name' => isset($parentCategory['name']) ? eb_htmlspecialchars($parentCategory['name']):'',
						'product_active_num' => $parentCategory['product_active_num'],
						'url_path' => isset($parentCategory['url']) ? $parentCategory['url']:'',
						'child' => $childCatListFormat
					);
				} else {
					$relatedTree = array();
				}
				break;
		}
		return array('relatedTree' => $relatedTree, 'type' => $type);
	}

	/**
	 * 通过id路径获取父类分类信息
	 * @param string categoryIdPath 分类的id路径
	 * @param integer $languageId 语言id
	 * @author qcn qianchangnain@hofan.cn
	 * @return array
	 */
	public function getParentsCategoryList($categoryIdPath,$languageId) {
		//分割分类id路径为分类id数组
		$categoryLds = explode('/',$categoryIdPath);
		//根据分类id数组取出分类的信息
		$categoryList = $this->getCateInfoById($categoryLds,$languageId);
		$categoryList = reindexArray($categoryList,'id');

		$list = array();
		foreach($categoryLds as $categoryLd){
			if(!isset($categoryList[$categoryLd])) {
				continue;
			}
			$list[] = $categoryList[$categoryLd];
		}
		return $list;
	}

	/**
	 * 获取父类ID根据PID
	 * @param array $pids 商品ID
	 * @return array 返回父ID列表
	 * @author lucas
	 */
	public function getParentCategoryIdByPid( $pids ){
		if( empty($pids) ){
			return array();
		}
		if( !is_array( $pids ) ){
			$pids = array( $pids );
		}

		//缓存处理
		$cacheKey = "parentCatIdByPid_%s";
		$pidsMcKey = md5(implode('_', $pids));
		$cacheParams = array( $pidsMcKey );
		$list = $this->memcache->get($cacheKey,$cacheParams);
		//数据取出
		if( $list === false || !is_array( $list ) ) {
			$this->db_ebmaster_read->select('id, category_id,path');
			$this->db_ebmaster_read->from('product');
			$this->db_ebmaster_read->where_in('id', $pids);
			$this->db_ebmaster_read->where('status', 1);
			$this->db_ebmaster_read->where('category_id > ', 0 );
			$query = $this->db_ebmaster_read->get();
			$listSql = $query->result_array();
			$list = array();
			if( !empty( $listSql ) && is_array( $listSql ) ){
				foreach( $listSql  as $value ) {
					$pid = (int)$value['id'] ;
					if( empty( $value['path'] ) ){
						$pathArr = array( (int)$value['category_id'] );
					}else{
						$pathArr = explode( '/' , $value['path'] );
					}
					$list[ $pid ] = $pathArr;
				}
			}
			//将取出的数据写入缓存
			$this->memcache->set( $cacheKey, $list, $cacheParams );
		}

		return $list;
	}

	/**
	 * 获取所有要展示的分类信息
	 * @param  integer  $languageId 语言id
	 * @author qcn
	 * @return array
	 */
	public function getShownCategory($languageId) {
		$cache_key = "idx_get_shown_category_%s";
		$cache_params = array($languageId);
		$list = $this->memcache->get($cache_key,$cache_params);
		if($list === false){
			//取出所有的页面展示的分类
			$this->db_ebmaster_read->select('id,p_id,name,url,type,path,image,product_active_num');
			$this->db_ebmaster_read->from('category');
			$this->db_ebmaster_read->where('status',1);
			$this->db_ebmaster_read->where('name !=','');
			$this->db_ebmaster_read->where('product_active_num >',0);
			$this->db_ebmaster_read->where('url !=','');
			$this->db_ebmaster_read->where('id >',15000);
			$this->db_ebmaster_read->order_by('sort','desc');
			$query = $this->db_ebmaster_read->get();
			$list = $query->result_array();

			//取出所有的分类的多语言信息
			$this->db_ebmaster_read->select('category_id,name');
			$this->db_ebmaster_read->from('category_desc');
			$this->db_ebmaster_read->where('language_id',$languageId);
			$this->db_ebmaster_read->where('name !=','');
			$this->db_ebmaster_read->group_by('category_id');
			$query = $this->db_ebmaster_read->get();
			$multilingual = $query->result_array();

			//分类的id为分类的多语言信息数组的key
			$multilingual = reindexArray($multilingual,'category_id');
			foreach($list as $key => $record){
				if(!isset($multilingual[$record['id']])){
					unset($list[$key]);
					continue;
				}
				$list[$key]['multilingual_name'] = $multilingual[$record['id']]['name'];
			}
			$list = array_values($list);
			$this->memcache->set($cache_key,$list,$cache_params);
		}

		return $list;
	}

	/**
	 * 更新分类下面的商品数 默认1小时执行一次 默认1小时
	 * @param null
	 * @return array
	 * @author lucas
	 */
	public function updateCategoryProductNumber(){
		set_time_limit ( 0 );
		$sql = "UPDATE `category` AS x SET x.`product_active_num` = ( SELECT count( a.`id` )
FROM `product` AS a
WHERE a.`path` LIKE CONCAT( '%', x.`id`, '%' )
AND a.`status` =1
AND a.`price` != '0.00'
AND a.`market_price` != '0.00'
) WHERE x.`status` =1 ";
		$result = $this->db_ebmaster_write->query( $sql );
		if( empty( $result ) ){
			return FALSE;
		}

		$this->db_ebmaster_read->select(' `pid` , `category_id` ') ;
		$this->db_ebmaster_read->from('category_product');
		$this->db_ebmaster_read->where( 'status' , 1 ) ;
		$rows = $this->db_ebmaster_read->get()->result_array();
		//过滤出PID
		$pidTmp = array();
		foreach ( $rows as $v ){
			$pid = (int) $v['pid'];
			$pidTmp[ $pid ] = $pid ;
		}
		$this->db_ebmaster_read->select('id') ;
		$this->db_ebmaster_read->from('product');
		$this->db_ebmaster_read->where_in( 'id' , $pidTmp ) ;
		$this->db_ebmaster_read->where( 'status' , 1 ) ;
		$this->db_ebmaster_read->where('price !=','0.00');
		$this->db_ebmaster_read->where('market_price !=','0.00');
		$rowsPid = $this->db_ebmaster_read->get()->result_array();
		$pids = array();
		foreach ( $rowsPid as $v){
			$pid = (int) $v['id'];
			$pids[ $pid ] = $pid ;
		}
		//格式化数据
		$result = array();
		foreach ( $rows as $v ) {
			$categoryId = (int) $v['category_id'] ;
			$pid = (int) $v['pid'];
			if( isset( $pids[ $pid ] ) && ( $categoryId > 0 ) && ( $pid > 0 ) ){
				isset( $result[ $categoryId ] ) ? $result[ $categoryId ]++ : $result[ $categoryId ] = 1 ;
			}
		}

		if( !empty( $result ) ){
			//获取父分类ID
			$rows = $this->getCategoryPathByCatId( array_keys( $result ) );
			$parentCatIdByPid = array();
			if( !empty( $rows ) ){
				foreach( $rows  as $value ) {
					$cid = (int)$value['id'] ;
					if( empty( $value['path'] ) ){
						$pathArr = array( (int)$value['category_id'] );
					}else{
						$pathArr = explode( '/' , $value['path'] );
					}
					$parentCatIdByPid[ $cid ] = $pathArr;
				}
			}

			foreach ( $result as $k => $v ){
				//获取分类的父分类ID
				if( isset( $parentCatIdByPid [ $k ] ) && !empty( $parentCatIdByPid [ $k ] ) ){
					$sql = ' UPDATE `category` SET `product_active_num` = ( `product_active_num` + ' .$v . ')
							WHERE `status` =1 AND `id` IN ( ' . implode( ',' , $parentCatIdByPid[ $k ] ) .' ) ';
					$this->db_ebmaster_write->query( $sql );
				}
			}
		}

		return TRUE;
	}

	/**
	 * 获取分类层级关系
	 * @param array $catIds 类别ID
	 */
	public function getCategoryPathByCatId( $catIds ){
		$this->db_ebmaster_read->select(' `id` , `path` ');
		$this->db_ebmaster_read->from('category');
		$this->db_ebmaster_read->where( 'status' , 1 );
		$this->db_ebmaster_read->where_in( 'id' , $catIds );
		$result = $this->db_ebmaster_read->get()->result_array();

		return $result;
	}

	/**
	 * 根据指定分类获取相关的分类信息
	 * @param integer $categoryId 分类的id
	 * @param integer $languageId 语言
	 * @author qcn qianchangnain@hofan.cn
	 * @return array
	 */
	protected function getCategoryListByCategory($categoryId, $languageId) {
		//获取缓存数据
		$cacheKey = "idx_catgegory_getcategorylistbycategory_%s_%s";
		$list = $this->memcache->get($cacheKey, array($categoryId, $languageId));
		if($list === false) {
			$this->db_ebmaster_read->select('id, name, url, path,nav_image, nav_image_bg, nav_url,product_active_num');
			$this->db_ebmaster_read->from('category');
			$this->db_ebmaster_read->where('p_id', $categoryId);
			$this->db_ebmaster_read->where('status', 1);
			$this->db_ebmaster_read->order_by('sort','desc');
			$query = $this->db_ebmaster_read->get();
			$list = $query->result_array();

			//取出分类的多语言信息
			$descList = array();
			$categoryIds = extractColumn($list, 'id');
			if(!empty($categoryIds)){
				$this->db_ebmaster_read->select('category_id, name');
				$this->db_ebmaster_read->from('category_desc');
				$this->db_ebmaster_read->where_in('category_id', $categoryIds);
				$this->db_ebmaster_read->where('language_id', $languageId);
				$query = $this->db_ebmaster_read->get();
				$descListQuery = $query->result_array();
				$descList = reindexArray($descListQuery,'category_id');
			}

			foreach($list as $key => $record){
				if(isset($descList[$record['id']])){
					$list[$key]['name'] = $descList[$record['id']]['name'];
				}
			}
			$this->memcache->set($cacheKey, $list, array($categoryId, $languageId));
		}
		return $list;
	}

	/**
	 * 格式化子分类数
	 * @param integer $categoryId 分类的id
	 * @param integer $languageId 语言
	 * @author qcn qianchangnain@hofan.cn
	 * @return array
	 */
	protected function _getFormatRelatedChildCatTree( $categoryId ,$languageId ) {
		$childListFormat = array();
		$childList = $this->getCategoryListByCategory( $categoryId , $languageId );
		foreach ($childList as $k => $child) {
			if ($child['product_active_num'] <= 0) {
				continue;
			}
			$childListFormat[$k] = array(
				'cat_name' => eb_htmlspecialchars($child['name']),
				'product_active_num' => $child['product_active_num'],
				'url_path' => $child['url']
			);
		}
		return $childListFormat;
	}

	/**
	 * 获取分类下的PIDs
	 * @param int $catId 分类ID
	 * @param int $sort 商品的排序 1,默认排序 1=sale_count; 2=add;3=price_asc;4=price_desc
	 * @param int $startCount 开始的个数
	 * @param int $pageSize 每页显示个数
	 *
	 * @return $result
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getPidsByCatId( $catId , $sort = 1 , $startCount = 0 , $pageSize  = 10, $isScriptTask = FALSE ){
		$result = array();
		$sortArr = array(
					1 => array( ),
					2 => array('sort' =>'add' ),
					3 => array('sort' =>'price_asc' ),
					4 => array('sort' =>'price_desc' ),
		 );
		if((int)$catId > 0 && isset( $sortArr[ $sort ] ) ){
			$productObj = new ProductModel();
			$lists = $productObj->getCategoryProductIds( $catId ,1 , array(), $sortArr[$sort] , $startCount , $pageSize, array(), $isScriptTask );
			$result = $lists['goodsList'];
		}

		return $result ;

	}

	/**
	 * get sub catid  by parent catid
	 * @param int $cat_id
	 * @return array $result
	 * @author lucas
	 */
	public function getSubCatIdbyCatId( $cat_id ){
		$cacheKey = "get_sub_catid_by_pcatid_{$cat_id}";
		$cacheParams = array( $cat_id );
		$list = $this->memcache->get( $cacheKey, $cacheParams );

		if( $list === false){
			$this->db_ebmaster_read->select('id,path');
			$this->db_ebmaster_read->from('category');
			$this->db_ebmaster_read->where('status',1);
			$this->db_ebmaster_read->where('product_active_num >',0);
			$this->db_ebmaster_read->where('url !=','');
			$this->db_ebmaster_read->like('path', $cat_id );
			$this->db_ebmaster_read->order_by('sort','asc');
			$query = $this->db_ebmaster_read->get();
			$list = $query->result_array();
			$list = reindexArray( $list , 'id' );

			$this->memcache->set( $cacheKey, $list, $cacheParams );
		}

		return $list;
	}

	/**
	 * 获取一级分类包含的商品数量
	 */
	public function getRootCategoryGoodsCount() {
		$cacheKey = "get_root_category_goods_count";
		$list = $this->memcache->get( $cacheKey, array() );

		if( $list === false){
			$this->db_ebmaster_read->select('id,product_active_num');
			$this->db_ebmaster_read->from('category');
			$this->db_ebmaster_read->where('p_id',0);
			$query = $this->db_ebmaster_read->get();
			$list = $query->result_array();
			$list = reindexArray( $list , 'id' );

			$this->memcache->set( $cacheKey, $list, array() );
		}

		return $list;
	}

	/**
	 * 取出所有的分类的id
	 * @param  bool $level1 是不是取出一级分类的id
	 */
	public function getCategoryAllId($level1 = false) {
		$this->db_ebmaster_read->select('id');
		$this->db_ebmaster_read->from('category');
		if($level1 === false) {
			$this->db_ebmaster_read->where('p_id !=', 0);
		}
		$this->db_ebmaster_read->where('status',1);
		$query = $this->db_ebmaster_read->get();
		$list = $query->result_array();
		$list = extractColumn( $list , 'id' );
		return $list;
	}

	/**
	 * 获得所有二级分类ID
	 * @return array
	 * @author lucas
	 */
	public function getTwoCategoryIds() {
		$cacheKey = "get_category_two_ids";
		$pidCategoryLevel2 = $this->memcache->get( $cacheKey, array() );

		if( $pidCategoryLevel2 === false ){
			$pidCategoryLevel2 = array();
			//获取所有的分类
			$fieldArray = array('id', 'p_id', 'path');
			$whereArray = array(
				'status' => 1,
				'type' => 1,
				'product_active_num >' => '0',
				'url !=' => '',
			);
			$orderBy = array('sort' => 'desc');
			$categoryMapArray = $this->getCategoryInfo( $fieldArray, $whereArray, $orderBy, $groupBy = '', array() );

			if( count( $categoryMapArray ) > 0 ) {
				//处理一级分类
				foreach ( $categoryMapArray as $key => $value ) {
					if( empty( $value['path'] ) ) {
						unset($value); continue;
					}

					$idPathArray = explode('/', $value['path'] );
					$countTmp = count( $idPathArray ) ;

					if ( $countTmp === 2 ){//二级分类
						if( isset( $idPathArray[1] ) ) {
							$pidCategoryLevel2[] = $idPathArray[1] ;
						}
					}
				}
			}

			$this->memcache->set( $cacheKey, $pidCategoryLevel2, array() );
		}

		return $pidCategoryLevel2;
	}
}
<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Model for category.
 * @author qcn
 */
class CategoryModel extends CI_Model {

	const CATEGORY_ITEM_LENGTH_1 = '18';
	const CATEGORY_ITEM_LENGTH_2 = '17';

	public function __construct(){
		parent::__construct();
	}

	/**
	 * Get the format data of child category tree.
	 * @author ALbie
	 * @param type $catId
	 * @param type $language_id
	 * @return array
	 */
	protected function _getFormatRelatedChildCatTree($catId,$language_id) {
		$childListFormat = array();
		$childList = $this->getCategoryListByCategory($catId, $language_id);
		foreach ($childList as $k => $child) {
			if ($child['product_active_num'] <= 0) {
				continue;
			}
			$childListFormat[$k] = array(
				'cat_name' => $child['cat_name'],
				'product_active_num' => $child['product_active_num'],
				'url_path' => $child['url_path']
			);
		}
		return $childListFormat;
	}

/*
| -------------------------------------------------------------------
|  DB Functions
| -------------------------------------------------------------------
*/
	public function getShownCategory($language_id , $is_recommend = FALSE ){
		$cache_key = "idx_get_shown_category_%s_%s";
		$cache_params = array($language_id,(int)$is_recommend);
		$list = $this->memcache->get($cache_key,$cache_params);

		if($list === false){
			$this->db_read->select('cat_id,cat_img,cat_logo_img,cat_logo_url,cat_logo_alt,cat_css_img,parent_id,product_active_num,url_path,id_path');
			$this->db_read->from('category');
			$this->db_read->where('is_show',1);
			$this->db_read->where('deleted',0);
			$this->db_read->where('product_active_num >',0);
			$this->db_read->where('url_path !=','');
			$this->db_read->order_by('sort_order','asc');
			$query = $this->db_read->get();
			$list = $query->result_array();

			$this->db_read->select('cat_id,cat_name');
			$this->db_read->from('category_multilingual');
			$this->db_read->where('language_id',$language_id);
			$this->db_read->where('language_show',1);
			if( $is_recommend === TRUE ){
				$this->db_read->where('is_recommend',1);
			}
			$this->db_read->where('cat_name !=','');
			$this->db_read->group_by('cat_id');
			$query = $this->db_read->get();
			$multilingual = $query->result_array();

			$multilingual = reindexArray($multilingual,'cat_id');
			foreach($list as $key => $record){
				if(!isset($multilingual[$record['cat_id']])){
					unset($list[$key]);
					continue;
				}
				$list[$key]['multilingual_name'] = $multilingual[$record['cat_id']]['cat_name'];
			}
			$list = array_values($list);
			$this->memcache->set($cache_key,$list,$cache_params);
		}

		return $list;
	}

	public function getCategoryGoodsTemplate($category_id_path,$language_id){
		$parent_category_ids = explode('/',$category_id_path);

		$fetch_db_category_ids = array();
		$cate_template_buffer = array();
		$cache_key = "idx_category_template_%s_%s";
		foreach($parent_category_ids as $category_id){
			$cache_params = array($category_id,$language_id);
			$template = $this->memcache->get($cache_key,$cache_params);
			if($template === false){
				$fetch_db_category_ids[] = $category_id;
			}else{
				$cate_template_buffer[$category_id] = $template;
			}
		}

		if(!empty($fetch_db_category_ids)){
			$this->db_read->from('category_template');
			$this->db_read->where_in('cat_id',$fetch_db_category_ids);
			$this->db_read->where('language_id',$language_id);
			$this->db_read->where('cat_template_status',1);
			$this->db_read->where('cat_template_content !=','');
			$query = $this->db_read->get();
			$list = $query->result_array();

			foreach($list as $record){
				$cate_template_buffer[$record['cat_id']] = $record['cat_template_content'];
			}
		}

		foreach($fetch_db_category_ids as $category_id){
			$cache_params = array($category_id,$language_id);
			$this->memcache->set($cache_key,id2name($category_id,$cate_template_buffer,''),$cache_params);
		}

		$template_content = '';
		$parent_category_ids = array_reverse($parent_category_ids);
		foreach($parent_category_ids as $category_id){
			if(isset($cate_template_buffer[$category_id]) && $cate_template_buffer[$category_id] != ''){
				$template_content = $cate_template_buffer[$category_id];
				break;
			}
		}

		return $template_content;
	}

	public function getParentsCategoryList($category_id_path,$language_id) {
		$category_ids = explode('/',$category_id_path);
		$category_list = $this->getCategoryById($category_ids,$language_id);
		$category_list = reindexArray($category_list,'cat_id');

		$list = array();
		foreach($category_ids as $category_id){
			if(!isset($category_list[$category_id])) continue;
			$list[] = $category_list[$category_id];
		}
		return $list;
	}

	public function getCategoryById($category_ids,$language_id = 1){
		if(!is_array($category_ids)) $category_ids = array($category_ids);
		if(empty($category_ids)) return array();

		$fetch_db_category_ids = array();
		$category_buffer = array();
		$cache_key = "idx_category_info_%s_%s";
		foreach($category_ids as $category_id){
			$cache_params = array($category_id,$language_id);
			$category = $this->memcache->get($cache_key,$cache_params);
			if($category === false){
				$fetch_db_category_ids[] = $category_id;
			}else{
				$category_buffer[$category_id] = $category;
			}
		}

		$list = array();
		if(!empty($fetch_db_category_ids)){
			$this->db_read->from('category');
			$this->db_read->where_in('cat_id',$fetch_db_category_ids);
			$query = $this->db_read->get();
			$category_list = $query->result_array();
			$category_list = reindexArray($category_list,'cat_id');

			$this->db_read->from('category_multilingual');
			$this->db_read->where_in('cat_id',$fetch_db_category_ids);
			$this->db_read->where('language_id',$language_id);
			$query = $this->db_read->get();
			$desc = $query->result_array();
			$desc = reindexArray($desc,'cat_id');

			foreach($category_list as $record){
				if(isset($desc[$record['cat_id']])){
					$record = array_merge($record,$desc[$record['cat_id']]);
				}
				$cache_params = array($record['cat_id'],$language_id);
				$this->memcache->set($cache_key,$record,$cache_params);

				$category_buffer[$record['cat_id']] = $record;
			}
		}
		$category_buffer = array_values($category_buffer);

		return $category_buffer;
	}

	/**
	* @method get operation top pic
	* @param $category_id
	* @author qianchangnian
	* @date 2014/3/25
	* @return array
	*/
	public function getTopCategoryBanner ( $category_id ) {
		$cache_key = "idx_get_top_category_banner_{$category_id}";
		$result = $this->memcache->get( $cache_key );
		if ( $result === false) {
			$this->db_read->from('operation_topic');
			$this->db_read->where('parent_cat_id',$category_id);
			$this->db_read->order_by('sort','asc');
			$this->db_read->limit(5);
			$query = $this->db_read->get();
			$result = $query->result_array();
			$this->memcache->set( $cache_key, $result, CACHE_TIME_CATEGORY_BANNER_AD );
		}
		return $result;
	}

	public function getLeftCategoryAd() {
		$cache_key = "idx_get_left_category_ad";
		$result = $this->memcache->get( $cache_key );
		if ( $result === false) {
			$this->db_read->from('operation_leftpic');
			$query = $this->db_read->get();
			$result = $query->result_array();
			$this->memcache->set( $cache_key, $result, CACHE_TIME_CATEGORY_BANNER_AD );
		}
		return $result;
	}

	/**
	* @method get operation top pic
	* @param $category_ids
	* @param $language_id
	* @param $language_code
	* @param $where
	* @author qianchangnian
	* @date 2014/3/25
	* @return array
	*/
	public function getCategoryListByCategory($category_id,$language_id){
		$cache_key = "idx_get_category_list_by_category_{$category_id}_{$language_id}";
		$list = $this->memcache->get($cache_key);
		if($list === false){
			$this->db_read->from('category');
			$this->db_read->where('parent_id',$category_id);
			$this->db_read->where('is_show',1);
			$this->db_read->order_by('sort_order','asc');
			$query = $this->db_read->get();
			$list = $query->result_array();

			$desc_list = array();
			$category_ids = extractColumn($list,'cat_id');
			if(!empty($category_ids)){
				$this->db_read->select('cat_id,cat_name');
				$this->db_read->from('category_multilingual');
				$this->db_read->where_in('cat_id',$category_ids);
				$this->db_read->where('language_id',$language_id);
				$query = $this->db_read->get();
				$desc_list = $query->result_array();
				$desc_list = reindexArray($desc_list,'cat_id');
			}

			foreach($list as $key => $record){
				if(isset($desc_list[$record['cat_id']])){
					$list[$key]['cat_name'] = $desc_list[$record['cat_id']]['cat_name'];
				}
			}

			$this->memcache->set($cache_key,$list,CACHE_TIME_CATEGORY_LIST_BY_CATEGORY);
		}

		return $list;
	}

	/**
	 * Get the category related category tree data.
	 * @autor Albie
	 * @param type $category
	 * @param type $language_id
	 * @return array
	 */
	public function getCatRelatedDataByCat($category, $language_id) {

		$relatedTree = array();
		$type = '';

		$idPathArr = explode('/', $category['path']);
		$levelCount = count($idPathArr);
		switch ($levelCount) {
			case 1:// Level 1 category
				$type = 1;
				$categoryId = current($idPathArr);
				$childCatList = $this->getCategoryListByCategory($categoryId, $language_id);
				foreach ($childCatList as $childCat) {
					if($childCat['product_active_num']<=0){
						continue;
					}
					$relatedTree[] = array(
						'cat_name' => $childCat['cat_name'],
						'product_active_num' => $childCat['product_active_num'],
						'url_path' => $childCat['url_path'],
						'child' => $this->_getFormatRelatedChildCatTree($childCat['cat_id'], $language_id)
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
					'cat_name' => $category['cat_name'],
					'product_active_num' => $category['product_active_num'],
					'url_path' => $category['url_path'],
					'child' => $this->_getFormatRelatedChildCatTree($category['cat_id'], $language_id),
					'current' => TRUE
				);
				$parentCatId = $idPathArr[0];
				$otherCatList = $this->getCategoryListByCategory($parentCatId, $language_id);
				foreach ($otherCatList as $cat) {
					if ($cat['cat_id'] != $category['cat_id'] && $cat['product_active_num']>0) {
						$relatedTree[] = array(
							'cat_name' => $cat['cat_name'],
							'product_active_num' => $cat['product_active_num'],
							'url_path' => $cat['url_path']
						);
					}
				}
				break;
			default:
				$type = 3;
				$parentCatId = $idPathArr[$levelCount-2];
				if ($parentCatId) {
					$parentCategory = current($this->CategoryModel->getCategoryById($parentCatId, $language_id));
					if($parentCategory['product_active_num']<=0){
						$relatedTree = array();
						break;
					}
					$catList = $this->getCategoryListByCategory($parentCatId, $language_id);
					$childCatListFormat = array();
					foreach ($catList as $cat) {
						if($cat['product_active_num']<=0){
							continue;
						}
						$childCatListFormat[] = array(
							'cat_name' => $cat['cat_name'],
							'product_active_num' => $cat['product_active_num'],
							'url_path' => $cat['url_path'],
							'current' => ($cat['cat_id'] == $category['cat_id'])?TRUE:FALSE
						);
					}
					$relatedTree[] = array(
						'cat_name' => $parentCategory['cat_name'],
						'product_active_num' => $parentCategory['product_active_num'],
						'url_path' => $parentCategory['url_path'],
						'child' => $childCatListFormat
					);
				} else {
					$relatedTree = array();
				}
				break;
		}
		return array('relatedTree' => $relatedTree, 'type' => $type);
	}

	/*
| -------------------------------------------------------------------
|  noDB Functions
| -------------------------------------------------------------------
*/
	public function buildList($data){
		$i = 0;
		$res = $data[0];
		while($i < count($res)){
			if(isset($data[$res[$i]['cat_id']])){
				array_splice($res,$i+1,0,$data[$res[$i]['cat_id']]);
			}
			$i++;
		}

		return $res;
	}

	public function buildTree($data,$root = 0){
		$res = array();

		if(isset($data[$root])){
			$res = $data[$root];
		}
		foreach($res as $key => $record){
			$res[$key]['children'] = $this->buildTree($data,$record['cat_id']);
		}

		return $res;
	}



	/**
	 * get  sub catid  by parent catid
	 * @param int $cat_id
	 * @return array $result
	 * @author bryan
	 */
	public function getSubCatIdbyCatId ($cat_id){
		$cache_key = "idx_get_sub_catid_by_pcatid_{$cat_id}";
		$list = $this->memcache->get($cache_key);

		if( $list === false){
			$this->db_read->select('cat_id,id_path');
			$this->db_read->from('category');
			$this->db_read->where('is_show',1);
			$this->db_read->where('deleted',0);
			$this->db_read->where('product_active_num >',0);
			$this->db_read->where('url_path !=','');
			$this->db_read->like('id_path', $cat_id );
			$this->db_read->order_by('sort_order','asc');
			$query = $this->db_read->get();
			$list = $query->result_array();
			$list = reindexArray( $list , 'cat_id' );
			$this->memcache->set($cache_key,$list,CACHE_TIME_ALL_CATEGORY);
		}

		return $list;
	}
}

<?php
namespace app\models;

use Yii;
use app\models\common\EbARModel as baseModel;
use app\components\helpers\ArrayHelper;
use app\components\helpers\HelpOther;
use app\components\helpers\HelpUrl;
use app\components\helpers\OtherHelper;


class Keywordrecommend extends baseModel {
	
	const UNRECOMMEND_STATUS = 1;
	const RECOMMEND_STATUS = 2;
	const RANDOM_NUM = 12;
	private static $_tableName = '';
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
	public static function tableName() {
		return static::$_tableName;
	}
	
	/**
	 * Get keywords from ciku by random.
	 * @param type $tableName
	 * @param type $catId
	 * @return type
	 */
	protected function _getRecoKeywordsIds($tableName, $catId) {
		
		$this->db_read->select('count(id) total');
		$this->db_read->from($tableName);
		$this->db_read->where('isActive',1);
		$this->db_read->where('cat_id',$catId);
		$this->db_read->where('is_online !=',self::RECOMMEND_STATUS);
		$total =  $this->db_read->get()->row()->total;
		
		$arrIds = array();
		if ($total > 0) {
			for ($i = 0; $i < self::RANDOM_NUM; $i++) {
				$random = mt_rand(0, $total - 1);
				$this->db_read->select('id');
				$this->db_read->from($tableName);
				$this->db_read->where('isActive', 1);
				$this->db_read->where('cat_id', $catId);
				$this->db_read->where('is_online !=', self::RECOMMEND_STATUS);
				$this->db_read->limit(1, $random);
				$arrIds[] = $this->db_read->get()->row()->id;
			}
		}
		return $arrIds;
	}
	
	//将以前的推荐关键词取消推荐。
	protected function _unRecoOldKeywordsByCatid($tableName, $cat_id) {
		
		$this->db_write->set('is_online',self::UNRECOMMEND_STATUS);
		$this->db_write->where('cat_id',$cat_id);
		$this->db_write->where('is_online',self::RECOMMEND_STATUS);
		$this->db_write->update($tableName);
	}
	
	/**
	 * 
	 * @param type $tableName
	 * @param type $ids
	 */
	protected function _recoKeywordsByIds($tableName, $ids) {
		if ($ids) {
			$this->db_write->set('is_online', self::RECOMMEND_STATUS);
			$this->db_write->where_in('id', $ids);
			$this->db_write->update($tableName);
		}
	}

	public function getAtozList($language_code){
		$list = array();

		if($language_code == 'ru'){
			$list = array('A','B','D','E','F','G','I','K','L','M','N','O','P','R','S','T','U','V','Y','Z');
		}else{
			$list = range('A','Z');
		}
		$list[] = '0-9';

		return $list;
	}

	/*
	* get keyword recommend list by language
	*/
	public function getKeywordRecommendList($language_id){
		$cache_key = "idx_get_keyword_recommend_list_%s";
		$cache_params = array($language_id);
		$list = $this->memcache->get($cache_key,$cache_params);
		if($list === false){
			$this->db_read->select('keyword');
			$this->db_read->from('keyword_recommend');
			$this->db_read->where('language_id',$language_id);
			$query = $this->db_read->get();
			$list = $query->result_array();

			$this->memcache->set($cache_key,$list,$cache_params);
		}

		return $list;
	}

	/*
	* get keyword recommend list by language
	*/
	public function getCategoryKeywordRecommendList($language_id){
		$cache_key = "idx_get_category_keyword_recommend_list_%s";
		$cache_params = array($language_id);
		$list = $this->memcache->get($cache_key,$cache_params);
		if($list === false){
			$query = static::find();
			$query->from( 'keyword_recommend_of_category' );
			$query->where(  ['language_id'=> $language_id, 'keyword','!=\'\''] );
			$query->orderBy(['mid'=> SORT_ASC , 'row_id'=> SORT_ASC , 'kid'=>SORT_ASC]);
			$list = $query->asArray()->all(); 

			$this->memcache->set($cache_key,$list,$cache_params);
		}

		return $list;
	}

	/**
	 * 获取分类页面关键词
	 * @param  string $categoryIdPath 分类id路径
	 * @param  integer $languageId 语言id
	 * @param  integer $limit 取出关键词的条数
	 * @author qcn qianchangnian@hofan.cn
	 * @return array 返回分类页面关键词的数组
	 */
	public function getCategoryKeywords($categoryIdPath, $languageId, $limit) {
		//从缓存种取出数据
		$cacheKey = "idx_get_category_keywords_%s_%s_%s";
		$cacheParams = array($categoryIdPath, $languageId, $limit);
		$res = $this->memcache->get($cacheKey,$cacheParams);
		if( $res === false){
			//将分类id路径转化为分类id数组
			$categoryIds = explode('/', $categoryIdPath);

			//取出关键词
			$list = array();
			if(!empty($categoryIds)) {
				$this->db_read->select('word, word_url, cat_id');
				$this->db_read->from($languageId==1?'ciku':'ciku_'.$languageId);
				$this->db_read->where('isActive', 1);
				$this->db_read->where('is_online', 2);
				$this->db_read->where_in('cat_id', $categoryIds);
				$this->db_read->order_by('id', 'asc');
				$query = $this->db_read->get();
				$list = $query->result_array();
			}
			$list = spreadArray($list, 'cat_id');
			$categoryIds = array_reverse($categoryIds);

			$res = array();
			foreach($categoryIds as $categoryId){
				if(!isset($list[$categoryId])) {
					continue;
				}
				$res = array_merge($res,$list[$categoryId]);
				if(count($res) >= $limit) {
					break;
				}
			}
			$res = array_slice($res,0,$limit);

			$shortCount = $limit - count($res);
			if($shortCount > 0){
				$this->db_read->select('word,word_url,cat_id');
				$this->db_read->from($languageId==1?'ciku':'ciku_'.$languageId);
				$this->db_read->where('isActive',1);
				$this->db_read->where('is_online',2);
				$this->db_read->order_by('id','asc');
				$this->db_read->limit($shortCount);
				$query = $this->db_read->get();
				$list = $query->result_array();

				$res = array_merge($res,$list);
			}
			$this->memcache->set($cacheKey,$res,$cacheParams);
		}

		return $res;
	}
	
	/**
	 * Change the category reconmond keywords by random.
	 */
	public function changeCatKeyword() {
		$arrCikuTb = array('ciku', 'ciku_2', 'ciku_3', 'ciku_4', 'ciku_5', 'ciku_6', 'ciku_7');
		foreach ($arrCikuTb as $tableName) {
			
			$this->db_read->select('cat_id');
			$this->db_read->from($tableName);
			$this->db_read->group_by('cat_id');
			$query = $this->db_read->get();
			$catList = $query->result_array();
			foreach ($catList as $catId) {
				$catId['cat_id'] = intval($catId['cat_id']);
				$recoKeywordsIds = $this->_getRecoKeywordsIds($tableName, $catId['cat_id']); //Get the keywords by random.
				$this->_unRecoOldKeywordsByCatid($tableName, $catId['cat_id']); //Cancel the reconmand keywords by cat id.
				$this->_recoKeywordsByIds($tableName, $recoKeywordsIds); //将指定的id对应的关键词推荐
			}
		}
	}
	
}

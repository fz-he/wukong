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
class Categorydesc extends baseModel {
	//定义操作表名 eb_pc_site:category
	private static $_tableName = 'category_desc';
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
    public static function tableName()  {  
        return static::$_tableName;  
    } 

	/**
	 * 获取分类到的信息
	 * @param  array  $fieldArray 字段
	 * @param  array  $whereArray 查询where条件
	 * @param  array  $orderBy    排序条件
	 * @param  string $groupBy    分组条件
	 * @param  array  $limit      limit条件
	 * @return array 返回分类的信息
	 */
	public function getCategoryDescInfo( $fieldArray = array(), $whereArray = array(), $orderBy = array(), $groupBy = '', $limit = array() ) {
		//缓存处理
		$memcacheKeyStrCode = array(md5(serialize($fieldArray) . serialize($whereArray) . serialize($orderBy) . $groupBy . serialize($limit)));
		$cacheKey = "idx_categorydescmodel_getcategorydescinfo_%s";
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

			//order by条件处理
			if(is_array($orderBy) &&  count( $orderBy ) > 0 ) {
				$query->orderBy( $orderBy );
			}

			//group by条件处理
			if(!empty($groupBy)) {
				$query->groupBy( $groupBy );
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

			if ( ! empty($result )){
				$this->memcache->set($cacheKey,$result,$memcacheKeyStrCode);
			}			
		}
		return $result;
	}
}
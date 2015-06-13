<?php

namespace app\models;

use Yii;
use app\models\common\EbARModel as baseModel;
use app\components\helpers\HelpOther;
use app\components\helpers\HelpUrl;
use app\components\helpers\ArrayHelper;

class Imagead extends baseModel {

	//定义操作表名 eb_pc_site:category
	private static $_tableName = 'image_ad_list';
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
		return static::$_tableName;
	}
	/*
	* get image ad list by id & language
	*/
	public function getImageAdList($image_ad_ids,$language_id,$cache = true){
		if(!is_array($image_ad_ids)) $image_ad_ids = array($image_ad_ids);
		if(empty($image_ad_ids)) return array();

		$res = array();
		$missing_image_ad_ids = array();
		foreach($image_ad_ids as $image_ad_id){
			$cache_key = "idx_get_image_ad_list_%s_%s";
			$cache_params = array($image_ad_id,$language_id);
		//	$item = $this->memcache->get($cache_key,$cache_params);
			$item = false;

			if($cache === false) {
				$item = false;
			}
			if($item === false ){
				$missing_image_ad_ids[] = $image_ad_id;
			}else{
				$res[$image_ad_id] = $item;
			}
		}

		if( !empty($missing_image_ad_ids) ){
			$query = static::find();
			$query->select(['image_ad_id' , 'image_ad_name']);
			if ( !empty ($missing_image_ad_ids) ){ //没有whereIN 类似的函数
				$where = 'image_ad_id in (' . implode(',' ,$missing_image_ad_ids) . ')' ;
				$query->where(  $where );
			}
			$query->groupBy('image_ad_id');
			$name_list = $query->asArray()->all(); 
			$name_list = ArrayHelper::reindexArray($name_list,'image_ad_id');

			$query = self::find();
			$query->from('image_ad_info');
			if ( !empty ($missing_image_ad_ids) ){ //没有whereIN 类似的函数
				$query->where(  $where . ' and language_id = '.  $language_id );
			}else {
				$query->where(  ['language_id' => $language_id ] );
			}
		
			$query->orderBy( ['image_position'=> SORT_ASC, 'image_index'=> SORT_ASC ] );
					
			$list = $query->asArray()->all();	

			foreach($list as $key => $record){
				if($record['image_valid'] == 1 && $record['image_end_time'] <= date( 'Y-m-d H:i:s', HelpOther::requestTime() ) ){
					unset($list[$key]);
					continue;
				}
				if($record['image_valid'] == 1 && $record['image_star_time'] == '0000-00-00 00:00:00'){
					unset($list[$key]);
					continue;
				}

				$image_ad_id = $record['image_ad_id'];
				$list[$key]['image_ad_name'] = 'image_ad_'.$image_ad_id;
				if(isset($name_list[$image_ad_id])){
					$list[$key]['image_ad_name'] = $name_list[$image_ad_id]['image_ad_name'];
				}
			}
			$list = array_values($list);
			$list = ArrayHelper::spreadArray($list,'image_ad_id');

			foreach($list as $image_ad_id => $record){
				$res[$image_ad_id] = $record;
				$cache_key = "idx_get_image_ad_list_%s_%s";
				$cache_params = array($image_ad_id,$language_id);
				$this->memcache->set($cache_key,$record,$cache_params);
			}
		}

		$result = array();
		if( !empty( $res ) ){
			foreach ( $res as $image_ad_id => $info ) {
				$result[ $image_ad_id ] = array();
				if(!empty( $info )){
					foreach ( $info as $v ){
						if($v['image_valid'] == 1 && $v['image_star_time'] >= date( 'Y-m-d H:i:s', HelpOther::requestTime() ) ){
							continue;
						}
						if($v['image_valid'] == 1 && $v['image_end_time'] <= date( 'Y-m-d H:i:s', HelpOther::requestTime() ) ){
							continue;
						}
						if($v['image_valid'] == 1 && $v['image_star_time'] == '0000-00-00 00:00:00'){
							continue;
						}
						//TODO 优化 kim
						if( COMMON_DOMAIN == 'eachbuyer.net' ){
							$v['image_link'] = str_replace('eachbuyer.com', COMMON_DOMAIN, $v['image_link'] );
						}
						$result[ $image_ad_id ][] = $v ;
					}
				}
			}
		}
		return $result;
	}
}
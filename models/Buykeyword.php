<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model for Buykeyword.
 * @author BRYAN
 */
class BuykeywordModel extends CI_Model {

	const BUY_KEYWORD_INFO_MEM_KEY = 'buy_keyword_md5_%s_%s'; //proSecKillInfo{$product_id} 产品的秒杀信息缓存key


	/**
	 * @return BuykeywordModel
	 */
	public function __construct(){
		parent::__construct();
	}

	/**
	 * 获取实例化
	 * @return BuykeywordModel
	 */
	public static function & getInstanceObj( ){
		return parent::_getBaseInstanceObj( __CLASS__ );
	}

	/**
	 * 根据 MD5 获取分类ID 以及状态等
	 * @param string $keywordMd5
	 * @param number $languageId
	 * @return array $result
	 */
	public function getBuyInfoByMd5( $keywordMd5 = '' , $languageId =1 ){
		global $language_list ;
		$result = array();
		if( !empty( $keywordMd5 )  && !empty( $language_list[ $languageId ] ) ) {
			$mcResult = $this->memcache->get( self::BUY_KEYWORD_INFO_MEM_KEY , array( $keywordMd5 , $languageId ) );
			//get db
			if( $mcResult === FALSE ){
				$this->db_ebmaster_read->select( '`category_id`,`md5`,`type`,`status`');
				$this->db_ebmaster_read->from( 'eb_buy_ciku_' . $languageId );
				$this->db_ebmaster_read->where( 'md5' , $keywordMd5 );
				$this->db_ebmaster_read->limit( 1 );
				$list = $this->db_ebmaster_read->get();
				// 获取不到数据 返回FALSE
				if( $list !=FALSE ){
					$list = $list->result_array() ;
					if( isset( $list[0] ) ){
						$this->memcache->set( self::BUY_KEYWORD_INFO_MEM_KEY , $list[0] , array( $keywordMd5 , $languageId ) );
						$result = $list[0] ;
					}
				}
			}else {
				$result = $mcResult ;
			}

			//过滤状态被删除的信息
			if( isset( $result[ 'status' ] ) && $result[ 'status' ] == 3 ){
				$result = array();
			}
		}

		return $result ;
	}

	/**
	 * crontab 删除禁用的关键词
	 * @return TRUE
	 */
	public function deleteKeywordBystatus(){
		global $language_list ;
		//循环删除禁用的关键词
		foreach ( $language_list as $k => $v ){
			$this->db_ebmaster_write->where('status',3);
			$this->db_ebmaster_write->delete('eb_buy_ciku_'. $k );
		}
		return TRUE ;
	}

}

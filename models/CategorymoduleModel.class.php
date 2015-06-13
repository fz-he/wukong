<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * 分类模版管理Model
 * @author lucas
 */
class CategorymoduleModel extends CI_Model {

	//表名 eb_pc_site:category_module
	private $_tableName_cm = 'category_module';

	//表名 eb_pc_site:category_module_record
	private $_tableName_cmr = 'category_module_record';

	/*
	 * 初始化
	 */
	public function __construct(){
		parent::__construct();
	}

	/**
	 * 获得分类下模板信息
	 * @param integet $categoryId 类别ID
	 * @param integet $languageId 语言ID
	 * @return Array 返回 分类模板信息数组
	 * @author lucas
	 */
	public function getCategoryModuleArr( $categoryId, $languageId = 1 ){
		$categoryModuleArr = array();
		//获得模板信息
		$categoryModuleResult = $this->getCategoryModuleInfoByCategoryId( $categoryId );
		//获得模板内容
		$categoryModuleContentResult = $this->getCategoryModuleContentByCategoryId( $categoryId );

		foreach( $categoryModuleResult as $moduleItem ){
			$nameLang = json_decode( $moduleItem['lang'], TRUE );
			$categoryModuleArr[ $moduleItem['id'] ] = array(
					'type' => $moduleItem['type'],
					'name' => empty( $nameLang[ $languageId ] ) ? $nameLang[ $languageId ] : $moduleItem['name'],
					'module_id' => $moduleItem['id'],
				);
			foreach( $categoryModuleContentResult as $contentItem ){
				if( $contentItem['category_module_id'] == $moduleItem['id'] ){
					if( defined( 'EBPLATEFORM' ) && ( EBPLATEFORM === 2 ) ){
						$contentItem['content'] = str_replace( 'eachbuyer.com' , 'eachbuyer.net' , $contentItem['content'] );
					}
					$contentArr = json_decode( $contentItem['content'], TRUE );

					foreach( $contentArr as &$record ){
						if( isset( $record['title'] ) ){
							$record['title'] = empty( $record['title'][ $languageId ] ) ? '' : $record['title'][ $languageId ];
						}
						if( isset( $record['item'] ) ){
							foreach( $record['item'] as $key => $value ){
								if( isset( $value[ $languageId ] ) ){
									$record['item'][ $key ] = empty( $value[ $languageId ] ) ? '' : $value[ $languageId ];
								}
							}
						}
					}
					unset( $record );
					if( isset( $contentArr['title'] ) ){
						$contentArr['title'] = empty( $contentArr['title'][ $languageId ] ) ? '' : $contentArr['title'][ $languageId ];
					}
					if( $moduleItem['type'] == 3 ){
						$contentArr['id'] = $contentItem['id'];
					}

					$categoryModuleArr[ $moduleItem['id'] ]['content'][] = $contentArr;
				}
			}
		}

		return $categoryModuleArr;
	}

	/**
	 * 获得分类模板信息
	 * @param integet $categoryId 类别ID
	 * @return Array 返回 分类模板信息数组
	 * @author lucas
	 */
	public function getCategoryModuleInfoByCategoryId( $categoryId ){
		$result = array();
		if( $categoryId > 0 ){
			$cacheKey = 'get_cat_module_info_%s' ;
			$result = $this->memcache->get( $cacheKey, $categoryId );
			if( $result === FALSE ){
				//get DB
				$this->db_ebmaster_read->select('id, name, type, lang');
				$this->db_ebmaster_read->from( $this->_tableName_cm );
				$this->db_ebmaster_read->where('category_id', $categoryId);
				$this->db_ebmaster_read->where('status', 1);
				$this->db_ebmaster_read->order_by('sort','DESC');
				$query = $this->db_ebmaster_read->get();
				$result = $query->result_array();

				//set mc
				$this->memcache->set( $cacheKey, $result, $categoryId );
			}
		}

		return $result;
	}

	/**
	 * 获得分类模板内容
	 * @param integet $categoryId 类别ID
	 * @return Array 返回 分类模板内容数组
	 * @author lucas
	 */
	public function getCategoryModuleContentByCategoryId( $categoryId ){
		$result = array();
		if( $categoryId > 0 ){
			$cacheKey = 'get_cat_module_content_%s';
			$result = $this->memcache->get( $cacheKey, $categoryId );
			if( $result === FALSE ){
				//get DB
				$this->db_ebmaster_read->select('id, category_module_id, content');
				$this->db_ebmaster_read->from( $this->_tableName_cmr );
				$this->db_ebmaster_read->where('category_id', $categoryId);
				$this->db_ebmaster_read->where('status', 1);
				$this->db_ebmaster_read->order_by('sort', 'DESC');
				$query = $this->db_ebmaster_read->get();
				$result = $query->result_array();

				//set mc
				$this->memcache->set( $cacheKey, $result, $categoryId );
			}
		}

		return $result;
	}

	/**
	 * 获得分类某模板内容
	 * @param integet $moduleId 模块ID
	 * @return Array 返回 分类模板内容数组
	 * @author lucas
	 */
	public function getCategoryModuleidContent( $moduleId ){
		$result = array();
		if( $moduleId > 0 ){
			$cacheKey = 'get_cat_moduleid_content_%s';
			$result = $this->memcache->get( $cacheKey, $moduleId );
			if( $result === FALSE ){
				//get DB
				$this->db_ebmaster_read->select('content');
				$this->db_ebmaster_read->from( $this->_tableName_cmr );
				$this->db_ebmaster_read->where('id', $moduleId);
				$this->db_ebmaster_read->where('status', 1);
				$query = $this->db_ebmaster_read->get();
				$result = $query->result_array();

				//set mc
				$this->memcache->set( $cacheKey, $result, $moduleId );
			}
		}

		return $result;
	}

}
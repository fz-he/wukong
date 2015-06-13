<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 促销模板类
 * Model for Promotetemplates
 * @author lucas
 */
class PromotetemplatesModel extends CI_Model {

	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * 获取实例化
	 * @return ProductModel
	 */
	public static function & getInstanceObj( ){
		return parent::_getBaseInstanceObj( __CLASS__ );
	}

	/**
	 * 获得模板信息
	 * @param  int $saleId 模板ID
	 * @return array
	 */
	public function getPromoteInfo( $saleId  ) {
		if(  $saleId <= 0){
			return array();
		}
		
		$cacheKey = "get_promotelist_%s";
		$params = array( $saleId );
		$promoteList = $this->memcache->get( $cacheKey, $params );
		$result = array();
		
		if( $promoteList == FALSE ) {
			$promoteList = $this->db_ebmaster_read
			->select('sale_id, name, type, color, start_time, end_time')
			->from('eb_sale')
			->where('sale_id', $saleId )
			->where('status', 1 )
//			->where($startTime ,  date( 'Y-m-d H:i:s', HelpOther::requestTime()+36000 ) )
//			->where('end_time >' , date( 'Y-m-d H:i:s', HelpOther::requestTime() ) )
			->get()->result_array();

			$promoteList = reindexArray( $promoteList, 'sale_id' );
			$this->memcache->set( $cacheKey, $promoteList, $params );
		}
        if( isset( $promoteList[ $saleId ] ) ){
            $result = $promoteList[ $saleId ];
        }

		return $result;
	}

	/**
	 * 获得模板模块
	 * @param int $saleId 模板ID
	 * @return array 
	 */
	public function getModulesGroup( $saleId ){
		$cacheKey = "get_promote_modules_group_%s";
		$cacheParams = array( $saleId ) ;
		$modulesGroupData = $this->memcache->get( $cacheKey, $cacheParams );

		if( $modulesGroupData == FALSE ) {
			$result = $this->db_ebmaster_read
			->select('module_group_id, sale_id, title, type, status_nav')
			->from('eb_sale_module_group')
			->where('status', 1 )
			->where('sale_id', $saleId)
			->order_by('sort', 'desc')
			->get()->result_array();

			$modulesGroupData = array();
			foreach ( $result as $key => $record ) {
				$record['title'] = json_decode( $record['title'], true );
				$modulesGroupData[ $record['module_group_id'] ] = $record;
			}

			$this->memcache->set( $cacheKey, $modulesGroupData, $cacheParams );
		}

		return $modulesGroupData;
	}

	/**
	 * 获得模块信息
	 * @param int $saleId 模板ID
	 * @return array 
	 */
	public function getModules( $saleId ){
		$cacheKey = "get_promote_modules_%s";
		$cacheParams = array( $saleId ) ;
		$modulesData = $this->memcache->get( $cacheKey, $cacheParams );

		if( $modulesData == FALSE ) {
			$result = $this->db_ebmaster_read
			->select('module_id, module_group_id, sale_id, category_id, title, url, content, status_dst, status_url')
			->from('eb_sale_module')
			->where('status', 1 )
			->where('sale_id', $saleId)
			->order_by('sort', 'desc')
			->get()->result_array();

			$modulesData = array();
			foreach ( $result as $key => $record ) {
				$record['title'] = json_decode( $record['title'], true );
				$record['url'] = json_decode( $record['url'], true );
				$record['content'] = explode(',', $record['content'] );
				
				$modulesData[ $record['module_group_id'] ][] = $record;
			}

			$this->memcache->set( $cacheKey, $modulesData, $cacheParams );
		}

		return $modulesData;
	}

	/**
	 * 获得模块图片
	 * @param int $saleId 模板ID
	 * @return array 
	 */
	public function getModulesImages( $saleId ){
		$cacheKey = "get_promote_modules_images_%s";
		$cacheParams = array( $saleId ) ;
		$modulesImagesData = $this->memcache->get( $cacheKey, $cacheParams );

		if( $modulesImagesData == FALSE ) {
			$result = $this->db_ebmaster_read
			->select('module_id, module_group_id, sale_id, images')
			->from('eb_sale_module_image')
			->where('status', 1 )
			->where('sale_id', $saleId)
			->order_by('sort', 'desc')
			->get()->result_array();

			$modulesImagesData = array();
			foreach ( $result as $key => $record ) {
				$record['images'] = json_decode( $record['images'], true );
				$modulesImagesData[ $record['module_group_id'] ][] = $record;
			}

			$this->memcache->set( $cacheKey, $modulesImagesData, $cacheParams );
		}

		return $modulesImagesData;
	}

	/**
	 * 获得模板Banner
	 * @param  int $saleId 模板ID
	 * @return array
	 */
	public function getBanner( $saleId ){
		$cacheKey = "get_promote_banner_%s";
		$cacheParams = array( $saleId ) ;
		$result = $this->memcache->get( $cacheKey, $cacheParams );

		if( $result == FALSE ) {
			$result = $this->db_ebmaster_read
			->select('banner_id, sale_id, type')
			->from('eb_sale_banner')
			->where('status', 1 )
			->where('sale_id', $saleId )
			->limit(1)
			->get()->result_array();

			$result = current( $result );
			$this->memcache->set( $cacheKey, $result, $cacheParams );
		}

		return $result;
	}

	/**
	 * 获得模板Banner图片
	 * @param  int $saleId 模板ID
	 * @return array
	 */
	public function getBannerImage( $saleId, $bannerId ){
		$cacheKey = "get_promote_banner_image_%s_%s";
		$cacheParams = array( $saleId, $bannerId ) ;
		$imagesData = $this->memcache->get( $cacheKey, $cacheParams );

		if( $imagesData == FALSE ) {
			$result = $this->db_ebmaster_read
			->select('banner_id, sale_id, type, images, url, alt, color')
			->from('eb_sale_banner_image')
			->where('status', 1 )
			->where('sale_id', $saleId)
			->where('banner_id', $bannerId)
			->order_by('sort', 'desc')
			->get()->result_array();

			$imagesData = array();
			foreach ( $result as $key => $record ) {
				$record['images'] = json_decode( $record['images'], true );
				$record['url'] = json_decode( $record['url'], true );
				$record['alt'] = json_decode( $record['alt'], true );
				$imagesData[] = $record;
			}

			$this->memcache->set( $cacheKey, $imagesData, $cacheParams );
		}

		return $imagesData;
	}

	/**
	 * 获得大促多语言
	 * @param  int $saleId 模板ID
	 * @return array
	 */
	public function getDescription( $saleId ){
		$cacheKey = "get_promote_description_%s";
		$cacheParams = array( $saleId ) ;
		$result = $this->memcache->get( $cacheKey, $cacheParams );

		if( $result == FALSE ) {
			$result = $this->db_ebmaster_read
			->select('language_id, title, meta')
			->from('eb_sale_description')
			->where('sale_id', $saleId )
			->get()->result_array();

			$result = reindexArray( $result, 'language_id' );
			$this->memcache->set( $cacheKey, $result, $cacheParams );
		}

		return $result;
	}

	/**
	 * 获得大促右侧边栏
	 * @param  int $saleId 模板ID
	 * @return array
	 */
	public function getSidebar( $saleId ){
		$cacheKey = "get_promote_sidebar_%s";
		$cacheParams = array( $saleId ) ;
		$result = $this->memcache->get( $cacheKey, $cacheParams );

		if( $result == FALSE ) {
			$result = $this->db_ebmaster_read
			->select('category_id, type, images')
			->from('eb_sale_sidebar')
			->where('status', 1 )
			->where('sale_id', $saleId )
			->order_by('sort', 'asc')
			->get()->result_array();

			$this->memcache->set( $cacheKey, $result, $cacheParams );
		}

		return $result;
	}

	/**
	 * 获得模块商品ID
	 * @param type $id 模块对应的主键id
	 * @return type
	 */
	public function getModulePidsInfoByid( $id = '' ){
		if( empty( $id ) ){
			return array();
		}
		
		$cacheKey = 'get_promote_module_pids_%s';
		$cacheParams = array( $id );
		$result = $this->memcache->get( $cacheKey, $cacheParams );
	
		$id = intval( $id );
		if( $result == FALSE ) {
			$contentIds = $this->db_ebmaster_read
			->select('content')
			->from('eb_sale_module')
			->where('status', 1 )
			->where('module_id', $id)
			->get()->result_array();

			$contentIds = current( $contentIds );
			$result = explode(',', $contentIds['content'] );

			$this->memcache->set( $cacheKey, $result, $cacheParams );
		}

		return $result;
	}

	/**
	 * @param type $saleId
	 * @return type
	 */

	private function getPromoteList( $saleId = 0  ) {
		if(  $saleId <= 0){
			return array();
		}
		$cacheKey = "get_promotelist_%s";
		$params = array( $saleId );
		$result = $this->memcache->get( $cacheKey, $params );

		if( $result == FALSE ) {
			$result = $this->db_ebmaster_read
			->select('sale_id, name, type, color, start_time, end_time')
			->from('eb_sale')
			->where('sale_id', $saleId )
			->where('status', 1 )
			->where('start_time <=' , date( 'Y-m-d H:i:s', HelpOther::requestTime()+36000 ) )
			->where('end_time >' , date( 'Y-m-d H:i:s', HelpOther::requestTime() ) )
			->get()->result_array();

			$result = reindexArray( $result, 'sale_id' );
			$this->memcache->set( $cacheKey, $result, $params );
		}

		return $result;
	}

}
<?php 

namespace app\models;

use app\models\common\EbARModel as baseModel;
use app\components\helpers\HelpOther;
use app\components\helpers\HelpUrl;

/**
 * 全站banner  和通栏banner
*/
class Banner extends baseModel {
	//全站通栏banner缓存
	const MEM_KEY_ALL_SITE_BANNER = 'banner_all_site';//banner_all_site #全站banner mc key.
	//分类通栏 banner缓存
	const MEM_KEY_CATEGORY_BANNER = 'banner_category_%s';//banner_category_%s #分类通栏 banner缓存
	//移动全站通栏banner缓存
	const MEM_KEY_ALL_MOBILE_SITE_BANNER = 'banner_all_mobile_site';//banner_all_mobile_site #全站移动banner mc key.

	//type
	const TYPE_CATEGORY = 1 ;//分类的type 1
	const TYPE_ALL_SITE = 2 ;//全站banner的type 2
	const TYPE_MOBILE = 3 ;//移动banner的type 3
	//缓存时间
	const EXPIRE_TIME = 36000 ;
	
	private static $_instance = NULL;
	private $table = 'banner';
	/**
	 * 获取全站BANNER
	 * @param int $langId //语言ID
	 *
	 * @return $return = array(
	 * 		'id' => 2,
	 * 		'start_time' => "2014-09-30 00:15:00" ,
	 * 		'end' => "2014-10-31 00:00:00" ,
	 * 		'img' => "http://img5.eachbuyer.com/upload/201409/20140930102743201.jpg?v=20140228102610" ,
	 * 		'url' => "test" ,
	 * 		'alt' => "test" ,
	 * 		'end' => "2014-10-31 00:00:00" ,
	 * ) ;
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getAllSiteBanner( $langId = 1 ){
		//获取当前的请求的服务器时间
		$requestTime = HelpOther::requestTime() ;
		$formatRequestTime = date('Y-m-d H:i:s' , $requestTime );
		//获取缓存数据
		$cacheKey = self::MEM_KEY_ALL_SITE_BANNER ;
		$list = $this->memcache->get( $cacheKey, array() );

		if( $list === false ) {
			$formatENDTime = date( 'Y-m-d H:i:s' , ( $requestTime + self::EXPIRE_TIME ) );

			$sql = 'SELECT id,content,start_time,end_time FROM '. $this->table . ' WHERE type= :type and status = 1 and  start_time <= :startTime'
					. ' and end_time >  :endTime  order by end_time asc';
			
			$command =  $this->db_ebmaster_read->createCommand( $sql )
			->bindValue(':type' ,self::TYPE_ALL_SITE)
			->bindValue(':startTime' ,  $formatENDTime)
			->bindValue(':endTime' , $formatRequestTime);
			$result = $command->queryAll();

			$list = array();
			if( !empty( $result ) ){
				foreach($result as $record){
					$content = json_decode( $record['content'], TRUE );
					$list[ (int)$record['id'] ] = array(
							'id' => $record['id'] ,
							'content' => $content ,
							'start_time' => $record['start_time'] ,
							'end_time' => $record['end_time'] ,
					) ;
				}
			}
			$this->memcache->set( $cacheKey , $list );
		}
		$return = array();
		//循环出当前语言的全站banner
		if( !empty( $list ) ){
			foreach ( $list as $v ){
				if( ( $v[ 'start_time' ] <= $formatRequestTime ) && ( $formatRequestTime < $v[ 'end_time' ] ) ){
					$return = array(
						'id'=> $v['id'],
						'startTime' => $v['start_time'] ,
						'endTime' => $v['end_time'] ,
						'excessTime' => (int)( strtotime( $v['end_time'] ) - $requestTime ) ,
						'img'=> isset( $v['content'][ $langId ][ 'img' ] )? HelpUrl::imgSite( trim(  $v['content'][ $langId ][ 'img' ] ) ) : '#',
						'url'=> isset( $v['content'][ $langId ][ 'url' ] )? trim( $v['content'][ $langId ][ 'url' ] ) : '',
						'alt'=> isset( $v['content'][ $langId ][ 'alt' ] )? trim( $v['content'][ $langId ][ 'alt' ] ) : '#',
					);
					break;
				}
			}
		}
		return $return;
	}

	/**
	 * 获取移动全站BANNER
	 * @param int $langId //语言ID
	 * @return Array 返回移动通栏Banne数据
	 * @author lucas <luowenyong@hofan.cn>
	 */
	public function getAllMobileSiteBanner( $langId = 1 ){
		//获取当前的请求的服务器时间
		$requestTime = HelpOther::requestTime() ;
		$formatRequestTime = date('Y-m-d H:i:s' , $requestTime );
		//获取缓存数据
		$cacheKey = self::MEM_KEY_ALL_MOBILE_SITE_BANNER ;
		$list = $this->memcache->get( $cacheKey, array() );
		if( $list === false ) {
			$formatENDTime = date( 'Y-m-d H:i:s' , ( $requestTime + self::EXPIRE_TIME ) );
			$this->db_ebmaster_read->select('id,content,start_time,end_time');
			$this->db_ebmaster_read->from('banner');
			$this->db_ebmaster_read->where('type', self::TYPE_MOBILE );
			$this->db_ebmaster_read->where('status', 1 );
			$this->db_ebmaster_read->where('start_time <= ', $formatENDTime );
			$this->db_ebmaster_read->where('end_time >', $formatRequestTime );
			$this->db_ebmaster_read->order_by('end_time','asc');
			$query = $this->db_ebmaster_read->get();
			$result = $query->result_array();

			$list = array();
			if( !empty( $result ) ){
				foreach($result as $record){
					$content = json_decode( $record['content'], TRUE );
					$list[ (int)$record['id'] ] = array(
							'id' => $record['id'] ,
							'content' => $content ,
							'start_time' => $record['start_time'] ,
							'end_time' => $record['end_time'] ,
					) ;
				}
			}
			$this->memcache->set( $cacheKey , $list );
		}
		$return = array();
		//循环出当前语言的全站banner
		if( !empty( $list ) ){
			foreach ( $list as $v ){
				if( ( $v[ 'start_time' ] <= $formatRequestTime ) && ( $formatRequestTime < $v[ 'end_time' ] ) ){
					$return = array(
						'id'=> $v['id'],
						'startTime' => $v['start_time'] ,
						'endTime' => $v['end_time'] ,
						'text'=> isset( $v['content'][ $langId ][ 'text' ] )? eb_htmlspecialchars( trim(  $v['content'][ $langId ][ 'text' ] ) ) : '',
						'url'=> isset( $v['content'][ $langId ][ 'url' ] )? trim( $v['content'][ $langId ][ 'url' ] ) : '',
						'color'=> isset( $v['content'][ $langId ][ 'color' ] )? trim( $v['content'][ $langId ][ 'color' ] ) : '#e80000',
					);
					break;
				}
			}
		}
		return $return;
	}

	/**
	 * 获取分类通栏BANNER
	 * @param int $categoryId  //分类ID
	 * @param int $langId	//语言ID
	 *
	 * @return $return = array(
	 * 		'id' => 2,
	 * 		'start_time' => "2014-09-30 00:15:00" ,
	 * 		'end' => "2014-10-31 00:00:00" ,
	 * 		'img' => "http://img5.eachbuyer.com/upload/201409/20140930102743201.jpg?v=20140228102610" ,
	 * 		'url' => "test" ,
	 * 		'alt' => "test" ,
	 * 		'end' => "2014-10-31 00:00:00" ,
	 * ) ;
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getCategoryBannerById( $categoryId , $langId ){
		//获取当前的请求的服务器时间
		$requestTime = HelpOther::requestTime() ;
		$formatRequestTime = date('Y-m-d H:i:s' , $requestTime );
		//获取缓存数据
		$cacheKey = self::MEM_KEY_CATEGORY_BANNER ;
		$list = $this->memcache->get( $cacheKey , array( $categoryId ) );
		if( $list === false ) {
			$formatENDTime = date( 'Y-m-d H:i:s' , ( $requestTime + self::EXPIRE_TIME ) );
			//获取分类ID下的BANNER
			$this->db_ebmaster_read->select('banner_id');
			$this->db_ebmaster_read->from('category_banner');
			$this->db_ebmaster_read->where('category_id', $categoryId );
			$this->db_ebmaster_read->where('status', 1 );
			$query = $this->db_ebmaster_read->get();
			$result = $query->result_array();
			$banner_ids = extractColumn( $result , 'banner_id' );

			$list = array();
			if( !empty( $banner_ids ) ){
				$this->db_ebmaster_read->select('id,content,start_time,end_time');
				$this->db_ebmaster_read->from('banner');
				$this->db_ebmaster_read->where('type', self::TYPE_CATEGORY );
				$this->db_ebmaster_read->where('status', 1 );
				$this->db_ebmaster_read->where_in('id', $banner_ids );
				$this->db_ebmaster_read->where('start_time <= ', $formatENDTime );
				$this->db_ebmaster_read->where('end_time >', $formatRequestTime  );
				$this->db_ebmaster_read->order_by('end_time','asc');
				$query = $this->db_ebmaster_read->get();
				$result = $query->result_array();

				if( !empty( $result ) ){
					foreach($result as $record){
						$content = json_decode( $record['content'], TRUE );
						$list[ (int)$record['id'] ] = array(
								'id' => $record['id'] ,
								'content' => $content ,
								'start_time' => $record['start_time'] ,
								'end_time' => $record['end_time'] ,
						) ;
					}
				}
			}
			$this->memcache->set( $cacheKey , $list , array( $categoryId ) );
		}
		$return = array();
		//循环出当前语言的全站banner
		if( !empty( $list ) ){
			foreach ( $list as $v ){
				if( ( $v[ 'start_time' ] <= $formatRequestTime ) && ( $formatRequestTime < $v[ 'end_time' ] ) ){
					$return = array(
							'id'=> $v['id'],
							'startTime' => $v['start_time'] ,
							'endTime' => $v['end_time'] ,
							'excessTime' => (int)( strtotime( $v['end_time'] ) - $requestTime ) ,
							'text'=> isset( $v['content'][ $langId ][ 'text' ] )? trim( $v['content'][ $langId ][ 'text' ] ) : 'NULL',
							'url'=> isset( $v['content'][ $langId ][ 'url' ] )? trim( $v['content'][ $langId ][ 'url' ] ) : '',
							'color'=> isset( $v['content'][ $langId ][ 'color' ] )? trim( $v['content'][ $langId ][ 'color' ] ) : '#',
					);
					break;
				}
			}
		}
		return $return;
	}


	/**
	 * 获取详情页的banner
	 * @param int $pid
	 * @param int $langId
	 * @return $result = array()
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getGoodsBannerByPid( $pid , $langId =1 ){
		$return = array();
		//获取pid 的分类ID
		$result = Categoryv2Model::getInstanceObj()->getParentCategoryIdByPid( $pid );
		if(!empty( $result[$pid] ) && is_array( $result[$pid] ) && ( count( $result[$pid] ) >= 1 ) ) {
			//支分类优先 一级分类最后 翻转分类ID
			$result[ $pid ] = array_reverse( $result[ $pid ] );
			foreach ( $result[ $pid ] as $v ){
				$return = $this->getCategoryBannerById( $v , $langId );
				if( isset ( $return['id'] ) ){
					break;
				}
			}
		}
		 return $return ;
	}


	/**
	 * 获取实例化
	 * @return BannerModel
	 */
	public static function getInstanceObj( ){
		if ( self::$_instance === NULL ){
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	/**
	 * 初始化的方法
	 */
	public function __construct() {
		parent::__construct();
	}
}
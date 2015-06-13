<?php if ( ! defined('BASEPATH')) {exit('No direct script access allowed');}

/**
 * Model for Flashsale.
 * @author lucas
 */
class FlashsaleModel extends CI_Model {

	const STATUS_ENABLED = 1; //状态:启用
	const TYPE_BIG_PICTURE = 1; //大图
	const TYPE_SMALL_PICTURE = 2; //小图
	const CATEGORY_LIST_TOTAL = 20; //列表最多显示20条数据 即大图
	const CATEGORY_RIGHT_LIST_TOTAL = 6; //右侧列表最多显示5条数据即小图
	/**
	 * 初始化
	 */
	public function __construct(){
		parent::__construct();
	}

	/**
	 * 获取实例化
	 * @return FlashsaleModel
	 */
	public static function & getInstanceObj(){
		return parent::_getBaseInstanceObj( __CLASS__ );
	}

	/**
	 * flashsale推荐大图分类中间
	 * @param int $languageId  当前语言ID
	 *
	 * @author lucas
	 * @return array
	 */
	public function getBigPictureCategory( $languageId=1 ) {
		$bigPictureList = $this->_getFlashsaleList( self::TYPE_BIG_PICTURE );
		$i = 0;
		//收集当前生效的分类
		$catIds = array();
		$flashsaleBigPicture = array();
		$dateTime = date('Y-m-d H:i:s' , HelpOther::requestTime() );
		foreach ( $bigPictureList as $record ) {
			if( $i < self::CATEGORY_LIST_TOTAL && !in_array( $record['category_id'], $catIds ) && ( $record['start_time'] <= $dateTime ) && ( $dateTime <=$record['end_time'] ) ){
				$imageArr = json_decode( $record['image'], TRUE );
				if( !empty( $imageArr[ $languageId ] ) ){
					$catIds[ (int)$record['category_id'] ] = (int)$record['category_id'];
					$flashsaleBigPicture[ $record['category_id'] ] = array(
						'category_id' => $record['category_id'],
						'image' => HelpUrl::imgSite( $imageArr[ $languageId ] ),
						'discount' => $record['discount'],
						'start_time' => strtotime( $record['end_time'] ),
						'end_time' => strtotime( $record['end_time'] ),
						'name' => '' ,
						'en_name' => '' ,
					);
					$i++;
				}
			}
		}

		$categoryObj = Categoryv2Model::getInstanceObj();
		//组合分类信息
		$categoryList = $categoryObj->getCateInfoById( $catIds , $languageId );
		foreach ( $categoryList as $key => $record ) {
			$flashsaleBigPicture[ $key ]['name'] = $record['name'];
			$flashsaleBigPicture[ $key ]['en_name'] = $record['name'];
		}

		//英语名称
		if( $languageId != 1 ) {
			$categroyInfoEnArray = $categoryObj->getCateInfoById( $catIds, 1 );
			foreach ($flashsaleBigPicture as $key => $value) {
				$flashsaleBigPicture[ $key ]['en_name'] = isset( $categroyInfoEnArray[ $value['category_id'] ]['name'] ) && !empty( $categroyInfoEnArray[$value['category_id']]['name'] ) ? eb_htmlspecialchars( $categroyInfoEnArray[$value['category_id']]['name'] ):'';
			}
		}

		return $flashsaleBigPicture;
	}


	/**
	 * 促销推荐分类右侧
	 * @param int $languageId  当前语言ID
	 *
	 * @author lucas
	 * @return array
	 */
	public function getFlashSaleCategoryRight( $languageId ) {
		$rightPictureList = $this->_getFlashsaleList( self::TYPE_SMALL_PICTURE );

		$i = 0;
		//收集当前生效的分类
		$catIds = array();
		$flashSaleCategoryRight = array();
		$dateTime = date('Y-m-d H:i:s' , HelpOther::requestTime() );
		foreach ( $rightPictureList as $record ) {
			if( $i < self::CATEGORY_RIGHT_LIST_TOTAL && !in_array( $record['category_id'], $catIds ) && ( $record['start_time'] <= $dateTime ) && ( $dateTime <= $record['end_time'] ) ){
				$catIds[ (int)$record['category_id'] ] = (int)$record['category_id'];
				$imageArr = json_decode( $record['image'], TRUE );
				$flashSaleCategoryRight[ $record['category_id'] ] = array(
						'category_id' => $record['category_id'],
						'image' => HelpUrl::imgSite( $imageArr[ $languageId ] ),
						'en_name' => '',
				);
				$i++;
			}
		}

		//分类英语名称
		$categoryList = Categoryv2Model::getInstanceObj()->getCateInfoById( $catIds, 1 );
		foreach ( $categoryList as $key => $record ) {
			$flashSaleCategoryRight[ $key ]['en_name'] = $record['name'];
		}

		return $flashSaleCategoryRight;
	}



	/**
	 * 获得Flash sale列表数据
	 * @param int $type 1大图 2小图
	 * @return array 返回列表数组
	 * @author lucas
	 */
	private function _getFlashsaleList( $type = 1 ){
		$cacheKey = 'flasasale_%s';
		$cacheParams = array( (int)$type );
		$flashsaleResult = $this->memcache->get( $cacheKey, $cacheParams );

		//数据取出
		if( $flashsaleResult === false ) {
			$this->db_ebmaster_read->select('`category_id`, `image`, `discount`, `start_time`, `end_time`');
			$this->db_ebmaster_read->from('flashsale');
			$this->db_ebmaster_read->where( 'status' , self::STATUS_ENABLED );
			$this->db_ebmaster_read->where( 'type' , $type );
			$this->db_ebmaster_read->where( 'end_time >' , date( 'Y-m-d H:i:s' , HelpOther::requestTime() ) );
			//60*60*12 = 43200 超前获取12小时的数据内要开始的数据
			$this->db_ebmaster_read->where( 'start_time <=' , date( 'Y-m-d H:i:s' ,( HelpOther::requestTime()+ 43200 ) ) );
			$this->db_ebmaster_read->order_by( 'sort', 'desc' );
			$this->db_ebmaster_read->order_by( 'start_time', 'asc' );
			$this->db_ebmaster_read->order_by( 'id', 'desc' );
			$flashsaleResult = $this->db_ebmaster_read->get()->result_array();

			$this->memcache->set( $cacheKey, $flashsaleResult, $cacheParams );
		}

		return $flashsaleResult;
	}
}
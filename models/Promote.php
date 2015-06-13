<?php
namespace app\models;

use Yii;
use app\models\common\EbARModel as baseModel;
use app\components\helpers\ArrayHelper;
use app\components\helpers\HelpOther;
use app\components\helpers\HelpUrl;
use app\components\helpers\OtherHelper;


/**
 * Model for promote.
 * @author Terry Lu
 */
class Promote extends baseModel { 
	/**
	 * 7	6	5	4	3	2	1 （占位）
	 * 0	0	0	0	0	0	0 （没有占位状态）
	 * 1	1	1	1	1	1	1 （已经占位状态）
	 */
	/*promote_discount (促销折扣表) 中的type =1 折扣倒计时。*/
	const DISCOUNT_TYPE_NOMAL = 1;
	/*promote_discount (促销折扣表) 中的type =2 满减。*/
	const DISCOUNT_TYPE_REDUCTION = 2;
	/* promote_bundle（捆绑表） 中的 捆绑销售 type。 @var int */
	const BUNDLE_TYPE_BINDING = 3 ;
	/* promote_bundle（捆绑表）中的 买赠  type。  @var int */
	const BUNDLE_TYPE_FREEBIE = 4 ;
	/* promote_activity(团购和秒杀表)中的type属性：5团购。*/
	const PROMOTE_TYPE_GROUP_BUY = 5;
	/* promote_activity(团购和秒杀表)中的type属性：秒杀。*/
	const PROMOTE_TYPE_SEC_KILL = 6;

	/* 被捆绑商品的 促销状态*/
	const BUNDLE_TYPE_BINDING_TIED = 4001 ;

	/* 秒杀提前半小时预告 以及缓存半小时等 用同一变量 */
	const DISCOUNT_SEC_KILL_NOTICE_TIME = 1800 ;
	/* 秒杀提前24小时预告 */
	const DISCOUNT_SEC_KILL_NOTICE_TWENTYFOUR_TIME = 86400 ;

	/*Status 1可用，2禁用。*/
	const STATUS_ENABLED = 1;
	const STATUS_DISABLED = 0;

	/*促销rang表中的规范范围类型(1包含分类 )。*/
	const PROMOTE_RANG_TYPE_CID = 1;
	/*促销rang表中的规范范围类型( 2包含PID )。*/
	const PROMOTE_RANG_TYPE_PID = 2;
	/*促销rang表中的规范范围类型(3排除PID)。*/
	const PROMOTE_RANG_TYPE_PID_REMOVE = 3;

	/* 获取 promote_range （ 折扣和满减对应的规则ID ）的TYPEID  @var int */
	const PROMOTE_RANG_DISCOUNT = 1;
	/* 获取 promote_range （ 折扣和满减对应的规则ID ） 的TYPEID @var int */
	const PROMOTE_RANG_BUNDLE= 2;


	const PRO_DISCOUNT_INFO_MEM_KEY = 'proDiscountInfo%s'; //proDiscountInfo{$product_id} 产品的打折信息缓存key
	const DISCOUNT_INFO_MEM_KEY = 'discountInfo%s'; //discountInfo{$discount_id} 根据打折id缓存的打折信息key
	const PRO_SEC_KILL_INFO_MEM_KEY = 'proSecKillInfo%s'; //proSecKillInfo{$product_id} 产品的秒杀信息缓存key
	const PRO_GROUPON_INFO_MEM_KEY = 'proGpInfo%s'; //proGpInfo{$product_id} 产品的团购信息缓存key
	const PRO_All_BUNDLE_INFO_MEM_KEY = 'pro_all_bundleInfo_%s'; //pro_all_bundleInfo_{$type}  获取所有已开启的捆绑规则或者买赠信息 promote_bundle
	const PRO_ALL_FULL_REDUCTION = 'pro_all_full_reduction' ; //pro_all_full_reduction 获取所有满减规则
	const PRO_ALL_SECKILL_PRO_KEY = 'pro_all_seckill_pro' ; //pro_all_seckill_pro 缓存半小时内的秒杀商品
	const PRO_ALL_SECKILL_TWENTYFOUR_PRO_KEY = 'proTwentyfourSeckillInfo%s' ; //proTwentyfourSeckillInfo 缓存24小时内的秒杀数据

		
	private static $_tableName = 'promote_range';
	private static $_instance = NULL;
	
	public function __construct() {
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
	 * Get the discount info of product whitch have no cache from db, and save the info into memcache.
	 * @param int||array $productIds
	 * @return array $return #The discount info of product id or ids.
	 * @author Terry
	 */
	private function _getProDiscountInfoByIdsInDB($productIds){

		/*Get discount id of product no cache from promote range by product ids.*/
		$query = self::find();
		$query->select(['content','promote_discount_id'])->from('promote_range');
		// where in 好像不支持 或许没找到相应的写法
		$where = 'content in('. implode(',', $productIds) .')' . ' and type='. self::PROMOTE_RANG_TYPE_PID .  ' and promote_discount_id !=0 and status ='. self::STATUS_ENABLED;
		$query->where( $where );
		$resultArrayPromoteRange = $query->asArray()->all();

		/*Get the product discount ids in array(unique).*/
		$promoteDiscountIds = array();
		foreach($resultArrayPromoteRange as $vPromoteRange){
			$promoteDiscountIds[] = $vPromoteRange['promote_discount_id'];
		}
		$promoteDiscountIdsUnique = array_unique($promoteDiscountIds);

		/*Get the discount info from memcache and collect the discount id whitch have no cache.*/
		$discountInfoArr = array();
		$noCacheDiscountIds = array();
		foreach($promoteDiscountIdsUnique as $discountId){
			$discountInfo = $this->memcache->get(self::DISCOUNT_INFO_MEM_KEY,array( $discountId ));
			if($discountInfo === false){
				$noCacheDiscountIds[] = $discountId;
			}else{
				$discountInfoArr[$discountId] = $discountInfo;
			}
		}

		/*Get discount info of no cache discount id, and save them in the mem cache and $discountInfoArr.*/
		if (!empty($noCacheDiscountIds)) {
			$curDataTime = date('Y-m-d H:i:s',HelpOther::requestTime());
			$query = self::find();
			$query->select(['id','effect_value','start_time','end_time'])->from('promote_discount');
			$where = 'id in ('. implode(',' ,$noCacheDiscountIds ) . ') and type='. self::DISCOUNT_TYPE_NOMAL . ' and status=' . self::STATUS_ENABLED .
					' and end_time > \'' . $curDataTime . '\' ';
			$query->where($where);
			$resultArrayDiscount = $query->asArray()->all();
			
			foreach ($resultArrayDiscount as $discountInfo) {
				$formatDiscountInfo = array( 'id'=> $discountInfo['id'] , 'discount' => $discountInfo['effect_value'], 'start_time' => $discountInfo['start_time'], 'end_time' => $discountInfo['end_time']);
				$discountInfoArr[$discountInfo['id']] = $formatDiscountInfo;
				$this->memcache->set(self::DISCOUNT_INFO_MEM_KEY, $formatDiscountInfo, array($discountInfo['id']));
			}
		}

		/*Get the product discount info by $discountInfoArr and save it also into the memcache.*/
		$return = array();
		foreach($resultArrayPromoteRange as $vPromoteRange){
			if(!isset($discountInfoArr[$vPromoteRange['promote_discount_id']])){
				continue;
			}
//			//判断折扣最大的促销规则 缓存起来
//			if( isset( $return[$vPromoteRange['content']] ) && ( $return[$vPromoteRange['content']]['discount'] >= $discountInfoArr[$vPromoteRange['promote_discount_id']]['discount'] ) ){
//				continue;
//			}
			$return[$vPromoteRange['content']][$vPromoteRange['promote_discount_id']] = $discountInfoArr[$vPromoteRange['promote_discount_id']];
			$this->memcache->set(self::PRO_DISCOUNT_INFO_MEM_KEY, $return[$vPromoteRange['content']] ,array($vPromoteRange['content']));
		}
		return $return;
	}

	/**
	 * Get product discount info by product id.(Support batch)
	 * @param int||array $productIds
	 * @return array $return #The discount info of product id or ids.
	 * @author Terry
	 */
	public function getProDiscountInfoByIds($productIds){
		$return = array();
		if( !empty( $productIds ) ){
			/*If the product id is not in array, format it into array.*/
			if(!is_array( $productIds )){
				$productIds = array($productIds);
			}

			/*Get the product discount info from memcache and collect the product id whitch have no cache.*/
			$noCacheProIds = array();
			foreach($productIds as $productId){
				$proDiscountInfo = $this->memcache->get(self::PRO_DISCOUNT_INFO_MEM_KEY,array($productId));
				if($proDiscountInfo === false){
					$noCacheProIds[] = $productId;
				}else{
					$return[$productId] = $proDiscountInfo;
				}
			}

			/*Get the discount info of product whitch have no cache from db, and save the info into memcache. Add this nocache result to the result whitch chached.*/
			if(!empty($noCacheProIds)){
				$noCacheProDiscountInfo = $this->_getProDiscountInfoByIdsInDB($noCacheProIds);
				$return += $noCacheProDiscountInfo;
			}
		}

		return $return;
	}

	/**
	 * Get the second kill info of product whitch have no cache from db, and save the info into memcache.
	 * @param int||array $productIds
	 * @return array $return #The second kill info of product id or ids.
	 * @author Terry
	 */
	private function _getProSecKillInfoByIdsInDB($productIds){
		$return = array();
		$curDataTime = date('Y-m-d H:i:s',HelpOther::requestTime());
		$sql = 'select pat.target_discount,pat.target_limit_total,pat.target_limit_order ,'.
					 'pat.purchased_number,pat.product_id,pa.id,pa.start_time,pa.end_time from ' .
				'promote_activity_target pat left join promote_activity as pa on pat.promote_activity_id=pa.id '.
				'where pat.product_id in (' . implode( ',' , $productIds ) . ') and pat.target_status = '.self::STATUS_ENABLED .
				' and  pa.type=' . self::PROMOTE_TYPE_SEC_KILL . ' and pa.status='.self::STATUS_ENABLED .
				' and pa.end_time > \'' .$curDataTime . '\' order by pa.start_time desc ' ;
	
		$resultArraySecKill =  self::findBySql($sql)->asArray()->all();

		foreach($resultArraySecKill as $vSecKill){
			$productId = $vSecKill['product_id'];
			unset($vSecKill['product_id']);
			$return[$productId] = $vSecKill;
			$this->memcache->set(self::PRO_SEC_KILL_INFO_MEM_KEY,$vSecKill,array($productId));
		}
		return $return;
	}

	/**
	 * Get the groupon info of product whitch have no cache from db, and save the info into memcache.
	 * @param int||array $productIds
	 * @return array $return #The groupon info of product id or ids.
	 * @author Terry
	 */
	private function _getProGrouponInfoByIdsInDB($productIds){

		$return = array();
		$curDataTime = date('Y-m-d H:i:s',HelpOther::requestTime());
		$resultArray = $this->db_ebmaster_read->select('pat.target_discount,pat.product_id,pa.start_time,pa.end_time')->from('promote_activity_target pat')->join('promote_activity as pa', 'pat.promote_activity_id=pa.id','left')->where_in('pat.product_id',$productIds)->where('pat.target_status',self::STATUS_ENABLED)->where('pa.type',self::PROMOTE_TYPE_GROUP_BUY)->where('pa.status',self::STATUS_ENABLED)->where("(pa.end_time > '$curDataTime' )")->get()->result_array();
		foreach($resultArray as $v){
			$productId = $v['product_id'];
			unset($v['product_id']);
			$return[$productId] = $v;
			$this->memcache->set(self::PRO_GROUPON_INFO_MEM_KEY,$v,array($productId));
		}
		return $return;
	}

	/**
	 * Get the product second kill info by it's id.(Support batch)
	 * @param int||array $productIds
	 * @return array
	 * @author Terry
	 */
	public function getProSecKillInfoByIds( $productIds ){
		$return = array();
		if( !empty( $productIds ) ){
			/*If the product id is not in array, format it into array.*/
			if(!is_array($productIds)){
				$productIds = array($productIds);
			}
			/*Get the product second kill info from memcache and collect the product id whitch have no cache.*/
			$noCacheProIds = array();
			foreach($productIds as $productId){
				$proSecKillInfo = $this->memcache->get(self::PRO_SEC_KILL_INFO_MEM_KEY,array($productId));
				if($proSecKillInfo === false){
					$noCacheProIds[] = $productId;
				}else{
					$return[$productId] = $proSecKillInfo;
				}
			}

			/*Get the second kill info of product whitch have no cache from db, and save the info into memcache. Add this nocache result to the result whitch chached.*/
			if(!empty($noCacheProIds)){
				$noCacheProSecKillInfo = $this->_getProSecKillInfoByIdsInDB($noCacheProIds);
				$return += $noCacheProSecKillInfo;
			}
		}

		return $return;
	}

	/**
	 * Get the product groupon info by it's id.(Support batch)
	 * @param int||array $productIds
	 * @return array
	 * @author Terry
	 */
	public function getProGrouponInfoByIds($productIds){
		$return = array();
		if( !empty( $productIds ) ){
			/*If the product id is not in array, format it into array.*/
			if(!is_array($productIds)){
				$productIds = array($productIds);
			}

			/*Get the product groupon info from memcache and collect the product id whitch have no cache.*/
			$noCacheProIds = array();
			foreach($productIds as $productId){
				$proGrouponInfo = $this->memcache->get(self::PRO_GROUPON_INFO_MEM_KEY,array($productId));
				if($proGrouponInfo === false){
					$noCacheProIds[] = $productId;
				}else{
					$return[$productId] = $proGrouponInfo;
				}
			}

			/*Get the groupon info of product whitch have no cache from db, and save the info into memcache. Add this nocache result to the result whitch chached.*/
			if(!empty($noCacheProIds)){
				$noCacheInfo = $this->_getProGrouponInfoByIdsInDB($noCacheProIds);
				$return += $noCacheInfo;
			}
		}

		return $return;
	}

	/**
	 *  Buy Freebie Pro BY  $pids
	 *  根据PIDs 获取满足PID买赠规则的具体信息
	 *
	 * @param array $pids  PIDS //商品PID
	 * @return array $result  空则返回空数组  正常返回如下
	 *  array(
	 *  	$pid1 => array  //传参的PID1
	 *  	(
	 *  		'buyFreebie' => array //  buyFreebie
	 *  		(
	 *  			$pid1 => $discount1 ,// 被赠的PID
	 *  			$pid2 => $discount2 ,// 被赠的PID
	 *  			...
	 *  		),
	 *  	),
	 *  	...
	 *
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getBuyFreebieProByPid($pids){
		$result = array();
		//判断参数是否合法
		if( !empty( $pids ) ){
			if( ! is_array( $pids ) ) {
				$pids = array( $pids );
			}

			//获取买赠的规则信息 根据PID
			$result = $this->_getPromoteBundleByPids( $pids , self::BUNDLE_TYPE_FREEBIE );
		}
		return $result ;
	}

	/**
	 * get Bundling Sales Pro By Pid
	 *
	 * @param array $pids  PIDS //商品PID
	 * @return array $result  空则返回空数组  正常返回如下
	 *  array(
	 *  	$pid1 => array  //传参的PID1
	 *  	(
	 *  		'bundleInfo' => array //  被捆绑的商品信息 bundleInfo
	 *  		(
	 *  				$productId1=>array(		//被捆绑的PID
	 * 						'id' => $bundleId , //这条捆绑规则主键ID
	 * 						'pid' => $productId1 , //被捆绑的PID
	 * 						'discount' => $discount //被捆绑商品的折扣数
	 * 					),
	 * 					$productId2=>array( // 被捆绑的PID
	 * 						'id' => $bundleId , //这条捆绑规则主键ID
	 * 						'pid' => $productId2 , //被捆绑的PID
	 * 						'discount' => $discount //被捆绑商品的折扣数
	 * 					),
	 *  				...
	 *  		),
	 *  	),
	 *  	...
	 *
	 *
	 *
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getProBundlingSalesByPids( $pids ){
		$result = array();
		//判断参数是否合法
		if( !empty( $pids ) ){
			if( ! is_array( $pids ) ) {
				$pids = array( $pids );
			}

			//获取捆绑的规则信息 根据PID
			$result = $this->_getPromoteBundleByPids( $pids , self::BUNDLE_TYPE_BINDING );
		}
		return $result ;
	}

	/**
	 * 获取捆绑的规则信息 或者 买赠信息 根据PIDs
	 * @param array $pids  PIDS //商品PID
	 * @param int  $promoteBundleType // 默认是3。 捆绑表中的 捆绑销售 $promoteBundleType = 3   || 捆绑表中的 买赠  $promoteBundleType = 4
	 * @return array $result  空则返回空数组  正常返回如下
	 *  array(
	 *  	$pid1 => array  //传参的PID1
	 *  	(
	 *  		'bundleInfo/buyFreebie' => array //  当type为3  则为 bundleInfo ， 当type为4 则为buyFreebie
	 *  		(
	 *  				$productId1=>array(		//被捆绑的PID
	 * 						'id' => $bundleId , //这条捆绑规则主键ID
	 * 						'pid' => $productId1 , //被捆绑的PID
	 * 						'discount' => $discount //被捆绑商品的折扣数
	 * 					),
	 * 					$productId2=>array( // 被捆绑的PID
	 * 						'id' => $bundleId , //这条捆绑规则主键ID
	 * 						'pid' => $productId2 , //被捆绑的PID
	 * 						'discount' => $discount //被捆绑商品的折扣数
	 * 					),
	 *  			...
	 *  		),
	 *  	),
	 *  	...
	 *
	 *
	 *
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */

	private function _getPromoteBundleByPids( $pids , $promoteBundleType = 3 ){
		$result = array();
		//判断参数是否合法
		if( !empty( $pids ) ){
			if( ! is_array( $pids ) ) {
				$pids = array( $pids );
			}

			//获取每一PID 对应的父分类ID
			$categoryObj = new Categoryv2Model();
			$parentCatIdByPid = $categoryObj->getParentCategoryIdByPid( $pids );

			//获取所有捆绑的促销规则
			$allPromoteBundle = $this->getAllPromoteBundleByType( $promoteBundleType );
			if( !empty( $allPromoteBundle ) && is_array( $allPromoteBundle ) ){
				//处理结果的key提前处理
				$arrKeyTmp = ( $promoteBundleType === self::BUNDLE_TYPE_BINDING ) ? 'bundleInfo' : 'buyFreebie' ;
				$arrCidKeyTmp = 'cid' . $arrKeyTmp ;
				//判断商品是否满足某一条规则
				foreach ( $pids as $pid ){
					$result [ $pid ] = array();
					//判断PID 是否满足PID规则 PID 优先
					foreach ( $allPromoteBundle as $info){
						//判断PID在此规则中是否被排除 排除直接跳过此规则 进行下一个规则处理
						if( isset( $info['excludePid'][ $pid ] ) ){
							continue;
						}
						//判断PID  是否在规则的PID中
						if( isset( $info['pid'][ $pid ] ) && isset( $info['info'] ) ){
							$info['info'] = $this->_getActiveBindPros($info['info'],$pid);
							if (!empty($info['info'])) {
								$result [$pid][$arrKeyTmp] = $info['info'];
								break;
							}
						}
						//判断分类ID  在规则中
						if( !empty( $parentCatIdByPid[ $pid ] ) && is_array( $parentCatIdByPid[ $pid ] ) && !empty( $info[ 'cid' ]  ) && is_array( $info[ 'cid' ] ) ){
							$intersect = array_intersect( $parentCatIdByPid[ $pid ] , $info[ 'cid' ] ) ;
							if( count( $intersect ) >= 1 ){
								$info['info'] = $this->_getActiveBindPros($info['info'], $pid);
								if (!empty($info['info'])) {
									$result [$pid][$arrCidKeyTmp] = $info['info'];
								}
							}
						}
					}

					//另外处理逻辑 。此处不能再循环中处理 因为  因为有可能分类在前  而PID在后的这种情况 所以在此另外处理 因为结果要在循环完毕才能知晓
					//判断分类满足规则 的逻辑处理
					if( isset( $result [ $pid ][ $arrCidKeyTmp ] ) ){
						//前提 判断分类满足规则 的逻辑处理  而PID没有满足的规则 那么分类的规则信息 进行赋值 后unset掉
						if( !isset( $result [ $pid ][ $arrKeyTmp ] ) ){
							$result [ $pid ][ $arrKeyTmp ] = $result [ $pid ][ $arrCidKeyTmp ] ;
							unset( $result [ $pid ][ $arrCidKeyTmp ] );
						}else{
						//前提 判断分类满足规则 的逻辑处理  而PID也满足的规则 那么分类直接删除unset掉
							unset( $result [ $pid ][ $arrCidKeyTmp ] );
						}
					}
				}
			}
		}
		return $result ;
	}

	/**
	 * 从捆绑商品中过滤掉下架、侵权、sku不存在等状态的pid
	 * @param array $bindPros #捆绑商品数组
	 * @return array
	 * @author	Terry
	 */
	private function _getActiveBindPros($bindPros,$mainPid){
		if(isset($bindPros[$mainPid])){
			unset($bindPros[$mainPid]);
		}
		$pids = extractColumn($bindPros, 'pid');
		$productModel = new ProductModel();
		$proActiveStatus = $productModel->getActiveStatus($pids);
		foreach($proActiveStatus as $pid=>$activeStatus){
			if(!$activeStatus){
				unset($bindPros[$pid]);
			}
		}
		return $bindPros;
	}

	/**
	 * 获取所有已开启的捆绑规则和买赠信息 promote_bundle
	 *
	 * @param int  $promoteBundleType // 默认是3。 捆绑表中的 捆绑销售 $promoteBundleType = 3   || 捆绑表中的 买赠  $promoteBundleType = 4
	 *
	 * @return array $result  空则返回空数组  正常返回如下
	 * 		array( $promoteId  => array //促销ID
	 * 			(
	 * 				'cid' => array(
	 * 					$categoryId1 => $categoryId1 ,//分类ID 满足这条促销规则的分类ID（即CID）
	 * 					$categoryId2 => category2 ,//分类ID 满足这条促销规则的分类ID（即CID）
	 * 					...
	 * 				),
	 * 				'pid' => array(
	 * 					$productId1=>$productId1,//PID  满足这条促销规则的PID
	 * 					$productId2=>$productId2,//PID  满足这条促销规则的PID
	 * 					...
	 * 				),
	 * 				'info' => array(  //满足这条规则 被赠或者被捆绑的PID
	 * 					$productId1=>array(		//被捆绑的PID
	 * 						'id' => $bundleId , //这条捆绑规则主键ID
	 * 						'pid' => $productId1 , //被捆绑的PID
	 * 						'discount' => $discount //被捆绑商品的折扣数
	 * 					),
	 * 					$productId2=>array( // 被捆绑的PID
	 * 						'id' => $bundleId , //这条捆绑规则主键ID
	 * 						'pid' => $productId2 , //被捆绑的PID
	 * 						'discount' => $discount //被捆绑商品的折扣数
	 * 					),
	 * 					...
	 * 				),
	 * 			),
	 * 		)
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getAllPromoteBundleByType( $promoteBundleType =3 ){
		$result = array();
		$promoteBundleTypeArr = array( self::BUNDLE_TYPE_BINDING , self::BUNDLE_TYPE_FREEBIE ) ;
		//判断传的参数是否在缓存中
		if( in_array( $promoteBundleType , $promoteBundleTypeArr ) ){
			$cacheKey = self::PRO_All_BUNDLE_INFO_MEM_KEY;
			$result = $this->memcache->get( $cacheKey , $promoteBundleType );
			if( $result === FALSE || !is_array( $result ) ){
				//获取捆绑/买赠的规则ID 以及满足此规则的PID和CID
				$promoteRangeInfo = $this->_promoteRange( self::PROMOTE_RANG_BUNDLE );
				// promote_bundle
				if( !empty( $promoteRangeInfo ) && is_array( $promoteRangeInfo ) ){
					$this->db_ebmaster_read->select('`id` , `title` ,`type` ') ;
					$this->db_ebmaster_read->from('promote_bundle');
					$this->db_ebmaster_read->where( 'type' , $promoteBundleType ) ;
					$this->db_ebmaster_read->where( 'status' , self::STATUS_ENABLED ) ;
					$this->db_ebmaster_read->where_in( 'id' , array_keys( $promoteRangeInfo ) ) ;
					$resultSql = $this->db_ebmaster_read->get()->result_array();
					if( !empty( $resultSql ) && is_array( $resultSql ) ){
						$promoteBundleId = extractColumn( $resultSql , 'id' );
						if( count( $promoteBundleId  ) >= 1 ){
							$this->db_ebmaster_read->select('`promote_bundle_id` , `product_id` ,`discount` ') ;
							$this->db_ebmaster_read->from('promote_bundle_target');
							$this->db_ebmaster_read->where( 'status' , self::STATUS_ENABLED ) ;
							$this->db_ebmaster_read->where_in( 'promote_bundle_id' , $promoteBundleId ) ;
							$resultSqlBundle = $this->db_ebmaster_read->get()->result_array();

							if( !empty( $resultSqlBundle ) && is_array( $resultSqlBundle ) ){
								foreach ( $resultSqlBundle as $v ){
									$bundleId = (int) $v[ 'promote_bundle_id' ] ;
									//此规则下的排除PID
									if( !isset( $result [ $bundleId ]['excludePid'] ) && isset( $promoteRangeInfo[ $bundleId ][ 'excludePid' ] ) ){
										$result [ $bundleId ]['excludePid'] = $promoteRangeInfo[ $bundleId ]['excludePid'];
									}
									//满足规则的PID
									if( !isset( $result [ $bundleId ]['pid'] ) && isset( $promoteRangeInfo[ $bundleId ][ 'pid' ]) ){
										$result [ $bundleId ]['pid'] = $promoteRangeInfo[ $bundleId ]['pid'];
									}
									//规则的分类ID
									if( !isset( $result [ $bundleId ]['cid'] ) && isset( $promoteRangeInfo[ $bundleId ][ 'cid' ]) ){
										$result [ $bundleId ]['cid'] = $promoteRangeInfo[ $bundleId ]['cid'];
									}
									$result [ $bundleId ]['info'] [ $v['product_id'] ] = array(
											'id' => $bundleId ,
											'pid' => $v['product_id'] ,
											'discount' => (int)$v['discount'] ,
										);
								}
							}
						}
					}
				}

				$this->memcache->set( $cacheKey , $result , $promoteBundleType );
			}
		}

		return $result ;
	}

	/**
	 * 获取促销规则
	 * @param int $promoteType   1 是 获取 折扣和满减对应的规则ID， 2是获取捆绑和买赠规则ID
	 * @return array $result array()
	 * 		array( $promoteId  => array //促销ID
	 * 			(
	 * 				'cid' => array(
	 * 					$categoryId1 => $categoryId1 ,//分类ID 满足这条促销规则的分类ID（即CID）
	 * 					$categoryId2 => category2 ,//分类ID 满足这条促销规则的分类ID（即CID）
	 * 					...
	 * 				),
	 * 				'pid' => array(
	 * 					$productId1=>$productId1,//PID  满足这条促销规则的PID
	 * 					$productId2=>$productId2,//PID  满足这条促销规则的PID
	 * 					...
	 * 				),
	 * 			),
	 * 		)
	 *
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	private function _promoteRange( $promoteType = 2 ){
		//处理参数
		$promoteType = (int)$promoteType;
		$result = array();
		//判断传的参数是否在 规则中的
		if( in_array( $promoteType , array( self::PROMOTE_RANG_DISCOUNT , self::PROMOTE_RANG_BUNDLE ) ) ){
			//获取规则的类型  折扣或者满减对应的规则ID/是获取捆绑或者买赠规则ID
			if( $promoteType === self::PROMOTE_RANG_DISCOUNT  ){
				$this->db_ebmaster_read->select('`promote_discount_id` ,`type`,`content`') ;
			}else{
				$this->db_ebmaster_read->select('`promote_bundle_id` ,`type`,`content`') ;
			}
			$this->db_ebmaster_read->from('promote_range');
			if( $promoteType === self::PROMOTE_RANG_DISCOUNT ){
				$this->db_ebmaster_read->where('promote_discount_id >',0);
			}else{
				$this->db_ebmaster_read->where('promote_bundle_id >',0);
			}
			$this->db_ebmaster_read->where( 'status' , self::STATUS_ENABLED ) ;
			$this->db_ebmaster_read->order_by('type', 'asc');
			$resultSql = $this->db_ebmaster_read->get()->result_array();

			//处理数组
			if( !empty($resultSql) && is_array( $resultSql ) ){
				foreach ( $resultSql as $v ){
					$type = (int)$v['type'] ;
					//促销的ID
					$promoteId = ( ( $promoteType === self::PROMOTE_RANG_DISCOUNT ) ? (int)$v['promote_discount_id']:(int)$v['promote_bundle_id'] );
					//获取内容ID
					$contentId = (int)$v['content'] ;
					//获取分类的ID
					if( $type === self::PROMOTE_RANG_TYPE_CID ){
						$result[ $promoteId ][ 'cid' ][ $contentId ] = $contentId ;
					//获取pid
					}elseif( $type === self::PROMOTE_RANG_TYPE_PID ){
						$result[ $promoteId ][ 'pid' ][ $contentId ] = $contentId ;
					//获取排除PID
					} elseif ( $type === self::PROMOTE_RANG_TYPE_PID_REMOVE){
						$result[ $promoteId ][ 'excludePid' ][ $contentId ] = $contentId ;
					}
				}
			}
		}

		return $result ;
	}


	/**
	* get Full Reduction Pro By Pid
	*  根据PIDs 获取满足复合PID满减商品信息
	* @param array $pids  PIDS //商品PID
	* @return array $result  空则返回空数组  正常返回如下
	*  array(
	*  	$pid1 => array(  //传参的PID1
	*  		'fullReductionInfo' => array(  //满足这条规则详细信息
	* 			'id' => 1,//规则ID
	* 			'title' =>  '满300减10/满300 打折95',//标题
	* 			'rule'=> 300,//生效金额
	* 			'effect_type' => 1/2，//影响的类型 1是满减 2是打折
	* 			'effect_value' => 10 , // 当影响的类型 为1 这里的10代表为 满300减10元 当影响的类型 为2则是 满300折扣10% 即实际销售为270
	* 			'start_time' => '2014-08-26 01:45:00'//这条满减规则的开始时间
	* 			'end_time' => '2014-09-26 02:00:00'//这条满减规则的结束时间
	* 		),
	*  	),
	*  	...
	*
	*
	*
	* @author BRYAN - NYD  <ningyandong@hofan.cn>
	*/
	public function getProFullReductionByPid( $pids ){
		$result = array();
		//判断参数是否合法
		if( !empty( $pids ) ){
			if( ! is_array( $pids ) ) {
				$pids = array( $pids );
			}

			//获取每一PID 对应的父分类ID
			$categoryObj = new Categoryv2Model();
			$parentCatIdByPid = $categoryObj->getParentCategoryIdByPid( $pids );

			//获取所有满减促销规则
			$allFullReduction = $this->getAllFullReduction();
			if( !empty( $allFullReduction ) && is_array( $allFullReduction ) ){

				//判断商品是否满足某一条规则
				foreach ( $pids as $pid ){
					$result [ $pid ] = array();
					//判断PID 是否满足PID规则 PID 优先
					foreach ( $allFullReduction as $info){
						$arrKeyTmp =  'fullReductionInfo' ;
						$arrCidKeyTmp = 'cid' . $arrKeyTmp ;
						//判断PID  是否在规则的PID中
						if( isset( $info['pid'][ $pid ] ) && isset( $info['info'] ) ){
							$result [ $pid ][ $arrKeyTmp ] = $info['info'];
							break;
						}

						//判断分类ID  在规则中
						if( !empty( $parentCatIdByPid[ $pid ] ) && is_array( $parentCatIdByPid[ $pid ] ) && !empty( $info[ 'cid' ]  ) && is_array( $info[ 'cid' ] ) ){
							$intersect = array_intersect( $parentCatIdByPid[ $pid ] , $info[ 'cid' ] ) ;
							if( count( $intersect ) >= 1 ){
								$result [ $pid ][ $arrCidKeyTmp ] = $info['info'];
								break;
							}
						}
					}

					//判断根据PID 不在规则 分类ID在规则的处理
					if( ( !isset( $result [ $pid ][ $arrKeyTmp ] ) && isset( $result [ $pid ][ $arrCidKeyTmp ] ) ) ){
						$result [ $pid ][ $arrKeyTmp ] = $result [ $pid ][ $arrCidKeyTmp ] ;
						unset( $result [ $pid ][ $arrCidKeyTmp ] );
					}
				}
			}
		}

		return $result ;
	}


	/**
	 * 获得全部满减对应的规则信息
	 *
	 * @return Array 促销满减活动信息数组 包括即将开始的满减规则  空则返回空数组  正常返回如下
	 * 		array( $promoteId  => array //促销ID
	 * 			(
	 * 				'cid' => array(
	 * 					$categoryId1 => $categoryId1 ,//分类ID 满足这条促销规则的分类ID（即CID）
	 * 					$categoryId2 => category2 ,//分类ID 满足这条促销规则的分类ID（即CID）
	 * 					...
	 * 				),
	 * 				'pid' => array(
	 * 					$productId1=>$productId1,//PID  满足这条促销规则的PID
	 * 					$productId2=>$productId2,//PID  满足这条促销规则的PID
	 * 					...
	 * 				),
	 * 				'info' => array(  //满足这条规则详细信息
	 * 					'id' => 1,//规则ID
	 * 					'title' =>  '满300减10/满300 打折95',//标题
	 * 					'rule'=> 300,//生效金额
	 * 					'effect_type' => 1/2，//影响的类型 1是满减 2是打折
	 * 					'effect_value' => 10 , // 当影响的类型 为1 这里的10代表为 满300减10元 当影响的类型 为2则是 满300打折10%即30元
	 * 					'start_time' => '2014-08-26 01:45:00'//这条满减规则的开始时间
	 * 					'end_time' => '2014-09-26 02:00:00'//这条满减规则的结束时间
	 * 				),
	 * 			),
	 * 			...
	 * 		)
	 * @author lucas
	 * +@author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getAllFullReduction(){
		//获取当前的请求的服务器时间
		$requestTime = HelpOther::requestTime() ;
		$formatRequestTime = date('Y-m-d H:i:s' , $requestTime );
		$formatEndTime = date('Y-m-d H:i:s' , ( $requestTime + self::DISCOUNT_SEC_KILL_NOTICE_TWENTYFOUR_TIME ) );
		//获取mc数据
		$cacheKey = self::PRO_ALL_FULL_REDUCTION;
		$result = $this->memcache->get( $cacheKey );
		if( $result === FALSE || !is_array( $result ) ){
			$result = array();
			//获取满减的促销规则ID以及详细信息
			$allFullReductionIds = $this->_promoteRange( self::PROMOTE_RANG_DISCOUNT );
			if( count( $allFullReductionIds ) >= 1 ){
				//getDB
				$this->db_ebmaster_read->select( 'id,title,rule,effect_type,effect_value,start_time,end_time' );
				$this->db_ebmaster_read->from( 'promote_discount' );
				$this->db_ebmaster_read->where_in( 'id', array_keys($allFullReductionIds)  );
				$this->db_ebmaster_read->where( 'type', self::DISCOUNT_TYPE_REDUCTION );
				$this->db_ebmaster_read->where( 'status', self::STATUS_ENABLED );
				$where ='( `start_time`<= "' .$formatEndTime . '" AND `end_time` >= "' . $formatRequestTime . '" )' ;
				$this->db_ebmaster_read->where( $where );
				$query = $this->db_ebmaster_read->get();
				$resultSql = $query->result_array();
				//处理数据
				if( !empty( $resultSql ) && is_array( $resultSql ) ){
					foreach ( $resultSql as $v ){
						$promoteId = (int) $v[ 'id' ] ;
						$result[ $promoteId ] = array(
								'cid' => isset( $allFullReductionIds[ $promoteId ]['cid'] ) ? $allFullReductionIds[ $promoteId ]['cid'] :array() ,
								'pid' => isset( $allFullReductionIds[ $promoteId ]['pid'] ) ? $allFullReductionIds[ $promoteId ]['pid'] : array() ,
								'info' => $v ,
						);
					}
				}
			}
			$this->memcache->set( $cacheKey , $result );
		}
		//实时计算
		$realTimeResult = array();
		//判断预告 的 或者是正在促销的满减活动规则
		if( count( $result ) >= 1 ){
			foreach ( $result as $k => $v ){
				//获取预告的促销规则
				if ( ( $v['info']['start_time'] <= $formatRequestTime ) && ( $formatRequestTime < $v['info']['end_time'] ) ){
					$realTimeResult[ $k ] = $v ;
				}
			}
		}

		return $realTimeResult ;
	}


	/**
	 * 获取 促销的详细信息 根据PIDS
	 * @param array $pids  获取商品PIDS
	 * @param int $type 默认是1  目前只支持[1/2/3/4]  1则获取 非捆绑的促销ID  2 获取 所有促销规则ID  3获取非秒杀的促销ID 4 获取非秒杀 非捆绑的促销规则
	 *
	 * @return array  $result
	 * array(
	 * 		$pid1 => array(
	 * 			$促销ID[1/3/6] =>促销ID对应促销详情 //1=折扣倒计时;3=捆绑促销; 6=秒杀;
	 * 		),
	 * 		$pid2 => array(
	 *			6=>array(//秒杀
	 *				"target_discount"=>"40",//折扣数
	 *				"target_limit_total"=> "10",//总限制
	 *				"target_limit_order"=>"2",//单订单限制
	 *				"overplus_limit_total" => 10 ,//总剩余个数
	 *				"overplus_limit_order" => 2 , //每单剩余的个数
	 *				"start_time" =>	"2014-09-01 00:45:00",//开始时间
	 *				"end_time" => "2014-09-09 22:00:00",//结束时间
	 *				"purchased_number" => 0 , //已经秒杀个数
	 *				"is_foreshow" =>  TRUE/ FALSE , //TRUE 为预告 FALSE 为正在秒杀
	 *			),
	 *			3 => array(//被捆绑的PID 当type为2 才会获取此信息
	 *				$pid111 => 30 ,//折扣ID对应的折扣数
	 *				$pid222 => 20 ,
	 *				....
	 *			),
	 *			1=> array(//普通折扣倒计时
	 *				'discount' =>10 ,//折扣 数
	 *				'start_time' =>'2014-08-27 03:00:00' ,//开始时间
	 *				'end_time' =>'2014-09-30 03:00:00',//结束时间
	 *			),
	 * 		),
	 *
	 *
	 * )
	 *
	 *
	 *
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getPromoteInfoBypids( $pids , $type=1 ){
		$result = array( );
		//判断参数是否合法
		if( !empty( $pids ) ){
			if( ! is_array( $pids ) ) {
				$pids = array( $pids );
			}

			//循环遍历数组为KEY
			foreach (  $pids as $v ){
				$result[ $v ] = array();
			}

			//获取当前的请求的服务器时间
			$requestTime = HelpOther::requestTime() ;
			$formatRequestTime = date('Y-m-d H:i:s' , $requestTime );
			if( in_array( $type , array(1,2) ) ) {
				//获取秒杀商品
				$proSecKillInfos = $this->getProSecKillInfoByIds( $pids ) ;
				if( count( $proSecKillInfos)> 0 ){
					foreach ( $proSecKillInfos as $k => $info ){
						//剩余总秒杀数量
						if( ( (int)$info['purchased_number'] < (int)$info['target_limit_total'] ) && ( (int)$info['target_limit_order'] > 0 ) ){
							//总秒杀剩余个数
							$info['overplus_limit_total'] = ( (int)$info['target_limit_total'] - (int)$info['purchased_number'] ) ;
							//剩余单个订单的个数
							$info['overplus_limit_order'] = (( (int)$info['target_limit_order'] ) > $info['overplus_limit_total'] ) ? $info['overplus_limit_total'] :(int)$info['target_limit_order'] ;

							$secondsLeft = strtotime($info['start_time'])-$requestTime;
							if( $formatRequestTime < $info['end_time'] && $secondsLeft<self::DISCOUNT_SEC_KILL_NOTICE_TWENTYFOUR_TIME){
								$info['is_foreshow'] = ($secondsLeft>0)?TRUE:FALSE ;
								$result[ $k ][ self::PROMOTE_TYPE_SEC_KILL ] = $info;
							}
						}
					}
				}
			}


			//团购一期不上线

			//获取折扣商品
			$proDiscountInfos = $this->getProDiscountInfoByIds( $pids );
			if( count( $proDiscountInfos ) > 0 ){
				foreach ( $proDiscountInfos as $k => $infos ){
					foreach ($infos as $info) {
						if (( $info['start_time'] <= $formatRequestTime ) && ( $formatRequestTime < $info['end_time'] )) {
							if(isset($result[$k]) && isset($result[$k][self::DISCOUNT_TYPE_NOMAL]) && $info['discount']<=$result[$k][self::DISCOUNT_TYPE_NOMAL]['discount']){
								continue;
							}
							$result[$k][self::DISCOUNT_TYPE_NOMAL] = $info;
						}
					}
				}
			}

			if( in_array( $type , array( 2 , 3 ) ) ){
				//获取捆绑商品信息
				$proBundlingSales = $this->getProBundlingSalesByPids( $pids );
				if( count( $proBundlingSales ) > 0 ) {
					foreach ( $proBundlingSales as $k => $info ){
						if( isset( $info['bundleInfo'] ) ){
							$result[ $k ] [ self::BUNDLE_TYPE_BINDING ] = $info['bundleInfo'];
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * 获取所有秒杀商品
	 * 用于分类页 获取所有分类的秒杀商品
	 *
	 * @return array $result
	 * $result =	array(
	 * 		352172 => array( //$pid 被秒杀的PID
	 * 			'target_discount' => '20' ,//秒杀期间的折扣数
	 * 			'target_limit_total'=> '30',//秒杀的总个数
	 * 			'target_limit_order' => '2',//秒杀的每单限制
	 * 			'product_id' => '352172' ,//$pid 被秒杀的PID
	 * 			'start_time' => "2014-09-11 15:33:00" ,//秒杀的开始时间
	 * 			'end_time' => "2014-10-12 10:00:00" ,//秒杀的结束时间
	 * 		),
	 * 		...
	 * 	)
	 *
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getAllSecKillPro(){
		//获取当前的请求的服务器时间
		$requestTime = HelpOther::requestTime() ;
		$formatRequestTime = date('Y-m-d H:i:s' , $requestTime );
		$formatStartTime = date('Y-m-d H:i:s' , ( $requestTime + self::DISCOUNT_SEC_KILL_NOTICE_TWENTYFOUR_TIME ) );
		//获取当前key
		$cacheKey = self::PRO_ALL_SECKILL_PRO_KEY;
		$result = $this->memcache->get( $cacheKey );
		if( $result === FALSE || !is_array( $result ) ){
			$resultArraySecKill = $this->db_ebmaster_read
			->select('pat.target_discount,pat.target_limit_total,pat.target_limit_order,pat.purchased_number,pat.target_sort,pat.product_id,pa.start_time,pa.end_time')
			->from('promote_activity_target pat')
			->join('promote_activity as pa', 'pat.promote_activity_id=pa.id','left')
			->where('pat.target_status',self::STATUS_ENABLED)
			->where('pa.type' , self::PROMOTE_TYPE_SEC_KILL )
			->where('pa.status',self::STATUS_ENABLED)
			->where("(pa.start_time <= '$formatStartTime' )")
			->where("(pa.end_time > '$formatRequestTime' )")
			->order_by( 'target_sort asc' )
			->get()->result_array();
			$result = reindexArray( $resultArraySecKill , 'product_id' );
			$this->memcache->set( $cacheKey , $result );
		}

		return $result ;
	}

	/**
	 * 检验促销规则是否有效
	 * @param int  $type  默认  1.  1是添加校验，2是更新校验 。
	 *
	 * @param array $pidsPromotes 参数规则
	 * 	array(
	 * 		$sku1 => array( //商品的sku
	 * 			'pid' => $pid ,//sku 对应的PID
	 * 			'promoteType' => $promote_type ,//
	 * 					//	0->默认商品；
	 * 					//	1 =（1）->折扣倒计时；
	 * 					//4=（100）->捆绑；
	 * 					//5=（101）->捆绑加折扣；
	 * 					// 32=（100000）->秒杀；
	 * 					//33=（100001）->秒杀预估+折扣；
	 * 					//36=(100100)->秒杀预估+捆绑
	 * 					//4001=（被捆绑类型特殊）->被捆绑商品
	 * 			'promoteId' => $promote_id ,//对应促销type的促销ID
	 * 			'bindingPid' => 0 , //如果是被捆绑商品 这里 捆绑的主商品PID promote_type =4001  此字段为 捆绑的主商品
	 * 		),
	 * 		$sku2 => array( //商品的sku
	 * 			'pid' => $pid ,//sku 对应的PID
	 * 			'promoteType' => $promote_type ,//
	 * 					//	0->默认商品；
	 * 					//	1 =（1）->折扣倒计时；
	 * 					//4=（100）->捆绑；
	 * 					//5=（101）->捆绑加折扣；
	 * 					// 32=（100000）->秒杀；
	 * 					//33=（100001）->秒杀预估+折扣；
	 * 					//36=(100100)->秒杀预估+捆绑
	 * 					//4001=（被捆绑类型特殊）->被捆绑商品
	 * 			'promoteId' => $promote_id ,//对应促销type的促销ID
	 * 			'bindingPid' => 0 , //如果是被捆绑商品 这里 捆绑的主商品PID promote_type =4001  此字段为 捆绑的主商品
	 * 		),
	 * )
	 *
	 *
	 *
	 *
	 * @return $result
	 *	$result = array(
	 *		'isChange' => TRUE/FALSE , //是否发生改变规则 有一个改变则返回TRUE，无规则改变返回FALSE
	 *		'info' => array(
	 *			$sku1 => array( //商品的sku
	 *				'pid' => $pid ,//sku 对应的PID
	 * 				'promoteType' => $promote_type ,//
	 * 					//	0->默认商品；
	 * 					//	1 =（1）->折扣倒计时；
	 * 					//4=（100）->捆绑；
	 * 					//5=（101）->捆绑加折扣；
	 * 					// 32=（100000）->秒杀；
	 * 					//33=（100001）->秒杀预估+折扣；
	 * 					//36=(100100)->秒杀预估+捆绑
	 * 					//4001=（被捆绑类型特殊）->被捆绑商品
	 * 				'promoteId' => $promote_id ,//对应促销type的促销ID
	 * 				'bindingPid' => 0 , //如果是被捆绑商品 这里 捆绑的主商品PID promote_type =4001  此字段为 捆绑的主商品
	 * 				'isChange'=> TRUE/FALSE  //此规则是否发生改变
	 * 				'msg'=> 'seckill OK' //简单的提示语
	 * 			),
	 * 			...
	 * 		),
	 * 		'errorCode' => 10 , //参数错误 0是争取
	 * 		'errMsg' => 'Param Error' , //简单提示语
	 * 	 )
	 *
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 *
	 */
	public function checkPromoteByPids( $pidsPromotes , $type = 1 ){
		/**
		$pidsPromotes = array (
				'BI292' => array(
						'pid' => 319116 ,
						'promoteType' => 32 , //秒杀
						'promoteId'=> 9999 ,
				),
				'BI221' => array(
						'pid' => 317977 ,
						'promoteType' => 1 , //普通 折扣
						'promoteId'=> 11111 ,
				),
				'BI224' => array(
						'pid' => 317952 ,
						'promoteType' => 4 , //捆绑
						'promoteId'=>  4444,
				),
				'CR56' => array(
						'pid' => 352306 ,
						'promoteType' => 4001 , //被捆绑
						'promoteId'=> 4444 ,
						'bindingPid'=> 317952 , //捆绑在此pid 上
				),
		);
		*/
		$result = array( 'isChange' => FALSE , 'info' => array() , 'errorCode'=> 0 , 'errMsg'=> '' );
		$legalPromoteTypeArr = array( 1,4,5,32,33,36 ) ; //0->默认商品；1 =（1）->折扣倒计时； 4=（100）->捆绑；5=（101）->捆绑加折扣；  32=（100000）->秒杀；33=（100001）->秒杀预估+折扣；36=(100100)->秒杀预估+捆绑 4001=（被捆绑类型特殊）->被捆绑商品
		if( !empty( $pidsPromotes ) ){
			//循环获取PID  排除被捆绑的
			$promotePagePids = $bindindTidePromoteId = array();
			foreach ( $pidsPromotes as $v ){
				if( (int)$v[ 'promoteType' ] === self::BUNDLE_TYPE_BINDING_TIED ){
					$bindindTidePromoteId[ $v['promoteId'] ] = $v['pid'] ;
				}elseif( (int)$v[ 'promoteType' ] > 0 ){
					$promotePagePids[ $v['pid'] ] = $v ;
				}
			}

			//判断是否有捆绑商品 无捆绑商品 不用获取捆绑促销信息
			if( !empty( $bindindTidePromoteId ) && ( count( $bindindTidePromoteId ) >= 1 )  ){
				$getPromoteType = 2 ;
			}else{
				$getPromoteType = 1 ;
			}
			if( !empty( $promotePagePids ) && ( count($promotePagePids ) >= 1 ) ) {
				//根据PID 获取促销ID
				$getPromoteInfoByPids = $this->getPromoteInfoBypids( array_keys( $promotePagePids ) , $getPromoteType );
				//循环校验处理促销规则
				foreach ( $pidsPromotes as $k => $v ){
					if( (int)$v[ 'promoteType' ] > 0 ){
						if ( in_array( (int)$v[ 'promoteType' ] , $legalPromoteTypeArr ) ){
							//校验秒杀商品
							if( (int)$v[ 'promoteType' ] === 32 ){
								if( isset( $getPromoteInfoByPids[ (int)$v['pid'] ][ self::PROMOTE_TYPE_SEC_KILL ]['id'] ) && ( (int)$v['promoteId'] === ( int )$getPromoteInfoByPids[ (int)$v['pid'] ][ self::PROMOTE_TYPE_SEC_KILL ]['id'] ) ){
									$v['isChange'] = FALSE ;
									$v['msg'] = ' seckill OK ' ;
								}else{
									$result['isChange'] = TRUE;
									$v['isChange'] = TRUE  ;
									$v['msg'] = 'seckill Rule Failure' ;
								}
							//校验普通折扣商品
							}else if( in_array( (int)$v[ 'promoteType' ] ,array( 1 , 5 , 33 ) ) ) {
								if( isset( $getPromoteInfoByPids[ (int)$v['pid'] ][ self::DISCOUNT_TYPE_NOMAL ]['id'] ) && ( (int)$v['promoteId'] === ( int )$getPromoteInfoByPids[ (int)$v['pid'] ][ self::DISCOUNT_TYPE_NOMAL ]['id'] ) ){
									//校验普通折扣商品
									$v['isChange'] = FALSE ;
									$v['msg'] = ' Discount OK ' ;
								}else{
									$result['isChange'] = TRUE;
									$v['isChange'] = TRUE  ;
									$v['msg'] = 'Discount Rule Failure' ;
								}
							//校验捆绑的主商品 4 无需校验
							}else if( (int)$v[ 'promoteType' ] === 4 ){
									//捆绑主商品
									$v['isChange'] = FALSE ;
									$v['msg'] = 'bundle OK ' ;
							//校验被捆绑商品
							}else if( (int)$v[ 'promoteType' ] === self::BUNDLE_TYPE_BINDING_TIED ){
								if( isset( $getPromoteInfoByPids[ (int)$v['bindingPid'] ][ self::BUNDLE_TYPE_BINDING ][ $k ]['id'] ) && ( (int)$v['promoteId'] === ( int )$getPromoteInfoByPids[ (int)$v['bindingPid'] ][ self::BUNDLE_TYPE_BINDING ][ $k ]['id'] ) ){
									//校验被捆绑商品信息
									$v['isChange'] = FALSE ;
									$v['msg'] = ' bundle tied OK ' ;
								}else{
									$result['isChange'] = TRUE;
									$v['isChange'] = TRUE  ;
									$v['msg'] = 'Discount Rule Failure' ;
								}
							}else{
								//此规则无检验
								$v['isChange'] = FALSE ;
								$v['msg'] = ' Rule not check ' ;
							}
						}else{
							$v['isChange'] = FALSE ;
							$v['msg'] = 'Promote Product Type Wrongful' ;
						}
						//赋值
						$result['info'][ $k ] = $v ;
					}else{
						$v['isChange'] = FALSE ;
						$v['msg'] = 'No  Promote Product' ;
						$result['info'][ $k ] = $v ;
					}
				}
			}else{
				$result ['errorCode'] = 0 ;
				$result ['errMsg'] = 'No  Promote Product' ;
			}
		}else{
			$result ['errorCode'] = 10 ;
			$result ['errMsg'] = 'Param Error' ;
		}

		return $result ;
	}

	/**
	 * 更新秒杀商品的已购买个数 如果购买个数大于总数  则把秒杀规则修改为已抢购完毕的状态
	 * @param int $id 促销ID
	 * @param int $pid 产品ID
	 * @param int $purchasedNumber int 已经 抢购的格式
	 * @return boolean TRUE/ FALSE
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function updateSecKillInfo( $id , $pid , $purchasedNumber) {
		$this->db_ebmaster_write->set('purchased_number', $purchasedNumber );
		$this->db_ebmaster_write->where('promote_activity_id', $id);
		$this->db_ebmaster_write->where('product_id', $pid);
		$this->db_ebmaster_write->update('promote_activity_target');
		//修改缓存
		$this->memcache->delete( self::PRO_SEC_KILL_INFO_MEM_KEY , array( $pid ) );
		//proTwentyfourSeckillInfo%s 清空促销模板缓存
		$this->memcache->delete( 'proTwentyfourSeckillInfo%s', array( $id ) );

		return TRUE;
	}

	/**
	 * 获取指定秒杀规则信息
	 * @param array $ids 
	 * @return array 返回秒杀信息
	 * @author lucas
	 */
	public function getFlashsaleRuleList( $ids ){
		if( !is_array( $ids ) ){
			$ids = explode(',', $ids);
		}

		$idArr = array();
		foreach ($ids as $id) {
			$idArr[] = (int)trim( $id );
		}

		//获取当前的请求的服务器时间
		$requestTime = HelpOther::requestTime() ;
		$formatRequestTime = date('Y-m-d H:i:s' , $requestTime );
		$formatStartTime = date('Y-m-d H:i:s' , ( $requestTime + self::DISCOUNT_SEC_KILL_NOTICE_TWENTYFOUR_TIME ) );
		//获取秒杀规则缓存
		$resultArraySecKill = array();
		$cacheKey = self::PRO_ALL_SECKILL_TWENTYFOUR_PRO_KEY;
		foreach ($idArr as $id) {
			$cacheParams = array( (int)$id );
			$cacheData = $this->memcache->get( $cacheKey, $cacheParams );
			if( !empty( $cacheData ) ){
				$resultArraySecKill[] = $cacheData;
			}
		}

		$result = array();
		if( count( $resultArraySecKill ) > 0 ){
			foreach ($resultArraySecKill as $record) {
				if( is_array( $record ) ){
					foreach ($record as $value) {
						$result[ $value['product_id'] ] = $value;
					}
				}
			}
		}

		if( count( $resultArraySecKill ) < count( $idArr ) ){
			$result = $this->db_ebmaster_read
			->select('pat.target_discount,pat.target_limit_total,pat.target_limit_order,pat.purchased_number,pat.target_sort,pat.product_id,pa.start_time,pa.end_time,pa.id')
			->from('promote_activity_target pat')
			->join('promote_activity as pa', 'pat.promote_activity_id=pa.id','left')
			->where_in('pa.id', $idArr)
			->where('pa.type' , self::PROMOTE_TYPE_SEC_KILL )
			->where('pat.target_status',self::STATUS_ENABLED)
			->where('pa.status',self::STATUS_ENABLED)
			->where("(pa.start_time <= '$formatStartTime' )")
			->where("(pa.end_time > '$formatRequestTime' )")
			->order_by( 'pat.target_sort' , 'asc' )
			->order_by('pat.id', 'asc')
			->get()->result_array();
			$result = reindexArray( $result , 'product_id' );

			$ruleData = array();
			foreach ($result as $record) {
				$key = $record['id'];
				$ruleData[ $key ][] = $record;
			}
			foreach ($ruleData as $key => $record) {
				$this->memcache->set( $cacheKey, $record, array( $key ) );
			}
		}

		// 实例话商品操作类库
		$this->ProductModel = new ProductModel();
		$pids = extractColumn($result, 'product_id');
		$resultStatus = $this->ProductModel->getActiveStatus($pids);
		if(!empty($resultStatus)) {
			foreach ($resultStatus as $key => $value) {
				if($value == false) {
					unset($result[$key]);
				}
			}
		}
		return $result ;
	}
}

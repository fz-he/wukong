<?php

namespace app\models;

use Yii;
use app\models\Promote;
use app\models\Review;
use app\models\common\EbARModel as baseModel;
use app\models\Appmodel;
use app\components\helpers\HelpOther;
use app\components\helpers\HelpUrl;
use app\components\helpers\ArrayHelper;
use app\components\helpers\OtherHelper;

use app\config\AppConfig;
/**
 * Model for product.
 * @author Terry Lu
 */
class Product extends baseModel {

	const GOOD_IS_ON_SALE=1;//产品上架
	const GOOD_NOT_ON_SALE=0;//产品下架
	const MEM_KEY_PRO_INFO = 'proInfo%s%s';//proInfo{$product_id}{$$languageId} 产品信息的memcache缓存key
	const MEM_KEY_PRO_SKU_INFO = 'proSkuInfo%s';//proSkuInfo{$product_id} 产品的子sku信息的memcache缓存key
	const MEM_KEY_PRO_SKU_ATTR_INFO = 'proSkuAttrInfo%s%s';//proSkuAttrInfo{$product_id}{$languageId} 产品的子sku属性信息的memcache缓存key
	const MEM_KEY_PRO_ATTR_TITLE = 'proAttrTitle%s%s';//proAttrTitle{$attr_id}{$languageId} 产品属性多语言title的mc缓存key
	const MEM_KEY_PRO_ATTR_INFO = 'proAttrInfo%s%s';//proAttrInfo{$product_id}{$languageId} 产品narrow search属性的mc缓存key
	const MEM_KEY_PRO_BOUGHT_TOGETHER = 'proBtToge%s%s';//proBtToge{$baseCatId}{$finalCatId}{$languageId} #bought together mc key.
	const MEM_KEY_PRO_RELA_RECO = 'proReco%s%s';//proReco{$cid}{$pid}{$languageId} #Related recommond products mc key.
	const MEM_KEY_PRO_ALL_PICS = 'proPics%s';//proPics{$pid} #Product pictrues mc key.
	const MEM_KEY_SKU_STOCK = 'skuStock%s';//skuStock{$sku} #Sku stock mc key.
	const MEM_KEY_PRO_ACTIVE_STATUS = 'proActive%s';//proActive{$pid} #Product active status mc key.
	const SIZE_CHART_LINE_LIMIT = 6;//尺码表列数限制个数
	
	private static $_tableName = 'product';
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
		return self::$_tableName;
	}

	/**
	 * 检查商品的active状态(product和sku的上下架状态以及库存)
	 * @param int||array $pids #example: array(352306,351908...) or 352306
	 * @param boolean $cache
	 * @return array #example: array(352306=>TRUE,351908=>FALSE...)
	 * @author Terry
	 */
	public function getActiveStatus($pids,$cache = TRUE, $isCheckSku = TRUE){

		if ($cache) {
			$return = $this->memcache->ebMcFetchData($pids,self::MEM_KEY_PRO_ACTIVE_STATUS,array($this,'getActiveStatus'));
		}else{
			$return = array();

			/*根据pids从product表取出有效的记录，并将有效记录的pid存储到数组$activeProArrOfProduct。*/
			$query = self::find();
			$query->select('id')->from('product');
			$where = 'id in (' . implode(',' ,$pids) . ')' . ' and status = 1';
			$query->where(  $where );			
			$queryProduct = $query->asArray()->all();	
	    	$activeProArrOfProduct = empty( $queryProduct )? []:ArrayHelper::extractColumn($queryProduct, 'id');
	
			//$queryProduct = $this->db_ebmaster_read->select('id')->from('product')->where_in('id', $pids)->where('status', 1)->get();
//			$activeProArrOfProduct = $queryProduct? ArrayHelper::extractColumn($queryProduct->result_array(), 'id'):array();
			
			
			if($isCheckSku){
				/*根据pids从product_sku表取出有效的记录，并将有效记录的pid存储到数组$activeProArrOfProduct。*/
				//$queryProductSku = $this->db_ebmaster_read->select('product_id')->from('product_sku')->where_in('product_id', $pids)->where('status', 1)->get();
				//$activeProArrOfProductSku = $queryProductSku?extractColumn($queryProductSku->result_array(), 'product_id'):array();				
				
				$query = self::find();
				$query->select('product_id')->from('product_sku');
				$where = 'product_id in (' . implode(',' ,$pids) . ')' . ' and status = 1';
				$query->where( $where );			
				$queryProductSku = $query->asArray()->all();	
				$activeProArrOfProduct = empty( $queryProductSku )? []:ArrayHelper::extractColumn($queryProductSku, 'id');
			}
			
			/*循环pids判断pid是否在$activeProArrOfProduct和$activeProArrOfProduct中，在则表示该pid对应的product是有效商品。*/
			foreach ($pids as $pid){
				$return[$pid] = FALSE;
				if( $isCheckSku && in_array($pid,$activeProArrOfProduct) && in_array($pid,$activeProArrOfProductSku)  ){
					$return[$pid] = TRUE;
				}else if( in_array($pid,$activeProArrOfProduct) ){
					$return[$pid] = TRUE;
				}
			}			
		}
		
		return $return;
	}

	/**
	 * Get product info by id(support batch).
	 * @param int|array $pids #One product id or some product id in an array.
	 * @param int $languageId #The language id.
	 * @param int $type  默认是1  目前只支持 [1/2/3]  1则获取 非捆绑的促销ID  提供 首页分类页   2 获取 所有促销规则 提供商品详情页 以及其他信息页面。3获取非秒杀的促销规则，提供购物车。 4 获取非秒杀 非捆绑的促销规则
	 * @param array $defaultSku array( $pid => $sku , $pid1 =>$sku ); pid 下面的价格是否按照你传的sku 价格走，若pid下面传了sku 则以此sku 价格走
	 *									此参数 一个PID下面 只能对应一个sku 需注意！！！
	 * @return array $return #Product info of the product id.
	 * @author Terry + BRYAN - NYD  <ningyandong@hofan.cn>
	 *
	 *
	 */
	public function getProInfoById($pids, $languageId = 1 , $type = 1 , $defaultSkus = array() ) {
		$return = $result = array();//The array used to save the products info which we need to return.
		$pids = is_array($pids)?$pids:array($pids);//如果是单个product id，这里同样处理成数组，这样方便后面处理。

		if( !empty( $pids ) ){
			//获取PID的促销ID;
			$promoteObj =  Promote::getInstanceObj(); 
			$promoteInfoBypids = $promoteObj->getPromoteInfoBypids( $pids , $type  );
			//过滤出被捆绑的商品信息
			if( in_array( $type , array( 2 , 3 ) ) ){
				//获取被捆绑的PID
				foreach ( $promoteInfoBypids as $k => $v ){
					if( isset( $v[ Promote::BUNDLE_TYPE_BINDING ] ) && ( count( $v[ Promote::BUNDLE_TYPE_BINDING ] ) > 0 ) ) {
						foreach ( $v[ Promote::BUNDLE_TYPE_BINDING ] as $k1 => $v1 ){
							$pids[] = $k1;
						}
					}
				}
				$pids = array_unique( $pids );
			}
			$noCachePids = array();//The array used to save the pids whitch have no cache.
			foreach($pids as $pid){//Get product info from memcache, and mark the pid which have no cache.
				$productInfo = $this->memcache->get(self::MEM_KEY_PRO_INFO, array($pid, $languageId));
				if( $productInfo === false){
					$noCachePids[] = $pid;
				}else{
					$return[$pid] = $productInfo;
				}
			}

			/*Get the product info of product which have no cache.*/
			if(!empty($noCachePids)){ 
				$query = self::find()->from('product');
				$where = 'id in(' . implode(',', $noCachePids) . ')';
				$query->where( $where );
				$prosBasicInfo = $query->asArray()->all();
				
			//	$prosBasicInfo = $this->db_ebmaster_read->from('product')->where_in('id', $noCachePids)->get()->result_array();//Get the product basic info.
				$descList = $this->getProductMultiLanguages($noCachePids, $languageId);//Get product desc info.
				$productExtList = $this->getProductWareHouse($noCachePids, $languageId);//取出商品的海外仓库 sku warehouse
				$skuInfos = $this->getProductSkuInfo($noCachePids);//商品的下属sku信息
				$complexattr = $this->getProComplexattr($noCachePids,array($languageId));
				foreach ($prosBasicInfo as $proBasicInfo) {//合并商品的各种信息到一起。
					$pid = $proBasicInfo['id'];
					$mixedData = $proBasicInfo;
					$mixedData['content'] = '';
					$mixedData = array_merge($mixedData,isset($descList[$pid]) ? $descList[$pid]:array());
					//content 进行过滤
					$contentTmp = strip_tags( trim( $mixedData['content'] ) );
					$searchTmp = array( '\t' , '\r' , '\n' , '\\' );
					$contentTmp = str_replace( $searchTmp , '', $contentTmp );
					$mixedData['content'] = ( trim( $contentTmp ) === '' )?'':trim( $mixedData['content'] ) ;

					$mixedData += isset($productExtList[$pid]) ? $productExtList[$pid] : array();
					$reviewModel = Review::getInstanceObj();
					$mixedData['review_count'] = $reviewModel->getProReviewCount($pid,$languageId);//统计评论数，区分语言来统计。
					$mixedData['name'] = $mixedData['name'];
					$mixedData['formatName'] = OtherHelper::eb_substr($mixedData['name'], 65);
					//sku   $proBasicInfo['type'] =1  是单品  2是复合商品 。 复合商品： 最终 销售价格 是（ pid 的价格 + sku的销售价） 处理 。  单品价格 是按照单品的价格走 不需要sku加价  即 sku里面的 final_price 为0
					if( isset( $skuInfos[$pid] ) && is_array( $skuInfos[$pid] ) && (count( $skuInfos[$pid]) >=1 ) ){
						// 复合商品 sku 价格处理
						if( (int)$proBasicInfo['type'] === 2 ){
							foreach ( $skuInfos[$pid] as $k => $v ){
								$skuInfos[$pid][ $k ]['final_market_price'] = ( $v['market_price'] + $proBasicInfo['market_price'] );
								$skuInfos[$pid][ $k ]['final_price'] = ( $v['price'] + $proBasicInfo['price'] );
							}
						}else{ //单品 sku 价格处理
							foreach ( $skuInfos[$pid] as $k => $v ){
								$skuInfos[$pid][ $k ]['final_market_price'] = $proBasicInfo['market_price'] ;
								$skuInfos[$pid][ $k ]['final_price'] = $proBasicInfo['price'] ;
							}
						}

					}

					$mixedData['skuInfo'] = isset($skuInfos[$pid]) ? $skuInfos[$pid] : array();
					$mixedData['complexattr'] = isset($complexattr[$pid]) ? $complexattr[$pid] : array();
					$mixedData['mergedComplexAttr'] = $this->_getMergedComplexAttr($mixedData['complexattr'],$languageId);
					$this->_addSoldOut($mixedData);
					$this->memcache->set(self::MEM_KEY_PRO_INFO, $mixedData, array($pid, $languageId));
					$return[$pid] = $mixedData;
				}
			}
			//格式化 促销的数组
			//promote_type：33 是 秒杀预告+折扣   32是 秒杀或者秒杀预告 ; 37是 捆绑+折扣+秒杀/秒杀预告 36是秒杀预告+捆绑  5是 捆绑+折扣 ;  4是捆绑 ;1是折扣
			$result = $this->_getPromoteInfoBYGetProInfoById( $promoteInfoBypids , $return , $type ,$defaultSkus );
		}
		return $result;
	}

	private function _addSoldOut(&$proInfo){

		/*验证product以及sku的状态和库存，异常则前台展示sold out.*/
		$proStatus = current($this->getActiveStatus($proInfo['id']));
		if(!$proStatus){
			$proInfo['soldOut'] = TRUE;
			return ;
		}

		/*复合商品没有复合属性（数据错误），前台显示sold out.*/
		if($proInfo['type']==2 && !$proInfo['complexattr']){
			$proInfo['soldOut'] = TRUE;
			return ;
		}

		$proInfo['soldOut'] = FALSE;
	}

	/**
	 * 将产品下所有sku的属性、属性值合并。
	 * @param type $complexattr
	 * @return array
	 * @author Terry
	 */
	private function _getMergedComplexAttr($complexattr,$lanId=''){
		if(!$complexattr){
			return array();
		}
		$mergedAttrArr = array();
		foreach($complexattr as $skuAttrArr){
			foreach($skuAttrArr as $attrId=>$arrtVal){
				if (!isset($mergedAttrArr[$attrId])) {
					$attrTitle = current($this->getComplexAttrTitle($attrId, array($lanId)));
					$mergedAttrArr[$attrId]['title'] = OtherHelper::eb_htmlspecialchars($attrTitle);
				}
				$mergedAttrArr[$attrId]['vals'][$arrtVal['attrValId']] = array('text'=>$arrtVal['attrValTitle']);
			}
		}
		ksort($mergedAttrArr);
		return $mergedAttrArr;
	}

	/**
	 * 根据sku获取商品的pid
	 * @param  string $sku 商品的sku
	 * @author qcn
	 * @return integer
	 */
	public function getPidBySku($sku) {
		if(empty($sku)) {
			return 0;
		}
		$cacheKey = "product_getpidbysku_%s";
		$cacheCode = array($sku);
		$result = $this->memcache->get( $cacheKey , $cacheCode );
		if($result === false) {
			$this->db_ebmaster_read->select('product_id');
			$this->db_ebmaster_read->from('product_sku');
			$this->db_ebmaster_read->where('sku', $sku);
			$this->db_ebmaster_read->where('status', 1);
			$query = $this->db_ebmaster_read->get();
			$result = $query->result_array();
			if(!empty($result)) {
				$result = current($result);
				$result = (int)$result['product_id'];
			} else {
				$result = 0;
			}
			$this->memcache->set($cacheKey,$result,$cacheCode);
		}
		return $result;
	}

	/**
	 * 根据pid和语言id获取词码表信息。
	 * @param type $pid
	 * @param type $lanId
	 * @return array
	 * @author Terry Lu
	 */
	public function getSizeChart($pid,$lanId=1) {

		$cacheKey = "proSizeChart%s";
		$params = array($pid);
		$result = $this->memcache->get( $cacheKey , $params );
		if ($result === false) {
			$result = array();
			$resultDb = $this->db_ebmaster_read->select('product_id,title,content')->from('product_sizechart')->where('product_id', $pid)->where('status', 1)->order_by('id desc')->get()->row_array();
			if ($resultDb) {
				$title = json_decode($resultDb['title']);
				foreach($title as $k=>$titleItem){
					$result['title'][$k] = object_to_array($titleItem);
				}
				$result['content'] = json_decode($resultDb['content']);
				$this->_formatSizeChart($result);//按要求格式化处理尺码表数据。
			}
			$this->memcache->set($cacheKey,$result,$params);
		}

		$sizeChartData = $this->memcache->get( $cacheKey , $params );
		if ($sizeChartData) {
			$title = array();
			foreach ($sizeChartData['title'] as $titleKey=>$titleItemArr) {
				if(isset($titleItemArr[$lanId]) && $titleItemArr[$lanId]!=''){
					$title[$titleKey] = $titleItemArr[$lanId];
				}
			}
			$return = array('title' => $title, 'content' => $sizeChartData['content']);
		}else{
			$return = array();
		}
		return $return;
	}

	public function formatSizeChart($sizeChart) {
		foreach ($sizeChart['content'] as $key=>$sizeChartContent) {
			foreach ($sizeChartContent as $sizeChartContentKey => $sizeChartContentItem) {
				$sizeChartContentItem = is_array($sizeChartContentItem)?implode('~', $sizeChartContentItem):$sizeChartContentItem;
				$sizeChart['content'][$key][$sizeChartContentKey] = $sizeChartContentKey==0?'<b>'.$sizeChartContentItem.'</b>':$sizeChartContentItem;
			}
		}
		return $sizeChart;
	}

	public function formatSizeChartToInch($sizeChart) {
		foreach ($sizeChart['content'] as $key=>$sizeChartContent) {
			foreach ($sizeChartContent as $sizeChartContentKey => $sizeChartContentItem) {
				if (is_array($sizeChartContentItem)) {
					foreach ($sizeChartContentItem as $k => $v) {
						$sizeChartContentItem[$k] = (is_numeric($v) && $sizeChartContentKey != 0) ? cm_to_inch($v) : $v;
					}
					$sizeChartContentItem = implode('~', $sizeChartContentItem);
				} else {
					$sizeChartContentItem = (is_numeric($sizeChartContentItem) && $sizeChartContentKey != 0) ? cm_to_inch($sizeChartContentItem) : $sizeChartContentItem;
				}
				$sizeChart['content'][$key][$sizeChartContentKey] = $sizeChartContentKey==0?'<b>'.$sizeChartContentItem.'</b>':$sizeChartContentItem;
			}
		}
		return $sizeChart;
	}

	/**
	 * 按要求格式化处理尺码表数据。（1、如果某行或列的值都不存在或者都为"/",则过滤掉该行、列；2、限制列的最大数量为6。）
	 * @param type $result(尺码表数据)
	 * @author Terry Lu
	 */
	private function _formatSizeChart(&$result){

		$titleEffective = array();
		foreach($result['content'] as $contentKey=>$contentItem){
			$effective = false;
			foreach($contentItem as $contentItemKey=>$contentItemVal){
				if($contentItemKey==0){
					continue;
				}
				if($contentItemVal && $contentItemVal!='/'){
					$effective = true;
					$titleEffective[$contentItemKey] = true;
				}
			}
			if($effective===false){
				unset($result['content'][$contentKey]);
			}
		}
		$i = 0;
		foreach($result['title'] as $titleKey=>$titleVal){
			$i++;
			if($titleKey==0){
				continue;
			}
			if(!isset($titleEffective[$titleKey]) || $i>self::SIZE_CHART_LINE_LIMIT){
				$i--;
				unset($result['title'][$titleKey]);
				foreach($result['content'] as $contentKey=>$contentItem){
					unset($result['content'][$contentKey][$titleKey]);
				}
			}
		}

		if(count($result['content'])==0 || count($result['title'])==1){
			$result = array();
		}
	}

	private function _setSecondKillCountDownNormal(&$result, $k,$secondKillInfo) {
		$curTimestamp = HelpOther::requestTime();
		$startTimestamp = strtotime($secondKillInfo['start_time']);
		if ($startTimestamp < $curTimestamp) {
			$endTimestamp = strtotime($secondKillInfo['end_time']);
			$timeLeft = $endTimestamp - $curTimestamp;
			$result[$k]['countdownSeconds'] = ($timeLeft < 3600 * 24 * 7 && $timeLeft > 0) ? $timeLeft : '';
			$result[$k]['countdownTime'] = ($timeLeft < 3600 * 24 * 7 && $timeLeft > 0) ? format_seconds_to_clocktime($timeLeft) : '';
		}
	}

	/*
	 * 格式化促销的数据信息
	 * 仅供 GetProInfoById 方法使用
	 */
	private function _getPromoteInfoBYGetProInfoById( $promoteInfoBypids , $return , $type ,$defaultSkus=array() ){
		$result = array();
		if( !empty( $promoteInfoBypids ) && is_array( $promoteInfoBypids ) && !empty( $return ) && is_array( $return ) ){
			//二进制标识 秒杀是 100000 ; 捆绑是 100;折扣是1 进行组合
			//处理促销价格 以及促销的类型
			foreach ( $promoteInfoBypids as $k => $v ){
				if( isset( $return [ $k ] ) ){
					$result[ $k ] = $return [ $k ];
					if($result[$k]['soldOut']){
						$v = array();
					}
				}else{
					continue;
				}

				$defaultSku = isset($defaultSkus[$k])?$defaultSkus[$k]:'';

				//无折扣 promote_type为 0;
				$result[ $k ]['promote_type'] = 0;
				$result[ $k ]['sku'] = $defaultSku?$defaultSku:($return[$k]['type'] == 1 ? key($return[$k]['skuInfo']) : '');
				$result[ $k ]['market_price'] += $defaultSku ? $return[$k]['skuInfo'][$defaultSku]['market_price'] : 0;
				$result[ $k ]['final_price'] = $defaultSku ? ($return[$k]['price']+$return[$k]['skuInfo'][$defaultSku]['price']) : $return[$k]['price'];
				$result[ $k ]['promote_discount'] = 0 ;
				$result[ $k ]['format_promote_discount'] = 0 ;
				$result[ $k ]['promote_id'] = 0 ;

				//判断正在秒杀的商品 32=（100000）
				if( isset( $v[ Promote::PROMOTE_TYPE_SEC_KILL ] ) ){
					$this->_setSecondKillCountDownNormal($result,$k,$v[ Promote::PROMOTE_TYPE_SEC_KILL ]);/*设置普通模式的秒杀倒计时*/

					$result[ $k ]['promote_type'] += HelpOther::occupied( Promote::PROMOTE_TYPE_SEC_KILL ) ;
					$result[ $k ]['promote_info'][ Promote::PROMOTE_TYPE_SEC_KILL ] = $v[ Promote::PROMOTE_TYPE_SEC_KILL ] ;
					$result[ $k ]['promote_id'] = $v[ Promote::PROMOTE_TYPE_SEC_KILL ]['id'];
					//秒杀预告将不影响现在销售的价格 否则 将处理为处理秒杀价格
					if( $v[ Promote::PROMOTE_TYPE_SEC_KILL ]['is_foreshow'] === FALSE ){
						$result[ $k ]['promote_discount'] = (int)$v[ Promote::PROMOTE_TYPE_SEC_KILL ]['target_discount']  ;
						//秒杀的库存做处理


						// 1. 把type二进制转化十进制  2.处理折扣格式化 向下取5整数 3.复合商品 sku 价格处理 4.最终销售价
						$result[ $k ] = $this->_getPromoteInfoBYGetProInfoByIdOther( $result[ $k ] );

						continue;
					}
				}
				//普通折扣 1 =（1）->折扣倒计时
				if( isset( $v[ Promote::DISCOUNT_TYPE_NOMAL ] ) ){
					$result[ $k ]['promote_type'] += HelpOther::occupied( Promote::DISCOUNT_TYPE_NOMAL ) ;
					$result[ $k ]['promote_info'][ Promote::DISCOUNT_TYPE_NOMAL ] = $v[ Promote::DISCOUNT_TYPE_NOMAL ] ;
					$result[ $k ]['promote_discount'] = (int)$v[ Promote::DISCOUNT_TYPE_NOMAL ]['discount'] ;
					$result[ $k ]['promote_id'] = $v[ Promote::DISCOUNT_TYPE_NOMAL ]['id'];
				}
				//判断 捆绑   4=（100）->捆绑；  4001  被捆绑商品的促销类型
				if( isset( $v[ Promote::BUNDLE_TYPE_BINDING ] ) && ( count( $v[ Promote::BUNDLE_TYPE_BINDING ] ) > 0 ) ){
					$result[ $k ]['promote_type'] += HelpOther::occupied( Promote::BUNDLE_TYPE_BINDING ) ;
					$BundleIdTmp = 0 ;
					//获取被捆绑的商品信息
					foreach (  $v[ Promote::BUNDLE_TYPE_BINDING ] as $vPidTmp=> $discountTmp ){
						if( isset( $return [ $vPidTmp ] ) ){
							$BundleIdTmp =  (int)$discountTmp['id'] ;
							$result[ $k ]['promote_info'][ Promote::BUNDLE_TYPE_BINDING ][ $vPidTmp ] = $return [ $vPidTmp ] ;
							$result[ $k ]['promote_info'][ Promote::BUNDLE_TYPE_BINDING ][ $vPidTmp ]['binding_id']  = $BundleIdTmp ;
							$result[ $k ]['promote_info'][ Promote::BUNDLE_TYPE_BINDING ][ $vPidTmp ]['promote_id']  = $BundleIdTmp ;
							$result[ $k ]['promote_info'][ Promote::BUNDLE_TYPE_BINDING ][ $vPidTmp ]['promote_type'] = Promote::BUNDLE_TYPE_BINDING_TIED;
							$result[ $k ]['promote_info'][ Promote::BUNDLE_TYPE_BINDING ][ $vPidTmp ]['binding_discount'] = (int)$discountTmp['discount'] ;
							$result[ $k ]['promote_info'][ Promote::BUNDLE_TYPE_BINDING ][ $vPidTmp ]['promote_discount'] = (int)$discountTmp['discount'] ;

							// 1. 把type二进制转化十进制  2.处理折扣格式化 向下取5整数 3.复合商品 sku 价格处理 4.最终销售价
							$result[ $k ]['promote_info'][ Promote::BUNDLE_TYPE_BINDING ][ $vPidTmp ] = $this->_getPromoteInfoBYGetProInfoByIdOther( $result[ $k ]['promote_info'][ Promote::BUNDLE_TYPE_BINDING ][ $vPidTmp ] );
							//最终价格 赋值给最终捆绑销售价格
							$result[ $k ]['promote_info'][ Promote::BUNDLE_TYPE_BINDING ][ $vPidTmp ]['binding_price'] = $result[ $k ]['promote_info'][ Promote::BUNDLE_TYPE_BINDING ][ $vPidTmp ]['final_price'] ;
						}
					}
					if(( int )$result[ $k ]['promote_id'] <=0 ){
						$result[ $k ]['promote_id'] = $BundleIdTmp ;
					}
				}
				//注意 再次添加的方法 1.在此添加方法   2 .需要在正在秒杀的 哪里  单独处理一下 。3.需要在被捆绑上也单独处理一下   若写到 _getPromoteInfoBYGetProInfoByIdOther 方法里面 则不需要处理3个地方全部处理
				// 1. 把type二进制转化十进制  2.处理折扣格式化 向下取5整数 3.复合商品 sku 价格处理 4.最终销售价
				$result[ $k ] = $this->_getPromoteInfoBYGetProInfoByIdOther( $result[ $k ] );

				//处理促销倒计时
				$result[$k]['countdownTime'] = '';
				$curTimestamp = HelpOther::requestTime();
				switch ($result[ $k ]['promote_type']) {
					case 1:
					case 5:
						$endTimestamp = strtotime($result[ $k ]['promote_info'][1]['end_time']);
						$timeLeft = $endTimestamp - $curTimestamp;
						$result[ $k ]['countdownSeconds'] = ($timeLeft < 3600 * 24 * 7 && $timeLeft > 0) ? $timeLeft : '';
						$result[ $k ]['countdownTime'] = ($timeLeft < 3600 * 24 * 7 && $timeLeft > 0) ? format_seconds_to_clocktime($timeLeft) : '';
						break;
					default:
						break;
				}
			}
		}

		return $result;
	}


	/**
	 *
	 * 转提供 私有方法  _getPromoteInfoBYGetProInfoById()使用
	 *  功能说明 目前处理
	 *     1. 折扣 二进制转10进制
	 *     2. 格式化折扣
	 *     3. 复合商品 下面所有sku 的 final_price根据促销重新算一下
	 *     4. PID 的最终销售价格
	 * @param array $result
	 *
	 * @return array();
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	private function _getPromoteInfoBYGetProInfoByIdOther( $result = array() ){
		//把type二进制转化十进制
		if( isset( $result['promote_type'] ) && ( (int)$result['promote_type'] > 0 ) &&  ( (int)$result['promote_type'] !== Promote::BUNDLE_TYPE_BINDING_TIED )  ){
			$result['promote_type'] = bindec( $result['promote_type'] );
		}
		//格式化促销 按照向下取5的整数   处理折扣数 目前是向下取5的整数 以及 最终销售价计算
		if( isset( $result['promote_discount'] ) && ( (int)$result['promote_discount'] > 0 ) ){
			$result['format_promote_discount'] = HelpOther::getRoundedDown( $result['promote_discount'] , 5 );
			//最终销售价计算
			$result['final_price'] = round( ( ( $result ['market_price'] )* ( 1 - ( $result['promote_discount'] / 100 ) ) ) , 2 ) ;
		}
		//复合商品 sku 价格处理
		if( ( $result['promote_type'] > 0 ) && ( $result['promote_discount'] > 0 ) && isset( $result[ 'skuInfo' ] ) && is_array( $result[ 'skuInfo' ] ) && ( count( $result[ 'skuInfo' ] ) >=1 ) ){
			foreach ( $result[ 'skuInfo' ] as $kSku => $vSkuInfo ){
				$result[ 'skuInfo' ][ $kSku ][ 'final_price' ] = round( $vSkuInfo['final_market_price'] * ( 1- ( $result['promote_discount'] / 100 ) ) , 2 ) ;
			}
		}

		return  $result ;
	}

	public function getProAllpics($pid,$defaultSku=''){
		$return = $this->memcache->get(self::MEM_KEY_PRO_ALL_PICS,array($pid));
		if($return===false){
			$resDb = $this->db_ebmaster_read->select('sku,image')->from('product_image')->where('product_id',$pid)->where('status',1)->order_by( 'sort desc , id asc' )->order_by('sku')->get();
			if($resDb){
				$return = $resDb->result_array();
				$this->memcache->set(self::MEM_KEY_PRO_ALL_PICS,$return,array($pid));
			}
		}

		if ($defaultSku) {
			$fistItem = array();
			foreach ($return as $k => $v) {
				if ($v['sku'] == $defaultSku) {
					$fistItem[] = $v;
					unset($return[$k]);
				}
			}
			$return = array_merge($fistItem,$return);
		}

		return $return;
	}

	/**
	 * Get the gallery list of product.(Support Batch)
	 * @param array|int $proIds
	 * @return array
	 * @author Terry
	 */
	public function getProGalleryList($proIds){

		$return = array();
		if (empty($proIds)) {
			return $return;
		}

		if (!is_array($proIds)) {
			$proIds = array($proIds);
		}

		$memKey = "proGalleryList_%s";
		$noCacheProIds = array();
		foreach($proIds as $proId){
			$proGalleryList = $this->memcache->get($memKey,array($proId));
			if($proGalleryList === false){
				$noCacheProIds[] = $proId;
			}else{
				$return[$proId] = $proGalleryList;
			}
		}

		/*Get the gallery list from db for no cache products.*/
		if(!empty($noCacheProIds)){
			$resultArray = $this->db_ebmaster_read->from('product_gallery')->where_in('product_id',$noCacheProIds)->where('status',1)->order_by('product_id')->order_by( 'sort desc , id asc' )->get()->result_array();
			$proGalleryListsArr = spreadArray($resultArray,'product_id');
			foreach($proGalleryListsArr as $proId => $proGalleryList){
				foreach($proGalleryList as $galleryKey=>$proGallery){
					$proGalleryList[$galleryKey]['img45'] = HelpUrl::img($proGallery['image'], '45');
					$proGalleryList[$galleryKey]['img350'] = HelpUrl::img($proGallery['image'], '350');
					$proGalleryList[$galleryKey]['img500'] = HelpUrl::img($proGallery['image'], '500');
				}
				$this->memcache->set($memKey,$proGalleryList,array($proId));
				$return[$proId] = $proGalleryList;
			}
		}

		return $return;
	}


	/**
	 * 获取商品下属的sku信息
	 * @param int||array $pids #商品的pid
	 * @return array
	 *
	 * @author ningyandong@hofan.cn
	 */
	public function getProductSkuInfo( $pids , $all = FALSE ){
		$result = array();
		$pidsArr = is_array( $pids )?$pids:array($pids);
		if( !empty( $pidsArr ) ){
			//批量生产key 的方法
			$keys = $noCachePids =array();
			foreach ( $pidsArr as $v ){
				$keys[$v] = $v ;
			}
			$result = $this->memcache->batchGetMc( self::MEM_KEY_PRO_SKU_INFO , $keys );
			foreach ( $result as $k => $v ){
				if( $v === FALSE ){
					$noCachePids[ $k ] = $k ;
				}
			}
			if( !empty( $noCachePids ) ){
				//获取DB
				$query = self::find()->select(  'product_id,sku,image,price,market_price,type,purchase_price,cost_price,length,width,height,weight,type_sensitive,warehouse,stock,flg_infringe,status')->from('product_sku');
				$where = 'product_id in(' . implode(',', $noCachePids) . ')';
				$query->where( $where );
				$resArr = $query->asArray()->all();
				//格式化数据
				if( !empty( $resArr ) && is_array( $resArr )) {
					foreach($resArr as $v){
						$pidTmp = (int)$v['product_id'] ;
						//最终sku最终加价 复合商品需要此价格
						$v[ 'final_price' ] = 0 ;
						$v[ 'final_market_price' ] = 0 ;
						$result[ $pidTmp ][ $v['sku'] ] = $v;
						$result[ $pidTmp ][ $v['sku'] ]['stock'] = 65535;
					}
				}
				

				//设置未缓存的数组
				foreach ($noCachePids as $v ){
					if( $result[ $v ] === FALSE ){
						$result[ $v ] = array();
					}
					//设置缓存
					$this->memcache->set( self::MEM_KEY_PRO_SKU_INFO  , $result[ $v ] , $v );
				}
			}
		}
		//处理掉废弃的sku 即下架的 处理
		if( $all === FALSE ){
			foreach ($result as $pidKey => $skuInfos ){
				foreach ( $skuInfos as $skuKey => $skuInfo ){
					if( (int)$skuInfo[ 'status' ] !== 1 ){
						unset( $result[ $pidKey ][ $skuKey ] );
					}
//					if($skuInfo['type']==1 && $skuInfo['stock']==0){
//						unset( $result[ $pidKey ][ $skuKey ] );
//					}
				}
			}
		}
		return $result;
	}

	/**
	 * 获取boughtTogether的商品信息
	 * @param str $proIdPath  详情页商品的分类id path.
	 * @param int $languageId
	 * @return arr
	 * @author Terry
	 */
	public function getBoughtTogether($proIdPath,$languageId){
		$catIdArr = explode('/',$proIdPath);
		$baseCatId = $catIdArr[0];
		$finalCatId = end($catIdArr);
		$memParams = array($baseCatId,$finalCatId);
		$randProIds = $this->memcache->get(self::MEM_KEY_PRO_BOUGHT_TOGETHER,$memParams);
		if (!$randProIds) {

			$allChildCatIdresultArray = $this->db_ebmaster_read->select('id')->from('category')->like('path', $baseCatId)->where('status', 1)->get()->result_array();
			$allChildCatIds = extractColumn($allChildCatIdresultArray, 'id');
			$searchCatIdsDiff = array_diff($allChildCatIds, array($finalCatId));
			$searchCatIds = empty($searchCatIdsDiff)?array($finalCatId):$searchCatIdsDiff;
			$prosCount = $this->db_ebmaster_read->select('count(*) prosCount')->from('product')->where_in('category_id', $searchCatIds)->where('status', 1)->get()->row()->prosCount;
			if ($prosCount>10) {
				$limitStart = rand(0, $prosCount - 10);
				$randProIdsRes = $this->db_ebmaster_read->select('id')->from('product')->where_in('category_id', $searchCatIds)->where('status', 1)->limit(10, $limitStart)->get()->result_array();
				$randProIds = extractColumn($randProIdsRes, 'id');
			} else {
				$prosCountRes = $this->db_ebmaster_read->select('count(*) prosCount')->from('product')->where('status', 1)->get();
				$prosCount = $prosCountRes->row()->prosCount;
				$limitStart = rand(0, $prosCount - 10);
				$randProIdsRes = $this->db_ebmaster_read->select('id')->from('product')->where('status', 1)->limit(10, $limitStart)->get()->result_array();
				$randProIds = extractColumn($randProIdsRes, 'id');
			}
			$this->memcache->set(self::MEM_KEY_PRO_BOUGHT_TOGETHER, $randProIds, $memParams);
		}

		shuffle($randProIds);
		$shuffleSelectedPids = array_slice($randProIds,0,3);
		$boughtTogetherProInfoList = $this->getProInfoById($shuffleSelectedPids, $languageId);

		return $boughtTogetherProInfoList;
	}

	public function getRelatedRecommended($cid,$pid,$languageId){
		$memParams = array($cid,$pid);
		$top10Pids = $this->memcache->get(self::MEM_KEY_PRO_RELA_RECO,$memParams);
		if (!$top10Pids) {

			/*获取分类下的所有product id(排除参数中传入的pid),上限5000*/
			$top10PidsRes = $this->db_ebmaster_read->select('id')->from('product')->where('category_id',$cid)->where('status',1)->where('id !=',$pid)->order_by('sale_count','desc')->limit(10)->get()->result_array();
			if($top10PidsRes){
				$top10Pids = extractColumn($top10PidsRes,'id');
			}else{
				$allPidsOfCatRes = $this->db_ebmaster_read->select('id')->from('product')->where('status',1)->where('id !=',$pid)->order_by('sale_count','desc')->limit(10)->get()->result_array();
				$top10Pids = extractColumn($allPidsOfCatRes,'id');
			}
			$this->memcache->set(self::MEM_KEY_PRO_RELA_RECO, $top10Pids, $memParams);
		}

		shuffle($top10Pids);
		$shuffleSelectedPids = array_slice($top10Pids,0,3);
		$relatedRecommendedList = $this->getProInfoById($shuffleSelectedPids, $languageId);

		return $relatedRecommendedList;
	}

	public function getRecentView(&$languageId,&$collectProIds,&$curPid=''){

		$userVisitedProsCookie = $this->input->cookie('userVisitedPros');
		$userVisitedProsCookieUnserialize = $userVisitedProsCookie===false?array():unserialize($userVisitedProsCookie);
		if($curPid){
			$userVisitedProsCookieUnserialize = array_diff($userVisitedProsCookieUnserialize,array($curPid));
		}
		$userVisitedProsCookieUnserializeSlice = array_slice($userVisitedProsCookieUnserialize,0,6);
		$proInfoList = $this->getProInfoById($userVisitedProsCookieUnserializeSlice, $languageId);
		$return = array();
		foreach ($userVisitedProsCookieUnserializeSlice as $v) {
			$proInfo = isset($proInfoList[$v])?$proInfoList[$v]:current($this->getProInfoById($v, 1));
			if ($proInfo) {
				$proInfo['isCollected'] = in_array($v, $collectProIds) ? TRUE : FALSE;
				$return[] = $proInfo;
			}
		}
		return $return;
	}

	/**
	 * 获取sku的可用库存
	 * @param str||array $skus
	 * @return array
	 * @author Terry
	 */
	public function getActiveStockBySku($skus,$cache=TRUE){

		if ($cache) {
			$return = $this->memcache->ebMcFetchData($skus,self::MEM_KEY_SKU_STOCK,array($this,'getActiveStockBySku'));
		} else {
			$return = array();
			$queryStock = $this->db_ebmaster_read->select('sku,type,stock')->from('product_sku')->where_in('sku', $skus)->get();
			if ($queryStock) {
				$resArr = $queryStock->result_array();
				foreach($resArr as $v){
					$return[$v['sku']] = ( $v['type']==2?65535:$v['stock'] );
				}
			}
		}

		return $return;
	}
	/**
	 * 获取product的narrow属性信息。
	 * @param int||array $pid
	 * @param array $languageId
	 * @param boolean $cache 是否取缓存数据
	 * @return array
	 * @author Terry
	 */
	public function getProAttrInfo($pid,$languageId,$cache=TRUE){
		$return = array();
		if($cache){
			$return = $this->memcache->get(self::MEM_KEY_PRO_ATTR_INFO,array($pid,$languageId));
			if($return===false){
				$return = $this->getProAttrInfo($pid, $languageId,false);
			}
		} else {
			$attrProductQuery = $this->db_ebmaster_read->select('block_id,attribute_id,attribute_value_id,attribute_value')->from('attribute_product')->where('product_id', $pid)->where('status', 1)->where('attribute_id >',0)->get();
			if ($attrProductQuery) {
				$attrProductArr = $attrProductQuery->result_array();
				$blockIds = array(); //属性模块数组
				$attrIds = array(); //属性名数组
				$attrValArr = array(); //属性值数组
				foreach ($attrProductArr as $attrProduct) {
					$blockIds[] = $attrProduct['block_id'];
					$attrIds[$attrProduct['block_id']][] = $attrProduct['attribute_id'];
					if ($attrProduct['attribute_value'] && !isset($attrValArr[$attrProduct['attribute_id']])) {
						$attrValArr[$attrProduct['attribute_id']] = $attrProduct['attribute_value'];
					} elseif(!$attrProduct['attribute_value']) {
						if(isset($attrValArr[$attrProduct['attribute_id']]) && !is_array($attrValArr[$attrProduct['attribute_id']])){
							$attrValArr[$attrProduct['attribute_id']] = array();
						}
						$attrValArr[$attrProduct['attribute_id']][] = $attrProduct['attribute_value_id'];
					}
				}
				$blockIdsUnique = array_unique($blockIds);

				if (empty($blockIdsUnique)) {//-----------------如果没有block id，认为是没有数据或者数据异常，返回结果空。
					return $return;
				}

				/* 根据block ids获取block的信息，并按照block的权重排序 */
				$blockInfoQuery = $this->db_ebmaster_read->select('id,name')->from('attribute_block')->where_in('id', $blockIdsUnique)->where('status', 1)->order_by('sort', 'desc')->get();
				if (!$blockInfoQuery) {//-----------------如果数据库中查不到有效的block id，认为是没有数据或者数据异常，返回结果空。
					return $return;
				}
				$blockInfoArr = $blockInfoQuery->result_array();

				/* 根据block ids和languageid获取其多语言信息 */
				$blockLanInfoQuery = $this->db_ebmaster_read->select('block_id,title')->from('attribute_block_lang')->where_in('block_id', $blockIdsUnique)->where('language_id', $languageId)->where('status', 1)->get();
				$blockLanInfoArr = $blockLanInfoQuery ? $blockLanInfoQuery->result_array() : array();
				$blockLanInfoArrReindex = reindexArray($blockLanInfoArr, 'block_id');

				/* 把数据库中得到的排过序的block结果集遍历 */
				foreach ($blockInfoArr as $blockInfo) {
					$blockId = $blockInfo['id'];
					$blockTitle = isset($blockLanInfoArrReindex[$blockId]) ? $blockLanInfoArrReindex[$blockId]['title'] : $blockInfo['name'];

					/* 获取该商品某个block下包含的属性id集，并根据属性id集获取其信息，且按权重排序。 */
					$attrIdsOfBlock = $attrIds[$blockId];
					$attrInfoQuery = $this->db_ebmaster_read->select('id,name,unit')->from('attribute')->where_in('id', $attrIdsOfBlock)->where('status', 1)->order_by('sort', 'desc')->get();
					if (!$attrInfoQuery) {
						continue;
					}
					$attrInfoArr = $attrInfoQuery->result_array();

					/* 根据attr ids和languageid获取其多语言信息 */
					$attrLanInfoQuery = $this->db_ebmaster_read->select('attribute_id,title')->from('attribute_lang')->where_in('attribute_id', $attrIdsOfBlock)->where('language_id', $languageId)->where('status', 1)->get();
					$attrLanInfoArr = $attrLanInfoQuery ? $attrLanInfoQuery->result_array() : array();
					$attrLanInfoArrReindex = reindexArray($attrLanInfoArr, 'attribute_id');

					foreach ($attrInfoArr as $attrInfo) {
						$attrId = $attrInfo['id'];
						$attrTitle = isset($attrLanInfoArrReindex[$attrId]) ? $attrLanInfoArrReindex[$attrId]['title'] : $attrInfo['name'];
						$attrVal = false;
						if( isset( $attrValArr[$attrId] ) ){
							if ( !is_array($attrValArr[$attrId])) {
								$attrVal = $attrValArr[$attrId];
							} else {
								$attrValIds = $attrValArr[$attrId];
								$attrValInfoQuery = $this->db_ebmaster_read->select('id,name')->from('attribute_value')->where_in('id', $attrValIds)->where('status', 1)->get();
								if (!$attrValInfoQuery) {
									continue;
								}
								$attrValInfoArr = $attrValInfoQuery->result_array();

								/* 根据attrval ids和languageid获取其多语言信息 */
								$attrValLanInfoQuery = $this->db_ebmaster_read->select('attribute_value_id,title')->from('attribute_value_lang')->where_in('attribute_value_id', $attrValIds)->where('language_id', $languageId)->where('status', 1)->get();
								$attrValLanInfoArr = $attrValLanInfoQuery ? $attrValLanInfoQuery->result_array() : array();
								$attrValLanInfoArrReindex = reindexArray($attrValLanInfoArr, 'attribute_value_id');

								$attrValTitleArr = array();
								foreach ($attrValInfoArr as $attrValInfo) {
									$attrValId = $attrValInfo['id'];
									$attrValTitle = isset($attrValLanInfoArrReindex[$attrValId]) ? $attrValLanInfoArrReindex[$attrValId]['title'] : $attrValInfo['name'];
									if ($attrValTitle) {
										$attrValTitleArr[] = $attrValTitle;
									}
								}
								$attrVal = implode(',', $attrValTitleArr);
							}
						}

						if ($attrVal) {
							$return[eb_htmlspecialchars($blockTitle)][eb_htmlspecialchars($attrTitle)] = array('unit' => eb_htmlspecialchars($attrInfo['unit']), 'val' => eb_htmlspecialchars($attrVal));
						}
					}
				}
			}
			$this->memcache->set(self::MEM_KEY_PRO_ATTR_INFO,$return,array($pid,$languageId));
		}

		return $return;
	}

	/**
	 * 获取pid下sku的属性值（多语言） @todo  需要优化 缓存查询数据库
	 * @param int||array $pids
	 * @param array $params （多语言参数）
	 * @param boolean $cache （是否缓存） TRUE
	 * @return array
	 * @author Terry Lu
	 */
	public function getProComplexattr($pids,$params,$cache = FALSE){
		if ($cache) {
			$return = $this->memcache->ebMcFetchData($pids,self::MEM_KEY_PRO_SKU_ATTR_INFO,array($this,'getProComplexattr'),$params);
		}else{
			$return = array();
			$proSkuInfos = $this->getProductSkuInfo($pids);
			foreach ($proSkuInfos as $pid => $proSkuInfo) {
				foreach ($proSkuInfo as $sku => $skuInfo) {
					$query = self::find()->select('complexattr_value_id')->from('complexattr_sku');
					$query->where( ['sku'=>$skuInfo['sku'], 'status'=> 1] );
					$complexAttrValIdsRes = $query->asArray()->all();
			
			//		$complexAttrValIdsRes = $this->db_ebmaster_read->select('complexattr_value_id')->from('complexattr_sku')->where('sku', $skuInfo['sku'])->where('status', 1)->get()->result_array();
					$complexAttrValIds = array();
					foreach ($complexAttrValIdsRes as $resRow) {
						$complexAttrValIds[] = $resRow['complexattr_value_id'];
					}
					if(empty($complexAttrValIds)){
						continue;
					}
					
					$query = self::find()->select('id,complexattr_id,title')->from('complexattr_value');
					$where = 'id in(' . implode(',', $complexAttrValIds ) . ')';
					$query->where( $where );
					$complexAttrValRes = $query->asArray()->all();
		
					//$complexAttrValRes = $this->db_ebmaster_read->select('id,complexattr_id,title')->from('complexattr_value')->where_in('id', $complexAttrValIds)->get()->result_array();
					foreach ($complexAttrValRes as $complexAttrRow) {
						$query = self::find()->select('title')->from('complexattr_value_lang');
						$query->where( ['complexattr_value_id' => $complexAttrRow['id'] , 'language_id'=> $params[0]] );
						$attrValTitleLanRow = $query->asArray()->one();
						
						//$attrValTitleLanRow = $this->db_ebmaster_read->select('title')->from('complexattr_value_lang')->where('complexattr_value_id', $complexAttrRow['id'])->where('language_id', $params[0])->get()->row();
						if(!empty($attrValTitleLanRow)){
							//$complexAttrRow['title'] = $attrValTitleLanRow->title;
							$complexAttrRow['title'] = $attrValTitleLanRow['title'];
						}
						$return[$pid][$sku][$complexAttrRow['complexattr_id']]['attrValId'] = $complexAttrRow['id'];
						$return[$pid][$sku][$complexAttrRow['complexattr_id']]['attrValTitle'] = OtherHelper::eb_htmlspecialchars($complexAttrRow['title']);
					}
				}
			}
		}
		return $return;
	}

	/**
	 * 根据产品的属性id，获取其相应的多语言title
	 * @param int||array $attrIds
	 * @param array $params
	 * @param boolean $cache
	 * @return array
	 */
	public function getComplexAttrTitle($attrIds,$params,$cache=TRUE){

		if ($cache) {
			$return = $this->memcache->ebMcFetchData($attrIds,self::MEM_KEY_PRO_ATTR_TITLE,array($this,'getComplexAttrTitle'),$params);
		} else {
			$return = array();
			//$query = $this->db_ebmaster_read->select('complexattr_id,title')->from('complexattr_lang')->where_in('complexattr_id', $attrIds)->where('language_id', $params[0])->get();
			//$resultArray = $query?$query->result_array():array();
			$query = self::find();
			$query->select(['complexattr_id' , 'title'])->from('complexattr_lang');
			if ( !empty ($attrIds) ){ //没有whereIN 类似的函数
				$where = 'complexattr_id in (' . implode(',' ,$attrIds) . ')' ;
				$query->where(  $where );
			}
			$query->andWhere( ['language_id' => $params[0] ] );
			$resultArray = $query->asArray()->all(); 
			$resultArray = empty( $resultArray )? []: $resultArray;
			
			$arrIdToTitle = array();
			foreach($resultArray as $res){
				$arrIdToTitle[$res['complexattr_id']] = $res['title'];
			}
			foreach($attrIds as $attrId){
				if(!isset($arrIdToTitle[$attrId])){
					//$defaultTitleRes = $this->db_ebmaster_read->select('title')->from('complexattr')->where('id', $attrId)->get()->row();
					//$arrIdToTitle[$attrId] = empty($defaultTitleRes)?'':$defaultTitleRes->title;
					$query = self::find()->select( 'title' )->from('complexattr');
					$query->where( ['id' => $attrId ] );
					$defaultTitleRes = $query->asArray()->one(); 
					$arrIdToTitle[$attrId] = empty( $defaultTitleRes )? '' : $defaultTitleRes['title'];
				}
				$return[$attrId] = $arrIdToTitle[$attrId];
			}
		}
		return $return;
	}

	/**
	 * 获取分类的商品列表
	 * @param  integer  $categoryId 指定的获取的分类的id
	 * @param  integer  $languageId  指定获取的语言的id
	 * @param  array $param 指定商品的排序的方式
	 * @param  integer $page 指定获取的分页数
	 * @param  integer $pageSize 指定每页显示的商品的数量
	 * @author  qcn qianchangnian@hofan.cn
	 * @return [type] 返回取出的商品的列表
	 */
	public function getCategoryProductList( $categoryId , $languageId ,$attributeIds=array(),$param = array() , $page = 1, $pageSize = PAGE_COUNT_CATEGORY , $priceStepList = array() ) {
		//方法的参数处理
		$categoryId = intval($categoryId);
		$languageId = intval($languageId);
		$page = intval($page);

		//商品取出偏移量
		$start = ($page - 1) * $pageSize;

		//通过分类标示，语言标示，排序参数取出指定排序后的商品的id
		$productArray = $this->getCategoryProductIds($categoryId,$languageId, $attributeIds,$param , $start, $pageSize , $priceStepList );


		$productIds = array();
		if(isset($productArray['goodsList']) && count($productArray['goodsList']) > 0) {
			$productIds = $productArray['goodsList'];
		}

		//获取商品的总数
		$count = 0;
		if(isset($productArray['goodsCount']) && count($productArray['goodsCount']) > 0) {
			$count = $productArray['goodsCount'];
		}

		if( isset( $productArray['priceStepList'] ) && is_array(  $productArray['priceStepList'] ) ) {
			$priceStepList = $productArray['priceStepList'] ;
		}

		//组合商品信息数组
		$list = $this->getProInfoById($productIds,$languageId);

		$list = reindexArray($list,'id');

		//恢复数组的键值
		$res = array();
		foreach($productIds as $proId){
			if(isset($list[$proId])){
				$res[] = $list[$proId];
			}
		}
		//返回商品数组和数量 以及价格跨度
		if( !empty( $priceStepList ) && is_array( $priceStepList ) ){
			$result = array( 'pidslist' => $res , 'totalCount' => $count , 'priceStepList' => $priceStepList );
		}else{
			$result = array( $res,$count ) ;
		}

		return $result;
	}

	/**
	 * 取出指定的分类的商品的id
	 * @param  integer $categoryId 指定的获取的分类的id
	 * @param  integer $languageId 指定获取的语言的id
	 * @param  array  $param 指定商品的排序的方式
	 * @param  integer $start 商品取出偏移量
	 * @param  integer $pageSize 每页产品的展示数量
	 * @param  array $attrIdsArray 分类页面属性id数组
	 * @author  qcn qianchangnian@hofan.cn
	 * @return array 返回商品的ids数组
	 */
	public function getCategoryProductIds($categoryId,$languageId, $attributeIds=array(),$param = array(),  $start = 0, $pageSize = PAGE_COUNT_CATEGORY ,  $priceStepList = array(), $isScriptTask = FALSE ) {
		//参数处理
		$param_cache_key = array();
		if(isset($param['sort'])) {
			$param_cache_key[] = 's'.$param['sort'];
		}
		//价格选择过滤
		if( !empty($priceStepList['price_step_list']) && is_array( $priceStepList['price_step_list'] ) ){
			foreach ( $priceStepList['price_step_list'] as $keyId => $info ){
				$param_cache_key[] =  $keyId.'k' . (int)$info['selected'] ;
			}
		}
		//缓存处理
		$param_cache_key = implode('_',$param_cache_key);
		$attributeIdsCacheKey = 'all';
		if( is_array( $attributeIds ) && !empty( $attributeIds ) && count( $attributeIds ) > 0 ){
			$attributeIdsCacheKey = '';
			$attributeIdsArrTmp = array();
			foreach ( $attributeIds as $k => $v ){
					sort($v);
					$attributeIdsArrTmp[ $k ] = $k.'v'.implode('a',$v);
			}
			sort( $attributeIdsArrTmp );
			$attributeIdsCacheKey = implode( 'X', $attributeIdsArrTmp );
		}

		//判断是否执行定时任务
		if( empty( $isScriptTask ) ){
			$appModelObj = Appmodel::getInstanceObj();
			$currency = $appModelObj->currentCurrency();
		}else{
			$currency = DEFAULT_CURRENCY;
		}
		$cache_key = "idx_get_category_pids_%s_%s_%s_%s_%s_%s_%s";
		$cache_params = array($categoryId,$languageId,$currency ,$param_cache_key,$attributeIdsCacheKey,$start,$pageSize);
		$list = $this->memcache->get($cache_key,$cache_params);
		if( $list === false || !is_array( $list ) ){
			$getAllSpecialProByCatidOnOff = TRUE ;
			if( isset( $priceStepList['has_price_step_list'] ) && ( count( $priceStepList['has_price_step_list'] ) > 0 ) ){
				$getAllSpecialProByCatidOnOff = FALSE ;
			}
			/*
			if( ( $getAllSpecialProByCatidOnOff === TRUE ) && !( empty($param['price_max'] ) && empty($param['price_min'] ) ) ){
				$getAllSpecialProByCatidOnOff = FALSE ;
			}
			*/

			$attributeIdsCount = count( $attributeIds );
			if( $attributeIdsCount <= 0 && !isset($param['sort']) && $getAllSpecialProByCatidOnOff ) {
				//获取特殊的商品PID
				$getAllSpecialProByCatid = $this->getAllSpecialProByCatid( $categoryId );
			}

			//取出指定分类的子分类
			$categoryIds = $this->getProductCategorySub($categoryId);

			//取出销售分类的商品id
			$proIdsFromCat = $this->getSaleCategoryProduct($categoryIds);

			//取出narrow search匹配的商品
			$narrowSearchProductIds = array();
			if( $attributeIdsCount > 0) {
				$narrowSearchProductIds = $this->getProductIdsByNarrowSearch( $categoryId , $languageId , $attributeIds );
				//属性没有上的时候直接返回
				if(empty($narrowSearchProductIds)) {
					return array('goodsCount' => 0, 'goodsList' => array());
				}
			}

			//取出商品的id
			$this->db_ebmaster_read->select('id ');
			$this->db_ebmaster_read->from('product');

			if(!empty($narrowSearchProductIds)) {
				$this->db_ebmaster_read->where_in( 'id', $narrowSearchProductIds );
			}
			//商品id取出条件的处理
			if( !empty($proIdsFromCat) ){
				$where_tmp = '( `category_id` IN ( ' . implode(',' , $categoryIds ) . ') OR `id`  IN ( ' . implode( ',' , $proIdsFromCat ) . ' ) )';
				$this->db_ebmaster_read->where( $where_tmp );
			} else {
				$this->db_ebmaster_read->where_in('category_id',$categoryIds);
			}
			//价格选择过滤 分类价格区间
			if( !empty($priceStepList['has_price_step_list']) && is_array( $priceStepList['has_price_step_list'] ) && ( count( $priceStepList['has_price_step_list'] ) > 0 ) ){
				$marketPriceSql = '';
				foreach ( $priceStepList['has_price_step_list'] as $v ){
					$marketPriceSql .= '( `market_price` >="' .$v['start'] . '" AND `market_price` <"' .$v['end'] .'" ) OR' ;
				}
				$marketPriceSql  = '(' . trim( $marketPriceSql , 'OR' ) . ')';
				$this->db_ebmaster_read->where( $marketPriceSql );
			}else{
				//价格的排序是按照商品默认的价格排序
				/*
				$paramPriceMax = isset($param['price_max']) && !empty($param['price_max']) ? $param['price_max']:0;
				$paramPriceMin = isset($param['price_min']) && !empty($param['price_min']) ? $param['price_min']:0;
				if($paramPriceMax && $paramPriceMin &&$paramPriceMax == $paramPriceMin) {
					$this->db_ebmaster_read->where('market_price',$paramPriceMax);
				} else {
					if($paramPriceMax) {
						$this->db_ebmaster_read->where('market_price <=', $paramPriceMax);
					}
					if($paramPriceMin) {
						$this->db_ebmaster_read->where('market_price >=',$paramPriceMin);
					}
				}
				*/
			}

			$this->db_ebmaster_read->where('status',1); //商品的状态 1是上架 0是下架
			$this->db_ebmaster_read->where('price !=','0.00');
			$this->db_ebmaster_read->where('market_price !=','0.00');
			//处理特殊商品
			$startSpecial = FALSE ; //$pageSize
			$pageSizeSpecial = FALSE ;//$start

			if( $attributeIdsCount <= 0 && !isset($param['sort'] ) && $getAllSpecialProByCatidOnOff  ){
				if( $getAllSpecialProByCatid['count'] > 0 ){
					$this->db_ebmaster_read->where_not_in('id',$getAllSpecialProByCatid['pids']);
					//判断特殊商品总数小于现在的偏移起始数
					if( $getAllSpecialProByCatid['count'] < $start ){
						$startSpecial = (int)($start - $getAllSpecialProByCatid['count'] );
						$pageSizeSpecial = $pageSize ;
					//特殊商品不足现在的数量
					}else if( ( $start <= $getAllSpecialProByCatid['count'] ) && ( $getAllSpecialProByCatid['count'] < ( $start+$pageSize ) ) ) {
						$startSpecial = 0;
						$pageSizeSpecial= (int)( $start+$pageSize - $getAllSpecialProByCatid['count'] ) ;
					} else if( $getAllSpecialProByCatid['count'] >= ( $start+$pageSize ) ){
						$startSpecial = 0;
						$pageSizeSpecial = 0 ;
					}
				}
			}

			if(isset($param['sort'])){
				if($param['sort']=='add') {
					$this->db_ebmaster_read->order_by('add_time','desc');
				} elseif($param['sort']=='price_asc') {
					$this->db_ebmaster_read->order_by('market_price','asc');
				} elseif($param['sort']=='price_desc') {
					$this->db_ebmaster_read->order_by('market_price','desc');
				}
			}
			//默认排序是按照销量排序
			$this->db_ebmaster_read->order_by('sale_count','desc');

			//分页取出
			if( $startSpecial !== FALSE ){
				$this->db_ebmaster_read->limit( $pageSizeSpecial , $startSpecial );
			} else if($start !== false && $pageSize !== false) {
				$this->db_ebmaster_read->limit($pageSize, $start);//第一个参数是取出多少条数据，第二个参数是从什么地方开始取出
			} elseif ($start === false && $pageSize !== false) {
				$this->db_ebmaster_read->limit($pageSize, 0);
			}

			$query = $this->db_ebmaster_read->get();
			$listArray = $query->result_array();
			$list = extractColumn($listArray,'id');
			//取出指定分类下商品总数
			$resultCountInfo = $this->getProductCountFromCategory($narrowSearchProductIds, $proIdsFromCat, $categoryIds, $param , $priceStepList );

			//特殊ID 处理
			if( $attributeIdsCount <= 0 && !isset($param['sort']) && $getAllSpecialProByCatidOnOff ){
				if( $getAllSpecialProByCatid['count'] > 0 ){
					//特殊商品不足现在的数量
					if( ( $start <= $getAllSpecialProByCatid['count'] ) && ( $getAllSpecialProByCatid['count'] < ( $start+$pageSize ) ) ) {
						$listTmp = array();
						$listTmp = array_slice( $getAllSpecialProByCatid['pids'] ,$start ) ;
						foreach ( $list as $v ){
							$listTmp[] = (int)$v;
						}
						$list = $listTmp ;
					//特殊商品数量大于现在的请求数量
					} else if( $getAllSpecialProByCatid['count'] >= ( $start+$pageSize ) ) {
						$listTmp = array();
						$listTmp = array_slice( $getAllSpecialProByCatid['pids'] , $start , $pageSize ) ;
						$list = $listTmp ;
					}
				}
			}

			//加入缓存
			$list = array( 'goodsCount' => $resultCountInfo['totalCount'], 'goodsList' => $list , 'priceStepList' => $resultCountInfo['priceStepList'] );
			$this->memcache->set($cache_key,$list,$cache_params);
		}

		return $list;
	}

	/**
	 * 获取用户收藏的商品
	 * @param  int $userId 用户的id 默认是false
	 * @author qcn qianchangnian@hofan.cn
	 * @return array 返回用户收藏的商品id
	 */
	public function getUserCollectedGoodsId( $userId = false ){
		if($userId === false || $userId == 0) {
			return array();
		}

		$this->db_ebmaster_read->select('product_id');
		$this->db_ebmaster_read->from('collect_products');
		$this->db_ebmaster_read->where('user_id',$userId);
		$query = $this->db_ebmaster_read->get();
		$listQuery = $query->result_array();
		$list = extractColumn($listQuery,'product_id');

		return $list;
	}

	/**
	 * 取出一个分类下商品默认销售价格的最大值和最小值
	 * @param  integer $categoryId 指定的获取的分类的id
	 * @param  integer $languageId 指定获取的语言的id
	 * @return array 返回一个分类下商品默认销售价格的最大值和最小值
	 */
	public function getCategoryProductPriceBoundary($categoryId,$languageId) {
		//从缓存种取出数据
		$cachekey = "idx_get_category_goods_price_boundary_%s_%s";
		$cacheParams = array($categoryId,$languageId);
		$result = $this->memcache->get($cachekey, $cacheParams);
		if ( $result === false) {
			//取出指定分类的子分类
			$categoryIds = $this->getProductCategorySub($categoryId);

			//取出销售分类的商品id
			$proIdsFromCat = $this->getSaleCategoryProduct($categoryIds);

			$this->db_ebmaster_read->select_min( 'market_price', 'price_min' );
			$this->db_ebmaster_read->select_max( 'market_price', 'price_max' );
			$this->db_ebmaster_read->from( 'product' );
			$this->db_ebmaster_read->where( 'status', 1);
			//商品id取出条件的处理
			if( !empty($proIdsFromCat) ){
				$where_tmp = '( `category_id` IN ( ' . implode(',' , $categoryIds ) . ') OR `id`  IN ( ' . implode( ',' , $proIdsFromCat ) . ' ) )';
				$this->db_ebmaster_read->where( $where_tmp );
			} else {
				$this->db_ebmaster_read->where_in('category_id',$categoryIds);
			}
			$this->db_ebmaster_read->limit( 1 );
			$query = $this->db_ebmaster_read->get();
			$resultQuery = $query->row_array();

			//如果数据为空的时候给定默认的价格
			$result['price_min'] = isset($resultQuery['price_min']) && !empty($resultQuery['price_min']) ? $resultQuery['price_min']:0;
			$result['price_max'] = isset($resultQuery['price_min']) && !empty($resultQuery['price_max']) ? $resultQuery['price_max']:0;
			//将取出的数据写入缓存
			$this->memcache->set( $cachekey, $result, $cacheParams );
		}
		return $result;
	}

	/**
	 * 获取首页special推荐产品列表
	 * @param int languageId语言ID
	 * @return array 返回推荐产品列表
	 * @author lucas
	 */
	public function getRecommendGoodsSpecialList( $languageId ){
		$languageId = intval( $languageId );
		//缓存处理
		$cacheKey = "idx_get_recommend_goods_special_list_%s";
		$cacheParams = array( $languageId );
		$productList = $this->memcache->get( $cacheKey, $cacheParams );

		//数据取出
		if($productList === false) {
//			$this->db_read->from('special_goods_recommend_info');
//			$query = $this->db_read->get();
//			$list = $query->result_array();
			
			$sql = 'SELECT * FROM  special_goods_recommend_info  ';
			$command =  $this->db_read->createCommand( $sql );
			$list = $command->queryAll();

			$categoryObj = Category::getInstanceObj();
			$productList = array();
			foreach($list as $record){
				$pids = array();
				for($i=1;$i<=10;$i++){
					if(!empty($record['sku_'.$i])) $pids[] = $record['sku_'.$i];
				}
				$pids = array_unique($pids);
				//获得商品信息列表
				$proInfoList = $this->getProInfoById( $pids, $languageId );
				foreach( $proInfoList as $key => $value ){
					if( $value['status'] != 1 ){
						unset( $proInfoList[ $key ] );
					}
				}

				$lackPidCount = 10 - count($proInfoList);
				if($lackPidCount > 0){
					$sort = '1';
					if($record['complement'] == 1){ //促销
						$sort = '1';
					}elseif($record['complement'] == 2){ //Popular
						$sort = '1';
					}elseif($record['complement'] == 3){ //new
						$sort = '2';
					}elseif($record['complement'] == 4){ //Price
						$sort = '4';
					}

					$fillup_goods = $categoryObj->getPidsByCatId( $record['category_id'], $sort );
					$fillup_List = $this->getProInfoById( $fillup_goods, $languageId );

					$i = 0;
					foreach( $fillup_List as $info ){
						if( empty( $proInfoList[$info['id']] ) && $i < $lackPidCount ){
							$proInfoList[$info['id']] = $info;
							$i++;
						}
					}
				}

				$productList[$record['model_id']] = $proInfoList;
			}

			$this->memcache->set( $cacheKey, $productList, $cacheParams );
		}

		return $productList;
	}

	/**
	 * 获取首页new推荐产品列表
	 * @param int languageId语言ID
	 * @return array 返回推荐产品列表
	 * @author lucas
	 */
	public function getRecommendGoodsNewList( $languageId ){
		$languageId = intval( $languageId );
		$cacheKey = "idx_get_recommend_goods_new_list_%s";
		$cacheParams = array($languageId);
		$modelSkuList = $this->memcache->get( $cacheKey, $cacheParams );
		if( $modelSkuList === false ){
//			$this->db_read->from('new_goods_recommend_info');
//			$query = $this->db_read->get();
//			$list = $query->result_array();
			
			$sql = 'SELECT * FROM new_goods_recommend_info';
			$command =  $this->db_read->createCommand( $sql );
			$list = $command->queryAll();			
			$categoryObj =  Category::getInstanceObj();

			$modelSkuList = array();
			foreach($list as $record){
				$pids = array();
				for($i=1;$i<=10;$i++){
					if($record['sku_'.$i] != '') $pids[] = $record['sku_'.$i];
				}
				$pids = array_unique($pids);
				//获得商品信息列表
				$proInfoList = $this->getProInfoById( $pids, $languageId );
				foreach( $proInfoList as $key => $value ){
					if( $value['status'] != 1 )
					{
						unset( $proInfoList[ $key ] );
					}
				}
				$lackPidCount = 10 - count($proInfoList);
				if($lackPidCount > 0){
					$pids = ArrayHelper::extractColumn( $proInfoList, 'id' );
					$sort = '1';
					if($record['complement'] == 1){ //促销
						$sort = '1';
					}elseif($record['complement'] == 2){ //Popular
						$sort = '1';
					}elseif($record['complement'] == 3){ //new
						$sort = '2';
					}elseif($record['complement'] == 4){ //Price
						$sort = '4';
					}

					$fillup_goods = $categoryObj->getPidsByCatId( $record['category_id'], $sort );
					$fillup_List = $this->getProInfoById( $fillup_goods, $languageId );

					$i = 0;
					foreach( $fillup_List as $info ){
						if( empty( $proInfoList[$info['id']] ) && $i < $lackPidCount ){
							$proInfoList[$info['id']] = $info;
							$i++;
						}
					}
				}
				$modelSkuList[$record['model_id']] = $proInfoList;
			}

			$this->memcache->set( $cacheKey, $modelSkuList, $cacheParams );
		}

		return $modelSkuList;
	}

	/**
	 * 更新商品对应的促销类型 默认1小时执行一次
	 * @param inc $time
	 * @return 默认1小时
	 * @author lucas
	 */
	public function updateProductPromoteType(){
		$result = FALSE ;
		set_time_limit ( 0 );
		//更新现在分类下面为6的PID  更新为0
		$this->db_ebmaster_write->where('sale_type > ', 0 );
		$result_up = $this->db_ebmaster_write->update('product', array('sale_type' => 0) );
		//查询出所有秒杀的PID
		if( $result_up ){
			//获取秒杀商品的PID
			$requestTime = HelpOther::requestTime() ;
			$formatRequestTime = date('Y-m-d H:i:s' , $requestTime );
			$formatStartTime = date('Y-m-d H:i:s' , ( $requestTime + Promote::DISCOUNT_SEC_KILL_NOTICE_TIME ) );
			$resultPidSecKill = $this->db_ebmaster_read->select('pat.product_id')
										->from('promote_activity_target pat')
										->join('promote_activity as pa', 'pat.promote_activity_id=pa.id','left')
										->where('pat.target_status',Promote::STATUS_ENABLED)
										->where('pa.type',Promote::PROMOTE_TYPE_SEC_KILL)
										->where('pa.status',Promote::STATUS_ENABLED)
										->where("(pa.end_time > '$formatRequestTime' )")
										->where("(pa.start_time < '$formatStartTime' )")
										->get()->result_array();
			$pids = array();
			//过滤现在是否有秒杀PID
			if( !empty($resultPidSecKill) && is_array( $resultPidSecKill ) ){
				//获取PIDS
				foreach ( $resultPidSecKill as $v ){
					$pidTmp  = (int)$v ['product_id'];
					$pids[ $pidTmp ] = $pidTmp ;
				}
				//判断秒杀PID商品是否存在 存在更新商品秒
				if( count( $pids ) > 0  ){
					$this->db_ebmaster_write->where_in('id', $pids );
					$result_update = $this->db_ebmaster_write->update('product', array('sale_type' => 6) );
					if( $result_update ){
						$result = TRUE ;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * 获取商品的多语言信息
	 * @param  array $productIds 商品的ID数组
	 * @param  integer $languageId 语言id
	 * @author qcn qianchangnian@hofan.cn
	 * @return array 商品的多语言信息数组
	 */
	protected function getProductMultiLanguages($productIds, $languageId) {
		$productIds = is_array($productIds) ? $productIds : array($productIds);
		$descList = array();
		if(!empty($productIds)) {
			$query = self::find()->from('product_description_' . $languageId);
			$where = 'product_id in(' . implode(',', $productIds) . ')';
			$query->where( $where );
			$descListQuery = $query->asArray()->all();

			$descList = ArrayHelper::reindexArray($descListQuery, 'product_id');
		}
		return $descList;
	}

	/**
	 * 获取商品的海外仓
	 * @param  array $productIds 商品的id
	 * @param  integer $languageId 语言id
	 * @author qcn qianchangnian@hofan.cn
	 * @return array 商品的海外仓
	 */
	protected function getProductWareHouse($productIds, $languageId) {
		$productIds = is_array($productIds) ? $productIds : array($productIds);
		$productExtList = array();
		if(!empty($productIds)) { 
			$warehouse = ArrayHelper::id2name($languageId, AppConfig::$language2warehouse, array('GZ', 'HK'));
			$query = self::find()->select(['product_id', 'sku', 'warehouse'])->from('product_sku');
			$where = 'product_id in(' . implode(',', $productIds) . ')';
			if(!empty($warehouse)){
				$where .= ' and warehouse in(\'' . implode('\',\'' , $warehouse ) . '\')';
			}
			$query->where( $where );
			$productExtListQuery = $query->asArray()->all();
			
			$productExtList =  ArrayHelper::reindexArray($productExtListQuery, 'product_id');
		}
		return $productExtList;
	}

	/**
	 * 获取上商品的评论数
	 * @param  array $productIds 商品的ID数组
	 * @author qcn qianchangnian@hofan.cn
	 * @return array 商品的商品的评论数
	 */
	protected function getProductReviewCount($productIds) {
		$productIds = is_array($productIds) ? $productIds : array($productIds);
		$reviewList = array();
		if(!empty($productIds)) {
			$this->db_ebmaster_read->select('count(*) as count,product_id');
			$this->db_ebmaster_read->from('comment');
			$this->db_ebmaster_read->where_in('product_id', $productIds);
			$this->db_ebmaster_read->group_by('product_id');
			$query = $this->db_ebmaster_read->get();
			$reviewListQuery = $query->result_array();
			$reviewList = reindexArray($reviewListQuery, 'product_id');
		}
		return $reviewList;
	}

	/**
	 * 取出指定商品分类的子分类 这个数据是实时的数据
	 * @param  integer $categoryId 分类的id
	 * @author qcn qianchangnian@hofan.cn
	 * @return array 返回商品的指定分类的子分类
	 */
	public function getProductCategorySub($categoryId) {
		$cacheKey = 'productCategorySubByCatId_%s' ;
		$result = $this->memcache->get( $cacheKey , $categoryId );
		if( $result === FALSE || !is_array( $result ) ){
			$this->db_ebmaster_read->select('id');
			$this->db_ebmaster_read->from('category');
			$this->db_ebmaster_read->like('path',$categoryId);
			$this->db_ebmaster_read->where('status',1); //取出子分类的时候不包含影藏的分类
			$query = $this->db_ebmaster_read->get();
			$result = $query->result_array();
			$result = extractColumn( $result ,'id');
			$this->memcache->set( $cacheKey , $result , $categoryId );
		}
		return $result;
	}

	/**
	 * 获取销售分类的商品的ids 调用此方法需加缓存 这个数据是实时的数据
	 * @param  array $categoryIds 分类的id数组
	 * @author qcn qianchangnian@hofan.cn
	 * @return array 返回销售分类的商品
	 * @todo mcKey 优化
	 */
	public function getSaleCategoryProduct($categoryIds) {
		$categoryIds = is_array($categoryIds) ? $categoryIds : array($categoryIds);
		$productList = array();
		if(!empty($categoryIds)) {
			$cacheKey = 'saleCategoryPidByCatId_%s' ;
			$cacheKeyValue = md5( implode( '_', $categoryIds ) );
			$productList = $this->memcache->get( $cacheKey , $cacheKeyValue );
			if( $productList === FALSE || !is_array( $productList ) ){
				$productList = array();
				$this->db_ebmaster_read->select('pid');
				$this->db_ebmaster_read->from('category_product');
				$this->db_ebmaster_read->where_in('category_id',$categoryIds);
				$this->db_ebmaster_read->where('status',1); //销售分类下的商品状态 1是正常 -2是删除
				$query = $this->db_ebmaster_read->get();
				$productListQuery = $query->result_array();
				foreach ( $productListQuery as $v ){
					$productList[ $v['pid'] ] = $v['pid'] ;
				}

				$this->memcache->set( $cacheKey , $productList , $cacheKeyValue );
			}
		}
		return $productList;
	}

	public function getProDiscount(&$productInfo){
		$resPromoteDiscountId = $this->db_ebmaster_read->select('promote_discount_id')->from('promote_range')->where('type',2)->where('content',$productInfo['id'])->where('status',1)->order_by('last_time','DESC')->get()->row();
		if(!$resPromoteDiscountId){
			$resPromoteDiscountId = $this->db_ebmaster_read->select('promote_discount_id')->from('promote_range')->where('type',1)->where('content',$productInfo['category_id'])->where('status',1)->order_by('last_time','DESC')->get()->row();
		}

		$promoteDiscountId = $resPromoteDiscountId->promote_discount_id;
		$discountRes =  $this->db_ebmaster_read->select('effect_value,start_time,end_time')->from('promote_discount')->where('id',$promoteDiscountId)->where('type',1)->where('status',1)->get()->row();

		$return = $discountRes?array(
			'discount' => (int) $discountRes->effect_value,
			'start_time' => $discountRes->start_time,
			'end_time' => $discountRes->end_time,
		):false;
		return $return;
	}

	/**
	 * 获取商品id根据属性id和属性值id
	 * @param  array $attributeIds 属性id
	 * @author qcn qianchangnian@hofan.cn
	 * @return array 返回narrow search匹配商品id数组
	 *
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	protected function getProductIdsByNarrowSearch( $categoryId , $languageId , $attributeIds) {
		$attributeIds = is_array($attributeIds) ? $attributeIds : array($attributeIds); //属性id判断处理
		//当属性id和属性值id唯恐的时候返回
		if(empty($attributeIds)) { return array(); }

		//获取pids
		$attrInfo = Categoryv2Model::getInstanceObj()->getNarrowSearchByCategoryId( $categoryId , $languageId  );
		$attrInfo = $attrInfo['info'];
		//获取PID 列表
		foreach ( $attributeIds as $attrId => $groupIds ){
			$pidArray [ $attrId ] = array();
			foreach ( $groupIds as $groupId ){
				if( isset( $attrInfo[ $attrId ] [ 'group_info' ][ $groupId ] ['pids'] ) && ( count(  $attrInfo[ $attrId ] [ 'group_info' ][ $groupId ] ['pids'] ) > 0 ) ){
					if( count( $pidArray [ $attrId ] ) > 0 ){
						foreach ( $attrInfo[ $attrId ] [ 'group_info' ][ $groupId ] ['pids'] as $pid => $marketPrice ){
							$pidArray [ $attrId ][ $pid ] = $marketPrice ;
						}
					}else{
						$pidArray [ $attrId ] =  $attrInfo[ $attrId ] [ 'group_info' ][ $groupId ] ['pids'] ;
					}
				}
			}
			//返回PID 列表 赋值给 属性的数组
			if( count( $pidArray [ $attrId ]  ) ){
				$pidArray [ $attrId ] = array_keys(  $pidArray [ $attrId ] );
			}
		}

		$arrayIds = array();//初始化返回的id数组
		if(count($pidArray) > 1) {
			$arrayIds = array_intersect_upgrade($pidArray);//循环取出所有的数组的交集
		} else {
			$arrayIds = current($pidArray);
		}

		return $arrayIds;
	}

	/**
	 * 获得类别下的商品信息排除条件内数据[注意！调用次方法父级必须加MC缓存]
	 * @param inc $categoryId 类别ID
	 * @param inc $languageId 语言ID
	 * @param array $exceptionPids 排除ID
	 * @param inc $limit
	 * @param array orderby
	 * @return array 返回排除条件的商品信息
	 * @author lucas
	 */
	protected function getGoodsListWithException( $categoryId, $languageId, $exceptionPids, $limit = 10, $orderby = array() ){
		$categoryIds = array();
		if( !empty($categoryId) ){
			//取出指定分类的子分类 //分类ID
			$categoryIds = $this->getProductCategorySub( $categoryId );
		}

		//取出商品信息
		$this->db_ebmaster_read->select('id');
		$this->db_ebmaster_read->from('product');
		//商品id取出条件的处理
		if( !empty($categoryIds) ){
			$this->db_ebmaster_read->where_in( 'category_id', $categoryIds );
		}
		if( !empty($exceptionPids) ){
			$this->db_ebmaster_read->where_not_in( 'id', $exceptionPids );
		}

		$this->db_ebmaster_read->where( 'status', 1 ); //商品的状态 1是上架 0是下架
		//按照设置排序规则商品排序
		$this->db_ebmaster_read->order_by( $orderby['column'], $orderby['dir'] ); //促销规则排序

		$this->db_ebmaster_read->limit( $limit );
		$query = $this->db_ebmaster_read->get();
		$list = $query->result_array();
		$pids = extractColumn($list,'id');
		$list = $this->getProInfoById( $pids, $languageId );

		return $list;
	}

	/**
	 * 获取指定分类下商品的总数
	 * @param  array $narrowSearchProductIds 满足narrow search的商品的数组
	 * @param  array $proIdsFromCat 满足副分类（销售分类）的商品的数组
	 * @param  array $categoryIds 分类id数据
	 * @param  array $param URL参数
	 * @return integer
	 * @author  qcn qianchangnian@hofan.cn
	 */
	protected function getProductCountFromCategory($narrowSearchProductIds = array(), $proIdsFromCat = array(), $categoryIds = array(), $param = array() , $priceStepList = array() ) {
		if( !empty($priceStepList ) && is_array( $priceStepList ) ){
			$this->db_ebmaster_read->select('id,market_price');
		}
		//取出指定分类下商品总数
		$this->db_ebmaster_read->from('product');
		//商品id取出条件的处理
		if(!empty($narrowSearchProductIds)) {
			$this->db_ebmaster_read->where_in( 'id', $narrowSearchProductIds );
		}
		//商品id取出条件的处理
		if( !empty($proIdsFromCat) ){
			$where_tmp = '( `category_id` IN ( ' . implode(',' , $categoryIds ) . ') OR `id`  IN ( ' . implode( ',' , $proIdsFromCat ) . ' ) )';
			$this->db_ebmaster_read->where( $where_tmp );
		} else {
			$this->db_ebmaster_read->where_in('category_id',$categoryIds);
		}

		$this->db_ebmaster_read->where('status',1); //商品的状态 1是上架 0是下架
		$this->db_ebmaster_read->where('price !=','0.00');
		$this->db_ebmaster_read->where('market_price !=','0.00');

		if( !empty( $priceStepList ) && is_array( $priceStepList ) && count( $priceStepList ) > 0  ){
			$pidsInfo = $this->db_ebmaster_read->get()->result_array();
			$resultCount = 0 ;
			$allCounterOn = ( ( count( $priceStepList['has_price_step_list'] ) > 0 ) ? FALSE : TRUE ) ;
			if( !empty( $pidsInfo ) ){
				foreach ( $pidsInfo as $info ){
					if( !empty( $priceStepList['price_step_list'] ) ){
						foreach ( $priceStepList['price_step_list'] as &$v ){
							if( ( $info['market_price']  >= $v['start'] ) && ( $info['market_price'] < $v['end'] ) ){
								$v['pidsCount'] ++ ;
								$v['pidsList'][ $info['id'] ]  = $info['market_price'] ;
								if( ( $allCounterOn === FALSE ) && ( $v['selected'] === TRUE ) ){
									$resultCount ++ ;
								}else if ( $allCounterOn === TRUE ){
									$resultCount ++ ;
								}
								break;
							}
						}
					}
				}
			}
		}else{
			//价格的排序是按照商品默认的价格排序
			$paramPriceMax = isset($param['price_max']) && !empty($param['price_max']) ? $param['price_max']:0;
			$paramPriceMin = isset($param['price_min']) && !empty($param['price_min']) ? $param['price_min']:0;
			if($paramPriceMax && $paramPriceMin &&$paramPriceMax == $paramPriceMin) {
				$this->db_ebmaster_read->where('price',$paramPriceMax);
			} else {
				if($paramPriceMax) {
					$this->db_ebmaster_read->where('market_price <=',$paramPriceMax);
				}
				if($paramPriceMin) {
					$this->db_ebmaster_read->where('market_price >=',$paramPriceMin);
				}
			}

			$resultCount = $this->db_ebmaster_read->count_all_results();
		}


		return array( 'totalCount'=> $resultCount , 'priceStepList' => $priceStepList ) ;
	}

	/**
	 * 获取某个分类下 所有秒杀商品+所有推荐商品 否在此分类下面
	 * @param  int  $categoryId 分类ID
	 *
	 * @return $result ;
	 * array( $pid1=> $pid1 , $pid2=> $pid2 , ... )
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getAllSpecialProByCatid( $categoryId = 0 ){
		$result = $result_tmp = array();
		if( (int)$categoryId > 0 ){
			$cacheKey = 'get_special_pro_by_catid_%s';
			$result = $this->memcache->get( $cacheKey , $categoryId );
			if( $result === FALSE || !is_array( $result ) ){
				$result = array();
				//获取所有秒杀商品
				$promoteObj = new Promote();
				$allSecKillPros = $promoteObj->getAllSecKillPro();
				if( !empty( $allSecKillPros ) && is_array( $allSecKillPros ) ){
					$result_tmp = $allSecKillPros ;
				}
				//获取所有推荐商品PID
				$recommendProBycatId = $this->getRecommendProBycatId( $categoryId );
				if( !empty( $recommendProBycatId ) && is_array( $recommendProBycatId ) ){
					foreach ( $recommendProBycatId as $k => $v ) {
						if( !isset( $result_tmp [ $k ] ) ){
							$result_tmp[ $k ] = $v ;
						}
					}
				}
				if( !empty( $result_tmp ) && is_array( $result_tmp ) ){
					//取出指定分类的子分类
					$categoryObj = new Categoryv2Model();
					$categoryIds = $categoryObj->getSubCategoryIdsById( $categoryId );
					if( !empty( $categoryIds ) && is_array( $categoryIds ) ){
						$this->db_ebmaster_read->select('id');
						$this->db_ebmaster_read->from('product');
						$this->db_ebmaster_read->where_in('category_id',$categoryIds);
						$this->db_ebmaster_read->where_in('id', array_keys( $result_tmp ) );
						$this->db_ebmaster_read->where('status',1); //商品的状态 1是上架 0是下架
						$this->db_ebmaster_read->where('price !=','0.00');
						$this->db_ebmaster_read->where('market_price !=','0.00');
						$pros = $this->db_ebmaster_read->get()->result_array();
						$pros = reindexArray( $pros , 'id' );
						//获取softcopy 分类ID
						$getSaleCategoryProduct = $this->getSaleCategoryProduct( $categoryIds );

						foreach ( $result_tmp as $k => $v ){
							if( isset( $pros[ $k ] ) || isset( $getSaleCategoryProduct[ $k ]  ) ) {
								$result[ $k ] = $k;
							}
						}
					}
				}

				$this->memcache->set( $cacheKey , $result , $categoryId );
			}
		}

		return  array( 'pids'=> $result , 'count' => count( $result ) );
	}

	/**
	 * 获取某一个分类下面 被推荐的商品PID
	 * @param int $categoryId
	 *
	 * @return $result
	 * $result = array(
	 * 		'352172'=> array( //分类ID
	 * 			"product_id" =>"352172" //PID
	 * 			"sort" =>"0"		//排序
	 * 		),
	 * 		...
	 * 	)
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getRecommendProBycatId( $categoryId = 0 ){
		$result = array();
		if( $categoryId > 0 ){
			//获取当前key
			$cacheKey = 'get_recommend_pro_by_catid_%s';
			$result = $this->memcache->get( $cacheKey , $categoryId );
			if( $result === FALSE || !is_array( $result ) ){
				$this->db_ebmaster_read->select( 'product_id ,sort ' );
				$this->db_ebmaster_read->from( 'product_recommend' );
				$this->db_ebmaster_read->where( 'category_id' , $categoryId );
				$this->db_ebmaster_read->where( 'status' , 1 );
				$this->db_ebmaster_read->order_by( 'sort' , 'desc' );
				$this->db_ebmaster_read->order_by( 'id' , 'asc' );
				$recommendPro= $this->db_ebmaster_read->get()->result_array();
				$result = reindexArray( $recommendPro , 'product_id' );
				$this->memcache->set( $cacheKey , $result , $categoryId );
			}
		}
		return $result;
	}

	/**
	 * 获取PID的可用库存
	 * @param string||array $pids 商品IDS
	 * @param boolean $cache 是否读缓冲
	 * @return array
	 * @author lucas
	 */
	public function getActiveStockByPid( $pids, $cache = TRUE ){
		//获取当前key
		$cacheKey = 'get_product_stock_%s';
		$cacheParams = array( md5( implode('_', $pids) ) );
		$result = $this->memcache->get( $cacheKey , $cacheParams );

		if( ( $result === FALSE || !is_array( $result )) || $cache == FALSE ){
			$result = array();
			$queryStock = $this->db_ebmaster_read->select('product_id,stock')->from('product_sku')->where('status', 1)->where_in('product_id', $pids)->get();
			if ( $queryStock ) {
				$resArr = $queryStock->result_array();
				foreach( $resArr as $v ){
					if( !empty($result[$v['product_id']]) ){
						$result[$v['product_id']] = $result[$v['product_id']]+$v['stock'];
					}else{
						$result[$v['product_id']] = $v['stock'];
					}
				}
			}
			$this->memcache->set( $cacheKey , $result , $cacheParams );
		}

		return $result;
	}


	/**
	 * 获取商品的信息, 最新的促销 ,校验等
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
	 * 					//37=(100101 )->秒杀预估+捆绑+折扣
	 * 					//4001=（被捆绑类型特殊）->被捆绑商品
	 * 			'promoteId' => $promote_id ,//对应促销type的促销ID
	 * 			'bindingPid' => 0 , //如果是被捆绑商品 这里 捆绑的主商品PID promote_type =4001  此字段为 捆绑的主商品
	 * 			'qty' => 123 , //购买的商品个数
	 * 		),
	 * 		$sku2 => array( //商品的sku
	 * 			'pid' => $pid ,//sku 对应的PID
	 * 			'promoteType' => $promote_type ,//
	 * 					//	0->默认商品；
	 * 					//	1 =（1）->折扣倒计时；
	 * 					//4=（100）->捆绑；
	 * 					//5=（101）->捆绑+折扣；
	 * 					// 32=（100000）->秒杀；
	 * 					//33=（100001）->秒杀预告+折扣；
	 * 					//36=(100100)->秒杀预告+捆绑
	 * 					//37=(100101)->秒杀预告+折扣+捆绑
	 * 					//4001=（被捆绑类型特殊）->被捆绑商品
	 * 			'promoteId' => $promote_id ,//对应促销type的促销ID
	 * 			'bindingPid' => 0 , //如果是被捆绑商品 这里 捆绑的主商品PID promote_type =4001  此字段为 捆绑的主商品
	 * 			'qty' => 123 , //购买的商品个数
	 * 		),
	 * )
	 * @param int $languageId //当前语言
	 * @param int $type  默认是1 目前只支持 [1/2/3/4]  1则获取 非捆绑的促销ID  2 获取所有促销规则（此参数不需要传）。 3获取非秒杀的促销规则，4 获取非秒杀 非捆绑的促销规则 （此参数不需要传）
	 *
	 * @return array $result
	 *
	 *	 array(
	 *		'errorCode'=>  0 , //10  Parameter Error 0是正常
	 *		'errMsg' => '' , //对应的错误信息
	 *		'data' => array( //具体信息
	 *			'BI292' => array( //所购买的sku
	 *				'pid' => 319116 , // sku对应的PID
	 *				'promoteType'=> 32 , //参数传送过来的促销类型
	 *					// 0->默认商品；1 =（1）->折扣倒计时;4=（100）->捆绑；
	 *					//5=（101）->捆绑+折扣;32=（100000）->秒杀；
	 *					//33=（100001）->秒杀预告+折扣; 36=(100100)->秒杀预告+捆绑
	 *					//37=(100101)->秒杀预告+折扣+捆绑 ;4001=（被捆绑类型特殊）->被捆绑商品
	 *				'promoteId' => 9999 ,//传送的促销ID
	 *				'qty' => 1 , //购买数量 当库存不足 现在的个数 则是库存剩余数
	 *				'status' => 1 ,  此商品的sku 信息
	 *					//1=正常 ;
	 *					//-4=促销ID不合法;
	 *					//-3 库存为0 ;
	 *					//-2 库存类型 type 不合法 非(1,2);
	 *					//-1 pid状态不为1  则用-1标识
	 *					//
	 *				'isChange' => 0 , //此信息是否发生变化 isChange 直接用二进制标识 (第一位是购物数量 第二位是促销类型 第三位促销ID发生变化)
	 *					//   0 = 信息 是未发生变化
	 *					// 1(=1 ) = 商品购买数发生变化  当库存不足 现在的个数
	 *					// 2(=10 ) = 促销规则类型 发生变化
	 *					// 3(=11 ) = 促销规则类型 发生变化 + 商品购买数发生变化  当库存不足 现在的个数
	 *					// 4(=100) = 促销ID发生变化
	 *					// 5(=101 )= 促销ID发生变化+ 商品购买数发生变化  当库存不足 现在的个数
	 *					// 6(=110 )= 促销ID发生变化+促销规则类型 发生变化
	 *					// 7(=111 )= 促销ID发生变化+促销规则类型 发生变化 + 商品购买数发生变化  当库存不足 现在的个数
	 *				'sku'=>'BI292' , //此商品的sku
	 *				'goodsName' => 'CPU Cooling Fan for Toshiba A200 A205 A215',//此商品的name
	 *				'finalPromoteType' => 32 , //最新的促销类型
	 *					// 0->默认商品；1 =（1）->折扣倒计时;4=（100）->捆绑；
	 *					//5=（101）->捆绑+折扣;32=（100000）->秒杀；
	 *					//33=（100001）->秒杀预告+折扣; 36=(100100)->秒杀预告+捆绑
	 *					//37=(100101)->秒杀预告+折扣+捆绑 ;4001=（被捆绑类型特殊）->被捆绑商品
	 *				'finalPromoteId' => 9999 ,//最新的促销ID
	 *				'finalPromoteDiscount'=> 30 ,//促销的折扣数 （1-30/100）*市场价
	 *				'finalmarketPrice' => 11 ,//市场价
	 *				'finalPrice' => 7.7 ,//最终销售价格
	 *				'purchasePrice' => 0.00 ,//采购价
	 *				'costPrice' => 0.00 ,//成本价
	 *				'length' => 0.000000 ,//长
	 *				'width' => 0.000000 ,//宽
	 *				'height' => 0.000000 ,//高
	 *				'weight' => 0.000000 ,//重
	 *				'stock' => 5 ,//现在剩余的库存数
	 *				'warehouse' => 'GZ' ,//仓库
	 *			),
	 *			...
	 *		)
	 *	)
	 *
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 *
	 */
	public function getLatestPromotionByInfo( $pidsPromotes  , $languageId = 1 , $type=1 ){

		$result = array( 'errorCode'=> 0 , 'errMsg'=> '' , 'data' => array() ,  );
		// 0->默认商品；1 =（1）->折扣倒计时;4=（100）->捆绑；
		//5=（101）->捆绑+折扣;32=（100000）->秒杀；
		//33=（100001）->秒杀预告+折扣; 36=(100100)->秒杀预告+捆绑
		//37=(100101)->秒杀预告+折扣+捆绑 ;4001=（被捆绑类型特殊）->被捆绑商品
		// 55555  -> 购物车中的赠品 		CartModel::CART_GOODS_TYPE_GIFT;
		$legalPromoteTypeArr = array( 0 ,1,4,5,32,33,36,37 ,4001 , CartModel::CART_GOODS_TYPE_GIFT  ) ;
		$resultGoodsInfo = array();
		if( !empty( $pidsPromotes ) && ( count( $pidsPromotes ) > 0 ) ){
			//循环获取PID  排除被捆绑的
			$promotePagePids = $bindindTidePromoteId = array();
			//判断是否有捆绑商品 无捆绑商品 不用获取捆绑促销信息
			$getPromoteType = 1 ;//获取非捆绑的促销规则
			foreach ( $pidsPromotes as $v ){
				//4=（100）->捆绑;5=（101）->捆绑+折扣;36=(100100)->秒杀预告+捆绑;37=(100101)->秒杀预告+折扣+捆绑
				if( ( $getPromoteType !== 2 ) && in_array( (int)$v[ 'promoteType' ] , array( 4,5,36,37 ) ) ) {
					$getPromoteType = 2 ; //获取所有促销规则
				}
				if( (int)$v[ 'promoteType' ] === Promote::BUNDLE_TYPE_BINDING_TIED ){
					$bindindTidePromoteId[ $v['promoteId'] ] = $v['pid'] ;
				}
				$promotePagePids[ $v['pid'] ] = $v ;
			}
			//当传3非秒杀促销类型的时候 。  会判断 是否需要获取非捆绑商品
			if( $type === 3 ) {
				//当传3 判断 	当$getPromoteType=1 获取非捆绑商品是 则  $getPromoteType 为4  获取非捆绑促销规则 和非秒杀促销规则    ;
				//			当$getPromoteType!=1 获取非捆绑商品是 则  $getPromoteType 为3  获取非秒杀促销规则  ;
				 ( $getPromoteType === 1 ) ? $getPromoteType = 4 : $getPromoteType = 3;
			}else if( $type === 4 ){
				$getPromoteType = 4 ;
			}


			//判断主商品存在不 不存在 则参数有问题
			if( !empty( $promotePagePids ) && ( count( $promotePagePids ) > 0 ) ){
				//获取pid 详情信息
				$goodsInfo = $this->getProInfoById( array_keys( $promotePagePids ) , $languageId , $getPromoteType );
				//处理具体信息
				foreach ( $pidsPromotes as $k => $v ){
					$sku = $v['sku'];
					$promoteTypeTmp = (int)$v[ 'promoteType' ] ;
					if( in_array( $promoteTypeTmp , $legalPromoteTypeArr ) ){
						//判断pid 的状态 存在则返回 否则则不返回
						if( isset( $goodsInfo[ $v['pid'] ] ) && ( (int)$goodsInfo[ $v['pid'] ]['status'] === 1 ) ){
							$skuStockTypeTmp = isset( $goodsInfo[ $v['pid'] ]['skuInfo'][ $sku ]['type'] ) ? (int)$goodsInfo[ $v['pid'] ]['skuInfo'][ $sku ]['type'] : 0 ;
							$v['productSkuType'] = $skuStockTypeTmp;
							$v['finalBindingPid'] = (int)$v['bindingPid'];
							//判断库存 type 是否合法
							if( in_array( $skuStockTypeTmp ,  array( 1,2 ) ) ){
								//判断type 为库存销售  库存数为0的情况
//								if( isset( $goodsInfo[ $v['pid'] ]['skuInfo'][ $sku ]['stock'] ) && ( (int) $goodsInfo[ $v['pid'] ]['skuInfo'][ $sku ]['stock'] <= 0 ) && ( $skuStockTypeTmp === 1 ) ){
//									$v['status'] = -3 ; //status -3 库存为0 ;
//									$resultGoodsInfo[ $k ] = $v ;
//								}else{
									$v['status'] = 1 ;
									$v[ 'isChange' ] = 0 ;
									//判断现在的库存数是否大于现在的购买的商品个数 如果小于 那么购买个数发生变化
//									if( ( (int) $goodsInfo[ $v['pid'] ]['skuInfo'][ $sku ]['stock'] < (int)$v[ 'qty' ] ) && ( $skuStockTypeTmp === 1 )  ){
//										$v[ 'isChange' ]  += 1 ; //库存变化 1
//										$v[ 'qty' ] = (int) $goodsInfo[ $v['pid'] ]['skuInfo'][ $sku ]['stock'] ; //购买的个数发生变化
//									}

									//促销校验 被捆绑校验
									if( $promoteTypeTmp === Promote::BUNDLE_TYPE_BINDING_TIED ){
										//判断 被捆绑的促销规则 类型 是否发生变化
										if( !( isset( $goodsInfo[ $v['bindingPid'] ]['status'] ) && ( (int)$goodsInfo[ $v['bindingPid'] ]['status'] === 1 ) && isset( $goodsInfo[ $v['bindingPid'] ] ['promote_info'] [ Promote::BUNDLE_TYPE_BINDING ] [  $v['pid'] ] ['promote_type'] ) && ( (int)$goodsInfo[ $v['bindingPid'] ] ['promote_info'] [ Promote::BUNDLE_TYPE_BINDING ] [  $v['pid'] ] ['promote_type'] === Promote::BUNDLE_TYPE_BINDING_TIED ) ) ){
											$v[ 'isChange' ] += 10 ; //促销规则类型 发生变化 10
										}
										//判断被捆绑的PID 是否发生变化
										if( !(isset( $goodsInfo[ $v['bindingPid'] ] ['promote_info'] [ Promote::BUNDLE_TYPE_BINDING ] [  $v['pid'] ] ['binding_id'] ) && ( (int)$goodsInfo[ $v['bindingPid'] ] ['promote_info'] [ Promote::BUNDLE_TYPE_BINDING ] [  $v['pid'] ] ['binding_id'] === (int)$v['promoteId'] ) ) ){
											$v[ 'isChange' ]  += 100 ; ///促销ID发生变化 100
										}
										//促销Type 发生变化 或者 被捆绑的信息不存在
										if( ( HelpOther::getBinarySystemIsTrueByBit( $v[ 'isChange' ] , 2 ) ) || ( (int)$goodsInfo[ $v['bindingPid']  ]['status'] !== 1 ) || !isset( $goodsInfo[ $v['bindingPid'] ] ['promote_info'] [ Promote::BUNDLE_TYPE_BINDING ] [  $v['pid'] ] ) ){
											//格式化数据
											$v = $this->_formattedInGetLatestPromotionByInfo( $v , $sku , $goodsInfo[ $v['pid'] ] ) ;
											$v['finalBindingPid'] = 0;
										}else{
											//格式化数据
											$v = $this->_formattedInGetLatestPromotionByInfo( $v , $sku , $goodsInfo[ $v['bindingPid'] ] ['promote_info'] [ Promote::BUNDLE_TYPE_BINDING ] [  $v['pid'] ] ) ;
										}
									//其他促销校验
									}else{
										if( ( $promoteTypeTmp !== (int)$goodsInfo[ $v['pid'] ]['promote_type'] ) ){
											$v[ 'isChange' ] += 10 ; //促销规则类型 发生变化10
										}
										//判断促销ID 是否发生变化
										//秒杀
										if( in_array( $promoteTypeTmp , array( 32 ) ) ){
											if( !( isset( $goodsInfo[ $v['pid'] ]['promote_info'][ Promote::PROMOTE_TYPE_SEC_KILL ]['id'] ) && ( (int)$goodsInfo[ $v['pid'] ]['promote_info'][ Promote::PROMOTE_TYPE_SEC_KILL ]['id'] === (int)$v['promoteId'] ) ) ){
												$v[ 'isChange' ] += 100 ; //促销ID发生变化 100
											}
										//普通折扣
										}else if( in_array( $promoteTypeTmp , array( 1,5,33,37 ) ) ){
											if( !( isset( $goodsInfo[ $v['pid'] ]['promote_info'][ Promote::DISCOUNT_TYPE_NOMAL ]['id'] ) && ( (int)$goodsInfo[ $v['pid'] ]['promote_info'][ Promote::DISCOUNT_TYPE_NOMAL ]['id'] === (int)$v['promoteId'] ) ) ){
												$v[ 'isChange' ] += 100 ; //促销ID发生变化 100
											}
										}
										//格式化数据
										$v = $this->_formattedInGetLatestPromotionByInfo( $v , $sku , $goodsInfo[ $v['pid'] ] ) ;
									}
									if( (int)$v[ 'isChange' ] > 0 ){
										$v[ 'isChange' ] = bindec( $v[ 'isChange' ] ) ;
									}
									//赋值
									$resultGoodsInfo[ $k ] = $v;
//								}
							}else{
								$v['status'] = -2 ; //status -2 库存类型 type 不合法 非(1,2) ;
								$resultGoodsInfo[ $k ] = $v ;
							}
						}else{
							$v['status'] = -1 ; //status -1则是状态不为1  则用-1标识
							$resultGoodsInfo[ $k ] = $v ;
						}
					} else{
						$v['status'] = -4 ; //促销ID 不合法
						$resultGoodsInfo[ $k ] = $v ;
					}
				}
			}
		}else{
			$result['errorCode'] = 10  ;
			$result['errMsg'] = 'Parameter Error' ;
		}

		$result['data'] = $resultGoodsInfo;
		return $result ;
	}

	/**
	 * 专属  GetLatestPromotionByInfo 内部格式化数据使用
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	private function _formattedInGetLatestPromotionByInfo( $v , $sku , $goodInfo ){
		$v['goodsName'] = $goodInfo['name'] ;
		$v['finalPromoteType'] = $goodInfo['promote_type'] ;
		$v['finalPromoteId'] = $goodInfo['promote_id'] ;
		$v['finalPromoteDiscount'] = $goodInfo['promote_discount'];

		//判断是否是复合商品
		if( (int) $goodInfo['type'] === 2 ){
			$v['finalmarketPrice'] = isset( $goodInfo[ 'skuInfo' ][ $sku ]['market_price'] ) ? ( $goodInfo['market_price'] + $goodInfo[ 'skuInfo' ][ $sku ]['market_price'] ): $goodInfo['market_price'] ;
			$v['finalPrice'] = isset( $goodInfo[ 'skuInfo' ][ $sku ]['final_price'] ) ? $goodInfo[ 'skuInfo' ][ $sku ]['final_price'] : $goodInfo['final_price'] ;
		}else{
			$v['finalmarketPrice'] = $goodInfo['market_price'] ;
			$v['finalPrice'] = $goodInfo['final_price'] ;
		}

		//coupon礼品判断
		if( (int)$v['promoteType'] === CartModel::CART_GOODS_TYPE_GIFT ){
			$v['finalPromoteType'] = CartModel::CART_GOODS_TYPE_GIFT  ;
			$v['finalPromoteId'] = $v['promoteId'] ;
			//$v['finalPromoteDiscount'] = 0 ; 赠品的折扣 暂时不处理
			$v['finalPrice'] = isset( $v['finalPrice'] )  ? $v['finalPrice'] : $goodInfo['final_price'];
		}

		if( (int) $v['finalPromoteType'] === 32  ){
			$v['seckillIsForeshow'] = (int)$goodInfo['promote_info'][ Promote::PROMOTE_TYPE_SEC_KILL ]['is_foreshow'] ;
			if ( $v['seckillIsForeshow'] === 0 ) {
				$v['purchasedNumber'] = (int)$goodInfo['promote_info'][ Promote::PROMOTE_TYPE_SEC_KILL ]['purchased_number'] ;
				$v['targetLimitOrder'] = (int)$goodInfo['promote_info'][ Promote::PROMOTE_TYPE_SEC_KILL ]['target_limit_order'] ;
				$v['targetLimitTotal'] = (int)$goodInfo['promote_info'][ Promote::PROMOTE_TYPE_SEC_KILL ]['target_limit_total'] ;
				$v['targetOverplusLimitOrder'] = (int)$goodInfo['promote_info'][ Promote::PROMOTE_TYPE_SEC_KILL ]['overplus_limit_order'] ;
				//修改购买库存格式

				//判断可以购买的商品个数
				$countTmp = (int)( $v['targetLimitTotal'] - $v['purchasedNumber'] );
				//判断每单购买数和剩余数比较
				$countTmp = $countTmp > (int)$v['targetLimitOrder'] ? (int)$v['targetLimitOrder'] : $countTmp ;
				$countTmp = $countTmp > 0 ? $countTmp : 0 ;
				( $v[ 'qty' ] > $countTmp )?  ( $v[ 'qty' ] = $countTmp ) : 1 ;

				//结束时间到现在的时间差值 添加购物车30分钟 与结束差值取最小值然后 添加到购物车
				$timeDifference = (int) ( strtotime( $goodInfo['promote_info'][ Promote::PROMOTE_TYPE_SEC_KILL ]['end_time'] ) - HelpOther::requestTime() );
				$timeDifference = $timeDifference <= 0 ? 0 : $timeDifference ;
				$v['seckillEndTime'] = ( HelpOther::requestTime()+( $timeDifference < 1800 ? $timeDifference : 1800 ) ) ;
			}
		}else{
			$v['seckillIsForeshow'] = -1 ;
			$v['seckillEndTime'] = 0 ;
			$v['purchasedNumber'] = 0 ;
			$v['targetLimitOrder'] = 0 ;
			$v['targetLimitTotal'] = 0 ;
			$v['targetOverplusLimitOrder'] = 0 ;
		}
		$v['productType'] = (int)$goodInfo['type'] ;
		$v['path'] = trim( $goodInfo['path']  );
		$v['categoryId'] = $goodInfo['category_id'] ;
		$v['url'] = $goodInfo['url'] ;
		$v['image'] = $goodInfo['image'] ;
		$v['image45fullUrl'] = HelpUrl::img( trim( $goodInfo['image'] ) , 45 ) ;
		$v['image70fullUrl'] = isset( $goodInfo['skuInfo'][ $sku ]['image'] )? HelpUrl::img( trim( $goodInfo['skuInfo'][ $sku ]['image'] ) , 70 ) :HelpUrl::img( trim( $goodInfo['image'] ) , 70 ) ;
		$v['image350fullUrl'] = isset( $goodInfo['skuInfo'][ $sku ]['image'] )? HelpUrl::img( trim( $goodInfo['skuInfo'][ $sku ]['image'] ) , 350 ) :HelpUrl::img( trim( $goodInfo['image'] ) , 350 ) ;

		$v['category_id'] = $goodInfo['category_id'] ;
		//sku 其他信息
		$v['purchasePrice'] = isset( $goodInfo[ 'skuInfo' ][ $sku ]['purchase_price'] ) ?  $goodInfo[ 'skuInfo' ][ $sku ]['purchase_price'] : 0 ;
		$v['costPrice'] = isset( $goodInfo[ 'skuInfo' ][ $sku ]['cost_price'] ) ?  $goodInfo[ 'skuInfo' ][ $sku ]['cost_price'] : 0 ;
		$v['length'] = isset( $goodInfo[ 'skuInfo' ][ $sku ]['length'] ) ?  $goodInfo[ 'skuInfo' ][ $sku ]['length'] : 0 ;
		$v['width'] = isset( $goodInfo[ 'skuInfo' ][ $sku ]['width'] ) ?  $goodInfo[ 'skuInfo' ][ $sku ]['width'] : 0 ;
		$v['height'] = isset( $goodInfo[ 'skuInfo' ][ $sku ]['height'] ) ?  $goodInfo[ 'skuInfo' ][ $sku ]['height'] : 0 ;
		$v['weight'] = isset( $goodInfo[ 'skuInfo' ][ $sku ]['weight'] ) ?  $goodInfo[ 'skuInfo' ][ $sku ]['weight'] : 0 ;
		$v['stock'] = isset( $goodInfo[ 'skuInfo' ][ $sku ]['stock'] ) ?  $goodInfo[ 'skuInfo' ][ $sku ]['stock'] : 0 ;
		$v['warehouse'] = isset( $goodInfo[ 'skuInfo' ][ $sku ]['warehouse'] ) ?  $goodInfo[ 'skuInfo' ][ $sku ]['warehouse'] : 0 ;
		$v['typeSensitive'] = isset( $goodInfo[ 'skuInfo' ][ $sku ]['type_sensitive'] ) ?  $goodInfo[ 'skuInfo' ][ $sku ]['type_sensitive'] : 0 ;
		$v['skuInfo'] = $goodInfo[ 'skuInfo' ] ;//此PID下面的其他符合商品信息 传送过去 以便于其他地方重复调用获取商品详情的信息
		return $v ;
	}

	/**
	 * 获得商品描述信息
	 * @param integer||string $pids
	 * @param integer $language
	 * @return Array 返回商品描述信息KEY为PID
	 * @author lucas
	 */
	public function getProductDescription( $pids, $languageId = 1 ){
		if( empty( $pids ) ){
			return array();
		}
		//获取当前key
		$cacheKey = 'get_prodesc_by_pid_%s';
		$result = $this->memcache->get( $cacheKey, is_array( $pids ) ? implode( '_', $pids ) : $pids );

		if( $result === FALSE || !is_array( $result ) || !empty( $pids ) ){
			$pids = is_array( $pids ) ? $pids : array( $pids );

			$this->db_ebmaster_read->select( 'product_id, content' );
			$this->db_ebmaster_read->from( 'product_description_' . $languageId );
			$this->db_ebmaster_read->where_in( 'product_id' , $pids );
			$this->db_ebmaster_read->order_by( 'last_time desc' );
			$productDesc = $this->db_ebmaster_read->get()->result_array();
			$result = array();
			foreach( $productDesc as $record ){
				$result[ $record['product_id'] ] = trim( $record['content'] );
			}

			$this->memcache->set( $cacheKey, $result, $pids );
		}else{
			$result = array();
		}

		return $result;
	}

	/**
	 * 修改商品的销量
	 * @param  array $info 销售商品信息
	 */
	public function updateGoodsSaleBatch($info) {
		if(!empty($info)){
			$this->db_ebmaster_write->update_batch('product',$info,'id', false);
		}
	}

	/**
	 * 获得发送系统邮件推荐商品列表
	 * @param int $languageId 语言
	 * @param array $productIds 当前购买这个订单的商品
	 * @param int $isScriptTask FALSE为网站触发 TRUE为定时任务触发
	 * @param array $cartProductId 用户购物车商品分类ID
	 * @param int $userId 用户ID
	 * @param string $currency 货币
	 * @param sting $languageCode 语言code
	 * @param string $orderFrom 订单来源
	 * @param string $source 来源
	 * @return array recommendProductList
	 * @author lucas
	 */
	public function getEmailRecommendProduct( $languageId = 1, $productIds = array(), $isScriptTask = FALSE, $cartProductId = array(), $userId = '', $currency = '', $languageCode = 'us', $orderFrom = '', $source = '' ){
		//订单商品数
		$productNumber = count( $productIds )+4;
		$categoryModel = new Categoryv2Model();
		//取出购物车商品分类ID
		$categoryId = array();
		$categoryTotal = 0;
		if( empty( $isScriptTask ) ){
			$cartModel = CartModel::getInstanceObj();
			$cartModel->loadCart();
			$cartData = $cartModel->getCartData();

			//当单商品＋购物车商品
			$cartProductIds = extractColumn( $cartData['goodsListCommon'], 'pid' );
			$productIds = array_merge( $productIds, $cartProductIds );
			$productNumber += count( $cartProductIds );

			if( isset( $cartData['goodsListCommon'] ) && count( $cartData['goodsListCommon'] ) > 0 ){
				foreach ( $cartData['goodsListCommon'] as $record ) {
					if( $categoryTotal >= $productNumber ){
						break;
					}
					$pathArr = explode( '/', $record['path'] );
					if( in_array( $record['pid'], $productIds ) || in_array( $pathArr[1], $categoryId ) ){
						continue;
					}
					$categoryId[] = $pathArr[1];
					$categoryTotal++;
				}
			}
		}else{
			//当单商品＋购物车商品
			$productIds = array_merge( $productIds, $cartProductId );
			$productNumber += count( $cartProductId );
			//通过商品ID获得二级分类ID
			$categoryPath = $categoryModel->getParentCategoryIdByPid( $cartProductId );
			foreach ( $categoryPath as $key => $pathRecord ) {
				if( $categoryTotal >= $productNumber ){
					break;
				}
				if( in_array( $key, $productIds ) || in_array( $pathRecord[1], $categoryId ) ){
					continue;
				}
				$categoryId[] = $pathRecord[1];

				$categoryTotal++;
			}
		}

		//购物车不足 取历史订单商品分类
		if( $categoryTotal < $productNumber ){
			$OrderModel = new OrderModel();
			$orderList = array();
			if( empty( $isScriptTask ) ){
				$appModelObj = Appmodel::getInstanceObj();
				if( $appModelObj->checkUserLogin() ){
					list( $orderList ) = $OrderModel->getOrderList( $appModelObj->getCurrentUserId(), 1 );
				}
			}else{
				list( $orderList ) = $OrderModel->getOrderList( $userId, 1 );
			}

			//获得订单ID
			if( count( $orderList ) > 0 ){
				$orderIds = array_slice( extractColumn( $orderList, 'order_id' ), 0, 10 );
				//批量取出订单商品
				$orderProductList = $OrderModel->getOrderGoodsBatchList( $orderIds );
				$orderProductList = spreadArray( $orderProductList, 'order_id' );

				//获得订单商品ID
				$productIdArr = array();
				if( is_array( $orderProductList ) && count( $orderProductList ) > 0 ){
					foreach ( $orderProductList as $proItem ) {
						foreach ( $proItem as $proRecord ) {
							if( in_array( $proRecord['product_id'], $productIdArr ) ){
								continue;
							}
							$productIdArr[] = $proRecord['product_id'];
						}
					}
				}

				//通过商品ID获得二级分类ID
				$categoryPath = $categoryModel->getParentCategoryIdByPid( $productIdArr );
				foreach ( $productIdArr as $productId ) {
					if( $categoryTotal >= $productNumber ){
						break;
					}
					if( !isset( $categoryPath[ $productId ][1] ) || in_array( $categoryPath[ $productId ][1], $categoryId ) ){
						continue;
					}
					$categoryId[] = $categoryPath[ $productId ][1];

					$categoryTotal++;
				}
			}
		}

		//购物车＋历史订单商品分类不足 随机取二级分类ID
		if( $categoryTotal < $productNumber ){
			$twoCategoryIds = $categoryModel->getTwoCategoryIds();
			$randomCateIdKeys = array_rand( $twoCategoryIds, $productNumber );
			foreach ( $randomCateIdKeys as $cateIdKey ) {
				if( $categoryTotal >= $productNumber ){
					break;
				}
				if( in_array( $twoCategoryIds[ $cateIdKey ], $categoryId ) ){
					continue;
				}
				$categoryId[] = $twoCategoryIds[ $cateIdKey ];
				$categoryTotal++;
			}
		}

		//通过分类ID获得most popular第一个商品
		$pidRecommendSale = array();
		$i = 1;
		foreach ( $categoryId as $cidRecord ) {
			if( $i > 4 ){ break; }
			$catePid = $categoryModel->getPidsByCatId( $cidRecord, 1, 0, 1, $isScriptTask );
			$pid = current( $catePid );
			if( !in_array( $pid, $productIds ) ){
				$pidRecommendSale[] = $pid;
				$i++;
			}
		}

		//通过pid获得商品数据
		$productList = $this->getProInfoById( $pidRecommendSale, $languageId );

		//返回商品信息
		$productListArray = array();
		if( count( $productList ) > 0 ) {
			foreach( $productList as $key => $record ){
				$productUrl = eb_gen_url( $record['url'], false, array(), '?', $languageCode );
				if( $orderFrom == 2 ){
					$productUrl = str_replace( ".com", ".net", $productUrl );
				}

				//统计代码
				$sourceUrl = '';
				if( $source === 'order' ){
					$sourceUrl = '?utm_source=System_Own&utm_medium=Email&utm_campaign=order_new&utm_nooverride=1';
				}elseif( $source === 'payment' ){
					$sourceUrl = '?utm_source=System_Own&utm_medium=Email&utm_campaign=got-payment&utm_nooverride=1';
				}elseif( $source === 'paying24checkout' ){
					$sourceUrl = '?utm_source=System_Own&utm_medium=Email&utm_campaign=2-day-reminder-checkout&utm_nooverride=1';
				}elseif( $source === 'paying24reorder' ){
					$sourceUrl = '?utm_source=System_Own&utm_medium=Email&utm_campaign=2-day-reminder-reorder&utm_nooverride=1';
				}elseif( $source === 'paying48' ){
					$sourceUrl = '?utm_source=System_Own&utm_medium=Email&utm_campaign=7-day-reminder&utm_nooverride=1';
				}

				$productListArray[ $key ] = array(
					'name' => $record['name'], //商品的名称
					'goods_img' =>  HelpUrl::img($record['image'] , 176 ), //分类页面商品的默认图
					//商品的价格处理
					'formatPrice' => formatPrice( $record['market_price'], $currency ), //市场价格
					'formatShopPrice' => formatPrice($record['price'], $currency ), //默认销售价格
					'discount' => $record['format_promote_discount'], //折扣
					'url' => $productUrl.$sourceUrl,
				);
				//分类列表页面中1是折扣对应的数据数组的键值是1
				//32是秒杀对应的数据数组的键值是6
				//别的都是正常
				if( ($record['promote_type'] == 1 || $record['promote_type'] == 32 || $record['promote_type'] == 33) && count($record['promote_info']) > 0 ) {
					$productListArray[ $key ]['formatShopPrice'] = formatPrice( $record['final_price'], $currency ); //默认销售价格
				}
			}
		}


		return $productListArray;
	}

	/**
	 * 获得发送系统邮件推荐商品列表
	 * @param int $languageId 语言
	 * @param string $currency 货币
	 * @param sting $languageCode 语言code
	 * @return array recommendProductList
	 * @param string $orderFrom 订单来源
	 * @param string $source 来源
	 * @author lucas
	 */
	public function getEmailRecommendProductNew( $languageId = 1, $currency = '', $languageCode = 'us', $orderFrom = '', $source = '' ){
		$pids = '';
		$cacheKey = "get_email_recommend_product_ids";
		$pids = $this->memcache->get( $cacheKey, array() );

		if( $pids === FALSE ) {
			$this->db_ebmaster_read->select('id');
			$this->db_ebmaster_read->from('product');
			$this->db_ebmaster_read->where('status',1 );
			$this->db_ebmaster_read->order_by('sale_count', 'desc');
			$this->db_ebmaster_read->limit( 1000 );
			$query = $this->db_ebmaster_read->get();
			$result = $query->result_array();

			$pids = extractColumn( $result, 'id' );
			$this->memcache->set( $cacheKey, $pids, array() );
		}

		//随机获得4个商品
		$productIds = array_rand( $pids, 4 );
		$pidRecommendSale = array();
		foreach ( $productIds as $key => $value ) {
			$pidRecommendSale[] = $pids[ $value ];
		}

		//通过pid获得商品数据
		$productList = $this->getProInfoById( $pidRecommendSale, $languageId );

		//返回商品信息
		$productListArray = array();
		if( count( $productList ) > 0 ) {
			foreach( $productList as $key => $record ){
				$productUrl = eb_gen_url( $record['url'], false, array(), '?', $languageCode );
				if( $orderFrom == 2 ){
					$productUrl = str_replace( ".com", ".net", $productUrl );
				}

				//统计代码
				$sourceUrl = '';
				if( $source === 'order' ){
					$sourceUrl = '?utm_source=System_Own&utm_medium=Email&utm_campaign=order_new&utm_nooverride=1';
				}elseif( $source === 'payment' ){
					$sourceUrl = '?utm_source=System_Own&utm_medium=Email&utm_campaign=got-payment&utm_nooverride=1';
				}elseif( $source === 'paying24checkout' ){
					$sourceUrl = '?utm_source=System_Own&utm_medium=Email&utm_campaign=2-day-reminder-checkout&utm_nooverride=1';
				}elseif( $source === 'paying24reorder' ){
					$sourceUrl = '?utm_source=System_Own&utm_medium=Email&utm_campaign=2-day-reminder-reorder&utm_nooverride=1';
				}elseif( $source === 'paying48' ){
					$sourceUrl = '?utm_source=System_Own&utm_medium=Email&utm_campaign=7-day-reminder&utm_nooverride=1';
				}

				$productListArray[ $key ] = array(
					'name' => $record['name'], //商品的名称
					'goods_img' =>  HelpUrl::img($record['image'] , 176 ), //分类页面商品的默认图
					//商品的价格处理
					'formatPrice' => formatPrice( $record['market_price'], $currency ), //市场价格
					'formatShopPrice' => formatPrice( $record['price'], $currency ), //默认销售价格
					'discount' => $record['format_promote_discount'], //折扣
					'url' => $productUrl.$sourceUrl,
				);
				//分类列表页面中1是折扣对应的数据数组的键值是1
				//32是秒杀对应的数据数组的键值是6
				//别的都是正常
				if( ($record['promote_type'] == 1 || $record['promote_type'] == 32 || $record['promote_type'] == 33) && count($record['promote_info']) > 0 ) {
					$productListArray[ $key ]['formatShopPrice'] = formatPrice( $record['final_price'], $currency ); //默认销售价格
				}
			}
		}

		return $productListArray;
	}

}

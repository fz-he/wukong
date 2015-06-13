<?php

namespace app\controllers\pc;

use Yii;
use yii\filters\AccessControl;
use app\models\Imagead;
use app\models\Product;
use app\models\Goods;
use app\models\Review;
use app\models\Promote;
use app\models\Keywordrecommend;
use app\components\helpers\ArrayHelper;
use app\components\helpers\HelpOther;
use app\components\helpers\HelpUrl;
use app\components\helpers\OtherHelper;
use app\controllers\pc\common\EbController as BaseController;

class HomeController extends BaseController
{
    public function actionIndex(){  
	
		//移动版检测
		$this->_checkMobile();
		$this->languageId = $this->m_app->currentLanguageId();

		//image ad
		$imageadModelObj = Imagead::getInstanceObj();
		$this->viewData['image_ad'] = $imageadModelObj->getImageAdList(array(1,2,3,4,5,6,7,9),$this->languageId);
		$this->dataLayerPushBanners( $this->viewData['image_ad']  );

		//实例化类
		$productModelObj = Product::getInstanceObj();
		$goodsModelOjb = Goods::getInstanceObj();
		$reviewModelObj = Review::getInstanceObj();
		$list = $reviewModelObj->getRecentlyReviewList( $this->languageId );
		$goodsIds = ArrayHelper::extractColumn($list,'product_id');
		$goods = $productModelObj->getProInfoById( $goodsIds, $this->languageId );
		foreach($list as $key => $record){
			if(isset($goods[$record['product_id']])){
				$record = array_merge($record,$goods[$record['product_id']]);
				$record['goods_img'] = HelpUrl::img($record['image'] , 70 );
				$record['goods_name_show'] = OtherHelper::eb_substr($record['name'],18);
				$list[$key] = $record;
			}else{
				unset($list[$key]);
			}
		}
		$this->viewData['recently_review_list'] = $list;


		//category keyword recommend
		$keywordrecommendModel =  Keywordrecommend::getInstanceObj();
		$max_length = 64;
		if($this->languageId == 7 ) {
			$max_length = 100;
		}
		$list = $keywordrecommendModel->getCategoryKeywordRecommendList( $this->languageId ); 
		$buffer = array();
		foreach($list as $record){
			$buffer[$record['mid']][$record['row_id']][$record['kid']] = array(
				'keyword' => $record['keyword'],
				'keyword_url' => $record['keyword_url'],
				'checked' => $record['checked'],
			);
		}
		$list = array();
		foreach($buffer as $mid => $row_list){
			if(!isset($row_list[0])) continue;
			foreach($row_list as $row_id => $keyword_list){
				if(!isset($keyword_list[0])) continue;
				$row_length = 0;
				foreach($keyword_list as $kid => $keyword){
					$keyword_length = strlen($keyword['keyword']) + 1;
					if($row_length + $keyword_length > $max_length) break;

					$list[$mid][$row_id][] = $keyword;
					$row_length += $keyword_length;
				}
			}
		}
		$this->viewData['category_keyword_recommend_list'] = $list;

		//special deals
		$this->viewData['special_goods_recommend_tab'] = $goodsModelOjb->getSpecialGoodsRecommendTabTitle( $this->languageId );
		//获得special推荐产品列表
		$list = $productModelObj->getRecommendGoodsSpecialList( $this->languageId );
		$this->viewData['special_goods_recommend_list'] = $this->_buildProductList( $list );

		//new arrival
		$this->viewData['new_goods_recommend_tab'] = $goodsModel->getNewGoodsRecommendTabTitle( $this->languageId );
		$list = $productModelObj->getRecommendGoodsNewList( $this->languageId );
		$this->viewData['new_goods_recommend_list'] = $this->_buildProductList( $list );
		
		$this->dataLayerPushImpressions($this->viewData['special_goods_recommend_list'] , $this->viewData['new_goods_recommend_list'] , 'Home Page' );

		//header category droplist
		$this->viewData['flg_header_category_droplist_disable'] = true;

//		//render page
	   $this->viewData['homePage'] = 'test view ';
	   return  $this->renderView('index');
	}
	
	//GA banner 
	protected function dataLayerPushBanners( $imagesBanner = array() ){
		if ( empty( $imagesBanner) ){
			$this->viewData['dataLayerBanners'] = '';
			return ;
		}
		
		$dataLayerBanners = array();
		$bannerIndexArr = array( 1, 2, 3 , 4 , 5, 6, 9 );				
		foreach ( $bannerIndexArr as $index ){
			if(isset($imagesBanner[ $index ] ) && $index != 2){
				foreach($imagesBanner[ $index ] as $key => $record){
					$dataLayerBanners[] =  array(
						'id' => stripslashes(  $record['image_ad_name'] ) . $key,
						'name' => stripslashes(   $record['image_alt'] )
					);
				}
			}elseif(isset($imagesBanner[ $index ] ) && $index == 2){
				$record = $imagesBanner[2][0];
				$dataLayerBanners[] =  array(
					'id' =>  stripslashes(  $record['image_ad_name'] ),
					'name' => stripslashes(   $record['image_alt'] )
				);
			}
		}

		//json_encode 会对 '\' 转义 所以不能 先addslashes 再json_encode 
		$dataLayerBannersJson = json_encode( $dataLayerBanners );
		if (strpos($dataLayerBannersJson, "\'" ) !== false ) {
		}elseif (strpos($dataLayerBannersJson, "'" ) !== false ) {
			$dataLayerBannersJson = str_replace("'","\'", $dataLayerBannersJson );
		}
		
		$this->viewData['dataLayerBanners'] = $dataLayerBannersJson;
	}
	
	/**
	* 相应页面刷新时，数据统计
	* @param type $specialGoodsRecommendList
	* @param type $newGoodsRecommendList
	* @param type $list
	* @param type $position
	* @return type
	*/
   protected function dataLayerPushImpressions( $specialGoodsRecommendList = array() , $newGoodsRecommendList = array() , $list = '' , $position = 1 ){
	   $products = array();
	   if ( empty ( $specialGoodsRecommendList ) &&  empty ( $newGoodsRecommendList ) ){
		   $this->viewData['dataLayerProducts'] = '';
		   return ;
	   }

	   foreach( $specialGoodsRecommendList as $model_id => $sku_list){
		   foreach($sku_list as $key => $sku){
			   $products[] = array(
				   'id' => $sku['id'],
				   'price'=> $sku['final_price'],  //美元价格
				   'list' => $list,
				   'position' => $position++
			   );
		   }
	   }
	   foreach($newGoodsRecommendList as $model_id => $sku_list){ 
		   foreach($sku_list as $key => $sku){
			   $products[] = array(
				   'id' => $sku['id'],
				   'price'=> $sku['final_price'],  //美元价格
				   'list' => $list,
				   'position' => $position++
			   );
		   }
	   }

	   $this->viewData['dataLayerProducts'] = json_encode( $products ); 
   }
	

	/**
	 * 获得商品信息
	 * @param array 商品数组
	 * @return array 返回包含价格数组
	 * @author lucas
	 */
	protected function _buildProductList( $productRecommendList ){
		$productListArray = array();
		if( count( $productRecommendList ) > 0 ) {
			foreach( $productRecommendList as $model_id => $pid_list ){
				$pids = extractColumn( $pid_list, 'id' );
				$productList = $productModelObj->getProInfoById( $pids, $this->languageId, 1 );
				foreach($productList as $key => $record){
					$record['name'] = $record['name'];//商品的名称
					$orig_desc = isset($record['content'])?strip_tags($record['content']):''; //商品描述
					$record['goods_desc'] = eb_substr($orig_desc,160); //商品简介
					$record['goods_img'] =  HelpUrl::img($record['image'] , 170 ); //分类页面商品的默认图
					$record['countdown_time'] = ''; //倒计时
					$record['flg_promote'] = false;
					//warehouse
					//$record['flgShowOrderTo'] = empty( $record['warehouse'] ) ? false : in_array( $record['warehouse'], AppConfig::$warehouse_oversea );
					//商品的价格处理
					$record['formatPrice'] = formatPrice($record['market_price']); //市场价格
					$record['formatShopPrice'] = formatPrice($record['price']); //默认销售价格
					$record['is_foreshow'] = false;
					$record['discount'] = $record['format_promote_discount'];//折扣
					//分类列表页面中1是折扣对应的数据数组的键值是1
					//32是秒杀对应的数据数组的键值是6
					//别的都是正常
					if( ($record['promote_type'] == 1 || $record['promote_type'] == 32 || $record['promote_type'] == 33) && count($record['promote_info']) > 0 ) {
						$record['flg_promote'] = true;
						$record['formatShopPrice'] = formatPrice($record['final_price']); //默认销售价格

						if($record['promote_type'] == 1) {
							//折扣
							$startTime = strtotime($record['promote_info'][1]['start_time']);
							$endTime = strtotime($record['promote_info'][1]['end_time']);
							if($endTime > HelpOther::requestTime() && $startTime <= HelpOther::requestTime()) {
								$countDownTime = $endTime - HelpOther::requestTime();
								//倒计时
								if(($countDownTime/86400) <= 7 ) { $record['countdown_time'] = $countDownTime; }
							}
						}
						if($record['promote_type'] == 32) {
							//false为秒杀  true为秒杀预告
							if($record['promote_info'][6]['is_foreshow'] == false) {
								$startTime = strtotime($record['promote_info'][6]['start_time']);
								$endTime = strtotime($record['promote_info'][6]['end_time']);
								if($endTime > HelpOther::requestTime() && $startTime <= HelpOther::requestTime()) {
									$record['countdown_time'] = $endTime - HelpOther::requestTime(); //倒计时
								}
							}
							$record['is_foreshow'] = $record['promote_info'][6]['is_foreshow'];
						}
						//秒杀预告折扣
						if($record['promote_type'] == 33) {
							//false为秒杀  true为秒杀预告
							if($record['promote_info'][6]['is_foreshow'] == false) {
								$startTime = strtotime($record['promote_info'][6]['start_time']);
								$endTime = strtotime($record['promote_info'][6]['end_time']);
								if($endTime > HelpOther::requestTime() && $startTime <= HelpOther::requestTime()) {
									$record['countdown_time'] = $endTime - HelpOther::requestTime(); //倒计时
								}
							}
							$record['is_foreshow'] = $record['promote_info'][6]['is_foreshow'];
						}
					}
					$productListArray[$model_id][$key] = $record;
				}
			}
		}

		return $productListArray;
	}

	/**
	 * 移动版登录检测
	 * @param  null
	 * @author lucas
	 */
	protected function _checkMobile() {
		$from = trim(  Yii::$app->request->get('from') );
		$fromSwitch = ( ( $from === 'mobile' ) ? TRUE : FALSE ) ;
		if( $this->_is_mobile_device( $fromSwitch ) ){
			HelpUrl::checkMobile($this->m_app->currentLanguageCode());
		}
	}
}
/* End of file home.php */
/* Location: ./application/controllers/default/home.php */


<?php

namespace app\controllers\pc;

use Yii;
use yii\filters\AccessControl;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\EntryForm;
use app\controllers\pc\common\EbController as BaseController;

class HomeController extends BaseController
{
    public function actionIndex(){  
		//移动版检测
//		$this->_checkMobile();
//		$this->languageId = $this->m_app->currentLanguageId();
//
//		//image ad
//		$this->load->model('Imageadmodel','m_imagead');
//		$this->_view_data['image_ad'] = $this->m_imagead->getImageAdList(array(1,2,3,4,5,6,7,9),$this->languageId);
//		$this->dataLayerPushBanners( $this->_view_data['image_ad']  );
//
//		//实例化类
//		$this->productModel = new ProductModel();
//		$goodsModel = new GoodsModel();
//		$reviewModel = new ReviewModel();
//		$list = $reviewModel->getRecentlyReviewList( $this->languageId );
//		$goodsIds = extractColumn($list,'product_id');
//		$goods = $this->productModel->getProInfoById( $goodsIds, $this->languageId );
//		foreach($list as $key => $record){
//			if(isset($goods[$record['product_id']])){
//				$record = array_merge($record,$goods[$record['product_id']]);
//				$record['goods_img'] = HelpUrl::img($record['image'] , 70 );
//				$record['goods_name_show'] = eb_substr($record['name'],18);
//				$list[$key] = $record;
//			}else{
//				unset($list[$key]);
//			}
//		}
//		$this->_view_data['recently_review_list'] = $list;
//
//
//		//category keyword recommend
//		$keywordrecommendModel = new KeywordrecommendModel();
//		$max_length = 64;
//		if($this->languageId == 7 ) {
//			$max_length = 100;
//		}
//		$list = $keywordrecommendModel->getCategoryKeywordRecommendList( $this->languageId );
//		$buffer = array();
//		foreach($list as $record){
//			$buffer[$record['mid']][$record['row_id']][$record['kid']] = array(
//				'keyword' => $record['keyword'],
//				'keyword_url' => $record['keyword_url'],
//				'checked' => $record['checked'],
//			);
//		}
//		$list = array();
//		foreach($buffer as $mid => $row_list){
//			if(!isset($row_list[0])) continue;
//			foreach($row_list as $row_id => $keyword_list){
//				if(!isset($keyword_list[0])) continue;
//				$row_length = 0;
//				foreach($keyword_list as $kid => $keyword){
//					$keyword_length = strlen($keyword['keyword']) + 1;
//					if($row_length + $keyword_length > $max_length) break;
//
//					$list[$mid][$row_id][] = $keyword;
//					$row_length += $keyword_length;
//				}
//			}
//		}
//		$this->_view_data['category_keyword_recommend_list'] = $list;
//
//		//special deals
//		$this->_view_data['special_goods_recommend_tab'] = $goodsModel->getSpecialGoodsRecommendTabTitle( $this->languageId );
//		//获得special推荐产品列表
//		$list = $this->productModel->getRecommendGoodsSpecialList( $this->languageId );
//		$this->_view_data['special_goods_recommend_list'] = $this->_buildProductList( $list );
//
//		//new arrival
//		$this->_view_data['new_goods_recommend_tab'] = $goodsModel->getNewGoodsRecommendTabTitle( $this->languageId );
//		$list = $this->productModel->getRecommendGoodsNewList( $this->languageId );
//		$this->_view_data['new_goods_recommend_list'] = $this->_buildProductList( $list );
//		
//		$this->dataLayerPushImpressions($this->_view_data['special_goods_recommend_list'] , $this->_view_data['new_goods_recommend_list'] , 'Home Page' );
//
//		//header category droplist
//		$this->_view_data['flg_header_category_droplist_disable'] = true;
//
//		//render page
//		parent::index();
	}
}

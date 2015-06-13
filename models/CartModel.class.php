<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 购物车
 */
class CartModel extends CI_Model {

	const CART_GOODS_TYPE_NORMAL = 0;
	const CART_GOODS_TYPE_GIFT = 55555 ;

	const PROMOTE_ACT_TYPE_PRICE_REDUCE = 1; //现金减免
	const PROMOTE_ACT_TYPE_PRICE_DISCOUNT = 2;//现金折扣
	const PROMOTE_ACT_TYPE_FULL_REDUCE = 3; //满额减免
	const PROMOTE_ACT_TYPE_FULL_DISCOUNT = 4;//满额折扣
	const PROMOTE_ACT_TYPE_PRICE_GIFT = 5; //赠品
	const INSURANCE_FEE = 1.99; //保险的价格
	const SHIPPING_CNEMAIL = 6; //中国物流

	/**
	 * 获取购物车缓存KEY
	 * @var mix
	 * @return string
	 */
	const MEM_KEY_CART_UID_INFO = 'cart_uid_%s';//cart_uid_%s 获取购物车商品的缓存KEY  根据uid
	const MEM_KEY_CART_SESSION_INFO = 'cart_session_%s';//cart_session_%s 获取购物车商品的缓存KEY  根据session

	//购物车
	protected $_goodsList = array(); //商品列表
	protected $_subtotalPrice = 0; //总共金额
	protected $_subtotalMarketPrice = 0; //总共金额
	protected $_totalPriceCart = 0; //购物车支付总价
	protected $_totalPrice = 0; //支付总价
	protected $_totalIntegral = 0; //积分总价
	protected $_flgCanCheckout = true; //是否可以生单
	protected $_cartMessage = false; //购物车信息
	protected $_goodsNum = 0; //购物车商品数量

	//积分
	protected $_useIntegral = 0; //积分
	protected $_integralMessage = false; //积分信息

	//折扣
	protected $_discountList = array(); //折扣列表
	protected $_discountDesc = ''; //折扣信息
	protected $_discountAmount = 0; //折扣金额
	protected $_couponDiscountAmount = 0; //coupon折扣金额  本位币美元
	protected $_useGroupDiscountAmount = 0; //用户组折扣金额  本位币美元
	protected $_useLevelDiscountAmount = 0; //用户积分等级折扣金额  本位币美元
	protected $_gift = false; //是否是赠品

	//环境信息
	protected $_user = array(); //用户信息
	protected $_sessionId = ''; //用户登录session id
	protected $_languageId = 1; //语言id
	protected $_languageCode = DEFAULT_LANGUAGE; //默认语言的code

	//物流
	protected $_addressId = 0; //地址id
	protected $_shippingCountry = false; //国家
	protected $_shippingCity = false; //城市
	protected $_flgEmptyProvince = false; //标记省份
	protected $_shippingList = array();
	protected $_shippingMessage = false;
	protected $_selectedShippingId = 0; //选择的物流id
	protected $_flgInsurance = false; //是否有保险
	protected $_flgSeparatePackage = false; //是否拆包
	protected $_flgSeparatePackageDisabled = false; //标记拆包状态
	protected $_isCnMail = false;
	protected $_shippingPrice = 0;

	//支付
	protected $_paymentCountry = false; //支付的国家
	protected $_paymentList = array(); //支付信息列表
	protected $_paymentId = 0; //支付id

	//是否可以生单
	protected $_flgIgnoreLoginCheck = false; //忽略登录状态
	protected $_flgIgnorePaymentCheck = false;
	protected $_flgCanPlaceorder = true;

	//礼物判断循环标示
	protected $_flgEnterLoop = TRUE; //礼物判断循环标示

	//购物车有没有进行合并操作表示
	protected $_markMergeCart = FALSE;

	//初始化对象
	protected $_objUserModel ;
	protected $_objCouponModel ;
	/**
	 * 加载购物车底层的时候初始化的方法
	 */
	public function __construct() {

		parent::__construct();

		//初始化models
		$this->load->model('Appmodel','m_app');
		$this->load->model('Addressmodel','m_address');

		$this->_objUserModel = new UserModel();
		$this->_objProductModel = new ProductModel();
		$this->_objCouponModel = CouponModel::getInstanceObj();
		$this->_objPointModel = PointModel::getInstanceObj();

		//初始化环境信息
		$this->_initEnvironmentInfo();

		//初始化用户积分
		$this->_loadSessionIntegralInfo();

		//coupon 页面刷新时用到
		$this->_loadSessionCouponInfo();

		//加载地址信息
		$this->_loadAddressInfo();

		//物流
		$this->load->module('shipping');
		$this->_loadSessionShippingInfo(); //初始化物流信息
		$this->_loadSessionInsuranceInfo(); //初始化保险
		$this->_loadSessionSeparatePackageInfo(); //初始化拆包信息
		$this->_loadSessionPaymentInfo(); //支付信息
		//支付
		$this->load->module('payment');
	}

	/**
	 * 获取实例化
	 * @return CartModel
	 */
	public static function & getInstanceObj( ){
		return parent::_getBaseInstanceObj( __CLASS__ );
	}

	/**
	 * 加入购物车
	 * @param array $goodsList 一组需要加入购物车的商品数据
	 */
	public function addToCart( $goodsList = array() ) {
		if(empty($goodsList)) {
			return false;
		}

		/*取出购物车中已经有的商品。*/
		$userId = isset($this->_user['user_id']) && !empty($this->_user['user_id']) ? (int) ( $this->_user['user_id'] ) : 0; //定义userid
		$list = $this->loadCartProduct();

		/*将当前用户购物车中现有的商品以约定的拼接规则为key，购物车记录数据为值，构造成一个数组，方便后面判断加入购物车时是更新数量还是插入新记录。*/
		$cartList = $secKillGoods = array();
		if(!empty($list)) {
			foreach ($list as $key => $value) {
				$key = $userId. '_' . $this->_sessionId. '_' .$value['product_id']. '_' .trim( $value['sku'] ). '_' .$value['pro_type']. '_' .$value['pro_id']. '_' .$value['binding_pid'];
				$cartList[$key] = $value;
				if( (int)$value['pro_type'] === 32 ){
					if( isset( $secKillGoods[ (int) $value['product_id'] ] ) ){
						$secKillGoods[ (int) $value['product_id'] ] += (int)$value['goods_number'];
					}else{
						$secKillGoods[ (int) $value['product_id'] ] = (int)$value['goods_number'];
					}
				}
			}
		}

		/*遍历要加入购物车的商品，加入(更新)到购物车.*/
		$SecondKillExpireCartIds = array();//秒杀支付时间过期的记录id数组
		foreach ($goodsList as $key => $value) {
			//秒杀预感的商品插入购物车数据库中 把预感的规则ID减去
			if( ( (int) $value['finalPromoteType'] >=32 ) && ( (int) $value['finalPromoteType'] <=40 ) && ( (int)$value['seckillIsForeshow'] !== 0  ) ) {
				//促销ID 减去秒杀预感的促销ID
				$value['finalPromoteType'] = ( (int) $value['finalPromoteType'] - 32 ) ;
				//如果是秒杀预感的商品 那么规则ID 变成0;
				if( ((int) $value['finalPromoteType'] === 32 ) && (int)$value['seckillIsForeshow'] === 1 ){
					$value['finalPromoteId'] = 0 ;
				}
			}

			/*检查同一条促销规则的商品是否存在，存在则更新商品的数量，不存在则插入新记录.*/
			$keySource = $userId. '_'.$this->_sessionId . '_' . $value['pid'] . '_' .trim( $value['sku'] ). '_'.$value['finalPromoteType'].'_'.$value['finalPromoteId'] . '_' .$value['finalBindingPid'];

			//每单秒杀个数处理，因为复合商品  秒杀每单限制到sku 因为 复合商品的秒杀单独处理
			if( ( (int)$value['finalPromoteType'] === 32 ) &&  ( (int)$value['productType'] === 2 ) && isset( $secKillGoods[ (int)$value['pid'] ] ) ){
				//剩余秒杀个数
				$secKillGoodsTmpQty = (int)( $value['targetOverplusLimitOrder'] - $secKillGoods[ (int)$value['pid'] ] );
				$value['qty'] = min( (int)$value['qty'] , $secKillGoodsTmpQty );
				if( $value['qty'] <= 0 ){
					continue;
				}
			}

			if(isset($cartList[$keySource])) {//--------------------如果购物车中已有该商品，则更新购物车中相应记录的数量。

				$existedCatData = $cartList[$keySource];

				/* 对秒杀过期的商品做特别处理，1、过期的话将被加入支付时间过期的记录id数组，循环结束后将从购物车中删除；2、退还秒杀商品使用数量 暂时关闭 */
				if (( $existedCatData['end_time'] > 0 ) && ( $existedCatData['end_time'] <= HelpOther::requestTime() )) {
					$SecondKillExpireCartIds[(int) $existedCatData['id']] = (int) $existedCatData['id'];
					//PromoteModel::getInstanceObj()->updateSecKillInfo($value['finalPromoteId'], $value['pid'], ( (int) $value['purchasedNumber'] - $existedCatData['goods_number']));
					continue;
				}

				/*购物车中相应记录需要更新的商品数量。 复合商品 秒杀时候  是商品个数的总和为此秒杀商品 */
				$goodsNumberUpdate = min((int)$value['qty'] + $existedCatData['goods_number'],65535);

				/*如果是秒杀商品：1、更新数量时要考虑是否超过秒杀数量限制；2、如果秒杀商品数量有变更，则更新秒杀商品已使用数量*/
				if ( (int) $value['seckillIsForeshow'] === 0 ) {
					//每单限制和购买个数比较那个小
					$goodsNumberUpdate = min($goodsNumberUpdate, $value['targetLimitOrder']);
					$newAddNum = $goodsNumberUpdate - $existedCatData['goods_number'];
					if ($newAddNum !== 0) {
						PromoteModel::getInstanceObj()->updateSecKillInfo($value['finalPromoteId'], $value['pid'], ( (int) $value['purchasedNumber'] + $newAddNum));
					}
				}

				/*更新购物车中记录的数量(更新数量和原本数量不一致时才更新)*/
				if ($goodsNumberUpdate != $existedCatData['goods_number']) {
					$this->updateCart($existedCatData['id'], array(
						'goods_number' => $goodsNumberUpdate,
						'update_time' => HelpOther::requestTime(),
					));
				}
			} else {//--------------------------------------------如果购物车中没有该商品，则直接新增插入购物车。
				$this->insertToCart($value);
				if ((int) $value['seckillIsForeshow'] === 0) {
					PromoteModel::getInstanceObj()->updateSecKillInfo($value['finalPromoteId'], $value['pid'], ( (int) $value['purchasedNumber'] + (int)$value['qty']));
				}
			}
		}

		/*从数据库中把购物车中过期的秒杀记录删除掉。*/
		if( !empty( $SecondKillExpireCartIds ) ){
			$this->deleteCartById( $SecondKillExpireCartIds );
		}

		return true;
	}

	public function getTopCartData(){

		$return = array();
		foreach($this->_goodsList as $v){
			$topCartItem = array();
			$topCartItem['goodsName'] = $v['goodsName'];
			$topCartItem['url'] = $v['url'];
			$topCartItem['image45'] = HelpUrl::img($v['skuInfo'][$v['sku']]['image'],45);
			$topCartItem['qty'] = $v['qty'];
			$topCartItem['finalPriceFormat'] = formatPrice($v['finalPrice']);
			$return[] = $topCartItem;
		}
		return $return;
	}

	/**
	 * 获取购物车信息 by terry。
	 */
	public function getCartData(){

		$goodsListCommon = array();
		$goodsListBind = array();
		foreach($this->_goodsList as $v){
			$pid = (int)$v['pid'];
			$bindPid = (int)$v['finalBindingPid'];

			/*格式化仓位文本，提示语*/
			$v['warehouseText'] = lang('warehouse_'.strtolower($v['warehouse']));
			$v['warehouseOnlyShipText'] = lang('only_ships_to_'.$v['warehouse']);
			$v['warehouse'] = strtolower($v['warehouse']);

			/*处理经销商品库存。*/
			$v['stock'] = ( $v['productSkuType']==2 && !$v['favorable_gift_id']) ? 65535 : $v['stock'];
			$v['stockMsg'] = sprintf(lang('itemsAllowed'),$v['stock']);

			/*添加一些格式化价格*/
			$v['finalPriceFormat'] = formatPrice($v['finalPrice']);
			//判断是不是促销品
			$v['flg_promote_active'] = (int)$v['finalPromoteType'] ;
			$recId  =  (int)$v['rec_id'] ;
			//捆绑主商品  处理
			if( $bindPid===0 ){
				//判断是否是秒杀商品 秒杀商品的话  key 为 pid_res_id
				if( (int)$v['finalPromoteType'] === 32 || $v['favorable_gift_id'] ){
					$goodsListCommon[ $pid . '_' . $recId ][  $recId ] = $v;
				}else{
					$goodsListCommon[ $pid ][  $recId ] = $v;
				}
			}else{
				$goodsListBind[$bindPid][ $recId ] = $v;
			}
		}
		//处理捆绑商品
		if( !empty( $goodsListBind ) ){
			foreach($goodsListBind as $bindPid=>$bindBlock){
				$bindMainProInfo = $goodsListCommon[$bindPid];
				$goodsListBind[$bindPid] = array_merge( $bindMainProInfo , $bindBlock );
				unset($goodsListCommon[$bindPid]);
			}
		}
		$goodsListCommonResult = array();
		//处理正常商品三维数组变成二维数组
		if( !empty( $goodsListCommon ) ){
			foreach ( $goodsListCommon as $v ){
				foreach ( $v as  $k => $infoTmp ){
					//判断是不是促销品 前台Promotion 小图标是否显示
					if( in_array( $infoTmp['finalPromoteType'] , array( 4 , 36 ) ) || ( (int)$infoTmp['seckillIsForeshow'] === 1 ) ){
						$infoTmp['flg_promote_active'] = 0;
					}
					$goodsListCommonResult[ $k ] = $infoTmp ;
				}
			}
		}
		$integDiscountFormat = formatPrice($this->_calculatePointPrice($this->_useIntegral));
		if($this->_useIntegral){
			$this->_integralMessage = sprintf(lang('pointUsedReminder'),$this->_useIntegral,$integDiscountFormat);
		}

		$markMergeCart = $this->session->get('markMergeCart');
		if($markMergeCart !== false) {
			$this->session->delete( 'markMergeCart' );
		}


		return array(
			'totalSavingsFormat' => formatPrice($this->_subtotalMarketPrice-$this->_subtotalPrice),
			'subtotalFormat' => formatPrice($this->_subtotalPrice),
			// 'totalIntegral' => round($this->_subtotalPrice),
			'totalIntegral' => $this->_objPointModel->getCartPoint( $this->_subtotalPrice, $this->_languageId, $this->m_app->currentCurrency() ),
			'useIntegralMax' => floor($this->_subtotalPrice*20),
			'integralMessage' => $this->_integralMessage,
			'useIntegral' => $this->_useIntegral,
			'useIntegralPriceFormat' => $integDiscountFormat,
			'couponCode' => $this->_objCouponModel->getCouponCode(),
			'couponCodeErrorMsg' => $this->_objCouponModel->getCouponMessage(),
			'couponCode_Message' => $this->_getCouponSuccessMsg(),
			'discount_amount' => $this->_discountAmount, //折扣金额数值
			'discount_amount_price' => formatPrice($this->_discountAmount), //折扣金额货币
			'discount_desc' => $this->_discountDesc,
			'total_price' => formatPrice($this->_totalPriceCart),
			'goodsListCommon' => !empty($goodsListCommonResult)?array_values($goodsListCommonResult):null,
			'goodsListBind' => !empty($goodsListBind)?array_values($goodsListBind):null,
			'goodsListBindArr' => $goodsListBind,
			'cartMergeMark' => $markMergeCart,
			'goodsNum' => $this->_goodsNum,
		);
	}

	/**
	 * 获取coupon应用成功后的提示信息。
	 * @return string
	 * @author Terry
	 */
	private function _getCouponSuccessMsg(){

		$return = '';
		if($this->_objCouponModel->getCouponCode()){
			$return = sprintf(lang('couponReminder'), $this->_objCouponModel->getCouponCode(), formatPrice($this->_couponDiscountAmount));
			if(!$this->_couponDiscountAmount){
				$return = substr($return,0,strpos($return,",")).'.';
			}
		}

		return $return;
	}

	/**
	 * 更改商品数量
	 * @todo 废弃
	 */
	public function updateQty($recId,$qty = 1, $sku) {
		return  array();

		//购物车数据id
		$recId = intval($recId);
		//增加的数量
		$qty = intval($qty);

		//取出购物车指定数据
		$row = $this->getCartRow($recId);
		if(empty($row)) { return false; }

		//取出商品的信息
		$this->ProductModel = new ProductModel();
		$goods = $this->ProductModel->getProInfoById($row['product_id']);
		if(empty($goods)) { return false; }
		$goods = current($goods);

		//库存判断
		if(empty($goods) || $goods['status'] == 0 || empty($goods['skuInfo'][$sku]) || $goods['skuInfo'][$sku]['status'] == 0) { return false; }
		$qty = min($qty,$goods['skuInfo'][$sku]['stock']);

		//判断如果是赠品加入购物车的数量按照赠品的限制数量
		if($row['pro_type'] == self::CART_GOODS_TYPE_GIFT) {
			$gift = $this->getGiftById( (int)$row['favorable_gift_id'] );
			if(!empty($gift)) { $gift = current($gift); }
			$qty = min($qty,id2name('gift_num_limit',$gift,0));
		}

		//更具用户登录和未登录的状态更新
		if(empty($this->_user)) {
			if($row['session_id'] == $this->_sessionId) {
				if($qty > 0) {
					$this->updateCart($recId,array('goods_number'=>$qty));
				} else {
					$this->deleteCartById($recId);
				}
			}
		} else {
			if($row['user_id'] == $this->_user['user_id']){
				if($qty > 0){
					$this->updateCart($recId,array('goods_number'=>$qty));
				}else{
					$this->deleteCartById($recId);
				}
			}
		}

		$this->session->delete('user_cart');
		return true;
	}

	/**
	 * 检查加入购物车的商品的库存和上下架的状态（根据商品的sku和id决定）
	 * @param  array $prodcutSku 加入购入车的商品
	 * @param  integer $languageId 语言id
	 * @param int $type  默认是2 目前只支持 [1/2/3]  1则获取 非捆绑的促销ID   2 获取所有促销规则。3获取非秒杀的促销规则， 4获取非秒杀，非捆绑的促销规则
	 * @author  qcn
	 * @return array
	 */
	public function checkStockAndStatus($prodcutSku = array(), $languageId = 1 , $type = 2 ) {
		//校验商品的信息（上下架状态，库存，购买的数量，商品是否违法）
		$this->ProductModel = new ProductModel();
		$productInfo = $this->ProductModel->getLatestPromotionByInfo( $prodcutSku , $languageId , $type );

		//循环判断排除不合法的商品
		$expireCartIds = array();
		if(!empty($productInfo['data'])) {
			foreach ($productInfo['data'] as $key => $value) {
				if( ( (int)$value['status'] !== 1 ) ) {
					unset($productInfo['data'][$key]);
					$recIdTmp = isset( $value['rec_id'] )? (int)$value['rec_id'] : 0;
					//删除购物车中的状态过期的数据
					if( $recIdTmp > 0 ){
						$expireCartIds[ $recIdTmp ] = $recIdTmp ;
					}
				}
			}
		}
		//删除购物车中的状态过期的数据
		if( !empty( $expireCartIds ) ){
			$this->deleteCartById( $expireCartIds );
		}

		//返回商品的信息
		return $productInfo['data'];
	}

	/**
	 * 计算购物车信息
	 */
	public function loadCart( $isGetPaymentList = FALSE , $isPaypayEC = FALSE ) {
		$this->_resetCartParams();//初始化重置购物车信息
		$languageId = $this->m_app->currentLanguageId();//获取当前语言id
		$cartProductInfo = $this->loadCartProduct();//获取购物车中的商品
		$nowTime = HelpOther::requestTime();//获取现在的时间戳(用于判断购物车秒杀支付是否过期)
		$SecondKillCartInfos = array();//用于存储秒杀的购物车记录信息
		$SecondKillExpireCartIds = array();//用于存储购物车中支付时间过期的秒杀记录id.

		/*遍历购物车列表：过滤掉过期的秒杀数据，同时将正常的秒杀数据收集到指定数组。*/
		foreach ($cartProductInfo as $k => $v) {
			if (( $v['end_time'] > 0 ) && ( $v['end_time'] <= $nowTime )) {
				unset($cartProductInfo[$k]);
				$SecondKillExpireCartIds[] = $v['id'];
			} else if ($v['end_time'] > 0) {
				$SecondKillCartInfos[(int) $v['id']] = $v;
			}
		}

		/*重新处理购物车列表数据，加入商品对应的复合属性信息。*/
		$productSkuPidArray = $this->_formatSkuPid($cartProductInfo, $languageId);

		//检测商品的库存和上下加状态促销信息
		$productInfoArray = $this->checkStockAndStatus($productSkuPidArray, $languageId, 3);

		//秒杀商品的价格 以购物车为准 以及相同促销类型的合并
		$mergerPromotionKeys = array();
		$isUpdateCart = FALSE ;
		$updateCartArr = array();
		//判断购物车的秒杀商品是否已经超过支付时间
		foreach ( $productInfoArray as $k => $v ){
			$tmprecId =  (int)$v['rec_id'] ;
			//判断购买的库存为空的商品自动删除
			if( empty($v['qty']) && $v['productSkuType'] != 2) {
				//unset( $productInfoArray[ $k ] );
				$SecondKillExpireCartIds[ $tmprecId ] = $tmprecId ;
			}

			$SecondKillfinalPromoteType = bindec( HelpOther::occupied( PromoteModel::PROMOTE_TYPE_SEC_KILL ) );
			//判断是否是秒杀商品  秒杀商品单独处理
			if( isset( $SecondKillCartInfos[ $tmprecId ] ) ){
				//获取秒杀商品是否还存在 以及倒计时是等
				$proSecKillInfo = PromoteModel::getInstanceObj()->getProSecKillInfoByIds( array( $v['pid'] ) ) ;
				//判断秒杀商品是否存在
				if( isset( $proSecKillInfo[ (int)$v['pid'] ] ) && ( (int)$proSecKillInfo[ (int)$v['pid'] ]['id'] === (int)$SecondKillCartInfos[ $tmprecId ]['pro_id'] ) ){
					//格式化 时间戳 秒杀的开始时间和结束时间
					$secKillTmpStartTime = strtotime( $proSecKillInfo[ (int)$v['pid'] ]['start_time'] );
					$secKillTmpEndTime = strtotime( $proSecKillInfo[ (int)$v['pid'] ]['end_time'] );
					//判断秒杀的时间是否在进行中
					if( ( $secKillTmpStartTime < $nowTime ) && ( $nowTime<= $secKillTmpEndTime ) ){
						//修改促销类型秒杀的
						$productInfoArray[ $k ][ 'finalPromoteType' ] = $v['finalPromoteType'] =  $SecondKillfinalPromoteType ;
						//修改促销ID
						$productInfoArray[ $k ][ 'finalPromoteId' ] = $v['finalPromoteId'] = (int)$SecondKillCartInfos[ $tmprecId ]['pro_id'];
						//修改被捆绑ID为0
						$productInfoArray[ $k ][ 'finalBindingPid' ] = $v[ 'finalBindingPid' ] = 0 ;
						//最终销售价 最终销售价 重新计算
						$productInfoArray[ $k ][ 'finalPrice' ] = $v[ 'finalPrice' ] = round( ( ( $v ['finalmarketPrice'] ) * ( 1 - ( $proSecKillInfo[ (int)$v['pid'] ]['target_discount'] / 100 ) ) ) , 2 ) ;
						//支付剩余时间  秒数  支付剩余时间 与现状的时间 重新计算
						$secKillExpireTimeTmp = ( (int)$SecondKillCartInfos[ $tmprecId ]['end_time'] > $secKillTmpEndTime ? $secKillTmpEndTime :(int)$SecondKillCartInfos[ $tmprecId ]['end_time'] );
						$expireTime = $secKillExpireTimeTmp - $nowTime ;
						$productInfoArray[$k]['countdownSeconds'] = $expireTime ;
						//格式化支付剩余时间
						$productInfoArray[$k]['countdownTime'] = format_seconds_to_clocktime( $expireTime ) ;
					}
				}
			}

			if( count( $productInfoArray ) > 1 ){
				//其他商品合并促销规则一样的商品信息   by BRYAN
				//记录KEY
 				$key = (int)$v[ 'pid' ]  .'_' . trim( $v['sku'] ) .'_' . (int)$productInfoArray[ $k ][ 'finalPromoteType' ]  .'_' . (int)$productInfoArray[ $k ][ 'finalPromoteId' ] .'_' . (int)$productInfoArray[ $k ][ 'finalBindingPid' ] ;
 				//判断促销是否存在
 				if( isset( $mergerPromotionKeys[ $key ] ) ){
 					$isUpdateCart = TRUE;
					$goodNumberTmp = min( ( (int)$mergerPromotionKeys[ $key ]['good_number']+(int)$v['qty'] ) , (int)$v['stock'] );
 					//新添加的将会被删除
					$delTmprecId = (int)$mergerPromotionKeys[ $key ]['rec_id'] ;
					$productInfoArray[ $tmprecId ][ 'qty' ] = $v['qty'] = $goodNumberTmp ;
					//合并购买的个数 更新购物车中的促销
					$updateCartArr[ $tmprecId ] = array(
							'id' => $tmprecId ,
							'goods_number' => $goodNumberTmp ,
					);
					//删除两个数据中一个数据
					$SecondKillExpireCartIds[ $delTmprecId ] = $delTmprecId ;
					unset( $productInfoArray[ $delTmprecId ] );
					//判断数据中有更新 就删除此信息
					if( isset( $updateCartArr[ $delTmprecId ] ) ){
						unset( $updateCartArr[ $delTmprecId ] ) ;
					}
 				}else{
	 				$mergerPromotionKeys[ $key ] = array(
 						'rec_id' => $tmprecId ,
 						'good_number' => (int)$v['qty'] ,
	 				);
 				}
			}

			//促销ID 有变化就更新数据信息
			if( ( (int)$v['finalPromoteType'] !== (int)$cartProductInfo[ $tmprecId ][ 'pro_type' ] ) || ( (int)$v['finalPromoteId'] !== (int)$cartProductInfo[ $tmprecId ][ 'pro_id' ] ) || ( (int)$v['finalBindingPid'] !== (int)$cartProductInfo[ $tmprecId ][ 'binding_pid' ] ) ){
				$isUpdateCart = TRUE;
				$updateCartArr[ $tmprecId ]['id'] =  (int)$tmprecId ;
				$updateCartArr[ $tmprecId ]['pro_type'] =  (int)$v['finalPromoteType'] ;
				$updateCartArr[ $tmprecId ]['pro_id'] = (int)$v['finalPromoteId'];
				$updateCartArr[ $tmprecId ]['binding_pid'] = (int)$v['finalBindingPid'];
				$updateCartArr[ $tmprecId ]['final_price'] = $v['finalPrice'];
				$updateCartArr[ $tmprecId ]['goods_number'] = $v['qty'];
			}
		}
		if( $isUpdateCart ){
			//更新购物车最新的促销类型和ID
			$this->updateBatchCart( $updateCartArr );
		}
		unset( $mergerPromotionKeys );
		//从数据库中把过期的秒杀记录删除掉
		if( !empty( $SecondKillExpireCartIds ) ){
			$this->deleteCartById( $SecondKillExpireCartIds );
		}

		$this->_goodsList = $productInfoArray ;

		//@todo礼物信息  数据库中cart中字段名是 favorable_gift_id 到时应该改成 xxx
		$giftId = 0 ;
		//取出购物车中已经有的商品
		$userIdCart = isset($this->_user['user_id']) && !empty($this->_user['user_id']) ? (int) ( $this->_user['user_id'] ):0; //定义userid

		foreach ($cartProductInfo as $key => $value) {
			$keySourceCart = (int)$userIdCart. '_' . $this->_sessionId. '_' .(int)$value['product_id']. '_' .trim( $value['sku'] ). '_' .(int)$value['pro_type']. '_' .(int)$value['pro_id']. '_' .(int)$value['binding_pid'];
			$cartProductInfo[$keySourceCart] = $value;
			unset($cartProductInfo[$key]);
			//获取赠品的giftID
			if( (int)$value['favorable_gift_id'] > 0 ){
				$giftId = (int)$value['favorable_gift_id'] ;
			}
		}
		$skuCartInfo = $cartProductInfo;
		$giftList = $this->getGiftById( $giftId  );

		//结构化商品信息数组和计算相关价格信息
		$appliedGift = array(); //应用礼品数组
		$existedGiftRid = array();
		$existedGiftId = array();
		$goodsWeightTotal = 0; //商品总的重量
		$goodsWeightMax = 0; //商品的最大重量
		$volumeWeightTotal = 0; //总的体积
		$maxSumlwh = 0; //最大长宽高
		$maxLength = 0; //最大长
		$flgBattery = false; //是否是电池
		$flgSensitive = false; //是否是敏感品
		$warehouseArr = array(); //海外仓库
		$totalNumber = 0; //总的数量

		//循环处理商品信息
		foreach ($this->_goodsList as $key => $record) {

			/* 宁彦栋 去掉这个代码 重复获取  优化代码 处理  因为    $this->_goodsList  这个里面已经获取有了 不需要重复获取
			//合并商品的详细信息
			$record += $prosInfo[$record['pid']];
			*/
			$record['favorable_gift_id'] = 0;
			if($record['promoteType'] == self::CART_GOODS_TYPE_GIFT) {
				$keyGiftSource = (int)$userIdCart. '_' . $this->_sessionId. '_' .(int)$record['pid']. '_' .trim( $record['sku'] ). '_' .(int)$record['promoteType']. '_' .(int)$record['promoteId']. '_' .(int)$record['bindingPid'];
				if(isset($skuCartInfo[$keyGiftSource]['favorable_gift_id']) && !empty($skuCartInfo[$keyGiftSource]['favorable_gift_id'])) {
					$record['favorable_gift_id'] = $skuCartInfo[$keyGiftSource]['favorable_gift_id'];
					if(isset($giftList[$record['favorable_gift_id']])) {
						$record['finalPrice'] = $giftList[$record['favorable_gift_id']]['price'];
					}
					// 如果是买赠的赠品 库存安赠品的走
					$giftStock = 0;
					if(isset($giftList[$record['favorable_gift_id']]['limit']) && !empty($giftList[$record['favorable_gift_id']]['limit'])) {
						$giftStock = $giftList[$record['favorable_gift_id']]['limit'];
						//如果礼物数量大于总限制 那么 购买个数修改
						if($record['qty'] > $giftStock) {
							$record['qty'] = $giftStock;
						}
					}
					// 判断是不是经销品 1是普通商品 2是经销商品
					if( $giftStock < $record['stock'] ) {
						$record['stock'] = $giftStock;
						if(isset($record['skuInfo']) && !empty($record['skuInfo'])) {
							$record['skuInfo'][$record['sku']]['stock'] = $giftStock;
						}
					}
				}
			}

			//商品的基本信息处理
			$this->_goodsList[$key]['url'] = eb_gen_url($record['url']); //商品访问url
			$this->_goodsList[$key]['image'] = HelpUrl::img($record['image'] , 350 ); //分类页面商品的默认图
			$this->_goodsList[$key]['categoryId'] = $record['categoryId']; //商品所属分类
			$flgShowOrderTo = in_array($record['warehouse'], AppConfig::$warehouse_oversea);
			$this->_goodsList[$key]['flg_show_order_to'] = $flgShowOrderTo;

			//判断商品的促销类型是赠品但是在赠品的列表中没有这个商品就将这个商品释放
			if($record['promoteType'] == self::CART_GOODS_TYPE_GIFT && !isset($giftList[$record['favorable_gift_id']])) {
				unset($this->_goodsList[$key]);
				continue;
			}

			//商品的库存检测
			// if($record['promoteType'] == self::CART_GOODS_TYPE_GIFT) {
			// 	$limit = isset($giftList[$record['favorable_gift_id']])?$giftList[$record['favorable_gift_id']]['gift_num_limit']:1;
			// 	$this->_addGoodsStockInfo($record,min($record['qty'],$limit));
			// } else {
			// 	$this->_addGoodsStockInfo($record, $record['qty']);
			// }
			// 如果优惠活动中优惠方案中促销品数量设置了则判断购买数量是否大于促销数量 @todo ??
			//if($record['promoteType'] == self::CART_GOODS_TYPE_GIFT) {
			//	$giftNumLimit = (int)$giftList[$record['favorable_gift_id']]['limit'];
			//	if(!empty($giftNumLimit)) {
			//		if($record['qty'] > $giftNumLimit) { $record['qty'] = $giftNumLimit; }
			//	}
			//}

			if($record['favorable_gift_id']){
				$appliedGift[] = $record;
				$existedGiftRid[] = $record['rec_id'];
				$existedGiftId[] = $record['favorable_gift_id'];
			}

			//价格计算
			$record = $this->_calculateGoodsPrice($record);

			//判断是不是赠品
			$record['flg_free'] = $record['finalPrice']!=0?FALSE:TRUE;
			//判断符合属性
			$record['attribute_list'] = array();

			if(!empty($record['complexattr']) && isset($record['complexattr'][$record['sku']]) && is_array($record['complexattr'][$record['sku']]) &&count($record['skuInfo'])>=1 && ((int)$record['productType']   === 2 ) ) {
				//属性列表
				$record['attribute_list'] = array();
				foreach($record['complexattr'][$record['sku']] as $attrId=>$attrVal){
					$attrTitle = current($this->_objProductModel->getComplexAttrTitle($attrId, array($this->m_app->currentLanguageId())));
					$record['attribute_list'][] = array('attrTitle'=>$attrTitle,'attrValTitle'=>$attrVal['attrValTitle']);
				}
			}

			//海外仓
			$record['flg_show_order_to'] = in_array($record['warehouse'], AppConfig::$warehouse_oversea)?TRUE:FALSE;//如果是海外仓，那么显示配送的限制说明。
			unset($record['content']);

			//秒杀限时支付倒计时。

			$this->_goodsList[$key] = $record;

			//销售总价
			$this->_subtotalPrice += $record['priceSubtotal'];

			//商品的总市场价
			$this->_subtotalMarketPrice += $record['marketPriceSubtotal'];

			//总的商品数量
			$totalNumber += $record['qty'];

			if(!$flgShowOrderTo) {
				//商品最大重量
				$goodsWeightMax = max($goodsWeightMax,$record['weight']);
				//商品所有重量
				$goodsWeightTotal += $record['weight'] * $record['qty'];

				//商品长度
				$length = $record['length'] * 100;
				//商品宽度
				$width = $record['width'] * 100;
				//商品的高度
				$height = $record['height'] * 100;

				//最大的长宽高
				$maxSumlwh = max($maxSumlwh,($length + $width + $height));
				//最大的宽度
				$maxLength = max($maxLength,$length);
				$maxLength = max($maxLength,$width);
				$maxLength = max($maxLength,$height);
				//总的体积重量
				$volumeWeightTotal += ($length * $width * $height) / 5000 * $record['qty'];

				//商品的敏感品信息
				if($record['typeSensitive'] == 1 || $record['typeSensitive'] == 5) {
					$flgBattery = true; //商品是电池
				} elseif($record['typeSensitive'] > 0) {
					$flgSensitive = true; //商品是敏感品信息
				}
			}

			$warehouseArr[] = $record['warehouse'];
		}
		$this->_totalPrice = $this->_subtotalPrice; //商品的总价
		$this->_goodsNum = $totalNumber; //商品的总价

		/*折扣信息*/
		//用户组折扣
		$this->_addGroupDiscountIntoDiscountList();
		//用户积分等级折扣
		$this->_addLevelDiscountIntoDiscountList();
		//用户使用coupon相关 逻辑
		if( $this->_objCouponModel->getCouponCode() != '' ){
			$this->_addCouponDiscountIntoDiscountList();
		}
		//应用所有的折扣
		$this->_applyAllDiscount();

		//礼物 coupon
		if($this->_flgEnterLoop === false) {

			$this->_flgEnterLoop = true;
			if($this->_gift){
				//@todo 和以前的可以同时使用??   (A != B,  A存在或为空)   A.B 都使用??
				if (!in_array($this->_gift['favorable_gift_id'], $existedGiftId)) {
					$this->addToCart(array($this->_gift['sku'] => $this->_gift));
					$this->loadCart();
					return;
				}
			}elseif(!empty($appliedGift)){
				$this->deleteCartById($existedGiftRid);
				$this->loadCart();
				return;
			}
		}

		if(!($this->_objCouponModel->getCouponCode()) && !empty($existedGiftRid)){
			$this->deleteCartById($existedGiftRid);
			$this->loadCart();
			return;
		}

		//积分使用计算
		$this->_applyIntegral();

		//总的积分价格
		$this->_totalIntegral = round($this->_totalPrice);

		//总价为空
		if($this->_totalPrice < 0.01 || empty($this->_goodsList)) {
			$this->_totalPrice = 0; //商品的总价为空
			$this->_flgCanCheckout = false; //购买的状态是不能购买
			$this->_cartMessage = lang('account_cart_empty');
		}

		$this->_totalPriceCart = $this->_totalPrice;

		//购物的方式
		if($this->_shippingCountry !== false) {
			$this->shipping->setShippingCountry($this->_shippingCountry);//物流国家
			$this->shipping->setShippingCity($this->_shippingCity);//物流城市
			$this->shipping->setOrderGoodsWeight($goodsWeightTotal);//订单商品重量
			$this->shipping->setMaxGoodsWeight($goodsWeightMax);//最大商品重量
			$this->shipping->setOrderVolumeWeight($volumeWeightTotal);//订单体积重量
			$this->shipping->setOrderPrice($this->_totalPrice);//订单的总价
			$this->shipping->setWarehouse($warehouseArr);//海外仓
			$this->shipping->setMaxSumLwh($maxSumlwh);//最大长宽高
			$this->shipping->setMaxLength($maxLength);//最大长
			//是否是敏感品
			if($flgSensitive) {
				$this->shipping->setContainSensitive();
			}
			//是否是电池
			if($flgBattery) {
				$this->shipping->setContainBattery();
			}
			//物流信息列表
			$this->_shippingList = $this->shipping->getShippingMethodList();
			if(isset($this->_shippingList['CN-Mail'])) {
				$this->_isCnMail = TRUE;
			}

			//如果可以配送
			if($this->shipping->checkShippingAvailable()) {
				//选择配送方式
				$this->_selectShipping($this->_selectedShippingId);
				//如果有保险，增加保险的价格
				if($this->_flgInsurance) {
					$this->_totalPrice += self::INSURANCE_FEE;
				}
			} else {
				$this->_selectedShippingId = 0;
				$this->_flgInsurance = false;
				$this->_flgSeparatePackage = false;
				$this->_flgSeparatePackageDisabled = true;
				$this->_shippingMessage = lang('shipping_notice');
			}
		}

		//如果拆包标记为空 判断物流是否为3 3不能拆包
		if($this->_flgSeparatePackage === null) {
			if($this->_selectedShippingId == 3) {
				$this->_flgSeparatePackage = false;
			} else {
				$this->_flgSeparatePackage = true;
			}
		}

		//物流为1的时候
		if($this->_selectedShippingId == 1) {
			$this->_flgSeparatePackage = true;
			$this->_flgSeparatePackageDisabled = true;
		}

		//商品数量小于等于1
		if($totalNumber <= 1){
			$this->_flgSeparatePackage = false;
			$this->_flgSeparatePackageDisabled = true;
		}

		//支付
		//支付的国家为空时安物流国家
		if($this->_paymentCountry === false) {
			$this->_paymentCountry = $this->_shippingCountry;
		}
		//设置物流的国家
		$this->payment->setShippingCountry($this->_shippingCountry);
		//设置支付国家
		$this->payment->setPaymentCountry($this->_paymentCountry);
		//加载支付列表 默认不初始化数值
		if( $isGetPaymentList === TRUE && $isPaypayEC === FALSE ){
			//走adyen的支付方式
			$this->payment->loadPaymentList($this->_paymentCountry,  $this->_totalPrice);
		}elseif( $isGetPaymentList === FALSE && $isPaypayEC === TRUE ){
			//支付 paypalEc 单独取出来
			$this->payment->loadPaypalECPayment();
		}else{
			$this->_paymentList = array();
		}
		if( $isGetPaymentList === TRUE || $isPaypayEC === TRUE ){
			$paymentListTmp = $this->payment->getPaymentList();
			$this->_paymentList = array_values( $paymentListTmp );
		}
		
		//检查是否可以下单
		if($this->_shippingCountry === false) {
			$this->_flgCanPlaceorder = false;
		}
		//区域为空的时候不可以下单
		if($this->_flgEmptyProvince === true) {
			$this->_flgCanPlaceorder = false;
		}
		//判断是否可以生单
		if(!$this->_flgCanCheckout) {
			$this->_flgCanPlaceorder = false;
		}
		//未登录的生单
		if(!$this->_flgIgnoreLoginCheck) {
			if(empty($this->_user) || $this->_addressId == 0) {
				$this->_flgCanPlaceorder = false;
			}
		}
		//未登录支付判断
		if(!$this->_flgIgnorePaymentCheck) {
			//订单不能支付判断
			if(!$this->payment->checkPlaceOrderPaymentAvailable($this->_paymentId)) {
				//默认支付方法
				$defaultPaymentId = $this->payment->getPlaceOrderDefaultAvailablePayment();
				if($defaultPaymentId === false) {
					$this->_flgCanPlaceorder = false;
				} else {
					$this->_paymentId = $defaultPaymentId;
				}
			}
		}
		//检测物流是否有效
		if(!$this->shipping->checkSelectedShippingAvailable($this->_selectedShippingId)) {
			$this->_flgCanPlaceorder = false;
		}
	}

	/**
	 * 获取购物车信息
	 * @return array
	 */
	public function getCart() {
		$cart = array(
			'goods_list' => array_values($this->_goodsList),
			'subtotal' => $this->_subtotalPrice,
			'subtotal_price' => formatPrice($this->_subtotalPrice),
			'total_price_number' => $this->_totalPrice,
			'total_price' => formatPrice($this->_totalPrice),
			'total_integral' => $this->_totalIntegral,
			'flg_can_checkout' => $this->_flgCanCheckout,
			'cart_message' => $this->_cartMessage,

			'coupon_code' => $this->_objCouponModel->getCouponCode(),
			'coupon_message' => $this->_objCouponModel->getCouponMessage(),

			'use_integral' => $this->_useIntegral,
			'use_integral_price_number' => $this->_calculatePointPrice($this->_useIntegral),
			'use_integral_price' => formatPrice($this->_calculatePointPrice($this->_useIntegral)),
			'integral_message' => $this->_integralMessage,

			'discount_desc' => $this->_discountDesc,
			'discount_amount' => $this->_discountAmount,
			'discount_amount_price' => formatPrice($this->_discountAmount),
			'discount_coupon_price' => $this->_couponDiscountAmount ,
			'discount_use_group_price' => $this->_useGroupDiscountAmount ,
			'discount_use_level_price' => $this->_useLevelDiscountAmount ,

			'address_id' => $this->_addressId,
			'shipping_country' => $this->_shippingCountry,
			'flg_empty_province' => $this->_flgEmptyProvince,
			'shipping_list' => $this->_shippingList,
			'shipping_id' => $this->_selectedShippingId,
			'shipping_message' => $this->_shippingMessage,
			'is_cn_mail' => $this->_isCnMail,
			'shipping_price_number' => $this->_shippingPrice,
			'shipping_price' => formatPrice($this->_shippingPrice),
			'flg_insurance' => $this->_flgInsurance,
			'insurance_price' => formatPrice(self::INSURANCE_FEE),
			'flg_separate_package' => $this->_flgSeparatePackage,
			'flg_separate_package_disabled' => $this->_flgSeparatePackageDisabled,

			'payment_country' => $this->_paymentCountry,

			'payment_list' => $this->_paymentList ,
			'payment_id' => $this->_paymentId,

			'flg_can_placeorder' => $this->_flgCanPlaceorder,
			//多语言
			'trackingNumberMul'=>lang('track_number'),
		);
		return $cart;
	}

	/**
	 * 允许拆包
	 */
	public function allowSeparatePackage() {
		// 判断设置拆包的状态，如果是中国就不用拆包
		$this->_flgSeparatePackage = true;
		if($this->_shippingCountry == 'CN') { $this->_flgSeparatePackage = false; }
		$this->_saveSessionSeparatePackageInfo();
	}

	/**
	 * 取消拆包
	 */
	public function denySeparatePackage() {
		$this->_flgSeparatePackage = false;
		$this->_saveSessionSeparatePackageInfo();
	}

	/**
	 * 添加保险
	 */
	public function addInsurance() {
		// 判断设置拆包的状态，如果是中国就没有保险
		$this->_flgInsurance = true;
		if($this->_shippingCountry == 'CN') { $this->_flgInsurance = false; }
		$this->_saveSessionInsuranceInfo();
	}

	/**
	 * 取消添加保险
	 */
	public function removeInsurance() {
		$this->_flgInsurance = false;
		$this->_saveSessionInsuranceInfo();
	}

	/**
	 * 删除session中用户的独有折扣
	 */
	public function cancelExclusiveCodeDiscount() {
		//删除session
		$this->session->delete('exclusiveCodeInfo');
	}

	/**
	 * 选择物流
	 * @param integer $shippingId 物流id
	 */
	public function selectShipping($shippingId) {
		$shippingId = intval($shippingId);
		if($this->_selectedShippingId != $shippingId) {
			$this->_flgSeparatePackage = null;
		}
		$this->_selectedShippingId = intval($shippingId);
	}

	/**
	 * 选择支付id
	 * @param  integer $id 支付id
	 */
	public function selectPayment($id){
		$this->_paymentId = $id;
		$this->_saveSessionPaymentInfo();
	}

	/**
	 * 设置支付的国家
	 */
	public function setPaymentCountry($countryCode) {
		$this->_paymentCountry = strtoupper($countryCode);
		$this->_saveSessionPaymentInfo();
	}

	/**
	 * 设置拆包信息  问测试人员
	 */
	public function resetSeparatePackage() {
		//设置拆包信息
		$this->_flgSeparatePackage = null;
	}

	/**
	 * 设置忽略登录检查状态
	 */
	public function setIgnoreLoginCheck() {
		//设置忽略登录检查状态
		$this->_flgIgnoreLoginCheck = true;
	}

	/**
	 * 设置忽略登录支付检查状态
	 */
	public function setIgnorePaymentCheck() {
		//设置忽略登录支付检查状态
		$this->_flgIgnorePaymentCheck = true;
	}

	/**
	 * 设置物流国家
	 */
	public function setShippingCountry($countryCode) {
		//设置物流国家
		$this->_shippingCountry = strtoupper($countryCode);
	}

	/**
	 * 设置物流城市
	 */
	public function setShippingCity($city) {
		//设置物流城市
		$this->_shippingCity = $city;
	}

	/**
	 * 合并购物车
	 */
	public function mergeCart(){
		if( empty( $this->_user ) ){ return false; }

		//获取用户购物车商品
		$userCart = $this->getCartByUser( (int)$this->_user['user_id'] );
		$userCartList = array();
		$margeCartMarkUserLogined = false;
		if(is_array($userCart) && count($userCart) > 0) {
			// 标记用户登陆购物车内有没有商品
			$margeCartMarkUserLogined = true;
			foreach( $userCart as $record ){
				$key = (int)$record['product_id'] . '_'. trim($record['sku']).'_'.(int)$record['pro_type']. '_' .(int)$record['pro_id']. '_' .(int)$record['binding_pid'] ;
				$userCartList[ $key ] = $record;
			}
		}

		//获取sessionId购物车商品
		$sessionCart = $this->getCartBySession( $this->_sessionId );
		$nowTime = HelpOther::requestTime();

		//购物车有没有进行商品合并的表示
		$margeCartMarkNologin = false;
		//将商品添加到cart表中
		if(is_array($sessionCart) && count($sessionCart) > 0) {
			foreach ( $sessionCart as $value ) {
				// 标记用户登陆前购物车内有没有商品
				$margeCartMarkNologin = true;
				//检查同一条促销规则的商品存在不，存在就是更新商品的数量
				$keySource = (int)$value['product_id'] . '_'. trim( $value['sku'] ) . '_' .(int)$value['pro_type']. '_' .(int)$value['pro_id'] . '_' .(int)$value['binding_pid'] ;
				$goodsNumber = (int) $value['goods_number'] ;
				$updateId = (int)$value['id'] ;
				if( isset( $userCartList[ $keySource ] ) ) {
					//如果购物车中有这个商品将商品的购买数量加1
					$goodsNumber += (int)$userCartList[ $keySource ]['goods_number'] ;
					$updateId = (int)$userCartList[ $keySource ]['id'] ;
					$deleteArr[ ( int )$value['id'] ] =  ( int )$value['id'];
				}
				//购物车中商品为礼物 合并大于限制个数 那么 取最大限定个数
				if( $value['pro_type'] ==  self::CART_GOODS_TYPE_GIFT && isset( $value[ 'favorable_gift_id' ] ) ){
					$_giftId = (int)$value[ 'favorable_gift_id' ];

					$gifts = $this->getGiftById( $_giftId );
					if(!empty($gifts) && count($gifts)){
						$giftAmountLimit = $gifts[0]['limit'];
						if($goodsNumber > $giftAmountLimit){
							$goodsNumber = $giftAmountLimit;
						}
					}
				}

				$updateArr[] = array(
					'id' => $updateId,
					'user_id' => $this->_user['user_id'],
					'session_id' => $this->_sessionId ,
					'goods_number' => $goodsNumber ,
					'update_time' => $nowTime,
				);
			}
		}
		if(isset($updateArr) && !empty( $updateArr ) ) $this->updateBatchCart( $updateArr, TRUE );
		if(isset($deleteArr) && !empty( $deleteArr ) ) $this->deleteCartById( $deleteArr );

		//设置购物车合并表示
		$margeCartMark = false;
		if($margeCartMarkUserLogined == true && $margeCartMarkNologin == true) {
			$margeCartMark = true;
			$this->session->set('markMergeCart', $margeCartMark);
			if($this->session->get('returnUrl')=='place_order'){
				$this->session->set('returnUrl','cart');
			}
		}
		$this->_markMergeCart = $margeCartMark;

		$this->session->delete('user_cart');
	}

	public function getCartMergeMark() {
		return $this->_markMergeCart;
	}

	/**
	 * 重置参数
	 */
	protected function _resetCartParams() {
		$this->_goodsList = array();
		$this->_subtotalPrice = 0;
		$this->_totalPrice = 0;
		$this->_totalIntegral = 0;
		$this->_flgCanCheckout = true;
		$this->_cartMessage = false;
		$this->_objCouponModel->setCouponMessage();
		$this->_integralMessage = false;
		$this->_discountList = array();
		$this->_discountDesc = '';
		$this->_discountAmount = 0;
		$this->_couponDiscountAmount = 0;//coupon折扣金额  本位币美元
		$this->_useGroupDiscountAmount = 0;//用户组折扣金额  本位币美元
		$this->_useLevelDiscountAmount = 0;//用户积分等级折扣金额  本位币美元
		$this->_subtotalMarketPrice = 0;
		$this->_gift = false;
	}

	/**
	 * 购物车初始化信息
	 */
	protected function _initEnvironmentInfo() {

		//当前登录用户的sessionid
		$this->_sessionId = $this->session->getSessionId();
		//当前访问的语言的id
		$this->_languageId = $this->m_app->currentLanguageId();
		//当前访问的语言的code
		$this->_languageCode = $this->m_app->currentLanguageCode();

		//检查用户是否登录 如果登录的话 获取用户的登录信息
		if($this->m_app->checkUserLogin()) {
			$this->_user = $this->_objUserModel->getUserInfo($this->m_app->getCurrentUserId());
		}

		if(!empty($this->_user)){
			//获取用户的积分
			$point = $this->_objUserModel->getUserPoint($this->_user['user_id']);
			$this->_user['point_active'] = intval(id2name('active', $point, 0) );
			$pointTotal = intval(id2name('total',$point,0));

			//取出用户级别
			$this->_user['level'] = array();
			$pointTotal = min($pointTotal, 9999);
			foreach(AppConfig::$user_rank as $level => $levelInfo){
				if($levelInfo['min_points'] <= $pointTotal && $levelInfo['max_points'] >= $pointTotal){
					$levelInfo['level'] = $level;
					$this->_user['level'] = $levelInfo;
					break;
				}
			}
		}
	}

	/**
	 * 用户的积分
	 */
	protected function _loadSessionIntegralInfo() {
		$integral = $this->session->get('cart_integral');
		if($integral !== false) {
			$this->_useIntegral = (int)$integral;
		}
	}

	/**
	 * 初始化地址加载
	 */
	protected function _loadAddressInfo() {
		//判断用户是否登录
		if(!empty($this->_user)) {
			$this->_addressId = id2name('address_id',$this->_user,0);
		} else {
			$this->_addressId = 0;
		}

		//初始化地址信息
		$address = array();
		if($this->_addressId > 0) {
			$address = $this->m_address->getAddress($this->_addressId);//取出指定的地址信息
		}

		//处理地址信息
		if(!empty($address) && $address['user_id'] == $this->_user['user_id']) {
			//国家
			$this->_shippingCountry = strtoupper($address['country']);
			//省份处理
			if(in_array($this->_shippingCountry, array('US', 'AU')) && $address['province'] == '') {
				$this->_flgEmptyProvince = true;
			}
			$this->_shippingCity = $address['city'];
		} else {
			$this->_addressId = 0;
			$this->_shippingCountry = false;
		}
	}

	/**
	 * 加载物流id
	 */
	protected function _loadSessionShippingInfo() {
		//物流id
		$shippingId = $this->session->get('cart_shipping_id');
		if($shippingId !== false) {
			//已经选择的物流ID
			$this->_selectedShippingId = intval($shippingId);
		}
	}

	/**
	 * 保险
	 */
	protected function _loadSessionInsuranceInfo() {
		//获取是否开启保险
		$flgInsurance = $this->session->get('cart_flg_insurance');
		if($flgInsurance !== false) {
			$this->_flgInsurance = true;
		}
	}

	/**
	 * 拆包信息
	 */
	protected function _loadSessionSeparatePackageInfo() {
		//获取物流信息
		$flgSeparatePackage = $this->session->get('cart_flg_separate_package');
		if($flgSeparatePackage == 1) {
			$this->_flgSeparatePackage = true;
		} else {
			$this->_flgSeparatePackage = false;
		}
	}

	/**
	 * 保存当前的拆包信息
	 */
	protected function _saveSessionSeparatePackageInfo() {
		if($this->_flgSeparatePackage) {
			$this->session->set('cart_flg_separate_package',1);
		} else {
			$this->session->set('cart_flg_separate_package',0);
		}
	}

	/**
	 * 保存保险信息
	 */
	protected function _saveSessionInsuranceInfo() {
		if($this->_flgInsurance) {
			$this->session->set('cart_flg_insurance',1);
		} else {
			$this->session->delete('cart_flg_insurance');
		}
	}

	/**
	 * 支付信息
	 */
	protected function _loadSessionPaymentInfo() {
		//获取支付信息
		$paymentId = $this->session->get('cart_payment_id');
		if($paymentId !== false) {
			$this->_paymentId = $paymentId;
		}

		//获取支付的国家
		$paymentCountry = $this->session->get('cart_payment_country');
		if($paymentCountry !== false){
			$this->_paymentCountry = $paymentCountry;
		}
	}

	/**
	 * 格式化参数数组用来调用商品基于skuinfo的判断
	 * @param  array  $skuPidArray 购物车的商品数组
	 * @return array
	 */
	protected function _formatSkuPid($skuPidArray, $languageId) {
		if(empty($skuPidArray)) {
			return array();
		}

		/*根据购物车列表商品的pid，获取相应的product info.*/
		$allPids = extractColumn( $skuPidArray , 'product_id' );
		$this->ProductModel = new ProductModel();
		$productInfoArr = $this->ProductModel->getProInfoById( $allPids, $languageId );

		/*遍历购物车数据列表，构建新的处理数据。*/
		$returnArray = array();
		foreach ($skuPidArray as $value) {
			$keyTmp = (int)$value['id'] ;
			$returnArray[ $keyTmp ] = array( //key为商品的sku
				'sku' => $value['sku'], //商品的sku
				'pid' => $value['product_id'], //商品的pid
				'promoteType' => $value['pro_type'], //促销类型
				'promoteId'=> $value['pro_id'], //促销活动的自增id
				'bindingPid'=> $value['binding_pid'], //捆绑在此pid
				'finalPrice' => $value['final_price'] ,//最后销售价
				'qty'=> (int) $value['goods_number'], //购买的商品的数量
				'rec_id'=> $keyTmp , //购物车的主键ID
				'createTimeCart' => ( isset( $value['create_time'] ) ? (int)$value['create_time'] : 0 ) , //购物车删除需要使用此信息
				'complexattr' => $productInfoArr[ $value['product_id'] ]['complexattr'],
			);
		}

		return $returnArray;
	}

	/**
	 * 计算购物车商品的价格
	 * @param  array $record 购物车商品的信息
	 */
	protected function _calculateGoodsPrice($record) {
		//当前售价
		$record['goodsPrice'] = $record['finalPrice']; //商品的最终售价
		$record['formatGoodsPrice'] = formatPrice($record['finalPrice']); //格式化商品最终售价
		$record['formatSubtotal'] = formatPrice($record['finalPrice']*$record['qty']);
		$record['priceSubtotal'] = $record['finalPrice']*$record['qty'];
		$record['marketPriceSubtotal'] = $record['finalmarketPrice']*$record['qty'];

		//原来售价
		$record['originalPrice'] = false;
		$record['originalSubtotal'] = false;

		//price icon
		$record['flgFree'] = false;

		return $record;
	}

	/**
	 * 添加用户组折扣信息
	 */
	protected function _addGroupDiscountIntoDiscountList() {

		//判断登录用户并且是折扣组用户
		if(!empty($this->_user) && $this->_user['user_group_id'] == 3){
			$priceDiscountAmount = sprintf("%.2f", $this->_totalPrice * 0.1);
			$priceDiscountAmount = floatval($priceDiscountAmount);
			$priceDiscountAmount = max($priceDiscountAmount,0);
			//用户组打折记录存数据库使用
			$this->_useGroupDiscountAmount = $priceDiscountAmount ;

			//判断记录折扣列表信息
			if($priceDiscountAmount > 0){
				$this->_discountList[] = array(
					'name' => 'Business Customer Discount',
					'amount' => $priceDiscountAmount,
				);
			}
		}
	}

	/**
	 * 用户级别折扣信息
	 */
	protected function _addLevelDiscountIntoDiscountList() {
		//判断用户所属的折扣条件
		if(!empty($this->_user) && !empty($this->_user['level']) && $this->_user['level']['discount'] > 0 && $this->_user['user_group_id'] != 3) {
			$priceDiscountAmount = sprintf("%.2f", $this->_totalPrice * ($this->_user['level']['discount'] / 100));
			$priceDiscountAmount = floatval($priceDiscountAmount);
			$priceDiscountAmount = max($priceDiscountAmount,0);
			if($priceDiscountAmount > 0){
				//使用积分等级折扣记录 用户存储数据库
				$this->_useLevelDiscountAmount = $priceDiscountAmount ;

				$this->_discountList[] = array(
					'name' => lang('level_'.$this->_user['level']['level'].'_title').' '.$this->_user['level']['discount'].'% '.lang('discount'),
					'amount' => $priceDiscountAmount,
				);
			}
		}
	}

	/**
	 * 应用所有的折扣
	 */
	protected function _applyAllDiscount() {
		if(!empty($this->_discountList)){
			$discountName = array();
			foreach($this->_discountList as $record){
				//用户组和积分等级 的打折 都是在总的价格上 进行百分比打折 所以 用于不可能大于 只有coupon 会出现此情况
				if($record['amount'] >= $this->_totalPrice) {
					return $this->_returnError(lang('coupon_code_not_stuitable'));
				}
				$discountName[] = $record['name'];
				$this->_totalPrice -= $record['amount'];
				$this->_discountAmount += $record['amount'];
			}
			$this->_discountDesc = implode(',',$discountName);
		}
	}

	/**
	 * 积分应用
	 */
	protected function _applyIntegral() {
		//判断登录用户再折扣组中就不能使用积分
		if(!empty($this->_user) && $this->_user['user_group_id'] == 3) {
			$this->_useIntegral = 0;
		} elseif ($this->_useIntegral > 0) {//使用积分的数量大于0
			//判断登录用的积分状态
			if(empty($this->_user) || $this->_useIntegral > $this->_user['point_active']) {
				//用户的可用户的积分
				$this->_useIntegral = $this->_user['point_active'];
				$this->_integralMessage = lang('integral_not_enough');
			}

			//订单积分的使用限制
			$orderLimitIntegral = $this->_calculateMaxIntegral();

			//用户使用积分大于订单积分限制
			if($this->_useIntegral > $orderLimitIntegral) {
				$this->_useIntegral = $orderLimitIntegral;
				$this->_integralMessage = lang('max_point');
			}
			//使用积分的价格总价
			$this->_totalPrice -= $this->_calculatePointPrice($this->_useIntegral);
		}

		//再session中记录积分使用的情况
		$this->_saveSessionIntegralInfo();
	}

	/**
	 * 计算最大的使用积分
	 */
	protected function _calculateMaxIntegral() {
		//乡下取舍为接近的整数
		$point = floor($this->_subtotalPrice * 20);
		return $point;
	}

	/**
	 * 用户使用积分后的价格判断
	 * @param  integer $point 用户的使用积分
	 * @return float 订单使用积分后的价格
	 */
	protected function _calculatePointPrice($point) {
		$price = round($point/100, 2);
		return $price;
	}

	/**
	 * 记录用户是使用积分再session中
	 */
	protected function _saveSessionIntegralInfo() {
		//记录购物车积分使用
		$this->session->set('cart_integral',$this->_useIntegral);
	}

	/**
	 * 选择指定的物流方式
	 * @param  integer $shippingId 物流id
	 */
	protected function _selectShipping($shippingId) {
		//已经选择的物流方式id
		$this->_selectedShippingId = 0;
		//默认物流方式
		$defaultShippingId = 0;
		//默认的物流价格
		$defaultShippingPrice = 0;
		//循环处理物流
		foreach($this->_shippingList as $record) {
			if($record['flg_active'] === true && isset($record['id']) && $record['id'] == $shippingId) {
				$this->_selectedShippingId = $record['id'];
				$this->_shippingPrice = $record['price'];
				break;
			}
			if(!empty($record['register']) && $record['register']['id'] == $shippingId) {
				$this->_selectedShippingId = $record['register']['id'];
				$this->_shippingPrice = $record['price'] + $record['register']['price'];
				break;
			}
			if($record['flg_active'] === true && $defaultShippingId == 0) {
				$defaultShippingId = $record['id'];
				$defaultShippingPrice = $record['price'];
			}
			if(!empty($record['register']) && $defaultShippingId == 0) {
				$defaultShippingId = $record['register']['id'];
				$defaultShippingPrice = $record['price'] + $record['register']['price'];
			}
		}

		if($this->_selectedShippingId == 0) {
			$this->_selectedShippingId = $defaultShippingId;
			$this->_shippingPrice = $defaultShippingPrice;
		}

		//判断物流国家如果不是中国的时候
		if($this->_shippingCountry != 'CN') {
			if($this->_selectedShippingId == self::SHIPPING_CNEMAIL) {
				$this->_selectedShippingId = 0;
				$this->_shippingPrice = 0.00;
				$this->_flgInsurance = false;
				$this->_flgSeparatePackage = false;
				$this->_flgSeparatePackageDisabled = true;
				$this->_shippingMessage = lang('shipping_notice');
			}
		}

		//物流后的总价
		$this->_totalPrice += $this->_shippingPrice;

		//记录物流信息
		$this->_saveSessionShippingInfo();
	}

	/**
	 * 记录物流信息
	 */
	protected function _saveSessionShippingInfo() {
		if($this->_selectedShippingId == 0){
			$this->session->delete('cart_shipping_id');
		}else{
			$this->session->set('cart_shipping_id',$this->_selectedShippingId);
		}
	}

	/**
	 * 保存用户的支付方式
	 */
	protected function _saveSessionPaymentInfo() {
		if($this->_paymentId > 0) {
			$this->session->set('cart_payment_id',$this->_paymentId);
		} else {
			$this->session->delete('cart_payment_id');
		}
		if($this->_paymentCountry !== false) {
			$this->session->set('cart_payment_country',$this->_paymentCountry);
		} else {
			$this->session->delete('cart_payment_country');
		}
	}

	/**
	 *
	 * @param type $rangeType 范围类型(1全站 2分类 3商品)
	 * @param type $range 适用范围(coupon_range_type==1时为空 coupon_range_type==2时为逗号分隔分类ID coupon_range_type==3时为逗号分隔PID)
	 * @return int 返回//需要优惠的总价
	 */
	protected function _getNeedFavorablePrice($rangeType = 1, $range = ''){
		if(empty($rangeType) && empty($range)){
			return 0;
		}
		$needFavorablePrice = 0;
		switch($rangeType){
			case 1:
				foreach($this->_goodsList as $goods){
					if($goods['promoteType'] != self::CART_GOODS_TYPE_GIFT ){
						$needFavorablePrice += $goods['priceSubtotal'];
					}
				}
				break;
			case 2:
				$cat_ids = explode(',' , $range);

				//range_type==2时适用范围为coupon_range中的所有分类ID的所有子分类(包括主分类和副分类)的所有商品
				$this->CategoryModel = new Categoryv2Model();
				$catIdLists = array();
				//取出主分类 @todo 一个商品在主分类存在就不会在副分类存在??!!
				foreach($cat_ids as $catId){
					$catIdLists =  array_merge($catIdLists, $this->CategoryModel->getSubCategoryIdsById($catId));
				}
				$catIdLists = array_unique($catIdLists);

				if(empty($catIdLists)){
					$catIdLists = $cat_ids ;
				}
				//取出副分类
				$productObj = ProductModel::getInstanceObj() ;
				$pids = $productObj->getSaleCategoryProduct( $catIdLists );

				foreach($this->_goodsList as $goods){
					if($goods['promoteType'] != self::CART_GOODS_TYPE_GIFT ){
						if(in_array($goods['category_id'], $catIdLists) ){
							$needFavorablePrice += $goods['priceSubtotal'];
						}elseif(in_array($goods['pid'], $pids) ){
							$needFavorablePrice += $goods['priceSubtotal'];
						}
					}
				}

				break;
			case 3;
				$pids = explode(',' , $range);

				foreach($this->_goodsList as $goods){
					if(in_array($goods['pid'], $pids) &&  $goods['promoteType'] != self::CART_GOODS_TYPE_GIFT  ){
						$needFavorablePrice += $goods['priceSubtotal'];
					}
				}

				break;
			default:
				$needFavorablePrice = 0;
				break;
		}
		return $needFavorablePrice;
	}
	/**
	 * 现金减免  和 满额减免
	 * $coupon['rangeType']  范围类型(1全站 2分类 3商品)
	 * $coupon['type'] 优惠类型(1现金减免 2现金折扣 3满额减免 4满额折扣 5赠品)
	 * @param type $coupon
	 * @return int
	 */
	protected function _getPriceFavorableAmountByReduce( $coupon, $needFavorablePrice = 0 ){
		if(empty($coupon)){
				return 0;
		}
		$favorablePriceAmonut = 0;
		$basePrice = 0;
		//coupon_effect里可能没有相应的数据,则$coupon['effect']不是数组
		if( empty($coupon['effect'])){
			return 0;
		}
		if($coupon['type'] == self::PROMOTE_ACT_TYPE_PRICE_REDUCE ){
			$favorablePriceAmonut = floatval($coupon['effect'][0]['value']);
			if($needFavorablePrice > 0 && $needFavorablePrice > $favorablePriceAmonut){//如果当前商品不是优惠的,则当前的coupon不能使用
				$favorablePriceAmonut = sprintf("%.2f", $favorablePriceAmonut);
			}else{
				$favorablePriceAmonut = 0;
			}
		}else if($coupon['type'] == self::PROMOTE_ACT_TYPE_FULL_REDUCE ){
			$effects = $coupon['effect'];
			$curCouponEffect = '';
			$prevEffectPrice = 0;
			//$_effect['price']大小顺序可能是乱的
			foreach($effects as  $_effect){ // 10 20 15 30 25   [ 22 ]
				if($needFavorablePrice >= $_effect['price'] &&  $_effect['price'] > $prevEffectPrice ){
					$curCouponEffect =  $_effect;
					$prevEffectPrice = $_effect['price'];
				}
			}
			if(!empty($curCouponEffect)){
				$favorablePriceAmonut = floatval($curCouponEffect['value']);
				$basePrice = $curCouponEffect['price'];
			}
			//range_type 范围类型 为2分类 3商品 时 $needFavorablePrice > 0
			if( ($needFavorablePrice > 0)  && ($basePrice > 0 ) && $needFavorablePrice >= $basePrice && $needFavorablePrice > $favorablePriceAmonut){
				$favorablePriceAmonut = sprintf("%.2f", $favorablePriceAmonut);
			}else{
				$favorablePriceAmonut = 0;
			}
		}
		$favorablePriceAmonut = floatval($favorablePriceAmonut);
		return  max($favorablePriceAmonut,0);
	 }

	/**
	 * 现金折扣 /满额折扣
	 * @param type $coupon
	 * @return int
	 */
	 protected function _getPriceFavorableAmountByDiscount($coupon, $needDiscountPrice = 0){
		  if(empty($coupon)){
		      return 0;
		  }
		$discountRate = 0;
		$basePrice = 0;
		//coupon_effect里可能没有相应的数据,则$coupon['effect']不是有效数组
		if(  empty($coupon['effect']) ){
			return 0;
		}
		if( $coupon['type']  == self::PROMOTE_ACT_TYPE_PRICE_DISCOUNT){
			   $discountRate = floatval($coupon['effect'][0]['value']);
			if($needDiscountPrice > 0){
				$priceDiscountAmount = sprintf("%.2f", $needDiscountPrice * $discountRate * 0.01 );
			}else{
				$priceDiscountAmount = 0;
			}
		}else if( $coupon['type']  == self::PROMOTE_ACT_TYPE_FULL_DISCOUNT){
			$effects = $coupon['effect'];
			$curCouponEffect = array();
			$prevEffectPrice = 0;
			//$_effect['price']大小顺序可能是乱的
			foreach($effects as  $_effect){ // 10 20 15 30 25   [ 22
				if($needDiscountPrice >= $_effect['price'] &&  $_effect['price'] > $prevEffectPrice ){
					$curCouponEffect = $_effect;
					$prevEffectPrice = $_effect['price'];
				}
			}
			if(!empty($curCouponEffect)){
				$discountRate = floatval($curCouponEffect['value']);
				$basePrice = $curCouponEffect['price'];
			}
			//range_type 范围类型 为2分类 3商品 时 $needDiscountPrice > 0
			if( ($needDiscountPrice > 0)  && ($basePrice > 0 ) && $needDiscountPrice >= $basePrice){
				$priceDiscountAmount = sprintf("%.2f", $needDiscountPrice * $discountRate * 0.01);
			}else{
				$priceDiscountAmount = 0;
			}
		}
		  $priceDiscountAmount = floatval($priceDiscountAmount);

		  return  max($priceDiscountAmount,0);
	 }

	 protected function _returnError($errorMessage = false, $setMessageFlag = true){
		  $this->_removeSessionCouponInfo();
		  $this->_objCouponModel->setCouponCode();
		  if($setMessageFlag){
			  $this->_objCouponModel->setCouponMessage( $errorMessage);
		  }
		  return false;
	 }

	/**
	 * 格式化copon
	 * 删除session
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function useCoupon($coupon){
		$this->_objCouponModel->setCouponCode( trim( $coupon ) );
		$this->_flgEnterLoop = false;
		$this->session->delete('user_cart');
	}

	/**
	 * 删除CouPon Session
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	protected function _removeSessionCouponInfo(){
		//删除coupon信息
		$this->session->delete('cart_coupon_code');
	}

	/**
	 * 设置coupon 到session 里
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	protected function _saveSessionCouponInfo(){
		$this->session->set('cart_coupon_code',$this->_objCouponModel->getCouponCode());
	}

	/**
	 * 重新设置 shipping_country为FALSE
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function setIgnoreShipping(){
		$this->_shippingCountry = false;
	}

	/**
	 * 设置积分
	 * @param int $point
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function useIntegral($point){

		$pointFinal = max(0,intval($point));
		$this->_useIntegral = $pointFinal;
	}



	/**
	 * 验证购物车中商品的sku是否复合coupon的sku规则
	 * @param type $couponSkus
	 * @return boolean
	 * @author Terry
	 */
	private function _checkCouponSku($couponSkus){

		$couponSkusArr = explode(',', $couponSkus);
		foreach ($couponSkusArr as $couponSku) {
			foreach ($this->_goodsList as $item) {
				if ($item['pid'] == $couponSku) {
					return TRUE;
				}
			}
		}

		return false;
	}

	/**
	 * coupon 运用 分类 上
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 * /
	protected function _applyCouponCategory( $coupon ) {
		$coupon['quota_level'] = json_decode($coupon['quota_level']);
		//取出coupon作用的分类id
		$catLds = explode(',',$coupon['range_value']);

		$fitCount = 0;
		$priceFitRange = 0;
		//判断商品是否参与coupon优惠
		foreach($this->_goodsList as $goods) {
			if(!in_array($goods['category_id'], $catLds)) { continue; }
			$fitCount++;
			$priceFitRange += $goods['priceSubtotal'];
		}

		if ($fitCount == 0) {//没有匹配到任何能应用coupon的商品，所以coupon不合适，应用不上。
			$this->_removeSessionCouponInfo();
			$this->_couponCode = '';
			$this->_couponMessage = lang('coupon_code_not_stuitable');
			return false;
		}

		$gift = array();
		$priceDiscountAmount = 0;
		//现金减免
		if($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_REDUCE){
			if($coupon['range_operator'] == 1) {
				if($fitCount > 0) {
					$priceDiscountAmount = sprintf("%.2f",$coupon['act_type_ext']);
				} else {
					$this->_removeSessionCouponInfo();
					$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
					$this->_couponMessage = lang('coupon_code_not_invalid');
					return false;
				}
			} elseif($coupon['range_operator'] == 2) {
				$priceDiscountAmount = sprintf("%.2f",$coupon['act_type_ext']*$fitCount);
			} else {
				$this->_removeSessionCouponInfo();
				$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
				$this->_couponMessage = lang('coupon_code_not_invalid');
				return false;
			}
			$priceDiscountAmount = floatval($priceDiscountAmount);
			$priceDiscountAmount = max($priceDiscountAmount,0);
		} elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_DISCOUNT) {
			if($coupon['range_operator'] == 1) {
				if($fitCount > 0) {
					$priceDiscountAmount = sprintf("%.2f", $this->_totalPrice * (1 - $coupon['act_type_ext'] / 100));
				} else {
					$this->_removeSessionCouponInfo();
					$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
					$this->_couponMessage = lang('coupon_code_not_invalid');
					return false;
				}
			} elseif($coupon['range_operator'] == 2) {
				$priceDiscountAmount = sprintf("%.2f", $priceFitRange * (1 - $coupon['act_type_ext'] / 100));
			} else {
				$this->_removeSessionCouponInfo();
				$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
				$this->_couponMessage = lang('coupon_code_not_invalid');
				return false;
			}
			$priceDiscountAmount = floatval($priceDiscountAmount);
			$priceDiscountAmount = max($priceDiscountAmount,0);
		} elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_GIFT && $this->_totalPrice >= $coupon['act_type_ext'] && $this->_checkCategoryCoupon($coupon['range_value'])) {
			$gift = CouponModel::getInstanceObj()->getCouponGiftInfo($coupon['act_id'],$this->_languageId);
		} elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_COND && $coupon['quota_level']) {

			$enabledItem = array();
			$enabledItemDiscount = array();
			foreach($coupon['quota_level'] as $item){
				if($this->_totalPrice>=$item->quota_amount){
					$enabledItem[] = $item->quota_amount;
					$enabledItemDiscount[$item->quota_amount]=$item->quota_type == 1 ? $item->quota_rate : $this->_totalPrice * $item->quota_rate * 0.01;
				}
			}
			if (empty($enabledItem)) {
				$this->_removeSessionCouponInfo();
				$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
				$this->_couponMessage = lang('coupon_code_not_stuitable');
				return false;
			}else{
				rsort($enabledItem);
				$priceDiscountAmount+= $enabledItemDiscount[$enabledItem[0]];
			}
		} else {
			$this->_removeSessionCouponInfo();
			$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
			$this->_couponMessage = lang('coupon_code_not_invalid');
			return false;
		}

		if($priceDiscountAmount > 0){
			$this->_discountList[] = array(
					'name' => $coupon['act_name_language'],
					'amount' => $priceDiscountAmount,
			);
			$this->_couponDiscountAmount+=$priceDiscountAmount;
		}

		if(!empty($gift)){
			$this->_gift = $gift;
		}
	}*/

	/**
	 * 判断购物车中商品的分类是否复合coupon中的分类规则。
	 * @param type $couponCatIds
	 * @return boolean
	 * @author Terry
	 */
	private function _checkCategoryCoupon($couponCatIds){

		$couponCatIdsArr = explode(',', $couponCatIds);
		foreach ($couponCatIdsArr as $couponCatId) {
			foreach ($this->_goodsList as $item) {
				$catPath = $item['path'];
				if (strpos($catPath, $couponCatId) !== false) {
					return TRUE;
				}
			}
		}

		return false;
	}



	/**
	 * coupon 处理
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	protected function _addCouponDiscountIntoDiscountList() {
		if($this->_objCouponModel->getCouponCode() == '' || (!empty($this->_user) && $this->_user['user_group_id'] == 3)) {
			return $this->_returnError();
		}
		//判断coupon是否可以使用,只在正常折扣，非优惠的商品才可以使用coupon
		if($this->_objCouponModel->checkCouponAvailable()){
			$subscribeInfo = $this->_objUserModel->getSubscribeInfoByHash($this->_objCouponModel->getCouponCode());
			if(!empty($subscribeInfo)){
				//subscribe coupon 订阅COUPON
				if($this->_objCouponModel->checkSubscribeCouponAvailable($subscribeInfo['code_status'] , $this->_totalPrice)){
					$this->_discountList[] = array(
						'name' => $this->_objCouponModel->getCouponCode(),
						'amount' => sprintf("%.2f", 3),
					);
					$this->_couponDiscountAmount =  sprintf("%.2f", 3);
					$this->_saveSessionCouponInfo();
				}else{
					$this->_returnError(false, false);
				}
			}else{
				$coupon = $this->_objCouponModel->getCoupon();

				if( $this->_objCouponModel->checkNormalCouponAvailable($coupon, $this->_languageId ) ){
					$gift = array();
					$couponEffectiveFlag = false;
					$priceFavorableAmount = 0;
					//coupon作用的有效价格
					$needFavorablePrice = $this->_getNeedFavorablePrice($coupon['type_range'], $coupon['range']);
					if($coupon['type'] == self::PROMOTE_ACT_TYPE_PRICE_REDUCE || $coupon['type'] == self::PROMOTE_ACT_TYPE_FULL_REDUCE ){
						$priceFavorableAmount = $this->_getPriceFavorableAmountByReduce( $coupon, $needFavorablePrice );
					}elseif($coupon['type'] == self::PROMOTE_ACT_TYPE_PRICE_DISCOUNT || $coupon['type'] == self::PROMOTE_ACT_TYPE_FULL_DISCOUNT){
						$priceFavorableAmount = $this->_getPriceFavorableAmountByDiscount($coupon, $needFavorablePrice );
					}elseif($coupon['type'] == self::PROMOTE_ACT_TYPE_PRICE_GIFT ){
						if( is_array($coupon['effect'][0]) && !empty($coupon['effect'][0]) ){
							if($needFavorablePrice >= $coupon['effect'][0]['price']){
								$gift = $this->_objCouponModel->getCouponGiftInfo($coupon['effect'][0], $this->_languageId);
							}
						}
					} else {
						return $this->_returnError(lang('coupon_code_not_invalid'));
					}
					if($priceFavorableAmount > 0){
						if($needFavorablePrice <= $priceFavorableAmount){
							return $this->_returnError(lang('coupon_code_not_stuitable'));
						}
						$this->_discountList[] = array(
								'name' => trim( $coupon['code'] ),//@todo 是否需要考虑语言版本 使用coupon
								'amount' => $priceFavorableAmount,
						);
						//coupon 优惠的金额
						$this->_couponDiscountAmount = $priceFavorableAmount;
						$couponEffectiveFlag = TRUE;
					}
					if(!empty( $gift )){
						$this->_gift = $gift;
						$couponEffectiveFlag = TRUE;
					}
					//coupon生效
					if( $couponEffectiveFlag ){
						$this->useCoupon($this->_objCouponModel->getCouponCode());
						//coupon 起作用 写入到session 中
						$this->_saveSessionCouponInfo();
					}else {
						return $this->_returnError(lang('coupon_code_not_stuitable'));
					}
				}else{
					$this->_flgEnterLoop = false;
					//当刷新或切换语言时,此处不用再设置错误提示
					$this->_returnError(false, false);
				}
			}
		}else{
			return $this->_returnError(lang('coupon_code_not_invalid'));
		}
	}

	/**
	 *  当订单支付后更新coupon使用状态
	 * @param type $cart
	 */
	public function processCoupon($couponCode = ''){
		if($couponCode != ''){
			$subscribeInfo = $this->_objUserModel->getSubscribeInfoByHash($couponCode);
			if(!empty($subscribeInfo)){
				$this->_objUserModel->updateEmailSubscribe($subscribeInfo['email'], array(
					'code_status' => 1,
				));
			}else{
				$this->_objCouponModel->setCouponCode($couponCode);
				$coupon = $this->_objCouponModel->getCoupon();
				$curUserEmail = $this->m_app->getCurrentUserEmail();
				//if($this->_objCouponModel->checkCustomerUsedCoupon($curUserEmail, $coupon['id'])){
				//	$this->_objCouponModel->addUpCustomerCouponTimes($curUserEmail, $coupon['id']);
				//}else{
				//这里每天coupon 都记录一条 不更新原有的数据 一条件记录表示使用一次
				$this->_objCouponModel->createCustomerCoupon(array(
					'coupon_id' => $coupon['id'],
					'email' => $curUserEmail,
					'counts' => 1,
					'time' => HelpOther::requestTime()
				));
				//}

				$cacheKey = "idx_couponmodel_count_coupon_%s";
				$this->memcache->delete( $cacheKey, array( (int)$coupon['id'] ) );
			}
		}
		return TRUE ;
	}

	/**
	 * 检查设置商品的库存信息
	 * @param array $cartRecord 商品的信息数组
	 * @param integer $qtyLimit 商品的购买数量
	 */
	protected function _addGoodsStockInfo( &$cartRecord, $qtyLimit = 0 ) {
		$cartRecord['flg_maxqty'] = false;
		$cartRecord['qty'] = $qtyLimit;

		$cartRecord['goods_number'] = isset($cartRecord['goods_number'])?$cartRecord['goods_number']:1;

		//检查商品的库存
//		if( $cartRecord['stock'] < $qtyLimit ) {
//			$this->_flgCanCheckout = false;
//			$this->_cartMessage = lang('goods_unstock').':'.$cartRecord['goodsName'];
//		}

//		if($cartRecord['stock'] >= $qtyLimit) {
//			$cartRecord['flg_maxqty'] = true;
//			$cartRecord['message'] = sprintf(lang('only_buy'), $qtyLimit);
//		} elseif($cartRecord['promoteType'] == self::CART_GOODS_TYPE_NORMAL && $qtyLimit <= 10) {
//			// $cartRecord['message'] = sprintf(lang('left'),$qtyLimit - $cartRecord['goods_number']);
//			$cartRecord['message'] = sprintf(lang('left'), $qtyLimit);
//		}
	}

	/**
	 * 加载coupon使用信息
	 */
	protected function _loadSessionCouponInfo() {
		$coupon = $this->session->get('cart_coupon_code');
		if($coupon !== false) {
			$this->_objCouponModel->setCouponCode(strval($coupon));
		}
	}

	//---------------------card model 走DB的 方法 start-------------------------------

	/**
	 * 更新购物车商品
	 * @param array $info
	 * @param boolean $isUpdateMcAll TRUE全部更新 FALSE部分更新
	 */
	public function updateBatchCart( $info, $isUpdateMcAll = FALSE ){

		if( !empty( $info ) && is_array( $info ) ){
			$this->db_ebmaster_write->update_batch( 'cart', $info ,'id' );
			if( $isUpdateMcAll === TRUE ) {
				$this->_clearMemcacheByCart();
			}else{
				//获取购物车数据
				$list = $this->loadCartProduct();
				// 是否更新缓存
				$isChange = FALSE ;
				foreach ( $info as $v ){
					$id = $v['id'];
					//更新数据
					if( isset( $list[ $id ] )  ){
						$isChange = TRUE ;
						unset( $v['id'] );
						foreach ( $v as $field => $value ){
							$list[ $id ][ $field ] = $value ;
						}
					}else{
						//如果购物车没有数据 说明有问题 全部清空mc 关闭 这种情况不存在
						//$this->_clearMemcacheByCart();
					}
				}

				if( $isChange ){
					//更新缓存 不清空缓存
					$this->_setMemcacheByCart( $list );
				}
			}
		}
		return TRUE ;
	}

	/**
	 * 更改商品信息
	 * 更新购物车
	 * @param int $recId 购物车主键ID
	 * @param int $info 修改的购买个数
	 * @return TRUE
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function updateCart( $id , $info ) {
		//获取购物车信息
		$list = $this->loadCartProduct();
		if( isset( $list[ $id ] ) ){
			foreach($info as $key => $value){
				$this->db_ebmaster_write->set($key,$value);
				$list[ $id ][ $key ] = $value;
			}
			$this->db_ebmaster_write->where('id',$id )->update('cart');

			//更新缓存 不清空缓存 单条数据更新
			$this->_setMemcacheByCart( $list );
		}

		return TRUE ;
	}

	/**
	 * 取出购物车中的商品
	 * @return array
	 */
	public function loadCartProduct() {
		$userId = isset( $this->_user['user_id'] ) ? (int)$this->_user['user_id'] : 0 ;
		if( $userId > 0 ) {
			$list = $this->getCartByUser( $userId );
		} else {
			$list = $this->getCartBySession( $this->_sessionId );
		}

		return $list;
	}

	/**
	 * 清空购物车
	 */
	public function clearCart() {
		$this->deleteSessionCart($this->_sessionId);
		if(!empty($this->_user) && ( (int)$this->_user['user_id'] > 0 ) ) {
			$this->deleteUserCart($this->_user['user_id']);
		}
		$this->session->delete('user_cart_all');
		$this->session->delete('cart_integral');
		$this->session->delete('cart_coupon_code');
		$this->session->delete('exclusiveCodeInfo');
		$this->session->delete('cart_shipping_id');
		$this->session->delete('cart_flg_insurance');
		$this->session->delete('cart_flg_separate_package');
		$this->session->delete('cart_payment_id');

	}

	/**
	 * 删除购物车商品
	 * @param  string $sessionId
	 */
	public function deleteSessionCart($sessionId) {

		if( !empty( $sessionId ) ){
			$this->db_ebmaster_write->where('session_id', $sessionId);
			$this->db_ebmaster_write->delete('cart');
			//清空MC
			$this->_clearMemcacheByCart();
		}

		return TRUE ;
	}

	/**
	 * 根据用户id删除购物车商品
	 * @param integer $userId 用户id
	 */
	public function deleteUserCart($userId) {
		$userId = (int)$userId ;
		if( $userId === (int)( $this->_user['user_id'] ) ){
			$this->db_ebmaster_write->where('user_id',$userId);
			$this->db_ebmaster_write->delete('cart');
			//清空MC
			$this->_clearMemcacheByCart();
		}

		return TRUE ;
	}

	/**
	 * 删除购物车商品
	 * @param array $recIds
	 * @return TRUE
	 *
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function deleteCartById( $recIds ){
		if(!is_array($recIds)){
			$recIds = array( $recIds );
		}

		if( !empty($recIds) && is_array( $recIds ) ){
			$this->db_ebmaster_write->where_in( 'id', $recIds );
			$this->db_ebmaster_write->delete( 'cart' );

			//获取购物车信息
			$list = $this->loadCartProduct();
			// 是否更新缓存
			$isChange = FALSE ;
			foreach ( $recIds as $id ){
				if( isset( $list[ $id ] ) ){
					unset( $list[ $id ] );
					$isChange = TRUE ;
				}else{
					//如果购物车没有数据 说明有问题 全部清空mc 关闭 这种情况不存在
					//$this->_clearMemcacheByCart();
				}
			}

			if( $isChange ){
				//更新缓存 不清空缓存
				$this->_setMemcacheByCart( $list );
			}
		}

		return TRUE ;
	}


	/**
	 * 获取 购物车主键ID 获取购物车信息
	 * @param int $id
	 * @return array()
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getCartInfoById($id){
		$result = array();//获取购物车信息

		$list = $this->loadCartProduct();

		if( isset( $list[ $id ] ) ){
			$result = $list[ $id ] ;
		}

		return $result;
	}

	/**
	 * 加入购物车
	 * @param array $item 商品信息
	 */
	public function insertToCart( $item ) {
		//判断当商品的购买数量为空的时候，就不让插入数据库
		if(empty($item['qty'])) { return 0; }

		$info = array(
				'user_id' => empty($this->_user)?0:$this->_user['user_id'],
				'session_id' => $this->_sessionId,
				'product_id' => id2name('pid',$item,0),
				'category_id' =>  id2name('category_id',$item,0),
				'sku' => id2name('sku',$item),
				'goods_name' => addslashes(id2name('goodsName',$item)),
				'purchase_price' => id2name('purchasePrice',$item,0),
				'cost_price' => id2name('costPrice',$item,0),
				'market_price' => id2name('finalmarketPrice',$item,0),
				'final_price' => id2name('finalPrice',$item,0),
				'goods_number' => id2name('qty',$item,0),
				'pro_type' => id2name('finalPromoteType',$item,0),
				'pro_id' => id2name('finalPromoteId',$item,0),
				'binding_pid' => id2name('bindingPid',$item,0),
				'order_to' => id2name('warehouse',$item,''),
				'favorable_gift_id' => id2name('favorable_gift_id',$item,0),
				'update_time' => HelpOther::requestTime(),
				'create_time' => HelpOther::requestTime(),
				'end_time' => id2name('seckillEndTime',$item,0),
		);
		//判断秒杀商品秒杀商品的信息
		$this->db_ebmaster_write->insert('cart',$info);
		//清空MC缓存
		$this->_clearMemcacheByCart();
		return $this->db_ebmaster_write->insert_id();
	}

	/**
	 * 获取购物车主键ID 获取购物车信息
	 * @param int $id
	 * @return array()
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public function getCartRow( $recId ){
		return $this->getCartInfoById( $recId );
	}

	/**
	 * 更具用户sessionId获取购物车商品
	 * @param  integer $sessionId 用户sessionId
	 * @author qcn
	 * @return array
	 */
	public function getCartBySession( $sessionId ) {
		$sessionId = trim( $sessionId );
		$list = array();
		if( $sessionId === trim( $this->_sessionId ) ) {
			$list = $this->memcache->get( self::MEM_KEY_CART_SESSION_INFO , $sessionId );
			if( $list === false) {
				//由于主从库有延迟 因此这里 读 主库
				$this->db_ebmaster_write->from('cart');
				$this->db_ebmaster_write->where('session_id',$sessionId);
				$this->db_ebmaster_write->order_by('id','asc');
				$list = $this->db_ebmaster_write->get()->result_array();
				$list = reindexArray( $list , 'id' );
				$this->memcache->set( self::MEM_KEY_CART_SESSION_INFO , $list , $sessionId );
			}
		}
		return $list;
	}

	/**
	 * 更具用户id获取购物车商品
	 * @param  integer $userId 用户id
	 * @author qcn
	 * @return array
	 */
	public function getCartByUser( $userId ) {
		$userId = (int) $userId ;

		$list = array();

		if( $userId === (int)( $this->_user['user_id'] ) ) {
			$list = $this->memcache->get( self::MEM_KEY_CART_UID_INFO , $userId );
			if($list === false) {
				//由于主从库有延迟 因此这里 读 主库
				$this->db_ebmaster_write->from('cart');
				$this->db_ebmaster_write->where('user_id',$userId);
				$this->db_ebmaster_write->order_by('id','asc');
				$list = $this->db_ebmaster_write->get()->result_array();
				$list = reindexArray( $list , 'id' );
				$this->memcache->set( self::MEM_KEY_CART_UID_INFO , $list , $userId );
			}
		}

		return $list;
	}

	/**
	 * 清空购物车的缓存   session 和 uid 的session 全部清空
	 */
	private function _clearMemcacheByCart(){
		$uid = empty( $this->_user['user_id'] ) ? 0 : (int) $this->_user['user_id'];
		//删除uid 的信息
		if( $uid !== 0 ){
			$this->memcache->delete( self::MEM_KEY_CART_UID_INFO , $uid );
		}
		//删除session 信息
		if( !empty( $this->_sessionId ) ){
			$this->memcache->delete( self::MEM_KEY_CART_SESSION_INFO  , trim( $this->_sessionId ) );
		}
		return TRUE ;
	}

	/**
	 * 网购物车添加数据
	 * @param array $list
	 */
	private function _setMemcacheByCart( $list ){
		if( is_array( $list ) ){
			$userId = isset( $this->_user['user_id'] ) ? (int)$this->_user['user_id'] : 0 ;
			if( $userId > 0 ) {
				$this->memcache->set( self::MEM_KEY_CART_UID_INFO , $list , $userId );
			} else {
				$this->memcache->set( self::MEM_KEY_CART_SESSION_INFO , $list , trim( $this->_sessionId ) );
			}
		}
		return $list;
	}

	//---------------------card model 走DB的 方法 end-------------------------------

	/**
	 * 获取赠品的id
	 * @param array $favorableGiftIds 商品赠品对应的coupon_effect的id
	 * @return [type]			[description]
	 */
	public function getGiftById( $giftCouponEffectId ) {
		$return = array();
		if( $giftCouponEffectId > 0){
			$cacheKey = "idx_cartmodel_getgiftbyid_%s";
			$return = $this->memcache->get($cacheKey, $giftCouponEffectId);
			if( $return === FALSE || !is_array( $return ) ){
				$this->db_ebmaster_read->select('id, value');
				$this->db_ebmaster_read->from('coupon_effect');
				$this->db_ebmaster_read->where('id', $giftCouponEffectId);
				$this->db_ebmaster_read->where('status', 1);
				$list = $this->db_ebmaster_read->get()->row_array();
				$return = array();
				if( !empty($list) && isset( $list['id'] )&& isset( $list['value'] ) ){
					$value = json_decode(stripslashes( $list['value'] ), true);
					$value['id'] = (int) $list['id'] ;
					$return[ (int) $list['id'] ] = $value ;
					$this->memcache->set($cacheKey, $return, $giftCouponEffectId);
				}
			}
		}
		return $return;
	}


	// 以下为老版代码

	/**
	 * 判断 订阅  coupon_code 是否合法
	 * @param array $subscribeInfo
	 * @return boolean
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 * /
	protected function _checkSubscribeCouponAvailable($subscribeInfo) {
		$now = time();

		if($now > strtotime('2015-09-30 23:59:59')) {
			$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
			$this->_couponMessage = lang('coupon_code_expired');
			return false;
		} elseif($this->_totalPrice < 30) {
			$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
			$this->_couponMessage = lang('coupon_sub_cart_total');
			return false;
		} elseif($subscribeInfo['code_status'] == 1) {
			$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
			$this->_couponMessage = lang('coupon_code_used');
			return false;
		}

		return true;
	}*/

	/*
	 * 非订阅 coupon  是否合法
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 * /
	protected function _checkNormalCouponAvailable($coupon) {
		$now = time();
		//实例化 CouponModel
		$couponObj = CouponModel::getInstanceObj();
		if(empty($coupon)) {
			$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
			$this->_couponMessage = lang('coupon_code_not_exist');
			return false;
		} elseif($coupon['start_time'] > $now) {
			$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
			$this->_couponMessage = lang('coupon_code_not_start');
			return false;
		} elseif($coupon['end_time'] !=0 && $coupon['end_time'] < $now) {
			$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
			$this->_couponMessage = lang('coupon_code_expired');
			return false;
		} elseif($coupon['act_status'] != 1) {
			$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
			$this->_couponMessage = lang('coupon_code_expired');
			return false;
		} elseif($coupon['max_use_times'] > 0 && $coupon['max_use_times'] <= $coupon['cur_use_times']) {
			$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
			$this->_couponMessage = lang('coupon_code_times_limit');
			return false;
		} elseif($coupon['max_use_times_customer'] > 0 && !empty($this->_user) && $coupon['max_use_times_customer'] <= ( $couponObj->getCouponUsedTimeByEmail($coupon['coupon_code_id'],$this->_user['email'] ) ) ) {
			$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
			$this->_couponMessage = lang('coupon_code_times_limit');
			return false;
		} elseif(strpos($coupon['website_code'],$this->_languageCode) === false) {
			$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
			$this->_couponMessage = lang('coupon_code_not_stuitable');
			return false;
		}

		return true;
	}*/

	/**
	 * coupon 运用 sku 上
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 * /
	protected function _applyCouponSku($coupon) {
		$coupon['quota_level'] = json_decode($coupon['quota_level']);

		//coupon作用于哪些pid
		$pids = explode(',',$coupon['range_value']);

		$fitCount = 0;
		$priceFitRange = 0;
		foreach( $this->_goodsList as $goods ) {
			if(!in_array($goods['pid'], $pids)) { continue; }
			$fitCount++;
			$priceFitRange += $goods['priceSubtotal'];
		}

		$gift = array();
		$priceDiscountAmount = 0;
		//打折
		if($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_REDUCE) {
			if($coupon['range_operator'] == 1) {
				if($fitCount > 0) {
					$priceDiscountAmount = sprintf("%.2f",$coupon['act_type_ext']);
				} else {
					$this->_removeSessionCouponInfo();
					$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
					$this->_couponMessage = lang('coupon_code_not_stuitable');
					return false;
				}
			} elseif($coupon['range_operator'] == 2) {
				$priceDiscountAmount = sprintf("%.2f",$coupon['act_type_ext']);
			} else {
				$this->_removeSessionCouponInfo();
				$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
				$this->_couponMessage = lang('coupon_code_not_stuitable');
				return false;
			}
			$priceDiscountAmount = floatval($priceDiscountAmount);
			$priceDiscountAmount = max($priceDiscountAmount, 0);
		//满减
		} elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_DISCOUNT) {
			if($coupon['range_operator'] == 1) {
				if($fitCount > 0) {
					$priceDiscountAmount = sprintf("%.2f", $this->_totalPrice * (1 - $coupon['act_type_ext'] / 100));
				} else {
					$this->_removeSessionCouponInfo();
					$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
					$this->_couponMessage = lang('coupon_code_not_invalid');
					return false;
				}
			} elseif($coupon['range_operator'] == 2) {
				$priceDiscountAmount = sprintf("%.2f", $priceFitRange * (1 - $coupon['act_type_ext'] / 100));
			} else {
				$this->_removeSessionCouponInfo();
				$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
				$this->_couponMessage = lang('coupon_code_not_invalid');
				return false;
			}
			$priceDiscountAmount = floatval($priceDiscountAmount);
			$priceDiscountAmount = max($priceDiscountAmount,0);
		//赠品
		} elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_GIFT && $this->_totalPrice >= $coupon['act_type_ext'] && $this->_checkCouponSku($coupon['range_value'])) {
			$gift = CouponModel::getInstanceObj()->getCouponGiftInfo($coupon['act_id'], $this->_languageId);
		} elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_COND) {
			$enabledItem = array();
			$enabledItemDiscount = array();
			foreach($coupon['quota_level'] as $item){
				if($this->_totalPrice>=$item->quota_amount){
					$enabledItem[] = $item->quota_amount;
					$enabledItemDiscount[$item->quota_amount]=$item->quota_type == 1 ? $item->quota_rate : $this->_totalPrice * $item->quota_rate * 0.01;
				}
			}
			if (empty($enabledItem)) {
				$this->_removeSessionCouponInfo();
				$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
				$this->_couponMessage = lang('coupon_code_not_stuitable');
				return false;
			}else{
				rsort($enabledItem);
				$priceDiscountAmount+= $enabledItemDiscount[$enabledItem[0]];
			}
		} else {
			$this->_removeSessionCouponInfo();
			$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
			$this->_couponMessage = lang('coupon_code_not_invalid');
			return false;
		}

		if($priceDiscountAmount > 0) {
			$this->_discountList[] = array(
					'name' => $coupon['act_name_language'],
					'amount' => $priceDiscountAmount,
			);
			$this->_couponDiscountAmount+=$priceDiscountAmount;
		}
		if(!empty($gift)) {
			$this->_gift = $gift;
		}
	}*/

	/**
	 * coupon 运用 价格 上
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 * /
	protected function _applyCouponPrice($coupon){
		$coupon['quota_level'] = json_decode($coupon['quota_level']);
		if ($this->_totalPrice < $coupon['checkout_value_min'] || ($coupon['checkout_value_max'] > 0 && $this->_totalPrice > $coupon['checkout_value_max'])) {
			$this->_removeSessionCouponInfo();
			$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
			$this->_couponMessage = lang('coupon_code_not_invalid');
			return false;
		}

		$gift = array();
		$priceDiscountAmount = 0;
		if($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_REDUCE){
			$priceDiscountAmount = sprintf("%.2f",$coupon['act_type_ext']);
			$priceDiscountAmount = floatval($priceDiscountAmount);
			$priceDiscountAmount = max($priceDiscountAmount,0);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_DISCOUNT){
			$priceDiscountAmount = sprintf("%.2f", $this->_totalPrice * (1 - $coupon['act_type_ext'] / 100));
			$priceDiscountAmount = floatval($priceDiscountAmount);
			$priceDiscountAmount = max($priceDiscountAmount,0);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_GIFT && $this->_totalPrice >= $coupon['act_type_ext']){
			$gift = CouponModel::getInstanceObj()->getCouponGiftInfo($coupon['act_id'],$this->_languageId);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_COND){
			$enabledItem = array();
			$enabledItemDiscount = array();
			foreach($coupon['quota_level'] as $item){
				if($this->_totalPrice>=$item->quota_amount){
					$enabledItem[] = $item->quota_amount;
					$enabledItemDiscount[$item->quota_amount]=$item->quota_type == 1 ? $item->quota_rate : $this->_totalPrice * $item->quota_rate;
				}
			}
			if (empty($enabledItem)) {
				$this->_removeSessionCouponInfo();
				$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
				$this->_couponMessage = lang('coupon_code_not_stuitable');
				return false;
			}else{
				rsort($enabledItem);
				$priceDiscountAmount+= $enabledItemDiscount[$enabledItem[0]];
			}
		} else {
			$this->_removeSessionCouponInfo();
			$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
			$this->_couponMessage = lang('coupon_code_not_stuitable');
			return false;
		}
		if($priceDiscountAmount > 0){
			$this->_discountList[] = array(
					'name' => $coupon['act_name_language'],
					'amount' => $priceDiscountAmount,
			);
			$this->_couponDiscountAmount+=$priceDiscountAmount;
		}
		if(!empty($gift)){
			$this->_gift = $gift;
		}
	}*/

	/**
	 * coupon 运用所有
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 * /
	protected function _applyCouponAll($coupon){
		$coupon['quota_level'] = json_decode($coupon['quota_level']);
		$gift = array();
		$priceDiscountAmount = 0;
		if($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_REDUCE){
			$priceDiscountAmount = sprintf("%.2f",$coupon['act_type_ext']);
			$priceDiscountAmount = floatval($priceDiscountAmount);
			$priceDiscountAmount = max($priceDiscountAmount,0);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_DISCOUNT){
			$priceDiscountAmount = sprintf("%.2f", $this->_totalPrice * (1 - $coupon['act_type_ext'] / 100));
			$priceDiscountAmount = floatval($priceDiscountAmount);
			$priceDiscountAmount = max($priceDiscountAmount,0);
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_GIFT && $this->_totalPrice >= $coupon['act_type_ext']){
			$gift = CouponModel::getInstanceObj()->getCouponGiftInfo($coupon['act_id'],$this->_languageId );
		}elseif($coupon['act_type'] == self::PROMOTE_ACT_TYPE_PRICE_COND){

			$enabledItem = array();
			$enabledItemDiscount = array();
			foreach($coupon['quota_level'] as $item){
				if($this->_totalPrice>=$item->quota_amount){
					$enabledItem[] = $item->quota_amount;
					$enabledItemDiscount[$item->quota_amount]=$item->quota_type == 1 ? $item->quota_rate : $this->_totalPrice * $item->quota_rate * 0.01;
				}
			}
			if (empty($enabledItem)) {
				$this->_removeSessionCouponInfo();
				$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
				$this->_couponMessage = lang('coupon_code_not_stuitable');
				return false;
			}else{
				rsort($enabledItem);
				$priceDiscountAmount+= $enabledItemDiscount[$enabledItem[0]];
			}
		} else {
			$this->_removeSessionCouponInfo();
			$this->_couponCode = ''; //防止不能使用的时候将这个值记录在数据库中
			$this->_couponMessage = lang('coupon_code_not_stuitable');
			return false;
		}

		if($priceDiscountAmount > 0){
			$this->_discountList[] = array(
					'name' => $coupon['act_name_language'],
					'amount' => $priceDiscountAmount,
			);
			$this->_couponDiscountAmount+=$priceDiscountAmount;
		}
		if(!empty( $gift )){
			$this->_gift = $gift;
		}
	}*/
}
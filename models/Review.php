<?php
namespace app\models;

use Yii;
use app\models\common\EbARModel as baseModel;
use app\components\helpers\ArrayHelper;
use app\components\helpers\HelpOther;
use app\components\helpers\HelpUrl;
use app\components\helpers\OtherHelper;
/**
 * Model for review.
 * @author Terry Lu
 */
class Review extends baseModel {
		
	private static $_tableName = 'comment';
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
	 * 获取产品的评论总数
	 * @param int $pid
	 * @param int $languageId
	 * @return int
	 * @author Terry
	 */
	public function getProReviewCount($pid,$languageId){
		$result = 0 ; 
		if( self::ON_AND_OFF === 1  ){
			$cacheKey = "product_review_count_%s_%s";
			$cacheArray = array( $pid , $languageId ) ;
			$result = $this->memcache->get( $cacheKey , $cacheArray );
			if( $result === FALSE ){
				$query= self::find()->select('count(*) totalNum')->from('comment');
				$query->where( ['product_id' => $pid, 'status'=> 1 , 'language_id' =>$languageId ] );
				$result = $query->asArray()->one();
				
				//$result = $this->db_ebmaster_read->select('count(*) totalNum')->from('comment')->
				//		where('product_id', $pid)->where('status',1)->where('language_id',$languageId)->get()->row()->totalNum;
				$this->memcache->set( $cacheKey , $result['totalNum'] , $cacheArray );
			}
		}
		return $result;
	}

	/**
	 * 获取产品的评论列表，按分页获取
	 * @param int||array $pids
	 * @param array $params
	 * @param boolean $cache
	 * @return array
	 * @author Terry
	 */
	public function getProReviewList($pids,$params,$cache=TRUE){
		$return = array();
		if( self::ON_AND_OFF === 1  ){
			if ($cache) {
				$return = $this->memcache->ebMcFetchData($pids,self::MEM_KEY_PRO_REVIEW_LIST,array($this,'getProReviewList'),$params);
			}else{
				$return = array();

				foreach ($pids as $pid) {
					$totalCount = $this->getProReviewCount($pid, $params[0]);
					$start = ($params[1]-1)*self::PAGE_SIZE;
					$totalPage = ceil($totalCount/self::PAGE_SIZE);
					$return[$pid]['page']['pageNumber'] = $params[1];
					$return[$pid]['page']['totalPage'] = $totalPage;

					$commentResArr = $this->db_ebmaster_read->select('id,user_name,title,content,support_count,unsupport_count,add_time')->from('comment')->where('product_id', $pid)->where('status',1)->where('language_id', $params[0])->order_by('add_time','desc')->limit(self::PAGE_SIZE,$start)->get()->result_array();
					foreach ($commentResArr as $commentRow) {
						$commentInfo = array();
						$commentInfo['id'] = $commentRow['id'];
						$commentInfo['addDate'] = date('M d,Y',$commentRow['add_time']);
						$commentInfo['title'] = $commentRow['title'];
						$commentInfo['content'] = eb_htmlspecialchars($commentRow['content']);
						$commentInfo['like'] = $commentRow['support_count'];
						$commentInfo['unlike'] = $commentRow['unsupport_count'];
						$commentInfo['usname'] = $commentRow['user_name'];
						$return[$pid]['review_list'][] = $commentInfo;
					}
				}
			}
		}
		return $return;
	}

	/**
	 * 添加评论
	 * @param int $pid
	 * @param int $languageId
	 * @param str $title
	 * @param str $content
	 * @param str $captcha
	 * @return array
	 * @author Terry
	 */
	public function addProReview($pid,$languageId,$title,$content,$captcha){
		$return = array('success'=>TRUE,'msg'=>'','feedback'=>array());

		if( self::ON_AND_OFF === 1  ){
			$uid = $this->m_app->getCurrentUserId();
			$userModel = new UserModel();
			if (!$this->m_app->checkUserLogin()) {
				$return['success'] = false;
				$return['feedback']['noLogin'] = TRUE;
			} elseif($this->checkReviewed($pid,$uid,$languageId)){//检查是否已经评论过
				$return['success'] = false;
				$return['msg'] = lang('hadReviewed');
			} elseif (!$userModel->checkProductPurchased($uid, $pid)) {//检查是否未买过该商品
				$return['success'] = false;
				$return['msg'] = lang('reviewNeedBuy');
			} elseif ($title == '' || $content == '' || $captcha=='') {
				$return['success'] = false;
				$return['msg'] = lang('input_requied_fields');
			}elseif(!$this->_checkCaptcha($captcha) && $captcha!='noCaptcha'){
				$return['success'] = false;
				$return['msg'] = lang('invalid_captcha');
			}else{
				$curTime = HelpOther::requestTime();
				$userModel = new UserModel();
				$userInfo = current($userModel->getUserInfoByIds($uid));
				$this->_createProReview(array(
					'product_id' => $pid,
					'user_id' => $uid,
					'user_name' => $userInfo['user_name'],
					'language_id' => $languageId,
					'title' => $title,
					'content' => $content,
					'add_time' => $curTime,
					'ip_address' => $this->input->ip_address(),
					'status' => 0,
				));
				$return['msg'] = lang('subOk');
				$return['feedback']['addDate'] = date('M d,Y',$curTime);
			}
		}
		return $return;
	}

	//插入评论（数据库操作）
	private function _createProReview($reviewInfo){
		$result = array();
		if( self::ON_AND_OFF === 1  ){
			$result = $this->db_ebmaster_write->insert('comment',$reviewInfo);
		}
		return $result ;
	}

	//判断验证码
	private function _checkCaptcha($inputCaptcha) {
		$captcha = new CaptchaModel(ROOT_PATH . 'data/captcha/');
		return $captcha->check_word( $inputCaptcha );
	}

	/**
	 * 用户评论的顶和踩（数据库操作）
	 * @param int $commentId
	 * @param boolean $isSupport  true=>顶， false=>踩
	 * @return int 顶或者踩的数量
	 * @author Terry
	 */
	private function _supportOrUnsupportReview($commentId,$isSupport){
		$result = 0 ;
		if( self::ON_AND_OFF === 1  ){
			$supportCountFiled = $isSupport?'support_count':'unsupport_count';
			$this->db_ebmaster_write->set($supportCountFiled,$supportCountFiled.'+1',false)->where('id',$commentId)->update('comment');//  更新comment表中的顶踩数
			$this->db_ebmaster_write->set('user_id', $this->m_app->getCurrentUserId())->set('comment_id',$commentId)->set('ip',$this->input->ip_address())->set('support_type',$isSupport?1:0)->set('create_time',  HelpOther::requestTime())->insert('comment_support');// 插入数据到comment_support（记录顶踩记录，用来判断用户是否重复顶和踩）
			$item =$this->db_ebmaster_write->select($supportCountFiled)->from('comment')->where('id',$commentId)->limit(1)->get()->row_array();
			$result = id2name($supportCountFiled,$item,0);
		}
		return $result ;
	}

	public function checkReviewed($pid,$uid,$languageId){
		$result = FALSE ;
		if( self::ON_AND_OFF === 1  ){
			$res = $this->db_ebmaster_read->from('comment')->where('product_id', $pid)->where('language_id',$languageId)->where('user_id',$uid)->get()->row();
			$result = $res?TRUE:FALSE;
		}
		return $result ;
	}

	/**
	 * 用户评论的顶和踩
	 * @param int $commentId
	 * @param boolean $isSupport  true=>顶， false=>踩
	 * @return array
	 * @author Terry
	 */
	public function supportOrUnsupportReview($commentId,$isSupport){
		$return = array('success'=>TRUE,'msg'=>'','feedback'=>array());
		if( self::ON_AND_OFF === 1  ){
			$uid = $this->m_app->getCurrentUserId();
			if (!$this->m_app->checkUserLogin()) {
				$return['success'] = false;
				$return['feedback']['noLogin'] = TRUE;
			}elseif($this->checkReviewSupported($commentId,$uid)){//检查是否顶（踩）过。
				$return['success'] = false;
				$return['msg'] = lang('hadSupportReview');
			}else{
				$return['msg'] = lang('supportReviewOk');
				$return['feedback']['count'] = $this->_supportOrUnsupportReview($commentId,$isSupport);
			}
		}
		return $return;
	}

	public function checkReviewSupported($commentId,$uid){
		$result = FALSE ;
		if( self::ON_AND_OFF === 1  ){
			$count = $this->db_ebmaster_read->from('comment_support')->where('comment_id',$commentId)->where('user_id',$uid)->count_all_results();
			$result = ( $count > 0 ) ? TRUE : FALSE ;
		}
		return $result ;
	}

	/**
	 * 获得近期评论商品列表
	 * @param inc $language_id
	 * @param inc $limit
	 * @return array 返回评论商品列表
	 * @author lucas
	 */
	public function getRecentlyReviewList( $language_id, $limit = 10 ){
		$list = array();
		if( self::ON_AND_OFF === 1  ){
			$cacheKey = "get_recently_review_list_%s_%s";
			$cacheParams = array( $language_id, $limit );
			$list = $this->memcache->get( $cacheKey, $cacheParams );
			if( $list === false ){
				$query = static::find();
				$query->select(['product_id', 'user_name', 'title']);
				$query->where(['language_id'=>$language_id, 'status'=>1]);
				$query->orderBy(['sort_order'=>	SORT_ASC, 'support_count'=> SORT_ASC, 'add_time' => SORT_DESC ]);
				$query->limit($limit);
				$list = $query->asArray()->all();
	
				$list = OtherHelper::eb_htmlspecialchars( $list );

				$this->memcache->set( $cacheKey , $list , $cacheParams );
			}
		}
		return $list;
	}

	/**
	 * 获得用户评论列表
	 * @param inc $userId
	 * @param inc $limit
	 * @return array 返回评论商品列表
	 * @author lucas
	 */
	public function getUserReviewList( $userId, $page = 1){
		$result = array(array(),0);
		if( self::ON_AND_OFF === 1  ){
			if( empty( $userId ) ){
				return array();
			}
			$limit = 10;
			$start = ($page-1)*$limit;

			$this->db_ebmaster_read->from('comment');
			$this->db_ebmaster_read->where('status',1);
			$this->db_ebmaster_read->where('user_id',$userId);
			$count = $this->db_ebmaster_read->count_all_results('',SQL_EXECUTE_RETAIN_CONDITION);

			$this->db_ebmaster_read->order_by('id','desc');
			$this->db_ebmaster_read->limit($limit,$start);
			$query = $this->db_ebmaster_read->get();
			$list = eb_htmlspecialchars($query->result_array());
			$result =  array($list,$count);
		}
		return $result;
	}

	const PAGE_SIZE = 10;
	const MEM_KEY_PRO_REVIEW_LIST = 'proReview%s%s%s';//proReview{$product_id}{$languageId}{$page} 产品评论信息（多语言、分页）
	const ON_AND_OFF = 1; //1 是开启  0是关闭
}

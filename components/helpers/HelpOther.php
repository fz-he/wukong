<?php
/**
 *
 * @author BRYAN - NYD  <ningyandong@hofan.cn>
 */
namespace app\components\helpers;

class HelpOther{
	/**
	 * 获取当前服务器的时间戳地址
	 *
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public static function requestTime() {
		return (int)$_SERVER[ 'REQUEST_TIME' ];
	}

	/**
	 * 二进制占位
	 * @param int $numberOfBits 必须大于1
	 *
	 * @return int   $result
	 * 举例
	 * 		若 $numberOfBits 1 则 $result=1
	 * 		若 $numberOfBits 2 则 $result=10
	 * 		若 $numberOfBits 3 则 $result=100
	 *		若 $numberOfBits 4 则 $result=1000
	 *		....
	 *		若 $numberOfBits 7 则 $result=1000000
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 *
	 */
	public static function occupied( $numberOfBits = 1 ){
		$result = 1;
		if( $numberOfBits>1 ){
			$count = $numberOfBits-1;
			for( $i=1;$i<=$count; $i++ ){
				$result .= 0 ;
			}
		}

		return (int)$result;
	}


	/**
	 * 获取二进制数中的某一位(即从左边算起 第一位 那么传参数中的 $bit=1 )是否是1
	 * 1则返回TRUE 0返回FALSE
	 * @param int $binaryNumber //二进制数字
	 * @param int $bit	//位数
	 * @return boolean   $result//TRUE/FALSE
	 * 举例  $binaryNumber = 10101
	 * 		若 $bit 1 则 $result= TRUE
	 * 		若 $bit 2 则 $result=FALSE
	 * 		若 $bit 3 则 $result=TRUE
	 *		若 $bit 4 则 $result=FALSE
	 *		若 $bit 5 则 $result=TRUE
	 *
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public static function getBinarySystemIsTrueByBit( $binaryNumber , $bit = 1 ){
		$result = FALSE ;
		$bit = (int) $bit ;
		//判断位数是否大于传送过来的数字总长度
		if( ( strlen( $binaryNumber ) >= $bit ) &&  ( $bit >= 1 ) ){
			$result = (boolean) substr( $binaryNumber , -$bit , 1 ) ;
		}
		return $result ;
	}

	/**
	 * 向下(基数参考 )取整的函数   即( $count - $count%$base ) ;
	 * 		例如 	$count = 11.5  $base = 1 ; $result = 11;
	 * 			$count = 13  $base = 3 ; $result = 12;
	 * 			$count = 16  $base = 5 ; $result = 15;
	 *			$count = 34  $base = 10 ; $result = 30;
	 * @param int $count 需要格式化的数据
	 * @param int $base 基数 默认是1
	 *
	 * @return int $result
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public static function getRoundedDown( $count , $base = 1 ){
		//格式化参数
		$count = (int)$count ;
		$base = (int)$base ;
		//初始化结果数据
		$result = 0 ;
		if( $count >= $base ){
			//结果 = 原数据  - 原数据%基数
			$result = ( $count - $count%$base );
		}
		return $result ;
	}

	/**
	 * json返回
	 * @param  array   $dataArray 数据数组
	 * @param  string  $message 信息
	 * @param  integer $errorCode 错误码
	 *
	 * @return json
	 * @author BRYAN - qcn  <qianchangnian@hofan.cn>
	 */
	public static function returnJson(  $dataArray = array() , $message = '', $errorCode = 0 ) {
		exit(
				json_encode(array(
					'errorCode' => $errorCode,
					'msg' => $message,
					'data' => $dataArray
					) )
			);
	}

	/**
	 * 检测电话号码时候合法
	 * 1） 电话长度不可小于5位
	 * 2） 只能包含 + - 括号（半角和全角） 空格 数字
	 * 3） 号码的全部数字不能为单一字符重复，如“111111”、“888888”
	 *
	 * @param  string $mobileNumber 电话号码
	 * @return boolean
	 * @author [SaviorQian] <[qianchangnian@hofan.cn]>
	 */
	public static function checkMobileNumber($mobileNumber = '') {
		// 初始化返回标记
		$mark = false;

		// 字符串为空的时候
		if(empty($mobileNumber)) { return $mark; }

		// 判断所含有的字符是否合法
		$mark = preg_match("/^[\d\s-+\)\(\）\（]+$/", $mobileNumber) ? true:false;
		if(!$mark) { return $mark; }

		// 判断长度是不是合法（长度大于5个字符）
		$mark = strlen($mobileNumber)>4 ? true:false;
		if(!$mark) { return $mark; }

		// 判断字符是否是单一字符
		$mobileNumber = trim($mobileNumber);
		$mobileNumber = str_split($mobileNumber);
		$mobileNumber = array_flip($mobileNumber);
		$mark = count($mobileNumber) > 1 ? true:false;
		 return $mark;
	}

	/**
	 * 检查Refer
	 * @param string $method //方法名
	 * @return boolean
	 * @assert ( 'ajaxcomment' ) == true
	 */
	public static function checkrefer(){
		global $lang_basic_url ;
		if( !defined( 'ENABLE_CHECKREFER' ) || ENABLE_CHECKREFER == false ){
			return TRUE;
		}
		if( empty( $_SERVER['HTTP_REFERER'] ) ) {
			return FALSE;
		}
		$referUrl = trim( $_SERVER['HTTP_REFERER'] , 'http://');
		$referUrl = trim( $referUrl , 'https://');
		$result = FALSE ;
		foreach ( $lang_basic_url as $k => $v ){
			$v = trim( $v , '/' );
			if(defined( 'EBPLATEFORM' ) && EBPLATEFORM == 1 && $k == 'us') {
				$v = trim( $v , '/'.$k );
			}
			$v = trim( $v , 'http://' );
			if( strpos( $referUrl , $v ) === 0 ){
				$result = TRUE ;
				break;
			}
		}

		return $result ;
	}

	/**
	 * 发送系统邮件统一方法
	 * @param string $addressee 收信人
	 * @param string $eid 邮件模板EID
	 * @param array $contentParam 模版自定义内容
	 * @return boolean || OK
	 * @author lucas
	 */
	public static function sendSystemEmail( $addressee, $eid, $contentParam ){
		$result = false;
		// 字符串为空的时候
		if( empty($addressee) || empty( $eid ) ) { return $result; }

		// 判断收件邮箱是否合法
		$mark = preg_match("/^[0-9a-z][0-9a-z-._]+@{1}[0-9a-z.-]+[a-z]{2,4}$/i", $addressee) ? true : false;
		if( !$mark ) { return $mark; }

		//验证模板内容和推荐商品数据
		if( !is_array( $contentParam ) ){
			return $result;
		}

		$CI = CI_Controller::get_instance();
		$CI->load->module('cheetahmail/ApiService');

		$client = new ApiService();

		// 首先进行登录操作
		$client->login('app');

		// 模板参数
		$ebmtrigger1 = array(
			// 'sid' => 2094472268,
			'email' => $addressee,
			'eid' => $eid,
		);

		$data = array_merge( $ebmtrigger1, $contentParam );

		$result = $client->callMethod('ebmtrigger1', $data);

		return $result;
	}
	
	// 计算多字节字符串长度
	public static  function getUtf8Strlen($string = null) { 
		// 将字符串分解为单元
		preg_match_all("/./us", $string, $match);
		// 返回单元个数
		return count($match[0]);
	}
	
	// 去掉分类名字中的 '&' ' ' , "'" , '/' 以在url中显示更友好
	public static  function filterCategoryName( $categoryName  = '') { 
		if( empty( $categoryName ) ){
			return '';
		}
		
		$categoryName = strtolower ( $categoryName );
		$categoryName = trim( $categoryName );
		//$search数组 里的字符串顺序很重要
		$search = array(  ' & ' ,  '& ' ,  ' &' , ' ' ,  '/'  );
		$categoryName = str_replace($search, '-', $categoryName );		
		$categoryName = str_replace( array("'" ), '', $categoryName );	
		$categoryName = htmlspecialchars( $categoryName );
		$categoryName =  HelpUrl::removeXSS( $categoryName );

		return $categoryName;
	}
	

}

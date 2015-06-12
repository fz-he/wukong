<?php
/*
 * @author BRYAN - NYD  <ningyandong@hofan.cn>
 */
namespace app\components\helpers;

use Yii;

class HelpUrl{
	//链接配置
	static $urlConfig = array();

	//读取配置文件
	public static function initConfig() {
		if( empty( self::$urlConfig ) ) {
			$params = Yii::$app->params;
			$_system_config = $params['system_config'];
			
			self::$urlConfig = & $_system_config;
		}
	}
	/**
	 * 获取EACHBUYER HOME地址
	 *
	 * @return string  返回图片URL
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public static function home() {
		return SITE_HOME_URL ;
	}

	/**
	 * 获取图片的地址
	 * @param string $imgName 图片相对地址
	 * @param string $size [45/70/155/170/350/500]
	 *
	 *
	 * @return string $rsUrl //返回URL
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public static function img( $imgName = '' , $imgSize=155 ){
		if( isset( $_SERVER[ 'REQUEST_URI' ] ) ){
			$headUrl = ( strpos( $_SERVER[ 'REQUEST_URI' ] , '/success?' ) === FALSE ) ? 'http://' : 'https://';
		}else{
			$headUrl = 'http://';
		}
		//图片支持的大小
		$imgSizes = array(
			45 => '45x45/' ,
			70 => '70x70/' ,
			155 => '155x120/' ,
			170 => '170x170/' ,
			176 => '176x176/' ,
			350 => '350x350/' ,
			500 => '500x500/' ,
		) ;
		$rsUrl = '' ;
		if( !empty( $imgSizes[ $imgSize ] ) ){
			self::initConfig();
			$staticType = self::$urlConfig[ 'url_config' ][ 'static_type' ] ;
			$rsUrl = $headUrl . self::$urlConfig[ 'url_config' ][ $staticType ]['img_url']['web_path'] . trim( $imgSizes[ $imgSize ] ) . trim( $imgName ) .'?v=' . self::$urlConfig[ 'url_config' ]['static_file_version'] ;
		}

		return $rsUrl ;
	}

	/**
	 * 获取eachbuyer 站点内 图片的地址
	 * @param string $imgName 图片相对地址
	 *
	 *
	 * @return string $rsUrl //返回URL
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public static function imgSite( $imgName = '' ){
		if( isset( $_SERVER[ 'REQUEST_URI' ] ) ){
			$headUrl = ( strpos( $_SERVER[ 'REQUEST_URI' ] , '/success?' ) === FALSE ) ? 'http://' : 'https://';
		}else{
			$headUrl = 'http://';
		}
		$rsUrl = '';
		if(! empty( $imgName ) ){
			self::initConfig();
			$staticType = self::$urlConfig[ 'url_config' ][ 'static_type' ] ;
			//随机返回一个数组中的下标
			$imgArr = self::$urlConfig[ 'url_config' ][ $staticType ]['img_site_url'] ;
			$index = array_rand( $imgArr , 1 );
			$rsUrl = $headUrl . $imgArr[ $index ] . trim( $imgName ) .'?v=' . self::$urlConfig[ 'url_config' ]['static_file_version'] ;
		}

		return $rsUrl ;
	}

	/**
	 * 获取CSS 路径
	 * @param string $cssFileName     //css文件名字
	 * @param string $relativePath     //CSS相对路径
	 *
	 * @return string $rsUrl //返回URL
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public static function css( $cssFileName = '' , $relativePath = 'css_v2'){
		if( isset( $_SERVER[ 'REQUEST_URI' ] ) ){
			$headUrl = ( strpos( $_SERVER[ 'REQUEST_URI' ] , '/success?' ) === FALSE ) ? 'http://' : 'https://';
		}else{
			$headUrl = 'http://';
		}
		//获取相对的路径
		$relativePath =  empty( $relativePath ) ? '' : trim( $relativePath ) . '/' ;
		//初始化系统配置文件
		self::initConfig();
		$staticType = self::$urlConfig[ 'url_config' ][ 'static_type' ] ;
		$rsUrl = $headUrl . self::$urlConfig[ 'url_config' ][ $staticType ]['css']['url'] . $relativePath . trim( $cssFileName ) .'?v=' . self::$urlConfig[ 'url_config' ]['static_file_version'] ;
		return $rsUrl ;
	}


	/**
	 * 获取JS 路径
	 * @param string $jsFileName     //JS文件名字
	 * @param string $relativePath     //相对路径
	 *
	 * @return string $rsUrl //返回URL
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public static function js( $jsFileName = '' , $relativePath = ''){
		if( isset( $_SERVER[ 'REQUEST_URI' ] ) ){
			$headUrl = ( strpos( $_SERVER[ 'REQUEST_URI' ] , '/success?' ) === FALSE ) ? 'http://' : 'https://';
		}else{
			$headUrl = 'http://';
		}
		//获取相对的路径
		$relativePath =  empty( $relativePath ) ? '' : trim( $relativePath ) . '/' ;
		//初始化系统配置文件
		self::initConfig();
		$staticType = self::$urlConfig[ 'url_config' ][ 'static_type' ] ;
		$rsUrl = $headUrl . self::$urlConfig[ 'url_config' ][ $staticType ]['js']['url'] . $relativePath . trim( $jsFileName ) .'?v=' . self::$urlConfig[ 'url_config' ]['static_file_version'] ;

		return $rsUrl ;
	}

	/**
	 * 获取eachbuyer 的相对路径
	 * @param string $relativePath  //获取相对路径
	 * @param array $params         //参数
	 * @param boolean $isShowVersion  //是否为链接添加版本信息
	 *
	 * @return string $rsUrl //返回URL
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public static function getUrl( $relativePath , $params = array() , $isShowVersion = TRUE ){
		self::initConfig();

		//Modified by Terry.
		global $lang_basic_url;
		$CI = & get_instance();
		$language_code = $CI->session->get('language_code');
		$rsUrl = $lang_basic_url[$language_code];
		$rsUrl .= empty($relativePath) ? '' : trim( $relativePath ) ;

		if( is_array( $params ) ){
			if( $isShowVersion === TRUE ){
				$params['v'] = self::$urlConfig[ 'url_config' ]['static_file_version'] ;
			}
			$rsUrl .= '?' . http_build_query( $params );
		}else{
			if( $isShowVersion === TRUE ){
				$rsUrl .= '?v=' . self::$urlConfig[ 'url_config' ]['static_file_version'] ;
			}
		}

		return $rsUrl;
	}

	/**
	 * 获取外部链接的绝对路径
	 * @param string $relativePath //绝对的URL
	 * @param array $params         //参数
	 *
	 * @return string $rsUrl //返回URL
	 * @author BRYAN - NYD  <ningyandong@hofan.cn>
	 */
	public static function absolutePath( $absolutePath , $params = array() , $isShowVersion = TRUE ){
		$rsUrl = '';
		if( !empty(  $absolutePath ) ){
			self::initConfig();
			$absolutePath = empty($absolutePath) ? SITE_HOME_URL : trim( $absolutePath ) ;
			if( strpos( $absolutePath , 'http://' ) === FALSE ){
				$absolutePath = 'http://' .$absolutePath;
			}
			$rsUrl = $absolutePath ;
			if( is_array( $params ) && !empty( $params ) ){
				if( $isShowVersion === TRUE ){
					$params['v'] = self::$urlConfig[ 'url_config' ]['static_file_version'] ;
				}
				$rsUrl .= '?' . http_build_query( $params );
			}else if(  $isShowVersion === TRUE ){
				$rsUrl .= '?v=' . self::$urlConfig[ 'url_config' ]['static_file_version'] ;
			}
		}

		return $rsUrl;
	}

	/**
	 * 检查是不是移动版访问
	 * @return  mix
	 * @author BRYAN - QCN  <qianchangnian@hofan.cn>
	 */
	public static function checkMobile($languageCode) {
		$requestUri = $_SERVER['REQUEST_URI'] . ( strpos ( $_SERVER['REQUEST_URI'] , '?')? '&from=site' : '?from=site') ;
		if( trim( $languageCode ) === 'us' ){
			redirect( 'http://m.' . COMMON_DOMAIN . $requestUri );
		}else {
			redirect( 'http://m.' . COMMON_DOMAIN . '/'. $languageCode . $requestUri );
		}
	}

	/**
	 * xss参数注入过滤
	 * @param  string $val 接受的参数
	 *
	 * @return string $val
	 * @author BRYAN - QCN  <qianchangnian@hofan.cn>
	 */
	public static function removeXSS($val) {
		// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
		// this prevents some character re-spacing such as <java\0script>
		// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
		// $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val); // 这里里面把逗号过滤了
		$val = preg_replace('/([\x00-\x08])/', '', $val);

		//替换转移字符 原标签=urlencode后的字符串  "<"="%3C";">"="%3E";"</"="%3C/"
		$strFilterUrlEncode = array( '%3C' , '%3E' , '%3C/' , '%22','(' , ')' ,'"','<' , '>' , 'cookie' , 'document' , 'script' , '%', ':','#','$','&','^', '[', ']', '{', '}', '€', '¥', '£', '*', '\\', "\n", "\r", "\t", );
		$val = str_replace( $strFilterUrlEncode , '', $val );

		// straight replacements, the user should never need these since they're normal characters
		// this prevents like <IMG SRC=@avascript:alert('XSS')>
		$search = 'abcdefghijklmnopqrstuvwxyz';
		$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$search .= '1234567890!@#$%^&*()';
		$search .= '~`";:?+/={}[]-_|\'\\';
		for ($i = 0; $i < strlen($search); $i++) {
			// ;? matches the ;, which is optional
			// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

			// @ @ search for the hex values
			$val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
			// @ @ 0{0,7} matches '0' zero to seven times
			$val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
		}

		// now the only remaining whitespace attacks are \t, \n, and \r
		$ra1 = array(
			'javascript',
			'vbscript',
			'expression',
			'applet',
			'meta',
			'xml',
			'blink',
			'link',
			'style',
			'script',
			'embed',
			'object',
			'iframe',
			'frame',
			'frameset',
			'ilayer',
			'layer',
			'bgsound',
			'base',
			'alertdocument',
		);
		$ra2 = array(
			'onabort',
			'onactivate',
			'onafterprint',
			'onafterupdate',
			'onbeforeactivate',
			'onbeforecopy',
			'onbeforecut',
			'onbeforedeactivate',
			'onbeforeeditfocus',
			'onbeforepaste',
			'onbeforeprint',
			'onbeforeunload',
			'onbeforeupdate',
			'onblur',
			'onbounce',
			'oncellchange',
			'onchange',
			'onclick',
			'oncontextmenu',
			'oncontrolselect',
			'oncopy',
			'oncut',
			'ondataavailable',
			'ondatasetchanged',
			'ondatasetcomplete',
			'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave',
			'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange',
			'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress',
			'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter',
			'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel',
			'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange',
			'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete',
			'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop',
			'onsubmit', 'onunload'
		);

		$ra = array_merge($ra1, $ra2);

		$found = true; // keep replacing as long as the previous round replaced something
		while ($found == true) {
			$val_before = $val;
			for ($i = 0; $i < sizeof($ra); $i++) {
				$pattern = '/';
				for ($j = 0; $j < strlen($ra[$i]); $j++) {
					if ($j > 0) {
						$pattern .= '(';
						$pattern .= '(&#[xX]0{0,8}([9ab]);)';
						$pattern .= '|';
						$pattern .= '|(&#0{0,8}([9|10|13]);)';
						$pattern .= ')*';
					}
					$pattern .= $ra[$i][$j];
				}
				$pattern .= '/i';
				$replacement = substr($ra[$i], 0, 2) . '_V_' . substr($ra[$i], 2); // add in <> to nerf the tag
				$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
				if ($val_before == $val) {
					// no replacements were made, so exit the loop
					$found = false;
				}
			}
		}

		return $val;
	}

	/**
	 * 分类页面url301和302跳转
	 * @param  array $data 跳转信息
	 * @param  string $str 跳转后缀链接 17948
	 */
	public static function url301And302Redirect($dataArray = array(), $str = '') {
		if(is_array($dataArray) && count($dataArray) > 0) {
			$nowTime = date('Y-m-d H:i:s', HelpOther::requestTime());
			foreach ($dataArray as $key => $data) {
				// 判断原分类id和目标分类id不相等的时候进行跳转
				if($data['to_category_id'] != $data['from_category_id']) {
					// 判断时间是否过期
					if($data['start_time'] <= $nowTime && $nowTime < $data['end_time'] ) {
						// 判断目标分类id是否为0
						if( (empty($data['to_category_id']) || $data['to_category_id'] == 0)  && empty( $data['url'] )) {
							// 跳转类型判断处理
							if($data['type'] == 1) {
								redirect(eb_gen_url(''),'location', 301);
							} else {
								redirect(eb_gen_url(''),'location', 302);
							}
							break;
						} else{
							if( !empty ($data['to_category_id'])) {
								$targetCategoryId = (int)$data['to_category_id'];
								$str = str_replace( '{$targetCategoryId}', $targetCategoryId, $str );
							}elseif(!empty( $data['url'])){
								$str =  $data['url'];
							}
							// 跳转类型判断处理
							if($data['type'] == 1) {
								redirect( eb_gen_url($str, true), 'location', 301 );
							} else {
								redirect( eb_gen_url($str, true), 'location', 302 );
							}
							break;	
						}											
					}
				}
			}
		}
	}

}

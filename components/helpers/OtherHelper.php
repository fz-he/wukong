<?php

namespace app\components\helpers;

use Yii;
use app\components\helpers\ArrayHelper;
use app\models\Appmodel;

class OtherHelper {
	
	public static 	function eb_gen_url($content = '',$ending_with_slash = false,$param = array(),$symbol = '?', $language_code = ''){
		global $lang_basic_url;
		$appModelObj = Appmodel::getInstanceObj();
		if ( empty( $language_code ) ){
			$language_code = $appModelObj->currentLanguageCode();
		}
		$base_url = ArrayHelper::id2name($language_code,$lang_basic_url,BASIC_URL);

		$res = $base_url.trim($content,'/');
		if($ending_with_slash) $res .= '/';

		if(is_array($param) && !empty($param)){
			$res .= $symbol;
			foreach($param as $key => $value){
				$param[$key] = $key .'='.$value;
			}
			$res .= implode('&',$param);
		}

		return $res;
	}

	public static 	function eb_gen_ssl_url($content = '',$ending_with_slash = false,$param = array()){
		global $lang_basic_url;
		$CI = & get_instance();
		$language_code = $CI->session->get('language_code');
		$base_url = ArrayHelper::id2name($language_code,$lang_basic_url,BASIC_URL);
		if( COMMON_DOMAIN == 'eachbuyer.com' ){
			$base_url = str_replace('http://','https://',$base_url);
		}

		$res = $base_url.trim($content,'/');
		if($ending_with_slash) $res .= '/';

		if(is_array($param) && !empty($param)){
			$res .= '?';
			foreach($param as $key => $value){
				$param[$key] = $key .'='.$value;
			}
			$res .= implode('&',$param);
		}

		return $res;
	}


	public static 	function eb_substr($str,$length = 0,$append = true){
		$str = trim($str);
		$strlength = strlen($str);

		if ($length == 0 || $length >= $strlength){
			return $str;
		}elseif ($length < 0){
			$length = $strlength + $length;
			if ($length < 0){
				$length = $strlength;
			}
		}

		if (function_exists('mb_substr')){
			$newstr = mb_substr($str, 0, $length, 'utf-8');
		}elseif (function_exists('iconv_substr')){
			$newstr = iconv_substr($str, 0, $length, 'utf-8');
		}else{
			$newstr = substr($str, 0, $length);
		}

		if ($append && $str != $newstr){
			$newstr .= '...';
		}

		return $newstr;
	}


	/**
	 * 设置网盟cookie
	 * @param str $medium
	 * @param str $source
	 * @param str $campaign
	 * @authr QianChangnian
	 * @date 2014/3/6
	 */
	public static 	function set_affiliate_cookie($source, $medium, $html_source, $campaign, $time='') {
		$source			= trim( $source );		//获取佣金平台
		$medium			= trim( $medium );		//网盟平台
		$html_source	= trim( $html_source );	//所属项目
		$campaign		= trim( $campaign );	//网盟平台唯一标示id

		if ( $medium && ( $medium == 'NetworkAffiliates' || $medium == 'aff' || $medium == 'mediaffiliation' )) {
			$str	= 'w=' . $source . '&s=' . $html_source . '&m=' . $medium . '&c=' .$campaign;
			setcookie('eb_smclog', $str , time()+$time, '/', '.' . COMMON_DOMAIN );
			$_COOKIE['eb_smclog'] = $str;
		}
		return;
	}

	/**
	 * Check if use new template style.
	 * @author	Albie.
	 * @return boolean
	 */
	public static 	function check_use_new_tpl($view) {
		global $pagesUsedNewTpl;
		if(in_array($view, $pagesUsedNewTpl)){
			return TRUE;
		}else{
			return FALse;
		}
	}

	public static 	function eb_view_path($file){
		$file = trim($file,'/');
		$file = PATH_VIEW . SITE_CODE . '/' . $file;
		if (!file_exists($file)) {
			$file = FALSE;
		}

		return $file;
	}


	/**
	 * For the new view tpl path.
	 * @author	Albie
	 */
    public static 	function eb_view_path_new($file) {
		$file = trim($file, '/');
		$file = PATH_VIEW . NEW_TPL . '/' . $file;
		return $file;
	}


	public static 	function is_email($input){
		if(empty($input) || is_array($input)) { return false; }
		if(strpos($input,'@') === false) { return false; }
		if(strpos($input,'.') === false) { return false; }
		if(!preg_match("/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i",$input)) { return false; }

		return true;
	}


	public static 	function is_credit_card($input){
		$input = preg_replace("/\D|\s/", "", $input);

		$sum = 0;
		for($i=0; $i<strlen($input); $i++){
			$digit = substr($input, $i, 1);
			if(($i % 2) == 0){
				// 在單數位置的數值乘 2
				$digit = $digit * 2;
			}

			if ($digit> 9)  $digit = $digit - 9;
			$sum += $digit;
		}

		if(($sum % 10) == 0 && strlen($input) == 16){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	public static 	function unset_cookie($key){

		Yii::$app->response->cookies->add(new \yii\web\Cookie([
			'name' => $key,
			'value' => '',
			'expire' => $_SERVER['REQUEST_TIME'] - 3600,
			'domain' => COMMON_DOMAIN,
			'path' => ArrayHelper::id2name('cookie_path',$GLOBALS,'/'),
			'secure' => ArrayHelper::id2name('cookie_secure',$GLOBALS,false),
		]));
		
		return true;
	}


	public static 	function set_cookie($key,$value,$expire){
		// add a new cookie to the response to be sent
		Yii::$app->response->cookies->add(new \yii\web\Cookie([
			'name' => $key,
			'value' => $value,
			'expire' => $expire,
			'domain' => COMMON_DOMAIN,
			'path' => ArrayHelper::id2name('cookie_path',$GLOBALS,'/'),
			'secure' => ArrayHelper::id2name('cookie_secure',$GLOBALS,false),
		]));
		
		return true;
	}
	
	public static 	function get_cookie($key){
		$cookies = Yii::$app->request->cookies;
		return $cookies->getValue('real_ipd');  
	}

	public static 	function encrypt($str,$key = 'eachbuyer_bWF0dA=='){
		$key .= ':';

		return base64_encode($key.$str);
	}

	public static 	function decrypt($str,$key = 'eachbuyer_bWF0dA=='){
		$str = base64_decode($str);
		if($str == '') return '';
		list($decrypt_key,$str) = explode(':',$str);
		if($decrypt_key == $key && is_string($str) && $str != '') return $str;

		return '';
	}


	public static 	function processMail($type,$email,$subject,$data = array()){
		$CI = & get_instance();
		$url_params = '?utm_source=System_Own&utm_medium=Email&utm_campaign='.$type.'&utm_nooverride=1';

		$data['site_url'] = eb_gen_url().$url_params;
		$data['account_url'] = eb_gen_url('account').$url_params;
		$data['help_url'] = eb_gen_url('contact_us.html').$url_params;
		$data['login_url'] = eb_gen_url('login').$url_params;
		$data['newsletter_url'] = eb_gen_url('newsletter').$url_params;

		$content = $CI->load->view('mail_templates/'.$CI->m_app->currentLanguageCode().'/'.$type,$data,true);

		send_mail($email,$subject,$content,true);
	}



	public static 	function send_mail($email,$subject,$content,$is_html = 0,$notification=false, $isScriptTask = false ){
		$CI = & get_instance();
		$mail_service = $CI->m_app->getConfig('mail_service',0);
		$mail_charset = $CI->m_app->getConfig('mail_charset','UTF-8');
		$shop_name = $CI->m_app->getConfig('shop_name','');

		$smtp_mail = $CI->m_app->getConfig('smtp_mail','');
		if( $isScriptTask === false ){
			$name = $CI->m_app->getCurrentUserName();
		}else{
			$name = '';
		}
		$shop_name = base64_encode($shop_name);
		$subject = base64_encode($subject);
		$name = base64_encode($name);

		$content_type = '';
		if($is_html){
			$content_type = "Content-Type: text/html; charset={$mail_charset};format=flowed";
		}else{
			$content_type = "Content-Type: text/plain; charset={$mail_charset};format=flowed";
		}

		if($mail_service == 0 && function_exists('mail')){
			$headers = array();
			$headers[] = "From: \"=?{$mail_charset}?B?{$shop_name}?=\" <{$smtp_mail}>";
			$headers[] = $content_type;
			if ($notification){
				$headers[] = "Disposition-Notification-To: \"=?{$mail_charset}?B?{$shop_name}?=\" <{$smtp_mail}>";
			}

			$headers = implode("\r\n", $headers);
			mail($email,"=?{$mail_charset}?B?{$subject}?=",$content,$headers);
		}else{
			include_once APPPATH.'third_party/smtp.php';
			$params['host'] = $CI->m_app->getConfig('smtp_host');
			$params['port'] = $CI->m_app->getConfig('smtp_port');
			$params['user'] = $CI->m_app->getConfig('smtp_user');
			$params['pass'] = $CI->m_app->getConfig('smtp_pass');

			if ($params['host'] == '' || $params['port'] == '') return false;
			if (!function_exists('fsockopen')) return false;

			$content = base64_encode($content);
			$headers = array();
			$headers[] = 'Date: ' . gmdate('D, j M Y H:i:s') . ' +0000';
			$headers[] = "To: \"=?{$mail_charset}?B?{$name}?=\" <{$email}>";
			$headers[] = "From: \"=?{$mail_charset}?B?{$shop_name}?=\" <{$smtp_mail}>";
			$headers[] = "Subject: =?{$mail_charset}?B?{$subject}?=";
			$headers[] = $content_type;
			$headers[] = 'Content-Transfer-Encoding: base64';
			$headers[] = 'Content-Disposition: inline';
			if ($notification){
				$headers[] = "Disposition-Notification-To: \"=?{$mail_charset}?B?{$shop_name}?=\" <{$smtp_mail}>";
			}

			if (!isset($smtp)) $smtp = new smtp($params);
			if(!$smtp->connect($CI->m_app->getConfig('smtp_ssl'))) return false;

			$smtp->send(array(
				'recipients' => $email,
				'headers' => $headers,
				'from' => $smtp_mail,
				'body' => $content,
			));
		}
	}


	/**
	 * Send mail function.
	 * @param type $mailToArr e.g. array(
	  array(
	  'name' => 'ayonggegexn',
	  'mail' => 'ayonggegexn@163.com'
	  ),
	  array(
	  'name' => 'jinliang',
	  'mail' => 'jinliang@hofan.cn'
	  ),
	  );
	 * @param type $subject
	 * @param type $content
	 * @param type $ccArr e.g. array(
	  array(
	  'name' => 'ayonggegexn',
	  'mail' => 'ayonggegexn@163.com'
	  ),
	  array(
	  'name' => 'jinliang',
	  'mail' => 'jinliang@hofan.cn'
	  ),
	  );
	 * @return boolean
	 * @author Terry
	 */
	public static 	function send_mail_hf($mailToArr, $subject, $content, $ccArr = array()) {

		global $mailConfig;
		include_once APPPATH . 'third_party/PHPMailer_v5.1/class.phpmailer.php';
		$mail = new PHPMailer();
		$mail->CharSet = "UTF-8";
		$mail->IsSMTP(); // set mailer to use SMTP
		$mail->Host = $mailConfig['host'];
		$mail->SMTPAuth = true;	 // turn on SMTP authentication
		$mail->IsHTML(true); // set email format to HTML
		$mail->Username = $mailConfig['userName'];
		$mail->Password = $mailConfig['pwd'];
		$mail->From = $mailConfig['from'];
		$mail->FromName = $mailConfig['fromName'];
		$mail->Subject = $subject;
		$mail->WordWrap = 50; // set word wrap to 50 characters
		foreach ($mailToArr as $mailTo) {
			$mail->AddAddress($mailTo['mail'], $mailTo['name']);
		}
		foreach ($ccArr as $cc) {
			$mail->AddCC($cc['mail'], $cc['name']);
		}
		$mail->Body = $content;
		if ($mail->Send()) {
			return TRUE;
		} else {
			return FALSE;
		}
	}


	/**
	 * Write log for debug.(log path logs/eb_debug.log)
	 * @param	all types $content
	 * @author	Albie
	 * @date	2014/4/8
	 */
	public static 	function eb_debug($content,$output=false){
		if($output){
			echo '<pre/>';var_dump($content);
		}else{
			error_log('['.var_export($content, 1).']'.PHP_EOL, 3, APPPATH.'/logs/eb_debug.log');
		}
	}

	public static 	function exchangePriceToDefaultCurrency($price = 0,$from_currency){
		$CI = & get_instance();
		// $from_rate = $CI->m_app->getConfig(strtolower($from_currency).'_rate',1);
		// $price = $price / $from_rate;

		$currencyInfo = $CI->m_app->getConfigCurrency($from_currency, 1);
		$price = $price / $currencyInfo['rate'];

		$price = sprintf("%.2f",$price);

		return $price;
	}

	public static 	function exchangePrice($price = 0,$currency = '' , $rateNumber = ''){
		$CI = & get_instance();
		if(empty($currency)){
			$currency = $CI->m_app->currentCurrency();
		}
		if($currency !== false){
			// $rate = $CI->m_app->getConfig(strtolower($currency).'_rate',1);
			// $price = sprintf("%.2f",$price*$rate);
			if( empty( $rateNumber ) ){
				$currencyInfo = $CI->m_app->getConfigCurrency($currency, 1);
				$price = sprintf("%.2f",$price*$currencyInfo['rate']);
			}else{
				$price = sprintf("%.2f", $price * $rateNumber );
			}
		}
		return $price;
	}

	public static 	function formatPrice($price = 0,$currency = '' , $rateNumber = '' ){
		$appModelObj = Appmodel::getInstanceObj();
		if(empty($currency)){
			$currency = $appModelObj->currentCurrency();
		}
		if($currency !== false) {
			// $rate = $appModelObj->getConfig(strtolower($currency).'_rate',1);
			// $price_format = $appModelObj->getConfig(strtolower($currency).'_price_format','$%s');
			// $price = number_format(sprintf("%.2f",$price*$rate), 2);
			// $price = sprintf($price_format,$price);

			$currencyInfo = $appModelObj->getConfigCurrency($currency, 1);
			//若传有汇率过来 那么以传的汇率进行计算
			if( empty( $rateNumber ) ){
				$price = number_format( sprintf("%.2f", $price * $currencyInfo['rate']), 2);
			}else{
				$price = number_format( sprintf("%.2f", $price * $rateNumber ) , 2);
			}
			$price = sprintf($currencyInfo['format'],$price);
		}
		return $price;
	}

	public static 	function genImageUrl($url = ''){
		global $image_server;

		$image_server_index = array_rand($image_server);
		if($image_server_index === NULL || $url == ''){
			$CI = & get_instance();
			$url = '/'.$CI->m_app->getConfig('no_picture');
		}else{
			$url = $image_server[$image_server_index].$url;
		}

		return $url;
	}

	public static 	function eb_show_404(){
		$CI = & get_instance();

		if ( ! empty($CI->router->routes['404_override'])){
			$x = explode('/', $CI->router->routes['404_override']);
			$class = $x[0];
			$method = (isset($x[1]) ? $x[1] : 'index');
			if ( ! class_exists($class)){
				if ( ! file_exists(APPPATH.'controllers/'.SITE_CODE.'/'.$class.'.php')){
					show_404();
				}

				include_once(APPPATH.'controllers/'.SITE_CODE.'/'.$class.'.php');
				unset($CI);
				$CI = new $class();

				call_user_func_array(array(&$CI, $method), array_slice($CI->uri->rsegments, 2));
			}
		}else{
			show_404();
		}
	}



	public static 	function getPagePrivateCss( $page ) {
		global $private_css;
		$css_file = ArrayHelper::id2name( SITE_CODE, $private_css, array() );

		return ArrayHelper::id2name( $page, $css_file, false);
	}

	public static 	function is_spider(){
		$spider = strtolower($_SERVER['HTTP_USER_AGENT']);
		$searchengine_bot = array(
			'googlebot',
			'mediapartners-google',
			'baiduspider+',
			'msnbot',
			'yodaobot',
			'yahoo! slurp;',
			'yahoo! slurp china;',
			'iaskspider',
			'sogou web spider',
			'sogou push spider'
		);
		foreach($searchengine_bot as $record){
			if(strpos($spider,$record) !== false){
				return true;
			}
		}

		return false;
	}

	public static 	function generateGACid(){
		$CI = & get_instance();
		$ga = $CI->input->cookie('_ga');
		if($ga !== false){
			list($version,$domainDepth,$cid1,$cid2) = explode('.',$ga,4);
			return $cid1.'.'.$cid2;
		}else{
			return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				// 32 bits for "time_low"
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
				// 16 bits for "time_mid"
				mt_rand( 0, 0xffff ),
				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 4
				mt_rand( 0, 0x0fff ) | 0x4000,
				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				mt_rand( 0, 0x3fff ) | 0x8000,
				// 48 bits for "node"
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
			);
		}
	}

	/**
	 * 截取字符串
	 */
	public static 	function sub_str($str, $length, $append = TRUE, $htmlspecialchars = TRUE)
	{
		$str = mb_strimwidth($str, 0, $length, $append === TRUE ? '...' : '', 'UTF-8');

		if ($htmlspecialchars === TRUE)
		{
			return htmlspecialchars($str, ENT_QUOTES);
		}
		else
		{
			return $str;
		}
	}



	/**
	 * Htmlspecialchars the data.
	 * @param string or array $data The data need to htmlspecialchars.
	 * @author	Albie
	 */
	public static 	function eb_htmlspecialchars($data) {
		if (is_array($data)) {
			$dataFormat = array();
			foreach ($data as $k => $v) {
				$dataFormat[$k] = self::eb_htmlspecialchars($v);
			}
		} else {
			$dataFormat = htmlspecialchars($data, ENT_QUOTES);
		}
		return $dataFormat;
	}



	/**
	 * Format the seconds to the clock time like 02(days):13(hours):34(minutus):07(seconds).
	 * @param $seconds
	 * @author	Terry
	 */
	public static 	function format_seconds_to_clocktime($seconds,$type=1) {
		if ($type == 2) {
			$hours = sprintf("%02d", floor($seconds / 3600));
			$hoursLeftSec = $seconds % 3600;
			$mins = sprintf("%02d", floor($hoursLeftSec / 60));
			$minsLeftSec = sprintf("%02d", $hoursLeftSec % 60);
			$return = $hours . ':' . $mins . ':' . $minsLeftSec;
		} else {
			$days = sprintf("%02d", floor($seconds / (3600 * 24)));
			$daysLeftSec = $seconds % (3600 * 24);
			$hours = sprintf("%02d", floor($daysLeftSec / 3600));
			$hoursLeftSec = $daysLeftSec % 3600;
			$mins = sprintf("%02d", floor($hoursLeftSec / 60));
			$minsLeftSec = sprintf("%02d", $hoursLeftSec % 60);
			$return = $days . ':' . $hours . ':' . $mins . ':' . $minsLeftSec;
		}
		return $return;
	}

	public static 	function get_data_from_mc($sourceKeys,$memKey,$mcObj,$dataFetchObj,$dataFetchFunc,$keyParams = array()){
		if (!is_array($sourceKeys)) {
			$sourceKeys = array($sourceKeys);
		}

		$cachedData = array();
		$noCacheKeys = array();
		foreach($sourceKeys as $sourceKey){
			$cacheData = $mcObj->get($memKey,array_merge(array($sourceKey),$keyParams));
			if($cacheData===false){
				$noCacheKeys[] = $sourceKey;
			}else{
				$cachedData[$sourceKey] = $sourceKey;
			}
		}
		$noCacheData = $dataFetchObj->$dataFetchFunc($noCacheKeys,false);//通过外部指定方法获取到未缓存key对应的数据
		foreach($noCacheKeys as $noCacheKey){//给未缓存数据key写入相应的缓存数据。
			$mcObj->set($memKey,$noCacheData[$noCacheKey],array_merge(array($sourceKey),$keyParams));
		}

		return $cachedData+$noCacheData;
	}

	/**
	 * 数组取交集优化
	 * @param $data array 要取交集的数组 组成的二维数组 例如  array(array('a','b'),array('b','c'))
	 * @author qcn
	 */
	public static function array_intersect_upgrade($data){
		$result = array();
		$i=1;
		foreach($data as $key=>$value) {
			if( is_array( $value ) ) {
				if($i>1) {
					$result = array_intersect($result,$value);
				}else{
					$result = $value;
				}
				$i++;
			}
		}
		return $result;
	}

	/**
	 * 对象转化为数组。
	 * @author Terry
	 */
	public static 	function object_to_array($e) {
		$eFormat = (array) $e;
		foreach ($eFormat as $k => $v) {
			if (gettype($v) == 'resource') {
				return;
			}
			if (gettype($v) == 'object' || gettype($v) == 'array') {
				$eFormat[$k] = (array) object_to_array($v);
			}
		}
		return $eFormat;
	}


	/**
	 * 厘米转化为英寸。
	 * @param num $num 需转化的数值
	 * @param int $num_digits 4舍五入保留的小数位数
	 * @return num
	 * @author Terry
	 */
	public static 	function cm_to_inch($num,$num_digits=2) {
		$formatNum = round($num*0.3937008,$num_digits);
		if($formatNum==0){//需求方特殊需求（要求为0时不要显示小数点）
			return 0;
		}
		$arr = explode('.', $formatNum);
		$arr[1] = isset($arr[1])?$arr[1]:'';
		$curNumDigits = strlen($arr[1]);
		if($curNumDigits<$num_digits){
			for($i=1;$i<=$num_digits-$curNumDigits;$i++){
				$arr[1].='0';
			}
		}
		return (int)$arr[0].'.'.$arr[1];
	}
}
/* End of file other_helper.php */
/* Location: ./application/helpers/other_helper.php */
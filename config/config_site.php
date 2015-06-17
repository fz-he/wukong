<?php 
/*
| -------------------------------------------------------------------
| Site Config
| -------------------------------------------------------------------
| This file differ in different environment.
| no version control.
|
| file: config_site.php.example
| usage: cp config_site.php.example config_site.php
|
*/
if( TRUE ){
	error_reporting(E_ALL);
	ini_set('display_errors',1);
}else{
	error_reporting(0);
	ini_set('display_errors',0);
}

define('DEV_DOMAIN_PREFIX','');//这里配置开发环境需要新增的前缀，比如.ly01, 线上这里设置为空。

define('DISABLE_CACHE',false);
define( 'OPEN_LOG_ANALYSIS' ,  false );//记录mysql 以及mc
/*On-Off of category 301 and 302 jump.*/
define('DISABLE_CATEGORY_301302_JUMP',FALSE);

define('FRONT_DEBUG',false);

define('COMMON_DOMAIN', 'wukong.com');//网站二级域,子域请拼0m|%
//域名
define( 'HOME_URL' , 'www'. DEV_DOMAIN_PREFIX.'.'. COMMON_DOMAIN );
//主站域名
define( 'EACHBUYER_URL' ,'www.'. COMMON_DOMAIN );

//新添加
define( 'SITE_HOME_URL' , 'http://'. HOME_URL .'/' );

/*
|--------------------------------------------------------------------------
| Root Path
|--------------------------------------------------------------------------
| e.g. /root/path/
*/
define('ROOT_PATH', str_replace('application/config/config_site.php', '', str_replace('\\', '/', __FILE__)));


/*
|--------------------------------------------------------------------------
| static file version
|--------------------------------------------------------------------------
*/
//define( 'STATIC_FILE_VERSION' , $_SYSTEM_CONFIG['url_config']['static_file_version'] );

/*
 |--------------------------------------------------------------------------
| Basic Url
|--------------------------------------------------------------------------
| config_item('base_url') : $config['base_url'] in config.php:
*/
//define('BASIC_URL',config_item('base_url').'/');
define('BASIC_URL', '/');
define( 'CUSTOM_SERVICE_EMAIL', 'cs@'.COMMON_DOMAIN );

//检测移动版 修改配置文件 @add 20150204 11:49
$httpUserAgent = empty($_SERVER['HTTP_USER_AGENT']) ? '' : strtolower($_SERVER['HTTP_USER_AGENT']) ;
$isMobileDevice = ( strpos( $httpUserAgent , 'obile' ) === FALSE ) ? FALSE :TRUE ;
if( $isMobileDevice ){
	define('SITE_CODE', 'mobile');
	define( 'EBPLATEFORM', 1 );//0 为com,1为移动, 2为net ,用作来源标记
	define( 'ORDER_PREFIX', '' );//订单规则前缀

	$lang_basic_url = array(
		'us' => 'http://www'.DEV_DOMAIN_PREFIX.'.' . COMMON_DOMAIN . '/',
		'de' => 'http://de'.DEV_DOMAIN_PREFIX.'.' . COMMON_DOMAIN . '/',
		'es' => 'http://es'.DEV_DOMAIN_PREFIX.'.' . COMMON_DOMAIN . '/',
		'fr' => 'http://fr'.DEV_DOMAIN_PREFIX.'.' . COMMON_DOMAIN . '/',
		'it' => 'http://it'.DEV_DOMAIN_PREFIX.'.' . COMMON_DOMAIN . '/',
		'br' => 'http://br'.DEV_DOMAIN_PREFIX.'.' . COMMON_DOMAIN . '/',
		'ru' => 'http://ru'.DEV_DOMAIN_PREFIX.'.' . COMMON_DOMAIN . '/',
	);
}else{
	define('SITE_CODE' , 'default');
	define( 'EBPLATEFORM' , 0 );//0 为com,1为移动, 2为net ,用作来源标记
	define( 'ORDER_PREFIX' , '' );//订单规则前缀

	$lang_basic_url = array(
		'us' => 'http://www'.DEV_DOMAIN_PREFIX.'.' . COMMON_DOMAIN . '/',
		'de' => 'http://de'.DEV_DOMAIN_PREFIX.'.' . COMMON_DOMAIN . '/',
		'es' => 'http://es'.DEV_DOMAIN_PREFIX.'.' . COMMON_DOMAIN . '/',
		'fr' => 'http://fr'.DEV_DOMAIN_PREFIX.'.' . COMMON_DOMAIN . '/',
		'it' => 'http://it'.DEV_DOMAIN_PREFIX.'.' . COMMON_DOMAIN . '/',
		'br' => 'http://br'.DEV_DOMAIN_PREFIX.'.' . COMMON_DOMAIN . '/',
		'ru' => 'http://ru'.DEV_DOMAIN_PREFIX.'.' . COMMON_DOMAIN . '/',
	);
}


/*
|--------------------------------------------------------------------------
| Template
|--------------------------------------------------------------------------
*/
define('STATIC_URL','http://www'. DEV_DOMAIN_PREFIX .'.' . COMMON_DOMAIN . '/resource/');
define('THEME','default_default');

/*
|--------------------------------------------------------------------------
| Image Server List
|--------------------------------------------------------------------------
*/
$image_server = array(
		'http://img.eachbuyer.com/',
		'http://img3.eachbuyer.com/',
);

/*
|--------------------------------------------------------------------------
| Session
|--------------------------------------------------------------------------
*/
define('SESSION_NAME','EB_ID');
define('SESSION_LIFE_TIME', 7200 );

/*
|--------------------------------------------------------------------------
| Language List
|--------------------------------------------------------------------------
*/
$language_list = array(
		1 => array('code'=>'us','title'=>'English','currency'=>'USD','common_code'=>'en'),
		2 => array('code'=>'de','title'=>'Deutsch','currency'=>'EUR','common_code'=>'de'),
		3 => array('code'=>'es','title'=>'Español','currency'=>'EUR','common_code'=>'es'),
		4 => array('code'=>'it','title'=>'Italiano','currency'=>'EUR','common_code'=>'it'),
		5 => array('code'=>'fr','title'=>'Français','currency'=>'EUR','common_code'=>'fr'),
		6 => array('code'=>'br','title'=>'Português','currency'=>'BRL','common_code'=>'pt'),
		7 => array('code'=>'ru','title'=>'Pусский','currency'=>'RUB','common_code'=>'ru'),
);
define('DEFAULT_LANGUAGE','us');

/*
|--------------------------------------------------------------------------
| Currency List
|--------------------------------------------------------------------------
*/
$currency_list = array('AUD','BRL','GBP','CAD','EUR','HKD','CHF','USD','RUB','INR','MXN');
define('DEFAULT_CURRENCY','USD');

/*
|--------------------------------------------------------------------------
| Shipping
|--------------------------------------------------------------------------
*/
$shipping_method_list = array(
		1 => 'airmail',
		2 => 'standard',
		3 => 'express',
		4 => 'register_airmail',
		5 => 'register_standard',
		6 => 'CN-Mail',
);
/*
|--------------------------------------------------------------------------
| Payment
|--------------------------------------------------------------------------
*/
$payment_list = array(
		1 => 'paypal_ec',
		2 => 'paypalsk',//PPHK
		3 => 'bank', 
		//4 => 'credit_card',
		7 => 'adyen',
	
		20 =>'alipay' ,
		21 => 'amex',
		22 => 'bcmc', // => 'Bancontact_Mister-Cash.jpg',
		23 => 'cartebancaire', 
		24 => 'diners',
		25 => 'discover' ,
		26 => 'ebanking_FI' ,
		27 => 'giropay',
		28 => 'ideal',
		29 => 'jcb',
		30 => 'maestro' ,
		31 => 'maestrouk',
		32 => 'mc' ,
		33 => 'paypal',//PPUK
		34 => 'qiwiwallet',
		35 => 'safetypay',
		36 => 'directEbanking' ,//Sofortüberweisung
		37 => 'unionpay' ,
		38 => 'visa' ,
);

define('PAYPAL_SANDBOX_DISABLED',false);
define('PAYPAL_EC_SANDBOX_DISABLED',false);
define('CHECKOUT_SANDBOX_DISABLED',false);
define('SOFORT_SANDBOX_DISABLED',false);
define('SAFETYPAY_SANDBOX_DISABLED',false);
define('ADYEN_SANDBOX_DISABLED',false);

$mailConfig = array(
	'host' => "smtp.hofan.cn",
	'userName' => "system@hofan.cn",
	'pwd' => "AA123456AA",
	'from' => "system@hofan.cn",
	'fromName' => "eachbuyer.com"
);
$orderReportMailReci = array(
	array(
		'name' => '秦焜',
		'mail' => 'qinkun@hofan.cn'
	),
	array(
		'name' => '宁彦栋',
		'mail' => 'ningyandong@hofan.cn'
	),
);

$paymentMethodsIcon = array(
	'paypal_ec' =>  array( 'icon'=>'','name'=> 'Paypal EC'),
	'paypalsk' =>  array( 'icon'=>'PayPalSK.jpg','name'=> 'PayPal'),
	'bank' =>  array( 'icon'=>'Wire-transfer.jpg','name'=> 'Wire transfer'),
);

$availablePaymentMethodsIcon = array(
	'alipay' =>  array( 'icon'=>'AliPay.jpg','name'=> 'AliPay'),
	'amex' => array( 'icon'=> 'American-Express.jpg','name'=> 'American Express'),
	'bcmc' => array( 'icon'=> 'Bancontact_Mister-Cash.jpg','name'=> 'Bancontact/Mister Cash'),//currency=EUR&country=be
	'cartebancaire' => array( 'icon'=> 'Carte-Bancaire.jpg','name'=> 'Carte Bancaire'),
	'diners' => array( 'icon'=> 'Diners-Club.jpg','name'=> 'Diners Club'),
	'discover' => array( 'icon'=> 'Discover.jpg','name'=> 'Discover'),
	'ebanking_FI' => array( 'icon'=> 'Finnish-E-Banking.jpg','name'=> 'Finnish E-Banking'),//有issuerId
	'giropay' => array( 'icon'=> 'GiroPay.jpg','name'=> 'GiroPay'),
	'ideal' => array( 'icon'=> 'iDEAL.jpg','name'=> 'iDEAL'),//有issuerId
	'jcb' => array( 'icon'=> 'JCB.jpg','name'=> 'JCB'),
	'maestro' => array( 'icon'=> 'Maestro.jpg','name'=> 'Maestro'),
	'maestrouk' => array( 'icon'=> 'Maestro-UK.jpg','name'=> 'Maestro UK'),//country=GB
	'mc' => array( 'icon'=> 'MasterCard.jpg','name'=> 'MasterCard'),
	'paypal' => array( 'icon'=> 'PayPal.jpg','name'=> 'PayPal'),
	'qiwiwallet' => array( 'icon'=> 'Qiwi-Wallet.jpg','name'=> 'Qiwi Wallet'),
	'safetypay' => array( 'icon'=> 'SafetyPay.jpg','name'=> 'SafetyPay'),	//currency=USD&country=CR
	'directEbanking' => array( 'icon'=> 'Sofort-Banking.jpg','name'=> 'Sofortüberweisung'), // => 'Sofortüberweisung.jpg',
	'unionpay' => array( 'icon'=> 'UnionPay.jpg','name'=> 'UnionPay'),
	'visa' => array( 'icon'=> 'VISA.jpg',	'name'=> 'VISA'),
);

 $allowableMinPayAmount = array(
	 //  >=0 都可以
	'alipay' => array('AUD' => 0.01, 'BRL' => 0.01 , 'CHF' =>0.01 , 'CAD'=> 0.01, 'EUR' => 0.01,
		 'GBP'=> 0.01, 'HKD'=> 0.01, 'INR'=> 0.01, 'MXN' => 0.01 , 'RUB'=> 0.01, 'USD'=> 0.01 ),
	 
	'amex' => array('AUD' => 0.03, 'BRL' => 0.06 , 'CHF' => 0.03, 'CAD'=> 0.03 ,  'EUR' => 0.02, 
		'GBP'=> 0.02, 'HKD'=> 0.2, 'INR'=>1.60 , 'MXN' => 0.25, 'RUB'=> 0.85, 'USD'=> 0.02 ),
	//GBP,USD,
	'bcmc' => array('AUD' => 0.03, 'BRL' => 0.06 , 'CHF' =>0.03 , 'CAD'=>  0.03, 'EUR' => 0.02, 
		'GBP'=> 0.02, 'HKD'=>0.2 , 'INR'=>1.60 , 'MXN' =>0.25 , 'RUB'=>0.85 , 'USD'=> 0.02 ),
	 
	'cartebancaire' => array('AUD' => 0.03, 'BRL' => 0.06, 'CHF' => 0.03, 'CAD'=>  0.03, 'EUR' => 0.02, 
		'GBP'=> 0.02, 'HKD'=> 0.2, 'INR'=> 1.60, 'MXN' => 0.25, 'RUB'=> 0.85, 'USD'=> 0.02),
	 
	'diners' => array('AUD' => 0.03, 'BRL' => 0.06 , 'CHF' => 0.03, 'CAD'=>  0.03,  'EUR' => 0.02,
		'GBP'=> 0.02, 'HKD'=>0.2 , 'INR'=> 1.60, 'MXN' => 0.25, 'RUB'=> 0.85 , 'USD'=> 0.02 ),
	
	'discover' => array('AUD' => 0.03, 'BRL' => 0.06 , 'CHF' =>0.03 , 'CAD'=> 0.03 ,'EUR' => 0.02,
		'GBP'=> 0.02, 'HKD'=> 0.2, 'INR'=>1.60 , 'MXN' =>0.25 , 'RUB'=> 0.85 , 'USD'=> 0.02 ),
	//'USD'=> 0.02 有效
	'ebanking_FI' => array('AUD' => 0.03, 'BRL' => 0.06, 'CHF' => 0.03, 'CAD'=>  0.03, 'EUR' => 0.02, 
		'GBP'=> 0.02, 'HKD'=> 0.2, 'INR'=>1.60 , 'MXN' => 0.25, 'RUB'=> 0.85, 'USD'=> 0.02 ),
	 //只EUR有效
	'giropay' => array('AUD' => 0.02, 'BRL' => 0.03, 'CHF' => 0.02, 'CAD'=> 0.02 ,  'EUR' => 0.01, 
		'GBP'=> 0.01, 'HKD'=> 0.2, 'INR'=> 0.08, 'MXN' =>0.13 , 'RUB'=> 0.43, 'USD'=> 0.02),
	//EUR,USD
	'ideal' => array('AUD' => 0.03, 'BRL' =>0.06 , 'CHF' =>0.03 , 'CAD'=> 0.03 , 'EUR' => 0.02, 
		'GBP'=>0.02 , 'HKD'=> 0.2, 'INR'=>0.08 , 'MXN' =>0.25 , 'RUB'=> 0.85 , 'USD'=> 0.02),
	 
	'jcb' => array('AUD' => 0.03, 'BRL' => 0.06 , 'CHF' => 0.03, 'CAD'=> 0.03 , 'EUR' => 0.02, 
		'GBP'=> 0.02, 'HKD'=> 0.2, 'INR'=>1.60 , 'MXN' =>0.25 , 'RUB'=> 0.85, 'USD'=> 0.02 ),
	 
	'maestro' => array('AUD' => 0.03, 'BRL' => 0.06 , 'CHF' => 0.03, 'CAD'=>  0.03,  'EUR' => 0.02,
		'GBP'=> 0.02, 'HKD'=>0.2 , 'INR'=>1.60 , 'MXN' => 0.25, 'RUB'=> 0.85, 'USD'=> 0.02 ),
	 
	'mc' => array('AUD' => 0.03, 'BRL' => 0.06 , 'CHF' => 0.03, 'CAD'=> 0.03, 'EUR' => 0.02, 
		'GBP'=> 0.02, 'HKD'=> 0.2, 'INR'=> 1.60, 'MXN' => 0.25, 'RUB'=> 0.85, 'USD'=> 0.02 ),
	 
	'paypal' => array('AUD' => 0.75, 'BRL' => 1.5, 'CHF' => 0.75, 'CAD'=>  0.75,  'EUR' => 0.50, 
		'GBP'=> 0.5, 'HKD'=> 5, 'INR'=> 40, 'MXN' => 6.25, 'RUB'=> 21.25, 'USD'=> 0.50 ),
	//只EUR，USD,RUB有效
	'qiwiwallet' => array('AUD' => 0.02, 'BRL' =>0.03 , 'CHF' =>0.01 , 'CAD'=> 0.03 ,  'EUR' => 0.01,
		'GBP'=> 0.01, 'HKD'=>0.1 , 'INR'=>0.02 , 'MXN' => 0.25, 'RUB'=> 0.85, 'USD'=> 0.01),
	 //只EUR 和 USD 有效，
	'safetypay' => array('AUD' => 0.02, 'BRL' => 0.03, 'CHF' => 0.01, 'CAD'=> 0.02,  'EUR' => 0.01, 
		'GBP'=> 0.01, 'HKD'=>0.1, 'INR'=> 0.02, 'MXN' => 0.02, 'RUB'=>0.85 , 'USD'=> 0.01 ),
	 //只EUR有效
	'directEbanking' => array('AUD' => 0.02, 'BRL' =>0.03 , 'CHF' => 0.01 , 'CAD'=> 0.02, 'EUR' => 0.01 ,
		'GBP'=> 0.01, 'HKD'=> 0.1, 'INR'=>0.80 , 'MXN' =>0.13 , 'RUB'=> 0.43, 'USD'=> 0.01),
	 
	'unionpay' => array('AUD' => 0, 'BRL' => 0, 'CHF' => 0, 'CAD'=> 0,  'EUR' => 0.02,
		'GBP'=> 0, 'HKD'=> 0.2, 'INR'=> 1.60, 'MXN' => 0.25, 'RUB'=>0.85 , 'USD'=> 0.02),
	 
	'visa' => array('AUD' => 0.03, 'BRL' => 0.06 , 'CHF' => 0.03, 'CAD'=> 0.03 ,'EUR' => 0.02,
		'GBP'=> 0.02, 'HKD'=>0.2 , 'INR'=> 1.60, 'MXN' => 0.25, 'RUB'=>0.85 , 'USD'=> 0.02 ),
 );

/* End of file config_site.php */
/* Location: ./application/config/config_site.php */


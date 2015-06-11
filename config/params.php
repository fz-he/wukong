<?php
$_SYSTEM_CONFIG = require(__DIR__ . '/db.php');

return [
    'adminEmail' => 'admin@example.com',
	'eb_db_config' => $_SYSTEM_CONFIG['eb_db_config'],
	'language_list' => [
		1 => array('code'=>'us','title'=>'English','currency'=>'USD','common_code'=>'en'),
		2 => array('code'=>'de','title'=>'Deutsch','currency'=>'EUR','common_code'=>'de'),
		3 => array('code'=>'es','title'=>'Español','currency'=>'EUR','common_code'=>'es'),
		4 => array('code'=>'it','title'=>'Italiano','currency'=>'EUR','common_code'=>'it'),
		5 => array('code'=>'fr','title'=>'Français','currency'=>'EUR','common_code'=>'fr'),
		6 => array('code'=>'br','title'=>'Português','currency'=>'BRL','common_code'=>'pt'),
		7 => array('code'=>'ru','title'=>'Pусский','currency'=>'RUB','common_code'=>'ru'),
	],
	'currency_list' => array('AUD','BRL','GBP','CAD','EUR','HKD','CHF','USD','RUB','INR','MXN'),
	'shipping_method_list' => array(
		1 => 'airmail',
		2 => 'standard',
		3 => 'express',
		4 => 'register_airmail',
		5 => 'register_standard',
		6 => 'CN-Mail',
	),
	'payment_list' => array(
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
	),
	'mailConfig' => array(
		'host' => "smtp.hofan.cn",
		'userName' => "system@hofan.cn",
		'pwd' => "AA123456AA",
		'from' => "system@hofan.cn",
		'fromName' => "eachbuyer.com"
	),
	'orderReportMailReci' => array(
		array(
			'name' => '秦焜',
			'mail' => 'qinkun@hofan.cn'
		),
		array(
			'name' => '宁彦栋',
			'mail' => 'ningyandong@hofan.cn'
		),
	),
	'paymentMethodsIcon' => array(
		'paypal_ec' =>  array( 'icon'=>'','name'=> 'Paypal EC'),
		'paypalsk' =>  array( 'icon'=>'PayPalSK.jpg','name'=> 'PayPal'),
		'bank' =>  array( 'icon'=>'Wire-transfer.jpg','name'=> 'Wire transfer'),
	),
	'availablePaymentMethodsIcon' => array(
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
	),
	'allowableMinPayAmount' => array(
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
	),
	
];

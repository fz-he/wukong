<?php

$params = require(__DIR__ . '/params.php');
$_SYSTEM_CONFIG = require(__DIR__ . '/db.php');

$config = [
    'id' => '',
    'basePath' => dirname(__DIR__),
	'language' => 'ru' ,
    'bootstrap' => ['log'],
	'defaultRoute'=> 'pc/home/index',
	//'controllerNamespace' => 'app\controllers',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'QhorEYwNyvV5lktLjvy_0gOGKuG-VVYJ',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
		//@todo 有多数据库时 不知道怎么用AR操作相应的数据库
        'db' => $_SYSTEM_CONFIG['eb_db_config']['eachbuyer_master'],
		'eachbuyer_slave' => $_SYSTEM_CONFIG['eb_db_config']['eachbuyer_slave'],
		'eachbuyer_eb_master' => $_SYSTEM_CONFIG['eb_db_config']['eachbuyer_eb_master'],
		'eachbuyer_eb_slave' => $_SYSTEM_CONFIG['eb_db_config']['eachbuyer_eb_slave'],
				
		 'view' => [
	      //  'class' => 'view\pc',
        ],
		'urlManager' => [       
			'enablePrettyUrl' => true,
            'showScriptName'=>FALSE,
            'suffix'=>'.html',
            'rules'=>array(
                '<controller:\w+>/<id:\d+>’=>’<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>’=>’<controller>/<action>',
                '<controller:\w+>/<action:\w+>’=>’<controller>/<action>',
            )
        ],
		'i18n' => [
			'translations' => [
				'*' => [//@todo 这样相当于所以的语言文件都加载了，影响性能
					'class'=>'yii\i18n\PhpMessageSource',
					'basePath' => '@app/languages',
					'fileMap' => [           
						//'common' => 'common.php',       
					],
				]
			]
		]
		
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;

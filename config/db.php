<?php

//return [
//    'class' => 'yii\db\Connection',
//    'dsn' => 'mysql:host=172.16.0.231;dbname=eachbuyer;port=3306 ',
//    'username' => 'root',
//    'password' => 'db01123456',
//    'charset' => 'utf8',
//];



	//DB mc static url 等系统配置集合
	$_SYSTEM_CONFIG = [
		//DB配置文件
		'eb_db_config' =>[
			//主库
			'eachbuyer_master' => [			
				'class' => 'yii\db\Connection',
				'dsn' => 'mysql:host=172.16.0.231;dbname=eachbuyer;port=3306 ',
				'username' => 'root',
				'password' => 'db01123456',
				'charset' => 'utf8',
			],
			'eachbuyer_slave' => [
				'class' => 'yii\db\Connection',
				'dsn' => 'mysql:host=172.16.0.231;dbname=eachbuyer;port=3306 ',
				'username' => 'root',
				'password' => 'db01123456',
				'charset' => 'utf8',
			],
			'eachbuyer_eb_master' => [
				'class' => 'yii\db\Connection',
				'dsn' => 'mysql:host=172.16.0.231;dbname=eb_pc_site;port=3306 ',
				'username' => 'root',
				'password' => 'db01123456',
				'charset' => 'utf8',
			],
			'eachbuyer_eb_slave' => [
				'class' => 'yii\db\Connection',
				'dsn' => 'mysql:host=172.16.0.231;dbname=eb_pc_site;port=3306 ',
				'username' => 'root',
				'password' => 'db01123456',
				'charset' => 'utf8',
			],
			//memcache 配置文件
			'memcache_config' => [
					'memcache_web' => [
							'host' => '172.16.0.230' ,
							'port' => 11211 ,
					],
					'memcache_page' => [
							'host' => '172.16.0.230' ,
							'port' => 11311 ,
					],
					'memcache_session' => [
							'host' => '172.16.0.230' ,
							'port' => 11411 ,
					],
			],
		],
	];

	return $_SYSTEM_CONFIG;
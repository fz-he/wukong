<?php

//return [
//    'class' => 'yii\db\Connection',
//    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
//    'username' => 'root',
//    'password' => '',
//    'charset' => 'utf8',
//];


	//DB mc static url 等系统配置集合
	$_SYSTEM_CONFIG = array(
			'class' => 'yii\db\Connection',
			//DB配置文件
			'eb_db_config' => array (
					//订单系统的数据库
					'eachbuyer_order' => array(
							'hostname' => '172.16.0.231' ,
							'port' => 3306 ,
							'username' => 'root' ,
							'password' => 'db01123456' ,
							'database' => 'eachbuyer_order' ,
							'dbdriver' => 'mysql' ,
							'dbprefix' => '',
							'pconnect' => FALSE ,
							'db_debug' => FALSE ,
							'cache_on' => FALSE ,
							'cachedir' => FALSE ,
							'char_set' => 'utf8' ,
							'dbcollat' => 'utf8_general_ci' ,
							'swap_pre' => '' ,
							'autoinit' => TRUE ,
							'stricton' => FALSE ,
					) ,
					//主库
					'eachbuyer_master' => array(
							'hostname' => '172.16.0.231' ,
							'port' => 3306 ,
							'username' => 'root' ,
							'password' => 'db01123456' ,
							'database' => 'eachbuyer' ,
							'dbdriver' => 'mysql' ,
							'dbprefix' => '',
							'pconnect' => FALSE ,
							'db_debug' => FALSE ,
							'cache_on' => FALSE ,
							'cachedir' => FALSE ,
							'char_set' => 'utf8' ,
							'dbcollat' => 'utf8_general_ci' ,
							'swap_pre' => '' ,
							'autoinit' => TRUE ,
							'stricton' => FALSE ,
					) ,
					//从库
					'eachbuyer_slave' => array(
							'hostname' => '172.16.0.231' ,
							'port' => 3306 ,
							'username' => 'root' ,
							'password' => 'db01123456' ,
							'database' => 'eachbuyer' ,
							'dbdriver' => 'mysql' ,
							'dbprefix' => '',
							'pconnect' => FALSE ,
							'db_debug' => FALSE ,
							'cache_on' => FALSE ,
							'cachedir' => FALSE ,
							'char_set' => 'utf8' ,
							'dbcollat' => 'utf8_general_ci' ,
							'swap_pre' => '' ,
							'autoinit' => TRUE ,
							'stricton' => FALSE ,
					) ,
					//新版 数据库  主库
					'eachbuyer_eb_master' => array(
							'hostname' => '172.16.0.231' ,
							'port' => 3306 ,
							'username' => 'root' ,
							'password' => 'db01123456' ,
							'database' => 'eb_pc_site' ,
							'dbdriver' => 'mysql' ,
							'dbprefix' => '',
							'pconnect' => FALSE ,
							'db_debug' => FALSE ,
							'cache_on' => FALSE ,
							'cachedir' => FALSE ,
							'char_set' => 'utf8' ,
							'dbcollat' =>  'utf8_general_ci' ,
							'swap_pre' => '' ,
							'autoinit' => TRUE ,
							'stricton' => FALSE ,
					) ,
					//新版数据库 从库
					'eachbuyer_eb_slave' => array(
							'hostname' => '172.16.0.231' ,
							'port' => 3306 ,
							'username' => 'root' ,
							'password' => 'db01123456' ,
							'database' => 'eb_pc_site' ,
							'dbdriver' => 'mysql' ,
							'dbprefix' => '',
							'pconnect' => FALSE ,
							'db_debug' => FALSE ,
							'cache_on' => FALSE ,
							'cachedir' => FALSE ,
							'char_set' => 'utf8' ,
							'dbcollat' =>  'utf8_general_ci' ,
							'swap_pre' => '' ,
							'autoinit' => TRUE ,
							'stricton' => FALSE ,
					) ,
			),
			//memcache 配置文件
			'memcache_config' => array(
					'memcache_web' => array(
							'host' => '172.16.0.230' ,
							'port' => 11211 ,
					),
					'memcache_page' => array(
							'host' => '172.16.0.230' ,
							'port' => 11311 ,
					),
					'memcache_session' => array(
							'host' => '172.16.0.230' ,
							'port' => 11411 ,
					),
			),
			//http://pic.eachbuyer.com/350x350/x9/p100/xx018637_a.jpg  web_path :x9/p100/xx018637_a.jpg
			'url_config' => array(
					'static_file_version' => 20141028102610 ,
					'static_type' => 'source' , // ['source'/'compress']静态文件类型( source:源代码,compress: 压缩后)
					'source' => array(
							'img_url' => array(
									'web_path'=>'img6.' . COMMON_DOMAIN .'/',
							 ),
							'img_site_url' => array(
									'img6.' . COMMON_DOMAIN .'/',
							),
							'css' => array(
									'url' => HOME_URL . '/css/',
							),
							'js' => array(
									'url' => HOME_URL . '/js/',
							),
					) ,
					'compress'=> array(
							'img_url' => array(
									'web_path'=>'img6.' . COMMON_DOMAIN .'/',
							),
							'img_site_url' => array(
									'img6.' . COMMON_DOMAIN .'/',
							),
							'css' => array(
									'url' => HOME_URL . '/compress/css/',
							),
							'js' => array(
									'url' => HOME_URL . '/compress/js/',
							),
					) ,
			),
	) ;

	return $_SYSTEM_CONFIG;
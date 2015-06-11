<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

//@todo 这里引入配置文件，不知道YII里要怎么引入才好
require(__DIR__ . '/../config/config_application.php');
require(__DIR__ . '/../config/config_redirect_products.php');
require(__DIR__ . '/../config/config_site.php');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();

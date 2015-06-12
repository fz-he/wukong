<?php

namespace app\models\common;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\Connection;
use app\libraries\Session;
use app\libraries\Memcache;
use app\libraries\Log;

class EbARModel extends ActiveRecord{
	
	public $db_read = NULL;
	public $db_write = NULL;
	public $db_ebmaster_write = NULL;
	public $db_ebmaster_read = NULL;
	public $memcache = NULL;
	public $session = NULL;
	public $log = NULL;

	//put your code here
	public function __construct($config = array()) {
		//parent::__construct($config);
		
		$this->db_write =  Yii::$app->db;
		$this->db_read  = Yii::$app->eachbuyer_slave;
		$this->db_ebmaster_write  = Yii::$app->eachbuyer_eb_master;
		$this->db_ebmaster_read  = Yii::$app->eachbuyer_eb_slave;
	
		if(!$this->db_read->open()){
			$this->db_read = $this->db_write;
		}
		
		$this->memcache = Memcache::getinstance();
		$this->session =  Session::getInstance();
		$this->log =  Log::getInstance();
		
	}
}

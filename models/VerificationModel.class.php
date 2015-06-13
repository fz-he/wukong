<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * 数据迁移脚本类
 * @author lucas
 */
class VerificationModel extends CI_Model {

	const DB_NAME = 'eachbuyer'; //迁移库
	const DB_NAME_NEW = 'eb_pc_site'; //迁移目标库

	/**
	 * 初始化
	 */
	public function __construct() {
		set_time_limit( 0 );
	}

	/**
	 * 验证对比迁移数据
	 * @param string $newTableName 导入新表名称
	 * @param string $tableName 被导入老表名称
	 * @param array $differenceField 差异字段数组
	 * 	array(
	 *		'fieldName' => 'fieldName2', //表1 A字段 [对应] 表2 B字段
	 *		'fieldName' => '', //表1 A字段 [对应] 表2 无此字段 改为B字段
	 *	)
	 * @param int $limit 取多少条数据比对
	 * @param string $order = ASC 默认递增 DESC 递减
	 * @return string 
	 */
	public function contrastTableaData( $newTableName = '', $tableName = '', $differenceField = array(), $limit = 50, $order = 'ASC' ){
		if( empty( $newTableName ) || empty( $tableName ) ){
			echo "参数非法，表名为空！\n";
			exit;
		}

		//迁移目的表记录总数
		$sql = "SELECT count(*) FROM `{$newTableName}`";
		$query = $this->db_ebmaster_write->query( $sql );
		$list = $query->result_array();
		echo self::DB_NAME_NEW.".".$newTableName." 表记录总数：".current( current( $list ) )."\n";

		//迁移表记录总数
		$sql = "SELECT count(*) FROM `{$tableName}`";
		$query = $this->db_write->query( $sql );
		$list = $query->result_array();
		$recordCount = current( current( $list ) );
		echo self::DB_NAME.".".$tableName." 表记录总数：".$recordCount."\n";

		$orderLimit = ( $order == 'ASC' ) ? $limit : $recordCount-$limit.','.$limit;
		//迁移目的表前50条记录
		$sql = "SELECT * FROM `{$newTableName}` LIMIT ". $orderLimit;
		$query = $this->db_ebmaster_write->query( $sql );
		$dataNew = $query->result_array();

		//迁移表前50条记录
		$sql = "SELECT * FROM `{$tableName}` LIMIT ". $orderLimit;
		$query = $this->db_write->query( $sql );
		$data = $query->result_array();

		$error = 0;
		foreach( $dataNew as $key => $record ){
			foreach( $record as $k => $v ){
				if( empty( $differenceField[ $k ] ) ){
					continue;
				}
				$oldId = isset( $differenceField[ $k ] ) ? $differenceField[ $k ] : $k;
				if( $record[ $k ] != $data[ $key ][ $oldId ] ){
					$error++;
					echo "{$orderText}{$limit}条数据对比不一致KEY：". $key ." - ". $k ."\n";
				}
			}
		}

		$orderText = ( $order == 'ASC' ) ? '前' : '后';
		if( empty( $error ) ){
			echo "数据对比完成－{$orderText}{$limit}条数据一致。\n";
		}else{
			echo "数据对比完成－{$orderText}{$limit}条数据存在".$error."处不一致。\n";
		}

		return TRUE;
	}

	/**
	 * 析构方法
	 */
	public function __destruct(){
		
	}

}

<?php if ( ! defined('BASEPATH')) {exit('No direct script access allowed');}

/**
 * Model for goods.
 * @author Terry Lu
 */
class FeedModel extends CI_Model {

	const SENSITIVE_TYPE_ORDINARY=0;//普通
	const SENSITIVE_TYPE_WITH_BATTERY=1;//含电池
	const SENSITIVE_TYPE_POWDER=2;//粉末
	const SENSITIVE_TYPE_LIQUID=3;//液体
	const SENSITIVE_TYPE_OTHER=7;//其他
	const SENSITIVE_TYPE_PURE_BATTERY=5;//纯电池
	const SENSITIVE_TYPE_INFRINGEMENT=6;//侵权
	const DEFAULT_TIME='2014-09-01 00:00:00';

	var $ts_time_types = array('product','product_sku','promote_activity_target','promote_bundle_target');
	var $category_ts_time_types = array('category','category_desc');
	var $languages = array();

	public function __construct(){
		parent::__construct();
	}

	/*
	| -------------------------------------------------------------------
	|  获取语言列表
	| -------------------------------------------------------------------
	*/
	public function getLanguageList(){
		if(count($this->languages)<1){
			$this->db_ebmaster_read->select('language_id,language_code');
			$this->db_ebmaster_read->from('languages');
			$query = $this->db_ebmaster_read->get();
			$record = $query->result_array();
			foreach($record as $info){
				$language_id = $info['language_id'];
				$language_code = $info['language_code'];
				if($language_code == 'us'){
					$language_code = 'en';
				}
				if($language_code == 'br'){
					$language_code = 'pt';
				}
				$this->languages[$language_id] = $language_code;
			}
		}
	}

    /*
	| -------------------------------------------------------------------
	|  货币格式化
	| -------------------------------------------------------------------
	*/
	public function formatPrice($price = 0){
		$format_price_list = array();
		$currency_list = array('USD','AUD','BRL','GBP','CAD','EUR','HKD','CHF','CNY','RUB','INR','MXN');
		if($price <= 0){
			return $format_price_list;
		}
		foreach($currency_list as $currency){
			$format_price_list[$currency]['format'] = formatPrice($price,$currency);
			$format_price_list[$currency]['value'] = exchangePrice($price,$currency);
		}
		return $format_price_list;
	}
	/*
	| -------------------------------------------------------------------
	|  获取需要更新的PID列表
	|  字段：product.last_time 可以监测:category_id/image/final_price/market_price/status/
	|  字段：product_sku.last_time 可以监测:skus/skus(image)/skus(final_price)/skus(final_market_price)/skus(weight)/skus(sensitive_type)//skus(stock)/skus(status)
	|  字段：product_description_1.last_time 可以监测:name/desc/url/
	|  字段：promote_activity_target.last_time 可以监测:promote_discount/promote_start_time/promote_end_time/final_price
	|  字段：promote_bundle_target.last_time 可以监测:promote_discount/promote_start_time/promote_end_time/final_price
	| -------------------------------------------------------------------
	*/
	public function getTheChangedPidList(){
		$step = 5000;
		$end_time = date('Y-m-d H:i:s',time() - 10);
		$the_changed_pid_list = array();
		$last_ts_time_list = array();
		$end_time_list = array();
		$ts_time_types_tmp = $this->ts_time_types;
		foreach($this->languages as $lan_id => $lan_code){
			$ts_time_types_tmp[] = "product_description_{$lan_id}";
		}
		$query = $this->db_ebmaster_read->query(" select type,last_ts_time from feed_ts_time_list where type in ('".implode("','",$ts_time_types_tmp)."') ");
		$tmp = array();
		if($query){
			$record = $query->result_array();
			foreach($record as $info)
			{
				$type = $info['type'];
				$last_ts_time = $info['last_ts_time'];
				$tmp[$type] = $last_ts_time;
			}
		}
		foreach($ts_time_types_tmp as $v){
			if(!isset($tmp[$v])){
				$last_ts_time = self::DEFAULT_TIME;
				$this->db_ebmaster_write->insert('feed_ts_time_list', array('type'=>$v,'last_ts_time'=>$last_ts_time));
				$last_ts_time_list[$v] = $last_ts_time;
			} else {
				$last_ts_time_list[$v] = $tmp[$v];
			}
		}
		unset($tmp);
		$tmp = '';

		$star_time = $last_ts_time_list['product'];
		$query = $this->db_ebmaster_read->query(" select id,last_time from product where last_time > '{$star_time}' and last_time < '{$end_time}' order by last_time limit 0,{$step} ");
		$tmp = array();
		if($query){
			$record = $query->result_array();
			foreach($record as $info)
			{
				$tmp[] = $info['id'];
				$end_time_list['product'] = $info['last_time'];
			}
			$the_changed_pid_list = array_merge($the_changed_pid_list,$tmp);
			unset($tmp);
			$tmp = '';
		}
		if(isset($end_time_list['product']) && !empty($end_time_list['product'])){
			$query = $this->db_ebmaster_read->query(" select id from product where last_time = '".$end_time_list['product']."' ");
			$tmp = array();
			if($query){
				$record = $query->result_array();
				foreach($record as $info)
				{
					$tmp[] = $info['id'];
				}
				$the_changed_pid_list = array_merge($the_changed_pid_list,$tmp);
				unset($tmp);
				$tmp = '';
			}
		}

		$star_time = $last_ts_time_list['product_sku'];
		$query = $this->db_ebmaster_read->query(" select product_id,last_time from product_sku where last_time > '{$star_time}' and last_time < '{$end_time}' order by last_time limit 0,{$step} ");
		$tmp = array();
		if($query){
			$record = $query->result_array();
			foreach($record as $info)
			{
				$tmp[] = $info['product_id'];
				$end_time_list['product_sku'] = $info['last_time'];
			}
			$the_changed_pid_list = array_merge($the_changed_pid_list,$tmp);
			unset($tmp);
			$tmp = '';
		}
		if(isset($end_time_list['product_sku']) && !empty($end_time_list['product_sku'])){
			$query = $this->db_ebmaster_read->query(" select product_id from product_sku where last_time = '".$end_time_list['product_sku']."' ");
			$tmp = array();
			if($query){
				$record = $query->result_array();
				foreach($record as $info)
				{
					$tmp[] = $info['product_id'];
				}
				$the_changed_pid_list = array_merge($the_changed_pid_list,$tmp);
				unset($tmp);
				$tmp = '';
			}
		}

		$star_time = $last_ts_time_list['promote_activity_target'];
		$query = $this->db_ebmaster_read->query(" select product_id,last_time from promote_activity_target where last_time > '{$star_time}' and last_time < '{$end_time}' order by last_time limit 0,{$step} ");
		$tmp = array();
		if($query){
			$record = $query->result_array();
			foreach($record as $info)
			{
				$tmp[] = $info['product_id'];
				$end_time_list['promote_activity_target'] = $info['last_time'];
			}
			$the_changed_pid_list = array_merge($the_changed_pid_list,$tmp);
			unset($tmp);
			$tmp = '';
		}
		if(isset($end_time_list['promote_activity_target']) && !empty($end_time_list['promote_activity_target'])){
			$query = $this->db_ebmaster_read->query(" select product_id from promote_activity_target where last_time = '".$end_time_list['promote_activity_target']."' ");
			$tmp = array();
			if($query){
				$record = $query->result_array();
				foreach($record as $info)
				{
					$tmp[] = $info['product_id'];
				}
				$the_changed_pid_list = array_merge($the_changed_pid_list,$tmp);
				unset($tmp);
				$tmp = '';
			}
		}

		$star_time = $last_ts_time_list['promote_bundle_target'];
		$query = $this->db_ebmaster_read->query(" select product_id,last_time from promote_bundle_target where last_time > '{$star_time}' and last_time < '{$end_time}' order by last_time limit 0,{$step} ");
		$tmp = array();
		if($query){
			$record = $query->result_array();
			foreach($record as $info)
			{
				$tmp[] = $info['product_id'];
				$end_time_list['promote_bundle_target'] = $info['last_time'];
			}
			$the_changed_pid_list = array_merge($the_changed_pid_list,$tmp);
			unset($tmp);
			$tmp = '';
		}
		if(isset($end_time_list['promote_bundle_target']) && !empty($end_time_list['promote_bundle_target'])){
			$query = $this->db_ebmaster_read->query(" select product_id from promote_bundle_target where last_time = '".$end_time_list['promote_bundle_target']."' ");
			$tmp = array();
			if($query){
				$record = $query->result_array();
				foreach($record as $info)
				{
					$tmp[] = $info['product_id'];
				}
				$the_changed_pid_list = array_merge($the_changed_pid_list,$tmp);
				unset($tmp);
				$tmp = '';
			}
		}

		$return_arr = array();
		foreach($this->languages as $lan_id => $lan_code){
			$tmp = array();
			$desc_table = "product_description_{$lan_id}";
			$star_time = $last_ts_time_list[$desc_table];
			$query = $this->db_ebmaster_read->query(" select product_id,last_time from {$desc_table} where last_time > '{$star_time}' and last_time < '{$end_time}' order by last_time limit 0,{$step} ");
			$tmp1 = array();
			if($query){
				$record = $query->result_array();
				foreach($record as $info)
				{
					$tmp1[] = $info['product_id'];
					$end_time_list[$desc_table] = $info['last_time'];
				}
				$the_changed_pid_list = array_merge($tmp,$tmp1);
				unset($tmp1);
				$tmp1 = '';
			}
			if(isset($end_time_list[$desc_table]) && !empty($end_time_list[$desc_table])){
				$query = $this->db_ebmaster_read->query(" select product_id from {$desc_table} where last_time = '".$end_time_list[$desc_table]."' ");
				$tmp2 = array();
				if($query){
					$record = $query->result_array();
					foreach($record as $info)
					{
						$tmp2[] = $info['product_id'];
					}
					$tmp = array_merge($the_changed_pid_list,$tmp2);
					unset($tmp2);
					$tmp2 = '';
				}
			}
			$return_arr[$lan_id] = array_merge($the_changed_pid_list,$tmp);
			unset($tmp);
			$tmp = '';
		}
		if(count($end_time_list) > 0){
			/*
			foreach($end_time_list as $k => $v){
				$this->db_ebmaster_write->where('type',$k);
				$this->db_ebmaster_write->update('feed_ts_time_list',array('last_ts_time'=>$v));
			}
			*/
			$return_arr['end_time_list'] = $end_time_list;
		}
		return $return_arr;
	}

	public function exitCheck($pid,$table_name){
		$exit_check = '';
		$this->db_ebmaster_read->select('ts_time');
		$this->db_ebmaster_read->from($table_name);
		$this->db_ebmaster_read->where('pid ',$pid);
		$query = $this->db_ebmaster_read->get();
		if($query){
			$record = $query->result_array();
			foreach($record as $info)
			{
				$exit_check = $info['ts_time'];
			}
		}
		return $exit_check;
	}

	public function updateData($lan_id,$table_name,$pids){
		if(count($pids)<1 || $lan_id < 1 || empty($table_name)){
			return false;
		}
		$productObj = new ProductModel();
		$pro_infos = $productObj->getProInfoById($pids, $lan_id);
		if(!is_array($pro_infos) || count($pro_infos) < 1){
			return false;
		}
		$arr = array();
		foreach($pro_infos as $pro_info){
			$arr = array();
			$arr['pid'] = $pro_info['product_id'];
			$arr['category_id'] = $pro_info['category_id'];
			$arr['name'] = $pro_info['name'];
			$arr['desc'] = $pro_info['content'];
			$arr['url'] = $pro_info['url'];
			$arr['image'] = HelpUrl::img($pro_info['image'], 350);
			$arr['market_price'] = $pro_info['market_price'];
			$arr['status'] = $pro_info['status'];
			$skuInfos = $pro_info['skuInfo'];
			if(count($skuInfos)<1){
				$skuInfos_tmp = $productObj->getProductSkuInfo(array($arr['pid']), TRUE );
				$skuInfos = isset($skuInfos_tmp[$arr['pid']]) ? $skuInfos_tmp[$arr['pid']] : array();
				unset($skuInfos_tmp);
				$skuInfos_tmp = '';
			}
			$arr['final_price'] = $pro_info['final_price'];
			$arr['promote_discount'] = $pro_info['format_promote_discount'];
			$promote_info = array();
			if(isset($pro_info['promote_info']) && is_array($pro_info['promote_info']) && isset($pro_info['promote_info'][1]))
			{
				$promote_info = is_array($pro_info['promote_info'][1]) ? $pro_info['promote_info'][1] : array();
			}
			$arr['promote_start_time'] = isset($promote_info['start_time']) ? $promote_info['start_time'] : '2010-01-01 00:00:00';
			$arr['promote_end_time'] = isset($promote_info['end_time']) ? $promote_info['end_time'] : '2028-01-01 00:00:00';
			unset($promote_info);
			$promote_info = '';
			$skus = array();
			$stock_tmp = 0;
			$weight_tmp = 0;
			$cost_price_tmp = 0;
			$sensitive_type_tmp = self::SENSITIVE_TYPE_ORDINARY;
			foreach($skuInfos as $sku => $skuInfo){
				$tmp = array();
				$tmp['warehouse'] = isset($pro_info['warehouse'])? $pro_info['warehouse']:'';
				$tmp['stock'] = $skuInfo['stock'];
				$stock_tmp = $skuInfo['stock'] > $stock_tmp ? $skuInfo['stock']:$stock_tmp;
				$tmp['status'] = $skuInfo['status'];
				$tmp['weight'] = $skuInfo['weight'];
				$weight_tmp = $skuInfo['weight'] > $weight_tmp ? $skuInfo['weight']:$weight_tmp;
				$tmp['cost_price'] = $skuInfo['cost_price'];
				$tmp['add_time'] = $pro_info['add_time'];
				$cost_price_tmp = $skuInfo['cost_price'] > $cost_price_tmp ? $skuInfo['cost_price']:$cost_price_tmp;
				$tmp['final_price'] = $skuInfo['final_price'];
				$tmp['format_final_price'] = $this->formatPrice($tmp['final_price']);
				$tmp['final_market_price'] = $skuInfo['final_market_price'];
				$tmp['format_final_market_price'] = $this->formatPrice($tmp['final_market_price']);
				$type_sensitive = $skuInfo['type_sensitive'];
				$sensitive_type = self::SENSITIVE_TYPE_ORDINARY;
				if($arr['status'] == '2'){
					$sensitive_type = self::SENSITIVE_TYPE_INFRINGEMENT;
					$sensitive_type_tmp = $sensitive_type;
				} else {
					switch($type_sensitive){
						case '1':
							$sensitive_type = self::SENSITIVE_TYPE_WITH_BATTERY;
							break;
						case '2':
							$sensitive_type = self::SENSITIVE_TYPE_POWDER;
							break;
						case '3':
							$sensitive_type = self::SENSITIVE_TYPE_LIQUID;
							break;
						case '4':
							$sensitive_type = self::SENSITIVE_TYPE_OTHER;
							break;
						case '5':
							$sensitive_type = self::SENSITIVE_TYPE_PURE_BATTERY;
							break;
					}
					$sensitive_type_tmp = $sensitive_type;
				}
				$tmp['sensitive_type'] = $sensitive_type;
				$skus[$sku] = $tmp;
				unset($skuInfo);
				$skuInfo = '';
				unset($tmp);
				$tmp = '';
			}
			$tmp = array();
			$tmp['warehouse'] = isset($pro_info['warehouse'])? $pro_info['warehouse']:'';
			$tmp['stock'] = $stock_tmp;
			$tmp['status'] = $pro_info['status'];
			$tmp['weight'] = $weight_tmp;
			$tmp['add_time'] = $pro_info['add_time'];
			$tmp['cost_price'] = $cost_price_tmp;
			$tmp['final_price'] = $pro_info['final_price'];
			$tmp['format_final_price'] = $this->formatPrice($tmp['final_price']);
			$tmp['final_market_price'] = $pro_info['market_price'];
			$tmp['format_final_market_price'] = $this->formatPrice($tmp['final_market_price']);
			$tmp['sensitive_type'] = $sensitive_type_tmp;
			$skus['default'] = $tmp;
			unset($tmp);
			$tmp = '';
			unset($skuInfos);
			$skuInfos = '';
			$arr['skus'] = json_encode($skus);
			unset($skus);
			$skus = '';
			$exitCheck = $this->exitCheck($arr['pid'],$table_name);
			if(!empty($exitCheck)){
				$this->db_ebmaster_write->where('pid',$arr['pid']);
				$this->db_ebmaster_write->update($table_name, $arr);
			} else {
				$this->db_ebmaster_write->insert($table_name, $arr);
			}
			unset($pro_info);
			$pro_info = '';
			unset($arr);
			$arr = '';
		}
		unset($pro_infos);
		$pro_infos = '';
		unset($productObj);
		$productObj = '';
	}

	/*
	| -------------------------------------------------------------------
	|  检测数据更新
	|  字段：product.last_time 可以监测:category_id/image/final_price/market_price/status/
	|  字段：product_sku.last_time 可以监测:skus/skus(image)/skus(final_price)/skus(final_market_price)/skus(weight)/skus(sensitive_type)//skus(stock)/skus(status)
	|  字段：product_description_1.last_time 可以监测:name/desc/url/
	|  字段：promote_activity_target.last_time 可以监测:promote_discount/promote_start_time/promote_end_time/final_price
	|  字段：promote_bundle_target.last_time 可以监测:promote_discount/promote_start_time/promote_end_time/final_price
	| -------------------------------------------------------------------
	|  $mixedData['img350'] = HelpUrl::img($mixedData['image'], 350); //http://pic.eachbuyer.com/350x350/x1/p11/bg80_a.jpg?v=20140228102610
	*/
	public function dataTrack(){
		$step = 50;
		$this->getLanguageList();
		$the_changed_pid_list = $this->getTheChangedPidList();
		$end_time_list = $the_changed_pid_list['end_time_list'];
		foreach($this->languages as $lan_id => $lan_code){
			if(!is_array($the_changed_pid_list[$lan_id]) || count($the_changed_pid_list[$lan_id]) < 1){
				continue;
			}
			$table_name = "feed_data_track_{$lan_code}";
			$tmp_pids = $the_changed_pid_list[$lan_id];
			$tmp = array();
			foreach($tmp_pids as $pid){
				$tmp[] = $pid;
				if(count($tmp) >= $step){
					$this->updateData($lan_id,$table_name,$tmp);
					unset($tmp);
					$tmp = array();
				}
			}
			if(count($tmp) > 0){
				$this->updateData($lan_id,$table_name,$tmp);
				unset($tmp);
				$tmp = array();
			}
		}
		if(count($end_time_list) > 0){
			foreach($end_time_list as $k => $v){
				$this->db_ebmaster_write->where('type',$k);
				$this->db_ebmaster_write->update('feed_ts_time_list',array('last_ts_time'=>$v));
			}
		}
	}

	function getCategoryInfoList($language_id = 1){
		$category_info_list = array();
		$done = 0;
		$pids = array('0');
		while(count($pids) > 0){
			$pids_str = implode(',',$pids);
			$pids = array();
			$query = $this->db_ebmaster_read->query(" select c.id,c.path,cd.name from category c inner join category_desc cd on c.id = cd.category_id where cd.language_id = {$language_id} and c.p_id in ({$pids_str}) ");
			if($query){
				$record = $query->result_array();
				foreach($record as $info)
				{
					$id = $info['id'];
					$path = $info['path'];
					$name = !empty($info['name'])? $info['name'] : 'no value';
					$full_path = $name;
					$path_arr = explode('/',$path);
					$full_path_arr = array();
					foreach($path_arr as $v){
						$full_path_arr[] = $v != $id ? $category_info_list[$v]['name'] : $name;
					}
					$full_path = implode('/',$full_path_arr);
					$pids[] = $id;
					$category_info_list[$id]['id'] = $id;
					$category_info_list[$id]['path'] = $path;
					$category_info_list[$id]['name'] = $name;
					$category_info_list[$id]['full_path'] = $full_path;
				}
			}
		}
		return $category_info_list;
	}

	public function exitCategoryCheck($cid,$table_name){
		$exit_check = '';
		$this->db_ebmaster_read->select('ts_time');
		$this->db_ebmaster_read->from($table_name);
		$this->db_ebmaster_read->where('cid ',$cid);
		$query = $this->db_ebmaster_read->get();
		if($query){
			$record = $query->result_array();
			foreach($record as $info)
			{
				$exit_check = $info['ts_time'];
			}
		}
		return $exit_check;
	}
	/*
	|  cid/id_path/full_path/google_path/bing_path/ts_time
	*/
	public function categoryTrack(){
		$this->getLanguageList();
		foreach($this->languages as $lan_id => $lan_code){
			$table_name = "feed_category_path_{$lan_code}";
			$category_info_list = $this->getCategoryInfoList($lan_id);
			foreach($category_info_list as $category_info){
				$id = $category_info['id'];
				$path = $category_info['path'];
				$full_path = $category_info['full_path'];
				$exitCheck = $this->exitCategoryCheck($id,$table_name);
				if(!empty($exitCheck)){
					$this->db_ebmaster_write->where('cid',$id);
					$this->db_ebmaster_write->update($table_name, array('id_path'=>$path,'full_path'=>$full_path));
				} else {
					$this->db_ebmaster_write->insert($table_name, array('cid'=>$id,'id_path'=>$path,'full_path'=>$full_path));
				}
			}
		}
	}
}

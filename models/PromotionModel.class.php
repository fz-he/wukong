<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * 专题汇总 model.
 * @author qcn
 */
class PromotionModel extends CI_Model {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 获取最近访问的专题列表
	 * @param  string  $date date('Y-m-d')格式化的时间
	 * @param  integer $language 语言id
	 */
	public function getCurrentSpecialTopicList($date, $language = 1) {
		$cacheKey = "get_current_special_topic_list_%s_%s";
		$memcacheKeyStrCode = array($date, $language);
		$list = $this->memcache->get($cacheKey,$memcacheKeyStrCode);

		// 缓存为空的时候取出数据
		if($list === false) {
			// 取出数据
			$this->db_read->from('special_topic');
			$this->db_read->where('special_topic_status', 1);
			$this->db_read->where('special_topic_date_start <=', $date);
			$this->db_read->where('special_topic_date_end >=', $date);
			$this->db_read->order_by('special_topic_date_start', 'desc');
			$query = $this->db_read->get();
			$list = $query->result_array();

			// 获取专题id
			$specialTopicIds = array();
			// 专题多语言
			$multilingual = array();
			// 语言数据
			$multilingualBuffer = array();
			if(!empty($list)) {
				$specialTopicIds = extractColumn($list,'special_topic_id');

				if(!empty($specialTopicIds)) {
					// 取出专题多语言的数据
					$this->db_read->from('special_topic_multilingual');
					$this->db_read->where_in('special_topic_id', $specialTopicIds);
					$this->db_read->where('language_id', $language);
					$queryMul = $this->db_read->get();
					$multilingual = $queryMul->result_array();
				}

				// 循环处理多语言
				if(!empty($multilingual)) {
					foreach ($multilingual as $record) {
						if($record['special_topic_multilingual_title'] == '') { continue; }
						if($record['special_topic_multilingual_pic_banner'] == '') { continue; }
						if($record['special_topic_multilingual_url'] == '') { continue; }
						$multilingualBuffer[$record['special_topic_id']] = $record;
					}
				}

				// 数据处理
				foreach ($list as $key => $record) {
					if(!isset($multilingualBuffer[$record['special_topic_id']])) {
						unset($list[$key]);
						continue;
					}

					$list[$key]['multilingual'] = $multilingualBuffer[$record['special_topic_id']];
				}

				// 将数据写入缓存
				$this->memcache->set($cacheKey,$list,$memcacheKeyStrCode);
			}
		}

		return $list;
	}

	/**
	 * 获取历史专题列表
	 * @param  string  $date date('Y-m-d')格式化的时间
	 * @param  integer $language 语言id
	 * @param  integer $start 指针
	 */
	public function getHistorySpecialTopicList($date, $language = 1, $start = 0) {
		$cacheKey = "get_history_special_topic_list_%s_%s_%s";
		$memcacheKeyStrCode = array($date, $language, $start);
		$list = $this->memcache->get($cacheKey,$memcacheKeyStrCode);
		// 缓存数据为空的时候
		if($list === false) {
			// 从数据库中取出数据
			$this->db_read->from('special_topic');
			$this->db_read->where('special_topic_status', 1);
			$this->db_read->where('special_topic_date_start <=', $date);
			$this->db_read->where('special_topic_date_end <=', $date);
			$this->db_read->order_by('special_topic_date_start', 'desc');
			$this->db_read->limit(SPECIAL_PAGE_LIMIT, $start);
			$query = $this->db_read->get();
			$list = $query->result_array();
			// 获取专题id
			$specialTopicIds = array();
			// 专题多语言
			$multilingual = array();
			if(!empty($list)) {
				// 专题id
				$specialTopicIds = extractColumn($list,'special_topic_id');

				if(!empty($specialTopicIds)) {
					// 取出专题多语言的数据
					$this->db_read->from('special_topic_multilingual');
					$this->db_read->where('language_id', $language);
					$this->db_read->where_in('special_topic_id', $specialTopicIds);
					$this->db_read->where('special_topic_multilingual_title !=', '');
					$this->db_read->where('special_topic_multilingual_pic_thumb !=', '');
					$this->db_read->where('special_topic_multilingual_url !=', '');
					$queryMul = $this->db_read->get();
					$multilingual = $queryMul->result_array();
					$multilingual = reindexArray($multilingual, 'special_topic_id');
				}
				foreach ($list as $key => $value) {
					$id = $value['special_topic_id'];
					if(isset($multilingual[$id]) && !empty($multilingual[$id])) {
						$list[$key] += $multilingual[$id];
					} else {
						unset($list[$key]);
					}
				}

				// 将数据写入缓存
				$this->memcache->set($cacheKey,$list,$memcacheKeyStrCode);
			}
		}

		return $list;
	}

	/**
	 * 获取历史专题列表的总数
	 * @param  string  $date date('Y-m-d')格式化的时间
	 * @param  integer $language 语言id
	 */
	public function getHistorySpecialTopicCount($date, $language = 1) {
		$cacheKey = "get_history_special_topic_count_%s_%s";
		$memcacheKeyStrCode = array($date, $language);
		$listCount = $this->memcache->get($cacheKey,$memcacheKeyStrCode);
		// 缓存数据为空的时候
		if($listCount === false) {
			// 从数据库中取出数据
			$this->db_read->select('special_topic_id');
			$this->db_read->from('special_topic');
			$this->db_read->where('special_topic_status', 1);
			$this->db_read->where('special_topic_date_start <=', $date);
			$this->db_read->where('special_topic_date_end <=', $date);
			$query = $this->db_read->get();
			$list = $query->result_array();
			// 获取专题id
			$specialTopicIds = array();
			// 专题多语言
			$multilingual = array();
			if(!empty($list)) {
				// 专题id
				$specialTopicIds = extractColumn($list,'special_topic_id');

				if(!empty($specialTopicIds)) {
					// 取出专题多语言的数据
					$this->db_read->select('special_topic_id');
					$this->db_read->from('special_topic_multilingual');
					$this->db_read->where('language_id', $language);
					$this->db_read->where_in('special_topic_id', $specialTopicIds);
					$this->db_read->where('special_topic_multilingual_title !=', '');
					$this->db_read->where('special_topic_multilingual_pic_thumb !=', '');
					$this->db_read->where('special_topic_multilingual_url !=', '');
					$queryMul = $this->db_read->get();
					$multilingual = $queryMul->result_array();
					$multilingual = reindexArray($multilingual, 'special_topic_id');
				}
				foreach ($list as $key => $value) {
					$id = $value['special_topic_id'];
					if(isset($multilingual[$id]) && !empty($multilingual[$id])) {
						$list[$key] += $multilingual[$id];
					} else {
						unset($list[$key]);
					}
				}
				$listCount = count($list);
				// 将数据写入缓存
				$this->memcache->set($cacheKey, $listCount, $memcacheKeyStrCode);
			}
		}

		return $listCount;
	}
}

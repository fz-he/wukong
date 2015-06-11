<?php 

namespace app\components\helpers;

class ArrayHelper 
{	
	public static  function spreadArray($orig,$column)
	{
		$result = array();

		if(is_array($orig)){
			foreach($orig as $record){

				if(isset($record[$column])){
					$key = $record[$column];
				}else{
					$key = 0;
				}

				$result[$key][] = $record;
			}
		}

		return $result;
		
	}

	public static  function extractColumn($list,$column){
		$result = array();

		if(is_array($list)){
			foreach($list as $record){

				if(isset($record[$column])){
					$result[] = $record[$column];
				}
			}
		}
		$result = array_unique($result);

		return $result;
	}


	public static  	function reindexArray($orig,$column){
		$result = array();

		if(is_array($orig)){
			foreach($orig as $record){

				if(isset($record[$column])){
					$key = $record[$column];
				}else{
					$key = 0;
				}

				$result[$key] = $record;
			}
		}

		return $result;
	}

	public static  	function sortArray(&$arr,$column,$dir = SORT_ASC){//SORT_DESC
		$sortColumn = array();
		foreach ($arr as $key => $row) {
			$sortColumn[$key]  = $row[$column];
		}

		array_multisort($sortColumn,$dir,$arr);
	}
	
	public static  	function id2name($id,$arr,$default = ''){
		if(!is_array($arr) || empty($arr)) return $default;

		if(isset($arr[$id])){
			return $arr[$id];
		}else{
			return $default;
		}
	}
	


	/* 二维数组按指定的键值排序
	* $array 数组
	* $key排序键值
	* $type排序方式
	*/
	public static  	function array_sort($arr, $keys, $type = 'desc') {
		$keysvalue = $new_array = array();
		foreach ($arr as $k => $v) {
			$keysvalue[$k] = $v[$keys];
		}
		if ($type == 'asc') {
			asort($keysvalue);
		} else {
			arsort($keysvalue);
		}
		reset($keysvalue);
		foreach ($keysvalue as $k => $v) {
			$new_array[$k] = $arr[$k];
		}
		return $new_array;
	}
}

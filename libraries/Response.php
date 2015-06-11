<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Response {

	protected $_items = array();
	protected $_totalCount = 0;
	protected $_errorCode = 0;
	protected $_detail = '';
	protected $_success = FALSE;
	protected $_addon = array();
	protected $_error_code = 0; //Default 0.
	protected $_data = NULL;
	protected $_msg = '';

	public function __construct(){}

	public function setItems($input){
		if(!is_array($input)) $input = array($input);
		$this->_items = $input;
	}

	public function getItems(){
		return $this->_items;
	}

	public function setTotalCount($input){
		if(null !== $input){
			$this->_totalCount = intval($input);
		}
	}

	public function getTotalCount(){
		return $this->_totalCount;
	}

	public function setDetail($input){
		if(null !== $input){
			$this->_detail = strval($input);
		}
	}

	public function getDetail(){
		return $this->_detail;
	}

	public function setSuccess(){
		$this->_success = TRUE;
	}

	public function isSuccess(){
		return $this->_success;
	}

	public function setAddon($input){
		if(!is_array($input)) $input = array($input);
		$this->_addon = $input;
	}

	public function getAddon(){
		return $this->_addon;
	}



	public function responseExit(){
		if($this->_totalCount == 0 && count($this->_items) > 0){
			$this->_totalCount = count($this->_items);
		}
		$resArray = array(
			'items' => $this->_items,
			'errorCode' => $this->_errorCode ,
			'totalCount' => $this->_totalCount ,
			'detail'     => $this->_detail,
			'success'    => $this->_success,
			'addon'      => $this->_addon,
		);

		echo json_encode($resArray);
		exit();
	}

	public function setErrorCode( $errorCode = 0 ){
		return $this->_errorCode = $errorCode ;
	}

	public function setData($data){
		if(!is_array($data)) {$data = array($data);}
		$this->_data = $data;
	}

	public function setMsg($msg){
		$this->_msg = $msg;
	}

	/**
	 * Respinse the format result.
	 * 	@author Albie
	 */
	public function responseOutput() {
		echo json_encode(array(
			'error_code' => $this->_errorCode,
			'msg' => $this->_msg,
			'data' => $this->_data
		));
		exit();
	}

}

/* End of file response.php */
/* Location: ./application/libraries/response.php */
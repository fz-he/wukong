<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Outputjson {
	/*
	 * 错误code
	 * int 12000  //1-20-30 1:代表模块 20：模块内的区域 30：代表具体的错误码
	 */
	protected $_errorCode = 0;
	/*
	 * 错误描述
	 * string
	 */
	protected $_msg = '';
	/*
	 * 详情信息
	 * array()
	 */
	protected $_data = array();


	public function __construct(){}

	public function setErrorCode( $errorCode = 0 ){
		return $this->_errorCode = $errorCode ;
	}

	public function getErrorCode(){
		return $this->_errorCode;
	}

	public function setMsg($msg = ''){
		return $this->_msg = $msg ;
	}

	public function getMsg(){
		return $this->_msg;
	}

	public function setData( $data = array() ){
		if( is_array( $data ) ) {
			return $this->_data = $data ;
		}else{
			return FALSE;
		}
	}

	public function getData( $data = array() ){
		return $this->_data ;
	}

	public function OutputJson(){
		$jsonArray = array(
			'errorCode' => $this->_errorCode ,
			'msg' => $this->_msg ,
			'data'     => $this->_data
		);
		echo json_encode($jsonArray);
		exit();
	}
}

/* End of file response.php */
/* Location: ./application/libraries/response.php */
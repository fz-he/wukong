<?php
/**
 *
 * Enter description here ...
 * @author Administrator
 *
 */

require_once 'ApiClientException.php';

/**
 *
 * Enter description here ...
 * @author Administrator
 *
 */
class ApiService{

	private $ebmServiceUrl = array(
		"setlist1"		=>"https://ebm.cheetahmail.com/cgi-bin/api/setlist1",
		"getlist1"		=>"https://ebm.cheetahmail.com/cgi-bin/api/getlist1",
		"setuser1"		=>"https://ebm.cheetahmail.com/api/setuser1",
		"getuser1"		=>"https://ebm.cheetahmail.com/api/getuser1",
		"ebmtrigger1"	=>"https://ebm.cheetahmail.com/ebm/ebmtrigger1"
	);
	private $appServiceUrl = array(
		"bulkmail1"		=>"https://app.cheetahmail.com/api/bulkmail1",
		"getissues1"	=>"https://app.cheetahmail.com/cgi-bin/api/getissues1",
		"mailgo1"		=>"https://app.cheetahmail.com/cgi-bin/api/mailgo1",
		"mailresult1"	=>"https://app.cheetahmail.com/cgi-bin/api/mailresults1",
		"setmail1"		=>"https://app.cheetahmail.com/cgi-bin/api/setmail1",
		"load1"			=>"https://app.cheetahmail.com/cgi-bin/api/load1",
		"unsub1"		=>"https://app.cheetahmail.com/cgi-bin/api/unsub1"
	);
	private $loginUrl = array(
		'ebm'=>'https://ebm.cheetahmail.com/api/login1',
		'app'=>'https://ebm.cheetahmail.com/api/login1'
	);

	//private $name = "PUT USERNAME HERE";
	//private $cleartext = "PUT PASSWORD HERE";
	private $name = "eachbuyer@api";
	private $cleartext = "Forward@15";

	/**
	 *
	 * Enter description here ...
	 * @var file
	 */
	private static $cookieFile;
	var $myCookieFile;

	/**
	 *
	 * Enter description here ...
	 */
	public function __construct(){
		//self::$cookieFile = tempnam('./', 'apicookie');
		$this->myCookieFile = tempnam('/tmp/cheatmail/', 'apicookie'); //lwy
	}

	/**
	 *
	 * Enter description here ...
	 * @throws ApiClientException
	 */
	public function login($server){

		try {
			//echo "server:$server";
			$url = $this->getLoginUrl($server);
			//echo "loginurl:$url";
			$param['name'] = $this->name;
			$param['cleartext'] = $this->cleartext;
			$param = http_build_query($param);
			//echo "param:$param";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true );
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			//curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->myCookieFile); //lwy
			$result = curl_exec($ch);
			curl_close($ch);
			if (strpos($result, "OK") === false){
				throw new ApiClientException("LOGIN ERROR: " . $result);
			}
		}catch (ApiClientException $e){
			return $e->getMessage();
		}
	}

	/**
	 *
	 * Enter description here ...
	 * @param string $name
	 * @param array $param
	 * @param string $file
	 */
	public function callMethod($name, $param, $file = null){
		//var_dump ($param);
		$result = null;
		try {
			$server = $this->getServer($name);
			//echo "server=$server";
			$this->login($server);
			$url = $this->getServiceUrl($name);
			//	echo "url=$url";
			if (null != $file){
				if(strcmp($name,"bulkmail1")==0)
				{

					$param['htmlfile'] = '@' . $file;
				}
				else
				{
					$param['file'] = '@' . $file;
				}
			}else {
				// note: this will not work for APIs that expect a file
				$param = http_build_query($param);
				//var_dump ($param);
			}



			$ch = curl_init();

			//print_r($param);
			if (null != $file){
						$this->curl_setopt_custom_postfields($ch, $param);
			}
			else
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
			}
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true );


			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			//curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->myCookieFile); //lwy

			$result = curl_exec($ch);

			curl_close($ch);
			if (strpos($result, "OK") === false){
				throw new ApiClientException("ERROR: " . $result);
			}
		} catch (ApiClientException $e) {
			return $e->getMessage();
		}
		return $result;
	}


	function curl_setopt_custom_postfields($ch, $postfields, $headers = null) {
		$algos = hash_algos();
		$hashAlgo = null;
		foreach ( array('sha1', 'md5') as $preferred ) {
			if ( in_array($preferred, $algos) ) {
				$hashAlgo = $preferred;
				break;
			}
		}
		if ( $hashAlgo === null ) { list($hashAlgo) = $algos; }
		$boundary =
			'----------------------------' .
			substr(hash($hashAlgo, 'cURL-php-multiple-value-same-key-support' . microtime()), 0, 12);

		$body = array();
		$crlf = "\r\n";
		$fields = array();
		if(is_array($postfields)){
		foreach ( $postfields as $key => $value ) {
			if ( is_array($value) ) {
				foreach ( $value as $v ) {
					$fields[] = array($key, $v);
				}
			} else {
				$fields[] = array($key, $value);
			}
		}}
		foreach ( $fields as $field ) {
			list($key, $value) = $field;
			if ( strpos($value, '@') === 0 ) {
				preg_match('/^@(.*?)$/', $value, $matches);
				list($dummy, $filename) = $matches;
				$body[] = '--' . $boundary;
				$body[] = 'Content-Disposition: form-data; name="' . $key . '"; filename="' . basename($filename) . '"';
				$body[] = 'Content-Type: application/octet-stream';
				$body[] = '';
				$body[] = file_get_contents($filename);
			} else {
				$body[] = '--' . $boundary;
				$body[] = 'Content-Disposition: form-data; name="' . $key . '"';
				$body[] = '';
				$body[] = $value;
			}
		}
		$body[] = '--' . $boundary . '--';
		$body[] = '';
		$contentType = 'multipart/form-data; boundary=' . $boundary;
		$content = join($crlf, $body);
		$contentLength = strlen($content);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Length: ' . $contentLength,
			'Expect: 100-continue',
			'Content-Type: ' . $contentType,
		));
		//var_dump($body,$fields);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $name
	 */
	public function getServer($name){

		if (array_key_exists($name, $this->appServiceUrl)) {
			return "ebm";
		}else if (array_key_exists($name, $this->ebmServiceUrl)){
			return "app";
		}
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $server
	 */
	public function getLoginUrl($server){
		return $this->loginUrl[$server];
	}

	/**
	 *
	 * Enter description here ...
	 * @param string $name
	 * @throws ApiClientException
	 */
	public function getServiceUrl($name){

		if (array_key_exists($name, $this->ebmServiceUrl)){
			return $this->ebmServiceUrl[$name];
		}else if (array_key_exists($name, $this->appServiceUrl)){
			return $this->appServiceUrl[$name];
		}
	}
	
	public function getReturnValue($character,$returnResponse)
	{
		$sidfromSetlistArry=explode($character,$returnResponse);
		$returnResponse=substr($sidfromSetlistArry[1],0,10);
		return $returnResponse;
	}
}
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-21
 * Time: 下午12:23
 */
class Curl
{
    private $ch;
    private $flag_if_have_run;
    public $url = '';
    public function init(){
        $this->ch = curl_init($this->url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER , 1 );
        curl_setopt($this->ch, CURLOPT_HEADER, 0);
        return $this;
    }

    public function close()
    {
        curl_close($this->ch);
    }

//    public function __destruct()
//    {
//        $this->close();
//    }

    public function set_time_out($timeout)
    {
        curl_setopt($this->ch, CURLOPT_TIMEOUT, intval($timeout));
        return $this;
    }

    public function set_referer($referer)
    {
        if (!empty($referer))
            curl_setopt($this->ch, CURLOPT_REFERER , $referer);
        return $this;
    }

    public function set_header($header){
        if(!empty($header) && is_array($header))
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
        return $this;
    }
    public function load_cookie($cookie_file)
    {
        curl_setopt($this->ch, CURLOPT_COOKIEFILE , $cookie_file);
        return $this;
    }

    public function save_cookie($cookie_file="")
    {
        //设置缓存文件，例如a.txt
        if(empty($cookie_file))
            $cookie_file = tempnam('./', 'cookie');
        curl_setopt($this->ch, CURLOPT_COOKIEJAR , $cookie_file);
        return $this;
    }

    public function exec ()
    {
        $str = curl_exec($this->ch);
        $this->flag_if_have_run = true;
        if($str == false)
            return $this->get_error();
        return $str;
    }

    public function post ($post)
    {
        curl_setopt($this->ch, CURLOPT_POST , 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS , $post );
        return $this;
    }

    public function get_info()
    {
        if($this->flag_if_have_run == true )
            return curl_getinfo($this->ch);
        else
            throw new Exception("<h1>需先运行( 执行exec )，再获取信息</h1>");
    }

    public function set_proxy($proxy)
    {
        //设置代理 ,例如'68.119.83.81:27977'
        curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($this->ch, CURLOPT_PROXY,$proxy);
        return $this;
    }

    public function set_ip($ip)
    {
        if(!empty($ip))
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("X-FORWARDED-FOR:$ip", "CLIENT-IP:$ip"));
        return $ip;
    }
    public function get_error(){
        return curl_error($this->ch);
    }
}

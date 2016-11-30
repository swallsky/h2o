<?php
/**
 * http协议
 * @category   H2O
 * @package    helpers
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\helpers;
class Http
{
    /**
     * @var string 默认浏览器头信息
     */
    public static $agent = '';
    /**
     * 设置用户浏览器头信息
     * @param $str 例如:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36
     */
    public static function setAgent($str)
    {
        self::$agent = $str;
    }
    /**
     * 返回用户浏览器头信息
     * @return string
     */
    public static function getAgent()
    {
        return self::$agent;
    }
    /**
     * get http 远程数据
     * @param string $url 远程地址
     * @param int $timeout 超时时间 单位秒
     * @return array code:状态码 例如 200,403,404等 html:被抓取的html信息
     */
    public static function get($url,$timeout = 10)
    {
        if(function_exists("curl_init")){
            $https = curl_init();
            curl_setopt($https, CURLOPT_URL, $url);
            curl_setopt($https, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($https, CURLOPT_CONNECTTIMEOUT,$timeout);
            if(!empty(self::$agent)) curl_setopt($https, CURLOPT_USERAGENT,self::$agent); //浏览器信息
            $html = curl_exec($https);
            $reponse_code = curl_getinfo($https,CURLINFO_HTTP_CODE);//状态码
            curl_close($https);
        }else{
            if(!empty(self::$agent)) ini_set('user_agent',self::$agent); //设置浏览器信息
            $opts = array(
                'http'=>array(
                    'method'    =>  "GET",
                    'timeout'   =>  $timeout
                )
            );
            $context = stream_context_create($opts);
            $html = file_get_contents($url,false,$context);
            preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$http_response_header[0],$out);
            $reponse_code = intval($out[1]);
        }
        return [
            'code'  =>  $reponse_code, //状态码
            'html'  =>  $html //页面信息
        ];
    }

    /**
     * post http 远程提交
     * @param string $url 远程地址
     * @param array $data 需要提交的字段信息 例如 ['name'=>'test','sex'=>'男']
     * @return array code:状态码 例如 200,403,404等 html:被抓取的html信息
     */
    public static function post($url,$data)
    {
        if(function_exists("curl_init")){
            $https = curl_init();
            curl_setopt($https, CURLOPT_URL, $url);
            curl_setopt($https, CURLOPT_RETURNTRANSFER, 1);
            if(!empty(self::$agent)) curl_setopt($https, CURLOPT_USERAGENT,self::$agent); //浏览器信息
            curl_setopt($https, CURLOPT_POST, 1);// post数据
            curl_setopt($https, CURLOPT_POSTFIELDS, $data);// post的变量
            $html = curl_exec($https);
            $reponse_code = curl_getinfo($https,CURLINFO_HTTP_CODE);//状态码
            curl_close($https);
        }else{
            if(!empty(self::$agent)) ini_set('user_agent',self::$agent); //设置浏览器信息
            $data = http_build_query($data);
            $opts = array(
                'http'=>array(
                    'method'=>"POST",
                    'header'=>"Content-type: application/x-www-form-urlencoded\r\n"."Content-length:".strlen($data)."\r\n",
                    'content' => $data,
                )
            );
            $cxContext = stream_context_create($opts);
            $html = file_get_contents($url,false,$cxContext);
            preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$http_response_header[0],$out);
            $reponse_code = intval($out[1]);
        }
        return [
            'code'  =>  $reponse_code, //状态码
            'html'  =>  $html //页面信息
        ];
    }
}
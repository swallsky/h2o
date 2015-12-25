<?php
/**
 * 访问请求基类
 * @category   H2O
 * @package    web
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\web;
class Request
{
	/**
	 * @var string 路由器控制器关键字
	 */
	private $routeKey = 'r';
	/**
	 * @var string 默认Controller名称
	 */
	private $defaultController = 'index';
	/**
	 * @var string 默认Action名称
	 */
	private $defaultAction = 'index';
	/**
	 * @var string url模式 默认为空时普通模式 当值为route时则为路由模式
	 */
	private $urlMode = '';
	/**
	 * @var string 真实的URL路径 路由模式会被转换为真实的URL路径
	 */
	private $realUrl = '';
	/**
	 * 路由配置
	 * @param array $config 路由规则
	 */
	public function __construct($config = [])
	{
		\H2O::configure($this, $config);
	}
	/**
	 * 将虚拟的路径转换为真实路径
	 */
	private function transRealUrl()
	{
		
	}
	/**
	 * 返回header头信息
	 */
	public function getHeaders()
	{
		if (function_exists('getallheaders')){
			$headers = getallheaders();
		}elseif(function_exists('http_get_request_headers')){
			$headers = http_get_request_headers();
		}else{
			$headers = [];
			foreach ($_SERVER as $name => $value) {
				if (substr($name, 0, 5) == 'HTTP_') {
					$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
			}
		}
		return $headers;
	}
	/**
	 * 获取路由控制器和动作
	 */
	public function getRoute()
	{
		$routepath = $this->_getRoutePath();
		if(empty($routepath)){//默认路由规则
			$data = [
				'controller'	=>	$this->defaultController,
				'action'			=>	$this->defaultAction,
				'postdata'		=>	[]
			];
		}else{//其他路由
			$pointcnt = substr_count($routepath,'.');
			if($pointcnt==1){
				$ep = explode('.',$routepath);
				$data = [
					'controller'	=>	$ep[0],
					'action'			=>	$ep[1],
					'postdata'		=>	isset($_GET)?$_GET:[]
				];
			}else{
				throw new \H2O\base\Exception('H2O\web\request','check your router!');
			}
		}
		unset($_GET);
		return $data;
	}
	/**
	 * 获取路由信息
	 */
	private function _getRoutePath()
	{
		$inPath = dirname($this->_getScriptUrl());
		$requesturi = $_SERVER['REQUEST_URI'];
		$wpos = strpos($requesturi,'?');
		$tcapath = $wpos===false?$requesturi:substr($requesturi,0,$wpos);
		$capath = str_replace($inPath,'',$tcapath);
		return trim($capath,'/');
	}
	/**
	 * 查找对应的脚本URL
	 */
	private function _getScriptUrl()
	{
		$scriptName = basename($_SERVER['SCRIPT_FILENAME']);
		if(basename($_SERVER['SCRIPT_NAME'])===$scriptName)
			$scriptUrl = $_SERVER['SCRIPT_NAME'];
		elseif(basename($_SERVER['PHP_SELF'])===$scriptName)
			$scriptUrl = $_SERVER['PHP_SELF'];
		elseif(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME'])===$scriptName)
			$scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
		elseif(($pos=strpos($_SERVER['PHP_SELF'],'/'.$scriptName))!==false)
			$scriptUrl = substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$scriptName;
		elseif(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'],$_SERVER['DOCUMENT_ROOT'])===0)
			$scriptUrl = str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
		else
			throw new \H2O\base\Exception('H2O\web\request','It is unable to determine the entry script URL.');
		return $scriptUrl;
	}
}
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
		return $_GET;
	}
	/**
	 * 获取URL参数
	 */
	public function getParams()
	{
		
	}
	/**
	 * 获取提交数据
	 */
	public function getPostData()
	{
		
	}
}
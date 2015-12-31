<?php
/**
 * Web应用的基类
 * @category   H2O
 * @package    web
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\web;
use H2O;
class Application extends H2O\base\Application
{
	/**
	 * @var 配置信息参数
	 */
	private $_config;
	/**
	 * Web应用初始化
	 * @param array $config 初始化参数
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);
		$this->_config = $config;
	}
	/**
	 * 执行方法
	 */
	public function handleRequest()
	{
		$request = new Request(isset($this->_config['request'])?$this->_config['request']:[]); //初始请求
		$dd = $request->getRoute();
		$this->runAction($dd);
	}
}
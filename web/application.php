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
	 * Web应用初始化
	 * @param array $config 初始化参数
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);
	}
	/**
	 * 设置预加载对象 缓存全局的类和对象 例如：module,view等
	 * 方便更多应用扩展现在类和对象
	 */
	public function setPreObject()
	{
		return [
		'module'		=>		'\H2O\base\module', //默认的模块类
		'view'				=>		'\H2O\web\view', //渲染层类
		];
	}
	/**
	 * 执行方法
	 */
	public function handleRequest()
	{
		$request = new Request(isset($this->_config['request'])?$this->_config['request']:[]); //初始请求
		$dd = $request->getRoute();
		\H2O::getContainer('module')->runAction($dd);
	}
}
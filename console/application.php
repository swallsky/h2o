<?php
/**
 * console应用的基类
 * @category   H2O
 * @package    console
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\console;
use H2O;
class Application extends H2O\base\Application
{
	/**
	 * 应用初始化
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
			'logger'			=>		'\H2O\console\logger', // 日志记录
			'request'		=>		'\H2O\console\Request', //控制台请求组件
			'module'		=>		'\H2O\base\module', //默认的模块类
			'view'				=>		'\H2O\base\view' //渲染层类
		];
	}
	/**
	 * 执行方法
	 */
	public function handleRequest()
	{
		$request = \H2O::getContainer('request'); //获取HTTP请求组件
		return $request->getRoute();
	}
}
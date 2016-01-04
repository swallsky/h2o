<?php
/**
 * 所有应用的基类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
abstract class Application
{
	/**
	 * @var string 应用程序的根空间
	 */
	const APP_ROOT_NAME = '\app';
	/**
	 * @event ActionEvent 前置事件
	 */
	const EVENT_BEFORE_ACTION = 'beforeAction';
	/**
	 * @event ActionEvent 后置事件
	 */
	const EVENT_AFTER_ACTION = 'afterAction';
	/**
	 * 初始化应用
	 * @param array $config
	 */
	public function __construct($config = [])
	{
		\H2O::setAppConfigs($config);
		$this->_preInit();
	}
	/**
	 * 设置预加载对象 缓存全局的类和对象 例如：module,view等
	 * 方便更多应用扩展现在类和对象
	 */
	public function setPreObject()
	{
		return [
			'module'		=>		'\H2O\base\module', //默认的模块类
			'view'				=>		'\H2O\base\view', //默认的渲染层类
		];
	}
	/**
	 * 预加载组件
	 */
	private function _preInit()
	{
		$pre = $this->setPreObject();
		foreach($pre as $n=>$o){
			\H2O::setContainer($n,new $o());
		}
	}
	/**
	 * 运行实例
	 */
	public function run()
	{
		Event::trigger(self::EVENT_BEFORE_ACTION);
		$this->handleRequest();
		Event::trigger(self::EVENT_AFTER_ACTION);
		echo \H2O::getContainer('module')->runModules();
	}
	/**
	 * 继承类必须实现的方法
	 * @param Request $request
	 */
	abstract public function handleRequest();
}
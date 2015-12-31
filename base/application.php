<?php
/**
 * 所有应用的基类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;

abstract class Application extends Module
{
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
		parent::__construct($config);
		$this->_preInit($config);
	}
	/**
	 * 预加载组件
	 * @param array $config
	 */
	private function _preInit($config)
	{
		//TODO
	}
	/**
	 * 运行实例
	 */
	public function run()
	{
		Event::trigger(self::EVENT_BEFORE_ACTION);
		$this->handleRequest();
		Event::trigger(self::EVENT_AFTER_ACTION);
	}
	/**
	 * 继承类必须实现的方法
	 * @param Request $request
	 */
	abstract public function handleRequest();
}
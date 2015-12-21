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
	public function __construct()
	{
		
	}
	/**
	 * 运行实例
	 */
	public function run()
	{
		$this->handleRequest('-----');
	}
	/**
	 * 继承类必须实现的方法
	 * @param Request $request
	 */
	abstract public function handleRequest($request);
}
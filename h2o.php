<?php
/**
 * 基础核心助手类
 * @category   H2O
 * @package    core
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
abstract class H2O
{
	/**
	 * @var array 全局缓存类容器
	 */
	public static $container = [];
	/**
	 * 配置初始化
	 * @param object $object 初始对象
	 * @param array $properties 初始化性属
	 * @return object 初始化后的对象
	 */
	public static function configure($object, $properties)
	{
		foreach ($properties as $name => $value) {
			$object->$name = $value;
		}
	
		return $object;
	}
	/**
	 * 框架初始化
	 */
	public static function init()
	{
		(new H2O\base\ErrorHandler())->register(); //注册自定义错误和异常信息
	}
}
H2O::init();
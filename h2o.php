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
	 * @var array 路径别名
	 */
	public static $aliases = ['@h2o' => __DIR__];
	/**
	 * 获取路径别名，如果不包含@，直接返回，如果存在@返回别名真实路径
	 * @param string $alias
	 * @param bool $throwException 是否抛弃异常
	 * @return 返回路径，如果不存在，则返回false
	 */
	public static function getAlias($alias, $throwException = true)
	{
		if (strncmp($alias, '@', 1)) {
			//不存在别名
			return $alias;
		}
	
		$pos = strpos($alias, '/');
		$root = $pos === false ? $alias : substr($alias, 0, $pos);
	
		if (isset(static::$aliases[$root])) {
			if (is_string(static::$aliases[$root])) {
				return $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
			} else {
				foreach (static::$aliases[$root] as $name => $path) {
					if (strpos($alias . '/', $name . '/') === 0) {
						return $path . substr($alias, strlen($name));
					}
				}
			}
		}
	
		if ($throwException) {
			throw new H2O\base\Exception("Alias","Invalid path alias: $alias");
		} else {
			return false;
		}
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
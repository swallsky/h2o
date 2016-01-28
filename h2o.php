<?php
/**
 * 基础核心助手类
 * @category   H2O
 * @package    core
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
/**
 * 程序执行开始时间
 */
defined('H2O_BEGIN_TIME') or define('H2O_BEGIN_TIME', microtime(true));
/**
 * 路径分隔符
 */
define('DS',DIRECTORY_SEPARATOR);
/**
 * 框架根目录
 */
defined('H2O_PATH') or define('H2O_PATH', __DIR__);
/**
 * 系统的根目录
 */
defined('APP_PATH') or define('APP_PATH', dirname(dirname(dirname(__DIR__))));
/**
 * 运行时缓存目录
 */
defined('APP_RUNTIME') or define('APP_RUNTIME', APP_PATH.DS.'runtime');
/**
 * 设置内部编码
 */
mb_internal_encoding('UTF-8');
/**
 * 设置默认时域 默认中国标准时间
 */
if(function_exists('date_default_timezone_set')) date_default_timezone_set('PRC');

abstract class H2O
{
	/**
	 * @var array 路径别名
	 */
	public static $aliases = ['@h2o' => __DIR__];
	/**
	 * 运行环境 prod:生产环境 dev:开发环境 test:测试环境 默认为prod
	 * @var string 
	 */
	private static $_runenv = 'prod';
	/**
	 * 设置运行环境
	 */
	private static function setRunEnv()
	{
		$env = self::getAppConfigs('runenv');
		$envs = ['prod','dev','test'];
		if(!empty($env) && in_array($env,$envs)!==false){
			self::$_runenv = $env;
		}
	}
	/**
	 * 返回当前应用运行环境
	 */
	public static function getRunEnv()
	{
		return self::$_runenv;
	}
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
			throw new \Exception("Invalid path alias: $alias");
		} else {
			return false;
		}
	}
	/**
	 * @var array 缓存应用配置信息
	 */
	private static $_appconfigs = [];
	/**
	 * 应用初始化时的配置信息缓存
	 * @param array $configs 应用配置信息
	 */
	public static function setAppConfigs($configs)
	{
		self::$_appconfigs = $configs;
	}
	/**
	 * 返回应用配置信息
	 * @param string $name 配置选项名称 为空时，返回所有配置信息
	 */
	public static function getAppConfigs($name = '')
	{
		if(empty($name)){
			return self::$_appconfigs;
		}else{
			return isset(self::$_appconfigs[$name])?self::$_appconfigs[$name]:[];
		}
	}
	/**
	 * @var array 全局容器类，缓存全局类
	 */
	private static $_container = [];
	/**
	 * 设置容器缓存
	 * @param string $name 缓存名称
	 * @param mixed $value 缓存信息
	 */
	public static function setContainer($name,$value)
	{
		self::$_container[$name] = $value;
	}
	/**
	 * 返回缓存信息
	 * @param $name
	 * @return mixed
	 */
	public static function getContainer($name)
	{
		return self::$_container[$name];
	}
	/**
	 * 初始化类
	 * @param string $class 类名
	 * @param mixed $params
	 * @throws \Exception
	 */
	public static function createObject($class,$params = '')
	{
		if(is_string($class)){
			return new $class($params);
		}else{
			throw new \Exception("Invalid path alias: $class");
		}
	}
	
	/**
	 * 框架初始化
	 */
	public static function init()
	{
		(new H2O\base\ErrorHandler())->register(); //注册自定义错误和异常信息
		self::setRunEnv(); //设置APP运行环境
	}
}
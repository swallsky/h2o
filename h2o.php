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
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
/**
 * 框架根目录
 */
defined('H2O_PATH') or define('H2O_PATH', __DIR__);
/**
 * composer安装目录
 */
defined('VENDOR_PATH') or define('VENDOR_PATH',dirname(dirname(H2O_PATH)));
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
if(function_exists('mb_internal_encoding')) mb_internal_encoding('UTF-8');
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
	 * @var string 应用程序的根空间
	 */
	const APP_ROOT_NAME = '\app';
	/**
	 * @var array 自动加载器
	 */
	public static $autoloader = null;
	/**
	 * @return string 返回当前版本号
	 */
	public static function getVersion()
	{
		return '0.6.53';
	}
	/**
	 * 获取自动加载器命名空间的前缀
	 * @param string $pre 命名空间前缀
	 */
	public static function getPreNameSpace($pre = '')
	{
		$data = self::$autoloader->getPrefixesPsr4();
		if(empty($pre)){
			return $data;
		}else{
			$pre = trim($pre,'\\').'\\';//转换为composer格式
			foreach($data as $k=>$v){
				if($k==$pre){//返回对应命名空间的路径
					$v = is_array($v)?$v[0]:$v;
					return realpath(str_replace('/',DS,$v));
				}
			}
			return '';
		}
	}
	/**
	 * 查找类对应的文件路径,如果未找到对应的路径,则返回false
	 * @param string $class 类名
	 * @return false|string
	 */
	public static function getClassPath($class)
	{
		return self::$autoloader->findFile($class);
	}
	/**
	 * 返回应用根空间的对应的目录
	 */
	public static function getAppRootPath()
	{
		return \H2O::getPreNameSpace(H2O::APP_ROOT_NAME);
	}
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
		if(strncmp($alias, '@', 1)) {//不存在别名
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
			throw new \Exception("Class create failure: $class");
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
/**
 * 初始化自动加载器
 */
H2O::$autoloader = require(VENDOR_PATH.DS.'autoload.php');


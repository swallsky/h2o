<?php
/**
 * 所有模块的基类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
use H2O;
class Module
{
	/**
	 * @var string 返回当前模块的目录
	 */
	private $_basePath;
	/**
	 * @var string 视图文件目录
	 */
	private $_viewPath;
	/**
	 * @var string 控制器命名空间
	 */
	private $_ctrnSpace;
	/**
	 * @var array 布局模块
	 */
	public static $layout = [];
	/**
	 * @var array 模块组 默认存在layout模块,但是为空
	 */
	public static $modules = [
		'layout'		=>	'',
		'content'		=>	''
	];
	/**
	 * 初始化
	 */
	public function __construct()
	{
		$config = \H2O::getAppConfigs(); //获取应用配置信息
		if(isset($config['basePath']))
			$this->setBasePath($config['basePath']);
		$this->init();
	}
	/**
	 * 初始化模块
	 */
	private function init()
	{
		if ($this->_ctrnSpace === null) {
			$trn = str_replace(APP_PATH,'',$this->_basePath);
			$this->_ctrnSpace = Application::APP_ROOT_NAME.str_replace('/','\\',$trn).'\\controllers';
		}
	}
	/**
	 * 返回当前模块的命名空间
	 */
	public function getNameSpace()
	{
		return str_replace('\\controllers','',$this->_ctrnSpace);
	}
	/**
	 * 将URL转换为标准的路由数组
	 * @param $routepath 路由URL 例如：main.index
	 * @return array 路由
	 * @throws 如果路由设置错误，抛弃异常
	 */
	public static function parseRoute($routepath)
	{
		$pointcnt = substr_count($routepath,'.');
		if($pointcnt==1){
			$ep = explode('.',$routepath);
			return [
				'controller'	=>	$ep[0],
				'action'		=>	$ep[1]
			];
		}else{
			throw new Exception('H2O\base\Module','routeUrl:'.$routepath.' is error.');
		}
	}
	/**
	 * @return string 返回模块的根目录
	 */
	public function getBasePath()
	{
		if($this->_basePath === null){
			$class = new \ReflectionClass($this);
			$this->_basePath = dirname($class->getFileName());
		}
		return $this->_basePath;
	}
	/**
	 * @param string $path 设置模块根目录
	 * @throws Exception 如果不存在，则出现异常
	 */
	public function setBasePath($path)
	{
		$path = H2O::getAlias($path);
		$p = realpath($path);
		if($p !== false && is_dir($p)){
			$this->_basePath = $p;
		}else{
			throw new Exception('Module::setBasePath',$path.' is error!');
		}
	}
	/**
	 * 返回这个模块的模板目录
	 * @return string 模板目录
	 */
	public function getViewPath()
	{
		if ($this->_viewPath !== null) {
			return $this->_viewPath;
		} else {
			return $this->_viewPath = $this->_basePath . DIRECTORY_SEPARATOR . 'views';
		}
	}
	/**
	 * 设置这个模块的模板目录
	 * @param string 模板目录
	 * @throws 如果不存在，则抛弃异常
	 */
	public function setViewPath($path)
	{
		$this->_viewPath = \H2O::getAlias($path);
	}
	/**
	 * 返回控制器的命名空间
	 */
	public function getCtrNameSpace()
	{
		return $this->_ctrnSpace;
	}
	/**
	 * 返回包含模块
	 * @param $url
	 * @return string
	 * @throws Exception
	 */
	public function loadModule($url)
	{
		$route = self::parseRoute($url);
		return $this->runSingleModules($route);
	}
	/**
	 * 执行单个模块
	 * @param array $route 路由
	 * @return string 解析后的模块
	 */
	private function runSingleModules($route)
	{
		$stro = $this->_ctrnSpace.'\\'.strtolower($route['controller']);
		$o = new $stro();
		$o->setViewPath($this->getViewPath());
		return $o->runAction(ucfirst(strtolower($route['action'])));
	}
	/**
	 * 获取布局信息
	 * @return array
	 */
	public static function getLayout()
	{
		return self::$layout;
	}
	/**
	 * 设置布局信息
	 * @param string $url 例如 layout.index
	 */
	public static function setLayout($url)
	{
		$route = self::parseRoute($url);
		self::$layout = $route;
	}
	/**
	 * 清空布局
	 */
	public static function clearLayout()
	{
		self::$layout = [];
	}
	/**
	 * 执行动作
	 * @param array $route 路由
	 * @param bool $content 是否是主内容
	 */
	public function runAction($route,$content = true)
	{
		$context = $this->runSingleModules($route);
		if($content) self::$modules['content'] = $context;
		return $context;
	}
	/**
	 * 返回主操作区信息
	 * @return string
	 */
	public static function getContent()
	{
		return self::$modules['content'];
	}
	/**
	 * 执行模块
	 */
	public function runModules()
	{
		if(empty(self::$layout)){//不存在布局，直接返回内容
			return self::$modules['content'];
		}else{//存在布局模块时返回布局
			return $this->runAction(self::$layout,false);
		}
	}
}
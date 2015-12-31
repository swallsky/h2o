<?php
/**
 * 所有模块的基类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
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
	 * @var string 缓存的配置信息
	 */
	public static $config = [];
	/**
	 * @var array 布局模块
	 */
	public static $layout = [];
	/**
	 * @var array 模块组 默认存在layout模块,但是为空
	 */
	public static $modules = [
		'layout'		=>	'',
		'content'		=>	'',
		'sonModules'	=>	[]
	];
	/**
	 * 初始化
	 * @param array $config
	 */
	public function __construct($config = [])
	{
		$conf = empty($config)?self::$config:$config;
		if(isset($conf['basePath']))
			$this->setBasePath($conf['basePath']);
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
		$path = \H2O::getAlias($path);
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
	 * 设置子模块
	 * @param string $name 子模块名
	 * @param array $route
	 */
	public static function setSonModules($name,$route)
	{
		self::$modules['sonModules'][$name] = $route;
	}
	/**
	 * 返回子模块的路由信息
	 * @param string $name 子模块名称
	 * @return array 所有子路由信息
	 */
	public static function getSonModules($name = '')
	{
		if(empty($name)){
			return self::$modules['sonModules'];
		}else{
			return isset(self::$modules['sonModules'][$name])?self::$modules['sonModules'][$name]:'';
		}
	}
	/**
	 * 执行所有的子模块
	 */
	public function runSonModules()
	{
		$sons = self::$modules['sonModules'];
		if(!empty($sons)){
			foreach($sons as $n=>$m){
				self::$modules['sonModules'][$n] = $this->runSingleModules($m);
			}
		}
	}
	/**
	 * 执行单个模块
	 * @param array $route 路由
	 * @return string 解析后的模块
	 */
	public function runSingleModules($route)
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
		return Module::$layout;
	}
	/**
	 * 设置布局信息
	 * @param array $route
	 * 例如：[
		'controller'	=>	'layout',
		'action'		=>	'index'
		]
	 */
	public static function setLayout($route)
	{
		Module::$layout = $route;
	}
	/**
	 * 显示主内容信息
	 * @return string 主内容
	 */
	public static function getContent()
	{
		return self::$modules['content'];
	}
	/**
	 * 执行动作
	 * @param array $route 路由
	 * @param bool $content 是否是主内容
	 */
	public function runAction($route,$content = true)
	{
		$this->runSonModules();//执行子模块
		$context = $this->runSingleModules($route);
		if($content) self::$modules['content'] = $context;
		return $context;
	}
	/**
	 * 执行模块
	 */
	public function runModules()
	{
		if(empty(self::$layout)){//不存在布局，直接返回内容
			return self::$modules['content'];
		}else{//存在布局模块时返回布局
			$layout = self::$layout;
			return $this->runAction($layout,false);
		}
	}
}
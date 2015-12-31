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
	 * @var string 布局文件目录
	 */
	private $_layoutPath;
	/**
	 * @var string 控制器命名空间
	 */
	private $_ctrnSpace;
	/**
	 * 初始化
	 * @param array $config
	 */
	public function __construct($config = [])
	{
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
			$this->_ctrnSpace = '\app'.str_replace('/','\\',$trn).'\\controllers';
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
	 * 执行控制器
	 * @param array $route 路由规则
	 */
	public function runController($route)
	{
		$stro = $this->_ctrnSpace.'\\'.$route['controller'];
		$o = new $stro();
		$o->setViewPath($this->getViewPath());
		$action = 'act'.ucfirst($route['action']);
		if(method_exists($o,$action)){
			return call_user_func([$o,$action]);
		}else{
			throw new Exception('Module::runController',$stro.' no method:'.$action);
		}
	}
}
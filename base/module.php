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
			$this->_ctrnSpace = str_replace('/','\\',$trn).'\\controllers';
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
		return $o->runAction($route['action']);
	}
}
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
	public $controllerNamespace;
	/**
	 * 初始化
	 * @param array $config
	 */
	public function __construct($config = [])
	{
		if(isset($config['basePath'])) $this->setBasePath($config['basePath']);
		$this->init();
	}
	/**
	 * 初始化模块
	 */
	private function init()
	{
		if ($this->controllerNamespace === null) {
			$class = get_class($this);
			if (($pos = strrpos($class, '\\')) !== false) {
				$this->controllerNamespace = substr($class, 0, $pos) . '\\controllers';
			}
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
}
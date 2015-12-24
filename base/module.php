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
	 * @var string 控制器命名空间
	 */
	public $controllerNamespace;
	/**
	 * 初始化
	 * @param array $config
	 */
	public function __construct($config = [])
	{
		\H2O::configure($this, $config);
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
}
<?php
/**
 * 所有控制器的基类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
use H2O;
abstract class Controller
{
	/**
	 * @var string 视图目录
	 */
	private $_viewPath;
	/**
	 * 返回视图目录
	 */
	public function getViewPath()
	{
		return $this->_viewPath;
	}
	/**
	 * @param string $path 设置视图目录
	 */
	public function setViewPath($path)
	{
		$path = \H2O::getAlias($path);
		$this->_viewPath = $path;
	}
	/**
	 * 返回模板渲染后的字符串
	 * @param string $tpl 模板文件
	 * @param array $vars 需要传入模板的数据参数
	 */
	public function render($tpl,$vars = [])
	{
		$ov = new View($tpl);
		$ov->setPath($this->getViewPath());
		return $ov->render($vars);
	}
	/**
	 * 查找运行对应的Action方法
	 * @param string $action
	 */
	public function runAction($action)
	{
		$action = 'act'.ucfirst($action);
		$o = new static(); //初始化对应的类
		if(method_exists($o,$action)){
			return call_user_func([$o,$action]); //执行对应的方法
		}else{
			throw new Exception('Controller::runAction',get_class($o).'->'.$action.' is not exist!');
		}
	}
}
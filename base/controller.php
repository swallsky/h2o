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
	 * @var string 控制器名称
	 */
	private $_name = '';
	/**
	 * @var string 视图目录
	 */
	private $_viewPath;
	/**
	 * 初始化控制器
	 */
	public function __construct()
	{
		$class = get_called_class();
		$this->_name = substr($class, strrpos($class,'\\'));
	}
	/**
	 * 执行对应的操作
	 * @param $act 操作名称
	 */
	public function runAction($act)
	{
		$action = 'act'.$act;
		if(method_exists($this,$action)){
			return call_user_func([$this,$action]);
		}else{
			throw new Exception('Module::runController',get_called_class().' no method:'.$action);
		}
	}
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
	 * 获取当前布局信息
	 * @return array 布局信息
	 */
	public function getLayout()
	{
		return Module::getLayout();
	}
	/**
	 * 设置布局信息
	 * @param array $route
	 * 例如：[
			'controller'	=>	'layout',
			'action'			=>	'index'
		]
	 */
	public function setLayout($route)
	{
		Module::setLayout($route);
	}
	/**
	 * 显示子模块信息
	 * @param string $name 子模块名称
	 * @return array
	 */
	public function getSonModules($name = '')
	{
		return Module::getSonModules($name);
	}
	/**
	 * 设置子模块
	 * @param string $name 子模块名称
	 * @param array $route 路由
	 */
	public function setSonModules($name,$route)
	{
		Module::setSonModules($name,$route);
	}
	/**
	 * 返回模板渲染后的字符串
	 * @param string $tpl 模板文件
	 * @param array $vars 需要传入模板的数据参数
	 */
	public function render($tpl,$vars = [])
	{
		$ov = new View($tpl);
		$viewpath = $this->getViewPath().DIRECTORY_SEPARATOR.$this->_name;
		$ov->setPath($viewpath);
		return $ov->render($vars);
	}
}
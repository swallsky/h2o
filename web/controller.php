<?php
/**
 * 所有控制器的基类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\web;
use H2O;
abstract class Controller extends H2O\base\Controller
{
	/**
	 * 初始化
	 */
	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * 获取当前布局信息
	 * @return array 布局信息
	 */
	public function getLayout()
	{
		return parent::getLayout();
	}
	/**
	 * 设置布局信息
	 * @param string $url 路由URL 例如layout.index
	 */
	public function setLayout($url)
	{
		parent::setLayout($url);
	}
	/**
	 * 清空布局
	 */
	public function clearLayout()
	{
		parent::clearLayout();
	}
	/**
	 * 返回包含模板
	 * @param string $url 例如 message.list
	 * @param string $namespace 命名空间
	 */
	public function loadModule($url,$namespace = '')
	{
		return parent::loadModule($url,$namespace);
	}
	/**
	 * 返回模板渲染后的字符串
	 * @param string $tpl 模板文件
	 * @param array $vars 需要传入模板的数据参数
	 */
	public function render($tpl,$vars = [])
	{
		return parent::render($tpl,$vars);
	}
}
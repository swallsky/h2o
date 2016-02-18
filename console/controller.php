<?php
/**
 * console的控制器
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\console;
use H2O,H2O\console\Request;
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
	 * 返回请求信息
	 * @return 返回请求句柄
	 */
	public function request()
	{
		return \H2O::getContainer('request');
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
	/**
	 * 命令下使用方法帮助信息
	 */
	abstract public function actHelp();
}
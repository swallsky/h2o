<?php
/**
 * Web应用的基类
 * @category   H2O
 * @package    web
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\web;
use H2O;
class Application extends H2O\base\Application
{
	/**
	 * @var string 默认路由
	 */
	public $defaultRoute = 'site.index';
	/**
	 * 执行方法
	 * @param Request $request
	 */
	public function handleRequest($request)
	{
		echo $request;
	}
}
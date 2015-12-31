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
class Controller extends H2O\base\Controller
{
    /**
     * 初始化控制器
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * @param string $url 设置标准的布局 例如: layout.index 布局路由
     */
    public function setLayout($url)
    {
        $route = Request::parseRoute($url);
        parent::setLayout($url);
    }
    /**
     * 设置子模块
     * @param string $name 子模块名称
     * @param string $url 路由URL 例如：main.pub
     */
    public function setSonModules($name,$url)
    {
        $route = Request::parseRoute($url);
        parent::setSonModules($name,$route);
    }
}
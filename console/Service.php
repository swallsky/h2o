<?php
/**
 * 服务程序
 * @category   H2O
 * @package    console
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\console;
class Service
{
    /**
     * @var string 日志路径
     */
    private $_logpath = '';
	/**
	 * 初始化
	 */
	public function __construct()
	{
	    $this->_logpath = APP_RUNTIME.DS.'console'.DS.'service'.DS; //日志目录
	}
	/**
	 * 返回对应的参数
	 * @param string $tag 参数key值 如果为空，则返回所有参数值
	 */
	private function _getParams($tag = '')
	{
	    $request = \H2O::getContainer('request'); //控制台请求
	    $params = $request->getParams();
	    return empty($tag)?$params:$params[$tag];
	}
	/**
	 * 启动
	 */
	public function actStart()
	{
	    $paras = $this->_getParams();
	    if(empty($paras['c'])){//该参数必须需要
	        echo 'The parameter `c` is a must!';
	        exit();
	    }
	    $routep = $paras['c']; //路由规则path
	    while(true){
	        $module = \H2O::getContainer('module');
	        $route = \H2O\base\Module::parseRoute($routep); //返回路由规则URL
	        $res = $module->runAction($route);
	        echo $res.PHP_EOL;
	        sleep(1); //休眠时间 1秒
	    }
	}
	/**
	 * 停止
	 */
	public function actStop()
	{
	    //TODO
	}
}
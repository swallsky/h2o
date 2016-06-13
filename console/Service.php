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
     * @var int 每次休眠时间 单位 秒
     */
    private $_sleepTime = 1;
	/**
	 * 初始化
	 */
	public function __construct()
	{
		//TODO
	}
	/**
	 * 返回对应的参数
	 * @param string $tag 参数key值
	 */
	private function _getParams($tag)
	{
	    $request = \H2O::getContainer('request'); //控制台请求
	    $params = $request->getParams();
	    if(empty($params[$tag])){
	        echo 'Missing required parameter: '.$tag;
	        exit();
	    }
	    return $params[$tag];
	}
	/**
	 * 启动
	 */
	public function actStart()
	{
	    $mf = $this->_getParams('n');
	    while(true){
	        $module = \H2O::getContainer('module');
	        $route = \H2O\base\Module::parseRoute($mf); //返回路由规则URL
	        $res = $module->runAction($route);
	        echo $res.PHP_EOL;
	        sleep($this->_sleepTime); //休眠时间
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
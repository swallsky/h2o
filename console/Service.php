<?php
/**
 * 服务程序
 * @category   H2O
 * @package    console
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\console;
use H2O;
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
	    $logfile = $this->_logpath.$routep.'.log'; //记录日志信息
	    $module = \H2O::getContainer('module');
	    $route = \H2O\base\Module::parseRoute($routep); //返回路由规则URL
	    while(true){
	        $res = $module->runAction($route);
	        $content = date('Y-m-d H:i:s').'　' . $res . PHP_EOL;
	        H2O\helpers\File::write($logfile,$content);//写入日志信息
	        sleep(1); //休眠时间 1秒
	    }
	}
	/**
	 * 查看单个服务运行情况
	 */
	public function actCat()
	{
	    $paras = $this->_getParams();
	    if(empty($paras['c'])){//该参数必须需要
	        echo 'The parameter `c` is a must!';
	        exit();
	    }
	    $routep = $paras['c']; //路由规则path
	    $logfile = $this->_logpath.$routep.'.log'; //记录日志信息
	    if(file_exists($logfile)){
	        $data = file($logfile);
	        for($i=0;$i<10;$i++){
	            echo $data[$i].PHP_EOL;
	        }
	        exit();
	    }else{
	        echo 'Not found related services';
	        exit();
	    }
	}
}
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
	 * 返回服务路由
	 * @param bool $require 是否必须需要路由 默认是true
	 */
	private function _getRoutePath($require = true)
	{
	    $paras = $this->_getParams();
	    if($require){
    	    if(empty($paras['c'])){//该参数必须需要
    	        echo 'The parameter `c` is a must!';
    	        exit();
    	    }
    	    return $paras['c']; //路由规则path
	    }else{
	        return isset($paras['c'])?$paras['c']:'';
	    }
	}
	/**
	 * 设置信号量
	 * @param string $data 设置信号量
	 * @param string $routep 应用路由
	 */
	private function _setSignal($data,$routep = '')
	{
	    $routep = empty($routep)?$this->_getRoutePath():$routep; //路由规则path
	    $logfile = $this->_logpath.$routep.'.signal'; //当前的信号信息
	    H2O\helpers\File::write($logfile,$data);//写入日志信息
	}
	/**
	 * 读取信息号信息
	 * @param string $routep 应用路由
	 */
	private function _getSignal($routep)
	{
	    $logfile = $this->_logpath.$routep.'.signal'; //当前的信号信息
	    if(file_exists($logfile)){
    	    $res = H2O\helpers\File::read($logfile); //读取信号量信息
    	    H2O\helpers\File::remove($logfile); //信号量只作临时缓存作用，所以一旦读取到，就直接删除不作缓存
    	    return $res;
	    }else{
	        return '';
	    }
	}
	/**
	 * 返回所有的服务
	 */
	private function _getAllService()
	{
	    if (!($handle = opendir($this->_logpath))) {
	        return;
	    }
	    $sers = [];
	    while (($file = readdir($handle)) !== false) {
	        if ($file === '.' || $file === '..') {
	            continue;
	        }
	        $ext = substr($file,-4);
	        if($ext == '.log'){
	            $sers[] = substr($file,0,-4);
	        }
	    }
	    closedir($handle);
	    return $sers;
	}
	/**
	 * 启动
	 */
	public function actStart()
	{
	    $routep = $this->_getRoutePath(); //路由规则path
	    $logfile = $this->_logpath.$routep.'.log'; //记录日志信息
	    $module = \H2O::getContainer('module');
	    $route = \H2O\base\Module::parseRoute($routep); //返回路由规则URL
	    //避免重复启动服务
	    //TODO
	    while(true){
	        $signal = $this->_getSignal($routep); //获取信号
	        if($signal == 'stop'){
	            $content = date('Y-m-d H:i:s').'　STOP SIGNAL' . PHP_EOL;
	            H2O\helpers\File::write($logfile,$content);//写入日志信息
	            exit();
	        }else{
    	        $res = $module->runAction($route);
    	        $content = date('Y-m-d H:i:s').'　' . $res . PHP_EOL;
    	        H2O\helpers\File::write($logfile,$content);//写入日志信息
	        }
	        sleep(1); //休眠时间 1秒
	    }
	}
	/**
	 * 显示单个服务程序信息
	 * @param string $routep 路由信息
	 * @param int $lines 需要最新的几行信息
	 */
	private function _catOne($routep,$lines = 10)
	{
	    $logfile = $this->_logpath.$routep.'.log'; //记录日志信息
	    if(file_exists($logfile)){
	        $data = file($logfile);
	        $cnt = count($data);
	        echo $routep.':'.PHP_EOL;
	        for($i=$cnt-1;$i>$cnt-11;$i--){ //倒序排序
	            echo "\t".$data[$i].PHP_EOL;
	        }
	    }else{
	        echo $routep.': Not found related services'.PHP_EOL;
	    }
	}
	/**
	 * 查看服务程序运行情况
	 */
	public function actCat()
	{
	    $routep = $this->_getRoutePath(false);
	    if(empty($routep)){
	        $sers = $this->_getAllService();
	        foreach($sers as $s){
	            $this->_catOne($s,2);
	        }
	    }else{
    	    $this->_catOne($routep,10);
	    }
	}
	/**
	 * 停止服务程序
	 */
	public function actStop()
	{
	    $routep = $this->_getRoutePath(false);
	    if(empty($routep)){
	        $sers = $this->_getAllService();
	        foreach($sers as $s){
	            $this->_setSignal('stop',$s);
	        }
	    }else{
	        $this->_setSignal('stop',$routep);
	    }
	}
}
<?php
/**
 * 服务程序
 * @category   H2O
 * @package    console
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\console;
use H2O,H2O\helpers\File;
class Service
{
	/**
	 * @var string 日志路径
	 */
	private $_logpath = '';
	/**
	 * @var string 停止信号
	 */
	private $_stopsignal = 'STOP';
	/**
	 * 初始化
	 */
	public function __construct()
	{
		$this->_logpath = APP_RUNTIME.DS.'console'.DS.'service'.DS; //日志目录
	}
	/**
	 * 返回对应的参数
	 */
	private function _getParams()
	{
		$request = \H2O::getContainer('request'); //控制台请求
		return $request->getParams();
	}
	/**
	 * 返回对应的参数
	 * @param string $tag 参数key值
	 */
	private function _getParamsByKey($tag)
	{
		$params = $this->_getParams();
		return isset($params[$tag])?$params[$tag]:'';
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
	 * @param int $pid 进程编号
	 */
	private function _setSignal($data,$routep, $pid = '')
	{
		$slog = $this->_logpath.$routep.'.signal'; //当前的信号信息
		$new = empty($pid)?$data:$data.PHP_EOL.$pid;
		if(file_exists($slog)){
			$temp = File::read($slog);
			$data = $new.PHP_EOL.date('Y-m-d H:i:s').PHP_EOL.$temp; //将信号源写入到头部
		}else{
			$data = $new.PHP_EOL.date('Y-m-d H:i:s').PHP_EOL;
		}
		File::write($slog,$data,false);//写入信号量信息
	}
	/**
	 * 读取信息号信息
	 * @param string $routep 应用路由
	 */
	private function _getSignal($routep)
	{
		$logfile = $this->_logpath.$routep.'.signal'; //当前的信号信息
		if(file_exists($logfile)){
			$res = file($logfile); //读取信号量信息
			foreach($res as $k=>$s){
				$res[$k] = trim($s); //过滤空格
			}
			return $res;
		}else{
			return '';
		}
	}
	/**
	 * 删除信号源
	 * @param string $routep 应用路由
	 */
	private function _deleteSignal($routep)
	{
		$slog = $this->_logpath.$routep.'.signal'; //当前的信号信息
		if(file_exists($slog)){
			File::remove($slog);
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
			if($ext == '.pid'){
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
		$logfile = $this->_logpath . $routep . DS . date('Ymd') . '.log'; //记录日志信息 按天记录
		$module = \H2O::getContainer('module');
		$route = \H2O\base\Module::parseRoute($routep); //返回路由规则URL
		//启动时，要删除已产生的停止信号，防止启动时就退出
		$this->_deleteSignal($routep);
		//写入进程信息
		$pid = getmypid();
		$pidfile = $this->_logpath . $routep . '.pid'; //进程存储文件
		File::write($pidfile,$pid . ':' . date('Y-m-d H:i:s') . PHP_EOL); //写入存储信息
		//循环业务处理
		while(true){
			$signal = $this->_getSignal($routep); //获取信号
			if($signal[0] == $this->_stopsignal){//信号源为停止，并且是当前应用
				//清理进程信息
				if($signal[1]==$pid){
					$apid = file($pidfile); //读取所有进程信息
					$apid = array_filter($apid); //过滤空格
					$tmpid = [];
					foreach($apid as $ap){
						$ap = trim($ap);
						$opid = substr($ap,0,strpos($ap,':'));
						if($opid != $pid && !empty($ap)){
							$tmpid[] = $ap.PHP_EOL;
						}
					}
					if(empty($tmpid)){//如果不存进程信息时，直接删除记录进程日志信息
						File::remove($pidfile); //删除运行时的日志
					}else{
						File::write($pidfile,implode('',$tmpid),false);//新写入进程日志
					}
				}else{
					File::remove($pidfile); //删除运行时的日志
				}
				exit();
			}else{
				$octr = $module->getController($route['controller']); //控制器对象
				$gwmethod = 'Gate'.ucfirst($route['action']); //方法网关
				if(method_exists($octr,$gwmethod)){//增加入口应用关口，可在此函数中处理业务逻辑，可实现定时任务等
					$gwm = $octr->$gwmethod();
					if($gwm===true){//返回值只有为true时才执行相应的程序
						$res = $octr->runAction(ucfirst($route['action'])); //执行操作
						$content = 'pid:' . $pid . ' datetime:' . date('Y-m-d H:i:s') . ' response:' . $res . PHP_EOL;
						File::write($logfile,$content);//写入日志信息
					}
				}else{
					$res = $octr->runAction(ucfirst($route['action'])); //执行操作
					$content = 'pid:' . $pid . ' datetime:' . date('Y-m-d H:i:s') . ' response:' . $res . PHP_EOL;
					File::write($logfile,$content);//写入日志信息
				}
			}
			sleep(1); //休眠时间 1秒
		}
	}
	/**
	 * 显示单个服务程序信息
	 * @param string $routep 路由信息
	 */
	private function _catOne($routep)
	{
		$pidfile = $this->_logpath . $routep . '.pid'; //记录进程信息
		if(file_exists($pidfile)){
			$data = file($pidfile);
			echo $routep.':'.PHP_EOL;
			foreach($data as $d){
				echo "\t".$d.PHP_EOL;
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
		if(empty($routep)){//显示所有服务程序运行况态
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
		if(empty($routep)){//关闭所有服务程序
			$sers = $this->_getAllService();
			foreach($sers as $s){
				$this->_setSignal($this->_stopsignal,$s);
			}
		}else{
			$pid = $this->_getParamsByKey('p'); //获取进程编号
			$this->_setSignal($this->_stopsignal,$routep,$pid);
		}
	}
	/**
	 * 守护子进程
	 */
	public function actDaemon()
	{
		$routep = $this->_getRoutePath(); //路由规则path
		$module = \H2O::getContainer('module');
		$route = \H2O\base\Module::parseRoute($routep); //返回路由规则URL

		$octr = $module->getController($route['controller']); //控制器对象
		$gwmethod = 'Gate'.ucfirst($route['action']); //方法网关
		$pid = getmypid(); //进程ID
		if(method_exists($octr,$gwmethod)){//增加入口应用关口，可在此函数中处理业务逻辑，可实现定时任务等
			$gwm = $octr->$gwmethod();
			if($gwm===true){//返回值只有为true时才执行相应的程序
				$res = $octr->runAction(ucfirst($route['action'])); //执行操作
				$response = 'pid:' . $pid . ' datetime:' . date('Y-m-d H:i:s') . ' response:' . $res . PHP_EOL;
			}
		}else{
			$res = $octr->runAction(ucfirst($route['action'])); //执行操作
			$response = 'pid:' . $pid . ' datetime:' . date('Y-m-d H:i:s') . ' response:' . $res . PHP_EOL;
		}
		echo $response;
	}
}

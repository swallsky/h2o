<?php
/**
 * 所有模块的基类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
use H2O;
class Module
{
	/**
	 * @var string 返回当前模块的目录
	 */
	private $_basePath;
	/**
	 * @var string 控制器命名空间
	 */
	private $_ctrnSpace;
	/**
	 * 初始化
	 */
	public function __construct()
	{
		$config = \H2O::getAppConfigs(); //获取应用配置信息
		if(isset($config['basePath'])) //应用程序主目录
			$this->setBasePath($config['basePath']);
		$this->init();
	}
	/**
	 * 初始化模块
	 */
	private function init()
	{
		if ($this->_ctrnSpace === null) {
			$trn = str_replace(\H2O::getAppRootPath(),'',$this->_basePath);
			$trn = str_replace('/','\\',$trn);
			$trn = (strpos($trn,'\\')===false)?'\\'.$trn:$trn;
			$this->_ctrnSpace = \H2O::APP_ROOT_NAME.$trn.'\\controllers';
		}
	}
	/**
	 * @return string 当前模块的命名空间
	 */
	public function getNameSpace()
	{
		return str_replace('\\controllers','',$this->_ctrnSpace);
	}
	/**
	 * @return string 控制器的命名空间
	 */
	public function getCtrNameSpace()
	{
		return $this->_ctrnSpace;
	}
	/**
	 * 将URL转换为标准的路由数组
	 * @param $routepath 路由URL 例如：main.index
	 * @return array 路由
	 * @throws 如果路由设置错误，抛弃异常
	 */
	public static function parseRoute($routepath)
	{
		$pointcnt = substr_count($routepath,'.');
		if($pointcnt==1){
			$ep = explode('.',$routepath);
			return [
				'controller'	=>	ucfirst($ep[0]),
				'action'		=>	$ep[1]
			];
		}else{
			throw new \Exception('routeUrl:'.$routepath.' is error.');
		}
	}
	/**
	 * @return string 返回模块的根目录
	 */
	public function getBasePath()
	{
		if($this->_basePath === null){
			$class = new \ReflectionClass($this);
			$this->_basePath = dirname($class->getFileName());
		}
		return $this->_basePath;
	}
	/**
	 * @param string $path 设置模块根目录
	 * @throws Exception 如果不存在，则出现异常
	 */
	public function setBasePath($path)
	{
		$path = H2O::getAlias($path);
		$p = realpath($path);
		if($p !== false && is_dir($p)){
			$this->_basePath = $p;
		}else{
			throw new \Exception('Configs of basePath set error:'.$path);
		}
	}
	/**
	 * 返回当前模块的controller对象
	 * @param string $ctr 控制器对象名称
	 */
	public function getController($ctr)
	{
		return \H2O::createObject($this->_ctrnSpace.'\\'.$ctr);
	}
	/**
	 * 执行动作
	 * @param array $route 路由
	 */
	public function runAction($route)
	{
		$o = \H2O::createObject($this->_ctrnSpace.'\\'.$route['controller']);
		if(method_exists($o,'runAction')){//继承控制器类
			return $o->runAction(ucfirst($route['action']));
		}else{//其他非系统控制器类执行
			$action = 'act'.ucfirst($route['action']);//与基类控制器方法一致,只能以act开头的方法可以执行
			if(method_exists($o,$action)){
				return call_user_func([$o,$action]);
			}else{
				throw new \Exception(get_class($o).' no method:'.$action);
			}
		}
	}
}
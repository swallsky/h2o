<?php
/**
 * 命令行请求基类
 * h2o <route> [--option1=value1 --option2=value2 ... argument1 argument2 ......]
 * @category   H2O
 * @package    console
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\console;
use H2O\helpers\Stdout;
class Request
{
	/**
	 * @var array 命令行参数
	 */
	private $_params = [];
	/**
	 * 参数检验
	 * @param array $params
	 */
	private function _checkParams($params)
	{
		foreach ($params as $p) {
			if (strpos($p,'--') !== 0) {
				throw new \Exception($p.' param format is error,for example: "--app=config"');
			}else{
				$eparms = explode('=',substr($p,2));
				$this->_params[$eparms[0]] = isset($eparms[1])?$eparms[1]:'';
			}
		}
	}
	/**
	 * 显示命令输入帮助信息
	 */
	public function help()
	{
		Stdout::title('This is H2O version '.\H2O::getVersion());
		//命令行使用 
		Stdout::table([
			['route','COMMAND <route> [--option1=value1 --option2=value2 ... argument1 argument2 ...]'],
			['example for windows','command hello.index --test=info'],
			['example for linux','./command hello.index --test=info']
		]);
		//数据迁移模块
		Stdout::table([
			['migrate','Manages application migrations','Params list'],
			['@migrate.create','Create a new migrate','name'],
			['@migrate.up','Update a new migrate','name'],
			['@migrate.restore','Restore a new migrate','name']
		]);
		echo Stdout::get();
		exit();
	}
	/**
	 * 获取路由控制器和动作
	 */
	public function getRoute()
	{
		$params = isset($_SERVER['argv'])?$_SERVER['argv']:[];
		if(empty($params) || count($params)<1){
			throw new \Exception('console params is error!');
		}
		array_shift($params);
		if(empty($params)){//显示帮助信息
			$this->help();
		}
		$pointcnt = substr_count($params[0],'.');
		if($pointcnt!=1){
			throw new \Exception('console route is error! for example: main.index');
		}
		$data = \H2O\base\Module::parseRoute($params[0]); //返回路由规则URL
		array_shift($params);
		$this->_checkParams($params); //参数格式检查
		return $data;
	}
	/**
	 * @return array 返回命令行参数
	 */
	public function getParams()
	{
		return $this->_params;
	}
	/**
	 * 返回完整的访问路径
	 * @return string
	 */
	public function getRequestUri()
	{
		return implode(' ',$_SERVER['argv']);
	}
}
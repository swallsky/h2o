<?php
/**
 * 命令行请求基类
 * h2o <route> [--option1=value1 --option2=value2 ... argument1 argument2 ...]
 * @category   H2O
 * @package    console
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\console;
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
	 * 获取路由控制器和动作
	 */
	public function getRoute()
	{
		$params = isset($_SERVER['argv'])?$_SERVER['argv']:[];
		if(empty($params) || count($params)>0){
			throw new \Exception('console params is error!');
		}
		$pointcnt = substr_count($params[0],'.');
		if($pointcnt!=1){
			throw new \Exception('console route is error! for example: main.index');
		}
		array_shift($params);
		$this->_checkParams($params); //参数格式检查
		$data = \H2O\base\Module::parseRoute($params[0]); //返回路由规则URL
		return $data;
	}
	/**
	 * @return array 返回命令行参数
	 */
	public function getParams()
	{
		return $this->_params;
	}
}
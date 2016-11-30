<?php
/**
 * 所有应用的基类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
abstract class Application
{
	/**
	 * 初始化应用
	 * @param array $config
	 */
	public function __construct($config = [])
	{
		\H2O::setAppConfigs($config);
		\H2O::init();
		$this->_preInit();
	}
	/**
	 * 设置预加载对象 缓存全局的类和对象 例如：module,view等
	 * 方便更多应用扩展现在类和对象
	 */
	public function setPreObject()
	{
		return [
			'logger'		=>		'\H2O\base\Logger', // 日志记录器接口
			'module'		=>		'\H2O\base\Module', //默认的模块类
			'view'			=>		'\H2O\base\View' //默认的渲染层类
		];
	}
	/**
	 * 预加载组件
	 */
	private function _preInit()
	{
		$pre = $this->setPreObject();
		foreach($pre as $n=>$o){
			\H2O::setContainer($n,new $o());
		}
	}
	/**
	 * 执行引导程序
	 * @param array $bt 引导程序数组
	 * @throws \Exception
	 */
	private function _boot($bt)
	{
		foreach($bt as $b){
			if(strpos($b,'.') === false){//如果填写不存在方法名直接报错
				throw new \Exception('Configs of boot params is error!');
			}else{
				$ao = explode('.',$b);
				$o = \H2O::createObject($ao[0]);
				call_user_func([$o,'act'.ucfirst($ao[1])]);
			}
		}
	}
	/**
	 * 运行实例
	 */
	public function run()
	{
		$configs = \H2O::getAppConfigs();
		if(isset($configs['boot']) && !empty($configs['boot']) && is_array($configs['boot'])){//加载引导程序
			$this->_boot($configs['boot']);
		}
		$res = $this->handleRequest();
		$res = \H2O\helpers\String::removeUTF8Bom($res);//移除utf8 bom
		$debug = $configs['debug'];
		if($debug===true){//debug
			$logger = \H2O::getContainer('logger');
			$logger->debugger($this->runExpend());//记录运行状态
		}
		echo $res;
	}
	/**
	 * 返回消耗的毫秒、内存峰值信息
	 */
	public function runExpend()
	{
		return [
			'runtime'		=>	ceil((microtime(true)-H2O_BEGIN_TIME)*1000).'ms',//运行时间 单位毫秒
			'memory'		=>	round(memory_get_peak_usage()/1024/1024,2).'MB' //运行内存峰值
		];
	}
	/**
	 * 应用请求处理
	 */
	abstract public function handleRequest();
}
<?php
/**
 * 所有控制器的基类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
use H2O;
abstract class Controller
{
	/**
	 * @var string 命名空间
	 */
	private $_namespace;
	/**
	 * @var string 布局模块 例如layout.index
	 */
	private $_layout;
	/**
	 * @var string 主操作模块
	 */
	private $_content;
	/**
	 * @var array 渲染给模板全局变量
	 */
	public static $viewglobalvars = [];
	/**
	 * 初始化
	 */
	public function __construct()
	{
		$class = strtolower(get_called_class());
		$lastsp = strrpos($class,'\\');
		$this->_namespace = substr($class,0,$lastsp);
		$config = \H2O::getAppConfigs(); //获取应用配置信息
		if(isset($config['defaultLayout'])){//默认布局
			$this->setLayout($config['defaultLayout']);
		}
	}
	/**
	 * 执行对应的操作
	 * @param $act 操作名称
	 */
	public function runAction($act)
	{
		$action = 'act'.$act;
		if(method_exists($this,$action)){
			return call_user_func([$this,$action]);
		}else{
			throw new \Exception(get_called_class().' no method:'.$action);
		}
	}
	/**
	 * 返回视图目录
	 */
	public function getViewPath()
	{
		$reflector = new \ReflectionClass($this);
		return dirname(dirname($reflector->getFileName())).DS.'views'.DS.strtolower($reflector->getShortName());
	}
	/**
	 * 获取当前布局信息
	 * @return array 布局信息
	 */
	public function getLayout()
	{
		return $this->_layout;
	}
	/**
	 * 设置布局信息
	 * @param string $url 路由URL 例如layout.index
	 */
	public function setLayout($url)
	{
		$this->_layout = $url;
	}
	/**
	 * 清空布局
	 */
	public function clearLayout()
	{
		$this->_layout = '';
	}
	/**
	 * 设置主模块缓存
	 * @param string $content 主模块内容
	 */
	public function setContent($content)
	{
		$this->_content = $content;
	}
	/**
	 * 返回主操作模块内容
	 */
	public function getContent()
	{
		return $this->_content;
	}
	/**
	 * 返回包含模板
	 * @param string $url 例如 message.list
	 * @param string $namespace 命名空间
	 */
	public function loadModule($url,$namespace = '')
	{
		$namespace = empty($namespace)?$this->_namespace:$namespace;
		$route = Module::parseRoute($url);
		$o = \H2O::createObject($namespace.'\\'.$route['controller']);
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
	/**
	 * 设置全局的模板变量
	 * @param mixed $tag string|array
	 * @param string $var
	 */
	public function assign($tag,$var = '')
	{
	    if(is_array($tag)){//如果数组，则循环设置
	        foreach($tag as $t=>$v){
	           self::$viewglobalvars[$t] = $v; 
	        }
	    }else{//单个变量赋值
	        self::$viewglobalvars[$tag] = $var;
	    }
	}
	/**
	 * 返回模板渲染后的字符串
	 * @param string $tpl 模板文件
	 * @param array $vars 需要传入模板的数据参数
	 */
	public function render($tpl,$vars = [])
	{
		$ov = \H2O::getContainer('view');
		$ov->setFile($tpl);
		$ov->setController(new static());//设置依附的控制器
		$ov->setPath($this->getViewPath());
		$ov->setContent($this->getContent());
		$vars = array_merge(self::$viewglobalvars,$vars);//合并全局变量和局部变量
		$content = $ov->render($vars);
		if(empty($this->_layout) || !empty($this->_content)){//非布局 或者已有内容信息，则当前已为布局模块
			return $content;
		}else{//有布局
			$route = Module::parseRoute($this->_layout);
			$o = \H2O::createObject($this->_namespace.'\\'.$route['controller']);
			$o->setContent($content);//设置主模块缓存
			return call_user_func([$o,'act'.ucfirst($route['action'])]);
		}
	}
}
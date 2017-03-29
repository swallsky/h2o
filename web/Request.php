<?php
/**
 * 访问请求基类
 * @category   H2O
 * @package    web
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\web;
class Request
{
	/**
	 * @var string 默认Controller名称
	 */
	private $_defaultController = 'Site';
	/**
	 * @var string 默认Action名称
	 */
	private $_defaultAction = 'index';
	/**
	 * @var string RESTful的POST的key值. 默认为 '__method'. 共有三个值可用PUT, PATCH or DELETE
	 */
	public $methodParam = '__method';
	/**
	 * @var 路由配置表
	 */
	private $_routeTable = [];
	/**
	 * @var array GET参数
	 */
	public static $getParams = [];
	/**
	 * @var array POST数据
	 */
	public static $postData = [];
	/**
	 * 路由配置
	 */
	public function __construct()
	{
		$config = \H2O::getAppConfigs('request'); //请求配置信息
		$this->_routeTable = isset($config['route'])?$config['route']:[];
		self::$getParams = $_GET;
		self::$postData = isset($_POST)?$_POST:[];
		unset($_GET,$_POST);
	}
	/**
	 * 返回header头信息
	 */
	public function getHeaders()
	{
		if (function_exists('getallheaders')){
			$headers = getallheaders();
		}elseif(function_exists('http_get_request_headers')){
			$headers = http_get_request_headers();
		}else{
			$headers = [];
			foreach ($_SERVER as $name => $value) {
				if (substr($name, 0, 5) == 'HTTP_') {
					$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
			}
		}
		return $headers;
	}
	/**
	 * 返回当前请求的方法 (e.g. GET, POST, HEAD, PUT, PATCH, DELETE).
	 * @return string 请求方法, 例如 GET, POST, HEAD, PUT, PATCH, DELETE.
	 * 返回的值变成了大写
	 */
	public function getMethod()
	{
		if (isset($_POST[$this->methodParam])) {
			return strtoupper($_POST[$this->methodParam]);
		} elseif (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
			return strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
		} else {
			return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
		}
	}
	/**
	 * 判断是否为GET请求
	 * @return boolean 是否为GET请求
	 */
	public function getIsGet()
	{
		return $this->getMethod() === 'GET';
	}
	/**
	 * 判断是否HEAD请求
	 * @return boolean 是否为HEAD请求
	 */
	public function getIsHead()
	{
		return $this->getMethod() === 'HEAD';
	}
	/**
	 * 判断是否POST请求
	 * @return boolean 是否为POST请求
	 */
	public function getIsPost()
	{
		return $this->getMethod() === 'POST';
	}
	/**
	 * 判断是否是DELETE请求
	 * @return boolean 是否DELETE请求
	 */
	public function getIsDelete()
	{
		return $this->getMethod() === 'DELETE';
	}
	/**
	 * 判断是否是PUT请求
	 * @return boolean 是否PUT请求
	 */
	public function getIsPut()
	{
		return $this->getMethod() === 'PUT';
	}
	/**
	 * 判断是否是PATCH请求
	 * @return boolean 是否PATCH请求
	 */
	public function getIsPatch()
	{
		return $this->getMethod() === 'PATCH';
	}
	/**
	 * 判断是否为Ajax请求
	 * @return boolean 是否为Ajax请求
	 */
	public function getIsAjax()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}
	/**
	 * 判断是否为flash请求
	 * @return boolean 是否为flash、flex请求
	 */
	public function getIsFlash()
	{
		return isset($_SERVER['HTTP_USER_AGENT']) &&
		(stripos($_SERVER['HTTP_USER_AGENT'], 'Shockwave') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'Flash') !== false);
	}
	/**
	 * 获取路由控制器和动作
	 */
	public function getRoute()
	{
		$routepath = $this->getRoutePath();
		if(empty($routepath)){//默认路由规则
			$data = [
				'controller'	=>	$this->_defaultController,
				'action'		=>	$this->_defaultAction
			];
		}else{//其他路由
			$routepath = $this->getRealPath($routepath);
			$routepath = strpos($routepath,'.') === false?$routepath.'.'.ucfirst(strtolower($this->getMethod())):$routepath;
			$data = \H2O\base\Module::parseRoute($routepath);
		}
		return $data;
	}
	/**
	 * 返回url参数
	 * @param string $name GET参数key值
	 * @param string $value 给GET参数设置值
	 * @return 返回GET数据，如果不存在返回为空
	 */
	public function get($name = '',$value = '')
	{
		if($name == ''){
			return self::$getParams;
		}else{
			if(!empty($value)){
				self::$getParams[$name] = $value;
			}else{
				return isset(self::$getParams[$name])?self::$getParams[$name]:'';
			}
		}
	}
	/**
	 * 返回完整的访问路径
	 * @return string
	 */
	public function getRequestUri()
	{
		return $_SERVER['REQUEST_URI'];
	}
	/**
	 * 返回post数据
	 * @param string $name POST数据名称
	 * @param mixed $value 给POST参数赋值
	 * @param array $hpcfg HTMLPurifier配置参数
	 * @return 返回POST数据，如果不存在返回为空
	 */
	public function post($name = '',$value = '',$hpcfg = [])
	{
		if($name == ''){//如果为空返回所有数据
			$data = self::$postData;
			//防止XSS攻击 
			//此处不建议用递归 防止不稳定
			foreach ($data as $k=>$v){//first
				if(is_array($v)){
					foreach ($v as $kk=>$vv){//second
						if(is_array($vv)){
							foreach ($vv as $kkk=>$vvv){//three 超过三级时，自动合并
								if(is_array($vvv)){
									$data[$k][$kk][$kkk] = \H2O\helpers\HTMLPurifier::filter(implode(",",$vvv),$hpcfg);
								}else{
									$data[$k][$kk][$kkk] = \H2O\helpers\HTMLPurifier::filter($vvv,$hpcfg);
								}
							}
						}else{
							$data[$k][$kk] = \H2O\helpers\HTMLPurifier::filter($vv,$hpcfg);
						}
					}
				}else{
					$data[$k] = \H2O\helpers\HTMLPurifier::filter($v,$hpcfg);
				}
			}
			return $data;
		}else{
			if(!empty($value)){
				self::$postData[$name] = \H2O\helpers\HTMLPurifier::filter($value,$hpcfg);
			}else{
				return isset(self::$postData[$name])?self::$postData[$name]:'';
			}
		}
	}
	/**
	 * 返回未过滤xss的原始数据
	 * @param string $name post字段名
	 * @return string
	 */
	public function getOrgPost($name)
	{
		return isset(self::$postData[$name])?self::$postData[$name]:'';
	}
	/**
	 * @param $curoute 当前路由名称
	 */
	private function getRealPath($curoute)
	{
		if(empty($this->_routeTable)){
			return $curoute;
		}else if(is_string($this->_routeTable)){//通过API模块查寻真实URL
		    if(strpos($this->_routeTable,'.') === false){//如果填写不存在方法名直接报错
		        throw new \Exception('Configs of `request` params is error!');
		    }else{//自定义路由
		        $ao = explode('.',$this->_routeTable);
		        $o = \H2O::createObject($ao[0]);
		        $rroute = call_user_func([$o,'act'.ucfirst($ao[1])]);
		        return empty($rroute)?$curoute:$rroute;
		    }
		}else{
			$ecur = explode('/',$curoute);
			$lecur = count($ecur);
			if(isset($this->_routeTable[$ecur[0]])){
				$rpath = $this->_routeTable[$ecur[0]];
				if(is_array($rpath)){
					for($i=1;$i<$lecur;$i++){
						if(isset($rpath[$i]))
							self::$getParams[$rpath[$i]] = $ecur[$i];
					}
					return $rpath[0];
				}else{
					return $rpath;
				}
			}else{
				return $curoute;
			}
		}
	}
	/**
	 * 获取路由信息
	 */
	private function getRoutePath()
	{
		$inPath = dirname($this->getScriptUrl());
		$requesturi = $_SERVER['REQUEST_URI'];
		$wpos = strpos($requesturi,'?');
		$tcapath = $wpos===false?$requesturi:substr($requesturi,0,$wpos);
		$capath = str_replace($inPath,'',$tcapath);
		return trim($capath,'/');
	}
	/**
	 * 返回当前的家目录
	 */
	public function getHomeUrl()
	{
		return rtrim(dirname($this->getScriptUrl()), '\\/');
	}
	/**
	 * 查找对应的脚本URL
	 */
	private function getScriptUrl()
	{
        $sUrl = \H2O::getAppConfigs('indexScriptFile');//引导文件路径
        if(empty($sUrl)) {
            $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
            if (basename($_SERVER['SCRIPT_NAME']) === $scriptName)
                $scriptUrl = $_SERVER['SCRIPT_NAME'];
            elseif (basename($_SERVER['PHP_SELF']) === $scriptName)
                $scriptUrl = $_SERVER['PHP_SELF'];
            elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName)
                $scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
            elseif (($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false)
                $scriptUrl = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
            elseif (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0)
                $scriptUrl = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
            else
                throw new \Exception('It is unable to determine the entry script URL.');
        }else{
            $scriptUrl = $sUrl;
        }
		return $scriptUrl;
	}
}
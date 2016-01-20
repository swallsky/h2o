<?php
/**
 * 数据库连接
 * @category   H2O
 * @package    db
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\db;
class Connection
{
	/**
	 * @var string 数据库标识 例如多种不同库调用等
	 */
	public $dsntag;
	/**
	 * @var string 数据库类型
	 */
	public $type = 'mysql';
	/**
	 * @var string 数据源
	 */
	public $dsn;
	/**
	 * @var string 主机地址
	 */
	public $host;
	/**
	 * @var string 数据库名
	 */
	public $dbname;
	/**
	 * @var string 用户名
	 */
	public $username;
	/**
	 * @var string 密码
	 */
	public $password;
	/**
	 * @var int 端口 默认端口3306
	 */
	public $port = 3306;
	/**
	 * @var string 字符集 默认字符集为utf8
	 */
	public $charset = 'utf8';
	/**
	 * @var array 定义PDO 属性
	 */
	public $attributes;
	/**
	 * @var resource PDO句柄
	 */
	public $pdo;
	/**
	 * @var array 实例
	 */
	private static $_instance = [];
	/**
	 * 初始化连接数据库句柄
	 * @param string $dsntag 数据库配置标识 方便多库调用 默认为db
	 */
	private function __construct($dsntag = 'db')
	{
		$db = \H2O::getAppConfigs('db');
		$this->dsntag = $dsntag;
		if(isset($db[$dsntag])){//配置参数存在
			$config = $db[$dsntag]; //获取数据配置信息
			$this->_checkconfig($config);
			$this->_open();
		}else{//如果配置参数存在，则直接抛弃异常
			throw new \H2O\base\Exception('H2O\db\Connection:__construct','Connect failure config is not found"'.$this->dsntag);
		}
	}
	/**
	 * 检查配置信息是否填写正确
	 * @param array $config 配置信息
	 */
	private function _checkconfig($config)
	{
		$error = []; $standard = [
			'host'			=>	'string',
			'port'			=>	'int',
			'username'	=>	'string',
			'password'	=>	'string',
			'dbname'	=>	'string'
		];
		if(!isset($config['port']))//如果端口不存在，则使用默认
			$config['port'] = $this->port;
		foreach($standard as $k=>$v){
			if(isset($config[$k]) && ($v=='string'?is_string($config[$k]):is_int($config[$k]))){
				$this->$k = $config[$k];
			}else{
				$error[] = $k.':'.$v;
			}
		}
		if(!empty($error))
			throw new \H2O\base\Exception('H2O\db\Connection:_checkconfig','Config error is "'.join(',',$error).'"');
		$this->dsn = $this->type.':host='.$config['host'].';port='.$config['port'].';dbname='.$config['dbname'].';charset='.$this->charset; //数据源
		
	}
	/**
	 * 连接数据库
	 */
	private function _open()
	{
		try{//尝试连接
			$this->pdo = new \PDO($this->dsn,$this->username,$this->password,$this->attributes);
			$this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);//字符转义
			$this->pdo->exec('SET NAMES '.$this->charset);
		}catch (\Exception $e){//连接异常
			throw new \H2O\base\Exception('H2O\db\Connection:_open','Connect failure "'.$this->dsntag.'" :'.$e->getMessage());
		}
	}
	/**
	 * 获取MYSQL单例对象
	 * @param string $cname 数据库配置文件名
	 */
	public static function getInstance($cname = 'db')
	{
		if(!isset(self::$_instance[$cname]))//判断使用的数据库是否已经初始化
			self::$_instance[$cname] =new self($cname);	//若当前对象实例不存在
		return self::$_instance[$cname];  	//调用对象私有方法连接 数据库
	}
}
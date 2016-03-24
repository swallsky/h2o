<?php
/**
 * 数据迁移程序
 * @category   H2O
 * @package    console
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\console;
use H2O\helpers\Stdout,H2O\helpers\File;
class Migrate
{
	/**
	 * @var string 数据迁移目录
	 */
	private $_migratedir;
	/**
	 * @var string 命名空间
	 */
	private $_namespace;
	/**
	 * @var string 当前运行环境
	 */
	private $_runenv;
	/**
	 * 初始化
	 */
	public function __construct()
	{
		$version = \H2O::getAppConfigs('version');
		if(empty($version)){
			throw new \Exception("Config set error: lost version param!");
		}
		$nv = 'v'.str_replace('.','',$version); 
		$this->_migratedir = \H2O::getAppRootPath().DS.'migrate'.DS.$nv;
		$this->_namespace = \H2O::APP_ROOT_NAME.'\\migrate\\'.$nv;//命名空间
		file::createDirectory($this->_migratedir);//创建目录
		$this->_runenv = \H2O::getRunEnv();
	}
	/**
	 * 执行对应的操作
	 * @param $act 操作名称
	 */
	public function runAction($act)
	{
		$action = 'act'.ucfirst($act);
		if(method_exists($this,$action)){
			$content = call_user_func([$this,$action]);
		}else{
			throw new \Exception(get_called_class().' no method:'.$action);
		}
	}
	/**
	 * 创建迁移
	 */
	public function actCreate()
	{
		$request = \H2O::getContainer('request'); //控制台请求
		$params = $request->getParams();
		$name = isset($params['name'])?$params['name']:'crt'.date('YmdHis');
		$name = strtolower($name);
		$mfile = $this->_migratedir.DS.$name.'.php';
		$code =	'<?php
namespace '.substr($this->_namespace,1).';
class '.ucfirst($name).' extends \H2O\db\Builder
{
	/**
	 * Initialization Migrate Applcation
	 */
	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * Migrate Applcation update
	 */
	public function actUp()
	{
		//TODO
	}
	/**
	 * Migrate Applcation restore
	 */
	public function actRestore()
	{
		//TODO
	}
}';
		file::write($mfile,$code);
		echo 'Migrate application to create success!';
	}
	/**
	 * 需要执行的命令
	 * @param string $n 命令名称
	 * @throws \ErrorException
	 */
	private function _cmd($n)
	{
		$request = \H2O::getContainer('request'); //控制台请求
		$params = $request->getParams();
		if(empty($params['name'])){
			echo 'Missing required parameter: name';
			exit();
		}
		$class = $this->_namespace.'\\'.ucfirst($params['name']);
		$oc = new $class();
		try{
			$oc->beginTransaction();
			$oc->$n();
			$oc->buildExec();//执行SQL
			$oc->pdo->commit();
			echo 'Executed successfully!';
			exit();
		}catch(\Exception $e){
			$oc->pdo->rollBack();//回滚
			throw new \ErrorException($e->getMessage());
		}
	}
	/**
	 * 更新操作
	 */
	public function actUp()
	{
		$this->_cmd('actUp');
	}
	/**
	 * 恢复更新操作
	 */
	public function actRestore()
	{
		$this->_cmd('actRestore');
	}
}
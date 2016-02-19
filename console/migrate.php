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
class Migrate implements MigrateInterface
{
	/**
	 * @var string 数据迁移目录
	 */
	private $_migratedir;
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
		$this->_migratedir = APP_PATH.DS.'migrate'.DS.$version;
		file::createDirectory($this->_migratedir);//创建目录
		$this->_runenv = \H2O::getRunEnv();
	}
	/**
	 * 执行对应的操作
	 * @param $action 操作名称
	 */
	public function runAction($action)
	{
		if(method_exists($this,$action)){
			$content = call_user_func([$this,$action]);
		}else{
			throw new \Exception(get_called_class().' no method:'.$action);
		}
	}
	/**
	 * 返回SQL操作对象
	 */
	public function getDdCommand()
	{
		return new \H2O\db\Command();
	}
	/**
	 * 创建迁移
	 */
	public function create()
	{
		if($this->_runenv == 'prod'){//生产环境不充许创建迁移
			echo "Prod environment is not allowed to create migrate application!";
			exit();
		}else{
			$request = \H2O::getContainer('request'); //控制台请求
			$params = $request->getParams();
			$name = isset($params['name'])?$params['name']:'crt'.date('YmdHis');
			$name = strtolower($name);
			$mfile = $this->_migratedir.DS.$name.'.php';
			$code =	'<?php
namespace Migrate;
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
	public function up()
	{
		//TODO
	}
	/**
	 * Migrate Applcation restore
	 */
	public function restore()
	{
		//TODO
	}
}';
			file::write($mfile,$code);
			echo 'Migrate application to create success!';
		}
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
		$file = $this->_migratedir.DS.$params['name'].'.php';
		if(file_exists($file)){
			require($file);
			$class = '\Migrate\\'.ucfirst($params['name']);
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
		}else{
			echo 'The file is not exist:"'.$file.'"';
			exit();
		}
	}
	/**
	 * 更新操作
	 */
	public function up()
	{
		$this->_cmd('up');
	}
	/**
	 * 恢复更新操作
	 */
	public function restore()
	{
		$this->_cmd('restore');
	}
}
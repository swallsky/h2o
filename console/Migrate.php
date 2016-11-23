<?php
/**
 * 数据迁移程序
 * @category   H2O
 * @package    console
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\console;
use H2O\helpers\File;
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
	 * @var string 模板目录
	 */
	private $_tpldir;
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
		$this->_namespace = \H2O::APP_ROOT_NAME.'\\migrate\\'.$nv;//命名空间
		$this->_tpldir = H2O_PATH.DS.'tpls'.DS.'migrate'.DS; //迁移模块路径
		$namepath = \H2O::getPreNameSpace('app\migrate\\')[0];//读取定义的app\migrate的路径
		$this->_migratedir = empty($namepath)?\H2O::getAppRootPath().DS.'migrate'.DS.$nv:$namepath.DS.$nv;

		file::createDirectory($this->_migratedir);//创建目录
		$this->_runenv = \H2O::getRunEnv();
	}
	/**
	 * 创建迁移
	 */
	public function actCreate()
	{
		$request = \H2O::getContainer('request'); //控制台请求
		$params = $request->getParams();
		$name = isset($params['name'])?$params['name']:'crt'.date('YmdHis');
		$name = ucfirst($name);
		$mfile = $this->_migratedir.DS.$name.'.php';
		if(file_exists($mfile)){//文件已存在，提示
			echo $mfile.' is exist!'.PHP_EOL;
			exit();
		}
		$oimg = new \H2O\coding\Image();
		if($name == 'All'){//全量模板
			$oimg->file('migrate/all.php',$mfile,[
				'search'	=>	['T_NAMESPACE','T_CLASS'],
				'replace'	=>	[substr($this->_namespace,1),$name]
			]);
		}else{//普通通用模型
			$oimg->file('migrate/general.php',$mfile,[
				'search'	=>	['T_NAMESPACE','T_CLASS'],
				'replace'	=>	[substr($this->_namespace,1),$name]
			]);
		}
		echo 'Migrate application to create success!'.PHP_EOL;
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
			echo 'Missing required parameter: name'.PHP_EOL;
			exit();
		}
		$class = $this->_namespace.'\\'.ucfirst($params['name']);
		$oc = new $class();
		try{
			$oc->beginTransaction();
			$oc->$n();
			$oc->buildExec();//执行SQL
			$oc->pdo->commit();
			echo 'Executed successfully!'.PHP_EOL;
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
	/**
	 * 更新/恢复该版本下所有迁移模块
	 */
	public function actAll()
	{
	    $request = \H2O::getContainer('request'); //控制台请求
	    $params = $request->getParams();
	    if(empty($params['type'])){
	        echo 'Missing required parameter: type'.PHP_EOL;
	        exit();
	    }
	    $mtype = 'act'.ucfirst($params['type']); //更新类型 up/restore
	    $all = $this->_namespace.'\All'; //全量数据列表
	    $oall = new $all();
	    if(!method_exists($oall,'Regtable')){
	        echo $all.' is not found method: regtable'.PHP_EOL;
	        exit();
	    }
	    $regnames = $oall->Regtable(); //获取所有注册到全量的数据信息
	    if(empty($regnames) || !is_array($regnames)){
	        echo $all.':regtable return value is empty or is not array!'.PHP_EOL;
	        exit();
	    }
		foreach ($regnames as $reg) //批量执行
		{
			try{
				$class = $this->_namespace.'\\'.ucfirst($reg);
				$oc = new $class();
				$oc->clearBuildSQL(); //清空上一个模块的SQL，防止重复写入
				$oc->beginTransaction();
				$oc->$mtype();
				$oc->buildExec();//执行SQL
				$oc->pdo->commit();
			}catch(\Exception $e){
				$oc->pdo->rollBack();//回滚
				throw new \ErrorException(PHP_EOL.'Class name:'.$class.','.$e->getMessage());
			}
		}
		echo 'Executed successfully!'.PHP_EOL;
		exit();
	}
}
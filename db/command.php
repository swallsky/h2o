<?php
/**
 * 数据库命令 SQL
 * @category   H2O
 * @package    db
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\db;
use H2O,PDO;
class Command
{
	/**
	 * @var PDO 
	 */
	public $pdo = null;
	/**
	 * @var array PDO参数值
	 */
	protected $params = [];
	/**
	 * @var string SQL语句
	 */
	private $_sql;
	/**
	 * @var int 单次批处理的行数
	 */
	protected $_batchSize;
	/**
	 * 初始化命令行
	 * @param string $tag 数据库标识 用户区分应用库
	 */
	public function __construct($tag = 'db')
	{
		$connect = Connection::getInstance($tag);
		$this->pdo = $connect->pdo;
	}
	/**
	 * 返回SQL语句
	 */
	public function getSql()
	{
		return $this->_sql;
	}
	/**
	 * 设置SQL
	 * @param string $sql
	 */
	public function setSql($sql)
	{
		if($sql !== $this->_sql){
			$this->_sql = $sql;
		}
		return $this;
	}
	/**
	 * 返回参数值替换后的SQL，主要用于debug或者写日志
	 * @return string 替换参数后的SQL
	 */
	public function getRawSql()
	{
		if (empty($this->params)) {
			return $this->_sql;
		}
		$params = [];
		foreach ($this->params as $name => $value) {
			if (is_string($name) && strncmp(':', $name, 1)) {
				$name = ':' . $name;
			}
			if (is_string($value)) {
				$params[$name] = $this->quoteValue($value);
			} elseif (is_bool($value)) {
				$params[$name] = ($value ? 'TRUE' : 'FALSE');
			} elseif ($value === null) {
				$params[$name] = 'NULL';
			} elseif (!is_object($value) && !is_resource($value)) {
				$params[$name] = $value;
			}
		}
		if (!isset($params[1])) {
			return strtr($this->_sql, $params);
		}
		$sql = '';
		foreach (explode('?', $this->_sql) as $i => $part) {
			$sql .= (isset($params[$i]) ? $params[$i] : '') . $part;
		}
		return $sql;
	}
	/**
	 * 插入记录
	 * @access public
	 * @param string $table  数据表名
	 * @param array  $data  字段数组
	 * @param array  $field  字段信息
	 * @return 受影响的行数
	 ~~~
	 example 1: 单行插入
	 $this->insert('sys_menu',['sm_id'=>1,'sm_title=>'test','sm_pid'=>0]);
	 example 2: 多行插入
	 $this->insert('sys_menu',
	 	[
	 		[1,'first menu',0],
	 		[2,'second menu',1],
	 	],
	 	['sm_id','sm_title,'sm_pid']
	 );
	 ~~~
	 */
	public function insert($table, $data = [],$field = [])
	{
		$fields = [];$values = [];
		if(empty($field)){//单行插入
			foreach($data as $k=>$v){
				$fields[] = $k;//字段列表
				$values[] = $this->quoteValue($v);//字段对应的值
			}
			$sval = '('.implode(',',$values).')';
		}else{//多行插入
			$fields = $field;
			foreach($data as $dv){
				if(is_array($dv)){//必须是二维数组
					foreach($dv as $k=>$v){
						$dv[$k] = $this->quoteValue($v);//字段对应的值
					}
					$values[] = '('.implode(',',$dv).')';
				}
			}
			$sval = implode(',',$values);
		}
		return $this->setSql('INSERT INTO '.$table.' ('.implode(',',$fields).') VALUES '.$sval);
	}
	/**
	 * 更改记录信息
	 * @param string 	$table  	数据表名
	 * @param array     $fdata  	字段数组
	 * @param string 	$where  	条件
	 * @param array		$param 变量替换值
	 * @return 成功返回true 否则返回false
	 */
	public function update($table, $fdata = [], $where,$param = [])
	{
		$items = [];
		foreach($fdata as $k=>$v)
			$items[] = $k.'='.$this->quoteValue($v);
		if(!empty($param) && is_array($param)){
			$this->params = $param;
		}
		return $this->setSql('UPDATE '.$table.' SET '.implode(',',$items).' WHERE '.$where);
	}
	/**
	 * 绑定一个参数到对应的SQL占位符上
	 * @param string $name
	 * @param mixed $value
	 */
	public function bindValue($name,$value)
	{
		$this->params[$name] = $value;
		return $this;
	}
	/**
	 * 绑定多个参数到对应的SQL占位符上
	 * @param array $values
	 */
	public function bindValues($values)
	{
		if(empty($values) || !is_array($values))
			return $this;
		foreach($values as $k=>$v){
			$this->params[$k] = $v;
		}
		return $this;
	}
	/**
	 * 执行一条 SQL 语句，并返回受影响的行数 不返回结果集
	 */
	public function exec()
	{
		$sql = $this->getSql();
		$res = $this->pdo->exec($sql);
		return $this->errorInfo($res, 'exec',$this->getRawSql());
	}
	/**
	 * 变量参数预处理
	 */
	public function execute()
	{
		$sql = $this->getSql();
		$sth = $this->pdo->prepare($sql);
		$this->errorInfo($sth, 'prepare',$this->getRawSql());
		$res = empty($this->params)?$sth->execute():$sth->execute($this->params);
		$this->errorInfo($res, 'execute',$this->getRawSql());
		return $sth;
	}
	/**
	 * 执行一条 SQL 语句,并返回结果集
	 */
	public function query()
	{
		$sql = $this->getSql();
		$res = $this->pdo->query($sql);
		return $this->errorInfo($res, 'query',$this->getRawSql());
	}
	/**
	 * 获取一行结果集
	 */
	public function fetch()
	{
		$sth = $this->execute();
		return $sth->fetch(PDO::FETCH_ASSOC);
	}
	/**
	 * 获取所有结果集
	 */
	public function fetchAll()
	{
		$sth = $this->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}
	/**
	 * 返回结果集行数
	 */
	public function rowCount()
	{
		$sth = $this->execute();
		return $sth->rowCount();
	}
	/**
	 * 大数据批处理
	 * @param int $bn 批次
	 */
	public function fetchBatch($bn)
	{
		$start = ($bn-1)*$this->_batchSize;//分页处理
		$sql = $this->getSql();
		$this->setSql($sql.'  LIMIT '.$start.','.$this->_batchSize);
		$res = $this->fetchAll();
		$this->setSql($sql);//将源SQL还原
		return $res;
	}
	/**
	 * 迭代批处理对象
	 * 例如:
	 	$db = new \H2O\db\Command();
		$query = $db->setSql('SELECT * FROM user ORDER BY us_id DESC')->batch(10);
		foreach ($query as $k=>$v){
			echo $v['us_id'].'<br>';
		}
	 * @param int $bsize 单次处理的行数
	 */
	public function batch($bsize = 20)
	{
		$this->_batchSize = $bsize;
		return new H2O\data\Batch($this);
	}
	/**
	 * 返回表所对应的字段列名
	 * @param string $table 表名
	 */
	public function getColumnName($table)
	{
		$sth = $this->pdo->query('SELECT * FROM '.$table);
		$colcount = $sth->columnCount();
		$fields = [];
		for($i=0;$i<$colcount;$i++){
			$fields[] = $sth->getColumnMeta($i)['name'];
		}
		return $fields;
	}
	/**
	 * 返回安全有效的数据
	 * @param mixed $str
	 */
	private function quoteValue($str)
	{
		if (!is_string($str)) {
			return $str;
		}
		if (($value = $this->pdo->quote($str)) !== false) {
			return $value;
		} else {
			return "'" . addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032") . "'";
		}
	}
	/**
	 * 返回插入的ID
	 */
	public function lastInsertId()
	{
		return $this->pdo->lastInsertId();
	}
	/**
	 * 事务处理 方便没有设置成功时 回滚 事务在mysql的表中只能为InnoDB引擎
	 * @param  anonymous function 事务函数
	 * 例如：
	 * $db = new \H2O\db\Command();
		$db->transaction(function($db){
			$randid = mt_rand(100,999);
			$db->insert('user',['us_name'=>'测试'.$randid,'us_password'=>'123456','us_email'=>'1@1'.$randid.'.com'])->execute();
			$query = $db->setSql('SELECT * FROM user WHERE us_name1=:us_name')->bindValues([':us_name'=>'root'])->fetch();
			print_r($query);
		});
	 */
	public function transaction($ansfun)
	{
		if(!empty($ansfun)){
			try {
				$this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);//显示报告和异常
				$this->pdo->beginTransaction(); //开始事务
				$ansfun($this); //业务事务
				$this->pdo->commit(); //提交事务
			} catch (\Exception $e) {
				$this->pdo->rollBack();//回滚
				throw new \ErrorException($e->getMessage());
			}
		}
	}
	/**
	 * 事务处理 不包含匿名函数作为参数的
	 * 例如：
	 * $db = new \H2O\db\Command();
	 * try{
			$db->beginTransaction();
			$randid = mt_rand(100,999);
			$db->insert('user',['us_name'=>'测试'.$randid,'us_password'=>'123456','us_email'=>'1@1'.$randid.'.com'])->execute();
			$query = $db->setSql('SELECT * FROM user WHERE us_name1=:us_name')->bindValues([':us_name'=>'root'])->fetch();
			print_r($query);
			$db->pdo->commit();
		}catch(\Exception $e){
			$db->pdo->rollBack();//回滚
			throw new \ErrorException($e->getMessage());
		}
	 */
	public function beginTransaction()
	{
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);//显示报告和异常
		$this->pdo->beginTransaction(); //开始事务
	}
	/**
	 * 错误信息
	 * @param mixed $res 查询后的句柄
	 * @param string $tag 类别
	 * @param string $sql 执行SQL
	 * @return
	 */
	private function errorInfo($res,$tag,$sql = '')
	{
		$error = $this->pdo->errorInfo();
		if($res===false && $error[0]!='00000'){//发生错误
			$sql = empty($sql)?'':$sql.PHP_EOL;
			throw new \ErrorException($sql."\tERROR:".$error[2]);
		}
		return $res;
	}
}
?>
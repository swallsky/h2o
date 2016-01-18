<?php
/**
 * 数据库命令查询
 * @category   H2O
 * @package    db
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\db;
use PDO;
class Command
{
	/**
	 * @var PDO 
	 */
	protected $pdo = null;
	/**
	 * @var string SQL语句
	 */
	private $_sql;
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
		$this->_sql = $sql;
	}
	/**
	 * 插入记录
	 * @access public
	 * @param string $table  数据表名
	 * @param array  $data  字段数组
	 * @return 受影响的行数
	 */
	public function insert($table, $data = array())
	{
		$fields = array();
		$values = array();
		foreach($data as $k=>$v){
			$fields[] = $k;//字段列表
			$values[] = $this->pdo->quote($v);//字段对应的值
		}
		$sql = 'INSERT INTO '.$table.' ('.join(',',$fields).') VALUES ('.join(',',$values).')';
		return $this->exec($sql);
	}
	/**
	 * 更改记录信息
	 * @param string 	$table  	数据表名
	 * @param array     $fdata  	字段数组
	 * @param string 	$where  	条件
	 * @param array		$param 变量替换值
	 * @return 成功返回true 否则返回false
	 */
	public function update($table, $fdata = array(), $where,$param = array())
	{
		$items = array();
		foreach($fdata as $k=>$v)
			$items[] = $k.'='.$this->pdo->quote($v);
		$sql = 'UPDATE '.$table.' SET '.implode(',',$items).' WHERE '.$where;
		return $this->execute($sql,$param);
	}
	/**
	 * 执行一条 SQL 语句，并返回受影响的行数 不返回结果集
	 * @param string $sql
	 */
	public function exec($sql)
	{
		$res = $this->pdo->exec($sql);
		return $this->errorInfo($res, 'exec',$sql);
	}
	/**
	 * 执行一条 SQL 语句,并返回结果集
	 * @param string $sql
	 */
	public function query($sql)
	{
		$res = $this->pdo->query($sql);
		return $this->errorInfo($res, 'query',$sql);
	}
	/**
	 * 变量参数预处理
	 * @param string $sql
	 * @param array $param
	 实例1
	 $sth = $dbh->prepare('SELECT name, colour, calories FROM fruit WHERE calories < ? AND colour = ?');
	 $sth->execute(array(150, 'red'));
	 $red = $sth->fetchAll();
	
	 实例2
	 $sql = 'SELECT name, colour, calories FROM fruit  WHERE calories < :calories AND colour = :colour';
	 $sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	 $sth->execute(array(':calories' => 150, ':colour' => 'red'));
	
	 */
	public function execute($sql,$param = array())
	{
		$sth = $this->pdo->prepare($sql);
		$this->errorInfo($sth, 'prepare',$sql);
		$res = empty($param)?$sth->execute():$sth->execute($param);
		$this->errorInfo($res, 'execute',$sql);
		return $sth;
	}
	/**
	 * 获取一行结果集
	 * @param string $sql
	 * @param array $param
	 */
	public function fetch($sql,$param = array())
	{
		$sth = $this->execute($sql,$param);
		return $sth->fetch(PDO::FETCH_ASSOC);
	}
	/**
	 * 获取所有结果集
	 * @param string $sql
	 * @param array $param
	 */
	public function fetchAll($sql,$param = array())
	{
		$sth = $this->execute($sql,$param);
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}
	/**
	 * 返回结果集行数
	 * @param string $sql
	 * @param array $param
	 */
	public function rowCount($sql,$param = array())
	{
		$sth = $this->execute($sql,$param);
		return $sth->rowCount();
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
	 */
	public function transaction($ansfun)
	{
		if(!empty($ansfun)){
			try {
				$this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);//显示报告和异常
				$this->pdo->beginTransaction(); //开始事务
				$ansfun(); //业务事务
				$this->pdo->commit(); //得交事务
			} catch (\Exception $e) {
				$this->pdo->rollBack();//回滚
				throw new \H2O\base\Exception('H2O\db\Command:transaction',$e->getMessage());
			}
		}
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
			throw new \H2O\base\Exception('SQL.'.$tag,$sql."\tERROR:".$error[2]);
		}
		return $res;
	}
}
?>
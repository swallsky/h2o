<?php
/**
 * 构建数据结构
 * @category   H2O
 * @package    db
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\db;
class Builder extends Command
{
	/**
	 * 初始化
	 * @param string $tag 数据库标识 用户区分应用库
	 */
	public function __construct($tag = 'db')
	{
		parent::__construct($tag);
	}
	/**
	 * @var array 缓存SQL语句变量
	 */
	private static $_buildsqls = [];
	/**
	 * @var array 列类型映射转换
	 * - `pk`: an auto-incremental primary key type, will be converted into "int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY"
     * - `bigpk`: an auto-incremental primary key type, will be converted into "bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY"
     * - `string`: string type, will be converted into "varchar(255)"
     * - `text`: a long string type, will be converted into "text"
     * - `longtext`: a long string type, will be converted into "longtext"
     * - `smallint`: a small integer type, will be converted into "smallint(6)"
     * - `int`: integer type, will be converted into "int(11)"
     * - `bigint`: a big integer type, will be converted into "bigint(20)"
     * - `boolean`: boolean type, will be converted into "tinyint(1)"
     * - `float``: float number type, will be converted into "float"
     * - `decimal`: decimal number type, will be converted into "decimal"
     * - `datetime`: datetime type, will be converted into "datetime"
     * - `timestamp`: timestamp type, will be converted into "timestamp"
     * - `time`: time type, will be converted into "time"
     * - `date`: date type, will be converted into "date"
     * - `money`: money type, will be converted into "decimal(19,4)"
     * - `binary`: binary data type, will be converted into "blob"
	 */
	private $_typeMap = [
		'pk'				=>	'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY', //主键
		'bigpk'		=>	'bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY', //主键
		'stringpk'	=>	'varchar(200) NOT NULL PRIMARY KEY', //非数字的主键值
		'string'		=>	'varchar(N)', //字符串 字符串长度默认为255
		'text'			=>	'text', //文本
		'longtext'	=>	'longtext', //长文本
		'smallint'	=>	'smallint(6)', //短整型
		'int'				=>	'int(11)', //整型
		'bigint'		=>	'bigint(20)', //大整型
		'bool'			=>	'tinyint(1)', //布尔类型 0，1
		'float'			=>	'float', //浮点型
		'decimal'	=>	'decimal', //小数
		'datetime'	=>	'datetime', //日期时间
		'timestamp'	=>	'timestamp', //时间戳
		'time'			=>	'time', //时间
		'date'			=>	'date', //时间
		'money'		=>	'decimal(19,4)', //金钱类型
		'binary'		=>	'blob' //二进制
	];
	/**
	 * 返回列信息
	 * @param array $type 列信息
	 */
	private function _getColumnType($type)
	{
		$col = ''; //列信息
		if(is_array($type) && isset($this->_typeMap[$type[0]])){//必须为数组，并且类型必须存在
			$comment = isset($type[1])?' COMMENT \''.$type[1].'\'':''; //备注
			switch ($type[0]){
				case 'pk': case 'bigpk':
					$col = $this->_typeMap[$type[0]].$comment;
					break;
				case 'string':
					$col = $this->_typeMap[$type[0]];
					$default = isset($type[4])?' DEFAULT \''.$type[4].'\'':''; //默认值
					$col = str_replace('(N)','('.(isset($type[2])?intval($type[2]):255).')',$col); //字符长度
					$col = $col.(isset($type[3]) && $type[3]==1?' NOT NULL':'').$default.$comment;
					break;
				default:
					$default = isset($type[3])?' DEFAULT \''.$type[3].'\'':''; //默认值
					$col = $this->_typeMap[$type[0]].(isset($type[2]) && $type[2]==1?' NOT NULL':'').$default.$comment;
			}
		}else{
			if(is_array($type)){
				throw new \Exception(get_called_class().' no found column type:'.$type[0]);
			}else{
				throw new \Exception(get_called_class().' no found column type:'.var_dump($type));
			}
		}
		return $col;
	}
	/**
	 * 新增SQL语句
	 * @param string $sql SQL语句
	 */
	public function setBuildSql($sql)
	{
		self::$_buildsqls[] = $sql;
	}
	/**
	 * 返回SQL语句
	 */
	public function getBuildSql()
	{
		return implode(';'.PHP_EOL,self::$_buildsqls).';'.PHP_EOL;
	}
	/**
	 * 将SQL语句导入到数据库
	 */
	public function buildExec()
	{
		return $this->setSql($this->getBuildSql())->exec();
	}
	/**
	 * 判断表是否存在
	 * @param string $table 表名
	 * @return false:为不存在,true:为存在
	 */
	public function existTable($table)
	{
	    $tmp = $this->setSql('SHOW TABLES WHERE Tables_in_'.$this->dbname.'="'.$table.'"')->fetch();
	    return empty($tmp)?false:true;
	}
	/**
	 * 创建新表
	 * 例如,
	 ~~~
	 $sql = $queryBuilder->createTable(['sys_user','用户表'], [
	 		'usr_id' => ['pk','用户ID'], //第一个字段类型，第二参数为备注
	 		'usr_name' => ['string','用户名',30,1,'无'], //如果为字符类型，第三参数是长度，第四个参数是否必填，其他类型都是第三个参数为是否必填
	 		'usr_age' => ['int','年龄',1,20], //第一个参数是类型，第二个参数是备注，第三个参数是否必填，第四个参数为默认值
	 		'usr_intro' => ['text','介绍',0],
	 		'usr_birthday' => ['date','生日',1]
	 ]);
	 ~~~
	 * @param mixed $table 要创建的表的名称
	 * @param array $columns 字段定义
	 * @param string $charset 字符集类型，默认为utf8
	 * @param string $engine 引擎类型，默认为InnoDB
	 * @return string 创建表的SQL语句
	 */
	public function createTable($table, $columns, $charset = 'utf8',$engine = 'InnoDB')
	{
		$cols = [];
		foreach ($columns as $name => $type) {
			if (is_string($name)) {
				$cols[] = "  `" . $name . '` ' . $this->_getColumnType($type);
			} else {
				$cols[] = '  '.$type;
			}
		}
		if(is_array($table)){
			$tname = $table[0];
			$tcomment = ' COMMENT \''.$table[1].'\'';
			$isdrop = (isset($table[2]) && $table[2]==1)?"DROP TABLE IF EXISTS `".$tname."`;".PHP_EOL:'';
		}else{
			$tname = $table;
			$tcomment = '';
			$isdrop = '';
		}
		self::setBuildSql($isdrop."CREATE TABLE `" . $tname . "` (".PHP_EOL . implode(",".PHP_EOL, $cols) .PHP_EOL.") ENGINE=".$engine." DEFAULT CHARSET=".$charset.$tcomment);
		return $this;
	}
	/**
	 * 修改数据库表名
	 * @param string $oldName 旧表名
	 * @param string $newName 新表名
	 * @return string 修改表名SQL语句
	 */
	public function renameTable($oldName, $newName)
	{
		self::setBuildSql('RENAME TABLE `' . $oldName . '` TO `' . $newName.'`');
		return $this;
	}
	/**
	 * 删除数据库表
	 * @param string $table 表名
	 * @return string 删除SQL语句
	 */
	public function dropTable($table)
	{
		self::setBuildSql("DROP TABLE `".$table."`");
		return $this;
	}
	/**
	 * 清空数据库表所有数据
	 * @param string $table 表名
	 * @return string 清空数据SQL语句
	 */
	public function truncateTable($table)
	{
		self::setBuildSql("TRUNCATE TABLE `".$table."`");
		return $this;
	}
	/**
	 * 增加数据库表的列字段
	 * @param string $table 表名
	 * @param string $column 字段名称
	 * @param string $type 字段属性
	 * @return string 增加表字段SQL语句
	 */
	public function addColumn($table, $column, $type)
	{
		self::setBuildSql('ALTER TABLE `'.$table.'` ADD `'.$column.'` '.$this->_getColumnType($type));
		return $this;
	}
	
	/**
	 * 删除数据库表的列字段
	 * @param string $table 表名
	 * @param string $column 字段名
	 * @return string 删除数据库表的列SQL语句
	 */
	public function dropColumn($table, $column)
	{
		self::setBuildSql("ALTER TABLE `".$table."` DROP COLUMN `".$column."`");
		return $this;
	}
	/**
	 * 修改数据库表的字段属性
	 * @param string $table 表名
	 * @param string $oldName 旧的字段名
	 * @param string $newName 新的字段名
	 * @param string $type 字段属性
	 * @return string 修改字段属性SQL语句
	 */
	public function alterColumn($table, $oldName, $newName, $type)
	{
		self::setBuildSql('ALTER TABLE `'.$table.'` CHANGE `'.$oldName. '` `'.$newName.'` '.$this->_getColumnType($type));
		return $this;
	}
	/**
	 * 构建一个SQL语句添加到现有表主键
	 * @param string $table 表名
	 * @param string $name 主键字段
	 * @return string 创建索引SQL语句
	 */
	public function addPrimaryKey($table,$name)
	{
	    self::setBuildSql('ALTER TABLE  `'.$table.'` ADD PRIMARY KEY (  `'.$name.'` )');
	    return $this;
	}
	
	/**
	 * 构建一个SQL语句删除现有表的主键
	 * @param string $table 表名
	 * @return string 创建索引SQL语句
	 */
	public function dropPrimaryKey($table)
	{
	    self::setBuildSql('ALTER TABLE '.$table.' DROP PRIMARY KEY');
	    return $this;
	}
	/**
	 * 创建一个索引SQL语句
	 * @param string $name 索引名称
	 * @param string $table 创建索引的表名
	 * @param string $columns 需要创建索引的字段名称，多个字段，以逗号分隔
	 * @param boolean $unique 是否需要创建一个唯一不重复的索引值 默认为false
	 * @return string 创建索引SQL语句
	 */
	public function createIndex($name, $table, $columns, $unique = false)
	{
		self::setBuildSql(($unique ? 'CREATE UNIQUE INDEX ' : 'CREATE INDEX ')
		. '`' . $name . '` ON `'.$table.'` ('.$columns.')');
		return $this;
	}
	/**
	 * 删除索引
	 * @param string $name 需要删除的索引名称
	 * @param string $table 表名
	 * @return string 删除索引的SQL语句
	 */
	public function dropIndex($name, $table)
	{
		self::setBuildSql('DROP INDEX `'.$name. '` ON `'.$table.'`');
		return $this;
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
		parent::insert($table,$data,$field);
		$sql = parent::getSql(); //获取单条时的SQL语句
		self::setBuildSql($sql);
		return $this;
	}
	/**
	 * 更改记录信息
	 * @param string 	$table  	数据表名
	 * @param array     $fdata  	字段数组
	 * @param string 	$where  	条件
	 * @return 成功返回true 否则返回false
	 */
	public function update($table, $fdata = [], $where)
	{
		parent::update($table,$fdata,$where);
		$sql = parent::getSql(); //获取单条时的SQL语句
		self::setBuildSql($sql);
		return $this;
	}
	/**
	 * 绑定一个参数到对应的SQL占位符上
	 * @param string $name
	 * @param mixed $value
	 */
	public function bindValue($name,$value)
	{
		throw new \Exception("Builder does not support this method");
	}
	/**
	 * 绑定多个参数到对应的SQL占位符上
	 * @param array $values
	 */
	public function bindValues($values)
	{
		throw new \Exception("Builder does not support this method");
	}
}
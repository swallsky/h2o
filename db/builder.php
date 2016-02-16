<?php
/**
 * 构建数据结构
 * @category   H2O
 * @package    db
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\db;
class Builder
{
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
	 * 创建新表
	 * 例如,
	 * ~~~
	 * $sql = $queryBuilder->createTable(['sys_user','用户表'], [
	 *  'usr_id' => ['pk','用户ID'], //第一个字段类型，第二参数为备注
	 *  'usr_name' => ['string','用户名',30,1], //如果为字符类型，第三参数是长度，第四个参数是否必填，其他类型都是第三个参数为是否必填
	 *  'usr_age' => ['int','年龄',1], //第三个参数如果为1，则是必须填写
	 *  'usr_intro' => ['text','介绍',0],
	 *  'usr_birthday' => ['date','生日',1]
	 * ]);
	 * ~~~
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
				$cols[] = "\t`" . $name . '` ' . $this->_getColumnType($type);
			} else {
				$cols[] = "\t" . $type;
			}
		}
		if(is_array($table)){
			$tname = $table[0];
			$tcomment = ' COMMENT '.$table[1];
		}else{
			$tname = $table;
			$tcomment = '';
		}
		return "CREATE TABLE `" . $tname . "` (\n" . implode(",\n", $cols) . "\n) ENGINE=".$engine." DEFAULT CHARSET=".$charset.$tcomment."\n";
	}
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
					$type = $this->_typeMap[$type[0]];
					$default = isset($type[5])?' DEFAULT \''.$type[5].'\'':''; //默认值
					$type = str_replace('(N)','('.(isset($type[2])?intval($type[2]):255).')',$type); //字符长度
					$col = $type.(isset($type[3]) && $type[3]==1?' NOT NULL':'').$default.$comment;
				break;
				default:
					$default = isset($type[4])?' DEFAULT \''.$type[4].'\'':''; //默认值
					$col = $this->_typeMap[$type[0]].(isset($type[2]) && $type[2]==1?' NOT NULL':'').$default.$comment;
			}
		}
		return $col;
	}
}
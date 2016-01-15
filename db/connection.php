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
	 * @var int 端口
	 */
	public $port;
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
	 * 初始化
	 * @param string $tag 多库标识
	 */
	public function __construct($tag)
	{
		
	}
}
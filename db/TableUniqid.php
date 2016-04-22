<?php
/**
 * 分表的全局唯一ID管理
* @category   H2O
* @package    db
* @author     Xujinzhang <xjz1688@163.com>
* @version    0.1.0
*/
namespace H2O\db;
class TableUniqid extends Builder
{
    /**
     * @var string 自定义函数前缀
     */
    private $_fnpre = 'hfnu_';
    /**
     * @var string 缓存表名
     */
    private $_cachetable = 'sequence';
    /**
     * @var string 数据库配置信息
     */
    private $_dbtag = 'db';
    /**
	 * 初始化
	 * @param string $tag 数据库标识 用户区分应用库
	 */
	public function __construct($tag = 'db')
	{
		parent::__construct($tag);
		$this->_dbtag = $tag;
	}
    /**
     * 创建自定义函数
     * @param string $name 函数名
     * @param array $params 参数
     * @param string $ret 函数返回类型  STRING|INTEGER|REAL
     * @param array $fun 函数体返回的SQL语句,数组形式返回
     * @param string $cmt 备注
     */
    private function _create($name,$params = [],$ret = 'INTEGER',$fun = [],$cmt = '')
    {
        $start = [ //自定义函数开始
            'DROP FUNCTION IF EXISTS '.$name.';',
            'DELIMITER $',
            'CREATE FUNCTION '.$name.' ('.implode(',',$params).')',
            'RETURNS INTEGER',
            'LANGUAGE SQL',
            'DETERMINISTIC',
            'CONTAINS SQL',
            'SQL SECURITY DEFINER',
            'COMMENT ""',
            'BEGIN'];
        $end = [ //自定义函数结束
            'END',
            '$',
            'DELIMITER ;'
        ];
        return implode(PHP_EOL,$start).PHP_EOL.implode(PHP_EOL,$fun).implode(PHP_EOL,$end).PHP_EOL;
    }
    /**
     * 存储全局唯一ID和初始值的缓存表
     * @param string $tbname 表名
     * @param int $startid 开始ID
     * @param int $stepid 步长
     */
    public static function SetVal($tbname,$startid = 0,$stepid = 1)
    {
        return 'INSERT INTO '.$this->_cachetable.' VALUES ("'.$tbname.'",'.$startid.','.$stepid.')';
    }
    /**
     * 创建全局缓存表
     */
    private function _createCacheTable()
    {
        $this->createTable([$this->_cachetable,'分表全局唯一ID缓存表'],[
            'tbname' 	  => ['string','表名',80,0,1],
            'insertid'      => ['int','插入ID'],
            'stepid'        => ['int','访问者ip']
        ]);
        $this->addPrimaryKey($this->_cachetable,'tbname'); //创建主键
    }
    /**
     * 创建获取插入值函数
     */
    private function _crtInsertId()
    {
        $this->setBuildSql($this->_create(
            $this->_fnpre.'getinsertid', //函数名
            ['tname VARCHAR(100)'], //参数
            'INTEGER', //返回值为整数
            [
                'DECLARE value INTEGER;', //设置返回变量
                'SET value = 0;', //设置变量默认值为0
                'SELECT insertid INTO value FROM '.$this->_cachetable.' WHERE tbname = tname;', //查询表名对应的最后插入值,并写入变量value中
                'RETURN value;' //返回查找到的结果
            ],
            '返回表名对应的autoid'
        ));
    }
    /**
     * 创建获取下一个插入值函数
     */
    private function _crtNextId()
    {
        $this->setBuildSql($this->_create(
            $this->_fnpre.'getnextid', //函数名
            ['tname VARCHAR(100)'], //参数
            'INTEGER', //返回值为整数
            [
                'UPDATE '.$this->_cachetable.' SET insertid = insertid + stepid WHERE tbname = tname;', //自增
                'RETURN '.$this->_fnpre.'getinsertid(tname);'
            ],
            '获取下一个自增值'
        ));
    }
    /**
     * 设置对应表的自增ID值
     */
    private function _crtSetId()
    {
        $this->setBuildSql($this->_create(
            $this->_fnpre.'setid', //函数名
            ['tname VARCHAR(100)','value INTEGER'], //参数
            'INTEGER', //返回值为整数
            [
                'UPDATE '.$this->_cachetable.' SET insertid = value WHERE tbname = tname;', //设置新的自增值
                'RETURN '.$this->_fnpre.'getinsertid(tname);'
            ],
            '设置自增值'
        ));
    }
    /**
     * 全局唯一ID初始化
     */
    public function create()
    {
        $this->_createCacheTable();
        $this->_crtInsertId();
        $this->_crtNextId();
        $this->_crtSetId();
        $sql = $this->getBuildSql();
        $sqlfile = APP_RUNTIME.DS.'dtguid.log'; //自定义函数
		\H2O\helpers\File::write($sqlfile,$sql);//写入缓存文件
		$config = \H2O::getAppConfigs('db');
		$conf = $config[$this->_dbtag];
		shell_exec('mysql -h'.$conf['host'].' -u'.$conf['username'].' -p'.$conf['password'].' '.$conf['dbname'].' < '.$sqlfile);
    }
    /**
     * 清空缓存表
     */
    public function clear()
    {
        $this->truncateTable($this->_cachetable)->buildExec();
    }
    /**
     * 删除缓存表和自定义函数
     */
    public function delete()
    {
        $this->dropTable($this->_cachetable); //删除表
        $this->setBuildSql('DROP FUNCTION '.$this->_fnpre.'getinsertid');
        $this->setBuildSql('DROP FUNCTION '.$this->_fnpre.'getnextid');
        $this->setBuildSql('DROP FUNCTION '.$this->_fnpre.'setid');
        $this->buildExec();
    }
}


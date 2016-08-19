<?php
/**
 * 分表策略
 * @category   H2O
 * @package    db
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\db;
abstract class TableStrategy extends Command
{
    /**
     * @var object build Object
     */
    private $_obuild = null;
    /**
     * @var string SQL语句中需要替换表名
     */
    private $_tablesql = '{TABLENAME}';
    /**
     * @var string 表名前缀
     */
    private $_tablepre = '';
	/**
	 * @var int 插入的ID
	 */
	protected $_insertid = 0;
	/**
	 * @var array 表对应的主键信息
	 */
	private $_keyPrCache = [];
	/**
	 * 初始化
	 * @param string $tag 数据库标识 用户区分应用库
	 */
	public function __construct($tag = 'db')
	{
		parent::__construct($tag);
		$this->_obuild = new \H2O\db\Builder($tag);
		$this->_tablepre = $this->_tablePre();
	}
	/**
	 * @return Object build对象
	 */
	public function getBuild()
	{
		return $this->_obuild;
	}
	/**
	 * 表名前缀固定部分 以当前类名为表名前缀
	 */
	private function _tablePre()
	{
	   if($this->_tablepre==''){
	        $class = get_called_class();
	        $last = strrpos($class,'\\');
	        if($last !== false) $class = substr($class,$last+1);
    	    $class = str_split($class);
    	    $tmp = [];
    	    foreach ($class as $k=>$c){
    	        $s = ord($c);
    	        $tmp[] = ($s > 64 && $s<91 && $k>0)?'_'.$c:$c;
    	    }
    	    return strtolower(implode('',$tmp)).'_';
	    }else{
	        return $this->_tablepre;
	    }
	}
	/**
	 * 表名后缀动态部分
	 ~~~
	 example:
	 public function TableExt()
	 {
	   return date('Ymd'); //动态表名后缀
	 }
	 ~~~
	 */
	abstract public function TableExt();
	/**
	 * 新建表时，初始化策略
	 */
	public function AUTO_INC_INIT()
	{
	    //TODO
	}
	/**
	 * 主键自增策略
	 * @return 自增ID 如果为空，则跟单表自增规则一致
	 */
	public function AUTO_INCREMENT()
	{
		$uuid = $this->setSql('SELECT UUID() as uid')->fetch();//利用mysql uuid函数生成一个唯一ID
		$this->_insertid = $this->quoteValue($uuid['uid']);
		return $this->_insertid;
	}
	/**
	 * 返回插入ID
	 * @return mixed
	 */
	public function getInsertId()
	{
		return $this->_insertid;
	}
	/**
	 * 定义表结构 
	 ~~~
	 example:
	 public function Structure(){
    	 return [
            'lgd_id' 	=> ['stringpk','日志ID'],
            'lgd_time' 	=> ['datetime','时间'],
            'lgd_model' 	=> ['string','模块名称',30,0],
            'lgd_stfid' 	=> ['int','访问者id',1],
            'lgd_stfname' 	=> ['string','访问者名称'],
            'lgd_url' 	=> ['text','访问url'],
            'lgd_requesttype' 	=> ['string','请求类型',30,0],
            'lgd_data' 	=> ['text','请求数据'],
            'lgd_ip' => ['string','访问者ip'],
            'lgd_group' => ['smallint','群组分类',1],
            'lgd_isdel' => ['bool','是否有效',1,0]
        ];
    }
	 ~~~
	 */
	abstract public function Structure();
	/**
	 * @return 返回分表的标记表名
	 */
	public function getTableTag()
	{
	    return $this->_tablesql;
	}
	/**
	 * 返回规则下的表名 默认为当前表
	 * @param string $ext 后缀值 如果为空就是返回当前表
	 * @throws \Exception
	 * @return string
	 */
	public function getTableName($ext = '')
	{
        return empty($ext)?$this->_tablepre.$this->TableExt():$this->_tablepre.$ext;
	}
	/**
	 * @return array 所有该规则的所有表
	 */
	public function getTablesName()
	{
		return parent::getTables($this->_tablepre);
	}
	/**
	 * 插入记录
	 * @access public
	 * @param array  $data  字段数组
	 * @param array  $field  字段信息
	 * @param string $ext 表名后缀信息 默认为空
	 * @return 受影响的行数
	 ~~~
	 example 1: 单行插入
	 $this->insert([
	 	'sm_id'=>1,
	 	'sm_title=>'test',
	 	'sm_pid'=>0
	 ])->execute();
	 example 2: 多行插入
	 $this->insert(
		 [
			 [1,'first menu',0],
			 [2,'second menu',1],
		 ],
		 ['sm_id','sm_title,'sm_pid']
	 )->execute();
	 ~~~
	 */
	public function insert($data = [],$field = [],$ext = '')
	{
	    $table = $this->getTableName($ext);
	    $fieldstu = $this->Structure();//表结构
	    if(!$this->_obuild->existTable($table)){ //如果表不存在，则创建
	        $this->_obuild->createTable($table,$fieldstu)->buildExec();
	        $this->AUTO_INC_INIT(); //初始化策略
			$this->_obuild->clearBuildSQL(); //清空
	    }
	    //主键查找逻辑
        $keyfield = '';
		if(isset($this->_keyPrCache[$table])){
			$keyfield = $this->_keyPrCache[$table];
		}else{
			foreach($fieldstu as $fk=>$fv){
				if(in_array($fv[0],['pk','bigpk','stringpk'])){//查找主键
					$keyfield = $fk;
					$this->_keyPrCache[$table] = $fk; //缓存主键
					break;
				}
			}
		}
        if(empty($keyfield)){//未找到主键时会报错
            throw new \Exception("Current table not found pk/bigpk/stringpk type field.");
        }
	    
        $fields = [];$values = [];
        if(empty($field)){//单行插入
            $fields[] = $keyfield;
            foreach($data as $k=>$v){
                $fields[] = $k;//字段列表
                $values[] = $this->quoteValue($v);//字段对应的值
            }
            $sval = '('.$this->AUTO_INCREMENT().','.implode(',',$values).')';
        }else{//多行插入
            array_unshift($field,$keyfield); //在开头加入自增字段
            $fields = $field;
            foreach($data as $dk=>$dv){
                if(is_array($dv)){//必须是二维数组
                    foreach($dv as $k=>$v){
                        $dv[$k] = $this->quoteValue($v);//字段对应的值
                    }
                    $values[] = '('.$this->AUTO_INCREMENT().','.implode(',',$dv).')';
                }
            }
            $sval = implode(',',$values);
        }
        $this->setSql('INSERT INTO '.$table.' ('.implode(',',$fields).') VALUES '.$sval);
	    return $this;
	}
	/**
	 * 数据库union 多表查询，但是不过滤重复值
	 */
	private function _unionSql()
	{
	    $sql = $this->getRawSql();//解析完参数后的SQL语句
	    $tables = $this->getTablesName();
		$sqls = '';
		if(count($tables)>1){//多表
			$tsql = [];
			foreach ($tables as $s){
				$tsql[] = '('.str_replace($this->_tablesql,$s,$sql).')';
			}
			$sqls = implode(' UNION ',$tsql);
		}else{//单表
			$sqls = str_replace($this->_tablesql,$tables[0],$sql);
		}
	    $this->setSql($sqls);
	}
	/**
	 * 更改记录信息
	 * @param array        $fdata  	字段数组
	 * @param string 	 $where  	条件
	 * @return 成功返回true 否则返回false
	 */
	public function update($fdata = [], $where)
	{
	    parent::update($this->_tablesql,$fdata,$where);
	    $this->_unionSql();
	    return $this;
	}
	/**
	 * 获取一行结果集
	 */
	public function fetch()
	{
	    $this->_unionSql();
	    $sth = $this->prepare();
	    $this->execute($sth);
		return $sth->fetch(\PDO::FETCH_ASSOC);
	}
	/**
	 * 获取所有结果集
	 */
	public function fetchAll()
	{
	    $this->_unionSql();
	    $sth = $this->prepare();
	    $this->execute($sth);
		return $sth->fetchAll(\PDO::FETCH_ASSOC);
	}
	/**
	 * 返回结果集行数
	 */
	public function rowCount()
	{
	    $this->_unionSql();
	    $sth = $this->prepare();
	    $this->execute($sth);
		return $sth->rowCount();
	}
}

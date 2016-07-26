<?php
/**
 * 基于DB的数据模型说明
 * @program    T_PROGRAM
 * @author     T_AUTHOR
 * @devtime    T_DEVTIME
 */
namespace T_NAMESPACE;
use H2O\db\Command;
class T_CLASS extends Command
{
    /**
     * @var string 表名
     */
    private $_tbname = 'T_DB_TABLENAME';
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 保存数据
     * @param $data
     * @return mixed
     */
    public function add($data)
    {
        return $this->insert($this->_tbname,$data)
                    ->execute();
    }
    /**
     * 修改数据
     * @param $data
     * @param $fid
     * @param $id
     * @return mixed
     */
    public function edit($data,$fid,$id)
    {
        return $this->update($this->_tbname,$data,$fid.'=?')
                    ->bindValues([$id])
                    ->execute();
    }
    /**
     * 删除数据
     * @param $fid
     * @param $id
     * @return bool
     */
    public function delete($fid,$id)
    {
        return $this->setSql('DELETE FROM '.$this->_tbname.' WHERE '.$fid.'=?')
                    ->bindValues([$id])
                    ->execute();
    }
    /**
     * 获取单条数据
     * @param $fid
     * @param $id
     * @return bool
     */
    public function getRow($fid,$id)
    {
        return $this->setSql('SELECT * FROM '.$this->_tbname.' WHERE '.$fid.'=?')
                    ->bindValues([$id])
                    ->fetch();
    }
    /**
     * 获取全部数据
     * @return mixed
     */
    public function getAll()
    {
        return $this->setSql('SELECT * FROM '.$this->_tbname)
                    ->fetchAll();
    }
}
?>
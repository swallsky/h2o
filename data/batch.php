<?php
/**
 * 大数据批处理，数据迭代器
 * @category   H2O
 * @package    data
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\data;
use H2O;
/**
 * 大数据量时，为减少内存开销，采用迭代器来完成批量处理任务
 *例如
 class Test
 {
 	public function fetchBatch($i)
 	{
 		return [];
 	}
 }
 $o = new Batch(new Test());
 foreach($o as $k=>$v){
 	//ToDo
 }
 */
class Batch implements \Iterator
{
	/**
	 * @var object 迭代的数据对象
	 */
	private $_eachObj;
    /**
     * @var integer 批处理次数 默认从1开始
     */
    private $_batchnum = 1;
    /**
     * @var array 当前批的数据
     */
    private $_batch;
    /**
     * @var mixed 当前迭代的值
     */
    private $_value;
    /**
     * @var string|integer 当前迭代的key值
     */
    private $_key;
    /**
     * 构造函数
     * @param object $obj 需要处理的对象
     */
    public function __construct($obj)
    {
    	$this->_eachObj = $obj;
    }
    /**
     * 析构函数
     */
    public function __destruct()
    {
        //确保关闭游标
        $this->reset();
    }
    /**
     * 清理现有的，为下次迭代重置条件
     */
    public function reset()
    {
        $this->_batch = null;
        $this->_value = null;
        $this->_key = null;
    }

    /**
     * 移到下一批首元素
     * Iterator接口方法
     */
    public function rewind()
    {
        $this->reset();
        $this->next();
    }

    /**
     * 下移一个元素
     * Iterator接口方法
     */
    public function next()
    {
        if ($this->_batch === null || next($this->_batch) === false) {
            $this->_batch = call_user_func([$this->_eachObj,'fetchBatch'],$this->_batchnum);
            reset($this->_batch);
            $this->_batchnum++;
        }
        $this->_value = current($this->_batch);
        $this->_key = key($this->_batch);
    }
    /**
     * Iterator接口方法
     * @return integer 返回当前元素的键值
     */
    public function key()
    {
        return $this->_key;
    }

    /**
     * Iterator接口方法
     * @return mixed 返回当前元素值
     */
    public function current()
    {
        return $this->_value;
    }
    /**
     * Iterator接口方法
     * @return boolean 判定是否还有后续元素, 如果有, 返回true
     */
    public function valid()
    {
        return !empty($this->_batch);
    }
}

<?php
/**
 * 创建分表的全局唯一ID管理缓存表及相应的自定义函数
 * @category   H2O
 * @package    console
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\console;
use H2O\db\TableUniqid;
class Dtguid
{
    /**
     * @var array 配置信息
     */
    private $_dblist = [];
	/**
	 * 初始化
	 */
	public function __construct()
	{
		$this->_dblist = \H2O::getAppConfigs('db');//获取所有的DB配置
		if(empty($this->_dblist)){
			throw new \Exception("Config set error: lost db param!");
		}
	}
	/**
	 * 多库单库操作
	 * @param string $tag 操作类型
	 */
	private function _unitqueue($tag)
	{
	    $oreq = \H2O::getContainer('request');
	    $parms = $oreq->getParams();
	    if(isset($parms['name']) && !empty($parms['name'])){
	        $obj = new TableUniqid($parms['name']);
	        $obj->{$tag}();
	    }else{
	        foreach ($this->_dblist as $k=>$v){
	            $obj = new TableUniqid($k);
	            $obj->{$tag}();
	        }
	    }
	}
	/**
	 * 创建迁移
	 */
	public function actCreate()
	{
	    $this->_unitqueue('create');
		echo 'Database table GUID to create success!';
	}
	/**
	 * 创建迁移
	 */
	public function actClear()
	{
	    $this->_unitqueue('clear');
	    echo 'Database table GUID to create success!';
	}
	/**
	 * 删除GUID
	 */
	public function actDelete()
	{
	    $this->_unitqueue('delete');
		echo 'Database table GUID to delete success!';
	}
}
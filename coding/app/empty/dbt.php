<?php
/**
 * 基于DB的分表模型模块说明
 * @program    T_PROGRAM
 * @author     T_AUTHOR
 * @devtime    T_DEVTIME
 */
namespace T_NAMESPACE;
use H2O\db\TableStrategy;
class T_CLASS extends TableStrategy
{
    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 表名后缀动态部分接口
     * @return bool|string
     */
    public function TableExt()
    {
        //return date('ym');
        //TODO
    }
    /**
     * 表结构信息
     */
    public function Structure()
    {
        return [
            //TODO
        ];
    }
}
?>
<?php
/**
 * 基于foolz/sphinxql-query-builder的全文检索
 * @category   H2O
 * @package    SphinxQL
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\db;

class SphinxQL extends \Foolz\SphinxQL\SphinxQL
{
    /**
     * 初始化sphinx服务
     * @param string $tag 配置标识 默认为sphinx
     */
    public function __construct($tag = 'sphinx')
    {
        $cnfp = \H2O::getAppConfigs('sphinx');
        $this->connection = new \Foolz\SphinxQL\Connection();
        if(isset($cnfp[$tag])){
            $conf = $cnfp[$tag];
            $this->connection->setParam(['host' => $conf['host'], 'port' => $conf['port']]);//设置连接参数
        }
    }
}
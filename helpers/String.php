<?php
/**
 * 字符串处理相关的函数
 * @category   H2O
 * @package    helpers
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\helpers;
class String
{
    /**
     * 去掉UTF8 BOM头
     * Remove UTF8 Bom
     *
     * @param  string    $string
     * @access public
     * @return string
     */
    public static function removeUTF8Bom($string)
    {
        if(substr($string, 0, 3) == pack('CCC', 239, 187, 191)) return substr($string, 3);
        return $string;
    }
}
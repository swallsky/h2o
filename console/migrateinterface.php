<?php
/**
 * 数据迁移程序接口
 * @category   H2O
 * @package    console
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\console;
interface MigrateInterface
{
	/**
	 * @return 返回SQL操作对象
	 */
	public function getDdCommand();
	/**
	 * 更新操作
	 */
	public function up();
	/**
	 * 恢复更新操作
	 */
	public function restore();
}
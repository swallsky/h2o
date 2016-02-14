<?php
/**
 * 构建数据结构
 * @category   H2O
 * @package    db
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\db;
class Builder
{
	/**
	 * 创建新表
	 *
	 * The columns in the new  table should be specified as name-definition pairs (e.g. 'name' => 'string'),
	 * where name stands for a column name which will be properly quoted by the method, and definition
	 * stands for the column type which can contain an abstract DB type.
	 * The [[getColumnType()]] method will be invoked to convert any abstract type into a physical one.
	 *
	 * If a column is specified with definition only (e.g. 'PRIMARY KEY (name, type)'), it will be directly
	 * inserted into the generated SQL.
	 *
	 * For example,
	 *
	 * ~~~
	 * $sql = $queryBuilder->createTable('user', [
	 *  'id' => 'pk',
	 *  'name' => 'string',
	 *  'age' => 'integer',
	 * ]);
	 * ~~~
	 *
	 * @param string $table the name of the table to be created. The name will be properly quoted by the method.
	 * @param array $columns the columns (name => definition) in the new table.
	 * @param string $options additional SQL fragment that will be appended to the generated SQL.
	 * @return string the SQL statement for creating a new DB table.
	 */
	public function createTable($table, $columns, $options = null)
	{
		$cols = [];
		foreach ($columns as $name => $type) {
			if (is_string($name)) {
				$cols[] = "\t" . $this->db->quoteColumnName($name) . ' ' . $this->getColumnType($type);
			} else {
				$cols[] = "\t" . $type;
			}
		}
		$sql = "CREATE TABLE " . $this->db->quoteTableName($table) . " (\n" . implode(",\n", $cols) . "\n)";
	
		return $options === null ? $sql : $sql . ' ' . $options;
	}
}
<?php
/**
 * 所有模型层的基类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
use H2O;
use IteratorAggregate,ArrayAccess,ArrayIterator;
use ReflectionClass,ReflectionProperty;
class Model  implements IteratorAggregate,ArrayAccess
{
	/**
	 * 返回表单名称
	 * @return string the form name of this model class.
	 */
	public function formName()
	{
		$reflector = new ReflectionClass($this);
		return $reflector->getShortName();
	}
	/**
	 * 返回属性名称的列表
	 * 默认情况下,这个方法返回所有公共类的非静态属性
	 * 可以重写这个方法来改默认情况
	 * @return array 属性列表
	 */
	public function attributes()
	{
		$class = new ReflectionClass($this);
		$names = [];
		foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
			if (!$property->isStatic()) {
				$names[] = $property->getName();
			}
		}
		return $names;
	}
	/**
	 *  返回所有字段标签列表
	 * @return array attribute labels
	 */
	public function attributeLabels()
	{
		return [];
	}
	/**
	 * 返回属性值
	 * @param array $names 属性名称
	 * 默认读取当前类属性列表
	 * @param array $except 排除的属性名
	 * @return array attribute values (name => value).
	 */
	public function getAttributes($names = null, $except = [])
	{
		$values = [];
		if ($names === null) {
			$names = $this->attributes();
		}
		foreach ($names as $name) {
			$values[$name] = $this->$name;
		}
		foreach ($except as $name) {
			unset($values[$name]);
		}
		return $values;
	}
	/**
	 * 设置字段值
	 * @param array $values attribute values (name => value) to be assigned to the model.
	 * @param array $hpcfg HTMLPurifier配置参数
	 */
	public function setAttributes($values,$hpcfg = [])
	{
		if (is_array($values)) {
			$attributes = $this->attributes();
			foreach ($values as $name => $value) {
				$this->$name = H2O\helpers\HTMLPurifier::filter($value,$hpcfg);
			}
		}
	}
	/**
	 * 加载数据
	 * @param array $data the data array. 数据
	 * @param string $formName 表单名称
	 * @param array $hpcfg HTMLPurifier配置参数
	 * @return boolean 是否加载成功
	 */
	public function load($data, $formName = null,$hpcfg = [])
	{
		$scope = $formName === null ? $this->formName() : $formName;
		if ($scope === '' && !empty($data)) {
			$this->setAttributes($data);
			return true;
		} elseif (isset($data[$scope])) {
			$this->setAttributes($data[$scope],$hpcfg);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 返回遍历所有属性
	 * @return ArrayIterator 迭代器遍历列表中的项目
	 */
	public function getIterator()
	{
		$attributes = $this->getAttributes();
		return new ArrayIterator($attributes);
	}
	
	/**
	 * 接口函数 判断变量是否存在
	 * @param mixed $offset 检查属性
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return $this->$offset !== null;
	}
	
	/**
	 * 接口函数 获取变量
	 * @param mixed $offset 属性名称
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->$offset;
	}
	
	/**
	 * 接口函数 设置变量
	 * @param integer $offset 属性名称
	 * @param mixed $item
	 */
	public function offsetSet($offset, $item)
	{
		$this->$offset = $item;
	}
	
	/**
	 * 接口函数 unset
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		$this->$offset = null;
	}
}

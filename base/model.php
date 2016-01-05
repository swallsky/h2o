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
	 * @var string 当前场景，默认为default
	 */
	private $_scenario = 'default';
	/**
	 * This method is invoked when an unsafe attribute is being massively assigned.
	 * The default implementation will log a warning message if YII_DEBUG is on.
	 * It does nothing otherwise.
	 * @param string $name the unsafe attribute name
	 * @param mixed $value the attribute value
	 */
	public function onUnsafeAttribute($name, $value)
	{
		Exception('Model::onUnsafeAttribute',"Failed to set unsafe attribute '$name' in '" . get_class($this) . "'.", __METHOD__);
	}
	
	/**
	 * Returns the scenario that this model is used in.
	 *
	 * Scenario affects how validation is performed and which attributes can
	 * be massively assigned.
	 *
	 * @return string the scenario that this model is in. Defaults to [[SCENARIO_DEFAULT]].
	 */
	public function getScenario()
	{
		return $this->_scenario;
	}
	
	/**
	 * Sets the scenario for the model.
	 * Note that this method does not check if the scenario exists or not.
	 * The method [[validate()]] will perform this check.
	 * @param string $value the scenario that this model is in.
	 */
	public function setScenario($value)
	{
		$this->_scenario = $value;
	}
	/**
	 * Returns the attribute names that are safe to be massively assigned in the current scenario.
	 * @return string[] safe attribute names
	 */
	public function safeAttributes()
	{
		$scenario = $this->getScenario();
		$scenarios = $this->scenarios();
		if (!isset($scenarios[$scenario])) {
			return [];
		}
		$attributes = [];
		foreach ($scenarios[$scenario] as $attribute) {
			if ($attribute[0] !== '!') {
				$attributes[] = $attribute;
			}
		}
	
		return $attributes;
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
	 * 批量设置属性信息
	 * @param array $values attribute values (name => value)  例如[['name'=>'test','title'=>'content']]
	 * @param boolean $safeOnly 是否开启安全验证模式
	 */
	public function setAttributes($values, $safeOnly = true)
	{
		if (is_array($values)){
			$attributes = array_flip($safeOnly ? $this->safeAttributes() : $this->attributes());
			foreach ($values as $name => $value) {
				if (isset($attributes[$name])) {
					$this->$name = $value;
				} elseif ($safeOnly) {
					$this->onUnsafeAttribute($name, $value);
				}
			}
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

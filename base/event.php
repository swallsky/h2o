<?php
/**
 * 事件类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
class Event
{
    /**
     * @var string 运行时的事件名
     */
    public $name;
    /**
     * @var boolean 运行时的句柄状态
     */
    public $handled = false;
    /**
     * @var mixed 事件数据
     */
    public $data;
	/**
	 * @var array 多事件队列
	 */
    private static $_events = [];


    /**
     * 添加事件
     * @param string $name 事件名
     * @param callable $handler 事件句柄
     * @param mixed $data 事件数据
     * @param boolean $append 是否将数据添加到该事件末尾，默认为true,否则将事件添加到之前
     */
    public static function on($name, $handler, $data = null, $append = true)
    {
        if ($append || empty(self::$_events[$name])) {
            self::$_events[$name][] = [$handler, $data];
        } else {
            array_unshift(self::$_events[$name], [$handler, $data]);
        }
    }

    /**
     * 解绑事件
     * @param string $name 事件名
     * @param callable $handler 事件句柄
     * @return boolean 返回解绑事件是否成功
     */
    public static function off($name, $handler = null)
    {
        if (empty(self::$_events[$name])) {
            return false;
        }
        if ($handler === null) {
            unset(self::$_events[$name]);
            return true;
        } else {
            $removed = false;
            foreach (self::$_events[$name] as $i => $event) {
                if ($event[0] === $handler) {
                    unset(self::$_events[$name][$i]);
                    $removed = true;
                }
            }
            if ($removed) {
                self::$_events[$name] = array_values(self::$_events[$name]);
            }

            return $removed;
        }
    }

    /**
     * 返回事件是否存在
     * @param string $name 事件名
     * @return boolean 返回事件是否存在
     */
    public static function hasHandlers($name)
    {
        if (empty(self::$_events[$name])) {
            return false;
        }else{
        	return true;
        }
    }

    /**
     * 触发事件
     * @param string $name 事件名
     * @param Event $event 事件参数. 默认为当前运的类
     */
    public static function trigger($name, $event = null)
    {
        if (empty(self::$_events[$name])) {
            return;
        }
        if ($event === null) {
            $event = new static;
        }
        $event->handled = false;
        $event->name = $name;
        if (!empty(self::$_events[$name])) {
        	foreach(self::$_events[$name] as $handler) {
            	$event->data = $handler[1];
                call_user_func($handler[0], $event); //事件如果成功需要返回 handled为true,否则下一事件直接停止
                if ($event->handled) {
                	return;
                }
             }
        }
    }
}
?>
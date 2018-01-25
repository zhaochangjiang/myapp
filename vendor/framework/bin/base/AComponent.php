<?php

namespace framework\bin\base;

use \RuntimeException;

/**
 * 容器
 *
 * @author heypigg
 */
class AComponent extends AppBase
{

    private $_e;
    private $_m;

//获取部件属性、事件和行为的magic method
    public function __get($name)
    {

        $getter = 'get' . $name;
        //是否存在属性的get方法
        if (method_exists($this, $getter))
            return $this->$getter ();
        //以on开头，获取事件处理句柄
        else if (strncasecmp($name, 'on', 2) === 0 && method_exists($this, $name)) {
            // 事件名小写
            $name = strtolower($name);
            // 如果_e[$name] 不存在，返回一个空的CList事件句柄队列对象
            if (!isset ($this->_e[$name]))
                $this->_e[$name] = new CList;
            // 返回_e[$name]里存放的句柄队列对象
            return $this->_e[$name];
        } // _m[$name] 里存放着行为对象则返回
        else if (isset ($this->_m[$name]))
            return $this->_m[$name];
        else {
            throw new Exception ('Property ' . get_class($this) . '.' . $name . ' is not defined.');
        }
    }

    /**
     * PHP magic method
     * 设置组件的属性和事件
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        //是否存在属性的set方法
        if (method_exists($this, $setter))
            $this->$setter ($value);
        //name以on开头，这是事件处理句柄
        else if (strncasecmp($name, 'on', 2) === 0 && method_exists($this, $name)) {
            // 事件名小写
            $name = strtolower($name);
            // _e[$name] 不存在则创建一个CList对象
            if (!isset ($this->_e[$name]))
                $this->_e[$name] = new CList;
            // 添加事件处理句柄
            $this->_e[$name]->add($value);
        } // 属性没有set方法，只有get方法，为只读属性，抛出异常
        else if (method_exists($this, 'get' . $name)) {
            throw new Exception ('Property ' . get_class($this) . '.' . $name . ' is read only');
        } else {
            throw new Exception ('Property ' . get_class($this) . '.' . $name . '  is not defined');
        }
    }

    /**
     * PHP magic method
     * 为isset()函数提供是否存在属性和事件处理句柄的判断
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter))
            return $this->$getter () !== null;
        else if (strncasecmp($name, 'on', 2) === 0 && method_exists($this, $name)) {
            $name = strtolower($name);
            return isset ($this->_e[$name]) && $this->_e[$name]->getCount();
        } else
            return false;
    }

    /**
     * PHP magic method
     * 设置属性值为空或删除事件名字对应的处理句柄
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter))
            $this->$setter (null);
        else if (strncasecmp($name, 'on', 2) === 0 && method_exists($this, $name))
            unset ($this->_e[strtolower($name)]);
        else if (method_exists($this, 'get' . $name)) {
            throw new Exception ('Property ' . get_class($this) . '.' . $name . '  is read only.');
        }
    }

    /**
     * PHP magic method
     * CComponent未定义的类方法，寻 找行为类里的同名方法， 实现行为方法的调用
     */
    public function __call($name, $parameters)
    {
        // 行为类存放的$_m数组不空
        if ($this->_m !== null) {
            // 循环取出$_m数组里存放的行为类
            foreach ($this->_m as $object) {
                // 行为类对象有效，并且方法存在，调用之
                if ($object->enabled && method_exists($object, $name))
                    return call_user_func_array(array(
                        $object,
                        $name), $parameters);
            }
        }
        throw new Exception (get_class($this) . ' does not have a method named ' . $name . '.');
    }

}

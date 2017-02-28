<?php
namespace framework\lib;
/**
 * 带有前缀的redis，主要是在不动老代码的基础上为了适应汽车票, 带上前缀
 *
 * @author heypigg
 */
class RedisCacheClass
{

    public $redis;
    public $host;
    public $prefix;

    public function __construct($host = '', $port = '', $prefix = '')
    {
        $this->redis  = new Redis();
        $this->host   = $host;
        $this->prefix = $prefix;
        $this->redis->connect($host, $port);
    }

    public function getInstance()
    {
        return $this->redis;
    }

    public function getConfig()
    {
        $data           = array();
        $info           = $this->redis->info();
        $data['host']   = $this->host;
        $data['memory'] = $info['used_memory_human'];
        return $data;
    }

    //检查给定key是否存在
    public function exists($key)
    {
        $key = $this->prefix . $key;
        return $this->redis->exists($key);
    }

    //获取有关指定键的值
    public function get($key, $encode = false)
    {
        $key   = $this->prefix . $key;
        $value = $this->redis->get($key);
        if ($encode)
        {
            return json_decode($value, true);
        }
        else
        {
            return $value;
        }
    }

//设置key和value的值
    public function set($key, $value, $encode = false)
    {
        $key = $this->prefix . $key;
        if ($encode)
        {
            $value = json_encode($value);
        }
        return $this->redis->set($key, $value);
    }

    //删除指定的键
    public function del($key)
    {
        $key = $this->prefix . $key;
        return $this->redis->delete($key);
    }

    //设定一个key的活动时间（s）
    public function expire($key, $secord)
    {
        $key = $this->prefix . $key;
        return $this->redis->setTimeout($key, $secord);
    }

    //同时给多个key赋值,encode 无效 兼容以前的代码
    public function mset($data, $encode = false)
    {
        return $this->redis->mset($data);
    }

    //返回名称为h的hash中元素个数
    public function hlen($key)
    {
        $key = $this->prefix . $key;
        return $this->redis->hLen($key);
    }

    //删除名称为$key的hash中键为$fields的域
    public function hdel($key, $fields)
    {
        $key = $this->prefix . $key;
        return $this->redis->hDel($key, $fields);
    }

    /**
     * 通过key匹配缓存中存在的key的列表
     * Enter description here ...
     * @param string $key    // $key = "*user*";
     */
    public function keys($key)
    {
        $key = $this->prefix . $key;
        return $this->redis->keys($key);
    }

    //名称为h的$key中是否存在键名字为$field的域
    public function hexists($key, $field)
    {
        $key = $this->prefix . $key;
        return $this->redis->hExists($key, $field);
    }

    //返回名称为$key的hash中所有键
    public function hkeys($key)
    {
        $key = $this->prefix . $key;
        return $this->redis->hKeys($key);
    }

    //向名称为$key的hash中添加元素$field—>$value
    public function hset($key, $field, $value, $encode = false)
    {
        $key = $this->prefix . $key;
        if ($encode)
        {

            $value = json_encode($value);
        }
        return $this->redis->hSet($key, $field, $value);
    }

    //增加一个元素,但不能重复
    public function hsetnx($key, $field, $value, $encode = false)
    {
        $key = $this->prefix . $key;
        if ($encode)
        {
            $value = json_encode($value);
        }
        return $this->redis->hSetNx($key, $field, $value);
    }

    //返回名称为$key的hash中$field对应的value
    public function hget($key, $field, $encode = false)
    {
        $key   = $this->prefix . $key;
        $value = $this->redis->hGet($key, $field);
        if ($encode)
        {
            $value = json_decode($value, true);
        }
        return $value;
    }

    //返回名称为$key的hash中$fields对应的value
    public function hmget($key, $fields, $encode = false)
    {
        $key = $this->prefix . $key;
        return $this->redis->hmGet($key, $fields);
    }

    //向名称为key的hash中批量添加元素
    public function hmset($key, $fields, $encode = false)
    {
        $key = $this->prefix . $key;
        return $this->redis->hMset($key, $fields);
    }

    //返回名称为$key的hash中所有的键（field）及其对应的value
    public function hgetall($key, $encode = false)
    {
        $key = $this->prefix . $key;
        return $this->redis->hGetAll($key);
    }

    //将名称为$key的hash中$field的value增加$offset
    public function hincrby($key, $field, $offset = 1)
    {
        $key = $this->prefix . $key;
        return $this->redis->hIncrBy($key, $field, $offset);
    }

    //带生存时间的写入值
    public function setex($key, $value, $second = 3600, $encode = false)
    {
        $key = $this->prefix . $key;
        return $this->redis->setex($key, $second, $value);
    }

    //判断是否重复的，写入值
    public function setnx($key, $value, $encode = false)
    {
        $key = $this->prefix . $key;
        return $this->redis->setnx($key, $value);
    }

    //返回名称为key的list中start至end之间的元素（end为 -1 ，返回所有）
    public function lrange($key, $offset = 0, $limit = -1, $encode = true)
    {
        $key  = $this->prefix . $key;
        $data = $this->redis->lRange($key, $offset, $limit);
        $list = array();
        foreach ($data as $key => $val)
        {
            if ($encode)
            {
                $list[$key] = json_decode($val, true);
            }
            else
            {
                $list[$key] = $val;
            }
        }
        return $list;
    }

    //返回名称为key的list中index位置的元素
    public function lindex($key, $index = 0, $encode = true)
    {
        $key  = $this->prefix . $key;
        $data = $this->redis->lGet($key, $index);
        if ($encode)
        {
            $data = json_decode($data, true);
        }
        return $data;
    }

    // $flag BEFORE|AFTER 插入之前还是之后
    public function linsert($key, $pivot, $value, $flag = "BEFORE")
    {
        $key = $this->prefix . $key;
        return $this->redis->lInsert($key, Redis::$flag, $pivot, $value);
    }

    //在名称为key的list左边（头）添加一个值为value的元素
    public function lpush($key, $value, $encode = true)
    {
        $key = $this->prefix . $key;
        if ($encode)
        {
            $value = json_encode($value);
        }
        return $this->redis->lPush($key, $value);
    }

    //输出名称为key的list左起的第一个元素，删除该元素
    public function lpop($key, $encode = true)
    {
        $key  = $this->prefix . $key;
        $data = $this->redis->lPop($key);
        if ($encode)
        {
            $data = json_decode($data, true);
        }
        return $data;
    }

    //在名称为key的list右边（尾）添加一个值为value的元素
    public function rpush($key, $value, $encode = true)
    {
        $key = $this->prefix . $key;
        if ($encode)
        {
            $value = json_encode($value);
        }
        return $this->redis->rPush($key, $value);
    }

    //输出名称为key的list右起的第一个元素，删除该元素
    public function rpop($key, $encode = true)
    {
        $key  = $this->prefix . $key;
        $data = $this->redis->rPop($key);
        if ($encode)
        {
            $data = json_decode($data, true);
        }
        return $data;
    }

    //删除count个名称为key的list中值为value元素，count为0，删除所有值为value的元素，count<0从头到尾删除count个值为value的元素
    public function lrem($key, $value, $count = 0)
    {
        $key = $this->prefix . $key;
        return $this->redis->lRem($key, $value, $count);
    }

    //截取名称为key的list，保留start至end之间的元素
    public function ltrim($key, $offset = 0, $stop = -1)
    {
        $key = $this->prefix . $key;
        return $this->redis->lTrim($key, $offset, $stop);
    }

    //返回名称为key的list有多少个元素
    public function llen($key)
    {
        $key = $this->prefix.$key;
        return $this->redis->lSize($key);
    }

    //给名称为key的list中index位置的元素赋值为value
    public function lset($key, $index, $value)
    {
        $key = $this->prefix.$key;
        return $this->redis->lSet($key, $index, $value);
    }

    //向名称为key的set中添加元素value,如果value存在，不写入 return false
    public function sadd($key, $value, $encode = false)
    {
        $key = $this->prefix.$key;
        if ($encode)
        {
            $value = json_encode($value);
        }
        return $this->redis->sAdd($key, $value);
    }

    //回名称为key的set的元素个数
    public function scard($key)
    {
        $key = $this->prefix.$key;
        return $this->redis->sCard($key);
    }

    //求差集
    public function sdiff($key)
    {
        $key = $this->prefix.$key;
        return $this->redis->sDiff($key);
    }

    //求差集并将差集保存到output的集合
    public function sdiffstore($dest, $keys)
    {
        
        return $this->redis->sDiffStore($dest, $keys);
    }

    // 随机返回并删除名称为key的set中一个元素
    public function spop($key, $encode = false)
    {
        $key = $this->prefix.$key;
        return $this->redis->sPop($key);
    }

    //名称为key的集合中查找是否有value元素，有ture 没有false
    public function sismember($key, $value, $encode = false)
    {
        $key = $this->prefix.$key;
        return $this->redis->sIsMember($key, $value);
    }

    //返回名称为key的set的所有元素
    public function smembers($key, $encode = false)
    {
        $key = $this->prefix.$key;
        return $this->redis->sMembers($key);
    }

    //返回key的类型值
    public function type($key)
    {
        $key = $this->prefix.$key;
        $num = $this->redis->type($key);
        switch ($num)
        {
            case 1:
                $str = 'string';
                break;
            case 2:
                $str = 'set';
                break;
            case 3:
                $str = 'list';
                break;
            case 4:
                $str = 'zset';
                break;
            case 5:
                $str = 'hash';
                break;
            default :
                $str = 'other';
                break;
        }

        return $str;
    }

    /**
     * 随机取一个key
     * Enter description here ...
     */
    public function randomkey()
    {
        return $this->redis->randomkey();
    }

    //排序， 分页等
    /*
     * array(
      'by' => 'pattern', //匹配模式
      'limit' => array(0, 1),
      'get' => 'pattern'
      'sort' => 'asc' or 'desc',
      'alpha' => TRUE,
      'store' => 'external-key'
      )
     */
    public function sort($key, $params = array(), $encode = true)
    {
        $key = $this->prefix.$key;
        return $this->redis->sort($key, $params);
    }

    //称为key的string的值在后面加上value
    public function append($key, $value)
    {
        $key = $this->prefix.$key;
        return $this->redis->append($key, $value);
    }

    //返回原来key中的值，并将value写入到key中
    public function getset($key, $value, $encode = false)
    {
        $key = $this->prefix.$key;
        return $this->redis->getSet($key, $value);
    }

    //返回名称为key的string中start到end之间的字符串
    public function getrange($key, $start = 0, $end = 0, $encode = false)
    {
        $key = $this->prefix.$key;
        return $this->redis->getRange($key, $start, $end);
    }

    //选择一个数据库
    public function select($index)
    {
        return $this->redis->select($index);
    }

    // 查看连接状态
    public function ping()
    {
        return $this->redis->ping();
    }

}

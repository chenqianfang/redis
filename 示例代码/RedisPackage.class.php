<?php
/**
 * created by Aron
 * 2017.9.22
 * redis单例模式
 * 目前只添加了get 和set 方法后续功能可以按需添加
 */
class RedisPackage
{
    private static $handler = null;
    private static $_instance = null;
    private static $options = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'select' => 0,
        'timeout' => 0,
        'expire' => 0,
        'persistent' => true,
        'prefix' => '',
    ];

    private function __construct($options = [])
    {
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: redis');      //判断是否有扩展
        }
        if (!empty($options)) {
            self::$options = array_merge(self::$options, $options);
        }
        $func = self::$options['persistent'] ? 'pconnect' : 'connect';     //长链接
        self::$handler = new \Redis;
        self::$handler->$func(self::$options['host'], self::$options['port'], self::$options['timeout']);

        if ('' != self::$options['password']) {
            self::$handler->auth(self::$options['password']);
        }

        if (0 != self::$options['select']) {
            self::$handler->select(self::$options['select']);
        }
    }


    /**
     * @return RedisPackage|null 对象
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 禁止外部克隆
     */
    public function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    /**
     * 68      * 写入缓存
     * 69      * @param string $key 键名
     * 70      * @param string $value 键值
     * 71      * @param int $exprie 过期时间 0:永不过期
     * 72      * @return bool
     * 73      */


    public static function set($key, $value, $exprie = 0)
    {
        if(is_object($value)||is_array($value)){
            $value = serialize($value);
        }
        if ($exprie == 0) {
            $set = self::$handler->set($key, $value);
        } else {
            $set = self::$handler->setex($key, $exprie, $value);
        }
        return $set;
    }

    /**
     * 读取缓存
     * @param string $key 键值
     * @return mixed
     */
    public static function get($key){
        $value = self::$handler -> get($key);
        $value_serl = @unserialize($value);
        if(is_object($value_serl)||is_array($value_serl)){
            return $value_serl;
        }
        return $value;
    }

    /**
     * 批量删除缓存
     * @param array $key 键名
     * @return mixed
     */
    public static function delete($key){
        if(is_array($key)){
            return self::$handler->delete($key);
        }else{
            return 0;
        }
    }

    /**
     * 获取值长度
     * @param string $key
     * @return int
     */
    public static function lLen($key)
    {
        return self::$handler->lLen($key);
    }

    /**
     * 将一个或多个值插入到列表头部
     * @param $key
     * @param $value
     * @return int
     */
    public static function LPush($key, $value)
    {
        return self::$handler->lPush($key, $value);
    }

    /**
     * 移出并获取列表的第一个元素
     * @param string $key
     * @return string
     */
    public static function lPop($key)
    {
        return self::$handler->lPop($key);
    }

    /**
     * 查询键名是否存在
     * @param string $key
     * @return array
     */
    public static function keys($key){
        return self::$handler->keys($key);
    }

}
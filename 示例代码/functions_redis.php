<?php
/**
 * Created by Array.
 * Date: 2017/12/27
 * Time: 18:26
 * 存放所有调用 Redis 的函数
 */

/**
 * 读取 redis 缓存
 * @param $key string 键名
 * @param $prefix string 前缀
 * @return string/bool
 */
function get_redis_key_value($key,$prefix = ''){
    require_once DIR_WS_CLASSES . 'RedisPackage.class.php';
    $redis = RedisPackage::getInstance();
    $key = $prefix.':'.md5($key);
    $result = $redis->get($key);
    if($result){
        return $result;
    }else{
        return false;
    }
}

/**
 * 设置 redis 缓存
 * 存储类型：string
 * @param $key string 键名
 * @param $value string 键值
 * @param $time int 生存时间(秒)，0为永不过期
 * @param $prefix string 前缀(区分缓存类型)
 * @return string/bool
 */
function set_redis_key_value($key,$value,$time = 0,$prefix = ''){
    require_once DIR_WS_CLASSES . 'RedisPackage.class.php';
    $redis = RedisPackage::getInstance();
    $key = md5($key);
    $key = $prefix.':'.$key;
    $result = $redis->set($key,$value,$time);
    if($result){
        return $result;
    }else{
        return false;
    }
}

/**
 * 清理 redis 某一前缀的所有缓存
 * @param  $prefix string 前缀名
 * @return int/false
 */
function remove_redis_by_prefix($prefix){
    if(empty($prefix)){
        return false;
    }
    require_once DIR_WS_CLASSES . 'RedisPackage.class.php';
    $redis = RedisPackage::getInstance();
    $keysArr = $redis->keys($prefix.'*');
    if(count($keysArr)){
        return $redis->delete($keysArr);
    }else{
        return 0;
    }

}
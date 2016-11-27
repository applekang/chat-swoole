<?php
namespace Libs\Classes;


class Storage{


    const IP       = '127.0.0.1';
    const PORT     = 6379;
    const TIMEOUT  = 3;
    const DB       = 0;

    private static function redisServer()
    {
        static $redis;
        if (!$redis)
        {
            $redis = new \Redis();
            $redis->connect(self::IP, self::PORT, self::TIMEOUT);
            $redis->select(self::DB);
        }

        return $redis;
    }

    /**
     * 所有客户端的fd保存在set中
     * @param $fd
     * @param string $set
     * @return bool|int
     */
    public static function addFd($fd, $set = 'fdList')
    {
        if (!$fd) return false;
        return self::redisServer()->sadd($set, $fd);
    }

    /**
     * 删除客户端的fd
     * @param $fd
     * @param string $set
     * @return bool|int
     */
    public static function moveFd($fd, $set = 'fdList')
    {
        if (!$fd) return false;
        return self::redisServer()->sremove($set, $fd);
    }

    /**
     * 检查客户端的fd是否存在
     * @param $fd
     * @param string $set
     * @return bool|void
     */
    public static function existFd($fd, $set = 'fdList')
    {
        if (!$fd) return false;
        return self::redisServer()->scontains($set, $fd);
    }

    /**
     * 检查客户端的fd数量
     * @param string $set
     * @return mixed
     */
    public static function sizeFd($set = 'fdList')
    {
        return self::redisServer()->ssize($set);
    }

    /**
     * 获取所有的fd
     * @param string $set
     * @return array
     */
    public static function listFd($set = 'fdList')
    {
        return self::redisServer()->smembers($set);
    }
}

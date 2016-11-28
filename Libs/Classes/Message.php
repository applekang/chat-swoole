<?php
namespace Libs\Classes;

class Message{

    // protected static $connection = "mongodb://192.168.0.116:12345";
    protected static $connection = "mongodb://192.168.13.191:27017";

    protected static $db         = 'swoole';

    private static function mongoServer()
    {
        static $mongo;

        if (!$mongo) {
            $db = new \MongoClient(self::$connection);
            $mongo = $db->selectDB(self::$db);
        }

        return $mongo;
    }

    /**
     * 查找用户
     * @param $name
     * @return array|null
     */
    public static function getUser($filter)
    {
        $collection = self::mongoServer()->users;
        $user = $collection->findOne($filter);
        return $user;
    }

    /**
     * 添加用户
     * @param array $data
     * @return array|bool
     */
    public static function addUser(Array $data)
    {
        $collection = self::mongoServer()->users;
        $result = $collection->insert($data);
        return $result;
    }


    /**
     * 更新用户信息
     * @param array $update
     * @param array $where
     * @return bool
     */
    public static function updateUser(Array $update,Array $where)
    {

        $collection = self::mongoServer()->users;
        $result = $collection->update($where, ['$set'=>$update]);
        return $result;
    }

    /**
     * 添加信息到discuss_group_record
     */
    public static function addMsgtoGroup(Array $data)
    {
        $collection = self::mongoServer()->discuss_group_record;
        $result = $collection->insert($data);
        return $result;
    }

    /**
     * 获取所有的群聊消息
     */
    public static function getAllMsgFromGroup()
    {
        $collection = self::mongoServer()->discuss_group_record;
        $result = $collection->find();
        $msgs = [];
        foreach ($result as $v)
        {
            $tmp = [];
            $tmp['name']    = $v['name'];
            $tmp['avatar']  = $v['avatar'];
            $tmp['content'] = $v['content'];
            $tmp['time']    = $v['time'];
            $msgs[] = $tmp;
        }

        return $msgs;
    }
}

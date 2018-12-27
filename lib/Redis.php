<?php
namespace AntMan\Lib;

class Redis
{
    private static $redis;

    public static function getRedis($server = 'redis', $port = 6379)
    {
        if (static::$redis == null) {
            $redis = new \Redis();
            $redis->connect($server, $port);
            self::$redis = $redis;
        }
        return static::$redis;
    }

    public static function disconnect()
    {
        if (static::$redis != null) {
            static::$redis->disconnect();
            static::$redis = null;
        }
    }
}

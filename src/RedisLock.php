<?php

namespace Xiaobopang;

class RedisLock
{
    private $redis;
    /**
     * 初始化redis
     *
     * @param string $host
     * @param string $port
     * @param string $password
     * @param integer $index
     */
    public function __construct($host = '127.0.0.1', $port = '6379', $password = '', $index = 0)
    {
        $this->redis = new \Redis();
        try {
            $this->redis->pconnect($host, $port);
            $this->redis->auth($password);
            $this->redis->select($index);
        } catch (Exception $e) {
            throw new Exception('Redis connection failed', 400);
        }
    }
    /**
     * 阻塞锁
     * @param $lock_key
     * @param $lock_timeout 过期时间戳，毫秒
     */
    public function block($lock_key, &$lock_timeout)
    {
        $try_num = 45;
        $s_ms = 5;
        while ($try_num--) {
            if ($this->tryLock($lock_key, $lock_timeout)) {
                return true;
            } else {
                usleep(1000 * $s_ms); // $s_ms 毫秒
                $lock_timeout += $s_ms;
                $s_ms = ($s_ms >= 160 ? 160 : $s_ms * 2); // 指数避让
            }
        }
        return false;
    }
    /**
     * redis 模拟锁
     * @param $lock_key
     * @param float $lock_timeout 过期时间，单位毫秒
     * @return bool
     */
    public function tryLock($lock_key, $lock_timeout)
    {
        $lock_key = strval($lock_key);
        $lock_timeout = (float) $lock_timeout;
        if (!$lock_key || $lock_timeout <= 0) {
            return false;
        }
        $now = $this->getMilliSecond();
        $lock = $this->redis->setnx($lock_key, $lock_timeout);
        if ($lock || (($now > (float) $this->redis->get($lock_key)) && $now > (float) $this->redis->get_set($lock_key, $lock_timeout))) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 释放锁
     * @param $lock_key
     * @param float $lock_timeout try_lock设置的过期时间
     * @return bool
     */
    public function releaseLock($lock_key, $lock_timeout)
    {
        $lock_key = strval($lock_key);
        $lock_timeout = (float) $lock_timeout;
        if (!$lock_key || $lock_timeout <= 0) {
            return false;
        }
        $now = $this->getMilliSecond();
        if ($now < $lock_timeout) {
            $this->redis->delete($lock_key);
        }
    }
    /**
     * 获取毫秒时间戳
     */
    public function getMilliSecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float) sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }
}


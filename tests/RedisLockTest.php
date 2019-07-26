<?php

namespace Xiaobopang\Test;

use PHPUnit\Framework\TestCase;
use Xiaobopang\RedisLock;

class RedisLockTest extends TestCase
{
    public function testRedisLockCase()
    {
        /**
         * 初始化redis连接
         */
        $redis = new RedisLock("192.168.1.1", 16379, 'test123', 3);

        //加锁
        $this->assertTrue($redis->tryLock("test_lock", $redis->getMilliSecond()));

        //释放锁
        $this->assertTrue($redis->releaseLock("test_lock", $redis->getMilliSecond()));

        //阻塞锁
        $this->assertTrue($redis->block("test_lock", $redis->getMilliSecond()));

    }
}

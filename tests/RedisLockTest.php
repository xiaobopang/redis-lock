<?php

namespace Xiaobopang\Test;

use PHPUnit\Framework\TestCase;
use Xiaobopang\RedisLock;

class PinyinTest extends TestCase
{
    public function testPinyinCase()
    {
        /**
         * 初始化redis连接
         */
        $redis = new RedisLock("172.17.0.1", 16379, 'pang123', 3);

        //模拟锁
        $this->assertNull(tryLock("test_lock", $redis->getMilliSecond()));
    }
}

# php_redis_distributed_lock
PHP Redis分布式锁机制的简单实现


### 安全和可靠性保证

#### 在描述我们的设计之前，先提出三个属性，这三个属性是实现高效分布式锁的基础。

```
1、安全属性：互斥，不管任何时候，只有一个客户端能持有同一个锁。
2、效率属性A：不会死锁，最终一定会得到锁，就算一个持有锁的客户端宕掉或者发生网络分区。
3、效率属性B：容错，只要大多数Redis节点正常工作，客户端应该都能获取和释放锁。

```


### Redis命令介绍

```
使用Redis实现分布式锁，有两个重要函数需要介绍

SETNX命令（SET if Not eXists）
语法：
SETNX key value
功能：
当且仅当 key 不存在，将 key 的值设为 value ，并返回1；若给定的 key 已经存在，则 SETNX 不做任何动作，并返回0。

GETSET命令
语法：
GETSET key value
功能：
将给定 key 的值设为 value ，并返回 key 的旧值 (old value)，当 key 存在但不是字符串类型时，返回一个错误，当key不存在时，返回nil。

GET命令
语法：
GET key
功能：
返回 key 所关联的字符串值，如果 key 不存在那么返回特殊值 nil 。

DEL命令
语法：
DEL key [KEY …]
功能：
删除给定的一个或多个 key ,不存在的 key 会被忽略。

```

### 加锁实现

```
SETNX 可以直接加锁操作，比如说对某个关键词redislock加锁，客户端可以尝试

command : SETNX key seconds value

SETNX redislock 20 redislock

如果返回1，表示客户端已经获取锁，可以往下操作，操作完成后，通过 DEL foo.lock命令来释放锁。

如果返回0，说明 redislock 已经被其他客户端上锁，如果锁是非堵塞的，可以选择返回调用。如果是堵塞调用调用，就需要进入以下个重试循环，直至成功获得锁或者重试超时。理想是美好的，现实是残酷的。仅仅使用SETNX加锁带有竞争条件的，在某些特定的情况会造成死锁错误。

```
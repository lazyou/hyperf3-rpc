<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler::class,
    Hyperf\Command\Listener\FailToHandleListener::class,
    // 启动 mysql、redis、async queue 监控, Mysql 连接数、Redis 连接数和 Async Queue 默认没有开启，根据需要进行开启。下面以开启 DB 和 Redis 为例
    // 需要监控哪一个就注入监听者
    \Hyperf\Metric\Listener\DBPoolWatcher::class,
    \Hyperf\Metric\Listener\RedisPoolWatcher::class,
];

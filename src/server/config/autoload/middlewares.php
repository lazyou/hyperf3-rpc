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
    'http' => [
    ],
    // 采集信息还需要配置一下中间件才能启用采集功能
    'jsonrpc-http' => [
        \Hyperf\Tracer\Middleware\TraceMiddleware::class,
    ],
];

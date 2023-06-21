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
    'enable' => [
        // 开启服务发现
        'discovery' => true,
        // 开启服务注册
        'register' => true,
    ],
    // 服务消费者相关配置
    'consumers' => [],
    // 服务提供者相关配置
    'providers' => [],
    'drivers' => [
        // consul 配置
        'consul' => [
            // 服务中心地址
            'uri' => 'http://consul:8500',
            // consul 权限控制所需要的 token
            'token' => '',
            'check' => [
                // 服务注销时间，若 consul 服务 90 分钟内没有收到心跳检测，
                // 那么 consul 就会从服务中心中提出当前关联的所有服务
                'deregister_critical_service_after' => '90m',
                // 健康检查时间，1 秒 检查一次
                'interval' => '1s',
            ],
        ],
// 无用，暂时注释
//        'nacos' => [
//            // nacos server url like https://nacos.hyperf.io, Priority is higher than host:port
//            // 'url' => '',
//            // The nacos host info
//            'host' => '127.0.0.1',
//            'port' => 8848,
//            // The nacos account info
//            'username' => null,
//            'password' => null,
//            'guzzle' => [
//                'config' => null,
//            ],
//            'group_name' => 'api',
//            'namespace_id' => 'namespace_id',
//            'heartbeat' => 5,
//            'ephemeral' => false,
//        ],
    ],
];

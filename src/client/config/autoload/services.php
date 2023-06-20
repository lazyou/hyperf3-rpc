<?php
// config/autoload/services.php
return [
    'consumers' => [
        [
            // 对应消费者类的 $serviceName
            'name' => 'UserService',
            // 直接对指定的节点进行消费，通过下面的 nodes 参数来配置服务提供者的节点信息
            'nodes' => [
                ['host' => 'server', 'port' => 9600],
            ],
        ]
    ],
];

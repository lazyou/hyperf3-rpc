<?php

declare(strict_types=1);

use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

if (! function_exists('container')) {
    /**
     * 容器实例.
     * @return ContainerInterface
     */
    function container()
    {
        return ApplicationContext::getContainer();
    }
}

if (! function_exists('logger')) {
    /**
     * 向日志文件记录日志.
     * @return LoggerInterface
     */
    function logger()
    {
        return container()->get(LoggerFactory::class)->make();
    }
}

if (! function_exists('jsonFormat')) {
    /**
     * json 格式化打印，常用于 model 的 collection.
     */
    function jsonFormat($val)
    {
        echo PHP_EOL;
        echo json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo PHP_EOL;
    }
}

if (! function_exists('ddJsonFormat')) {
    /**
     * json 格式化打印，常用于 model 的 collection, 并中断（调试）.
     */
    function ddJsonFormat($val)
    {
        echo PHP_EOL;
        echo json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo PHP_EOL;
        exit(0);
    }
}

/**
 * 是否单元测试环境.
 */
if (! function_exists('isTesting')) {
    function isTesting(): bool
    {
        return config('app_env') == 'testing';
    }
}

/**
 * 是否单元测试环境 || 开发环境.
 */
if (! function_exists('isTestingOrDev')) {
    function isTestingOrDev(): bool
    {
        return in_array(config('app_env'), ['testing', 'dev']);
    }
}

/**
 * 是否生产环境.
 */
if (! function_exists('isProd')) {
    function isProd(): bool
    {
        return config('app_env') == 'prod';
    }
}

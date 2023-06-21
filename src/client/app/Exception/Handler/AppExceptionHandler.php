<?php
// app/Exception/Handler/ApiExceptionHandler.php

namespace App\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // 处理 RuntimeException 异常
        if ($throwable instanceof \RuntimeException) {
            return $this->exceptionHandle($throwable, $response);
        }

        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());
        return $response->withHeader('Server', 'Hyperf')->withStatus(500)->withBody(new SwooleStream('Internal Server Error.'));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

    // 返回异常信息
    public function exceptionHandle(Throwable $throwable, ResponseInterface $response)
    {
        $data = json_encode([
            'code'    => $throwable->getCode(),
            'message' => $throwable->getMessage(),
        ], JSON_UNESCAPED_UNICODE);

        return $response->withAddedHeader('Content-Type', ' application/json; charset=UTF-8')
            ->withStatus(500)
            ->withBody(new SwooleStream($data));
    }
}

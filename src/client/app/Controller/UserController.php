<?php
// app/Controller/UserController.php

namespace App\Controller;

use App\Constants\ResponseCode;
use App\JsonRpc\Interface\UserServiceInterface;
use App\Tools\ResponseTool;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\RateLimit\Exception\RateLimitException;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[Controller]
class UserController extends AbstractController
{
    #[Inject]
    protected UserServiceInterface $userService;

    // 添加用户
    #[PostMapping('/users/store')]
    public function store()
    {
        $name = (string) $this->request->input('name', '');
        $gender = (int) $this->request->input('gender', 0);

        $user = $this->userService->createUser($name, $gender);
        if ($user['code'] != ResponseCode::SUCCESS) {
            throw new \RuntimeException($user['message']);
        }

        return ResponseTool::success($user['data']);
    }

    // 获取用户信息
    #[GetMapping('/users/show')]
    public function getUserInfo()
    {
        $id = (int) $this->request->input('id');
        $user = $this->userService->getUserInfo($id);

        if ($user['code'] != ResponseCode::SUCCESS) {
            throw new \RuntimeException($user['message']);
        }

        return ResponseTool::success($user['data']);
    }

    #[GetMapping('/users/test')]
    #[RateLimit(create: 1, consume: 1, limitCallback: [UserController::class, 'limitCallback'], key: [UserController::class, 'getUserId'], waitTimeout: 1)]
    public function test()
    {
        return ResponseTool::success($this->userService->test());
    }

    // 作为 RateLimit 的 回调
    public static function limitCallback(float $seconds, ProceedingJoinPoint $proceedingJoinPoint)
    {
        throw new RateLimitException('频繁的请求，请稍后重试（消费者）', 500);
    }

    // 作为 RateLimit 的 key
    public static function getUserId(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $request = ApplicationContext::getContainer()->get(RequestInterface::class);
        return $request->input('user_id', 0);
    }
}

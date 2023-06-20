<?php
// app/Controller/UserController.php

namespace App\Controller;

use App\JsonRpc\Interface\UserServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller]
class UserController extends AbstractController
{
    #[Inject]
    protected UserServiceInterface $userService;

    // 添加用户
    #[PostMapping('/users/store')]
    public function store()
    {
        $name   = (string)$this->request->input('name', '');
        $gender = (int)$this->request->input('gender', 0);

        return $this->userService->createUser($name, $gender);
    }

    // 获取用户信息
    #[GetMapping('/users/show')]
    public function getUserInfo()
    {
        $id = (int) $this->request->input('id');

        return $this->userService->getUserInfo($id);
    }
}

<?php
// app/JsonRpc/Interface/UserServiceInterface.php

namespace App\JsonRpc\Interface;

interface UserServiceInterface
{
    // 创建用户
    public function createUser(string $name, string $gender);

    // 获取用户信息
    public function getUserInfo(int $id);

    // 【集群】时客户端调用此方法，用于区分调用的是哪台服务器的服务
    public function test();
}

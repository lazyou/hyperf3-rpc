<?php
//// app/JsonRpc/Service/UserService.php
//namespace App\JsonRpc\Service;
//
//// app/JsonRpc/Service/UserService.php 的作用只是为了构建发起请求的参数和返回结果，hyperf 支持自动配置服务消费者代理类
//use App\JsonRpc\Interface\UserServiceInterface;
//use Hyperf\RpcClient\AbstractServiceClient;
//
///**
// * 手动创建消费者【一般是使用 自动配置服务消费者】 - 消费者业务代码编写
// */
//class UserService extends AbstractServiceClient implements UserServiceInterface
//{
//    // 定义对应服务提供者的服务名称
//    protected string $serviceName = 'UserService';
//
//    // 定义对应服务提供者的服务协议
//    protected string $protocol = 'jsonrpc-http';
//
//    public function createUser(string $name, int $gender)
//    {
//        return $this->__request(__FUNCTION__, compact('name', 'gender'));
//    }
//
//    public function getUserInfo(int $id)
//    {
//        return $this->__request(__FUNCTION__, compact('id'));
//    }
//
//    public function test()
//    {
//        return $this->__request(__FUNCTION__);
//    }
//}

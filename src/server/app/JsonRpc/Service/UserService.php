<?php
// app/JsonRpc/Service/UserService.php
namespace App\JsonRpc\Service;

use App\JsonRpc\Interface\UserServiceInterface;
use App\Model\User;
use Hyperf\RpcServer\Annotation\RpcService;

#[RpcService(name: "UserService", server: "jsonrpc-http", protocol: "jsonrpc-http")]
class UserService implements UserServiceInterface
{
    public function createUser(string $name, string $gender): string
    {
        if (empty($name)) {
            throw new \RuntimeException('用户名不能为空');
        }

        $user = User::query()->create([
            'name'   => $name,
            'gender' => $gender,
        ]);

        return $user ? 'success' : 'failed';
    }

    public function getUserInfo(int $id): array
    {
        $user = User::query()->find($id);
        if (empty($user)) {
            throw new \RuntimeException('没有该用户');
        }

        return $user->toArray();
    }
}

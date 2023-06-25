<?php
// app/JsonRpc/Service/UserService.php
namespace App\JsonRpc\Service;

use App\JsonRpc\Interface\UserServiceInterface;
use App\Model\User;
use App\Tools\ResponseTool;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\RpcServer\Annotation\RpcService;
use Hyperf\ServiceGovernanceConsul\ConsulAgent;

#[RpcService(name: "UserService", server: "jsonrpc-http", protocol: "jsonrpc-http", publishTo: "consul")]
class UserService implements UserServiceInterface
{
    public function createUser(string $name, string $gender): array
    {
        if (empty($name)) {
            throw new \RuntimeException('用户名不能为空');
        }

        $user = User::query()->create([
            'name'   => $name,
            'gender' => $gender,
        ]);

        return $user ? ResponseTool::success($user->toArray()) : ResponseTool::error('创建用户失败');
    }

    public function getUserInfo(int $id): array
    {
        $user = User::query()->find($id);
        if (empty($user)) {
            throw new \RuntimeException('没有该用户');
        }

        return ResponseTool::success($user->toArray());
    }

//    // 用于测试服务是否注册到服务中心
//    public function test()
//    {
//        // 获取注册的服务
//        $agent = ApplicationContext::getContainer()->get(ConsulAgent::class);
//
//        return ResponseTool::success([
//            'services' => $agent->services()->json(),
//            'checks'   => $agent->checks()->json(),
//        ]);
//    }

    // 客户端调用查看集群所调用的机器
    public function test()
    {
        $host = '';

        $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
        $servers = $config->get('server.servers');
        $appName = $config->get('app_name');

        foreach ($servers as $server) {
            if ($server['name'] == 'jsonrpc-http') {
                $host = $server['host'];
                break;
            }
        }

        // 获取容器内的ip
        $localIps = swoole_get_local_ip();
        if ($localIps) {
            $key = array_key_first($localIps);
            $host = $localIps[$key];
        }

        return ResponseTool::success([
            'app_name' => $appName,
            'host'     => $host,
        ]);
    }
}

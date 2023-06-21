docker-compose up [--build] -d
docker-compose up [--build] -d server

mysql8 默认账户为 root
redis6 默认账户为 default


```sql
CREATE TABLE `users` (
                         `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                         `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                         `gender` tinyint(1) NOT NULL DEFAULT 0,
                         `created_at` timestamp NULL DEFAULT now(),
                         `updated_at` timestamp NULL DEFAULT now(),
                         PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
```


### Composer
`composer config -g repo.packagist composer https://mirrors.aliyun.com/composer`


### server -- 服务提供者
```shell
composer require hyperf/json-rpc
composer require hyperf/rpc-server
```

* 不需要控制器

* 进入容器执行（TODO: 但是没理 method 是如何对应上的???）:
    ```shell
    # 创建用户
    curl --location 'http://127.0.0.1:9600/' \
    --header 'Content-Type: application/json' \
    --data '{
        "jsonrpc": "2.0",
        "method": "/user/createUser",
        "params": {
            "name": "李四",
            "gender": 1
        }
    }'

    # 查询用户 
    curl --location 'http://127.0.0.1:9600/' \
    --header 'Content-Type: application/json' \
    --data '{
        "jsonrpc": "2.0",
        "method": "/user/getUserInfo",
        "params": {
          "id": 1
        },
        "id": "",
        "context": []
    }'
    ```

* 【重要】关于请求与路由的说明（这里没有定义路由，怎么就可以通过路由进行访问了？）:
    * 看 UserService.php 注解中 `name: "UserService"` 指服务，**Hyperf 在底层会把 UserService 中的 User 取出并转为小写，然后拼接当前服务类中的方法，并组成路由**。
    * 如 UserService.php 中有 `getUserInfo` 方法，那么路由就是 `/user/getUserInfo`


### client -- 服务消费者
* 消费者是 http 请求，消费者再请求 rpc服务提供者

```shell
composer require hyperf/json-rpc
composer require hyperf/rpc-client
```

* 外部浏览器访问: http://localhost:9501/users/show?id=1

* 外部 curl 创建数据:
  ```shell
  curl --location 'http://localhost:9501/users/store' \
  --header 'Content-Type: application/json' \
  --data '{
      "name": "李2",
      "gender": 1
  }'
  ```

* 自动创建消费者:
    * app/JsonRpc/Service/UserService.php 的作用只是为了构建发起请求的参数和返回结果，hyperf 支持自动配置服务消费者代理类.

    * 还是通过上面的访问方式


# Consul -- 服务注册、发现
* Consul 是微服务架构中，解决服务发现、配置中心的分布式中间件

* 启动参数说明：
  ```shell
  -dev：表示开发环境模式运行；
  
  -server：以服务端身份启动（注册中心）；
  
  -client：指定客户端访问的 ip，0.0.0.0 表示不限制客户端 IP；
  
  -ui：开启 web 界面访问；
  
  -bootstrap-expect=3：表示 server 集群最低节点数为 3，低于该值工作不正常；
  
  -data-dir：表示指定数据的存储目录（该目录必须先创建）；
  
  -node：表示节点在 web ui 中显示的名称。
  ```

* 常用命令:
  ```shell
  # 查看集群节点
  consul members
  
  # 重新加载配置文件
  consul reload
  
  # 优雅的关闭节点
  consul leave
  
  # 查询所有注册服务
  consul catalog services
  ```


### 上 -- 【服务注册】，构建服务提供者
```shell
# 安装 consul 组件
composer require hyperf/service-governance-consul
   
# 生成配置文件
# 该命令会在 config/autoload 目录下生成 services.php 文件
php bin/hyperf.php vendor:publish hyperf/service-governance
```

* 测试 consul 服务：
  ```shell
  # server 容器内执行
  curl --location 'http://127.0.0.1:9600/' \
  --header 'Content-Type: application/json' \
  --data '{
      "jsonrpc": "2.0",
      "method": "/user/test",
      "params": {
          "name": "李四",
          "gender": 1
      }
  }'
  ```

* 浏览器查看服务: http://localhost:8500/ui/dc1/services


### 下 -- 【服务发现】，构建消费者使用服务
* `composer require hyperf/service-governance-consul`

* client 配置 `config/autoload/services.php`

* 外部浏览器访问: http://localhost:9501/users/show?id=1

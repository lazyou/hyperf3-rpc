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
  
    # 用户不存在情况，message 查看报错的具体容器ip
    curl --location 'http://172.28.0.5:9600/' \
    --header 'Content-Type: application/json' \
    --data '{
        "jsonrpc": "2.0",
        "method": "/user/getUserInfo",
        "params": {
          "id": 99
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
  curl --location 'http://localhost:9600/' \
  --header 'Content-Type: application/json' \
  --data '{
      "jsonrpc": "2.0",
      "method": "/user/test",
      "params": {}
  }'
  
  # 如果 server/config/autoload/server.php 配置是指定容器内 ip 的话
  curl --location 'http://172.28.0.5:9600/' \
  --header 'Content-Type: application/json' \
  --data '{
      "jsonrpc": "2.0",
      "method": "/user/test",
      "params": {}
  }'
  ```

* 浏览器查看服务: http://localhost:8500/ui/dc1/services


### 下 -- 【服务发现】，构建消费者使用服务
* `composer require hyperf/service-governance-consul`

* client 配置 `config/autoload/services.php`

* 外部浏览器访问: http://localhost:9501/users/show?id=1


### consul 负载均衡
* virtual 准备4台虚拟机，桥接模式。其中 master 使用 docker 安装 mysql redis

```shell
# 1. 服务端启动 consul （TODO： 如何后台启动啊？ 加 & 是临时解决方案）
consul agent -server -bind=192.168.20.35 -client=0.0.0.0 -ui -bootstrap-expect=3 -data-dir=/home/u/consul/data/ -node=server-01 &

consul agent -server -bind=192.168.20.36 -client=0.0.0.0 -ui -bootstrap-expect=3 -data-dir=/home/u/consul/data/ -node=server-02 &

consul agent -server -bind=192.168.20.37 -client=0.0.0.0 -ui -bootstrap-expect=3 -data-dir=/home/u/consul/data/ -node=server-03 &


# 2. 消费端以 client 模式启动
consul agent -client=0.0.0.0 -data-dir=/home/u/consul/data/ -ui -bind=192.168.20.38 -node=client-01 &


# 3. 除了 192.168.20.35 都执行
consul join 192.168.20.35

# master 运行 mysql 8
docker run -d \
    --name test-mysql8 \
    -p 3306:3306 \
    -v $PWD/cnf:/etc/mysql/conf.d \
    -v $PWD/data:/var/lib/mysql \
    -v /etc/localtime:/etc/localtime:ro \
    -e MYSQL_ROOT_PASSWORD=mysql112233 \
    mysql:8.0.31

# master 运行redis5， redis.conf 参考 blog
docker run -d \
    --name test-redis5 \
    -p 6379:6379 \
    -v $PWD/redis.conf:/etc/redis/redis.conf \
    -v $PWD/data:/data \
    redis:5.0.13 \
    redis-server /etc/redis/redis.conf \
    --appendonly yes \
    --requirepass "redis112233"

# 配置 APP_NAME DB 等
cp .env.example .env
php bin/hyperf.php start


# 访问 consul master 的 web ui
http://192.168.20.35:8500/ui/dc1/services

http://192.168.20.35:8500/ui/dc1/nodes


# 访问 client 接口:
http://192.168.20.38:9501/users/show?id=1

http://192.168.20.38:9501/users/test


# 压测，太多扛不住咋回事
ab -n 1000 -c 10 'http://192.168.20.38:9501/users/show?id=1'
```



### Nacos 集群
* https://www.ziruchu.com/art/666

* TODO: 可以单机试试


### 服务限流
* 服务限流指在高并发情况下，为了保护系统正常运行，从而对象访问服务的请求进行限制，从而保证服务的高可用

#### 为什么需要服务限流
* 把系统拆分为微服务之后，每个微服务可能会存在相互调用的关系，若其中某个服务被突如其来的大流量击垮，可能会引发 **雪崩** ，导致相关的微服务都不可用，从而影响业务。

#####【方案1：服务提供者中实现限流】Server  -- 针对所有请求的限流（不是针对用户 ID）
```shell
# 安装限流组件
composer require hyperf/rate-limit

# 该限流组件默认使用 redis 作为存储，也以把 redis 装上
composer require hyperf/redis


# 限流组件 -- 生成配置
# 该组件会在 config/autoload 目录下生成 rate_limit.php 文件
php bin/hyperf.php vendor:publish hyperf/rate-limit

# redis 组件
# 该组件会在 config/autoload 目录下生成 redis.php 文件
php bin/hyperf.php vendor:publish hyperf/redis

# 限流代码 app/JsonRpc/Service/UserService.php
```

* 需要注意： 该限流是【针对所有请求】进行的，而不是针对具体用户。
  * 如最大支持 1000 个请求，假如说某一个用户一瞬间请求了 1000 次，后面后续的用户都将触发限流机制。
  * 如果要针对用户进行限流，达到 A 用户被限流，B 用户正常请求，可以根据用户 ID 进行。

* 浏览器访问（不断 f5 刷新就能看到限流响应）: http://localhost:9501/users/show?id=1
  * 如果没效果则删除 server 的 runtime 目录



#####【方案2：服务提供者中实现限流】Client  -- 控制器中实现对用户限（针对用户 ID）
```shell
# 安装限流组件
composer require hyperf/rate-limit
# 生成配置文件
php bin/hyperf.php vendor:publish hyperf/rate-limit

# 限流代码 app/Controller/UserController.php
```

* 浏览器访问（不断 f5 刷新就能看到限流响应）: http://localhost:9501/users/test?user_id=2
  * 如果没效果则删除 server 的 runtime 目录


##### 限流总结
* TODO: 但是 rate_limit.php 配置并不能很直观的看出支持每秒多少个请求啊？？？？

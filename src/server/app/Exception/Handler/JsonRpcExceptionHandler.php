<?php
// app/Exception/Handler/JsonRpcExceptionHandler.php
namespace App\Exception\Handler;

use Hyperf\Contract\ConfigInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class JsonRpcExceptionHandler extends ExceptionHandler
{
    protected string $appName = '';

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $responseContents = json_decode($response->getBody()->getContents(), true);

        $errorMessage = $responseContents['error']['message'];
        if (! empty($responseContents['error'])) {
            $port = 0;
            $host = '';
            $config  = ApplicationContext::getContainer()->get(ConfigInterface::class);
            $servers = $config->get('server.servers');

            foreach ($servers as $server) {
                if ($server['name'] == 'jsonrpc-http') {
                    $port = $server['port'];
//                    $host = $server['host'];
                    $host = $server['host_log'];
                    break;
                }
            }

            // 知道是哪台服务抛出了异常
            if (empty($this->appName)) {
                $this->appName = (string) $config->get('app_name', '');
            }

            $responseContents['error']['message'] = "{$this->appName} : {$host}:{$port} : $errorMessage";
        }
        $data = json_encode($responseContents, JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)->withBody(new SwooleStream($data));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}

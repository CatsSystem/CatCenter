<?php
/*******************************************************************************
 *  This file is part of CatCenter.
 *
 *  CatCenter is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  CatCenter is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *******************************************************************************
 * Author: Lidanyang  <simonarthur2012@gmail.com>
 ******************************************************************************/

namespace base\server;

use core\component\config\Config;
use core\component\log\Log;
use core\component\task\TaskRoute;
use core\server\adapter\HttpServer;
use app\service\BaseService;

class MainServer extends HttpServer
{
    /**
     * @var \swoole_server
     */
    private $server;

    /**
     * 初始化函数，在swoole_server启动前执行
     * @param \swoole_server $server
     */
    public function init(\swoole_server $server)
    {

    }

    /**
     * Worker进程启动前回调此函数
     * @param \swoole_server $server swoole_server对象
     * @param int $workerId Worker进程ID
     */
    public function onWorkerStart(\swoole_server $server, $workerId)
    {
        $this->server = $server;
        // 加载配置
        Config::load();
    }


    /**
     * 服务器接收到Http完整数据包后回调此函数
     * @param \swoole_http_request $request swoole封装的http请求对象
     * @param \swoole_http_response $response swoole封装的http应答对象
     */
    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $uri = $request->server['request_uri'];
        $uris = explode("/", $uri);

        $class = str_replace("." , '\\', $uris[1]);
        $routes = Config::get('route');
        $class  = $routes[$class]??'';
        $method = "run" . $uris[2];

        $rawContent = $data = substr($request->rawContent(), 5);

        if(!class_exists($class))
        {
            Log::ERROR("System", "$class not found");
            $response->status(403);
            $response->end();
            return;
        }

        try {
            $service = new $class();

            $result = $service->$method($rawContent);

            $data = pack('CN', 0, strlen($result)) . $result;
            $response->end($data);
        } catch (\Exception $e) {
            Log::ERROR("System", $e->getMessage());
            $response->status(500);
            $response->end($e->getMessage());
        } catch (\Error $e) {
            Log::ERROR("System", $e->getMessage());
            $response->status(500);
            $response->end($e->getMessage());
        }
    }

    /**
     * 当Worker进程投递任务到Task Worker进程时调用此函数
     * @param \swoole_server $server swoole_server对象
     * @param int $task_id 任务ID
     * @param int $src_worker_id 发起任务的Worker进程ID
     * @param mixed $data 任务数据
     */
    public function onTask(\swoole_server $server, $task_id, $src_worker_id, $data)
    {
        $task_config = Config::getField('component', 'task');
        TaskRoute::onTask($task_config['task_path'], $data);
    }

    /**
     * Swoole进程间通信的回调函数
     * @param \swoole_server $server swoole_server对象
     * @param int $from_worker_id 来源Worker进程ID
     * @param mixed $message 消息内容
     */
    public function onPipeMessage(\swoole_server $server, $from_worker_id, $message)
    {

    }
}
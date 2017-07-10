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
 *  along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
 *******************************************************************************
 * Author: Lidanyang  <simonarthur2012@gmail.com>
 ******************************************************************************/

namespace app\service;

use app\common\Constants;
use app\common\Error;
use app\common\Utils;
use App\Message\LoadConfigRequest;
use App\Message\LoadConfigResponse;
use App\Message\OfflineRequest;
use App\Message\OfflineResponse;
use App\Message\OnlineRequest;
use App\Message\OnlineResponse;
use App\Message\StatusRequest;
use App\Message\StatusResponse;
use base\etcd\KVClient;
use core\model\MySQLStatement;

class Node extends BaseService
{


    /**
     * @param string $method 请求方法
     * @param string $raw_content 请求内容
     * @return mixed
     */
    public function parse($method, $raw_content)
    {
        $request_class = "\\app\\message\\{$method}Request";
        $request = new $request_class();
        if (method_exists($request, 'decode')) {
            $request->decode($raw_content);
        } else {
            $request->mergeFromString($raw_content);
        }
        return $request;
    }

    public function Online(OnlineRequest $request)
    {
        $response = new OnlineResponse();
        $service = $request->getService();

        $service_key = sprintf("%s:%s:%s",
            $service->getHost(),
            $service->getPort(),
            $service->getName());

        $result = yield MySQLStatement::prepare()
            ->select('service_table', 'service_id')
            ->where([
                'service_key' => $service_key
            ])
            ->getOne($this->mysql_pool->pop());
        if($result['code'] != Error::SUCCESS)
        {
            $status = 500;
            $error = $result['code'];
            goto error;
        }

        $service_id             = $result['data'];
        if(empty($service_id))
        {
            $service_id             = Utils::grantNodeId();
        }

        $result = yield MySQLStatement::prepare()
            ->insert('service_table' , [
                'service_id' => $service_id,
                'name' => $service->getName(),
                'type' => $service->getType(),
                'host' => $service->getHost(),
                'port' => $service->getPort(),
                'extra'=> $service->getExtra()
            ], "IGNORE", [
                    'name' => $service->getName(),
                    'type' => $service->getType(),
                    'host' => $service->getHost(),
                    'port' => $service->getPort(),
                    'extra'=> $service->getExtra()
                ]
            )
            ->query($this->mysql_pool->pop());

        if($result['code'] != Error::SUCCESS)
        {
            $status = 500;
            $error = $result['code'];
            goto error;
        }

        $response->setId($service_id);
        $header = $this->getHeader(200);
        $response->setHeader($header);
        return $response;

        error:
        $header = $this->getHeader($status, $error);
        $response->setHeader($header);
        return $response;
    }

    public function Offline(OfflineRequest $request)
    {
        $response = new OfflineResponse();
        $service_id = $request->getId();
        $result = yield KVClient::getInstance()->del($service_id);
        if($result != Error::SUCCESS)
        {
            $status = 500;
            $error = "保存失败";
            goto error;
        }
        $header = $this->getHeader(200);
        $response->setHeader($header);
        return $response;

        error:
        $header = $this->getHeader($status, $error);
        $response->setHeader($header);
        return $response;
    }

    public function Status(StatusRequest $request)
    {
        $response   = new StatusResponse();
        $service_id = $request->getId();
        $status     = $request->getStatus();
    }

    public function LoadConfig(LoadConfigRequest $request)
    {
        $response   = new LoadConfigResponse();
        $service_id = $request->getId();

        $key = Constants::CONFIG_PREFIX . $service_id;
        $result = yield KVClient::getInstance()->get($key);
        if (is_int($result)) {
            $status = 500;
            $error = "保存失败";
            goto error;
        }
        $response->setHeader($this->getHeader(200));
        $response->setConfig($result);
        return $response;

        error:
        $response->setHeader($this->getHeader($status, $error));
        return $response;
    }
}
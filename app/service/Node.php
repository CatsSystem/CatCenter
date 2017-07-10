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

namespace app\service;

use app\common\Constants;
use app\common\Error;
use app\common\Utils;
use base\framework\mysql\Model;
use base\framework\service\BaseService;
use Proto\Message\LoadConfigRequest;
use Proto\Message\LoadConfigResponse;
use Proto\Message\OfflineRequest;
use Proto\Message\OfflineResponse;
use Proto\Message\OnlineRequest;
use Proto\Message\OnlineResponse;
use Proto\Message\StatusRequest;
use Proto\Message\StatusResponse;
use base\etcd\KVClient;
use core\model\MySQLStatement;
use Proto\Service\NodeService;

class Node extends NodeService
{
    use BaseService;

    public function Online(OnlineRequest $request)
    {
        $response = new OnlineResponse();
        $service = $request->getService();

        $service_key = sprintf("%s:%s:%s",
            $service->getHost(),
            $service->getPort(),
            $service->getName());
        $result = Model::D("service_table")
            ->field("service_id")
            ->where([
                'service_key' => $service_key
            ])
            ->find();
        if(empty($result))
        {
            $service_id         = Utils::grantNodeId();
            $service_info = [
                'service_id'    => $service_id,
                'service_key'   => $service_key,
                'name'          => $service->getName(),
                'type'          => $service->getType(),
                'host'          => $service->getHost(),
                'port'          => $service->getPort(),
                'extra'         => $service->getExtra(),
            ];
            $result = Model::D("service_table")->add($service_info);
        } else {
            $service_id         = $result['service_id'];
            $result = true;
        }

        $response->setId($service_id);
        $header = $this->getHeader($result ? 200 : 500);
        $response->setHeader($header);
        return $response;
    }

    public function Offline(OfflineRequest $request)
    {
        $response = new OfflineResponse();
        $service_id = $request->getId();



        $result = yield MySQLStatement::prepare()
            ->update("service_table", [
                'status'    => Constants::SERVICE_OFFLINE
            ], [
                'service_id' => $service_id
            ])
            ->query($this->mysql_pool->pop());

        if($result['code'] != Error::SUCCESS)
        {
            $status = 500;
            $error = $result['code'];
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

        $result = yield MySQLStatement::prepare()
            ->update("service_status",
                sprintf("status=%d, last_status=status, update_time=%d",$status, time()),
                [
                    'service_id' => $service_id
                ]
            )
            ->query($this->mysql_pool->pop());

        if($result['code'] != Error::SUCCESS)
        {
            $status = 500;
            $error = $result['code'];
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
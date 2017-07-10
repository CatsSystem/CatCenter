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
use Proto\Service\NodeService;
use Utils\GrpcTransform;

class Node extends NodeService
{
    use BaseService;

    public function Online(OnlineRequest $request)
    {
        $request = GrpcTransform::getInstance()->objectToArr($request);
        $data    = \app\controller\Node::getInstance()->Online($request);
        $tree    = [
            'header' => '\Service\Message\ResponseHeader',
        ];
        return GrpcTransform::getInstance()->arrToObject(OnlineResponse::class, $data, $tree);
    }

    public function Offline(OfflineRequest $request)
    {
        $request = GrpcTransform::getInstance()->objectToArr($request);
        $data    = \app\controller\Node::getInstance()->Offline($request);
        $tree    = [
            'header' => '\Service\Message\ResponseHeader',
        ];
        return GrpcTransform::getInstance()->arrToObject(OfflineResponse::class, $data, $tree);
    }

    public function Status(StatusRequest $request)
    {
        $response   = new StatusResponse();
        $service_id = $request->getId();
        $status     = $request->getStatus();

        $result = Model::D("service_table")
            ->where([
                'service_id' => $service_id
            ])
            ->save([
                'status'        => $status,
                'last_status'   => 'status',
                'update_time'   => time()
            ]);
        if($result === false)
        {
            $status = 500;
            $error = Error::ERR_UPDATE_SERVICE_FAILED;
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
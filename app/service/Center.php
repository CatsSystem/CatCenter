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

use app\common\Error;
use base\framework\service\BaseService;
use core\component\config\Config;
use Proto\Message\GetEtcdAddressRequest;
use Proto\Message\GetEtcdAddressResponse;
use Proto\Message\GetServiceRequest;
use Proto\Message\GetServiceResponse;
use Proto\Message\ListServiceRequest;
use Proto\Message\ListServiceResponse;
use Proto\Message\Service;
use Proto\Service\CenterService;

class Center extends CenterService
{
    use BaseService;

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

    public function GetEtcdAddress(GetEtcdAddressRequest $request)
    {
        $response = new GetEtcdAddressResponse();
        $response->setHeader($this->getHeader(200));
        $response->setAddress(Config::getField('etcd', 'hostname'));
        return $response;
    }

    public function GetService(GetServiceRequest $request)
    {
        $response = new GetServiceResponse();
        $service_id = $request->getId();

        $result = yield $this->redis_pool->pop()->HGETALL($service_id);
        if($result['code'] != Error::SUCCESS)
        {
            $status = 500;
            $error = "获取失败";
            goto error;
        }

        $info = [];
        $len = count($result['data']);
        for($i = 0; $i < $len; $i += 2)
        {
            $info[$result['data'][$i]] = $result['data'][$i + 1];
        }
        $service = new Service();
        $service->setName($info['name']);
        $service->setType($info['type']);
        $service->setHost($info['host']);
        $service->setPort($info['port']);
        $service->setExtra($info['extra']);
        $header = $this->getHeader(200);
        $response->setHeader($header);
        $response->setService($service);
        return $response;

        error:
        $header = $this->getHeader($status, $error);
        $response->setHeader($header);
        return $response;
    }

    public function ListService(ListServiceRequest $request)
    {
        $response   = new ListServiceResponse();
        $service_id = $request->getId();
        $status     = $request->getStatus();
    }
}
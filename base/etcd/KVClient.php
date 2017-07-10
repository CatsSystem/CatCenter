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

namespace base\etcd;

use app\common\Error;
use core\component\config\Config;
use core\concurrent\Promise;
use Etcdserverpb\DeleteRangeRequest;
use Etcdserverpb\DeleteRangeResponse;
use Etcdserverpb\PutRequest;
use Etcdserverpb\RangeRequest;
use Etcdserverpb\RangeResponse;

class KVClient
{
    private static $instance;

    /**
     * @return KVClient
     */
    public static function getInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new KVClient();
        }

        return KVClient::$instance;
    }

    public function __construct()
    {
        $this->hostname = Config::getField('etcd', 'hostname');
        $this->connect();
    }

    /**
     * @var \etcd\KVClient
     */
    private $client;

    private $hostname;

    public function connect()
    {
        $this->client = new \etcd\KVClient($this->hostname, []);
    }

    public function put($key, $value)
    {
        $promise = new Promise();
        $request = new PutRequest();
        $request->setKey($key);
        $request->setValue($value);
        $this->client->Put($request)->wait(function($result) use ($promise){
            list($response, $status) = $result;
            if($status != 200)
            {
                $promise->resolve(Error::ERR_ETCD_REQUEST_FAILED);
                return;
            }
            $promise->resolve(Error::SUCCESS);
        });
        return $promise;
    }

    public function get($key)
    {
        $promise = new Promise();
        $request = new RangeRequest();
        $request->setKey($key);
        $this->client->Range($request)->wait(function($result) use ($promise){
            list($response, $status) = $result;
            if(!$response instanceof RangeResponse)
            {
                $promise->resolve(Error::ERR_ETCD_REQUEST_FAILED);
                return;
            }
            if($status != 200)
            {
                $promise->resolve(Error::ERR_ETCD_REQUEST_FAILED);
                return;
            }
            $list = $response->getKvs();
            if($response->getCount() == 0)
            {
                $promise->resolve(Error::ERR_ETCD_REQUEST_FAILED);
                return;
            }
            foreach ($list as $item)
            {
                $promise->resolve($item->getValue());
                break;
            }
        });
        return $promise;
    }

    public function range($start, $end)
    {
        $promise = new Promise();
        $request = new RangeRequest();
        $request->setKey($start);
        $request->setRangeEnd($end);
        $this->client->Range($request)->wait(function($result) use ($promise){
            list($response, $status) = $result;
            if($status != 200)
            {
                $promise->resolve(Error::ERR_ETCD_REQUEST_FAILED);
                return;
            }
            $list = $response->getKvs();
            $items = [];
            foreach ($list as $item)
            {
                $items[$item->getKey()] = $item->getValue();
            }
            $promise->resolve($items);
        });
        return $promise;
    }

    public function list($prefix)
    {
        $promise = new Promise();
        $request = new RangeRequest();
        $request->setKey($prefix);
        $request->setRangeEnd($prefix + 1);
        $this->client->Range($request)->wait(function($result) use ($promise){
            list($response, $status) = $result;
            if($status != 200)
            {
                $promise->resolve(Error::ERR_ETCD_REQUEST_FAILED);
                return;
            }
            $list = $response->getKvs();
            $items = [];
            foreach ($list as $item)
            {
                $items[$item->getKey()] = $item->getValue();
            }
            $promise->resolve($items);
        });
        return $promise;
    }

    public function all($limit = 0)
    {
        $promise = new Promise();
        $request = new RangeRequest();
        $request->setKey(0);
        $request->setRangeEnd(0);
        if($limit > 0)
        {
            $request->setLimit($limit);
        }
        $this->client->Range($request)->wait(function($result) use ($promise){
            list($response, $status) = $result;
            if($status != 200)
            {
                $promise->resolve(Error::ERR_ETCD_REQUEST_FAILED);
                return;
            }
            $list = $response->getKvs();
            $items = [];
            foreach ($list as $item)
            {
                $items[$item->getKey()] = $item->getValue();
            }
            $promise->resolve($items);
        });
        return $promise;
    }

    public function del($key)
    {
        $promise = new Promise();
        $request = new DeleteRangeRequest();
        $request->setKey($key);
        $this->client->DeleteRange($request)->wait(function($result) use ($promise){
            list($response, $status) = $result;
            if(!$response instanceof DeleteRangeResponse)
            {
                $promise->resolve(Error::ERR_ETCD_REQUEST_FAILED);
                return;
            }
            if($status != 200)
            {
                $promise->resolve(Error::ERR_ETCD_REQUEST_FAILED);
                return;
            }
            if(($deleted = $response->getDeleted()) == 0)
            {
                $promise->resolve(Error::ERR_ETCD_REQUEST_FAILED);
                return;
            }
            $promise->resolve($deleted);
        });
        return $promise;
    }
}
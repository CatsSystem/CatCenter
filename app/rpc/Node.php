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

namespace app\rpc;

use app\common\Utils;
use base\etcd\KVClient;
use core\component\config\Config;

class Node
{
    private $etcd_server;

    public function __construct()
    {
        $this->etcd_server = Config::getField('etcd', 'hostname');
    }

    public function online($name, $socket_type, $server_ip, $port)
    {
        $id = Utils::grantNodeId();
        $info['name']           = $name;
        $info['socket_type']    = $socket_type;
        $info['ip']             = $server_ip;
        $info['port']           = $port;
        $info['ping_url']       = '';
        KVClient::getInstance($this->etcd_server)->put($id,
            \swoole_serialize::pack($info));
        return $id;
    }

    public function setPing($id, $url)
    {
        $result = yield KVClient::getInstance($this->etcd_server)->get($id);
        if(is_int($result))
        {
            return $result;
        }
        $info = \swoole_serialize::unpack($result);
        $info['ping_url'] = $url;
        KVClient::getInstance($this->etcd_server)->put($id,
            \swoole_serialize::pack($info));
        return 0;
    }

    public function status()
    {

    }
}
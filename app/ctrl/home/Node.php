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

namespace app\ctrl\home;

use app\ctrl\Base;
use base\etcd\KVClient;
use core\component\config\Config;

class Node extends Base
{
    private $etcd_server;

    public function __construct()
    {
        $this->etcd_server = Config::getField('etcd', 'hostname');
    }

    public function getList()
    {
        $result = KVClient::getInstance($this->etcd_server)->all();
        if(is_int($result))
        {
            return $this->error($result, '获取列表失败');
        }

        return $this->success($result);
    }

    public function getDetail()
    {
        $id = $this->params['id'];
        $result = KVClient::getInstance($this->etcd_server)->get($id);
        if(is_int($result))
        {
            return $this->error($result, '获取详情失败');
        }

        return $this->success($result);
    }

    public function saveConfig()
    {
        $id = $this->params['id'];
        $config = $this->params['config'];

        $key = "Config_" . $id;
        $result = KVClient::getInstance($this->etcd_server)->put($key, $config);
        if(is_int($result))
        {
            return $this->error($result, '保存失败');
        }

        return $this->success();
    }
}
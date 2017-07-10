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

namespace app\controller;

use app\common\Constants;
use app\common\Error;
use app\common\Utils;
use base\framework\BaseController;
use base\framework\Instance;
use base\framework\mysql\Model;

class Node extends BaseController
{
    use Instance;

    public function Online($request)
    {
        $service = $request['service'];

        $service_key = sprintf("%s:%s:%s",
            $service['host'],
            $service['port'],
            $service['name']);
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
                'name'          => $service['name'],
                'type'          => $service['type'],
                'host'          => $service['host'],
                'port'          => $service['port'],
                'extra'         => $service['extra'],
            ];
            $result = Model::D("service_table")->add($service_info);
        } else {
            $service_id         = $result['service_id'];
            $result = true;
        }

        if($result)
        {
            $this->success([
                'service_id' => $service_id
            ]);
        }
    }

    public function Offline($request)
    {
        $service_id = $request['id'];

        $result = Model::D("service_table")
            ->where([
                'service_id' => $service_id
            ])
            ->save([
                'status'        => Constants::SERVICE_OFFLINE,
                'last_status'   => 'status',
                'update_time'   => time()
            ]);

        if($result === false)
        {
            return $this->error(500, Error::ERR_UPDATE_SERVICE_FAILED);
        }
        return $this->success();
    }
}
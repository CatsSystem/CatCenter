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


require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../rpc_grpc_pb.php';

use App\Message\OnlineRequest;
use App\Message\Service;

$client = new \App\Service\NodeClient("0.0.0.0:8340", [
    'credentials' => \Grpc\ChannelCredentials::createInsecure(),
]);

$request = new OnlineRequest();
$service = new Service();
$service->setName("TestService");
$service->setType(1);
$service->setHost("127.0.0.1");
$service->setPort("9510");
$service->setExtra(json_encode([
    'socket_type' => 'tcp'
]));
$request->setService($service);

list($reply, $status) = $client->Online($request)->wait();

if($reply instanceof \App\Message\OnlineResponse)
{
    var_dump($reply);
    var_dump($reply->getHeader()->getStatus());
    var_dump($reply->getHeader()->getError());
    var_dump($reply->getId());
}



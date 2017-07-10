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

class Test
{
    public function __construct()
    {
        $server = new swoole_http_server("0.0.0.0", 9502);
        $server->set([
            'worker_num' => 1,
            'max_request' => 10000
        ]);
        $server->on("workerstart", function(){
            $this->connect(function(){});
        });
        $server->on("request", function($request, $res){
            $this->count --;
            if($this->count <= 0)
            {
                $this->client->close();
                $this->connect(function () use ($res){
                    $this->client->post("/test", "hello", 3, function($client, $response) use ($res){
                        $res->end($response->body);
                    });
                });

            } else {
                $this->client->post("/test", "hello", 3, function($client, $response) use ($res){
                    $res->end($response->body);
                });
            }
        });
        $server->start();
    }

    public function connect($callback)
    {
        $this->client = new http2_client("127.0.0.1", 9501, false);
        $this->count = 1000;
        $this->client->connect(1, $callback);
    }

}

new Test();


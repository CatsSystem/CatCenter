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

$server = new swoole_http_server("0.0.0.0", 9501);
$server->set([
    'open_http2_protocol' => true,
    'buffer_output_size'    => 128 * 1024 *1024,
    'socket_buffer_size'    => 128 * 1024 *1024
]);
$server->on("request", function($request, $response){
    //var_dump($request);
    $response->end(str_repeat("HelloWorld", 5000));
});

$server->start();

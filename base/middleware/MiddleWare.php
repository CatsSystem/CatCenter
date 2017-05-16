<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 2017/5/15
 * Time: 下午8:35
 */

namespace base\middleware;


use base\framework\Request;
use base\framework\Response;

interface MiddleWare
{
    public function doInvoke(Request &$request, Response &$response);
}
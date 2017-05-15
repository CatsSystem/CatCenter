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

namespace base\framework;

class Request
{
    /**
     * @var \swoole_http_request
     */
    private $request;


    /**
     * @var string
     */
    private $module;

    /**
     * @var string
     */
    private $ctrl;

    /**
     * @var string
     */
    private $action;

    /**
     * @var array
     */
    private $params;

    /**
     * @var string
     */
    private $rawContent;

    public static function newRequest($request, $path_info)
    {
        $result = explode("/", $path_info);
        if(count($result) < 4)
        {
            return null;
        }
        $module     = isset( $path[1] ) ? $path[1] : "";
        $ctrl       = isset( $path[2] ) ? $path[2] : "";
        $action     = isset( $path[3] ) ? $path[3] : "";
        return new Request($request, $module, $ctrl, $action);
    }

    public function __construct($request, $module, $ctrl, $action)
    {
        $this->request  = $request;
        $this->module   = $module;
        $this->ctrl     = $ctrl;
        $this->action   = $action;
    }

    public function init()
    {
        if( isset($this->request->post) )
        {
            $this->params = $this->request->post;
        }
        else
        {
            $this->rawContent = $this->request->rawContent();
            if(empty($this->rawContent))
            {
                $this->params = [];
            }
            else
            {
                $this->params = json_decode($this->rawContent,
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public function getPathInfo()
    {
        return $this->request->server['path_info'];
    }

    public function cookie()
    {
        return $this->request->cookie ?? [];
    }

    public function header()
    {
        return $this->request->header ?? [];
    }

    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getCtrl(): string
    {
        return $this->ctrl;
    }

    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * @return string
     */
    public function getRawContent(): string
    {
        return $this->rawContent;
    }
}
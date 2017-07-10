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

namespace app\ctrl;

use app\common\Error;
use base\framework\IController;
use base\framework\Request;
use base\framework\Response;

class Base implements IController
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $params;

    public function before(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->params = $request->getParams();
        return true;
    }

    public function success($data = [])
    {
        return [
            'code' => Error::SUCCESS,
            'data' => $data
        ];
    }

    public function error($code, $msg = 'request failed.')
    {
        return [
            'code'  => $code,
            'error' => $msg
        ];
    }
}
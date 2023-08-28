<?php

namespace Mx\Controllers;

use Bitrix\Main\Engine\Response\Json;
use Bitrix\Main\Request;

class BaseController
{
    protected Request $request;
    protected Json $response;

    protected function __construct(Request $request)
    {
        $this->request = $request;
        $this->response = new Json();
    }

    protected function setResponse(int $status, bool $isSuccess, $data = null, $message = null)
    {
        $response = [
            'success' => $isSuccess,
            'data' => $data,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        $this->response->setStatus($status);
        $this->response->setData($response);

        $this->response->send();
    }
}

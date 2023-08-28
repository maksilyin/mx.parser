<?php

namespace Mx;

use \Bitrix\Main\Engine\Response\Json;
use \Bitrix\Main\Request;
use \Bitrix\Main\Context;

class ControllerLoader
{
    protected Request $request;
    protected Json $jsonResponse;

    public function __construct()
    {
        $this->request = Context::getCurrent()->getRequest();
        $this->jsonResponse = new Json();
    }

    public function loadController()
    {
        $controllerName = $this->request->get('controller');
        $actionName = ($this->request->get('action') ?? 'index').'Action';
        $controllerName = $this->getControllerClass($controllerName);

        if (class_exists($controllerName) && method_exists($controllerName, $actionName)) {
            $controller = new $controllerName($this->request);
            $controller->{$actionName}();
        }
        else {
            $this->jsonResponse->setStatus(404)->send();
        }
    }

    private function getControllerClass(string $controllerName): string
    {
        $controllerName = mb_strtoupper(mb_substr($controllerName, 0, 1, 'UTF-8'), 'UTF-8')
            . mb_substr($controllerName, 1, null, 'UTF-8');
        return '\Mx\Controllers\\'.$controllerName.'Controller';
    }
}

<?php

namespace Mx\Controllers;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Request;

class SettingsController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }
    /**
     * @throws ArgumentNullException
     */
    public function indexAction()
    {
        $arSettings = Option::getForModule('mx.parser');

        if (empty($arSettings)) {
            $arSettings = Option::getDefaults('mx.parser');
        }

        $this->setResponse(200, true, $arSettings);
    }

    public function defaultAction()
    {
        $arSettings = Option::getDefaults('mx.parser');
        $this->setResponse(200, true, $arSettings);
    }

    /**
     * @throws ArgumentOutOfRangeException
     */
    public function saveAction()
    {
        $request = json_decode(file_get_contents('php://input'), true);

        foreach ($request['settings'] as $param => $value) {
            Option::set('mx.parser', $param, $value);
        }

        $this->setResponse(200, true, true);
    }
}

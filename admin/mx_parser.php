<?php
set_time_limit(0);
header('Access-Control-Allow-Origin: *');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Mx\ControllerLoader;

Asset::getInstance()->addString("<link rel='stylesheet' href='/local/modules/mx.parser/admin/resources/dist/css/app.css' />");

Loader::includeModule('mx.parser');

$request = Context::getCurrent()->getRequest();

if ($request->get('controller')) {
    $controllerLoader = new ControllerLoader();
    $controllerLoader->loadController();

    return;
}

?>

<div id="app"></div>
<div id="modals"></div>

<script src = "<?="/local/modules/mx.parser/admin/resources/dist/js/chunk-vendors.js"?>"></script>
<script src = "<?="/local/modules/mx.parser/admin/resources/dist/js/app.js"?>"></script>

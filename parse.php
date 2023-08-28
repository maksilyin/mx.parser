<?php
set_time_limit(1000);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use Mx\Helpers\ParserCatalog;
use Mx\Helpers\StatusHelper;

Loader::includeModule('mx.parser');

$parserCatalog = new ParserCatalog();

$step = StatusHelper::STATUS_END;

if ($argv[0]) {
    $step = $argv[0];
}

$parserCatalog->process($step);

<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

global $USER, $APPLICATION;

if(!$USER->IsAuthorized())
    return false;

if (!Loader::includeModule('mx.parser')) return;

$SUP_RIGHT = $APPLICATION::GetGroupRight('mx.parser');

if ($SUP_RIGHT != 'D') {
    return array(
        'parent_menu' => 'global_menu_content',
        'text' => 'Парсер',
        'icon' => 'custom_menu_icon',
        'url' => '/bitrix/admin/mx_parser.php',
    );
}

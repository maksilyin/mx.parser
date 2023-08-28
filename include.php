<?php
$requiredModules = include(__DIR__.'/install/require.php');
foreach ($requiredModules as $module){
    \Bitrix\Main\Loader::includeModule($module);
}
CModule::AddAutoloadClasses('mx.parser', array());

require_once( __DIR__ . '/lib/autoload.php');

<?php

namespace Mx\Helpers;

use Bitrix\Main\IO;
use Bitrix\Main\Application;
use Bitrix\Main\IO\FileNotFoundException;

class StatusHelper
{
    const STATUS_FILE = 'status.json';
    const DATA_LIST_FILE = 'list.json';
    const LOG_FILE = 'log.json';
    const DIR = '/upload/mx_parser/';

    const STATUS_MAIN_CATALOG = 1;
    const STATUS_GET_SUBCATEGORIES = 2;
    const STATUS_CREATING_CATEGORIES = 3;
    const STATUS_PARSE_PRODUCT_LIST = 4;
    const STATUS_PARSE_PRODUCTS = 5;
    const STATUS_CREATING_PRODUCTS = 6;
    const STATUS_DATA_EMPTY = 7;
    const STATUS_DATA_ERROR = 8;
    const STATUS_END = 0;
    /**
     * @var array|string[]
     */

    private static array $arMessages = [
        0 => 'Завершено',
        1 => 'Парсим список каталогов',
        2 => 'Парсим список подкатегорий',
        3 => 'Добавляем категории ',
        4 => 'Парсим список товаров',
        5 => 'Парсим товары',
        6 => 'Добавляем товары',
        7 => 'Не удалось получить данные, проверьте настройки xPath',
        8 => 'Не предвиденная ошибка',
    ];

    /**
     * @throws FileNotFoundException
     */
    public static function getStatus()
    {
        $file = new IO\File(self::getStatusPath());

        if (!$file->isExists()) {
            return false;
        }

        $result = $file->getContents();

        if ($result) {
            $result = json_decode($result, true);
        }

        return $result;
    }

    public static function setStatusCode($code, $message = false, $error = false, $data = false)
    {
        $numCode = $code;

        if ($error !== false) {
            $numCode = $error;
        }

        $message = $message === false ? self::$arMessages[$numCode] : $message;

        $status = [
            'status' => $code,
            'message' => $message,
            'error' => $error,
            'data' => $data
        ];

        self::setStatus(json_encode($status));
    }

    public static function setList($data, $code = null)
    {
        self::setDataFile($data, self::DATA_LIST_FILE);

        if ($code !== null) {
            self::setStatusCode($code);
        }
    }

    public static function getListData()
    {
        $file = new IO\File(self::getDirPath().self::DATA_LIST_FILE);

        if (!$file->isExists()) {
            return false;
        }

        $result = $file->getContents();

        if ($result) {
            $result = json_decode($result, true);
        }

        return $result;
    }

    /**
     * @throws FileNotFoundException
     */
    public static function addLog($message, $type = 'message')
    {
        $data = [
            [
                'date' => date('d.m.Y H:i:s'),
                'type' => $type,
                'message' => $message
            ]
        ];

        $arLogs = self::getLog();

        if ($arLogs) {
            $arLogs = array_merge($arLogs, $data);
        }
        else {
            $arLogs = $data;
        }

        self::setDataFile(json_encode($arLogs), self::LOG_FILE);
    }

    /**
     * @throws FileNotFoundException
     */
    public static function getLog()
    {
        $file = new IO\File(self::getDirPath().self::LOG_FILE);

        if (!$file->isExists()) {
            return false;
        }

        $result = $file->getContents();

        if ($result) {
            $result = json_decode($result, true);
        }

        return $result;
    }

    public static function clearLog()
    {
        self::setDataFile("", self::LOG_FILE);
    }

    private static function setStatus($data)
    {
        self::setDataFile($data, self::STATUS_FILE);
    }

    private static function setDataFile($data, $fileName, $type = IO\File::REWRITE)
    {
        $dirPath = self::getDirPath();
        $dir = new IO\Directory($dirPath);
        $filePath = $dirPath.$fileName;

        if (!$dir->isExists()) {
            $dir->create();
        }

        $file = new IO\File($filePath);

        $file->putContents($data, $type);
    }

    private static function getDirPath()
    {
        return Application::getDocumentRoot().self::DIR;
    }

    private static function getStatusPath()
    {
        return self::getDirPath().self::STATUS_FILE;
    }
}

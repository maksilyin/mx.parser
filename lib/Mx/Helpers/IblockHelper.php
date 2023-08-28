<?php

namespace Mx\Helpers;

use Bitrix\Main\Config\Option;
use CFile;
use CIBlockSection;
use CUtil;

class IblockHelper
{
    public static function addSection($name, $picture = false, $parentId = 0)
    {
        $iBlockId = Option::get('mx.parser', 'iblock_id');
        $bs = new CIBlockSection;
        $code = CUtil::translit($name, 'ru', ['replace_space' => '-', 'replace_other' => '-']);
        $arFields = Array(
            "ACTIVE" => 'Y',
            "IBLOCK_SECTION_ID" => $parentId,
            "IBLOCK_ID" => $iBlockId,
            "NAME" => $name,
            "CODE" => $code,
            "SORT" => 500,
        );

        if ($picture) {
            $arFile = CFile::MakeFileArray($picture);
            $arFields['PICTURE'] = $arFile;
        }

        return $bs->Add($arFields);
    }

    public static function getSectionIdByName($name, $parentId = 0)
    {
        $iBlockId = Option::get('mx.parser', 'iblock_id');
        $oRes = \CIBlockSection::GetList([], ['IBLOCK_ID' => $iBlockId, 'NAME' => $name, 'SECTION_ID' => $parentId], false, ['ID']);

        if ($arFields = $oRes->GetNext()) {
            return $arFields['ID'];
        }

        return false;
    }

    public static function getElementByCode($code)
    {
        $iBlockId = Option::get('mx.parser', 'iblock_id');
        $oRes = \CIBlockElement::GetList([], ['IBLOCK_ID' => $iBlockId, 'CODE' => $code]);

        if ($arFields = $oRes->GetNext()) {
            return $arFields;
        }

        return false;
    }

    public static function processElement($productData)
    {
        $iBlockId = Option::get('mx.parser', 'iblock_id');
        $el = new \CIBlockElement;
        $code = CUtil::translit($productData['name'], 'ru', ['replace_space' => '-', 'replace_other' => '-']);
        $iblockSectionId = 0;
        $image = "";
        $props = [];

        if (!empty($productData['image'])) {
            $image = CFile::MakeFileArray($productData['image']);
        }

        if (!empty($productData['category'])) {
            $iblockSectionId = $productData['category'][count($productData['category']) - 1];
        }

        if (!empty($productData['categoryName'])) {
            $sectionId = self::getSectionIdByName($productData['categoryName']);

            if (in_array($sectionId, $productData['categoryName'])) {
                $iblockSectionId = $sectionId;
            }
        }

        if (!empty($productData['spec'])) {
            foreach ($productData['spec'] as $specItem) {
                $res = \CIBlock::GetProperties($iBlockId, array(), array("NAME" => $specItem['name']));

                if ($arProp = $res->Fetch()) {
                    $props[$arProp['CODE']] = $specItem['value'];
                }
                else {
                    $propCode = CUtil::translit($specItem['name'], 'ru', ['replace_space' => '_', 'replace_other' => '_', 'change_case' => 'U']);
                    $arFields = Array(
                        "NAME" 			=> $specItem['name'],
                        "ACTIVE" 		=> "Y",
                        "SORT" 			=> "500",
                        "DEFAULT_VALUE" => "",
                        "CODE" 			=> $propCode,
                        "ROW_COUNT" 	=> "1",
                        "COL_COUNT" 	=> "10",
                        "MULTIPLE"	 	=> "N",
                        "MULTIPLE_CNT" 	=> "",
                        "PROPERTY_TYPE"	=> "S",
                        "LIST_TYPE" 	=> "L",
                        "IBLOCK_ID" 	=> $iBlockId
                    );
                    $ibp = new \CIBlockProperty;
                    $SrcPropID = $ibp->Add($arFields);

                    if (!$SrcPropID) {
                        $message = 'Не удалось создать свойство '. $specItem['name'] . ' - ' .$ibp->LAST_ERROR;
                        StatusHelper::addLog($message, 'error');
                    }
                    else {
                        $message = 'Создано свойство '. $specItem['name'];
                        $props[$propCode] = $specItem['value'];
                        StatusHelper::addLog($message);
                    }
                }
            }
        }

        $arFields = [
            "IBLOCK_SECTION_ID" => $iblockSectionId,
            "IBLOCK_SECTION" => $productData['category'],
            "IBLOCK_ID"      => $iBlockId,
            "PROPERTY_VALUES"=> $props,
            "NAME"           => $productData['name'],
            "CODE"           => $code,
            "ACTIVE"         => "Y",
            "DETAIL_PICTURE" => $image,
        ];

        $element = self::getElementByCode($code);

        if ($element) {
            $id = $element['ID'];
            $res = $el->Update($id, $arFields);

            if ($res) {
                StatusHelper::addLog('Товар ' . $productData['name'] . ' успешно обновлен');
            }
            else {
                StatusHelper::addLog('Не удалось обновить товар ' . $productData['name'] . ' - ' . $el->LAST_ERROR, 'error');
            }
        }
        else {
            $id = $el->Add($arFields);

            if ($id) {
                StatusHelper::addLog('Товар ' . $productData['name'] . ' успешно добавлен');
            } else {
                StatusHelper::addLog('Не удалось создать товар ' . $productData['name'] . ' - ' . $el->LAST_ERROR, 'error');
            }
        }

        if ($id && !empty($productData['price'])) {
            $priceLess = Option::get('mx.parser', 'price_less');
            $price = $productData['price'] - $productData['price'] * floatval($priceLess) / 100;
            $arPriceFields = Array(
                "PRODUCT_ID" => $id,
                "CATALOG_GROUP_ID" => "1",
                "PRICE" => $price,
                "CURRENCY" => "RUB",
            );

            $res = \CPrice::GetList(
                array(),
                array(
                    "PRODUCT_ID" => $id,
                    "CATALOG_GROUP_ID" => 1
                )
            );
            if ($arr = $res->Fetch()) {
                \CPrice::Update($arr['ID'], $arPriceFields);
            }
            else {
                \CPrice::Add($arPriceFields);
            }
        }
    }
}

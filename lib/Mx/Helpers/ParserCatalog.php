<?php

namespace Mx\Helpers;

use Bitrix\Main\Config\Option;
use Bitrix\Main\IO\FileNotFoundException;
use DOMDocument;
use DOMXPath;
use Mx\Parser;

class ParserCatalog
{
    private string $baseUrl = '';
    private $oDom = null;

    public function __construct() {
        $this->baseUrl = Option::get('mx.parser', 'url');
        $this->oDom = new DOMDocument('1.0', 'utf-8');
    }

    /**
     * @throws FileNotFoundException
     */
    public function process($step = StatusHelper::STATUS_END)
    {
        switch ($step) {
            case StatusHelper::STATUS_END:
            case StatusHelper::STATUS_MAIN_CATALOG:
            case StatusHelper::STATUS_DATA_EMPTY:
            case StatusHelper::STATUS_DATA_ERROR:
                $url = $this->baseUrl;
                StatusHelper::clearLog();
                StatusHelper::setStatusCode(StatusHelper::STATUS_MAIN_CATALOG);
                $parseData = $this->parseUrl($url);

                StatusHelper::addLog('Парсим список категорий...');

                if (!$parseData['data']) {
                    StatusHelper::addLog('Не удалось получить данные по адресу '.$url, 'error');
                    StatusHelper::setStatusCode(StatusHelper::STATUS_MAIN_CATALOG, $parseData['error']['message'], StatusHelper::STATUS_DATA_ERROR);
                    return;
                }

                $data = $this->getMainCatalogLinks($parseData['data']['content']);

                if (empty($data)) {
                    StatusHelper::addLog('Не удалось получить список категорий', 'error');
                    StatusHelper::setStatusCode(StatusHelper::STATUS_MAIN_CATALOG, false, StatusHelper::STATUS_DATA_EMPTY);
                    return;
                }
                StatusHelper::setList(json_encode($data), StatusHelper::STATUS_GET_SUBCATEGORIES);
            case StatusHelper::STATUS_GET_SUBCATEGORIES:
                $dataList = StatusHelper::getListData();

                if (!$dataList) {
                    StatusHelper::setStatusCode(StatusHelper::STATUS_GET_SUBCATEGORIES, false, StatusHelper::STATUS_DATA_ERROR);
                    return;
                }
                StatusHelper::addLog('Парсим список подкатегорий...');
                $categoryData = $this->getSubcategoryList($dataList);

                if (empty($categoryData)) {
                    StatusHelper::addLog('Не удалось получить список подкатегорий', 'error');
                    StatusHelper::setStatusCode(StatusHelper::STATUS_GET_SUBCATEGORIES, false, StatusHelper::STATUS_DATA_EMPTY);
                    return;
                }
                StatusHelper::setList(json_encode($categoryData), StatusHelper::STATUS_CREATING_CATEGORIES);
            case StatusHelper::STATUS_CREATING_CATEGORIES:
                if(!$dataList = StatusHelper::getListData()) {
                    StatusHelper::setStatusCode(StatusHelper::STATUS_CREATING_CATEGORIES, false, StatusHelper::STATUS_DATA_ERROR);
                    return;
                }

                StatusHelper::addLog('Добавляем категории в инфоблок...');
                $categoryData = $this->createCategory($dataList);
                StatusHelper::setList(json_encode($categoryData), StatusHelper::STATUS_PARSE_PRODUCT_LIST);
            case StatusHelper::STATUS_PARSE_PRODUCT_LIST:
                if(!$dataList = StatusHelper::getListData()) {
                    StatusHelper::setStatusCode(StatusHelper::STATUS_PARSE_PRODUCT_LIST, false, StatusHelper::STATUS_DATA_ERROR);
                    return;
                }
                StatusHelper::addLog('Парсим список товаров...');
                $productList = $this->parseProductList($dataList);

                if (empty($productList)) {
                    StatusHelper::addLog('Не удалось получить список товаров', 'error');
                    StatusHelper::setStatusCode(StatusHelper::STATUS_PARSE_PRODUCT_LIST, false, StatusHelper::STATUS_DATA_EMPTY);
                    return;
                }
                else {
                    StatusHelper::setList(json_encode($productList), StatusHelper::STATUS_PARSE_PRODUCTS);
                }
            case StatusHelper::STATUS_PARSE_PRODUCTS:
                if(!$dataList = StatusHelper::getListData()) {
                    StatusHelper::setStatusCode(StatusHelper::STATUS_PARSE_PRODUCTS, false, StatusHelper::STATUS_DATA_ERROR);
                    return;
                }
                StatusHelper::addLog('Парсим данные товаров...');
                $productList = $this->parseProductsDetail($dataList);

                if (empty($productList)) {
                    StatusHelper::addLog('Не удалось получить данные товаров...', 'error');
                    StatusHelper::setStatusCode(StatusHelper::STATUS_PARSE_PRODUCTS, false, StatusHelper::STATUS_DATA_EMPTY);
                    return;
                }
                else {
                    StatusHelper::setList(json_encode($productList), StatusHelper::STATUS_CREATING_PRODUCTS);
                }
            case StatusHelper::STATUS_CREATING_PRODUCTS:
                if(!$dataList = StatusHelper::getListData()) {
                    StatusHelper::setStatusCode(StatusHelper::STATUS_CREATING_PRODUCTS, false, StatusHelper::STATUS_DATA_ERROR);
                    return;
                }
                StatusHelper::addLog('Добавляем товары в инфоблок...');
                $this->createProduct($dataList);
                StatusHelper::setStatusCode(StatusHelper::STATUS_END);
                StatusHelper::addLog('Завершено');
        }
    }

    public function getDataFromXpath($sHtml, $xPath, $isFirst = false)
    {
        $this->oDom->loadHTML(
            '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'
            .$sHtml
        );

        $xpath = new DOMXPath($this->oDom);
        $nodeList = $xpath->query($xPath);
        if (!$isFirst) {
            return $nodeList;
        }
        else {
            if ($nodeList->count()) {
                return $nodeList->item(0);
            }
        }
        return false;
    }

    public function getLinksFromXpath($nodeList, $xPathLinks = false, $xpathImage = false): array
    {
        $data = [];

        foreach ($nodeList as $key => $node) {
            $link = '';
            $value = $node->nodeValue;
            $image = '';

            if (!empty($xPathLinks)) {
                $nodeHtml = $node->ownerDocument->saveHTML($node);
                $nodeLink = $this->getDataFromXpath($nodeHtml, $xPathLinks);

                if ($nodeLink) {
                    $link = $this->baseUrl.$nodeLink->item(0)->attributes->getNamedItem('href')->value;
                }
            }
            else {
                $link = $this->baseUrl.$node->attributes->getNamedItem('href')->value;
            }

            if ($xpathImage) {
                $nodeHtml = $node->ownerDocument->saveHTML($node);
                $nodeImage = $this->getDataFromXpath($nodeHtml, $xpathImage);

                if ($nodeImage) {
                    $image = $this->baseUrl.$nodeImage->item(0)->attributes->getNamedItem('src')->value;
                }
            }

            $data[] = [
                'link' =>  $link,
                'value' => $value,
                'image' => $image,
            ];
        }

        return $data;
    }

    public function getMainCatalogLinks($sHtml): array
    {
        $xpathCatalogLinkMenu = Option::get('mx.parser', 'xpathCatalogLinkMenu');
        $nodeList = $this->getDataFromXpath($sHtml, $xpathCatalogLinkMenu);
        return $this->getLinksFromXpath($nodeList);
    }

    public function getSubcategoryList($mainCategoryList): array
    {
        foreach ($mainCategoryList as &$arItem) {
            $arItem['children'] = $this->parseSubCategoryPage($arItem['link']);
        }

        return $mainCategoryList;
    }

    private function parseSubCategoryPage($url)
    {
        $parseData = $this->parseUrl($url);

        if (!$parseData['data']) {
            return null;
        }
        $xpathSubcategoryBlock = Option::get('mx.parser', 'xpathSubcategoryBlock');
        $xpathSubcategoryLink = Option::get('mx.parser', 'xpathSubcategoryLink');
        $xpathSubcategoryImage = Option::get('mx.parser', 'xpathSubcategoryImage');

        $nodeListLinks = $this->getDataFromXpath($parseData['data']['content'], $xpathSubcategoryBlock);

        if (!$nodeListLinks) {
            return false;
        }

        $dataLink = $this->getLinksFromXpath($nodeListLinks, $xpathSubcategoryLink, $xpathSubcategoryImage);

        foreach ($dataLink as &$arLink) {
            $arLink['children'] = $this->parseSubCategoryPage($arLink['link']);
        }

        return $dataLink;
    }

    private function parseProductList($categoryList): array
    {
        $paginationParam = Option::get('mx.parser', 'paginationParam');
        $xpathCard = Option::get('mx.parser', 'xpathCard');
        $productKey = Option::get('mx.parser', 'productKey', 'article');
        $productList = [];

        foreach ($categoryList as $categoryItem) {
            $url = $categoryItem['link'].$paginationParam;
            $parseData = $this->parseUrl($url);

            if ($parseData['data']) {
                $nodeListProduct = $this->getDataFromXpath($parseData['data']['content'], $xpathCard);

                if ($nodeListProduct) {
                    foreach ($nodeListProduct as $productNode) {
                        $productData = $this->getProductCardData($productNode);

                        if ($productData[$productKey]) {
                            if (!$productList[$productData[$productKey]]) {
                                $productData['category'] = [$categoryItem['id']];
                                $productList[$productData[$productKey]] = $productData;
                            }
                            else {
                                $productList[$productData[$productKey]]['category'][] = $categoryItem['id'];
                            }
                        }
                    }
                }
            }
        }

        return $productList;
    }

    private function parseProductsDetail($productList): array
    {
        $result = [];
        foreach ($productList as $productItem) {
            if (!$productItem['link']) {
                continue;
            }

            $parseData = $this->parseUrl($productItem['link']);

            if (!$parseData['data']) {
                $message = 'Не удалось получить данные '. $productItem['link'] . ' - ' .$parseData['error']['message'];
                StatusHelper::addLog($message, 'error');
                continue;
            }

            $data = $this->getProductDetailData($parseData['data']['content'], $productItem['link']);
            $data['category'] = $productItem['category'];
            $data['article'] = $productItem['article'];
            $data['name'] = $productItem['value'];

            $result[] = $data;
        }

        return $result;
    }

    private function getProductDetailData($sHtml, $link = ''): array
    {
        $xpathProductDetailPrice = Option::get('mx.parser', 'xpathProductDetailPrice');
        $xpathProductDetailChar = Option::get('mx.parser', 'xpathProductDetailChar');
        $xpathProductDetailImage = Option::get('mx.parser', 'xpathProductDetailImage');
        $xpathProductDetailBreadcrumbs = Option::get('mx.parser', 'xpathProductDetailBreadcrumbs');

        $price = $this->getDataFromXpath($sHtml, $xpathProductDetailPrice, true);
        $spec = $this->getDataFromXpath($sHtml, $xpathProductDetailChar, true);
        $image = $this->getDataFromXpath($sHtml, $xpathProductDetailImage, true);
        $breadcrumbsItems = $this->getDataFromXpath($sHtml, $xpathProductDetailBreadcrumbs);
        $catalogName = '';
        $specData = [];

        if ($price) {
            $price = $price->nodeValue;
            $price = str_replace([' ', ' '], '', $price);
            $price = floatval($price);
        }
        else {
            $message = 'Не удалось получить цену - '. $link;
            StatusHelper::addLog($message, 'warning');
        }

        if ($breadcrumbsItems->count() && $breadcrumbsItems->count() - 2 >= 1 ) {
            $catalogName = $breadcrumbsItems->item($breadcrumbsItems->count() - 2)->nodeValue;
        }
        else {
            $message = 'Не удалось получить имя категории - '. $link;
            StatusHelper::addLog($message, 'warning');
        }

        if ($spec) {
            $tableHtml = $spec->ownerDocument->saveHTML($spec);
            $xpathProductDetailCharRow = Option::get('mx.parser', 'xpathProductDetailCharRow');
            $xpathProductDetailCharCell = Option::get('mx.parser', 'xpathProductDetailCharCell');

            $trNodeList = $this->getDataFromXpath($tableHtml, $xpathProductDetailCharRow);

            if ($trNodeList) {
                foreach ($trNodeList as $tr) {
                    $trHtml = $tr->ownerDocument->saveHTML($tr);
                    $cellNodes = $this->getDataFromXpath($trHtml, $xpathProductDetailCharCell);

                    if ($cellNodes->count() >= 2) {
                        $specData[] = [
                            'name' => $cellNodes->item(0)->nodeValue,
                            'value' => $cellNodes->item(1)->nodeValue,
                        ];
                    }
                }
            }
        }

        if (empty($specData)) {
            $message = 'Не удалось получить характеристики - '. $link;
            StatusHelper::addLog($message, 'warning');
        }

        if (!$image) {
            $message = 'Не удалось получить картинку - '. $link;
            StatusHelper::addLog($message, 'warning');
        }

        return [
            'price' => $price,
            'image' => $image ? $this->baseUrl.$image->attributes->getNamedItem('src')->value : false,
            'categoryName' => $catalogName,
            'spec' => $specData,
        ];
    }

    private function getProductCardData($productNode): array
    {
        $xpathCardProductLink = Option::get('mx.parser', 'xpathCardProductLink');
        $xpathCardProductName = Option::get('mx.parser', 'xpathCardProductName');
        $xpathCardProductArticle = Option::get('mx.parser', 'xpathCardProductArticle');

        $nodeHtml = $productNode->ownerDocument->saveHTML($productNode);

        $link = $this->getDataFromXpath($nodeHtml, $xpathCardProductLink, true);
        $name = $this->getDataFromXpath($nodeHtml, $xpathCardProductName, true);
        $article = $this->getDataFromXpath($nodeHtml, $xpathCardProductArticle, true);

        if ($article) {
            if (preg_match('/(\d+)/', $article->nodeValue, $match)) {
                $article = $match[1];
            }
        }

        return [
            'link' => $link ? $this->baseUrl.$link->attributes->getNamedItem('href')->value : false,
            'value' => $name ? $name->nodeValue : false,
            'article' => $article
        ];
    }

    private function createProduct($productDataList)
    {
        foreach ($productDataList as $productData) {
            IblockHelper::processElement($productData);
        }
    }

    private function createCategory($categoryList, $parentId = 0): array
    {
        $newCategoryList = [];

        foreach ($categoryList as $category) {

            $categoryData = [
                'link' => $category['link'],
                'value' => $category['value'],
            ];

            if (!$id = IblockHelper::getSectionIdByName($category['value'], $parentId)) {
                $id = IblockHelper::addSection($category['value'], $category['image'], $parentId);
            }

            if ($id) {
                $categoryData['id'] = $id;
                $newCategoryList[] = $categoryData;

                if (!empty($category['children'])) {
                    $newCategoryList = array_merge($newCategoryList, $this->createCategory($category['children'], $id));
                }
            }
        }

        return $newCategoryList;
    }

    public function parseUrl($url)
    {
        return Parser::getPage(['url' => $url]);
    }
}

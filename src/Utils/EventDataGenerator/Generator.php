<?php

namespace Prokl\BitrixTestingTools\Utils\EventDataGenerator;

use Bitrix\Iblock\InheritedProperty\ElementTemplates;
use Bitrix\Main\Loader;
use CIBlock;
use CIBlockElement;

/**
 * Class Generator
 * @package Prokl\BitrixTestingTools\Utils\EventDataGenerator
 */
class Generator
{
    /**
     * Сгенерировать массив $arFields для тестов событий типа OnAfterIBlockElementAdd.
     *
     * @internal Для OnBeforeIBlockElementAdd достаточно удалить ID из результата.
     * Покоцанный код из ядра Битрикса.
     *
     * @param integer $iblockId  ID инфоблока.
     * @param integer $idElement ID элемента.
     *
     * @return array | false
     */
    public function generateFieldsOnAfterIBlockElementAdd(int $iblockId, int $idElement)
    {
        global $USER, $DB;

        $arFields = [
            'IBLOCK_ID' => $iblockId,
            'ID' => $idElement,
        ];

        $db_element = CIBlockElement::GetList(
            [],
            ['ID' => $idElement, 'SHOW_HISTORY' => 'Y'],
            false,
            false,
            [
                'ID',
                'TIMESTAMP_X',
                'MODIFIED_BY',
                'DATE_CREATE',
                'CREATED_BY',
                'IBLOCK_ID',
                'IBLOCK_SECTION_ID',
                'ACTIVE',
                'ACTIVE_FROM',
                'ACTIVE_TO',
                'SORT',
                'NAME',
                'PREVIEW_PICTURE',
                'PREVIEW_TEXT',
                'PREVIEW_TEXT_TYPE',
                'DETAIL_PICTURE',
                'DETAIL_TEXT',
                'DETAIL_TEXT_TYPE',
                'WF_STATUS_ID',
                'WF_PARENT_ELEMENT_ID',
                'WF_NEW',
                'WF_COMMENTS',
                'IN_SECTIONS',
                'CODE',
                'TAGS',
                'XML_ID',
                'TMP_ID',
            ]
        );

        if (!($arResult = $db_element->Fetch())) {
            return false;
        }

        $arFields['WF_PARENT_ELEMENT_ID'] = $idElement;

        if (array_key_exists("IBLOCK_SECTION_ID", $arResult)) {
            if (!array_key_exists("IBLOCK_SECTION", $arResult)) {
                $arFields["IBLOCK_SECTION"] = [$arResult["IBLOCK_SECTION_ID"]];
            } elseif (is_array($arResult["IBLOCK_SECTION"]) &&
                !in_array($arResult["IBLOCK_SECTION_ID"], $arResult["IBLOCK_SECTION"])) {
                unset($arFields["IBLOCK_SECTION_ID"]);
            }
        }

        $arFields['NAME'] = $arResult['NAME'];

        $ipropTemplates = new ElementTemplates($arFields["IBLOCK_ID"], 0);
        if (is_set($arResult, "PREVIEW_PICTURE")) {
            if (is_array($arResult["PREVIEW_PICTURE"])) {
                $arFields["PREVIEW_PICTURE"]["MODULE_ID"] = "iblock";
                $arFields["PREVIEW_PICTURE"]["name"] = \Bitrix\Iblock\Template\Helper::makeFileName(
                    $ipropTemplates,
                    "ELEMENT_PREVIEW_PICTURE_FILE_NAME",
                    $arResult,
                    $arResult["PREVIEW_PICTURE"]
                );
            }
        }

        if (is_set($arResult, "DETAIL_PICTURE")) {
            if (is_array($arResult["DETAIL_PICTURE"])) {
                $arFields["DETAIL_PICTURE"]["MODULE_ID"] = "iblock";
                $arFields["DETAIL_PICTURE"]["name"] = \Bitrix\Iblock\Template\Helper::makeFileName(
                    $ipropTemplates,
                    "ELEMENT_DETAIL_PICTURE_FILE_NAME",
                    $arResult,
                    $arResult["DETAIL_PICTURE"]
                );
            }
        }

        $arFields["ACTIVE"] = $arResult['ACTIVE'];
        if (is_set($arResult, "ACTIVE") && $arResult["ACTIVE"] != "Y") {
            $arFields["ACTIVE"] = "N";
        }

        $arFields["PREVIEW_TEXT_TYPE"] = $arResult['PREVIEW_TEXT_TYPE'];
        if (is_set($arResult, "PREVIEW_TEXT_TYPE") && $arResult["PREVIEW_TEXT_TYPE"] != "html") {
            $arFields["PREVIEW_TEXT_TYPE"] = "text";
        }

        $arFields["DETAIL_TEXT_TYPE"] = $arResult['DETAIL_TEXT_TYPE'];
        if (is_set($arResult, "DETAIL_TEXT_TYPE") && $arResult["DETAIL_TEXT_TYPE"] != "html") {
            $arFields["DETAIL_TEXT_TYPE"] = "text";
        }

        if (is_set($arResult, "DATE_ACTIVE_FROM")) {
            $arFields["ACTIVE_FROM"] = $arResult["DATE_ACTIVE_FROM"];
        }

        if (is_set($arResult, "DATE_ACTIVE_FROM")) {
            $arFields["ACTIVE_FROM"] = $arResult["DATE_ACTIVE_FROM"];
        }

        if (is_set($arResult, "ACTIVE_TO")) {
            $arFields["ACTIVE_TO"] = $arResult["ACTIVE_TO"];
        }

        if (is_set($arResult, "EXTERNAL_ID")) {
            $arFields["XML_ID"] = $arFields["EXTERNAL_ID"];
        }

        $arFields["SEARCHABLE_CONTENT"] = $arResult["NAME"];

        if (isset($arResult["PREVIEW_TEXT"])) {
            if (isset($arResult["PREVIEW_TEXT_TYPE"]) && $arResult["PREVIEW_TEXT_TYPE"] === "html") {
                $arFields["SEARCHABLE_CONTENT"] .= "\r\n".HTMLToTxt($arResult["PREVIEW_TEXT"]);
            } else {
                $arFields["SEARCHABLE_CONTENT"] .= "\r\n".$arResult["PREVIEW_TEXT"];
            }
        }
        if (isset($arResult["DETAIL_TEXT"])) {
            if (isset($arResult["DETAIL_TEXT_TYPE"]) && $arFields["$arResult"] === "html") {
                $arFields["SEARCHABLE_CONTENT"] .= "\r\n".HTMLToTxt($arResult["DETAIL_TEXT"]);
            } else {
                $arFields["SEARCHABLE_CONTENT"] .= "\r\n".$arResult["DETAIL_TEXT"];
            }
        }

        $arFields["SEARCHABLE_CONTENT"] = mb_strtoupper($arFields["SEARCHABLE_CONTENT"]);

        $arFields["CREATED_BY"] = $arResult["CREATED_BY"];
        $arFields["MODIFIED_BY"] = $arResult["MODIFIED_BY"];

        if (is_object($USER)) {
            if (!isset($arResult["CREATED_BY"]) || intval($arResult["CREATED_BY"]) <= 0) {
                $arFields["CREATED_BY"] = (int)$USER->GetID();
            }
            if (!isset($arResult["MODIFIED_BY"]) || intval($arResult["MODIFIED_BY"]) <= 0) {
                $arFields["MODIFIED_BY"] = (int)$USER->GetID();
            }
        }

        $arFields["~TIMESTAMP_X"] = $arFields["~DATE_CREATE"] = $DB->CurrentTimeFunction();

        if (array_key_exists("PREVIEW_PICTURE", $arResult)) {
            $arFields["PREVIEW_PICTURE_ID"] = $arResult["PREVIEW_PICTURE"];
            $arFields["PREVIEW_PICTURE"] = $arResult['PREVIEW_PICTURE'];
        }

        if (array_key_exists("DETAIL_PICTURE", $arResult)) {
            $arFields["DETAIL_PICTURE_ID"] = $arResult["DETAIL_PICTURE"];
            $arFields["DETAIL_PICTURE"] = $arResult['DETAIL_PICTURE'];
        }

        $arElement["PROPERTY_VALUES"] = [];

        $obElement = new CIBlockElement;
        $n = 1;
        $rsProperties = $obElement->GetProperty($arFields["IBLOCK_ID"], $arFields["ID"]);
        while ($arProperty = $rsProperties->Fetch()) {
            if (!array_key_exists($arProperty["ID"], $arElement["PROPERTY_VALUES"])) {
                $arElement["PROPERTY_VALUES"][$arProperty["ID"]] = [];
            }
            if ($arProperty["PROPERTY_TYPE"] === "F") {
                $arElement["PROPERTY_VALUES"][$arProperty["ID"]]["n".$n] = [
                    "VALUE" => $arProperty["VALUE"],
                    "DESCRIPTION" => $arProperty["DESCRIPTION"],
                ];
                $n++;
            } else {
                $arElement["PROPERTY_VALUES"][$arProperty["ID"]][$arProperty["PROPERTY_VALUE_ID"]] = [
                    "VALUE" => $arProperty["VALUE"],
                    "DESCRIPTION" => $arProperty["DESCRIPTION"],
                ];
            }
        }

        return array_merge($arFields, $arElement);
    }
}
<?php

namespace Prokl\BitrixTestingTools\Utils\EventDataGenerator;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CIBlock;
use CIBlockElement;

/**
 * Class OnAfterIBlockElementUpdate
 * @package Prokl\BitrixTestingTools\Utils\EventDataGenerator
 */
class OnAfterIBlockElementUpdate
{
    use PropValueTraits;

    /**
     * @param integer $idElement ID элемента.
     *
     * @return array|false
     *
     * @throws LoaderException
     */
    public function data(int $idElement)
    {
        $arFields = [];
        $bWorkFlow = false;

        $db_element = CIBlockElement::GetList(
            array(),
            array('ID' => $idElement, 'SHOW_HISTORY' => 'Y'),
            false,
            false,
            array(
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
            )
        );
        if (!($ar_element = $db_element->Fetch())) {
            return false;
        }

        $arIBlock = CIBlock::GetArrayByID($ar_element['IBLOCK_ID']);
        $bWorkFlow = $bWorkFlow && is_array($arIBlock) && ($arIBlock['WORKFLOW'] != 'N') && Loader::includeModule('workflow');

        $ar_wf_element = $ar_element;

        $LAST_ID = CIBlockElement::WF_GetLast($idElement);
        if ($LAST_ID != $idElement) {
            $db_element = CIBlockElement::GetByID($LAST_ID);
            if (!($ar_wf_element = $db_element->Fetch())) {
                return false;
            }
        }

        $arFields['WF_PARENT_ELEMENT_ID'] = $idElement;

        $arFields = $this->getPropertyValues(
            $arFields,
            $ar_element,
            $ar_wf_element
        );

        unset(
            $ar_wf_element['DATE_ACTIVE_FROM'],
            $ar_wf_element['DATE_ACTIVE_TO'],
            $ar_wf_element['EXTERNAL_ID'],
            $ar_wf_element['TIMESTAMP_X'],
            $ar_wf_element['IBLOCK_SECTION_ID'],
            $ar_wf_element['ID']
        );

        $arFields = $arFields + $ar_wf_element;

        $arFields['WF'] = ($bWorkFlow ? 'Y' : 'N');

        $bBizProc = is_array($arIBlock) && ($arIBlock['BIZPROC'] == 'Y') && IsModuleInstalled('bizproc');
        if (array_key_exists('BP_PUBLISHED', $arFields)) {
            if ($bBizProc) {
                if ($arFields['BP_PUBLISHED'] == 'Y') {
                    $arFields['WF_STATUS_ID'] = 1;
                    $arFields['WF_NEW'] = false;
                } else {
                    $arFields['WF_STATUS_ID'] = 2;
                    $arFields['WF_NEW'] = 'Y';
                    $arFields['BP_PUBLISHED'] = 'N';
                }
            } else {
                $arFields['WF_NEW'] = false;
                unset($arFields['BP_PUBLISHED']);
            }
        } else {
            $arFields['WF_NEW'] = false;
        }

        if ($this->is_set($arFields, 'ACTIVE') && $arFields['ACTIVE'] != 'Y') {
            $arFields['ACTIVE'] = 'N';
        }

        if ($this->is_set($arFields, 'PREVIEW_TEXT_TYPE') && $arFields['PREVIEW_TEXT_TYPE'] != 'html') {
            $arFields['PREVIEW_TEXT_TYPE'] = 'text';
        }

        if ($this->is_set($arFields, 'DETAIL_TEXT_TYPE') && $arFields['DETAIL_TEXT_TYPE'] != 'html') {
            $arFields['DETAIL_TEXT_TYPE'] = 'text';
        }

        $ipropTemplates = new \Bitrix\Iblock\InheritedProperty\ElementTemplates(
            $ar_element['IBLOCK_ID'],
            $ar_element['ID']
        );

        if (isset($arFields['PREVIEW_PICTURE']) && is_array($arFields['PREVIEW_PICTURE'])) {
            if ($arFields['PREVIEW_PICTURE']['name'] == ''
                && $arFields['PREVIEW_PICTURE']['del'] == ''
                && !$this->is_set($arFields['PREVIEW_PICTURE'], 'description')
            ) {
                unset($arFields['PREVIEW_PICTURE']);
            } else {
                $arFields['PREVIEW_PICTURE']['MODULE_ID'] = 'iblock';
                $arFields['PREVIEW_PICTURE']['old_file'] = $ar_wf_element['PREVIEW_PICTURE'];
                $arFields['PREVIEW_PICTURE']['name'] = \Bitrix\Iblock\Template\Helper::makeFileName(
                    $ipropTemplates,
                    'ELEMENT_PREVIEW_PICTURE_FILE_NAME',
                    array_merge($ar_element, $arFields),
                    $arFields['PREVIEW_PICTURE']
                );
            }
        }

        if (isset($arFields['DETAIL_PICTURE']) && is_array($arFields['DETAIL_PICTURE'])) {
            if ($arFields['DETAIL_PICTURE']['name'] == ''
                && $arFields['DETAIL_PICTURE']['del'] == ''
                && !$this->is_set($arFields['DETAIL_PICTURE'], 'description')
            ) {
                unset($arFields['DETAIL_PICTURE']);
            } else {
                $arFields['DETAIL_PICTURE']['MODULE_ID'] = 'iblock';
                $arFields['DETAIL_PICTURE']['old_file'] = $ar_wf_element['DETAIL_PICTURE'];
                $arFields['DETAIL_PICTURE']['name'] = \Bitrix\Iblock\Template\Helper::makeFileName(
                    $ipropTemplates,
                    'ELEMENT_DETAIL_PICTURE_FILE_NAME',
                    array_merge($ar_element, $arFields),
                    $arFields['DETAIL_PICTURE']
                );
            }
        }

        if ($this->is_set($arFields, 'DATE_ACTIVE_FROM')) {
            $arFields['ACTIVE_FROM'] = $arFields['DATE_ACTIVE_FROM'];
        }
        if ($this->is_set($arFields, 'DATE_ACTIVE_TO')) {
            $arFields['ACTIVE_TO'] = $arFields['DATE_ACTIVE_TO'];
        }
        if ($this->is_set($arFields, 'EXTERNAL_ID')) {
            $arFields['XML_ID'] = $arFields['EXTERNAL_ID'];
        }

        $PREVIEW_tmp = $this->is_set($arFields, 'PREVIEW_TEXT') ? $arFields['PREVIEW_TEXT'] : $ar_wf_element['PREVIEW_TEXT'];
        $PREVIEW_TYPE_tmp = $this->is_set(
            $arFields,
            'PREVIEW_TEXT_TYPE'
        ) ? $arFields['PREVIEW_TEXT_TYPE'] : $ar_wf_element['PREVIEW_TEXT_TYPE'];
        $DETAIL_tmp = $this->is_set($arFields, 'DETAIL_TEXT') ? $arFields['DETAIL_TEXT'] : $ar_wf_element['DETAIL_TEXT'];
        $DETAIL_TYPE_tmp = $this->is_set(
            $arFields,
            'DETAIL_TEXT_TYPE'
        ) ? $arFields['DETAIL_TEXT_TYPE'] : $ar_wf_element['DETAIL_TEXT_TYPE'];

        $arFields['SEARCHABLE_CONTENT'] = ToUpper(
            ($this->is_set($arFields, 'NAME') ? $arFields['NAME'] : $ar_wf_element['NAME'])."\r\n".
            ($PREVIEW_TYPE_tmp == 'html' ? HTMLToTxt($PREVIEW_tmp) : $PREVIEW_tmp)."\r\n".
            ($DETAIL_TYPE_tmp == 'html' ? HTMLToTxt($DETAIL_tmp) : $DETAIL_tmp)
        );

        if (array_key_exists('IBLOCK_SECTION_ID', $arFields)) {
            if (!array_key_exists('IBLOCK_SECTION', $arFields)) {
                $arFields['IBLOCK_SECTION'] = array($arFields['IBLOCK_SECTION_ID']);
            } elseif (is_array($arFields['IBLOCK_SECTION']) && !in_array(
                $arFields['IBLOCK_SECTION_ID'],
                $arFields['IBLOCK_SECTION']
            )) {
                unset($arFields['IBLOCK_SECTION_ID']);
            }
        }

        $arFields['IBLOCK_ID'] = $ar_element['IBLOCK_ID'];

        return $arFields;
    }
}

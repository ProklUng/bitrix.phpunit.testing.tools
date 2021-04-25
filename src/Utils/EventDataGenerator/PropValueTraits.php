<?php

namespace Prokl\BitrixTestingTools\Utils\EventDataGenerator;

use CFile;
use CIBlockElement;

/**
 * Trait PropValueTraits
 * @package Prokl\BitrixTestingTools\Utils\EventDataGenerator
 */
trait PropValueTraits
{

    /**
     * @param array $arFields
     * @param array $ar_element
     * @param array $ar_wf_element
     *
     * @return array
     */
    private function getPropertyValues(array $arFields, array $ar_element, array $ar_wf_element): array
    {
        if (!isset($arFields['PROPERTY_VALUES']) || !is_array($arFields['PROPERTY_VALUES'])) {
            $arFields['PROPERTY_VALUES'] = [];
        }

        $bFieldProps = [];
        foreach ($arFields['PROPERTY_VALUES'] as $k => $v) {
            $bFieldProps[$k] = true;
        }

        $arFieldProps = &$arFields['PROPERTY_VALUES'];
        $props = CIBlockElement::GetProperty($ar_element['IBLOCK_ID'], $ar_wf_element['ID']);
        while ($arProp = $props->Fetch()) {
            $pr_val_id = $arProp['PROPERTY_VALUE_ID'];
            if ($arProp['PROPERTY_TYPE'] === 'F' && $pr_val_id <> '') {
                if ($arProp['CODE'] <> '' && $this->is_set($arFieldProps, $arProp['CODE'])) {
                    $pr_id = $arProp['CODE'];
                } else {
                    $pr_id = $arProp['ID'];
                }

                if (array_key_exists($pr_id, $arFieldProps)
                    && array_key_exists($pr_val_id, $arFieldProps[$pr_id])
                    && is_array($arFieldProps[$pr_id][$pr_val_id])
                ) {
                    $new_value = $arFieldProps[$pr_id][$pr_val_id];
                    if ($new_value['name'] === ''
                        && $new_value['del'] !== 'Y'
                        && $new_value['VALUE']['name'] === ''
                        && $new_value['VALUE']['del'] !== 'Y'
                    ) {
                        if (array_key_exists('DESCRIPTION', $new_value)
                            && ($new_value['DESCRIPTION'] != $arProp['DESCRIPTION'])
                        ) {
                            $p = array('VALUE' => CFile::MakeFileArray($arProp['VALUE']));
                            $p['DESCRIPTION'] = $new_value['DESCRIPTION'];
                            $p['MODULE_ID'] = 'iblock';
                            $arFieldProps[$pr_id][$pr_val_id] = $p;
                        } elseif ($arProp['VALUE'] > 0) {
                            $arFieldProps[$pr_id][$pr_val_id] = array(
                                'VALUE' => $arProp['VALUE'],
                                'DESCRIPTION' => $arProp['DESCRIPTION'],
                            );
                        }
                    }
                } else {
                    $arFieldProps[$pr_id][$pr_val_id] = array(
                        'VALUE' => $arProp['VALUE'],
                        'DESCRIPTION' => $arProp['DESCRIPTION'],
                    );
                }

                continue;
            }

            if ($pr_val_id == ''
                || array_key_exists($arProp['ID'], $bFieldProps)
                || (
                    $arProp['CODE'] !== ''
                    && array_key_exists($arProp['CODE'], $bFieldProps)
                )
            ) {
                continue;
            }

            $arFieldProps[$arProp['ID']][$pr_val_id] = array(
                'VALUE' => $arProp['VALUE'],
                'DESCRIPTION' => $arProp['DESCRIPTION'],
            );
        }

        if ($ar_wf_element['IN_SECTIONS'] == 'Y') {
            $ar_wf_element['IBLOCK_SECTION'] = array();
            $rsSections = CIBlockElement::GetElementGroups(
                $ar_element['ID'],
                true,
                array('ID', 'IBLOCK_ELEMENT_ID')
            );
            while ($arSection = $rsSections->Fetch()) {
                $ar_wf_element['IBLOCK_SECTION'][] = $arSection['ID'];
            }
        }

        return $arFields;
    }

    /**
     * @param mixed $a
     * @param mixed $k
     *
     * @return boolean
     */
    private function is_set(&$a, $k = false) : bool
    {
        if ($k === false) {
            return isset($a);
        }

        if (is_array($a)) {
            return array_key_exists($k, $a);
        }

        return false;
    }
}
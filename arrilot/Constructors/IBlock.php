<?php

namespace Arrilot\BitrixMigrationsFork\Constructors;

use Arrilot\BitrixMigrationsFork\Logger;
use CIBlock;
use Exception;

/**
 * Class IBlock
 * @package Arrilot\BitrixMigrationsFork\Constructors
 */
class IBlock
{
    use FieldConstructor;

    /**
     * Добавить инфоблок.
     *
     * @return integer
     * @throws Exception
     */
    public function add() : int
    {
        $obj = new CIBlock();

        $iblockId = $obj->Add($this->getFieldsWithDefault());
        if (!$iblockId) {
            throw new Exception($obj->LAST_ERROR);
        }

        Logger::log("Добавлен инфоблок {$this->fields['CODE']}", Logger::COLOR_GREEN);

        return (int)$iblockId;
    }

    /**
     * Обновить инфоблок.
     *
     * @param integer $id
     *
     * @return void
     * @throws Exception
     */
    public function update(int $id) : void
    {
        $obj = new CIBlock();
        if (!$obj->Update($id, $this->fields)) {
            throw new Exception($obj->LAST_ERROR);
        }

        Logger::log("Обновлен инфоблок {$id}", Logger::COLOR_GREEN);
    }

    /**
     * Удалить инфоблок.
     *
     * @param integer $id ID инфоблока.
     *
     * @return void
     * @throws Exception
     */
    public static function delete(int $id) : void
    {
        if (!CIBlock::Delete($id)) {
            throw new Exception('Ошибка при удалении инфоблока');
        }

        Logger::log("Удален инфоблок {$id}", Logger::COLOR_GREEN);
    }

    /**
     * Установить настройки для добавления инфоблока по умолчанию.
     *
     * @param string $name
     * @param string $code
     * @param $iblock_type_id
     *
     * @return $this
     */
    public function constructDefault($name, $code, $iblock_type_id)
    {
        return $this->setName($name)->setCode($code)->setIblockTypeId($iblock_type_id);
    }

    /**
     * ID сайта.
     *
     * @param string $siteId ID сайта.
     *
     * @return $this
     */
    public function setSiteId(string $siteId) : self
    {
        $this->fields['SITE_ID'] = $siteId;

        return $this;
    }

    /**
     * Символьный идентификатор.
     *
     * @param string $code
     *
     * @return $this
     */
    public function setCode(string $code) : self
    {
        $this->fields['CODE'] = $code;

        return $this;
    }

    /**
     * Внешний код.
     *
     * @param string $xml_id
     *
     * @return $this
     */
    public function setXmlId(string $xml_id) : self
    {
        $this->fields['XML_ID'] = $xml_id;

        return $this;
    }

    /**
     * Код типа инфоблока.
     *
     * @param string $iblockTypeId
     * @return $this
     */
    public function setIblockTypeId($iblockTypeId) : self
    {
        $this->fields['IBLOCK_TYPE_ID'] = $iblockTypeId;

        return $this;
    }

    /**
     * Название.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name) : self
    {
        $this->fields['NAME'] = $name;

        return $this;
    }

    /**
     * Флаг активности.
     *
     * @param boolean $active
     *
     * @return $this
     */
    public function setActive($active = true) : self
    {
        $this->fields['ACTIVE'] = $active ? 'Y' : 'N';

        return $this;
    }

    /**
     * Индекс сортировки.
     *
     * @param integer $sort
     *
     * @return $this
     */
    public function setSort(int $sort = 500) : self
    {
        $this->fields['SORT'] = $sort;

        return $this;
    }

    /**
     * Шаблон URL-а к странице для публичного просмотра списка элементов информационного блока.
     *
     * @param string $listPageUrl
     *
     * @return $this
     */
    public function setListPageUrl(string $listPageUrl) : self
    {
        $this->fields['LIST_PAGE_URL'] = $listPageUrl;

        return $this;
    }

    /**
     * Шаблон URL-а к странице для просмотра раздела.
     *
     * @param string $sectionPageUrl
     *
     * @return $this
     */
    public function setSectionPageUrl(string $sectionPageUrl) : self
    {
        $this->fields['SECTION_PAGE_URL'] = $sectionPageUrl;

        return $this;
    }

    /**
     * Канонический URL элемента.
     *
     * @param string $canonicalPageUrl
     *
     * @return $this
     */
    public function setCanonicalPageUrl(string $canonicalPageUrl) : self
    {
        $this->fields['CANONICAL_PAGE_URL'] = $canonicalPageUrl;

        return $this;
    }

    /**
     * URL детальной страницы элемента.
     *
     * @param string $detailPageUrl
     *
     * @return $this
     */
    public function setDetailPageUrl(string $detailPageUrl) : self
    {
        $this->fields['DETAIL_PAGE_URL'] = $detailPageUrl;

        return $this;
    }

    /**
     * Устанавливает значения по умолчанию для страниц инфоблока, раздела и деталей элемента
     * (как при создании через административный интерфейс или с ЧПУ).
     *
     * Для использовании ЧПУ рекомендуется сделать обязательными для заполнения символьный код
     * элементов и разделов инфоблока.
     *
     * @param boolean sef Использовать ли ЧПУ (понадобится добавить правило в urlrewrite)
     *
     * @return $this
     */
    public function setDefaultUrls(bool $sef = false) : self
    {
        if ($sef === true) {
            $prefix = "#SITE_DIR#/#IBLOCK_TYPE_ID#/#IBLOCK_CODE#/";
            $this
                ->setListPageUrl($prefix)
                ->setSectionPageUrl("$prefix#SECTION_CODE_PATH#/")
                ->setDetailPageUrl("$prefix#SECTION_CODE_PATH#/#ELEMENT_CODE#/");
        } else {
            $prefix = "#SITE_DIR#/#IBLOCK_TYPE_ID#";
            $this
                ->setListPageUrl("$prefix/index.php?ID=#IBLOCK_ID#")
                ->setSectionPageUrl("$prefix/list.php?SECTION_ID=#SECTION_ID#")
                ->setDetailPageUrl("$prefix/detail.php?ID=#ELEMENT_ID#");
        }

        return $this;
    }

    /**
     * Код картинки в таблице файлов.
     *
     * @param array $picture
     *
     * @return $this
     */
    public function setPicture(array $picture) : self
    {
        $this->fields['PICTURE'] = $picture;

        return $this;
    }

    /**
     * Описание.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description) : self
    {
        $this->fields['DESCRIPTION'] = $description;

        return $this;
    }

    /**
     * Тип описания (text/html).
     *
     * @param string $descriptionType
     *
     * @return $this
     */
    public function setDescriptionType(string $descriptionType = 'text') : self
    {
        $this->fields['DESCRIPTION_TYPE'] = $descriptionType;

        return $this;
    }

    /**
     * Разрешен экспорт в RSS динамически.
     *
     * @param boolean $rssActive
     *
     * @return $this
     */
    public function setRssActive(bool $rssActive = true) : self
    {
        $this->fields['RSS_ACTIVE'] = $rssActive ? 'Y' : 'N';

        return $this;
    }

    /**
     * Время жизни RSS и интервал между генерациями файлов RSS (при включенном RSS_FILE_ACTIVE или RSS_YANDEX_ACTIVE) (часов).
     *
     * @param integer $rssTtl
     *
     * @return $this
     */
    public function setRssTtl(int $rssTtl = 24) : self
    {
        $this->fields['RSS_TTL'] = $rssTtl;

        return $this;
    }

    /**
     * Прегенерировать выгрузку в файл.
     *
     * @param boolean $rssFileActive
     *
     * @return $this
     */
    public function setRssFileActive($rssFileActive = false) : self
    {
        $this->fields['RSS_FILE_ACTIVE'] = $rssFileActive ? 'Y' : 'N';

        return $this;
    }

    /**
     * Количество экспортируемых в RSS файл элементов (при включенном RSS_FILE_ACTIVE).
     *
     * @param integer $rssFileLimit
     *
     * @return $this
     */
    public function setRssFileLimit($rssFileLimit) : self
    {
        $this->fields['RSS_FILE_LIMIT'] = $rssFileLimit;

        return $this;
    }

    /**
     * За сколько последних дней экспортировать в RSS файл.
     * (при включенном RSS_FILE_ACTIVE). -1 без ограничения по дням.
     *
     * @param integer $rssFileDays
     *
     * @return $this
     */
    public function setRssFileDays(int $rssFileDays) : self
    {
        $this->fields['RSS_FILE_DAYS'] = $rssFileDays;

        return $this;
    }

    /**
     * Экспортировать в RSS файл в формате для yandex.
     *
     * @param boolean $rssYandexActive
     * @return $this
     */
    public function setRssYandexActive(bool $rssYandexActive = false) : self
    {
        $this->fields['RSS_YANDEX_ACTIVE'] = $rssYandexActive ? 'Y' : 'N';

        return $this;
    }

    /**
     * Индексировать для поиска элементы информационного блока.
     *
     * @param boolean $indexElement
     *
     * @return $this
     */
    public function setIndexElement(bool $indexElement = true) : self
    {
        $this->fields['INDEX_ELEMENT'] = $indexElement ? 'Y' : 'N';

        return $this;
    }

    /**
     * Индексировать для поиска разделы информационного блока.
     *
     * @param boolean $indexSection
     *
     * @return $this
     */
    public function setIndexSection(bool $indexSection = false) : self
    {
        $this->fields['INDEX_SECTION'] = $indexSection ? 'Y' : 'N';

        return $this;
    }

    /**
     * Режим отображения списка элементов в административном разделе (S|C).
     *
     * @param string $listMode
     *
     * @return $this
     */
    public function setListMode(string $listMode) : self
    {
        $this->fields['LIST_MODE'] = $listMode;

        return $this;
    }

    /**
     * Режим проверки прав доступа (S|E).
     *
     * @param string $rightsMode
     *
     * @return $this
     */
    public function setRightsMode(string $rightsMode = 'S') : self
    {
        $this->fields['RIGHTS_MODE'] = $rightsMode;

        return $this;
    }

    /**
     * Признак наличия привязки свойств к разделам (Y|N).
     *
     * @param string $sectionProperty
     *
     * @return $this
     */
    public function setSectionProperty(string $sectionProperty) : self
    {
        $this->fields['SECTION_PROPERTY'] = $sectionProperty;

        return $this;
    }

    /**
     * Признак наличия фасетного индекса (N|Y|I).
     *
     * @param string $propertyIndex
     *
     * @return $this
     */
    public function setPropertyIndex(string $propertyIndex) : self
    {
        $this->fields['PROPERTY_INDEX'] = $propertyIndex;

        return $this;
    }

    /**
     * Служебное поле для процедуры конвертации места хранения значений свойств инфоблока.
     *
     * @param integer $lastConvElement
     *
     * @return $this
     */
    public function setLastConvElement($lastConvElement) : self
    {
        $this->fields['LAST_CONV_ELEMENT'] = $lastConvElement;

        return $this;
    }

    /**
     * Служебное поле для установки прав для разных групп на доступ к информационному блоку.
     *
     * @param array $groupId Массив соответствий кодов групп правам доступа
     *
     * @return $this
     */
    public function setGroupId(array $groupId) : self
    {
        $this->fields['GROUP_ID'] = $groupId;

        return $this;
    }

    /**
     * Служебное поле для привязки к группе социальной сети.
     *
     * @param integer $socnetGroupId
     *
     * @return $this
     */
    public function setSocnetGroupId(int $socnetGroupId) : self
    {
        $this->fields['SOCNET_GROUP_ID'] = $socnetGroupId;

        return $this;
    }

    /**
     * Инфоблок участвует в документообороте (Y|N).
     *
     * @param boolean $workflow
     *
     * @return $this
     */
    public function setWorkflow(bool $workflow = true) : self
    {
        $this->fields['WORKFLOW'] = $workflow ? 'Y' : 'N';

        return $this;
    }

    /**
     * Инфоблок участвует в бизнес-процессах (Y|N).
     *
     * @param boolean $bizproc
     *
     * @return $this
     */
    public function setBizProc(bool $bizproc = false) : self
    {
        $this->fields['BIZPROC'] = $bizproc ? 'Y' : 'N';

        return $this;
    }

    /**
     * Флаг выбора интерфейса отображения привязки элемента к разделам (D|L|P).
     *
     * @param string $sectionChooser
     *
     * @return $this
     */
    public function setSectionChooser(string $sectionChooser) : self
    {
        $this->fields['SECTION_CHOOSER'] = $sectionChooser;

        return $this;
    }

    /**
     * Флаг хранения значений свойств элементов инфоблока (1 - в общей таблице | 2 - в отдельной).
     *
     * @param integer $version
     *
     * @return $this
     */
    public function setVersion(int $version = 1) : self
    {
        $this->fields['VERSION'] = $version;

        return $this;
    }

    /**
     * Полный путь к файлу-обработчику массива полей элемента перед сохранением на странице редактирования элемента.
     *
     * @param string $editFileBefore
     *
     * @return $this
     */
    public function setEditFileBefore(string $editFileBefore) : self
    {
        $this->fields['EDIT_FILE_BEFORE'] = $editFileBefore;

        return $this;
    }

    /**
     * Полный путь к файлу-обработчику вывода интерфейса редактирования элемента.
     *
     * @param string $editFileAfter
     *
     * @return $this
     */
    public function setEditFileAfter(string $editFileAfter) : self
    {
        $this->fields['EDIT_FILE_AFTER'] = $editFileAfter;

        return $this;
    }

    /**
     * Название элемента в единственном числе.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessElementName(string $message = 'Элемент') : self
    {
        $this->fields['ELEMENT_NAME'] = $message;

        return $this;
    }

    /**
     * Название элемента во множнственном числе.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessElementsName(string $message = 'Элементы') : self
    {
        $this->fields['ELEMENTS_NAME'] = $message;

        return $this;
    }

    /**
     * Действие по добавлению элемента.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessElementAdd(string $message = 'Добавить элемент') : self
    {
        $this->fields['ELEMENT_ADD'] = $message;

        return $this;
    }

    /**
     * Действие по редактированию/изменению элемента.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessElementEdit(string $message = 'Изменить элемент') : self
    {
        $this->fields['ELEMENT_EDIT'] = $message;

        return $this;
    }

    /**
     * Действие по удалению элемента.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessElementDelete(string $message = 'Удалить элемент') : self
    {
        $this->fields['ELEMENT_DELETE'] = $message;

        return $this;
    }

    /**
     * Название раздела в единственном числе.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessSectionName(string $message = 'Раздел') : self
    {
        $this->fields['SECTION_NAME'] = $message;

        return $this;
    }

    /**
     * Название раздела во множнственном числе.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessSectionsName(string $message = 'Разделы') : self
    {
        $this->fields['SECTIONS_NAME'] = $message;

        return $this;
    }

    /**
     * Действие по добавлению раздела.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessSectionAdd(string $message = 'Добавить раздел') : self
    {
        $this->fields['SECTION_ADD'] = $message;

        return $this;
    }

    /**
     * Действие по редактированию/изменению раздела.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessSectionEdit(string $message = 'Изменить раздел') : self
    {
        $this->fields['SECTION_EDIT'] = $message;

        return $this;
    }

    /**
     * Действие по удалению раздела.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessSectionDelete(string $message = 'Удалить раздел') : self
    {
        $this->fields['SECTION_DELETE'] = $message;

        return $this;
    }


}

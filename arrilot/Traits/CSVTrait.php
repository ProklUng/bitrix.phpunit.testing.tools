<?php

namespace Arrilot\BitrixMigrationsFork\Traits;

use Arrilot\BitrixMigrationsFork\BaseMigrations\BitrixMigration;
use Arrilot\BitrixMigrationsFork\Exceptions\MigrationException;
use Bitrix\Main\Type\DateTime;
use CFile;
use CIBlockElement;
use CIBlockSection;
use CUtil;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

/**
 * @mixin BitrixMigration
 */
trait CSVTrait
{
    /**
     * @var $sectionCache
     */
    private $sectionCache;

    /**
     * @return void
     * @throws MigrationException
     */
    protected function importCsv()
    {
        if (!$file = fopen($this->getCsvPath(), 'rb')) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new MigrationException('There is no csv file to import');
        }

        $iblockId = $this->getIblockIdByCode($this->getIblockCode());

        $element = new CIBlockElement();

        $count = 0;
        while ($record = fgetcsv($file, 0, ';')) {
            $sections = [];
            foreach ($this->getImportDefinitionSections() as $sectionKey) {
                $sections[] = $record[$sectionKey];
            }

            $sectionId = $this->getSectionId((array)$sections);

            $fields = [
                'IBLOCK_ID'         => $iblockId,
                'IBLOCK_SECTION_ID' => $sectionId,
            ];

            foreach ($this->getImportDefinitionFields() as $code => $csvKey) {
                if ($code === 'ACTIVE_FROM' || $code === 'ACTIVE_TO') {
                    try {
                        $dateData = new DateTimeImmutable($record[$csvKey]);
                    } catch (\Exception $e) {
                        continue;
                    }

                    $dateObj = self::dateTimeImmutableToBitrixStringDateTime($dateData);
                    $fields[$code] = $dateObj;
                    continue;
                }

                $fields[$code] = $record[$csvKey];
            }

            foreach ($this->getImportDefinitionProperties() as $code => $csvKey) {
                if ($record[$csvKey] !== '') {
                    if (in_array($code, $this->getFileProperties(), true)) {
                        $fields['PROPERTY_VALUES'][$code] = $this->getFileArray($record[$csvKey]);
                    } else {
                        $fields['PROPERTY_VALUES'][$code] = $record[$csvKey];
                    }
                }
            }

            // Ошибки добавления элемента игнорируются, чтобы не заморачиваться определением первой строчки CSV
            // файла с названиями столбцов.
            $element->Add($fields, false, false);
        }

        fclose($file);
    }

    /**
     * @param string[] $sections
     *
     * @return integer
     * @throws MigrationException
     */
    private function getSectionId($sections)
    {
        $sections = array_filter($sections);

        if (count($sections) === 0) {
            return false;
        }

        $hash = md5(implode('|', $sections));
        if (empty($this->sectionCache[$hash])) {
            $sectionCurrent = count($sections) > 1 ?  array_pop($sections) : $sections;

            $parentId = $this->getSectionId(...$sections);
            $fields = [
                'NAME'              => current($sectionCurrent),
                'CODE'              => CUtil::translit(current($sectionCurrent), 'ru', [
                    'replace_space' => '-',
                    'replace_other' => '-',
                ]),
                'IBLOCK_SECTION_ID' => $parentId,
                'IBLOCK_ID'         => $this->getIblockIdByCode($this->getIblockCode()),
            ];

            $section = new CIBlockSection();
            $id = $section->Add($fields);
            if (!$id) {
                throw new MigrationException('Error while adding a section: ' . $section->LAST_ERROR);
            }

            $this->sectionCache[$hash] = $id;
        }

        return $this->sectionCache[$hash];
    }

    /**
     * @return array
     * @ToDo  Окультурить.
     */
    protected function getImportDefinitionFields() : array
    {
        return [
            'XML_ID'       => 0,
            'NAME'         => 1,
            'ACTIVE'       => 3,
            'ACTIVE_FROM'  => 4,
            'ACTIVE_TO'    => 5,
            'PREVIEW_TEXT' => 7,
            'PREVIEW_TEXT_TYPE' => 8,
            'DETAIL_TEXT'  => 10,
            'DETAIL_TEXT_TYPE'  => 11,
            'CODE'         => 12,
            'SORT'         => 13,
        ];
    }

    /**
     * @return array
     */
    protected function getFileProperties() {
        return [];
    }

    /**
     * @param string $path Path to the file beginning with 'upload/iblock'.
     *
     * @return array
     * @throws MigrationException
     */
    private function getFileArray($path)
    {
        $path = array_filter(explode('/', $path));
        array_shift($path);
        array_shift($path);
        $dir = pathinfo($this->getCsvPath(), PATHINFO_FILENAME);
        // ToDo Пути!
        $path = dirname(dirname(__DIR__)) . "/migrations/data/$dir/" . implode('/', $path);

        if (!file_exists($path)) {
            throw new MigrationException('File not found: ' . $path);
        }

        return CFile::MakeFileArray($path);
    }

    /**
     * Конвертирует строку с датой и временем в формате сайта в объект DateTimeImmutable
     *
     * @param string $dateTime
     * @param string|bool $fromSite Идентификатор сайта, в формате которого было задано время time.
     *      Необязательный параметр. По умолчанию - текущий сайт.
     * @param boolean $searchInSitesOnly Необязательный параметр. По умолчанию - "false", текущий сайт.
     * @param DateTimeZone|null $timeZone
     *
     * @return bool|DateTimeImmutable false при ошибке.
     *
     * @link https://dev.1c-bitrix.ru/api_help/main/functions/date/convertdatetime.php
     */
    private static function bitrixStringDateTimeToDateTimeImmutable(
        string $dateTime,
        $fromSite = false,
        bool $searchInSitesOnly = false,
        DateTimeZone $timeZone = null
    ) {
        return DateTimeImmutable::createFromFormat(
            'Y-m-d\TH:i:s',
            sprintf(
                '%sT%s',
                ConvertDateTime($dateTime, 'YYYY-MM-DD', $fromSite, $searchInSitesOnly),
                ConvertDateTime($dateTime, 'HH:MI:SS', $fromSite, $searchInSitesOnly)
            ),
            $timeZone
        );
    }

    /**
     * Конвертирует объект DateTimeImmutable в строку с датой и временем в формате сайта.
     *
     * @param DateTimeImmutable $dateTimeImmutable
     * @param string $type Тип формата. Допустимы следующие значения:
     *      <ul>
     *          <li>FULL - полный (дата и время)</li>
     *          <li>SHORT - короткий (дата)</li>
     *      </ul>
     * @param boolean $site Идентификатор сайта, в формате которого необходимо вернуть дату.
     *      Необязательный параметр. По умолчанию - текущий сайт.
     * @param boolean $searchInSitesOnly Искать только на сайте.
     *      Необязательный параметр. По умолчанию - "false" текущий сайт.
     *
     * @return string
     *
     * @link https://dev.1c-bitrix.ru/api_help/main/functions/date/converttimestamp.php
     */
    private static function dateTimeImmutableToBitrixStringDateTime(
        DateTimeImmutable $dateTimeImmutable,
        $type = 'SHORT',
        $site = false,
        bool $searchInSitesOnly = false
    ): string {
        $timestamp = $dateTimeImmutable->getTimestamp();
        if (!$timestamp) {
            return '';
        }

        return ConvertTimeStamp(
            $timestamp,
            $type,
            $site,
            $searchInSitesOnly
        );
    }

    /**
     * Конвертирует объект DateTimeImmutable в Битриксовый объект DateTime
     *
     * @param DateTimeImmutable $dateTimeImmutable
     *
     * @return DateTime
     */
    private static function dateTimeImmutableToBitrixDateTime(DateTimeImmutable $dateTimeImmutable): DateTime
    {
        return DateTime::createFromPhp(
            \DateTime::createFromFormat(
                DATE_ISO8601,
                $dateTimeImmutable->format(DATE_ISO8601),
                /**
                 * Позволяет сохранить временную зону в точности так, как она была у исходной даты.
                 */
                $dateTimeImmutable->getTimezone()
            )
        );
    }

    /**
     * @return string
     */
    abstract protected function getIblockCode();

    /**
     * @return array
     */
    abstract protected function getImportDefinitionSections();

    /**
     * @return array
     */
    abstract protected function getImportDefinitionProperties();

    /**
     * @return string
     */
    abstract protected function getCsvPath();
}
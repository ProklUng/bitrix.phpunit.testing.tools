<?php

namespace Arrilot\BitrixMigrationsFork\BaseMigrations;

use Arrilot\BitrixMigrationsFork\Exceptions\MigrationException;
use Arrilot\BitrixMigrationsFork\Interfaces\MigrationInterface;
use Bitrix\Main\Application;
use Bitrix\Main\DB\Connection;
use CIBlock;
use CIBlockProperty;
use CUserTypeEntity;

/**
 * Class BitrixMigration
 * @package Arrilot\BitrixMigrationsFork\BaseMigrations
 */
class BitrixMigration implements MigrationInterface
{
    /**
     * DB connection.
     *
     * @var Connection
     */
    protected $db;

    /**
     * @var boolean|null $use_transaction
     */
    public $use_transaction = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->db = Application::getConnection();
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidReturnType
     */
    public function up()
    {
        //
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidReturnType
     */
    public function down()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function useTransaction($default = false)
    {
        if (!is_null($this->use_transaction)) {
            return $this->use_transaction;
        }

        return $default;
    }

    /**
     * Find iblock id by its code.
     *
     * @param string      $code       Код инфоблока.
     * @param null|string $iBlockType Тип инфоблока.
     *
     * @throws MigrationException
     *
     * @return integer
     */
    protected function getIblockIdByCode(string $code, ?string $iBlockType = null) : int
    {
        if (!$code) {
            throw new MigrationException('Не задан код инфоблока');
        }

        $filter = [
            'CODE'              => $code,
            'CHECK_PERMISSIONS' => 'N',
        ];

        if ($iBlockType !== null) {
            $filter['TYPE'] = $iBlockType;
        }

        $iblock = (new CIBlock())->GetList([], $filter)->fetch();

        if (!$iblock['ID']) {
            throw new MigrationException("Не удалось найти инфоблок с кодом '{$code}'");
        }

        return (int)$iblock['ID'];
    }

    /**
     * Delete iblock by its code.
     *
     * @param string $code Код инфоблока.
     *
     * @throws MigrationException Когда не удалось удалить инфоблок.
     *
     * @return void
     */
    protected function deleteIblockByCode(string $code) : void
    {
        $id = $this->getIblockIdByCode($code);

        $this->db->startTransaction();
        if (!CIBlock::Delete($id)) {
            $this->db->rollbackTransaction();
            throw new MigrationException('Ошибка при удалении инфоблока');
        }

        $this->db->commitTransaction();
    }

    /**
     * Add iblock element property.
     *
     * @param array $fields Значения полей.
     *
     * @return integer
     * @throws MigrationException
     */
    public function addIblockElementProperty(array $fields)
    {
        $ibp = new CIBlockProperty();
        $propId = $ibp->add($fields);

        if (!$propId) {
            throw new MigrationException('Ошибка при добавлении свойства инфоблока '.$ibp->LAST_ERROR);
        }

        return $propId;
    }

    /**
     * Delete iblock element property.
     *
     * @param integer $iblockId ID инфоблока.
     * @param string  $code     Код инфоблока.
     *
     * @return void
     *
     * @throws MigrationException
     */
    public function deleteIblockElementPropertyByCode($iblockId, string $code)
    {
        if (!$iblockId) {
            throw new MigrationException('Не задан ID инфоблока');
        }

        if (!$code) {
            throw new MigrationException('Не задан код свойства');
        }

        $id = $this->getIblockPropIdByCode($code, $iblockId);

        CIBlockProperty::Delete($id);
    }

    /**
     * Add User Field.
     *
     * @param array $fields Значения полей.
     *
     * @throws MigrationException
     *
     * @return integer
     */
    public function addUF(array $fields)
    {
        if (!$fields['FIELD_NAME']) {
            throw new MigrationException('Не заполнен FIELD_NAME');
        }

        if (!$fields['ENTITY_ID']) {
            throw new MigrationException('Не заполнен код ENTITY_ID');
        }

        $oUserTypeEntity = new CUserTypeEntity();

        $fieldId = $oUserTypeEntity->Add($fields);

        if (!$fieldId) {
            throw new MigrationException(
                "Не удалось создать пользовательское свойство с FIELD_NAME = {$fields['FIELD_NAME']} и ENTITY_ID = {$fields['ENTITY_ID']}"
            );
        }

        return $fieldId;
    }

    /**
     * Get UF by its code.
     *
     * @param string $entity Сущность свойства.
     * @param string $code   Код свойства.
     *
     * @return integer
     * @throws MigrationException
     */
    public function getUFIdByCode(string $entity, string $code)
    {
        if (!$entity) {
            throw new MigrationException('Не задана сущность свойства');
        }

        if (!$code) {
            throw new MigrationException('Не задан код свойства');
        }

        $filter = [
            'ENTITY_ID'  => $entity,
            'FIELD_NAME' => $code,
        ];

        $arField = CUserTypeEntity::GetList(['ID' => 'ASC'], $filter)->fetch();
        if (!$arField || !$arField['ID']) {
            throw new MigrationException("Не найдено свойство с FIELD_NAME = {$filter['FIELD_NAME']} и ENTITY_ID = {$filter['ENTITY_ID']}");
        }

        return $arField['ID'];
    }

    /**
     * @param string  $code
     * @param integer $iblockId
     *
     * @throws MigrationException
     *
     * @return integer
     */
    protected function getIblockPropIdByCode(string $code, int $iblockId) : int
    {
        $filter = [
            'CODE'      => $code,
            'IBLOCK_ID' => $iblockId,
        ];

        $prop = CIBlockProperty::getList(['sort' => 'asc', 'name' => 'asc'], $filter)->getNext();
        if (!$prop || !$prop['ID']) {
            throw new MigrationException("Не удалось найти свойство с кодом '{$code}'");
        }

        return (int)$prop['ID'];
    }

    /**
     * @param integer        $idIblock ID инфоблока.
     * @param integer|string $code     Код свойства.
     * @param array          $fields   Поля.
     *
     * @return void
     * @throws MigrationException
     */
    public function updateProperty(int $idIblock, $code, array $fields) : void
    {
        if ($idIblock <= 0) {
            throw new MigrationException('You must set iblock id due to ambiguity avoiding');
        }

        $arProperty = CIBlockProperty::GetList([], ['IBLOCK_ID' => $idIblock, 'CODE' => $code])->Fetch();
        if (!$arProperty['ID']) {
            throw new MigrationException(sprintf('Can\'t find property "%s" in iblock %d', $code, $idIblock));
        }

        $prop = new CIBlockProperty();
        if (!$prop->Update($arProperty['ID'], $fields)) {
            throw new MigrationException(sprintf('Can\'t update property "%s" with error: %s', $code, $prop->LAST_ERROR));
        }
    }
}

<?php

namespace App\Service;

use App\Entity\DbChange;

class LogChangesService
{
    private const ACTION_UPDATE = 'update';
    private const ACTION_INSERT = 'insert';
    private const ACTION_DELETE = 'delete';
    private const DEFAULT_USER_ID = 1;

    public function logEntityUpdate(
        string $entityId,
        string $tableName,
        string $columnName,
        string $oldValue,
        string $newValue
    ): DbChange {
        $dbChange = new DbChange();
        $dbChange
            ->setCreatedAt(new \DateTime())
            ->setUserId($this->getAuthorizedUserId())
            ->setTableName($tableName)
            ->setEntityId($entityId)
            ->setAction(self::ACTION_UPDATE)
            ->setFieldName($columnName)
            ->setOldValue($oldValue)
            ->setNewValue($newValue);

        return $dbChange;
    }

    public function logEntityInsert(string $entityId, string $tableName): DbChange
    {
        $dbChange = new DbChange();
        $dbChange
            ->setCreatedAt(new \DateTime())
            ->setUserId($this->getAuthorizedUserId())
            ->setTableName($tableName)
            ->setEntityId($entityId)
            ->setAction(self::ACTION_INSERT)
            ->setFieldName('*')
            ->setOldValue('')
            ->setNewValue('');

        return $dbChange;
    }

    public function logEntityDelete(string $entityId, string $tableName): DbChange
    {
        $dbChange = new DbChange();
        $dbChange
            ->setCreatedAt(new \DateTime())
            ->setUserId($this->getAuthorizedUserId())
            ->setTableName($tableName)
            ->setEntityId($entityId)
            ->setAction(self::ACTION_DELETE)
            ->setFieldName('*')
            ->setOldValue('')
            ->setNewValue('');

        return $dbChange;
    }

    /**
     * TODO use real user id
     */
    private function getAuthorizedUserId(): string
    {
        return (string) self::DEFAULT_USER_ID;
    }
}

<?php

namespace App\EventSubscriber;

use App\Entity\DbChange;
use App\Service\LogChangesService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

class EntitySubscriber implements EventSubscriber
{
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var LogChangesService
     */
    private $logChangesService;

    /**
     * @var array
     */
    private $insertedEntities = [];

    /**
     * EntitySubscriber constructor.
     * @param LogChangesService $logChangesService
     */
    public function __construct(
        LogChangesService $logChangesService
    )
    {
        $this->logChangesService = $logChangesService;
    }

    /**
     * @param OnFlushEventArgs $args
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $updatedEntities = $unitOfWork->getScheduledEntityUpdates();
        $deletedEntities = $unitOfWork->getScheduledEntityDeletions();
        $this->insertedEntities = $unitOfWork->getScheduledEntityInsertions();

        foreach ($updatedEntities as $updatedEntity) {
            // skip for DbChange entity
            if ($updatedEntity instanceof DbChange) {
                continue;
            }

            $changeSet = $unitOfWork->getEntityChangeSet($updatedEntity);

            // get metadata
            $entityClassName = get_class($updatedEntity);
            $metaData = $entityManager->getClassMetadata($entityClassName);

            // get entity id
            $entityId = $this->getEntityId($unitOfWork, $updatedEntity);

            // get table name
            $tableName = $metaData->getTableName();

            foreach ($changeSet as $fieldName => $changes) {
                $oldValue = array_key_exists(0, $changes) ? $changes[0] : null;
                $newValue = array_key_exists(1, $changes) ? $changes[1] : null;
                $columnName = $metaData->getFieldMapping($fieldName)['columnName'];

                if ($oldValue !== $newValue) {
                    $oldValue = $this->convertValueToString($oldValue);
                    $newValue = $this->convertValueToString($newValue);

                    $logChange = $this->logChangesService->logEntityUpdate(
                        $entityClassName,
                        $entityId,
                        $tableName,
                        $columnName,
                        $oldValue,
                        $newValue
                    );

                    $entityManager->persist($logChange);

                    $logMetadata = $entityManager->getClassMetadata(DbChange::class);
                    $unitOfWork->computeChangeSet($logMetadata, $logChange);
                }
            }
        }

        foreach ($deletedEntities as $deletedEntity) {
            // skip for LogChange entity
            if ($deletedEntity instanceof DbChange) {
                continue;
            }

            // get metadata
            $entityClassName = get_class($deletedEntity);
            $metaData = $entityManager->getClassMetadata($entityClassName);

            // get entity id
            $entityId = $this->getEntityId($unitOfWork, $deletedEntity);

            // get table name
            $tableName = $metaData->getTableName();

            $logChange = $this->logChangesService->logEntityDelete(
                $entityClassName,
                $entityId,
                $tableName
            );

            $entityManager->persist($logChange);

            $logMetadata = $entityManager->getClassMetadata(DbChange::class);
            $unitOfWork->computeChangeSet($logMetadata, $logChange);
        }
    }

    /**
     * @param PostFlushEventArgs $args
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($this->insertedEntities as $insertedEntity) {
            if ($insertedEntity instanceof DbChange) {
                continue;
            }

            // get metadata
            $entityClassName = get_class($insertedEntity);
            $metaData = $entityManager->getClassMetadata($entityClassName);

            // get entity id
            $entityId = $this->getEntityId($unitOfWork, $insertedEntity);

            // get table name
            $tableName = $metaData->getTableName();

            $logChange = $this->logChangesService->logEntityInsert(
                $entityClassName,
                $entityId,
                $tableName
            );

            $entityManager->persist($logChange);
            $entityManager->flush();
        }
    }

    /**
     * @param UnitOfWork $unitOfWork
     * @param $entity
     * @return integer
     */
    private function getEntityId(UnitOfWork $unitOfWork, $entity)
    {
        $identifier = $unitOfWork->getEntityIdentifier($entity);
        $idFieldName = array_key_first($identifier);
        $entityId = $identifier[$idFieldName];

        return $entityId;
    }

    public function convertValueToString($value): string
    {
        if($value instanceof \DateTime){
            $value = $value->format(self::DATETIME_FORMAT);
        }

        return (string) $value;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
            'postFlush'
        ];
    }
}
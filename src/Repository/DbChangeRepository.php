<?php

namespace App\Repository;

use App\Entity\DbChange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method DbChange|null find($id, $lockMode = null, $lockVersion = null)
 * @method DbChange|null findOneBy(array $criteria, array $orderBy = null)
 * @method DbChange[]    findAll()
 * @method DbChange[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DbChangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DbChange::class);
    }

    // /**
    //  * @return DbChange[] Returns an array of DbChange objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DbChange
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

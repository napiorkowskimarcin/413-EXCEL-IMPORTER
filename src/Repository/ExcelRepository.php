<?php

namespace App\Repository;

use App\Entity\Excel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Excel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Excel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Excel[]    findAll()
 * @method Excel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExcelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Excel::class);
    }

    // /**
    //  * @return Excel[] Returns an array of Excel objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Excel
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
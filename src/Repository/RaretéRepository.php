<?php

namespace App\Repository;

use App\Entity\Rareté;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rareté>
 *
 * @method Rareté|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rareté|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rareté[]    findAll()
 * @method Rareté[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RaretéRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rareté::class);
    }

    //    /**
    //     * @return Rareté[] Returns an array of Rareté objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Rareté
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

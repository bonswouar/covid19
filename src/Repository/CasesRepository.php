<?php

namespace App\Repository;

use App\Entity\Cases;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Cases|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cases|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cases[]    findAll()
 * @method Cases[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CasesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cases::class);
    }

    public function findOneByCountryAndDate($country, $date): ?Cases
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.country = :country and d.date = :date')
            ->setParameters(['country' => $country, 'date' => $date])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllByDate()
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.country', 'c')
            ->addOrderBy('d.date', 'DESC')
            ->addOrderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByCountries(array $countries)
    {
        return $this->createQueryBuilder('d')
            ->join('d.country', 'c')
            ->orderBy('d.date', 'ASC')
            ->where('c.code IN (:countries)')
            ->setParameters(['countries' => $countries])
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByCountry($country)
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.date', 'ASC')
            ->andWhere('d.country = :country')
            ->setParameters(['country' => $country])
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Cases[] Returns an array of Cases objects
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
    public function findOneBySomeField($value): ?Cases
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

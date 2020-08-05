<?php

namespace App\Repository;

use App\Entity\Cases;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

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
}

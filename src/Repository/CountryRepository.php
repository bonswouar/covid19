<?php

namespace App\Repository;

use App\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Country|null find($id, $lockMode = null, $lockVersion = null)
 * @method Country|null findOneBy(array $criteria, array $orderBy = null)
 * @method Country[]    findAll()
 * @method Country[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CountryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Country::class);
    }

    public function findOneByCodeAndName($code, $name): ?Country
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.code = :code and c.name = :name')
            ->setParameters(['code' => $code, 'name' => $name])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByCodes(array $codes)
    {
        return $this->createQueryBuilder('d')
            ->where('c.code IN (:countries)')
            ->setParameters(['countries' => $countries])
            ->getQuery()
            ->getResult()
        ;
    }

    public function findIfCaseRank($casesMin, $maxCountries)
    {
        $result = $this->createQueryBuilder('c')
            ->select( 'c.id, c.code, c.name, c.disabled, SUM(d.cases) as totalCases')
            ->orderBy('totalCases', 'desc')
            ->join('c.cases', 'd')
            ->groupBy('c.id')
            ->having('totalCases >= :casesMin and (c.disabled != 1 or c.disabled is null)')
            ->setParameters(['casesMin' => $casesMin])
            ->setMaxResults($maxCountries)
            ->getQuery()
            ->getResult()
        ;
        return $result;
    }

    public function findIfDeathRank($deathsMin, $maxCountries)
    {
        $result = $this->createQueryBuilder('c')
            ->select( 'c.id, c.code, c.name, c.disabled, SUM(d.deaths) as totalDeaths')
            ->orderBy('totalDeaths', 'desc')
            ->join('c.cases', 'd')
            ->groupBy('c.id')
            ->having('totalDeaths >= :deathsMin and (c.disabled != 1 or c.disabled is null)')
            ->setParameters(['deathsMin' => $deathsMin])
            ->setMaxResults($maxCountries)
            ->getQuery()
            ->getResult()
        ;
        return $result;
    }

    // /**
    //  * @return Country[] Returns an array of Country objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Country
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

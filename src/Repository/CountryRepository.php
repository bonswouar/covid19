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

    public function getCountryCodesIfCaseRank($casesMin, $maxCountries)
    {
        $result = $this->createQueryBuilder('c')
            ->select('c.code, c.disabled, SUM(d.cases) as totalCases')
            ->orderBy('totalCases', 'desc')
            ->join('c.cases', 'd')
            ->groupBy('c.code')
            ->having('totalCases >= :casesMin and (c.disabled != 1 or c.disabled is null)')
            ->setParameters(['casesMin' => $casesMin])
            ->setMaxResults($maxCountries)
            ->getQuery()
            ->getScalarResult()
        ;
        $result = array_map(function($element){return $element["code"];}, $result);
        return $result;
    }

    public function getCountryCodesIfDeathRank($deathsMin, $maxCountries)
    {
        $result = $this->createQueryBuilder('c')
            ->select('c.code, c.disabled, SUM(d.deaths) as totalDeaths')
            ->orderBy('totalDeaths', 'desc')
            ->join('c.cases', 'd')
            ->groupBy('c.code')
            ->having('totalDeaths >= :deathsMin and (c.disabled != 1 or c.disabled is null)')
            ->setParameters(['deathsMin' => $deathsMin])
            ->setMaxResults($maxCountries)
            ->getQuery()
            ->getScalarResult()
        ;
        $result = array_map(function($element){return $element["code"];}, $result);
        return $result;
    }
}

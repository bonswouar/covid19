<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Service\EcdcParser;
use App\Entity\Cases;
use App\Entity\Country;
use App\Entity\Config;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/cases", name="cases")
     */
    public function cases(EcdcParser $ecdcParser, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $cases = $em->getRepository(Cases::class)->findAllByDate();
        $data = $serializer->serialize($cases, 'json', ['groups' => 'api-cases']);

        return JsonResponse::fromJsonString($data);
    }

    /**
     * @Route("/countries", name="countries")
     */
    public function countries(EcdcParser $ecdcParser, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $countries = $em->getRepository(Country::class)->findAll();
        $data = $serializer->serialize($countries, 'json', ['groups' => 'api-countries']);

        return JsonResponse::fromJsonString($data);
    }

    /**
     * @Route("/evolution/cases", name="evolution-cases")
     */
    public function evolutionCases(EcdcParser $ecdcParser, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $dataCountry = [];
        $minCases = 100;
        $maxCountries = 30;
        $countries = $em->getRepository(Country::class)->findIfCaseRank($minCases, $maxCountries);
        foreach ($countries as $country) {
            $dataCountry[$country["name"]] = [];
            $cases = $em->getRepository(Cases::class)->findByCountry($em->getReference(Country::class, ($country["id"])));
            $totalCases = 0;
            $days = 0;
            foreach ($cases as $case) {
                $totalCases += $case->getCases();
                if ($totalCases >= $minCases) {
                    $dataCountry[$country["name"]][$days++] = $totalCases;
                }
            }
        }

        $countryKeys = ["days"];
        $data = [];
        $hasOneCountry = true;
        $day = 0;
        while ($hasOneCountry) {
            $hasOneCountry = false;
            $data[$day] = [$day];
            $key = 0;
            foreach ($dataCountry as $country => $dataDays) {
                if ($day == 0) {
                    $countryKeys[] = $country;
                }
                if (isset($dataDays[$day])) {
                    $hasOneCountry = true;
                    $data[$day][$key + 1] = $dataDays[$day];
                } else {
                    $data[$day][$key + 1] = null;
                }
                $key++;
            }
            $day++;
        }

        $lastUpdate = $em->getRepository(Config::class)->findOneByParam(Config::PARAM_LAST_UPDATE);

        return new JsonResponse(["countries" => $countryKeys, "data" => $data, "info" => ["last_update" => $lastUpdate->getValue()] ]);
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Service\EcdcParser;
use App\Entity\Cases;
use App\Entity\Country;
use App\Entity\Config;
use App\Normalizer\EvolutionCasesNormalizer;

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
        $minCases = 100;
        $maxCountries = 30;
        $countries = $em->getRepository(Country::class)->findIfCaseRank($minCases, $maxCountries);
        $cases = $em->getRepository(Cases::class)->findByCountries($countries);

        $data = $serializer->normalize($cases, null, ["minCases" => $minCases]);
        $lastUpdate = $em->getRepository(Config::class)->findOneByParam(Config::PARAM_LAST_UPDATE);
        $data["last_update"] = $lastUpdate->getValue();

        return new JsonResponse($data);
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use App\Service\EcdcParser;
use App\Entity\Cases;
use App\Entity\Country;
use App\Entity\Config;
use App\Normalizer\EvolutionNormalizer;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/evolution/cases", name="evolution-cases")
     * Cumulative cases per day
     */
    public function evolutionCases(EcdcParser $ecdcParser, EntityManagerInterface $em, Request $request, $minCases)
    {
        $this->maxCountries = $this->getParameter('app.graph_max_countries');
        $minCases = $request->query->get('min') ?: $minCases;
        $countryCodes = $this->getCountryCodes($request);
        if (!$countryCodes || !count($countryCodes)) {
            $countryCodes = $em->getRepository(Country::class)->getCountryCodesIfCaseRank($minCases, $this->maxCountries);
        }
        $cases = $em->getRepository(Cases::class)->findByCountries($countryCodes);
        $serializer = new Serializer([new EvolutionNormalizer()]);
        $data = $serializer->normalize($cases, null, ["min" => $minCases, "property" => "cases"]);
        $data["last_update"] = $em->getRepository(Config::class)->findOneByParam(Config::PARAM_LAST_UPDATE)->getValue();
        return new JsonResponse($data);
    }

    /**
     * @Route("/evolution/deaths", name="evolution-deaths")
     * Cumulative deaths per day
     */
    public function evolutionDeaths(EcdcParser $ecdcParser, EntityManagerInterface $em, Request $request, $minDeaths)
    {
        $this->maxCountries = $this->getParameter('app.graph_max_countries');
        $minDeaths = $request->query->get('min') ?: $minDeaths;
        $countryCodes = $this->getCountryCodes($request);
        if (!$countryCodes || !count($countryCodes)) {
            $countryCodes = $em->getRepository(Country::class)->getCountryCodesIfDeathRank($minDeaths, $this->maxCountries);
        }
        $cases = $em->getRepository(Cases::class)->findByCountries($countryCodes);
        $serializer = new Serializer([new EvolutionNormalizer()]);
        $data = $serializer->normalize($cases, null, ["min" => $minDeaths, "property" => "deaths"]);
        $data["last_update"] = $em->getRepository(Config::class)->findOneByParam(Config::PARAM_LAST_UPDATE)->getValue();
        return new JsonResponse($data);
    }

    /**
     * @Route("/countries", name="countries")
     * Country list
     */
    public function countries(EcdcParser $ecdcParser, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $countries = $em->getRepository(Country::class)->findAll();
        $data = $serializer->serialize($countries, 'json', ['groups' => 'api-countries']);

        return JsonResponse::fromJsonString($data);
    }

    private function getCountryCodes($request)
    {
        if ($request->query->get('countries')) {
            $countryCodes = json_decode($request->query->get('countries'));
            $countryCodes = array_slice($countryCodes, 0, $this->maxCountries);
            return $countryCodes;
        }
        return null;
    }

    /**
     * @Route("/cases", name="cases")
     * Basic API endpoint with new cases by day
     * Not used
     */
    public function cases(EcdcParser $ecdcParser, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $cases = $em->getRepository(Cases::class)->findAllByDate();
        $data = $serializer->serialize($cases, 'json', ['groups' => 'api-cases']);

        return JsonResponse::fromJsonString($data);
    }
}

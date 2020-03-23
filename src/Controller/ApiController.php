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
use App\Normalizer\EvolutionCasesNormalizer;
use App\Normalizer\EvolutionDeathsNormalizer;

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

    private function getCountryCodes($request)
    {
        if ($request->query->get('countries')) {
            $countryCodes = json_decode($request->query->get('countries'));
            $countryCodes = array_slice($countryCodes, 0, $this->maxCountries);
        } else {
            return null;
        }

        return $countryCodes;
    }

    /**
     * @Route("/evolution/cases", name="evolution-cases")
     */
    public function evolutionCases(EcdcParser $ecdcParser, EntityManagerInterface $em, Request $request)
    {
        $this->maxCountries = $this->getParameter('app.graph_max_countries');;
        $minCases = $request->query->get('min') ?: 100;
        $countryCodes = $this->getCountryCodes($request);
        if (!$countryCodes || !count($countryCodes)) {
            $countries = $em->getRepository(Country::class)->findIfCaseRank($minCases, $this->maxCountries);
            $countryCodes = [];
            foreach ($countries as $country) {
                $countryCodes[] = $country["code"];
            }
        }
        $cases = $em->getRepository(Cases::class)->findByCountries($countryCodes);
        $serializer = new Serializer([new EvolutionCasesNormalizer()]);
        $data = $serializer->normalize($cases, null, ["minCases" => $minCases]);
        $data["last_update"] = $em->getRepository(Config::class)->findOneByParam(Config::PARAM_LAST_UPDATE)->getValue();
        return new JsonResponse($data);
    }

    /**
     * @Route("/evolution/deaths", name="evolution-deaths")
     */
    public function evolutionDeaths(EcdcParser $ecdcParser, EntityManagerInterface $em, Request $request)
    {
        $this->maxCountries = $this->getParameter('app.graph_max_countries');;
        $minDeaths = $request->query->get('min') ?: 100;
        $countryCodes = $this->getCountryCodes($request);
        if (!$countryCodes || !count($countryCodes)) {
            $countries = $em->getRepository(Country::class)->findIfCaseRank($minDeaths, $this->maxCountries);
            $countryCodes = [];
            foreach ($countries as $country) {
                $countryCodes[] = $country["code"];
            }
        }
        $cases = $em->getRepository(Cases::class)->findByCountries($countryCodes);
        $serializer = new Serializer([new EvolutionDeathsNormalizer()]);
        $data = $serializer->normalize($cases, null, ["minDeaths" => $minDeaths]);
        $data["last_update"] = $em->getRepository(Config::class)->findOneByParam(Config::PARAM_LAST_UPDATE)->getValue();
        return new JsonResponse($data);
    }
}

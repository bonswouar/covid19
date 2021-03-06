<?php
namespace App\Normalizer;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\PropertyAccess\PropertyAccess;

use App\Entity\Cases;

class EvolutionNormalizer implements ContextAwareNormalizerInterface
{
    private $normalizer;

    public function normalize($cases, $format = null, array $context = [])
    {
        $min = isset($context["min"]) ? $context["min"] : 100;
        $property = isset($context["property"]) ? $context["property"] : "cases";
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $dataCountry = [];
        $countryStartDates = [];
        $countryTotal = [];
        $nbDaysMax = 0;

        // Progressive sum of total cases per country, starting at minCases
        foreach ($cases as $case) {
            $country = $case->getCountry();
            if (!isset($countryTotal[$case->getCountry()->getName()])) {
                $countryTotal[$country->getName()] = 0;
            }
            $countryTotal[$country->getName()] += $propertyAccessor->getValue($case, $property);
            if ($countryTotal[$country->getName()] >= $min) {
                if (!isset($dataCountry[$case->getCountry()->getName()])) {
                    $countryStartDates[$country->getCode()] = $case->getDate();
                }
                $nbDays = (int)$countryStartDates[$country->getCode()]->diff($case->getDate())->format("%a");
                if ($nbDays > $nbDaysMax) {
                    $nbDaysMax = $nbDays;
                }
                $dataCountry[$country->getName()][$nbDays] = $countryTotal[$country->getName()];
            }
        }

        // Order by country and by day
        $data = [];
        $day = 0;
        for ($day=0; $day <= $nbDaysMax ; $day++) {
            foreach ($dataCountry as $country => $dataDays) {
                if (!isset($data[$country])) {
                    $data[$country] = [];
                }
                if (isset($dataDays[$day])) {
                    $data[$country][$day] = $dataDays[$day];
                }
            }
        }
        // Header for Chart
        $header = array_merge(["Number of days after the ".$min."th ".(substr($property, 0, -1))], array_keys($dataCountry));

        return ["header" => $header, "data" => $data, "countries" => array_keys($countryStartDates), "nbDays" => $day];
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return is_array($data) && isset($data[0]) && $data[0] instanceof Cases;
    }
}

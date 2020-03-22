<?php
namespace App\Normalizer;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use App\Entity\Cases;

class EvolutionCasesNormalizer implements ContextAwareNormalizerInterface
{
    private $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function normalize($cases, $format = null, array $context = [])
    {
        if (isset($context["minCases"])) {
            $minCases = $context["minCases"];
        } else {
            $minCases = 100;
        }
        $dataCountry = [];
        $countryStartDates = [];
        $countryTotalCases = [];
        $nbDaysMax = 0;

        // Progressive sum of total cases per country, starting at minCases
        foreach ($cases as $case) {
            $country = $case->getCountry();
            if (!isset($countryTotalCases[$case->getCountry()->getName()])) {
                $countryTotalCases[$country->getName()] = 0;
            }
            $countryTotalCases[$country->getName()] += $case->getCases();
            if ($countryTotalCases[$country->getName()] >= $minCases) {
                if (!isset($dataCountry[$case->getCountry()->getName()])) {
                    $countryStartDate[$country->getName()] = $case->getDate();
                }
                $nbDays = (int)$countryStartDate[$country->getName()]->diff($case->getDate())->format("%a");
                if ($nbDays > $nbDaysMax) {
                    $nbDaysMax = $nbDays;
                }
                $dataCountry[$country->getName()][$nbDays] = $countryTotalCases[$country->getName()];
            }
        }

        // Order by day and country
        $data = [];
        $hasAtLeastOneCountry = true;
        $day = 0;
        for ($day=0; $day <= $nbDaysMax ; $day++) {
            $hasOneCountry = false;
            // First col is the day number
            $data[$day] = [$day];
            $countryKey = 0;
            foreach ($dataCountry as $country => $dataDays) {
                if (isset($dataDays[$day])) {
                    $hasOneCountry = true;
                    $data[$day][$countryKey + 1] = $dataDays[$day];
                } else {
                    $data[$day][$countryKey + 1] = null;
                }
                $countryKey++;
            }
        }
        // Header for Chart
        $header = array_merge(["Number of days after the ".$minCases."th case"], array_keys($dataCountry));

        return ["header" => $header, "data" => $data];
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return $data[0] instanceof Cases;
    }
}

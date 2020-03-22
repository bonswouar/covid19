<?php
namespace App\Normalizer;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use App\Entity\Cases;

class EvolutionDeathsNormalizer implements ContextAwareNormalizerInterface
{
    private $normalizer;

    public function normalize($cases, $format = null, array $context = [])
    {
        $minDeaths = isset($context["minDeaths"]) ? $context["minDeaths"] : 10;
        $dataCountry = [];
        $countryStartDates = [];
        $countryTotalCases = [];
        $nbDaysMax = 0;

        // Progressive sum of total cases per country, starting at minDeaths
        foreach ($cases as $case) {
            $country = $case->getCountry();
            if (!isset($countryTotalCases[$case->getCountry()->getName()])) {
                $countryTotalCases[$country->getName()] = 0;
            }
            $countryTotalCases[$country->getName()] += $case->getDeaths();
            if ($countryTotalCases[$country->getName()] >= $minDeaths) {
                if (!isset($dataCountry[$case->getCountry()->getName()])) {
                    $countryStartDates[$country->getCode()] = $case->getDate();
                }
                $nbDays = (int)$countryStartDates[$country->getCode()]->diff($case->getDate())->format("%a");
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
        $header = array_merge(["Number of days after the ".$minDeaths."th death"], array_keys($dataCountry));

        return ["header" => $header, "data" => $data, "countries" => array_keys($countryStartDates)];
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return is_array($data) && isset($data[0]) && $data[0] instanceof Cases;
    }
}

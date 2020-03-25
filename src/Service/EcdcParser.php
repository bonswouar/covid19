<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\ProgressBar;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

use App\Entity\Country;
use App\Entity\Cases;

class EcdcParser
{
    const HEADERS = [
        "DateRep"                   => "date",
        "Cases"                     => "cases",
        "Deaths"                    => "deaths",
        "Countries and territories" => "country",
        "GeoId"                     => "countryCode"
    ];
    const CONFIG_DATE_FORMAT = "m/d/Y";

    private $logger;
    private $httpClient;
    private $em;

    private $xlsxFile;
    private $rawData;

    private $newCases = [];

    public function __construct(LoggerInterface $logger, HttpClientInterface $httpClient, EntityManagerInterface $em)
    {
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->em = $em;
    }

    private function getUrl($date)
    {
        $formatted = $date->format("Y-m-d");
        $url = "https://www.ecdc.europa.eu/sites/default/files/documents/COVID-19-geographic-disbtribution-worldwide-".$formatted.".xlsx";
        return $url;
    }

    public function getXlsx($date)
    {
        $url = $this->getUrl($date);
        $response = $this->httpClient->request('GET', $url);
        if (200 !== $response->getStatusCode()) {
            $this->logger->warning($response->getStatusCode().': No document available for this day. '.$url);
            return false;
        } else {
            $file = tempnam(sys_get_temp_dir(), 'excel_');
            $handle = fopen($file, "w");
            fwrite($handle, $response->getContent());
            $this->xlsxFile = $file;
            return true;
        }
    }

    public function parseXlsx()
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($this->xlsxFile);
        $this->rawData = $spreadsheet->getActiveSheet()->toArray();
    }

    private function getCountry($countryCode, $countryName)
    {
        $country = $this->em->getRepository(Country::class)->findOneByCodeAndName($countryCode, $countryName);
        if (!$country) {
            if ($this->em->getRepository(Country::class)->findOneByCodeAndName($countryCode, $countryName) || $this->em->getRepository(Country::class)->findOneByCodeAndName($countryCode, $countryName)) {
                throw new \Exception("Multiple results for ".$countryCode.": ".$countryName);
            } else {
                $country = new Country;
                $country->setCreatedAt(new \DateTime);
                $country->setCode($countryCode);
                $country->setName($countryName);
                $this->em->persist($country);
                $this->em->flush();
            }
        }
        return $country;
    }

    private function updateCases($casesNb, $deaths, $date, $country)
    {
        $keySearch = $country->getCode().$date->format("d/m/Y");
        if (in_array($keySearch, $this->newCases)) {
            return null;
        }
        $cases = $this->em->getRepository(Cases::class)->findOneByCountryAndDate($country, $date);
        if (!$cases) {
            $this->logger->info('Adding datas for '.($country->getName()).' on '.$date->format("d/m/Y"));
            $cases = new Cases();
            $cases->setCreatedAt(new \DateTime);
            $this->newCases[] = $keySearch;
        }
        $cases->setCases($casesNb);
        $cases->setDeaths($deaths);
        $cases->setDate($date);
        $cases->setCountry($country);
        $cases->setUpdatedAt(new \DateTime);
        return $cases;
    }

    public function persistData($io = false)
    {
        $headers = [];
        if ($io) {
            $io->progressStart(count($this->rawData) - 1);
        }
        foreach ($this->rawData as $key => $row) {
            if ($key == 0) {
                foreach (self::HEADERS as $name => $property) {
                    if (($col = array_search($name, $row)) !== false) {
                        $headers[$property] = $col;
                    } else {
                        throw new \Exception($name." not found in : ".implode($row, ", "));
                    }
                }
            } else {
                $country = $this->getCountry($row[$headers["countryCode"]], $row[$headers["country"]]);
                $date = \DateTime::createFromFormat(self::CONFIG_DATE_FORMAT, $row[$headers["date"]]);
                $date->setTime(0, 0, 0);
                $cases = $this->updateCases(abs($row[$headers["cases"]]), abs($row[$headers["deaths"]]), $date, $country);
                if ($cases) {
                    $this->em->persist($cases);
                }
            }
            if ($io) {
                $io->progressAdvance();
            }
        }
        $this->em->flush();
        if ($io) {
            $io->progressFinish();
        }
        return true;
    }
}

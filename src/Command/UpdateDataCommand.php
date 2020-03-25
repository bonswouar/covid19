<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Config;
use App\Service\EcdcParser;

class UpdateDataCommand extends Command
{
    protected static $defaultName = 'app:update-data';

    private $ecdcParser;
    private $em;
    private $lastUpdate;

    public function __construct(EcdcParser $ecdcParser, EntityManagerInterface $em)
    {
        $this->ecdcParser = $ecdcParser;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Update data from ECDC daily Excel file')
            ->addOption('clear', null, InputOption::VALUE_NONE, 'Clear database')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Load file even if already up to date')
        ;
    }

    private function truncate($className)
    {
        $cmd = $this->em->getClassMetadata($className);
        $connection = $this->em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeUpdate($q);
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
    }

    private function shouldUpdate($force, $clear, $date)
    {
        if ($force || $clear) {
            return true;
        }
        $this->lastUpdate = $this->em->getRepository(Config::class)->findOneByParam(Config::PARAM_LAST_UPDATE);
        if (!$this->lastUpdate) {
            $this->lastUpdate = new Config();
            $this->lastUpdate->setCreatedAt(new \DateTime);
            $this->lastUpdate->setParam(Config::PARAM_LAST_UPDATE);
            return true;
        }

        $lastUpdateDate = \DateTime::createFromFormat(Config::PARAM_LAST_UPDATE_DATE_FORMAT, $this->lastUpdate->getValue());
        if ($date <= $lastUpdateDate) {
            return false;
        }
        return true;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption("clear")) {
            $this->truncate("App\Entity\Cases");
            $this->truncate("App\Entity\Country");
        }

        $io = new SymfonyStyle($input, $output);
        $io->title('Retrieve .xlsx file from ECDC website');

        $date = new \DateTime("today");
        $date->setTime(0, 0, 0);
        while (!$this->ecdcParser->getXlsx($date)) {
            $date->modify('-1 day');
        }
        if (!$this->shouldUpdate($input->getOption("force"), $input->getOption("clear"), $date)) {
            $io->text('Datas already updated with last available document ('.$date->format("d/m/Y").').');
            return 0;
        }
        $io->text("File saved.");

        $io->title('Parse .xlsx file');
        $this->ecdcParser->parseXlsx();
        $io->text("Raw data saved.");

        $io->title('Persist Data');
        $this->ecdcParser->persistData($io);
        $io->text("Data persisted.");
        $io->success('Datas successfully updated.');

        $this->lastUpdate->setValue($date->format(Config::PARAM_LAST_UPDATE_DATE_FORMAT));
        $this->lastUpdate->setUpdatedAt(new \DateTime);
        $this->em->persist($this->lastUpdate);
        $this->em->flush();

        return 0;
    }
}

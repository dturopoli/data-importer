<?php


namespace Pimcore\Bundle\DataHubBatchImportBundle\Command;


use Pimcore\Bundle\DataHubBatchImportBundle\Processing\ImportPreparationService;
use Pimcore\Bundle\DataHubBatchImportBundle\Processing\ImportProcessingService;
use Pimcore\Bundle\DataHubBundle\Configuration\Dao;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronExecutionCommand extends AbstractCommand
{

    /**
     * @var ImportPreparationService
     */
    protected $importPreparationService;


    public function __construct(ImportPreparationService $importPreparationService)
    {
        parent::__construct();
        $this->importPreparationService = $importPreparationService;
    }

    protected function configure()
    {
        $this
            ->setName('datahub:batch-import:execute-cron')
            ->setDescription('Executes all batch import configurations corresponding to their cron definition.')
            ->addArgument('config_name', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Names of configs that should be considered. Uses all if not specified.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $configNames = $input->getArgument('config_name');

        if (empty($configNames)) {

            $configNames = [];
            $allDataHubConfiguations = Dao::getList();
            foreach ($allDataHubConfiguations as $dataHubConfig) {
                if (in_array($dataHubConfig->getType(), ['batchImportDataObject'])) {
                    $configNames[] = $dataHubConfig->getName();
                }
            }
        }

        foreach ($configNames as $configName) {
            $output->writeln("Cron execution of config '$configName'");
            $this->importPreparationService->executeCron($configName);
        }


        return 0;
    }


}
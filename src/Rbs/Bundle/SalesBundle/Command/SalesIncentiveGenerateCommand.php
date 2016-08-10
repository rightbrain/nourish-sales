<?php

namespace Rbs\Bundle\SalesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SalesIncentiveGenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('nsm:generate:sales-incentive')
            ->setDescription('Generate Monthly/Yearly Sales Incentive')
            ->addArgument(
                'agentId',
                InputArgument::REQUIRED,
                'Enter Agent Id'
            )
            ->addArgument(
                'durationType',
                InputArgument::REQUIRED,
                'Duration Type (Monthly/Yearly)'
            )
            ->addArgument(
                'startDate',
                InputArgument::REQUIRED,
                'Enter Start Date'
            )
            ->addArgument(
                'endDate',
                InputArgument::REQUIRED,
                'Enter End Date'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $agentId = $input->getArgument('agentId');
        $durationType = $input->getArgument('durationType');
        $startDate = $input->getArgument('startDate');
        $endDate = $input->getArgument('endDate');

        $path = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../') . '/test.log';
        file_put_contents($path, $agentId . '=>' . $durationType . PHP_EOL, FILE_APPEND);
        $output->writeln($path);
    }
}
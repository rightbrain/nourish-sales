<?php

namespace Rbs\Bundle\SalesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SalesIncentiveInitCommand extends ContainerAwareCommand
{
    protected $startDate;
    protected $endDate;

    protected function configure()
    {
        $this
            ->setName('nsm:initiate:sales-incentive')
            ->setDescription('Create Sales Incentive Job Queue')
            ->addOption(
                'durationType',
                null,
                InputOption::VALUE_OPTIONAL,
                'Duration Type (Month or Year)',
                'month'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $durationType = $input->getOption('durationType');
        
        if ($durationType == 'year') {
            $this->startDate = date('Y-01-01', strtotime(date('Y-m') . " -1 year"));
            $this->endDate = date('Y-12-31', strtotime(date('Y-m') . " -1 year"));
        } else {
            $this->startDate = date('Y-m-d', strtotime(date('Y-m') . " -1 month"));
            $this->endDate = date('Y-m-t', strtotime(date('Y-m') . " -1 month"));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $durationType = $input->getOption('durationType');
        $beanstalkTube = $this->getContainer()->getParameter('beanstalkd_tube');
        $beanstalk = $this->getContainer()->get('leezy.pheanstalk.primary');

        for ($i = 0; $i < 10; $i++) {
            $agentId = rand(1111, 9999);
            $beanstalk->useTube($beanstalkTube)->put(
                json_encode(
                    array(
                        'agentId'      => $agentId,
                        'durationType' => $durationType,
                        'startDate'    => $this->startDate,
                        'endDate'      => $this->endDate,
                    )
                )
            );
            $output->writeln('Added Queue of Agent: ' . $agentId);
        }

        $output->writeln('DONE');
    }
}
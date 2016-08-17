<?php

namespace Rbs\Bundle\SalesBundle\Command;

use Doctrine\ORM\EntityManager;
use Rbs\Bundle\SalesBundle\Entity\Incentive;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SalesIncentiveGenerateCommand extends ContainerAwareCommand
{
    /** @var  EntityManager */
    protected $em;

    protected function configure()
    {
        $this
            ->setName('nsm:generate:sales-incentive')
            ->setDescription('Generate Monthly/Yearly Sales Incentive')
            ->addArgument(
                'orderId',
                InputArgument::REQUIRED,
                'Enter Order Id'
            )
            ->addArgument(
                'durationType',
                InputArgument::REQUIRED,
                'Duration Type (Monthly/Yearly)'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orderId = $input->getArgument('orderId');
        $durationType = $input->getArgument('durationType');

        $this->generateCommission($orderId, $durationType);

        $output->writeln('Done : ' . $orderId);
    }

    protected function generateCommission($orderId, $durationType)
    {
        $order = $this->em->getRepository('RbsSalesBundle:Order')->find($orderId);
        $agent = $this->em->getRepository('RbsSalesBundle:Agent')->find($order->getAgent()->getId());
        $orderIncentive = $this->em->getRepository('RbsSalesBundle:OrderItem')->getOrderIncentive($orderId);
        $amount = $this->em->getRepository('RbsCoreBundle:SaleIncentive')->getSalesIncentive($orderIncentive[0]['categoryId'], $orderIncentive[0]['quantity']);
        $details = "Total quantity ".$orderIncentive[0]['quantity'].", item type ".$orderIncentive[0]['categoryName'];
        $incentive = new Incentive();
        $incentive->setAgent($agent);
        $incentive->setType(Incentive::SALE);
        $incentive->setAmount(floatval($orderIncentive[0]['quantity']) * floatval($amount[0]['amount']));
        if ($durationType == 'year') {
            $incentive->setDuration(Incentive::YEAR);
        } else {
            $incentive->setDuration(Incentive::MONTH);
        }
        $incentive->setDetails($details);
        $incentive->setDate(new \DateTime());

        $this->em->getRepository('RbsSalesBundle:Incentive')->create($incentive);
    }
}
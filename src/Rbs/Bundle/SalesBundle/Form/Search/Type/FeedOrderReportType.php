<?php

namespace Rbs\Bundle\SalesBundle\Form\Search\Type;

use Rbs\Bundle\CoreBundle\Entity\Depo;
use Rbs\Bundle\CoreBundle\Repository\DepoRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedOrderReportType extends AbstractType
{
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('depo', 'entity', array(
                'class' => 'RbsCoreBundle:Depo',
                'attr' => array(
                    'placeholder' => 'Select Depo',
                    'class' => 'select2me input-medium'
                ),
                'property' => 'name',
                'required' => true,
                'empty_value' => 'Select Depot',
                'empty_data' => null,
                'query_builder' => function (DepoRepository $repository)
                {
                    if($this->user->hasRole("ROLE_DEPO_USER")){
                        return $repository->createQueryBuilder('d')
                            ->innerJoin('d.users','users')
                            ->andWhere('d.deletedAt IS NULL')
                            ->andWhere('users = :userId')
                            ->setParameter('userId', $this->user->getId());
                    }else{
                        return $repository->createQueryBuilder('d')
                            ->where('d.deletedAt IS NULL')
                            ->andWhere('d.depotType IS NULL or d.depotType =:type')
                            ->setParameter('type', Depo::DEPOT_TYPE_FEED)
                            ;
                    }
                }
            ))
            ->add('start_date', 'text', array(
                'attr' => array(
                    'class' => 'date-picker input-small form-control',
                    'autocomplete'=>'off'
                )
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(

        ));
    }

    public function getName()
    {
        return 'feed_order_report';
    }
}

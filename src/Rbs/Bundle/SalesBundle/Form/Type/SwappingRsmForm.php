<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\CoreBundle\Repository\AreaRepository;
use Rbs\Bundle\UserBundle\Entity\User;
use Rbs\Bundle\UserBundle\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SwappingRsmForm extends AbstractType
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
            ->add('username', 'entity', array(
                'class' => 'Rbs\Bundle\UserBundle\Entity\User',
                'property' => 'username',
                'query_builder' => function(UserRepository $userRepository) {
                    return $userRepository->createQueryBuilder('u')
                        ->andWhere("u.id = :userId")
                        ->setParameters(array('userId' => $this->user->getId()));
                }
            ))
            ->add('areaNew', 'entity', array(
                'class' => 'RbsCoreBundle:Area',
                'property' => 'areaName',
                'required' => false,
                'mapped' => false,
                'empty_value' => 'Select Area',
                'empty_data' => null,
                'query_builder' => function (AreaRepository $repository)
                {
                    return $repository->createQueryBuilder('a')
                        ->where('a.deletedAt IS NULL')
                        ->andWhere("a.id != :area")
                        ->setParameters(array('area' => $this->user->getArea()->getId()))
                        ->orderBy('a.areaName','ASC');
                }
            ))
            ->add('userChange', 'entity', array(
                'class' => 'Rbs\Bundle\UserBundle\Entity\User',
                'property' => 'username',
                'required' => false,
                'empty_value' => 'Select User',
                'empty_data' => null,
                'mapped' => false,
                'query_builder' => function(UserRepository $userRepository) {
                    return $userRepository->createQueryBuilder('u')
                        ->andWhere("u.userType = :userType")
                        ->andWhere("u.id != :userId")
                        ->setParameters(array('userType' => User::RSM, 'userId' => $this->user->getId()));
                }
            ))
            ->add('areaOld', 'entity', array(
                'class' => 'RbsCoreBundle:Area',
                'property' => 'areaName',
                'mapped' => false,
                'query_builder' => function (AreaRepository $repository)
                {
                    return $repository->createQueryBuilder('a')
                        ->andWhere("a.id = :area")
                        ->setParameters(array('area' => $this->user->getArea()->getId()));
                }
            ))
            ->add('submit', 'submit', array(
                'attr'     => array('class' => 'btn green')
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Rbs\Bundle\UserBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'rsm_swap';
    }
}

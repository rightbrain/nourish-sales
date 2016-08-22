<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Rbs\Bundle\CoreBundle\Repository\LocationRepository;
use Rbs\Bundle\UserBundle\Entity\User;
use Rbs\Bundle\UserBundle\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SwappingSrForm extends AbstractType
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
            ->add('location', 'entity', array(
                'class' => 'Rbs\Bundle\CoreBundle\Entity\Location',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.level = :level')
                        ->andWhere('a.id != :oldLocation')
                        ->setParameter('level', 4)
                        ->setParameter('oldLocation', $this->user->getZilla()->getId())
                        ->orderBy('a.name');
                },
                'attr' => array(
                    'class' => 'zilla-selector select2me',
                    'id' => 'user_level1'
                ),
                'placeholder' => 'Choose an Location',
                'required' => true,
                'mapped' => false
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
        return 'sr_swap';
    }
}

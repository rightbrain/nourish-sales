<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\CoreBundle\Repository\ProjectRepository;
use Rbs\Bundle\SalesBundle\RbsSalesBundle;
use Rbs\Bundle\UserBundle\Entity\User;
use Rbs\Bundle\UserBundle\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TargetForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', 'entity', array(
                'class' => 'Rbs\Bundle\UserBundle\Entity\User',
                'query_builder' => function(UserRepository $userRepository) {
                    return $userRepository->createQueryBuilder('u')
                        ->where("u.userType != :AGENT")
                        ->andWhere("u.userType != :USER")
                        ->setParameters(array('AGENT'=> User::AGENT, 'USER' => User::USER));
                },
                'property' => 'username'
            ))
            ->add('child_entities', 'collection', array(
                'type' => new TargetSubForm(),
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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\Target'
        ));
    }

    public function getName()
    {
        return 'target';
    }
}

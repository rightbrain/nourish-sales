<?php

namespace Rbs\Bundle\UserBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserUpdateForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', array(
                'label' => 'form.email', 'translation_domain' => 'FOSUserBundle'
            ))
            ->add('groups', 'entity', array(
                'class' => 'Rbs\Bundle\UserBundle\Entity\Group',
                'query_builder' => function(EntityRepository $groupRepository) {
                    return $groupRepository->createQueryBuilder('g')
                        ->andWhere("g.name != :group")
                        ->setParameter('group', 'Super Administrator');
                },
                'property' => 'name',
                'multiple' => true,
            ))
            ->add('submit', 'submit');

        $builder
            ->add('profile', new ProfileForm());
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
        return 'user';
    }
}

<?php

namespace Rbs\Bundle\UserBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Rbs\Bundle\CoreBundle\Repository\LocationRepository;
use Rbs\Bundle\UserBundle\Entity\User;
use Rbs\Bundle\UserBundle\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

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
                'label' => 'form.email', 'translation_domain' => 'FOSUserBundle',
                'constraints' => array(
                    new NotBlank(array(
                        'message'=>'Email should not be blank'
                    )),
                    new email()
                ),
            ))
            ->add('userType', 'choice', array(
                'choices'  => array(
                    'USER' => User::USER,
                    'RSM' => User::RSM,
                    'SR' => User::SR,
                    'AGENT' => User::AGENT
                ),
                'data' => User::USER
            ))
            ->add('location', 'entity', array(
                'class' => 'RbsCoreBundle:Location',
                'property' => 'name',
                'required' => false,
                'empty_value' => 'Select Location',
                'empty_data' => null,
                'query_builder' => function (LocationRepository $repository)
                {
                    return $repository->createQueryBuilder('l')
                        ->where('l.level = 4 OR l.level = 8')
                        ->orderBy('l.name','ASC');
                }
            ))
            ->add('parentId', 'entity', array(
                'class' => 'Rbs\Bundle\UserBundle\Entity\User',
                'query_builder' => function(UserRepository $userRepository) {
                    return $userRepository->createQueryBuilder('u')
                        ->where("u.userType != :AGENT")
                        ->setParameters(array('AGENT'=> User::AGENT));
                },
                'mapped' => false,
                'property' => 'username',
                'required' => false,
                'empty_value' => 'Select Parent',
            ))
        ;

        $builder
            ->add('profile', new ProfileForm());

        $builder
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
        ;

        $builder
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
        return 'user';
    }
}

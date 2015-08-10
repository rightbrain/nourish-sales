<?php

namespace Rbs\Bundle\CoreBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Rbs\Bundle\CoreBundle\Entity\Area;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AreaForm extends AbstractType
{
    /** @var Request */
    private $request;

    public function __construct($request = null)
    {
        $this->request = $request;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Area $areaData */
        $areaData = $options['data'];

        $builder
            ->add('areaName')
            ->add('level1', 'entity', array(
                'label' => 'Zilla',
                'class' => 'Rbs\Bundle\CoreBundle\Entity\Address',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.level = :level')->setParameter('level', 2)->orderBy('a.name');
                },
                'attr' => array(
                    'class' => 'zilla-selector select2me'
                ),
            ))
            ->add('level2', 'entity', array(
                'label' => 'Thana/UpoZilla',
                'class' => 'Rbs\Bundle\CoreBundle\Entity\Address',
                'query_builder' => function (EntityRepository $er) use ($areaData) {
                    $level1 = $this->request->request->get('rbs_bundle_corebundle_area[level1]', null, true);
                    $level1 = (!$level1 && $areaData->getLevel1()) ? $areaData->getLevel1() : $level1;

                    $qb = $er->createQueryBuilder('a')
                        ->where('a.level = :level')->setParameter('level', 3)->orderBy('a.name');
                    if ($areaData->getLevel1()) {
                        $qb->andWhere('a.c4 = :val')->setParameter('val', $level1);
                    }

                    return $qb;
                },
                'attr' => array(
                    'class' => 'thana-selector select2me'
                ),
                'placeholder' => 'Choose an option',
            ))
            ->add('level3', 'entity', array(
                'label' => 'Union',
                'class' => 'Rbs\Bundle\CoreBundle\Entity\Address',
                'query_builder' => function (EntityRepository $er)  use ($areaData){
                    $level2 = $this->request->request->get('rbs_bundle_corebundle_area[level2]', null, true);
                    $level2 = (!$level2 && $areaData->getLevel2()) ? $areaData->getLevel2() : $level2;
                    $qb = $er->createQueryBuilder('a')
                        ->where('a.level = :level')->setParameter('level', 4)->orderBy('a.name');

                    if ($level2) {
                        $qb->andWhere('a.c4 = :val')->setParameter('val', $level2);
                    }
                    return $qb;
                },
                'attr' => array(
                    'class' => 'union-selector select2me'
                ),
                'placeholder' => 'Choose an option',
                'required' => false
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Rbs\Bundle\CoreBundle\Entity\Area'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'rbs_bundle_corebundle_area';
    }
}

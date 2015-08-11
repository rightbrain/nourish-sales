<?php

namespace Rbs\Bundle\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Rbs\Bundle\CoreBundle\Entity\Bundle;

class LoadBundleData implements FixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $modules = array('Purchase Module', 'Salas Module');
        foreach ($modules as $module) {
            $bundle = new Bundle();
            $bundle->setName($module);
            $manager->persist($bundle);
        }

        //$manager->flush();
    }
}
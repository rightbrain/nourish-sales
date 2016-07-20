<?php
namespace Rbs\Bundle\CoreBundle\Repository;


use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    public function getAllActiveCategory()
    {
        $query = $this->createQueryBuilder('c');
        $query->where('c.status = 1');

        return $query->getQuery()->getResult();
    }
}
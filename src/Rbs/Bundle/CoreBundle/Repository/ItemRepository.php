<?php
namespace Rbs\Bundle\CoreBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Rbs\Bundle\SalesBundle\RbsSalesBundle;

class ItemRepository extends EntityRepository
{
    public function allItems()
    {
        $query = $this->createQueryBuilder('i');
        $query->join('i.bundles', 'b');
        $query->select('i.id');
        $query->addSelect('i.name');
        $query->where('b.id = :bundle');
        $query->setParameter('bundle', RbsSalesBundle::ID);
        $query->groupBy('i.id');
        $query->orderBy('i.name');

        $result = $query->getQuery()->getResult();

        $data = array();
        foreach($result as $row) {
            $data[$row['id']] = $row;
        }

        return $data;
    }
}
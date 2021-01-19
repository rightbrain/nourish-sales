<?php
namespace Rbs\Bundle\CoreBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Rbs\Bundle\CoreBundle\Entity\ItemType;
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
    public function getAllItems()
    {
        $query = $this->createQueryBuilder('i');
        $query->join('i.bundles', 'b');
        $query->join('i.category', 'c');
        $query->join('i.itemType', 'it');
        $query->select('i.name as name');
        $query->addSelect('i.sku as code');
        $query->addSelect('c.name as category');
        $query->addSelect('it.itemType as itemType');
        $query->where('b.id = :bundle');
        $query->andWhere('i.status=1');
        $query->setParameter('bundle', RbsSalesBundle::ID);
        $query->groupBy('i.id');
        $query->orderBy('i.name');

        $result = $query->getQuery()->getResult();
        return $result;

    }
    
    public function getItemName()
    {
        $query = $this->createQueryBuilder('l');
        $query->select('l.name');
        $query->where('l.level = 5');

        foreach ($data['zilla'] as $zilla){
            $query->andWhere('l.parentId = :zilla');
            $query->setParameter('zilla', $zilla);
        }

        return $query->getQuery()->getResult();
    }

    public function getChickItems() {
        $query = $this->createQueryBuilder('i');
        $query->join('i.itemType', 'it');
        $query->where('it.itemType = :itemType');
        $query->setParameter('itemType', ItemType::Chick);
        $query->andWhere('i.status = 1');

        return $query->getQuery()->getResult();
    }

    public function getFeedItems() {
        $query = $this->createQueryBuilder('i');
        $query->join('i.itemType', 'it');
        $query->where('it.itemType != :itemType');
        $query->setParameter('itemType', ItemType::Chick);
        $query->andWhere('i.status = 1');
        $query->orderBy('i.name');

        return $query->getQuery()->getResult();
    }


}
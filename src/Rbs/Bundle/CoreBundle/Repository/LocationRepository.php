<?php
namespace Rbs\Bundle\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class LocationRepository extends EntityRepository
{
    public function getAll()
    {
        return $this->findAll();
    }

    public function create($data)
    {
        $this->_em->persist($data);
        $this->_em->flush();
    }

    public function delete($data)
    {
        $this->_em->remove($data);
        $this->_em->flush();
    }

    public function update($data)
    {
        $this->_em->persist($data);
        $this->_em->flush();
        return $this->_em;
    }

    public function getSectors()
    {
        $query = $this->createQueryBuilder('l');
        $query->where('l.level = 1');

        return $query->getQuery()->getResult();
    }

    public function getZones()
    {
        $query = $this->createQueryBuilder('l');
        $query->where('l.level = 2');

        return $query->getQuery()->getResult();
    }

    public function getZoneBySector($parentId)
    {
        $query = $this->createQueryBuilder('l');
        $query->where('l.level = 2');
        $query->andWhere('l.parentId = :parentId');
        $query->setParameter('parentId', $parentId);

        return $query->getQuery()->getResult();
    }

    public function getRegionBySector($parentId)
    {
        $query = $this->createQueryBuilder('l');
        $query->where('l.level = 3');
        $query->andWhere('l.parentId = :parentId');
        $query->setParameter('parentId', $parentId);

        return $query->getQuery()->getResult();
    }

    public function getDistrictByRegion($parentId)
    {
        $query = $this->createQueryBuilder('l');
        $query->where('l.level = 4');
        $query->andWhere('l.parentId = :parentId');
        $query->setParameter('parentId', $parentId);

        return $query->getQuery()->getResult();
    }

    public function getUpozillaByDistrict($parentId)
    {
        $query = $this->createQueryBuilder('l');
        $query->where('l.level = 5');
        $query->andWhere('l.parentId = :parentId');
        $query->setParameter('parentId', $parentId);

        return $query->getQuery()->getResult();
    }

    public function getZillaByParentId($data)
    {
        $query = $this->createQueryBuilder('i');
        $query->select('i.name');

        foreach ($data['item'] as $item) {
            $query->andWhere('i.id = :item');
            $query->setParameter('item', $item);
        }

        return $query->getQuery()->getResult();
    }

    public function getAllFromSectorToUpozilla()
    {
        $query = $this->createQueryBuilder('l');
        $query->where('l.level = 5 or l.level = 4 or l.level = 3 or l.level = 2 or l.level = 1');

        return $query->getQuery()->getResult();
    }

    public function getRegions()
    {
        $query = $this->createQueryBuilder('l');
        $query->where('l.level = 3');

        return $query->getQuery()->getResult();
    }
    public function getRegionById($id)
    {
        $query = $this->find($id);

        return $query->getName();
    }

    public function getRegionsForChick()
    {
        $query = $this->createQueryBuilder('l');
        $query->select('l.id, l.name');
        $query->where('l.level = 3');
        $query->orderBy('l.name','ASC');

        return $query->getQuery()->getResult();
    }

    public function getDistricts()
    {
        $query = $this->createQueryBuilder('l');
        $query->select("l.id, l.name AS text, l.parentId");
        $query->where('l.level = 4');

        return $query->getQuery()->getResult();
    }

    public function getDistrictsByName($request)
    {
        $query = $this->createQueryBuilder('l');
        $query->select("l.id, l.name AS text");
        $query->where('l.level = 4');
        if ($q = $request->query->get('q')) {
            $query->andWhere("l.name LIKE '{$q}%'");
        }

        return $query->getQuery()->getResult();
    }

    public function getDistrictsForChick()
    {
        $query = $this->createQueryBuilder('l');
        $query->select('l.id, l.name, l.parentId');
        $query->where('l.level = 4');
        $query->orderBy('l.name','ASC');

        return $query->getQuery()->getResult();
    }

    public function getDistrictById($id)
    {
        $query = $this->createQueryBuilder('l');
        $query->select('l.id, l.name, l.parentId');
        $query->where('l.level = 4');
        $query->orderBy('l.name','ASC');

        return $query->getQuery()->getResult();
    }

    public function getDistrictByUpozillas()
    {
        $query = $this->createQueryBuilder('l');
        $query->where('l.level = 5');
        $arrayData= array();

        foreach ($query->getQuery()->getResult() as $upozilla){
          $arrayData[$upozilla->getId()]=  $this->find(array('id'=>$upozilla->getParentId()));

        }

        return $arrayData;
    }

    public function getUpozillas()
    {
        $query = $this->createQueryBuilder('l');
        $query->where('l.level = 5');

        return $query->getQuery()->getResult();
    }

    public function getUnions()
    {
        $query = $this->createQueryBuilder('l');
        $query->where('l.level = 6');

        return $query->getQuery()->getResult();
    }
}
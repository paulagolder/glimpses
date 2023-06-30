<?php

namespace App\Repository;

use App\Entity\Source;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class SourceRepository extends EntityRepository
{


    public function xfindOne($sid)
    {
        $sql = "select g from App:Source g ";
        $sql .= " where g.sourceid = ".$sid." ";
        $query = $this->getEntityManager()->createQuery($sql);
        $sources = $query->getOneOrNullResult();
        return $sources[0];
    }

    public function findOne($sid)
    {
        $qb = $this->createQueryBuilder('s');
         $qb->where("  s.sourceid = :sid ");
          $qb->setParameter('sid', $sid );
        $qy = $qb->getQuery();
        $source =  $qy->getOneOrNullResult();
        return $source;

    }

     public function getAll()
    {
         $qb = $this->createQueryBuilder('p');
         $qb->orderby(" p.region , p.period");
         $qy = $qb->getQuery();
         $sources = $qy->getResult();
         return $sources;

    }

     public function delete($sid)
    {
        $sql = "delete from App:Source g ";
        $sql .= " where g.sourceid = ".$sid." ";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->getResult();
    }


        public function findbyLocation($location)
    {
        $sql = "select s from App:Source s ";
        $sql .= " where s.region LIKE '%".$location."%' ";
        $query = $this->getEntityManager()->createQuery($sql);
        $sources = $query->getResult();
        return $sources;
    }
}


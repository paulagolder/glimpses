<?php

namespace App\Repository;

use App\Entity\Source;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class SourceRepository extends EntityRepository
{



    public function getOne($sid)
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


  /*  public function seek($location)
    {
        $sql = "select s from App:Source s ";
        $sql .= " where s.region LIKE '".$location."' where ";

        $query = $this->getEntityManager()->createQuery($sql);
        $sources = $query->getResult();
        return $sources;
    }*/

     public function filterf($filterstr)
       {
           $qb = $this->createQueryBuilder('a');
           $qb->select();
           $sql = "SELECT s FROM App\Entity\Source s WHERE ";
           $filterlist = explode(",",$filterstr);
           $n=0;
           foreach($filterlist as $filter)
           {
                //dump($filter);
                if($n>0) $sql .=" or ";
                $afilter= "%".$filter."%";
                $sql .= "  s.title LIKE '".$afilter."' or s.region LIKE '".$afilter."' ";
                $n++;
           }
           //dump($sql);
           $query = $this->getEntityManager()->createQuery($sql);
           $sources = $query->getResult();
           //dump($sources);
           return $sources;
       }



}


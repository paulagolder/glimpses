<?php

namespace App\Repository;

use App\Entity\Glimpse;
use App\Entity\Role;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\MyLibrary;
use Doctrine\DBAL\Driver\Connection;

class GlimpseRepository extends EntityRepository
{

    public function getOne($gid)
    {
        $qb = $this->createQueryBuilder('g');
        $qb->where("g.glimpseid = :gid ");
        $qb->setParameter( "gid", $gid);
        $glimpse = $qb->getQuery()->getOneOrNullResult();
        return $glimpse;
    }

    public function findAll()
    {
        $qb = $this->createQueryBuilder('g');
        $qb->orderby("g.date ");
        $glimpses = $qb->getQuery()->getResult();
        return $glimpses;
    }

    public function delete($gid)
    {
        $sql = "delete from App:Glimpse g ";
        $sql .= " where g.glimpseid = ".$gid." ";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->getResult();
    }

    public function viewregion($region)
    {
        $sql = "select g from App:glimpse g ";
        $sql .= " where g.location = '".$region."' ";
        $query = $this->getEntityManager()->createQuery($sql);
        $glimpses = $query->getResult();
        return $glimpses;
    }

    public function viewsource($sourceid)
    {
        $qb = $this->createQueryBuilder('g');
        $qb->where("g.sourceid = :sourceid ");
        $qb->orderby("g.date");
        $qb->setParameter( "sourceid", $sourceid);
        $sources = $qb->getQuery()->getResult();
        foreach($sources as &$source )
        {
         $gid= $source->getGlimpseid();
         $roles = $this->getEntityManager()->getRepository(Role::class)->findChildren($gid);
         $source->{"roles"} = $roles;
        }
        //dump($sources);
        return $sources;
    }

    public function filter($filter)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "select g from App:Glimpse g  where  LOCATE( 'Coker' , g.location  ) > 0 ";
    //    $query = $this->getEntityManager()->createQuery($sql);
     //   //dump($query);
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        //dump($results);
        return $results;
    }

    public function filterf($filterstring)
    {
       $conn = $this->getEntityManager()->getConnection();
        $filterlist = explode(",", $filterstring);
        $sql = "select g from App:Glimpse g , App:Role r where  g.glimpseid = r.glimpseref and ( ";
        $n =0;
        foreach($filterlist as $filterpair)
        {
           $filter = explode("+",$filterpair);
           if(count($filter)>1)
           {
             $namefilter ="%".$filter[0]."%".$filter[1]."%";
           }else
           {
             $namefilter ="%".$filter[0]."%";
           }
           if($n >0 ) $sql .= " or ";
           $sql .= "  r.name like '".$namefilter."' or  r.predicates like '".$namefilter."'" ;
           $n++;
        }
   /* $sql .="   r.name like '".$namefilter."'  )";*/
        $sql .= ") order by  g.date ";
        $query = $this->getEntityManager()->createQuery($sql);
        $glimpses = $query->getResult();
        $n=0;

        foreach($glimpses as &$glimpse )
        {
           $roles = $this->getEntityManager()->getRepository(Role::class)->findChildren($glimpse->getGlimpseid());
           $glimpse->{"role"} = $roles;
        }
        return $glimpses;
    }

    public function findDuplicates($glimpse1)
    {
       $conn = $this->getEntityManager()->getConnection();
         //dump($glimpse1);
        $sql = "select g from App:Glimpse g  where  g.type = '".$glimpse1->getType()."' and  g.date = '".$glimpse1->getDate()."' ";
        $query = $this->getEntityManager()->createQuery($sql);
        $glimpses = $query->getResult();
        $n=0;
        foreach($glimpses as &$glimpse )
        {
           $roles = $this->getEntityManager()->getRepository(Role::class)->findChildren($glimpse->getGlimpseid());
           $glimpse->{"role"} = $roles;
        }
        return $glimpses;
    }


    public function Countglimpses($sourceid)
    {
        $sql = "select  min(g.date),max(g.date),count(g) from App:Glimpse as g ";
        $sql .= " where g.sourceid = $sourceid  group by g.sourceid";
        $query = $this->getEntityManager()->createQuery($sql);
        $results = $query->getResult();
        if($results)
        return $results[0];
        else return [0,0,0,0];
    }



}





